<?php
// navbar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-xl navbar-dark fixed-top" id="main-navbar">
    <div class="container-fluid">
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center" href="index.php" data-aos="fade-right">
            <div class="brand-icon">
                <i class="fas fa-cloud-sun-rain fa-2x"></i>
            </div>
            <div class="brand-text ms-3">
                <h1 class="mb-0 fs-4 fw-bold">WeatherMaster Pro</h1>
                <small class="d-block opacity-75">Sistema Meteorológico</small>
            </div>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarContent" aria-controls="navbarContent" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            <span class="badge bg-danger notification-badge" id="mobile-notification-badge">3</span>
        </button>
        
        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Left Menu -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0" data-aos="fade-down">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                       href="index.php">
                        <i class="fas fa-home me-2"></i>
                        <span>Início</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="weatherDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cloud-sun me-2"></i>
                        <span>Clima</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="weatherDropdown">
                        <li>
                            <a class="dropdown-item" href="#current-weather">
                                <i class="fas fa-temperature-half me-2"></i>Temperatura Atual
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#hourly-forecast">
                                <i class="fas fa-clock me-2"></i>Previsão Horária
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#daily-forecast">
                                <i class="fas fa-calendar-day me-2"></i>Previsão Diária
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="nav-item">
    <a class="nav-link <?php echo $current_page == 'mapas.php' ? 'active' : ''; ?>" 
       href="mapas.php">
        <i class="fas fa-map me-2"></i>
        <span>Mapas</span>
    </a>
</li>
                            <li class="nav-item">
    <a class="nav-link <?php echo $current_page == 'radar.php' ? 'active' : ''; ?>" 
       href="radar.php">
        <i class="fas fa-satellite-dish me-2"></i>
        <span>Radar</span>
    </a>
</li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="mapsDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        <span>Mapas</span>
                        <span class="badge bg-success ms-2">Novo</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="mapsDropdown">
                        <li>
                            <a class="dropdown-item" href="#temperature-map">
                                <i class="fas fa-thermometer-half me-2"></i>Mapa de Temperatura
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#precipitation-map">
                                <i class="fas fa-cloud-rain me-2"></i>Mapa de Precipitação
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#wind-map">
                                <i class="fas fa-wind me-2"></i>Mapa de Ventos
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#satellite-map">
                                <i class="fas fa-satellite me-2"></i>Imagem de Satélite
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="toolsDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-tools me-2"></i>
                        <span>Ferramentas</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="toolsDropdown">
                        <li>
                            <a class="dropdown-item" href="#unit-converter">
                                <i class="fas fa-exchange-alt me-2"></i>Conversor de Unidades
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#weather-alerts">
                                <i class="fas fa-bell me-2"></i>Alertas Meteorológicos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#uv-index">
                                <i class="fas fa-sun me-2"></i>Índice UV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#air-quality">
                                <i class="fas fa-wind me-2"></i>Qualidade do Ar
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
    <a class="nav-link <?php echo $current_page == 'sobre.php' ? 'active' : ''; ?>" 
       href="sobre.php">
        <i class="fas fa-info-circle me-2"></i>
        <span>Sobre</span>
    </a>
</li>
            </ul>
            
            <!-- Search Form -->
            <form class="d-flex search-form mx-3" id="global-search-form" data-aos="fade-down">
                <div class="input-group search-group">
                    <span class="input-group-text bg-dark border-dark">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control bg-dark border-dark text-white" 
                           id="global-search" placeholder="Buscar cidade ou localização..." 
                           aria-label="Buscar cidade" autocomplete="off">
                    <button class="btn btn-primary" type="submit" id="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="search-results" id="search-results"></div>
            </form>
            
            <!-- Right Menu -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0" data-aos="fade-left">
                <!-- Quick Actions -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="quickActionsDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bolt"></i>
                        <span class="d-none d-xl-inline">Ações Rápidas</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" 
                        aria-labelledby="quickActionsDropdown">
                        <li>
                            <a class="dropdown-item" href="#" onclick="getCurrentLocation()">
                                <i class="fas fa-location-arrow me-2"></i>Minha Localização
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="refreshWeatherData()">
                                <i class="fas fa-sync-alt me-2"></i>Atualizar Dados
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="shareWeather()">
                                <i class="fas fa-share-alt me-2"></i>Compartilhar Clima
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="printWeatherReport()">
                                <i class="fas fa-print me-2"></i>Imprimir Relatório
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="exportWeatherData()">
                                <i class="fas fa-download me-2"></i>Exportar Dados
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notificationsDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="d-none d-xl-inline">Notificações</span>
                        <span class="badge bg-danger notification-badge" id="notification-badge">3</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end notification-dropdown" 
                        aria-labelledby="notificationsDropdown">
                        <li class="dropdown-header">
                            <h6 class="mb-0">Notificações Meteorológicas</h6>
                            <small class="text-muted">Alertas ativos</small>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="notification-item">
                                    <div class="notification-icon bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6>Tempestade Próxima</h6>
                                        <small>Área: São Paulo - Atualizado há 15min</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="notification-item">
                                    <div class="notification-icon bg-info">
                                        <i class="fas fa-temperature-high"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6>Alerta de Calor</h6>
                                        <small>Temperatura acima de 35°C - Atualizado há 1h</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="notification-item">
                                    <div class="notification-icon bg-danger">
                                        <i class="fas fa-wind"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6>Ventos Fortes</h6>
                                        <small>Velocidade acima de 60km/h - Atualizado há 2h</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center" href="#">
                                Ver todas as notificações
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- User Settings -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                       id="userSettingsDropdown" role="button" data-bs-toggle="dropdown" 
                       aria-expanded="false">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </div>
                        <span class="d-none d-xl-inline ms-2">Configurações</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" 
                        aria-labelledby="userSettingsDropdown">
                        <li class="dropdown-header">
                            <h6 class="mb-0">Preferências</h6>
                            <small class="text-muted">Personalize sua experiência</small>
                        </li>
                        <li>
                            <div class="dropdown-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           id="darkModeSwitch" <?php echo $_SESSION['user_settings']['theme'] == 'dark' ? 'checked' : ''; ?> 
                                           onchange="toggleDarkMode()">
                                    <label class="form-check-label" for="darkModeSwitch">
                                        Modo Escuro
                                    </label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item">
                                <label class="form-label small d-block mb-1">Unidades</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="units" 
                                           id="units-metric" autocomplete="off" 
                                           <?php echo $_SESSION['user_settings']['units'] == 'metric' ? 'checked' : ''; ?> 
                                           onchange="changeUnits('metric')">
                                    <label class="btn btn-outline-primary btn-sm" for="units-metric">
                                        Métrico
                                    </label>
                                    <input type="radio" class="btn-check" name="units" 
                                           id="units-imperial" autocomplete="off" 
                                           <?php echo $_SESSION['user_settings']['units'] == 'imperial' ? 'checked' : ''; ?> 
                                           onchange="changeUnits('imperial')">
                                    <label class="btn btn-outline-primary btn-sm" for="units-imperial">
                                        Imperial
                                    </label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item">
                                <label class="form-label small d-block mb-1">Idioma</label>
                                <select class="form-select form-select-sm bg-dark text-white" 
                                        id="languageSelect" onchange="changeLanguage(this.value)">
                                    <option value="pt" <?php echo $_SESSION['user_settings']['language'] == 'pt' ? 'selected' : ''; ?>>Português</option>
                                    <option value="en" <?php echo $_SESSION['user_settings']['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="es" <?php echo $_SESSION['user_settings']['language'] == 'es' ? 'selected' : ''; ?>>Español</option>
                                </select>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2"></i>Configurações Avançadas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-question-circle me-2"></i>Ajuda & Suporte
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-sign-out-alt me-2"></i>Sair do Sistema
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Fullscreen Toggle -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="toggleFullscreen()" 
                       title="Tela Cheia">
                        <i class="fas fa-expand"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="breadcrumb-nav" data-aos="fade-down">
    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="#">Clima</a></li>
            <li class="breadcrumb-item active" aria-current="page">Previsão Atual</li>
        </ol>
        
        <!-- Weather Summary Bar -->
        <div class="weather-summary-bar" id="weather-summary-bar">
            <div class="weather-summary-item">
                <i class="fas fa-temperature-half"></i>
                <span>Atual: <strong id="current-temp-summary">22°C</strong></span>
            </div>
            <div class="weather-summary-item">
                <i class="fas fa-wind"></i>
                <span>Vento: <strong id="current-wind-summary">12 km/h</strong></span>
            </div>
            <div class="weather-summary-item">
                <i class="fas fa-tint"></i>
                <span>Umidade: <strong id="current-humidity-summary">65%</strong></span>
            </div>
            <div class="weather-summary-item">
                <i class="fas fa-cloud-rain"></i>
                <span>Chuva: <strong id="current-rain-summary">10%</strong></span>
            </div>
        </div>
    </div>
</nav>

<!-- Floating Action Button -->
<button class="btn btn-primary btn-floating" id="floating-action-btn" 
        onclick="scrollToTop()" title="Voltar ao topo">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
/* Navbar Styles */
#main-navbar {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.95) 0%, rgba(52, 152, 219, 0.95) 100%);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
    padding: 0.5rem 0;
    transition: all var(--transition-speed) ease;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1030;
}

#main-navbar.scrolled {
    padding: 0.3rem 0;
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.98) 0%, rgba(52, 152, 219, 0.98) 100%);
    box-shadow: 0 6px 40px rgba(0, 0, 0, 0.4);
}

.brand-icon {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}

.brand-icon:hover {
    transform: rotate(15deg);
}

.brand-icon i {
    background: linear-gradient(45deg, #FFD700, #FFA500);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.brand-text h1 {
    background: linear-gradient(45deg, #fff, #e0e0e0);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.nav-link {
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    border-radius: 8px;
    margin: 0 2px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.5s ease;
}

.nav-link:hover::before {
    left: 100%;
}

.nav-link.active {
    background: rgba(255, 255, 255, 0.15);
    color: #FFD700 !important;
    box-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
}

.nav-link i {
    transition: transform 0.3s ease;
}

.nav-link:hover i {
    transform: scale(1.2);
}

.dropdown-menu {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.98) 0%, rgba(52, 152, 219, 0.98) 100%);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    padding: 0.5rem;
    min-width: 250px;
    animation: dropdownFadeIn 0.3s ease;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin: 2px 0;
    transition: all 0.3s ease;
    color: #e0e0e0 !important;
}

.dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff !important;
    transform: translateX(5px);
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Search Form */
.search-form {
    position: relative;
    min-width: 300px;
}

.search-group {
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.search-group .form-control {
    border: none;
    background: rgba(0, 0, 0, 0.3) !important;
    color: white !important;
    padding-left: 0;
}

.search-group .form-control:focus {
    box-shadow: none;
    border: none;
}

.search-group .input-group-text {
    border: none;
    background: rgba(0, 0, 0, 0.3);
}

.search-group .btn {
    border-radius: 0 25px 25px 0;
    padding: 0.5rem 1.5rem;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.98) 0%, rgba(52, 152, 219, 0.98) 100%);
    backdrop-filter: blur(20px);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 10px;
    padding: 1rem;
    z-index: 1000;
    display: none;
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.search-result-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

/* Notifications */
.notification-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(231, 76, 60, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(231, 76, 60, 0);
    }
}

.notification-dropdown {
    min-width: 350px;
}

.notification-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.notification-content h6 {
    margin: 0;
    font-size: 0.9rem;
}

.notification-content small {
    font-size: 0.8rem;
    opacity: 0.8;
}

/* User Avatar */
.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(45deg, #3498db, #2c3e50);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.user-avatar:hover {
    transform: scale(1.1);
    border-color: rgba(255, 255, 255, 0.4);
}

/* Breadcrumb Navigation */
.breadcrumb-nav {
    background: rgba(0, 0, 0, 0.2);
    padding: 0.75rem 0;
    margin-top: 76px; /* Altura da navbar */
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.breadcrumb {
    margin: 0;
    background: transparent;
}

.breadcrumb-item a {
    color: #aaa;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
    color: #fff;
}

.breadcrumb-item.active {
    color: #FFD700;
}

/* Weather Summary Bar */
.weather-summary-bar {
    display: flex;
    gap: 2rem;
    align-items: center;
    justify-content: flex-end;
}

.weather-summary-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #aaa;
}

.weather-summary-item i {
    color: #3498db;
}

.weather-summary-item strong {
    color: #fff;
    font-weight: 600;
}

/* Floating Action Button */
.btn-floating {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    display: none;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.btn-floating.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive Styles */
@media (max-width: 1200px) {
    .search-form {
        min-width: 250px;
    }
    
    .weather-summary-bar {
        display: none;
    }
}

@media (max-width: 992px) {
    .search-form {
        order: 3;
        width: 100%;
        margin: 1rem 0;
    }
    
    .navbar-nav {
        margin: 1rem 0;
    }
    
    .notification-dropdown {
        min-width: 300px;
    }
}

@media (max-width: 768px) {
    .brand-text h1 {
        font-size: 1.1rem;
    }
    
    .brand-text small {
        font-size: 0.7rem;
    }
    
    .search-form {
        min-width: 100%;
    }
}

/* Animation for navbar on scroll */
#main-navbar {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar for dropdowns */
.search-results::-webkit-scrollbar,
.dropdown-menu::-webkit-scrollbar {
    width: 8px;
}

.search-results::-webkit-scrollbar-track,
.dropdown-menu::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}

.search-results::-webkit-scrollbar-thumb,
.dropdown-menu::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}

.search-results::-webkit-scrollbar-thumb:hover,
.dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>

<script>
// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('main-navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
    
    // Show/hide floating button
    const floatingBtn = document.getElementById('floating-action-btn');
    if (window.scrollY > 300) {
        floatingBtn.classList.add('show');
    } else {
        floatingBtn.classList.remove('show');
    }
});

// Search functionality
const globalSearch = document.getElementById('global-search');
const searchResults = document.getElementById('search-results');
const searchForm = document.getElementById('global-search-form');

globalSearch.addEventListener('input', function(e) {
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    
    // Simulated search results (in production, this would be an API call)
    const cities = [
        { name: 'São Paulo', country: 'Brasil', lat: -23.5505, lon: -46.6333 },
        { name: 'Rio de Janeiro', country: 'Brasil', lat: -22.9068, lon: -43.1729 },
        { name: 'Brasília', country: 'Brasil', lat: -15.7801, lon: -47.9292 },
        { name: 'Nova York', country: 'EUA', lat: 40.7128, lon: -74.0060 },
        { name: 'Londres', country: 'Reino Unido', lat: 51.5074, lon: -0.1278 }
    ];
    
    const filteredCities = cities.filter(city => 
        city.name.toLowerCase().includes(query.toLowerCase()) ||
        city.country.toLowerCase().includes(query.toLowerCase())
    );
    
    if (filteredCities.length > 0) {
        searchResults.innerHTML = filteredCities.map(city => `
            <div class="search-result-item" onclick="selectCity(${city.lat}, ${city.lon}, '${city.name}')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${city.name}</h6>
                        <small class="text-muted">${city.country}</small>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        `).join('');
        searchResults.style.display = 'block';
    } else {
        searchResults.innerHTML = `
            <div class="text-center py-3">
                <i class="fas fa-search fa-2x mb-2 opacity-50"></i>
                <p class="mb-0">Nenhuma cidade encontrada</p>
            </div>
        `;
        searchResults.style.display = 'block';
    }
});

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!searchForm.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

// Theme toggle
function toggleDarkMode() {
    const switchElement = document.getElementById('darkModeSwitch');
    const themeCss = document.getElementById('theme-css');
    const body = document.body;
    
    if (switchElement.checked) {
        body.setAttribute('data-theme', 'dark');
        themeCss.href = 'assets/css/themes/dark.css';
        // Save to session/local storage
        localStorage.setItem('theme', 'dark');
    } else {
        body.setAttribute('data-theme', 'light');
        themeCss.href = 'assets/css/themes/light.css';
        localStorage.setItem('theme', 'light');
    }
}

// Units change
function changeUnits(unit) {
    // Implement unit conversion logic
    console.log('Changing units to:', unit);
    // This would trigger a re-fetch of weather data with new units
}

// Language change
function changeLanguage(lang) {
    console.log('Changing language to:', lang);
    // Implement language switching logic
}

// Other functions
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                // Fetch weather for current location
                fetchWeather(lat, lon, 'Minha Localização');
            },
            error => {
                alert('Não foi possível obter sua localização: ' + error.message);
            }
        );
    } else {
        alert('Geolocalização não é suportada pelo seu navegador.');
    }
}

function refreshWeatherData() {
    // Implement refresh logic
    console.log('Refreshing weather data...');
}

function shareWeather() {
    if (navigator.share) {
        navigator.share({
            title: 'Previsão do Tempo',
            text: 'Confira a previsão do tempo atual!',
            url: window.location.href
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        alert('Compartilhamento não suportado neste navegador.');
    }
}

function printWeatherReport() {
    window.print();
}

function exportWeatherData() {
    // Implement export logic (CSV, JSON, etc.)
    console.log('Exporting weather data...');
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function selectCity(lat, lon, name) {
    // Update search input
    globalSearch.value = name;
    searchResults.style.display = 'none';
    
    // Fetch weather for selected city
    fetchWeather(lat, lon, name);
}

// Remove preload class after page loads
window.addEventListener('load', function() {
    document.body.classList.remove('preload');
});
</script>