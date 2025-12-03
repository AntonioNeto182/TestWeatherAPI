<?php
// header.php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $_SESSION['user_settings']['theme']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="author" content="WeatherMaster Pro">
    <meta name="keywords" content="clima, previsão, meteorologia, temperatura, chuva, vento, API Open-Meteo">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="generate-favicon.php" type="image/x-icon">
    
    <title><?php echo SITE_NAME . ' | ' . (isset($page_title) ? $page_title : 'Previsão do Tempo'); ?></title>
    
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Theme-specific CSS -->
    <link rel="stylesheet" href="assets/css/themes/<?php echo $_SESSION['user_settings']['theme']; ?>.css" id="theme-css">
    
    <!-- Custom CSS Variables -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(45deg, #3498db, #2c3e50);
            --gradient-warning: linear-gradient(45deg, #f39c12, #e74c3c);
            --shadow-light: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-medium: 0 5px 20px rgba(0,0,0,0.15);
            --shadow-heavy: 0 10px 40px rgba(0,0,0,0.2);
            --border-radius: 12px;
            --transition-speed: 0.3s;
        }
        
        [data-theme="dark"] {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #34495e;
            --dark-color: #ecf0f1;
        }
        
        /* Corrigir ícones do Leaflet */
        .leaflet-default-icon-path {
            background-image: url(https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png);
        }
        .leaflet-default-shadow-path {
            background-image: url(https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png);
        }
    </style>
    
    <script>
        // Definir constantes globais para JavaScript
        const DEFAULT_LAT = <?php echo DEFAULT_LAT; ?>;
        const DEFAULT_LON = <?php echo DEFAULT_LON; ?>;
        const DEFAULT_CITY = "<?php echo DEFAULT_CITY; ?>";
        const SITE_NAME = "<?php echo SITE_NAME; ?>";
        const TIMEZONE = "<?php echo TIMEZONE; ?>";
        const OPEN_METEO_URL = "<?php echo OPEN_METEO_URL; ?>";
        const GEOCODING_URL = "<?php echo GEOCODING_URL; ?>";
        
        // Configurações do usuário
        const USER_SETTINGS = <?php echo json_encode($_SESSION['user_settings'] ?? []); ?>;
    </script>
</head>
<body class="preload">
    <!-- Loading Screen -->
    <div id="loading-screen" class="loading-screen">
        <div class="loading-content">
            <div class="weather-loader">
                <i class="fas fa-cloud-sun fa-spin"></i>
            </div>
            <div class="loading-text">
                <h3>Carregando WeatherMaster Pro</h3>
                <div class="progress" style="height: 4px; width: 200px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'navbar.php'; ?>
    
    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>
    
    <!-- Main Content -->
    <main id="main-content" class="main-content">