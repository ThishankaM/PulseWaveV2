<?php
declare(strict_types=1);

final class LibraryRepository
{
    public static function playlists(int $userId): array
    {
        $statement = db()->prepare(
            'SELECT
                p.id,
                p.name,
                p.created_at,
                COUNT(pt.track_id) AS track_count,
                COALESCE(MAX(t.cover_path), "uploads/album_art/default-album-art.jpg") AS cover_path
             FROM playlists p
             LEFT JOIN playlist_tracks pt ON pt.playlist_id = p.id
             LEFT JOIN tracks t ON t.id = pt.track_id
             WHERE p.user_id = ?
             GROUP BY p.id
             ORDER BY p.updated_at DESC, p.created_at DESC'
        );
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    public static function playlist(int $playlistId, int $userId): ?array
    {
        $statement = db()->prepare(
            'SELECT id, name, created_at, updated_at
             FROM playlists
             WHERE id = ? AND user_id = ?
             LIMIT 1'
        );
        $statement->execute([$playlistId, $userId]);
        $playlist = $statement->fetch();
        return $playlist ?: null;
    }

    public static function playlistTracks(int $playlistId, int $userId): array
    {
        $statement = db()->prepare(
            'SELECT
                t.id,
                t.artist_id,
                t.title,
                t.audio_path,
                t.cover_path,
                t.release_year,
                t.play_count,
                t.like_count,
                a.name AS artist_name,
                EXISTS(
                    SELECT 1 FROM likes liked
                    WHERE liked.track_id = t.id AND liked.user_id = ?
                ) AS is_liked
             FROM playlists p
             INNER JOIN playlist_tracks pt ON pt.playlist_id = p.id
             INNER JOIN tracks t ON t.id = pt.track_id
             INNER JOIN artists a ON a.id = t.artist_id
             WHERE p.id = ? AND p.user_id = ?
             ORDER BY pt.position, pt.added_at'
        );
        $statement->execute([$userId, $playlistId, $userId]);
        return $statement->fetchAll();
    }

    public static function availableTracks(int $playlistId, int $userId): array
    {
        $statement = db()->prepare(
            'SELECT t.id, t.title, a.name AS artist_name
             FROM tracks t
             INNER JOIN artists a ON a.id = t.artist_id
             WHERE NOT EXISTS (
                 SELECT 1
                 FROM playlist_tracks pt
                 INNER JOIN playlists p ON p.id = pt.playlist_id
                 WHERE pt.track_id = t.id AND p.id = ? AND p.user_id = ?
             )
             ORDER BY a.name, t.title'
        );
        $statement->execute([$playlistId, $userId]);
        return $statement->fetchAll();
    }

    public static function likedTracks(int $userId): array
    {
        $statement = db()->prepare(
            'SELECT
                t.id,
                t.artist_id,
                t.title,
                t.audio_path,
                t.cover_path,
                t.release_year,
                t.play_count,
                t.like_count,
                a.name AS artist_name,
                1 AS is_liked
             FROM likes liked
             INNER JOIN tracks t ON t.id = liked.track_id
             INNER JOIN artists a ON a.id = t.artist_id
             WHERE liked.user_id = ?
             ORDER BY liked.created_at DESC'
        );
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    public static function createPlaylist(int $userId, string $name): int
    {
        $statement = db()->prepare('INSERT INTO playlists (user_id, name) VALUES (?, ?)');
        $statement->execute([$userId, $name]);
        return (int) db()->lastInsertId();
    }

    public static function renamePlaylist(int $playlistId, int $userId, string $name): bool
    {
        $statement = db()->prepare(
            'UPDATE playlists SET name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?'
        );
        $statement->execute([$name, $playlistId, $userId]);
        return $statement->rowCount() > 0;
    }

    public static function deletePlaylist(int $playlistId, int $userId): bool
    {
        $statement = db()->prepare('DELETE FROM playlists WHERE id = ? AND user_id = ?');
        $statement->execute([$playlistId, $userId]);
        return $statement->rowCount() > 0;
    }

    public static function addTrack(int $playlistId, int $trackId, int $userId): bool
    {
        if (!self::playlist($playlistId, $userId)) {
            return false;
        }

        $positionStatement = db()->prepare(
            'SELECT COALESCE(MAX(position), 0) + 1 FROM playlist_tracks WHERE playlist_id = ?'
        );
        $positionStatement->execute([$playlistId]);
        $position = (int) $positionStatement->fetchColumn();

        $statement = db()->prepare(
            'INSERT IGNORE INTO playlist_tracks (playlist_id, track_id, position) VALUES (?, ?, ?)'
        );
        $statement->execute([$playlistId, $trackId, $position]);
        return $statement->rowCount() > 0;
    }

    public static function removeTrack(int $playlistId, int $trackId, int $userId): bool
    {
        $statement = db()->prepare(
            'DELETE pt
             FROM playlist_tracks pt
             INNER JOIN playlists p ON p.id = pt.playlist_id
             WHERE pt.playlist_id = ? AND pt.track_id = ? AND p.user_id = ?'
        );
        $statement->execute([$playlistId, $trackId, $userId]);
        return $statement->rowCount() > 0;
    }

    public static function userStats(int $userId): array
    {
        $statement = db()->prepare(
            'SELECT
                (SELECT COUNT(*) FROM playlists WHERE user_id = ?) AS playlists,
                (SELECT COUNT(*) FROM likes WHERE user_id = ?) AS likes,
                (SELECT COUNT(*) FROM follows WHERE user_id = ?) AS follows,
                (SELECT COUNT(*) FROM listening_history WHERE user_id = ?) AS plays'
        );
        $statement->execute([$userId, $userId, $userId, $userId]);
        return $statement->fetch() ?: ['playlists' => 0, 'likes' => 0, 'follows' => 0, 'plays' => 0];
    }
}

