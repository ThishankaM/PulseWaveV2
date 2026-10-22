        </main>

        <footer class="site-footer">
            <div class="footer-brand-wrap">
                <a class="brand footer-brand" href="<?= e(url('index.php')) ?>">
                    <?= brand_mark('brand-mark-small') ?>
                    <span>PulseWave</span>
                </a>
                <p>Find your next frequency.</p>
            </div>
            <div class="footer-links">
                <a href="<?= e(url('legal.php?doc=privacy')) ?>">Privacy</a>
                <a href="<?= e(url('legal.php?doc=terms')) ?>">Terms</a>
                <a href="mailto:support@pulsewave.local">Contact</a>
            </div>
            <p class="copyright">&copy; <?= date('Y') ?> PulseWave</p>
        </footer>
    </div>
</div>

<div class="queue-scrim" data-queue-close hidden></div>
<aside class="queue-panel" data-queue-panel aria-label="Playback queue" hidden>
    <div class="queue-heading">
        <div>
            <span class="eyebrow">Now playing</span>
            <h2>Up next</h2>
        </div>
        <button class="icon-button" type="button" data-queue-close aria-label="Close queue"><?= icon('close') ?></button>
    </div>
    <div class="queue-source" data-queue-source>Your listening queue</div>
    <div class="queue-list" data-queue-list>
        <p class="queue-empty">Choose a track to start building your queue.</p>
    </div>
</aside>

<div class="device-popover" data-device-popover hidden>
    <span class="eyebrow">Playback device</span>
    <strong>This browser</strong>
    <small>Audio is currently playing on this device.</small>
</div>

<section class="player-shell" data-player aria-label="Music player">
    <audio data-audio preload="metadata"></audio>

    <div class="now-playing">
        <span class="player-art-wrap">
            <img data-player-cover src="<?= e(media_url(null)) ?>" alt="">
            <span class="playing-bars" aria-hidden="true"><i></i><i></i><i></i></span>
        </span>
        <span class="now-playing-copy">
            <strong data-player-title>Choose a track</strong>
            <span data-player-artist>Your next favorite is close</span>
        </span>
        <?= icon('chevron-down', 'expand-chevron') ?>
    </div>

    <div class="player-center">
        <div class="player-controls">
            <button type="button" class="player-icon" data-shuffle aria-label="Toggle shuffle" aria-pressed="false"><?= icon('shuffle') ?></button>
            <button type="button" class="player-icon" data-previous aria-label="Previous track"><?= icon('previous') ?></button>
            <button type="button" class="player-toggle" data-play-pause aria-label="Play"><?= icon('play') ?></button>
            <button type="button" class="player-icon" data-next aria-label="Next track"><?= icon('next') ?></button>
            <button type="button" class="player-icon" data-repeat aria-label="Toggle repeat" aria-pressed="false"><?= icon('repeat') ?></button>
        </div>
        <div class="timeline-row">
            <span data-current-time>0:00</span>
            <input type="range" min="0" max="100" value="0" step="0.1" data-timeline aria-label="Track position">
            <span data-duration>0:00</span>
        </div>
    </div>

    <div class="player-tools">
        <button type="button" class="player-icon" data-queue-toggle aria-label="Open queue" aria-expanded="false"><?= icon('queue') ?></button>
        <button type="button" class="player-icon" data-device-toggle aria-label="Choose playback device" aria-expanded="false"><?= icon('device') ?></button>
        <div class="volume-control">
            <button type="button" class="player-icon" data-mute aria-label="Mute" aria-pressed="false"><?= icon('volume') ?></button>
            <input type="range" min="0" max="1" value="0.8" step="0.05" data-volume aria-label="Volume">
        </div>
        <button type="button" class="player-icon expand-toggle" data-player-expand aria-label="Expand player"><?= icon('expand') ?></button>
    </div>
</section>

<div class="toast" data-toast role="status" aria-live="polite" hidden></div>
</body>
</html>
