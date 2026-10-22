<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireRole(['artist', 'admin']);

$artists = CatalogRepository::artists();
$genres = CatalogRepository::genres();
$pageTitle = 'Upload track';
$pageDescription = 'Add a new track to the PulseWave catalog.';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="form-page section-shell">
    <div class="form-intro">
        <span class="eyebrow">Creator studio</span>
        <h1>Release something new</h1>
        <p>Add the track, artwork and release details. We handle the technical details so you can focus on the sound.</p>
        <ul class="check-list">
            <li>MP3, WAV, FLAC, OGG or OPUS audio</li>
            <li>JPG, PNG or WebP artwork</li>
            <li>Maximum sizes follow your environment configuration</li>
        </ul>
    </div>
    <form class="panel stack-form upload-form" method="post" action="<?= e(url('actions/upload.php')) ?>" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <div class="two-column-fields">
            <label><span>Track title</span><input type="text" name="title" maxlength="150" required></label>
            <label>
                <span>Artist</span>
                <select name="artist_id" required>
                    <?php foreach ($artists as $artist): ?>
                        <option value="<?= (int) $artist['id'] ?>"><?= e($artist['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Genre</span>
                <select name="genre_id" required>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?= (int) $genre['id'] ?>"><?= e($genre['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><span>Release year</span><input type="number" name="release_year" min="1900" max="<?= date('Y') + 1 ?>" value="<?= date('Y') ?>"></label>
        </div>
        <label class="file-drop">
            <span><?= icon('music') ?> Audio file</span>
            <input type="file" name="audio_file" accept=".mp3,.wav,.flac,.ogg,.opus,audio/*" required>
            <small data-file-name>Choose an audio file</small>
        </label>
        <label class="file-drop">
            <span><?= icon('upload') ?> Cover artwork</span>
            <input type="file" name="cover_file" accept=".jpg,.jpeg,.png,.webp,image/*">
            <small data-file-name>Optional — default artwork will be used</small>
        </label>
        <button class="button button-primary" type="submit"><?= icon('upload') ?> Publish release</button>
    </form>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

