<footer class="alt">
    <div class="alt-ic">
        <div class="alt-marka">
            <span class="marka-logo kucuk" aria-hidden="true">
                <svg viewBox="0 0 32 32"><path d="M8 22c0-5 4-5 6-8s1-6 5-6 5 4 3 7-6 3-6 7 3 3 6 3"/></svg>
            </span>
            <div>
                <strong>Harita &amp; Pist Takip Sistemi</strong>
                <p>İnternet Programcılığı I · PHP Proje Ödevi</p>
            </div>
        </div>

        <ul class="teknolojiler" aria-label="Kullanılan teknolojiler">
            <li>PHP 8</li>
            <li>HTML5</li>
            <li>CSS3</li>
            <li>JavaScript</li>
            <li>Leaflet</li>
            <li>OpenStreetMap</li>
        </ul>

        <a class="alt-link" href="https://github.com/muratcankorkmaz41/harita-takip" target="_blank" rel="noopener">
            <i class="bi bi-github" aria-hidden="true"></i> Kaynak kod
        </a>
    </div>
</footer>

<?php if (!empty($haritaGerekli)): ?>
    <script src="assets/vendor/leaflet/leaflet.js"></script>
<?php endif; ?>
<script src="assets/app.js?v=<?php echo filemtime(__DIR__ . '/assets/app.js'); ?>"></script>
</body>
</html>
