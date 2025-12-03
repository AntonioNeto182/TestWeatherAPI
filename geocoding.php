<?php
// api/geocoding.php - Endpoint de geocodificação
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurações
$CACHE_DIR = __DIR__ . '/../cache/';
$CACHE_DURATION = 86400; // 24 horas

// Criar diretório de cache se não existir
if (!is_dir($CACHE_DIR)) {
    mkdir($CACHE_DIR, 0755, true);
}

// Lidar com requisições OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Validar e sanitizar parâmetros
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'pt';

// Validar parâmetros
if (empty($query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Consulta vazia']);
    exit();
}

if ($limit < 1 || $limit > 20) {
    $limit = 5;
}

$validLangs = ['pt', 'en', 'es', 'fr', 'de'];
if (!in_array($lang, $validLangs)) {
    $lang = 'pt';
}

// Gerar chave de cache
$cacheKey = sprintf('geocode_%s_%d_%s', md5($query), $limit, $lang);
$cacheFile = $CACHE_DIR . $cacheKey . '.json';

// Verificar cache
if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    
    if ($cacheData && isset($cacheData['timestamp'])) {
        $cacheAge = time() - $cacheData['timestamp'];
        
        if ($cacheAge < $CACHE_DURATION) {
            // Retornar dados do cache
            $cacheData['cached'] = true;
            $cacheData['cache_age'] = $cacheAge;
            echo json_encode($cacheData);
            exit();
        }
    }
}

try {
    // Construir URL da API de geocodificação
    $baseUrl = 'https://geocoding-api.open-meteo.com/v1/search';
    
    $params = [
        'name' => $query,
        'count' => $limit,
        'language' => $lang,
        'format' => 'json'
    ];
}
    $queryString = http_build_query