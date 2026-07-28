-- PulseWave Enhanced
-- Fresh MySQL 8 / MariaDB 10.4+ schema.
-- The original database is preserved separately in database/legacy/.

CREATE DATABASE IF NOT EXISTS `pulsewave`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `pulsewave`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `listening_history`;
DROP TABLE IF EXISTS `likes`;
DROP TABLE IF EXISTS `follows`;
DROP TABLE IF EXISTS `playlist_tracks`;
DROP TABLE IF EXISTS `playlists`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `subscription_plans`;
DROP TABLE IF EXISTS `track_genres`;
DROP TABLE IF EXISTS `genres`;
DROP TABLE IF EXISTS `tracks`;
DROP TABLE IF EXISTS `artists`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(80) NOT NULL,
    `email` VARCHAR(190) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'artist', 'admin') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_role_status_index` (`role`, `status`)
) ENGINE=InnoDB;

CREATE TABLE `artists` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `name` VARCHAR(120) NOT NULL,
    `slug` VARCHAR(140) NOT NULL,
    `bio` VARCHAR(500) NULL,
    `image_path` VARCHAR(255) NULL,
    `follower_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `artists_slug_unique` (`slug`),
    UNIQUE KEY `artists_user_unique` (`user_id`),
    KEY `artists_name_index` (`name`),
    CONSTRAINT `artists_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `tracks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `artist_id` INT UNSIGNED NOT NULL,
    `uploaded_by` INT UNSIGNED NULL,
    `title` VARCHAR(150) NOT NULL,
    `audio_path` VARCHAR(255) NOT NULL,
    `cover_path` VARCHAR(255) NOT NULL DEFAULT 'uploads/album_art/default-album-art.jpg',
    `release_year` SMALLINT UNSIGNED NULL,
    `duration_seconds` INT UNSIGNED NULL,
    `play_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `like_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `tracks_artist_index` (`artist_id`),
    KEY `tracks_title_index` (`title`),
    KEY `tracks_popularity_index` (`play_count`, `like_count`),
    KEY `tracks_uploader_index` (`uploaded_by`),
    CONSTRAINT `tracks_artist_fk`
        FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `tracks_uploader_fk`
        FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `genres` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(80) NOT NULL,
    `slug` VARCHAR(90) NOT NULL,
    `accent_color` CHAR(7) NOT NULL DEFAULT '#9d6cff',
    PRIMARY KEY (`id`),
    UNIQUE KEY `genres_name_unique` (`name`),
    UNIQUE KEY `genres_slug_unique` (`slug`)
) ENGINE=InnoDB;

CREATE TABLE `track_genres` (
    `track_id` INT UNSIGNED NOT NULL,
    `genre_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`track_id`, `genre_id`),
    KEY `track_genres_genre_index` (`genre_id`),
    CONSTRAINT `track_genres_track_fk`
        FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE,
    CONSTRAINT `track_genres_genre_fk`
        FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `playlists` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(80) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `playlists_user_index` (`user_id`, `updated_at`),
    CONSTRAINT `playlists_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `playlist_tracks` (
    `playlist_id` INT UNSIGNED NOT NULL,
    `track_id` INT UNSIGNED NOT NULL,
    `position` INT UNSIGNED NOT NULL DEFAULT 1,
    `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`playlist_id`, `track_id`),
    KEY `playlist_tracks_track_index` (`track_id`),
    KEY `playlist_tracks_position_index` (`playlist_id`, `position`),
    CONSTRAINT `playlist_tracks_playlist_fk`
        FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
    CONSTRAINT `playlist_tracks_track_fk`
        FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `likes` (
    `user_id` INT UNSIGNED NOT NULL,
    `track_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `track_id`),
    KEY `likes_track_index` (`track_id`, `created_at`),
    CONSTRAINT `likes_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `likes_track_fk`
        FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `follows` (
    `user_id` INT UNSIGNED NOT NULL,
    `artist_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `artist_id`),
    KEY `follows_artist_index` (`artist_id`, `created_at`),
    CONSTRAINT `follows_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `follows_artist_fk`
        FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `listening_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `track_id` INT UNSIGNED NOT NULL,
    `played_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `history_user_time_index` (`user_id`, `played_at`),
    KEY `history_track_index` (`track_id`),
    CONSTRAINT `history_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `history_track_fk`
        FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `subscription_plans` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(60) NOT NULL,
    `slug` VARCHAR(70) NOT NULL,
    `price_monthly` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `max_devices` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `audio_quality` VARCHAR(40) NOT NULL,
    `features` TEXT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subscription_plans_slug_unique` (`slug`)
) ENGINE=InnoDB;

CREATE TABLE `subscriptions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'active', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
    `provider_reference` VARCHAR(190) NULL,
    `starts_at` DATETIME NULL,
    `ends_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `subscriptions_user_status_index` (`user_id`, `status`),
    KEY `subscriptions_plan_index` (`plan_id`),
    CONSTRAINT `subscriptions_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `subscriptions_plan_fk`
        FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Existing PulseWave accounts are retained. The local admin account below uses
-- password PulseWave123! and must be changed or removed before deployment.
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'Shadow', 'thishanka.methshan@gmail.com', '$2y$10$cVsJGSDQzG7rOzWmBA1BfeY1rjOcmBxhB4UJz/Q0o0iBOUzlPNG3e', 'user', 'active', '2025-01-01 00:00:00'),
(2, 'Jeff', 'example2@gmail.com', '$2y$10$6PI1T1WOgG3cxaHGEVvnM.R/tqzbmMSDMKcKJmPRRBX1ZEzdxfQSC', 'artist', 'active', '2025-01-01 00:00:00'),
(6, 'Darci', 'example@gmail.com', '$2a$10$Aiy75/Sj7m5O/axrjkKKIeo3jCaSZeH.WT5o27JriROlHG39.JmEe', 'artist', 'active', '2025-01-01 00:00:00'),
(7, 'Chenix', 'example3@gmail.com', '$2a$10$3uiLrDMXcAxBuFMbh5iw/uvejVa2GUxr9rozHDo5RlHotbp7GKLvy', 'artist', 'active', '2025-01-01 00:00:00'),
(8, 'DrAX', 'shadow@gmail.com', '$2y$10$xas7BGhlztkwBEpUdut9C.9oJxNU7dakGaLb49Xs70l5JcWVu1IqK', 'user', 'active', '2025-01-01 00:00:00'),
(9, 'Justin Bieber', 'example8@gmail.com', '$2a$10$RXih8BEiwW8lqQimsX197.Qxg00Ut3gMtFyvzTc7RtgewYoGBwHtm', 'artist', 'active', '2025-01-01 00:00:00'),
(10, 'PulseWave Admin', 'admin@pulsewave.local', '$2b$12$QInIg816nIaARRJFJ4eX8OC8oG7fKzJQotdfoicW7G0f0WSeMWoAK', 'admin', 'active', CURRENT_TIMESTAMP);

INSERT INTO `artists` (`id`, `name`, `slug`, `bio`, `image_path`, `follower_count`) VALUES
(1, 'Jennifer Lopez', 'jennifer-lopez', 'Global pop and dance performer known for high-energy records and iconic performances.', 'uploads/artists/JenniferLopez.jpg', 1100),
(2, 'Carly Rae Jepsen', 'carly-rae-jepsen', 'Canadian pop artist celebrated for bright melodies and emotionally precise songwriting.', 'uploads/artists/CarlyRaeJepsen.jpg', 950),
(3, 'Akiaura', 'akiaura', 'Electronic artist creating atmospheric, bass-heavy soundscapes.', 'uploads/album_art/pussypodium.jpg', 300),
(4, 'LONOWN', 'lonown', 'Producer exploring dark electronic and phonk-inspired textures.', 'uploads/album_art/avangard.jpg', 550),
(5, 'Morgan Wallen', 'morgan-wallen', 'Country artist blending contemporary production with Southern storytelling.', 'uploads/album_art/whiskeyglasses.jpg', 1000),
(6, 'SUICIDAL-IDOL', 'suicidal-idol', 'Alternative electronic creator known for dreamy slowed-down releases.', 'uploads/album_art/ectasy.jpg', 400),
(7, 'Rihanna', 'rihanna', 'Barbadian artist whose catalog spans pop, R&B and dance.', 'uploads/artists/Rihanna.jpg', 700),
(8, 'Ironmouse', 'ironmouse', 'Virtual performer and singer with a wide-ranging, energetic music catalog.', 'uploads/artists/Ironmouse.jpg', 800),
(9, 'Kiara Takanashi', 'kiara-takanashi', 'Virtual singer delivering vibrant pop performances.', 'uploads/album_art/Chimera.jpg', 300),
(10, 'Ramin Djawadi', 'ramin-djawadi', 'Composer known for cinematic themes and large-scale television scores.', 'uploads/album_art/GOTMain1.jpg', 530),
(11, 'Honor of Kings', 'honor-of-kings', 'Music and original soundtracks from the Honor of Kings universe.', 'uploads/album_art/TrueHertz.jpg', 800),
(12, '指尖笑', 'zhi-jian-xiao', 'Chinese-language artist featured in the PulseWave C-Pop collection.', 'uploads/album_art/不问别离Electronic.jpg', 400),
(13, 'SING女团', 'sing-girls', 'Chinese pop group combining traditional visual themes with modern production.', 'uploads/artists/Sing.jpg', 600),
(14, 'LiSA', 'lisa-japan', 'Japanese singer known for powerful rock vocals and anime theme songs.', 'uploads/artists/LiSA.jpg', 1300),
(15, 'Darci', 'darci', 'Alternative R&B artist with atmospheric production and intimate vocals.', 'uploads/album_art/Throwback.jpg', 1500),
(16, 'Ado', 'ado', 'Japanese vocalist known for dramatic performances and a distinctive expressive voice.', 'uploads/artists/ado.jpg', 850),
(17, 'Ariana Grande', 'ariana-grande', 'Pop and R&B vocalist recognized for agile vocals and polished production.', 'uploads/artists/Arianagrande.jpg', 1200),
(18, 'Kordhell', 'kordhell', 'Producer known for aggressive phonk rhythms and cinematic atmosphere.', 'uploads/artists/kordhell.jpg', 650),
(19, 'Maroon 5', 'maroon-5', 'Pop-rock group with a catalog of radio-focused hits and collaborations.', 'uploads/artists/maroon5.jpg', 1150),
(20, 'Reol', 'reol', 'Japanese singer, songwriter and producer blending electronic music and pop.', 'uploads/artists/Reol.jpg', 900),
(21, 'Rosé', 'rose', 'Singer-songwriter known for expressive pop vocals and global collaborations.', 'uploads/artists/Rose.jpg', 1400),
(22, 'Taylor Swift', 'taylor-swift', 'Singer-songwriter with a wide catalog spanning country, pop and alternative music.', 'uploads/artists/taylorswift.jpg', 1500),
(23, 'The Weeknd', 'the-weeknd', 'Canadian singer and producer known for dark pop and contemporary R&B.', 'uploads/artists/TheWeekend.jpg', 1000),
(24, 'Yuri', 'yuri', 'J-Pop vocalist presented in the original PulseWave artist collection.', 'uploads/artists/Yuri.jpg', 880),
(25, 'Justin Bieber', 'justin-bieber', 'Pop and R&B artist with a catalog of global releases.', 'uploads/album_art/default-album-art.jpg', 1000);

INSERT INTO `genres` (`id`, `name`, `slug`, `accent_color`) VALUES
(1, 'Pop', 'pop', '#ef6ff2'),
(2, 'J-Pop', 'j-pop', '#9d6cff'),
(3, 'K-Pop', 'k-pop', '#ff7aa8'),
(4, 'C-Pop', 'c-pop', '#f7c86b'),
(5, 'Phonk', 'phonk', '#a78bfa'),
(6, 'Electronic', 'electronic', '#4fd1c5'),
(7, 'Rock', 'rock', '#fb7185'),
(8, 'Soundtrack', 'soundtrack', '#60a5fa'),
(9, 'R&B', 'r-and-b', '#c084fc'),
(10, 'Country', 'country', '#fbbf24'),
(11, 'Metal', 'metal', '#94a3b8'),
(12, 'Alternative', 'alternative', '#34d399'),
(13, 'Chill', 'chill', '#67e8f9'),
(14, 'Workout', 'workout', '#bef264'),
(15, 'Party', 'party', '#f97316');

INSERT INTO `tracks` (`id`, `artist_id`, `title`, `audio_path`, `cover_path`, `release_year`, `play_count`, `like_count`) VALUES
(1, 1, 'Papi', 'uploads/audio/papi.opus', 'uploads/album_art/papi.jpg', 2011, 3820, 100),
(2, 2, 'I Really Like You', 'uploads/audio/I_Really_Like_You.flac', 'uploads/album_art/I_Really_Like_You.webp', 2015, 4410, 300),
(3, 1, 'Ain''t Your Mama', 'uploads/audio/Aint_your_Mama.flac', 'uploads/album_art/aintyourmama.jpg', 2016, 3520, 400),
(4, 1, 'Follow the Leader', 'uploads/audio/Follow_the_leader.opus', 'uploads/album_art/followtheleader.jpg', 2012, 1920, 70),
(7, 2, 'Call Me Maybe', 'uploads/audio/Call_Me_Maybe.flac', 'uploads/album_art/callmemaybe.jpg', 2011, 5580, 40),
(8, 1, 'On the Floor', 'uploads/audio/On_The_Floor.flac', 'uploads/album_art/papi.jpg', 2011, 6840, 210),
(9, 3, 'Pussypodium Slowed', 'uploads/audio/pussypodium.flac', 'uploads/album_art/pussypodium.jpg', 2023, 4920, 320),
(10, 4, 'AVANGARD', 'uploads/audio/AVANGARD.flac', 'uploads/album_art/avangard.jpg', 2023, 5170, 550),
(11, 5, 'Whiskey Glasses', 'uploads/audio/Whiskey_Glasses.flac', 'uploads/album_art/whiskeyglasses.jpg', 2018, 4210, 1000),
(12, 21, 'APT.', 'uploads/audio/apt.mp3', 'uploads/album_art/apt.jpg', 2024, 15200, 10000),
(13, 6, 'Ecstacy (Slowed)', 'uploads/audio/ecstacy.flac', 'uploads/album_art/ectasy.jpg', 2023, 3200, 220),
(14, 7, 'S&M', 'uploads/audio/S&M.flac', 'uploads/album_art/S&M.jpg', 2010, 7300, 700),
(15, 8, 'KING', 'uploads/audio/KING.flac', 'uploads/album_art/KING.jpg', 2024, 6220, 800),
(16, 9, 'Chimera', 'uploads/audio/Chimera.flac', 'uploads/album_art/Chimera.jpg', 2024, 2850, 430),
(17, 10, 'Game of Thrones Main Title', 'uploads/audio/MainTitle.flac', 'uploads/album_art/GOTMain1.jpg', 2011, 5420, 530),
(18, 11, 'This World', 'uploads/audio/ThisWorld.flac', 'uploads/album_art/TrueHertz.jpg', 2024, 3980, 260),
(19, 12, '不问别离', 'uploads/audio/不问别离.flac', 'uploads/album_art/不问别离Electronic.jpg', 2024, 4420, 800),
(20, 13, '寄明月（梦幻西游手游同人曲入骨相思）', 'uploads/audio/寄明月.flac', 'uploads/album_art/寄明月.jpg', 2024, 4180, 400),
(21, 14, 'ADAMAS', 'uploads/audio/ADAMAS.flac', 'uploads/album_art/Adamas.jpg', 2018, 6240, 200),
(25, 15, 'Throwback', 'uploads/audio/Throwback.flac', 'uploads/album_art/Throwback.jpg', 2017, 4850, 1500),
(26, 25, 'Ghost', 'uploads/audio/Ghost.flac', 'uploads/album_art/default-album-art.jpg', 2021, 3890, 0);

INSERT INTO `track_genres` (`track_id`, `genre_id`) VALUES
(1,1),(1,15),(2,1),(3,1),(3,15),(4,1),(4,15),(7,1),(8,1),(8,15),
(9,5),(9,6),(10,5),(10,6),(11,10),(12,1),(12,3),(13,6),(13,12),
(14,1),(14,9),(15,1),(15,2),(16,1),(16,2),(17,8),(18,8),(18,6),
(19,4),(19,6),(20,4),(20,1),(21,2),(21,7),(25,9),(25,12),(26,1);

INSERT INTO `playlists` (`id`, `user_id`, `name`, `created_at`) VALUES
(4, 2, 'Phonk', '2025-01-17 04:48:11'),
(10, 1, 'My Hits', '2025-01-17 13:58:37'),
(11, 8, 'Random 1', '2025-01-28 17:47:21'),
(12, 8, 'Random 2', '2025-01-28 17:47:50'),
(13, 8, 'Random 3', '2025-01-28 17:48:12');

INSERT INTO `playlist_tracks` (`playlist_id`, `track_id`, `position`, `added_at`) VALUES
(4,9,1,'2025-01-17 04:48:21'),(4,10,2,'2025-01-17 04:48:27'),(4,13,3,'2025-01-17 04:48:31'),
(10,1,1,'2025-01-18 18:38:03'),(10,2,2,'2025-01-18 18:38:05'),(10,4,3,'2025-01-18 18:38:09'),(10,7,4,'2025-01-18 18:38:12'),
(11,1,1,'2025-01-28 17:47:24'),(11,2,2,'2025-01-28 17:47:26'),(11,3,3,'2025-01-28 17:47:34'),(11,8,4,'2025-01-28 17:47:36'),(11,10,5,'2025-01-28 17:47:39'),
(12,21,1,'2025-01-28 17:47:53'),(12,20,2,'2025-01-28 17:47:55'),(12,25,3,'2025-01-28 17:47:57'),(12,18,4,'2025-01-28 17:47:59'),(12,17,5,'2025-01-28 17:48:02'),
(13,19,1,'2025-01-28 17:48:14'),(13,16,2,'2025-01-28 17:48:17'),(13,13,3,'2025-01-28 17:48:19'),(13,20,4,'2025-01-28 17:48:23');

INSERT INTO `follows` (`user_id`, `artist_id`, `created_at`) VALUES
(1,3,'2025-01-26 00:57:22'),(2,1,'2025-01-26 00:57:22'),(6,2,'2025-01-26 00:57:22'),(6,5,'2025-01-26 00:57:22'),(7,4,'2025-01-26 00:57:22');

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `price_monthly`, `max_devices`, `audio_quality`, `features`) VALUES
(1, 'Free', 'free', 0.00, 1, 'Standard · 128 kbps', '["Ad-supported listening","Limited skips","Playlist creation"]'),
(2, 'Student', 'student', 4.99, 2, 'High · 256 kbps', '["Ad-free listening","Unlimited skips","Offline-ready account"]'),
(3, 'Solo', 'solo', 7.99, 3, 'High · 320 kbps', '["Ad-free listening","Unlimited skips","Personal recommendations","Offline-ready account"]'),
(4, 'Family', 'family', 14.99, 6, 'High · 320 kbps', '["Up to six profiles","Parental controls","Shared family playlists","Offline-ready account"]');

INSERT INTO `subscriptions` (`user_id`, `plan_id`, `status`, `starts_at`)
SELECT `id`, 1, 'active', CURRENT_TIMESTAMP FROM `users`;

SET FOREIGN_KEY_CHECKS = 1;
