<?php
// api/weather.php - Endpoint da API de clima
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurações
$CACHE_DIR = __DIR__ . '/../cache/';
$CACHE_DURATION = 300; // 5 minutos

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
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
$units = isset($_GET['units']) ? $_GET['units'] : 'metric';
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'pt';

// Validar coordenadas
if ($lat === null || $lon === null || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    http_response_code(400);
    echo json_encode(['error' => 'Coordenadas inválidas']);
    exit();
}

// Validar unidades
$validUnits = ['metric', 'imperial'];
if (!in_array($units, $validUnits)) {
    $units = 'metric';
}

// Validar idioma
$validLangs = ['pt', 'en', 'es', 'fr', 'de'];
if (!in_array($lang, $validLangs)) {
    $lang = 'pt';
}

// Gerar chave de cache
$cacheKey = sprintf('weather_%.4f_%.4f_%s_%s', $lat, $lon, $units, $lang);
$cacheFile = $CACHE_DIR . md5($cacheKey) . '.json';

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
    // Construir URL da API Open-Meteo
    $baseUrl = 'https://api.open-meteo.com/v1/forecast';
    
    $params = [
        'latitude' => $lat,
        'longitude' => $lon,
        'hourly' => 'temperature_2m,relative_humidity_2m,precipitation_probability,weather_code,wind_speed_10m,wind_direction_10m',
        'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max',
        'current_weather' => 'true',
        'timezone' => 'auto',
        'forecast_days' => 7,
        'timeformat' => 'unixtime'
    ];
    
    // Adicionar parâmetros de unidade
    if ($units === 'imperial') {
        $params['temperature_unit'] = 'fahrenheit';
        $params['wind_speed_unit'] = 'mph';
        $params['precipitation_unit'] = 'inch';
    }
    
    // Construir query string
    $queryString = http_build_query($params);
    $url = $baseUrl . '?' . $queryString;
    
    // Configurar contexto para requisição
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "User-Agent: WeatherSystem/1.0\r\n"
        ]
    ]);
    
    // Fazer requisição
    $response = @file_get_contents($url, false, $context);
    
    if ($response === FALSE) {
        throw new Exception('Erro ao conectar com a API de clima');
    }
    
    $data = json_decode($response, true);
    
    if (!$data) {
        throw new Exception('Resposta inválida da API');
    }
    
    // Processar dados
    $processedData = $this->processWeatherData($data, $units, $lang);
    
    // Adicionar metadados
    $processedData['metadata'] = [
        'latitude' => $lat,
        'longitude' => $lon,
        'units' => $units,
        'language' => $lang,
        'timestamp' => time(),
        'source' => 'Open-Meteo API',
        'generated_at' => date('c')
    ];
    
    // Salvar em cache
    $cacheData = $processedData;
    $cacheData['timestamp'] = time();
    file_put_contents($cacheFile, json_encode($cacheData));
    
    // Adicionar flag de cache
    $processedData['cached'] = false;
    
    echo json_encode($processedData);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'timestamp' => time()
    ]);
}

function processWeatherData($data, $units, $lang) {
    $processed = $data;
    
    // Processar dados atuais
    if (isset($data['current_weather'])) {
        $current = $data['current_weather'];
        
        // Converter temperatura se necessário
        if ($units === 'imperial') {
            $current['temperature'] = round(($current['temperature'] * 9/5) + 32, 1);
        }
        
        // Adicionar descrição do tempo
        $current['weather_description'] = $this->getWeatherDescription($current['weathercode'], $lang);
        $current['weather_icon'] = $this->getWeatherIcon($current['weathercode']);
        $current['wind_direction_text'] = $this->getWindDirection($current['winddirection']);
        
        $processed['current_weather'] = $current;
    }
    
    // Processar dados horários
    if (isset($data['hourly'])) {
        $hourly = $data['hourly'];
        
        // Converter unidades se necessário
        if ($units === 'imperial') {
            if (isset($hourly['temperature_2m'])) {
                $hourly['temperature_2m'] = array_map(function($temp) {
                    return round(($temp * 9/5) + 32, 1);
                }, $hourly['temperature_2m']);
            }
            
            if (isset($hourly['wind_speed_10m'])) {
                $hourly['wind_speed_10m'] = array_map(function($speed) {
                    return round($speed * 0.621371, 1);
                }, $hourly['wind_speed_10m']);
            }
        }
        
        // Adicionar descrições do tempo
        if (isset($hourly['weather_code'])) {
            $hourly['weather_description'] = array_map(function($code) use ($lang) {
                return $this->getWeatherDescription($code, $lang);
            }, $hourly['weather_code']);
            
            $hourly['weather_icon'] = array_map(function($code) {
                return $this->getWeatherIcon($code);
            }, $hourly['weather_code']);
        }
        
        $processed['hourly'] = $hourly;
    }
    
    // Processar dados diários
    if (isset($data['daily'])) {
        $daily = $data['daily'];
        
        // Converter unidades se necessário
        if ($units === 'imperial') {
            if (isset($daily['temperature_2m_max'])) {
                $daily['temperature_2m_max'] = array_map(function($temp) {
                    return round(($temp * 9/5) + 32, 1);
                }, $daily['temperature_2m_max']);
            }
            
            if (isset($daily['temperature_2m_min'])) {
                $daily['temperature_2m_min'] = array_map(function($temp) {
                    return round(($temp * 9/5) + 32, 1);
                }, $daily['temperature_2m_min']);
            }
            
            if (isset($daily['precipitation_sum'])) {
                $daily['precipitation_sum'] = array_map(function($precip) {
                    return round($precip * 0.0393701, 2);
                }, $daily['precipitation_sum']);
            }
            
            if (isset($daily['wind_speed_10m_max'])) {
                $daily['wind_speed_10m_max'] = array_map(function($speed) {
                    return round($speed * 0.621371, 1);
                }, $daily['wind_speed_10m_max']);
            }
        }
        
        // Adicionar descrições do tempo
        if (isset($daily['weather_code'])) {
            $daily['weather_description'] = array_map(function($code) use ($lang) {
                return $this->getWeatherDescription($code, $lang);
            }, $daily['weather_code']);
            
            $daily['weather_icon'] = array_map(function($code) {
                return $this->getWeatherIcon($code);
            }, $daily['weather_code']);
            
            // Adicionar nomes dos dias
            $daily['day_names'] = array_map(function($timestamp) use ($lang) {
                $date = new DateTime('@' . $timestamp);
                $formatter = new IntlDateFormatter(
                    $lang,
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::FULL,
                    null,
                    null,
                    'EEEE'
                );
                return $formatter->format($date);
            }, $daily['time']);
        }
        
        $processed['daily'] = $daily;
    }
    
    // Calcular estatísticas
    if (isset($data['hourly']['temperature_2m'])) {
        $temps = $data['hourly']['temperature_2m'];
        $processed['statistics'] = [
            'temperature_avg' => round(array_sum($temps) / count($temps), 1),
            'temperature_max' => round(max($temps), 1),
            'temperature_min' => round(min($temps), 1),
            'temperature_range' => round(max($temps) - min($temps), 1)
        ];
        
        if ($units === 'imperial') {
            foreach ($processed['statistics'] as &$value) {
                $value = round(($value * 9/5) + 32, 1);
            }
        }
    }
    
    return $processed;
}

function getWeatherDescription($code, $lang = 'pt') {
    $descriptions = [
        'pt' => [
            0 => 'Céu limpo',
            1 => 'Principalmente limpo',
            2 => 'Parcialmente nublado',
            3 => 'Nublado',
            45 => 'Nevoeiro',
            48 => 'Nevoeiro com geada',
            51 => 'Chuvisco leve',
            53 => 'Chuvisco moderado',
            55 => 'Chuvisco denso',
            61 => 'Chuva leve',
            63 => 'Chuva moderada',
            65 => 'Chuva forte',
            71 => 'Queda de neve leve',
            73 => 'Queda de neve moderada',
            75 => 'Queda de neve forte',
            77 => 'Grãos de neve',
            80 => 'Pancadas de chuva leves',
            81 => 'Pancadas de chuva moderadas',
            82 => 'Pancadas de chuva violentas',
            85 => 'Pancadas de neve leves',
            86 => 'Pancadas de neve fortes',
            95 => 'Tempestade',
            96 => 'Tempestade com granizo leve',
            99 => 'Tempestade com granizo forte'
        ],
        'en' => [
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45 => 'Fog',
            48 => 'Depositing rime fog',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            61 => 'Slight rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            71 => 'Slight snow fall',
            73 => 'Moderate snow fall',
            75 => 'Heavy snow fall',
            77 => 'Snow grains',
            80 => 'Slight rain showers',
            81 => 'Moderate rain showers',
            82 => 'Violent rain showers',
            85 => 'Slight snow showers',
            86 => 'Heavy snow showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with slight hail',
            99 => 'Thunderstorm with heavy hail'
        ],
        'es' => [
            0 => 'Cielo despejado',
            1 => 'Principalmente despejado',
            2 => 'Parcialmente nublado',
            3 => 'Nublado',
            45 => 'Niebla',
            48 => 'Niebla con escarcha',
            51 => 'Llovizna ligera',
            53 => 'Llovizna moderada',
            55 => 'Llovizna densa',
            61 => 'Lluvia ligera',
            63 => 'Lluvia moderada',
            65 => 'Lluvia fuerte',
            71 => 'Nevada ligera',
            73 => 'Nevada moderada',
            75 => 'Nevada fuerte',
            77 => 'Granos de nieve',
            80 => 'Chubascos ligeros',
            81 => 'Chubascos moderados',
            82 => 'Chubascos fuertes',
            85 => 'Chubascos de nieve ligeros',
            86 => 'Chubascos de nieve fuertes',
            95 => 'Tormenta',
            96 => 'Tormenta con granizo ligero',
            99 => 'Tormenta con granizo fuerte'
        ]
    ];
    
    return $descriptions[$lang][$code] ?? 'Unknown condition';
}

function getWeatherIcon($code) {
    $icons = [
        0 => 'fas fa-sun',
        1 => 'fas fa-cloud-sun',
        2 => 'fas fa-cloud-sun',
        3 => 'fas fa-cloud',
        45 => 'fas fa-smog',
        48 => 'fas fa-smog',
        51 => 'fas fa-cloud-rain',
        53 => 'fas fa-cloud-rain',
        55 => 'fas fa-cloud-rain',
        61 => 'fas fa-cloud-showers-heavy',
        63 => 'fas fa-cloud-showers-heavy',
        65 => 'fas fa-cloud-showers-heavy',
        71 => 'fas fa-snowflake',
        73 => 'fas fa-snowflake',
        75 => 'fas fa-snowflake',
        77 => 'fas fa-snowflake',
        80 => 'fas fa-cloud-showers-heavy',
        81 => 'fas fa-cloud-showers-heavy',
        82 => 'fas fa-cloud-showers-heavy',
        85 => 'fas fa-snowflake',
        86 => 'fas fa-snowflake',
        95 => 'fas fa-bolt',
        96 => 'fas fa-bolt',
        99 => 'fas fa-bolt'
    ];
    
    return $icons[$code] ?? 'fas fa-question-circle';
}

function getWindDirection($degrees) {
    $directions = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'];
    $index = round(($degrees % 360) / 45);
    return $directions[$index % 8];
}
?>