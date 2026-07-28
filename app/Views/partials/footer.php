<?php
declare(strict_types=1);
?>
</main>

<footer class="site-footer">
    <div>
        <a class="brand footer-brand" href="<?= e(url('index.php')) ?>">PulseWave</a>
        <p>A focused music-streaming experience built for discovery.</p>
    </div>
    <div class="footer-links">
        <a href="<?= e(url('legal.php?doc=privacy')) ?>">Privacy</a>
        <a href="<?= e(url('legal.php?doc=terms')) ?>">Terms</a>
        <a href="mailto:support@pulsewave.local">Contact</a>
    </div>
    <p class="copyright">&copy; <?= date('Y') ?> PulseWave</p>
</footer>

<section class="player-shell" data-player aria-label="Music player">
    <audio data-audio preload="metadata"></audio>
    <div class="now-playing">
        <img data-player-cover src="<?= e(media_url(null)) ?>" alt="">
        <div>
            <strong data-player-title>Choose a track</strong>
            <span data-player-artist>PulseWave</span>
        </div>
    </div>
    <div class="player-center">
        <div class="player-controls">
            <button type="button" class="player-icon" data-shuffle aria-label="Toggle shuffle">⌘</button>
            <button type="button" class="player-icon" data-previous aria-label="Previous track">◀</button>
            <button type="button" class="player-toggle" data-play-pause aria-label="Play">▶</button>
            <button type="button" class="player-icon" data-next aria-label="Next track">▶</button>
            <button type="button" class="player-icon" data-repeat aria-label="Toggle repeat">↻</button>
        </div>
        <div class="timeline-row">
            <span data-current-time>0:00</span>
            <input type="range" min="0" max="100" value="0" step="0.1" data-timeline aria-label="Track position">
            <span data-duration>0:00</span>
        </div>
    </div>
    <div class="volume-control">
        <button type="button" class="player-icon" data-mute aria-label="Mute">◕</button>
        <input type="range" min="0" max="1" value="0.8" step="0.05" data-volume aria-label="Volume">
    </div>
</section>

<div class="toast" data-toast role="status" aria-live="polite" hidden></div>
</body>
</html>

