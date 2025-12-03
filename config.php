<?php
// config.php
session_start();

// Configurações do site
define('SITE_NAME', 'WeatherMaster Pro');
define('SITE_DESCRIPTION', 'Sistema avançado de previsão meteorológica');
define('SITE_VERSION', '2.0.0');
define('DEFAULT_LAT', -23.5505);
define('DEFAULT_LON', -46.6333);
define('DEFAULT_CITY', 'São Paulo');
define('TIMEZONE', 'America/Sao_Paulo');

// Configurações da API
define('OPEN_METEO_URL', 'https://api.open-meteo.com/v1/forecast');
define('GEOCODING_URL', 'https://geocoding-api.open-meteo.com/v1/search');
define('CACHE_DURATION', 300); // 5 minutos em segundos
define('MAX_API_CALLS_PER_HOUR', 100);

// Configurações de segurança
define('ALLOWED_ORIGINS', ['http://localhost', 'http://127.0.0.1', 'http://localhost/projeto_clima_php']);
define('API_KEY', '');

// Configurações do banco de dados (se necessário)
define('DB_HOST', 'localhost');
define('DB_NAME', 'weather_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações de cache
define('CACHE_ENABLED', true);
define('CACHE_DIR', __DIR__ . '/cache/');

// Configurações de logging
define('LOG_ENABLED', true);
define('LOG_FILE', __DIR__ . '/logs/system.log');

// Configurações de tema
define('THEME', 'dark');
define('DARK_MODE', true);

// Inicializar timezone
date_default_timezone_set(TIMEZONE);

// Headers de segurança
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Função para logging
function log_message($message, $level = 'INFO') {
    if (LOG_ENABLED && defined('LOG_FILE')) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Criar diretório de logs se não existir
        $log_dir = dirname(LOG_FILE);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents(LOG_FILE, $log_entry, FILE_APPEND);
    }
}

// Função para limpar inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Função para validar coordenadas
function validate_coordinates($lat, $lon) {
    if (!is_numeric($lat) || !is_numeric($lon)) {
        return false;
    }
    
    $lat = floatval($lat);
    $lon = floatval($lon);
    
    return ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180);
}

// Inicializar sessão de usuário
if (!isset($_SESSION['user_settings'])) {
    $_SESSION['user_settings'] = [
        'units' => 'metric',
        'language' => 'pt',
        'theme' => THEME,
        'notifications' => true,
        'last_searches' => [],
        'favorites' => []
    ];
}

// Configurar locale
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

// Permitir CORS para desenvolvimento
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], ALLOWED_ORIGINS)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}
?>