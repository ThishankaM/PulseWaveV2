CREATE DATABASE  IF NOT EXISTS `project` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `project`;
-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: project
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `artist`
--

DROP TABLE IF EXISTS `artist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `artist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `followcount` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artist`
--

LOCK TABLES `artist` WRITE;
/*!40000 ALTER TABLE `artist` DISABLE KEYS */;
INSERT INTO `artist` VALUES (1,'Jennifer Lopez',1000,'2025-01-25 18:54:39'),(2,'Carly Rae Jepsen',500,'2025-01-25 18:54:39'),(3,'Akiaura',300,'2025-01-25 18:54:39'),(4,'LONOWN',550,'2025-01-25 18:54:39'),(5,'Morgan Wallen',1000,'2025-01-25 18:54:39'),(6,'SUICIDAL-IDOL',400,'2025-01-25 18:54:39'),(7,'Rihanna',700,'2025-01-25 18:54:39'),(8,'Ironmouse',800,'2025-01-25 18:54:39'),(9,'Kiara Takanashi',300,'2025-01-25 18:54:39'),(10,'Ramin Djawadi',530,'2025-01-25 18:54:39'),(11,'Honor of Kings',800,'2025-01-25 18:54:39'),(12,'指尖泪',400,'2025-01-25 18:54:39'),(13,'SING女团',600,'2025-01-25 18:54:39'),(14,'LISA',800,'2025-01-25 18:54:39'),(15,'Darci',1500,'2025-01-25 18:54:39'),(16,'Ado',850,'2025-01-25 18:54:39'),(17,'Ariana Grande',1200,'2025-01-25 18:54:39'),(18,'Carly Rae Jepsen',950,'2025-01-25 18:54:39'),(19,'Ironmouse',700,'2025-01-25 18:54:39'),(20,'Jennifer Lopez',1100,'2025-01-25 18:54:39'),(21,'Kordell',650,'2025-01-25 18:54:39'),(22,'LiSA',1300,'2025-01-25 18:54:39'),(23,'Maroon 5',1150,'2025-01-25 18:54:39'),(24,'Reol',900,'2025-01-25 18:54:39'),(25,'Rose',1400,'2025-01-25 18:54:39'),(26,'Sing',720,'2025-01-25 18:54:39'),(27,'Taylor Swift',1500,'2025-01-25 18:54:39'),(28,'Weekend',1000,'2025-01-25 18:54:39'),(29,'Yuri',880,'2025-01-25 18:54:39');
/*!40000 ALTER TABLE `artist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `follows`
--

DROP TABLE IF EXISTS `follows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `follows` (
  `User_ID` int(11) NOT NULL,
  `Artist_ID` int(11) NOT NULL,
  `Followed_At` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`User_ID`,`Artist_ID`),
  KEY `Artist_ID` (`Artist_ID`),
  CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `logins` (`id`),
  CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`Artist_ID`) REFERENCES `artist` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `follows`
--

LOCK TABLES `follows` WRITE;
/*!40000 ALTER TABLE `follows` DISABLE KEYS */;
INSERT INTO `follows` VALUES (1,3,'2025-01-26 00:57:22'),(2,1,'2025-01-26 00:57:22'),(6,2,'2025-01-26 00:57:22'),(6,5,'2025-01-26 00:57:22'),(7,4,'2025-01-26 00:57:22');
/*!40000 ALTER TABLE `follows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logins`
--

DROP TABLE IF EXISTS `logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Usertype` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logins`
--

LOCK TABLES `logins` WRITE;
/*!40000 ALTER TABLE `logins` DISABLE KEYS */;
INSERT INTO `logins` VALUES (1,'Shadow','thishanka.methshan@gmail.com','$2y$10$cVsJGSDQzG7rOzWmBA1BfeY1rjOcmBxhB4UJz/Q0o0iBOUzlPNG3e','user'),(2,'jeff','example2@gmail.com','$2y$10$6PI1T1WOgG3cxaHGEVvnM.R/tqzbmMSDMKcKJmPRRBX1ZEzdxfQSC','artist'),(6,'Darci','example@gmail.com','$2a$10$Aiy75/Sj7m5O/axrjkKKIeo3jCaSZeH.WT5o27JriROlHG39.JmEe','artist'),(7,'Chenix','example3@gmail.com','$2a$10$3uiLrDMXcAxBuFMbh5iw/uvejVa2GUxr9rozHDo5RlHotbp7GKLvy','artist'),(8,'DrAX','shadow@gmail.com','$2y$10$xas7BGhlztkwBEpUdut9C.9oJxNU7dakGaLb49Xs70l5JcWVu1IqK','user'),(9,'Justin Biber','example8@gmail.com','$2a$10$RXih8BEiwW8lqQimsX197.Qxg00Ut3gMtFyvzTc7RtgewYoGBwHtm','artist');
/*!40000 ALTER TABLE `logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `music`
--

DROP TABLE IF EXISTS `music`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `album_art` varchar(255) NOT NULL,
  `LikeCount` int(11) DEFAULT NULL,
  `artist_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artist_id` (`artist_id`),
  CONSTRAINT `music_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artist` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `music`
--

LOCK TABLES `music` WRITE;
/*!40000 ALTER TABLE `music` DISABLE KEYS */;
INSERT INTO `music` VALUES (1,'Papi','Jennifer Lopez','audio/papi.opus','album_art/papi.jpg',100,1),(2,'I Really Like You','Carly Rae Jepsen','audio/I_Really_Like_You.flac','album_art/I_Really_Like_You.webp',300,2),(3,'Aint your Mama','Jennifer Lopez','audio/Aint_your_Mama.flac','album_art/aintyourmama.jpg',400,1),(4,'Follow The Leader','Jennifer Lopez','audio/Follow_the_leader.opus','album_art/followtheleader.jpg',70,1),(7,'Call Me Maybe','Carly Rae Jepsen','audio/Call_Me_Maybe.flac','album_art/callmemaybe.jpg',40,2),(8,'On The Floor','Jennifer Lopez','audio/On_The_Floor.flac','album_art/papi.jpg',210,1),(9,'Pussypodium Slowed','Akiaura & LONOWN','audio/pussypodium.flac','album_art/pussypodium.jpg',320,3),(10,'AVANGARD','LONOWN','audio/AVANGARD.flac','album_art/avangard.jpg',550,4),(11,'Whiskey Glasses','Morgan Wallen','audio/Whiskey_Glasses.flac','album_art/whiskeyglasses.jpg',1000,5),(12,'APT','ROSE & Bruno Mars','audio/apt.mp3','album_art/apt.jpg',10000,25),(13,'Ecstacy (slowed)','SUICIDAL-IDOL','audio/ecstacy.flac','album_art/ectasy.jpg',220,6),(14,'S&M','Rihanna','audio/S&M.flac','album_art/S&M.jpg',700,7),(15,'KING','Ironmouse shirobeats & Sleeping Forest','audio/KING.flac','album_art/KING.jpg',800,8),(16,'Chimera','Kiara Takanashi','audio/Chimera.flac','album_art/Chimera.jpg',430,9),(17,'GOT Main Season 1','Ramin Djawadi','audio/MainTitle.flac','album_art/GOTMain1.jpg',530,10),(18,'This World','Honor of Kings & True Hertz','audio/ThisWorld.flac','album_art/TrueHertz.jpg',260,11),(19,'不问别离','指尖笑','audio/不问别离.flac','album_art/不问别离Electronic.jpg',800,12),(20,'寄明月（梦幻西游手游同人曲入骨相思）',' SING女团','audio/寄明月.flac','album_art/寄明月.jpg',400,13),(21,'ADAMAS','LiSA','audio/ADAMAS.flac','album_art/Adamas.jpg',200,14),(25,'Throwback','Darci','audio/Throwback.flac','album_art/Throwback.jpg',1500,15),(26,'Ghost','Justin Biber','audio/Ghost.flac','album_art/Ghost.jpg',NULL,NULL);
/*!40000 ALTER TABLE `music` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playlist_songs`
--

DROP TABLE IF EXISTS `playlist_songs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `playlist_songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `playlist_id` (`playlist_id`),
  KEY `song_id` (`song_id`),
  CONSTRAINT `playlist_songs_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`),
  CONSTRAINT `playlist_songs_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `music` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playlist_songs`
--

LOCK TABLES `playlist_songs` WRITE;
/*!40000 ALTER TABLE `playlist_songs` DISABLE KEYS */;
INSERT INTO `playlist_songs` VALUES (4,4,9,'2025-01-17 04:48:21'),(5,4,10,'2025-01-17 04:48:27'),(6,4,13,'2025-01-17 04:48:31'),(23,10,1,'2025-01-18 18:38:03'),(24,10,2,'2025-01-18 18:38:05'),(25,10,4,'2025-01-18 18:38:09'),(26,10,7,'2025-01-18 18:38:12'),(27,11,1,'2025-01-28 17:47:24'),(28,11,2,'2025-01-28 17:47:26'),(31,11,3,'2025-01-28 17:47:34'),(32,11,8,'2025-01-28 17:47:36'),(33,11,10,'2025-01-28 17:47:39'),(34,12,21,'2025-01-28 17:47:53'),(35,12,20,'2025-01-28 17:47:55'),(36,12,25,'2025-01-28 17:47:57'),(37,12,18,'2025-01-28 17:47:59'),(38,12,17,'2025-01-28 17:48:02'),(39,13,19,'2025-01-28 17:48:14'),(40,13,16,'2025-01-28 17:48:17'),(41,13,13,'2025-01-28 17:48:19'),(42,13,20,'2025-01-28 17:48:23');
/*!40000 ALTER TABLE `playlist_songs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playlists`
--

DROP TABLE IF EXISTS `playlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playlists`
--

LOCK TABLES `playlists` WRITE;
/*!40000 ALTER TABLE `playlists` DISABLE KEYS */;
INSERT INTO `playlists` VALUES (4,2,'phonk','2025-01-17 04:48:11'),(10,1,'My Hits','2025-01-17 13:58:37'),(11,8,'Random 1','2025-01-28 17:47:21'),(12,8,'Random 2','2025-01-28 17:47:50'),(13,8,'Random 3','2025-01-28 17:48:12');
/*!40000 ALTER TABLE `playlists` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-04 19:26:24
