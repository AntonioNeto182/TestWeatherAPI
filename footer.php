<!-- footer.php -->
</main> <!-- Fecha a tag main aberta no header -->

<footer class="footer py-4 mt-5 border-top">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0 text-muted">
                    &copy; <?php echo date('Y'); ?> WeatherMaster Pro. Sistema de previsão meteorológica.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted">
                    Dados fornecidos por <a href="https://open-meteo.com/" target="_blank" class="text-decoration-none">Open-Meteo API</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Scripts do Sistema -->
<script src="assets/js/script.js"></script>
<script src="assets/js/weather-charts.js"></script>
<script src="assets/js/geolocation.js"></script>

<script>
    // Inicializar AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }
</script>

</body>
</html>