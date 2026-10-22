<?php
declare(strict_types=1);

final class CatalogRepository
{
    private static function trackSelect(): string
    {
        return '
            SELECT
                t.id,
                t.artist_id,
                t.title,
                t.audio_path,
                t.cover_path,
                t.release_year,
                t.duration_seconds,
                t.play_count,
                t.like_count,
                a.name AS artist_name,
                EXISTS(
                    SELECT 1 FROM likes liked
                    WHERE liked.track_id = t.id AND liked.user_id = ?
                ) AS is_liked
            FROM tracks t
            INNER JOIN artists a ON a.id = t.artist_id
        ';
    }

    public static function featuredTracks(int $limit = 12): array
    {
        $limit = max(1, min($limit, 50));
        $statement = db()->prepare(self::trackSelect() . ' ORDER BY t.play_count DESC, t.created_at DESC LIMIT ' . $limit);
        $statement->execute([Auth::id() ?? 0]);
        return $statement->fetchAll();
    }

    public static function trendingTracks(int $limit = 12): array
    {
        $limit = max(1, min($limit, 50));
        $statement = db()->prepare(
            self::trackSelect() .
            ' INNER JOIN listening_history history ON history.track_id = t.id
              WHERE history.played_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY)
              GROUP BY t.id
              ORDER BY COUNT(history.id) DESC, t.play_count DESC
              LIMIT ' . $limit
        );
        $statement->execute([Auth::id() ?? 0]);
        $tracks = $statement->fetchAll();
        return $tracks ?: self::featuredTracks($limit);
    }

    public static function recentTracks(int $limit = 12): array
    {
        $limit = max(1, min($limit, 50));
        $statement = db()->prepare(self::trackSelect() . ' ORDER BY t.created_at DESC LIMIT ' . $limit);
        $statement->execute([Auth::id() ?? 0]);
        return $statement->fetchAll();
    }

    public static function recentlyPlayedByUser(int $userId, int $limit = 8): array
    {
        $limit = max(1, min($limit, 20));
        $statement = db()->prepare(
            'SELECT
                t.id,
                t.artist_id,
                t.title,
                t.audio_path,
                t.cover_path,
                t.release_year,
                t.duration_seconds,
                t.play_count,
                t.like_count,
                a.name AS artist_name,
                EXISTS(
                    SELECT 1 FROM likes liked
                    WHERE liked.track_id = t.id AND liked.user_id = ?
                ) AS is_liked,
                MAX(h.played_at) AS last_played_at
             FROM listening_history h
             INNER JOIN tracks t ON t.id = h.track_id
             INNER JOIN artists a ON a.id = t.artist_id
             WHERE h.user_id = ?
             GROUP BY t.id
             ORDER BY last_played_at DESC
             LIMIT ' . $limit
        );
        $statement->execute([$userId, $userId]);
        return $statement->fetchAll();
    }

    public static function allTracks(): array
    {
        $statement = db()->prepare(self::trackSelect() . ' ORDER BY a.name, t.title');
        $statement->execute([Auth::id() ?? 0]);
        return $statement->fetchAll();
    }

    public static function releases(int $limit = 12): array
    {
        $limit = max(1, min($limit, 30));
        $statement = db()->prepare(
            'SELECT
                t.id,
                t.artist_id,
                t.title,
                t.cover_path,
                t.release_year,
                t.duration_seconds,
                a.name AS artist_name,
                grouped.track_count,
                grouped.latest_at
             FROM (
                 SELECT artist_id, cover_path, MIN(id) AS keep_id, COUNT(*) AS track_count, MAX(created_at) AS latest_at
                 FROM tracks
                 WHERE cover_path <> "uploads/album_art/default-album-art.jpg"
                 GROUP BY artist_id, cover_path
             ) grouped
             INNER JOIN tracks t ON t.id = grouped.keep_id
             INNER JOIN artists a ON a.id = t.artist_id
             ORDER BY grouped.latest_at DESC, t.id DESC
             LIMIT ' . $limit
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    public static function releaseTracks(int $representativeId): array
    {
        $representative = self::track($representativeId);
        if (!$representative) {
            return [];
        }
        $statement = db()->prepare(
            self::trackSelect() .
            ' WHERE t.artist_id = ? AND t.cover_path = ?
              ORDER BY t.release_year, t.id'
        );
        $statement->execute([Auth::id() ?? 0, (int) $representative['artist_id'], (string) $representative['cover_path']]);
        return $statement->fetchAll();
    }

    public static function track(int $id): ?array
    {
        $statement = db()->prepare(self::trackSelect() . ' WHERE t.id = ? LIMIT 1');
        $statement->execute([Auth::id() ?? 0, $id]);
        $track = $statement->fetch();
        return $track ?: null;
    }

    public static function artistTracks(int $artistId): array
    {
        $statement = db()->prepare(self::trackSelect() . ' WHERE t.artist_id = ? ORDER BY t.play_count DESC, t.title');
        $statement->execute([Auth::id() ?? 0, $artistId]);
        return $statement->fetchAll();
    }

    public static function genreTracks(int $genreId): array
    {
        $statement = db()->prepare(
            self::trackSelect() .
            ' INNER JOIN track_genres tg ON tg.track_id = t.id
              WHERE tg.genre_id = ?
              ORDER BY t.play_count DESC, t.title'
        );
        $statement->execute([Auth::id() ?? 0, $genreId]);
        return $statement->fetchAll();
    }

    public static function artists(int $limit = 100): array
    {
        $limit = max(1, min($limit, 100));
        $statement = db()->query(
            'SELECT
                a.id,
                a.name,
                a.bio,
                a.image_path,
                a.follower_count,
                COUNT(DISTINCT t.id) AS track_count
             FROM artists a
             LEFT JOIN tracks t ON t.artist_id = a.id
             GROUP BY a.id
             ORDER BY a.follower_count DESC, a.name
             LIMIT ' . $limit
        );
        return $statement->fetchAll();
    }

    public static function artist(int $id): ?array
    {
        $statement = db()->prepare(
            'SELECT
                a.id,
                a.name,
                a.bio,
                a.image_path,
                a.follower_count,
                COUNT(DISTINCT t.id) AS track_count,
                EXISTS(
                    SELECT 1 FROM follows followed
                    WHERE followed.artist_id = a.id AND followed.user_id = ?
                ) AS is_following
             FROM artists a
             LEFT JOIN tracks t ON t.artist_id = a.id
             WHERE a.id = ?
             GROUP BY a.id
             LIMIT 1'
        );
        $statement->execute([Auth::id() ?? 0, $id]);
        $artist = $statement->fetch();
        return $artist ?: null;
    }

    public static function followedArtists(int $userId, int $limit = 12): array
    {
        $limit = max(1, min($limit, 30));
        $statement = db()->prepare(
            'SELECT
                a.id,
                a.name,
                a.bio,
                a.image_path,
                a.follower_count,
                COUNT(DISTINCT t.id) AS track_count
             FROM follows f
             INNER JOIN artists a ON a.id = f.artist_id
             LEFT JOIN tracks t ON t.artist_id = a.id
             WHERE f.user_id = ?
             GROUP BY a.id
             ORDER BY f.created_at DESC
             LIMIT ' . $limit
        );
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    public static function genres(): array
    {
        $statement = db()->query(
            'SELECT
                g.id,
                g.name,
                g.slug,
                g.accent_color,
                COUNT(DISTINCT tg.track_id) AS track_count,
                COALESCE(MAX(t.cover_path), "uploads/album_art/default-album-art.jpg") AS cover_path
             FROM genres g
             LEFT JOIN track_genres tg ON tg.genre_id = g.id
             LEFT JOIN tracks t ON t.id = tg.track_id
             GROUP BY g.id
             ORDER BY g.name'
        );
        return $statement->fetchAll();
    }

    public static function genre(int $id): ?array
    {
        $statement = db()->prepare('SELECT id, name, slug, accent_color FROM genres WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $genre = $statement->fetch();
        return $genre ?: null;
    }

    public static function search(string $query, int $limit = 8): array
    {
        $query = trim($query);
        $limit = max(1, min($limit, 20));
        if (mb_strlen($query) < 2) {
            return ['releases' => [], 'tracks' => [], 'artists' => [], 'genres' => []];
        }

        $like = '%' . $query . '%';
        $trackStatement = db()->prepare(
            'SELECT t.id, t.title, t.cover_path, a.id AS artist_id, a.name AS artist_name
             FROM tracks t
             INNER JOIN artists a ON a.id = t.artist_id
             WHERE t.title LIKE ? OR a.name LIKE ?
             ORDER BY t.play_count DESC
             LIMIT ' . $limit
        );
        $trackStatement->execute([$like, $like]);

        $artistStatement = db()->prepare(
            'SELECT id, name, image_path
             FROM artists
             WHERE name LIKE ?
             ORDER BY follower_count DESC
             LIMIT ' . $limit
        );
        $artistStatement->execute([$like]);

        $genreStatement = db()->prepare(
            'SELECT id, name, accent_color
             FROM genres
             WHERE name LIKE ?
             ORDER BY name
             LIMIT ' . $limit
        );
        $genreStatement->execute([$like]);

        $releaseStatement = db()->prepare(
            'SELECT t.id, t.title, t.cover_path, t.release_year, a.name AS artist_name
             FROM tracks t
             INNER JOIN artists a ON a.id = t.artist_id
             WHERE t.cover_path <> "uploads/album_art/default-album-art.jpg"
               AND (t.title LIKE ? OR a.name LIKE ?)
             ORDER BY t.release_year DESC, t.play_count DESC
             LIMIT ' . $limit
        );
        $releaseStatement->execute([$like, $like]);

        return [
            'releases' => $releaseStatement->fetchAll(),
            'tracks' => $trackStatement->fetchAll(),
            'artists' => $artistStatement->fetchAll(),
            'genres' => $genreStatement->fetchAll(),
        ];
    }

    public static function recordPlay(int $trackId): void
    {
        $connection = db();
        $connection->beginTransaction();
        try {
            $update = $connection->prepare('UPDATE tracks SET play_count = play_count + 1 WHERE id = ?');
            $update->execute([$trackId]);

            if (Auth::check()) {
                $history = $connection->prepare(
                    'INSERT INTO listening_history (user_id, track_id, played_at)
                     VALUES (?, ?, CURRENT_TIMESTAMP)'
                );
                $history->execute([Auth::id(), $trackId]);
            }
            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    public static function toggleLike(int $trackId, int $userId): bool
    {
        $connection = db();
        $connection->beginTransaction();
        try {
            $check = $connection->prepare('SELECT 1 FROM likes WHERE user_id = ? AND track_id = ? FOR UPDATE');
            $check->execute([$userId, $trackId]);
            $liked = (bool) $check->fetchColumn();

            if ($liked) {
                $delete = $connection->prepare('DELETE FROM likes WHERE user_id = ? AND track_id = ?');
                $delete->execute([$userId, $trackId]);
                $update = $connection->prepare('UPDATE tracks SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?');
                $update->execute([$trackId]);
            } else {
                $insert = $connection->prepare('INSERT INTO likes (user_id, track_id) VALUES (?, ?)');
                $insert->execute([$userId, $trackId]);
                $update = $connection->prepare('UPDATE tracks SET like_count = like_count + 1 WHERE id = ?');
                $update->execute([$trackId]);
            }

            $connection->commit();
            return !$liked;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    public static function toggleFollow(int $artistId, int $userId): bool
    {
        $connection = db();
        $connection->beginTransaction();
        try {
            $check = $connection->prepare('SELECT 1 FROM follows WHERE user_id = ? AND artist_id = ? FOR UPDATE');
            $check->execute([$userId, $artistId]);
            $following = (bool) $check->fetchColumn();

            if ($following) {
                $delete = $connection->prepare('DELETE FROM follows WHERE user_id = ? AND artist_id = ?');
                $delete->execute([$userId, $artistId]);
                $update = $connection->prepare('UPDATE artists SET follower_count = GREATEST(follower_count - 1, 0) WHERE id = ?');
                $update->execute([$artistId]);
            } else {
                $insert = $connection->prepare('INSERT INTO follows (user_id, artist_id) VALUES (?, ?)');
                $insert->execute([$userId, $artistId]);
                $update = $connection->prepare('UPDATE artists SET follower_count = follower_count + 1 WHERE id = ?');
                $update->execute([$artistId]);
            }

            $connection->commit();
            return !$following;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }
}

