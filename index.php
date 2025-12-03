<?php
// index.php
$page_title = 'Dashboard Meteorológico';
include 'header.php';
?>

<!-- Hero Section -->
<section class="hero-section" data-aos="fade-in">
    <div class="hero-background">
        <canvas id="weather-canvas"></canvas>
    </div>
    <div class="container position-relative z-3">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="hero-content animate__animated animate__fadeInUp">
                    <h1 class="display-3 fw-bold mb-4 text-shadow">
                        <i class="fas fa-cloud-sun-rain me-3"></i>
                        WeatherMaster Pro
                    </h1>
                    <p class="lead fs-4 mb-5 opacity-90">
                        Sistema avançado de previsão meteorológica em tempo real<br>
                        <span class="text-warning">Dados precisos da API Open-Meteo</span>
                    </p>
                    
                    <!-- Weather Stats Bar -->
                    <div class="weather-stats-bar" data-aos="zoom-in" data-aos-delay="300">
                        <div class="row g-4">
                            <div class="col-md-3 col-6">
                                <div class="stat-card">
                                    <i class="fas fa-satellite"></i>
                                    <h3 class="stat-number" data-count="1500">0</h3>
                                    <p class="stat-label">Estações Ativas</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-card">
                                    <i class="fas fa-globe-americas"></i>
                                    <h3 class="stat-number" data-count="120">0</h3>
                                    <p class="stat-label">Países Cobertos</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-card">
                                    <i class="fas fa-history"></i>
                                    <h3 class="stat-number" data-count="24">0</h3>
                                    <p class="stat-label">Atualizações/Hora</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-card">
                                    <i class="fas fa-bullseye"></i>
                                    <h3 class="stat-number" data-count="99.8">0</h3>
                                    <p class="stat-label">% Precisão</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Dashboard -->
<div class="container-fluid dashboard-container py-5">
    <div class="row g-4">
        
        <!-- Left Column -->
        <div class="col-lg-8">
            
            <!-- Current Weather Card -->
            <div class="card dashboard-card animate__animated animate__fadeInLeft" id="current-weather">
                <div class="card-header bg-gradient-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-temperature-half me-2"></i>
                            Condições Atuais
                        </h3>
                        <div class="location-selector">
                            <select class="form-select form-select-sm bg-dark text-white" 
                                    id="location-select" style="width: 250px;">
                                <option value="">Selecione uma localização...</option>
                                <option value="auto">Minha Localização</option>
                                <optgroup label="Cidades Populares">
                                    <option value="-23.5505,-46.6333">São Paulo, Brasil</option>
                                    <option value="-22.9068,-43.1729">Rio de Janeiro, Brasil</option>
                                    <option value="-15.7801,-47.9292">Brasília, Brasil</option>
                                    <option value="40.7128,-74.0060">Nova York, EUA</option>
                                    <option value="51.5074,-0.1278">Londres, UK</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="current-weather-display">
                                <div class="weather-icon-large" id="current-weather-icon">
    <i class="fas fa-cloud-sun fa-3x text-info"></i>
</div>
                                <div class="temperature-display">
                                    <h1 class="display-1 fw-bold" id="current-temperature">--°</h1>
                                    <div class="weather-meta">
                                        <span class="feels-like" id="feels-like">Sensação: --°</span>
                                        <span class="weather-desc" id="weather-description">Carregando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="weather-detail">
                                        <i class="fas fa-wind"></i>
                                        <div>
                                            <small>Vento</small>
                                            <h5 id="wind-speed">-- km/h</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="weather-detail">
                                        <i class="fas fa-tint"></i>
                                        <div>
                                            <small>Umidade</small>
                                            <h5 id="humidity">--%</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="weather-detail">
                                        <i class="fas fa-compass"></i>
                                        <div>
                                            <small>Pressão</small>
                                            <h5 id="pressure">1013 hPa</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="weather-detail">
                                        <i class="fas fa-eye"></i>
                                        <div>
                                            <small>Visibilidade</small>
                                            <h5 id="visibility">10 km</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="weather-detail">
                                        <i class="fas fa-cloud-rain"></i>
                                        <div>
                                            <small>Chuva</small>
                                            <h5 id="precipitation">--%</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="weather-detail">
                                        <i class="fas fa-sun"></i>
                                        <div>
                                            <small>Índice UV</small>
                                            <h5 id="uv-index">5 Moderado</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weather Alert -->
                    <div class="weather-alert mt-4" id="weather-alert" style="display: none;">
                        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-1" id="alert-title">Alerta Meteorológico</h5>
                                <p class="mb-0" id="alert-description">Tempestade se aproximando da área.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hourly Forecast -->
            <div class="card dashboard-card mt-4 animate__animated animate__fadeInLeft" id="hourly-forecast">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Previsão Horária - Próximas 12 horas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="hourly-forecast-slider" id="hourly-slider">
                        <!-- JavaScript will populate this -->
                    </div>
                    <div class="chart-container mt-4">
                        <canvas id="temperature-chart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Daily Forecast -->
            <div class="card dashboard-card mt-4 animate__animated animate__fadeInLeft" id="daily-forecast">
                <div class="card-header bg-gradient-success">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Previsão para os Próximos 7 Dias
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover weather-table" id="daily-forecast-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Condição</th>
                                    <th>Máx</th>
                                    <th>Mín</th>
                                    <th>Chuva</th>
                                    <th>Vento</th>
                                    <th>Nascer do Sol</th>
                                    <th>Pôr do Sol</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- JavaScript will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4">
            
            <!-- Location Info -->
            <div class="card dashboard-card animate__animated animate__fadeInRight" id="location-info">
                <div class="card-header bg-gradient-secondary">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Informações da Localização
                    </h3>
                </div>
                <div class="card-body">
                    <div class="location-details">
                        <div class="location-map mb-4">
                            <div id="location-map" style="height: 200px; border-radius: 10px;"></div>
                        </div>
                        <div class="location-data">
                            <div class="location-item">
                                <i class="fas fa-city"></i>
                                <div>
                                    <small>Cidade</small>
                                    <h5 id="city-name"><?php echo DEFAULT_CITY; ?></h5>
                                </div>
                            </div>
                            <div class="location-item">
                                <i class="fas fa-globe-americas"></i>
                                <div>
                                    <small>País</small>
                                    <h5 id="country">Brasil</h5>
                                </div>
                            </div>
                            <div class="location-item">
                                <i class="fas fa-ruler-combined"></i>
                                <div>
                                    <small>Coordenadas</small>
                                    <h5 id="coordinates"><?php echo DEFAULT_LAT; ?>°, <?php echo DEFAULT_LON; ?>°</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card dashboard-card mt-4 animate__animated animate__fadeInRight" id="quick-stats">
                <div class="card-header bg-gradient-dark">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Estatísticas Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="quick-stats-grid">
                        <div class="quick-stat">
                            <i class="fas fa-temperature-high"></i>
                            <div>
                                <small>Temperatura Máxima</small>
                                <h4 id="max-temp-today">--°C</h4>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-temperature-low"></i>
                            <div>
                                <small>Temperatura Mínima</small>
                                <h4 id="min-temp-today">--°C</h4>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-cloud-sun-rain"></i>
                            <div>
                                <small>Precipitação Total</small>
                                <h4 id="total-precipitation">-- mm</h4>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-sun"></i>
                            <div>
                                <small>Horas de Sol</small>
                                <h4 id="sun-hours">-- h</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Weather History -->
            <div class="card dashboard-card mt-4 animate__animated animate__fadeInRight" id="weather-history">
                <div class="card-header bg-gradient-warning">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Histórico Meteorológico
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="history-controls">
                                <div class="mb-3">
                                    <label class="form-label">Período</label>
                                    <select class="form-select" id="history-period">
                                        <option value="7">Últimos 7 dias</option>
                                        <option value="30">Últimos 30 dias</option>
                                        <option value="90">Últimos 3 meses</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Métrica</label>
                                    <select class="form-select" id="history-metric">
                                        <option value="temperature">Temperatura</option>
                                        <option value="precipitation">Precipitação</option>
                                        <option value="humidity">Umidade</option>
                                        <option value="wind">Velocidade do Vento</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100" onclick="loadHistory()">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Gerar Gráfico
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<section class="features-section py-5" id="features">
    <div class="container">
        <h2 class="section-title text-center mb-5" data-aos="fade-up">
            <i class="fas fa-star me-2"></i>
            Recursos Avançados
        </h2>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h4>IA Meteorológica</h4>
                    <p>Previsões aprimoradas por inteligência artificial com precisão de 95%.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4>Alertas Inteligentes</h4>
                    <p>Notificações em tempo real para eventos meteorológicos severos.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4>App Mobile</h4>
                    <p>Aplicativo nativo para iOS e Android com todas as funcionalidades.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<!-- External Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Main Script -->
<script src="script.js"></script>

<!-- Weather Canvas Script -->
<script>
// Weather canvas animation
function initWeatherCanvas() {
    const canvas = document.getElementById('weather-canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Set canvas size
    function resizeCanvas() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    }
    
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
    
    // Simple particles
    const particles = [];
    const particleCount = 30;
    
    class Particle {
        constructor() {
            this.reset();
        }
        
        reset() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2 + 1;
            this.speedX = Math.random() * 0.5 - 0.25;
            this.speedY = Math.random() * 0.5 - 0.25;
            this.color = `rgba(255, 255, 255, ${Math.random() * 0.1})`;
        }
        
        update() {
            this.x += this.speedX;
            this.y += this.speedY;
            
            if (this.x > canvas.width) this.x = 0;
            if (this.x < 0) this.x = canvas.width;
            if (this.y > canvas.height) this.y = 0;
            if (this.y < 0) this.y = canvas.height;
        }
        
        draw() {
            ctx.fillStyle = this.color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }
    
    // Create particles
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }
    
    // Animation loop
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Gradient background
        const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.05)');
        gradient.addColorStop(1, 'rgba(118, 75, 162, 0.05)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Update and draw particles
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        requestAnimationFrame(animate);
    }
    
    animate();
}

// Initialize weather canvas when page loads
window.addEventListener('load', function() {
    initWeatherCanvas();
});
</script>