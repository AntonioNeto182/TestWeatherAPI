<?php
// api-handler.php - Centraliza todas as chamadas à API
session_start();
require_once 'config.php';
require_once 'weather-functions.php';
require_once 'location-functions.php';

class APIHandler {
    private $cacheEnabled;
    private $cacheDir;
    
    public function __construct() {
        $this->cacheEnabled = CACHE_ENABLED;
        $this->cacheDir = CACHE_DIR;
        
        // Criar diretório de cache se não existir
        if ($this->cacheEnabled && !is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Faz uma requisição HTTP
     */
    private function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        $defaultHeaders = [
            'Accept: application/json',
            'User-Agent: WeatherMasterPro/2.0 (https://github.com/weathermaster)',
            'Content-Type: application/json'
        ];
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => $allHeaders,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            log_message("Erro CURL: $error - URL: $url", 'ERROR');
            throw new Exception("Erro na requisição: $error");
        }
        
        if ($httpCode !== 200) {
            log_message("HTTP $httpCode - URL: $url", 'ERROR');
            throw new Exception("API retornou código $httpCode");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Obtém dados do cache
     */
    private function getFromCache($key) {
        if (!$this->cacheEnabled) return null;
        
        $cacheFile = $this->cacheDir . md5($key) . '.cache';
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            
            if (isset($data['expires']) && $data['expires'] > time()) {
                return $data['data'];
            } else {
                // Cache expirado
                unlink($cacheFile);
            }
        }
        
        return null;
    }
    
    /**
     * Salva dados no cache
     */
    private function saveToCache($key, $data, $ttl = 300) {
        if (!$this->cacheEnabled) return;
        
        $cacheFile = $this->cacheDir . md5($key) . '.cache';
        
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($cacheFile, json_encode($cacheData));
    }
    
    /**
     * Limpa o cache expirado
     */
    private function cleanExpiredCache() {
        if (!$this->cacheEnabled) return;
        
        $files = glob($this->cacheDir . '*.cache');
        $now = time();
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (isset($data['expires']) && $data['expires'] < $now) {
                unlink($file);
            }
        }
    }
    
    /**
     * Obtém dados meteorológicos da API Open-Meteo
     */
    public function getWeatherData($lat, $lon, $options = []) {
        // Validar coordenadas
        if (!validate_coordinates($lat, $lon)) {
            return [
                'success' => false,
                'error' => 'Coordenadas inválidas'
            ];
        }
        
        // Verificar cache
        $cacheKey = "weather_{$lat}_{$lon}_" . json_encode($options);
        $cachedData = $this->getFromCache($cacheKey);
        
        if ($cachedData) {
            log_message("Dados meteorológicos carregados do cache para $lat,$lon", 'INFO');
            return [
                'success' => true,
                'data' => $cachedData,
                'cached' => true
            ];
        }
        
        try {
            // Parâmetros padrão
            $defaultParams = [
                'latitude' => $lat,
                'longitude' => $lon,
                'hourly' => 'temperature_2m,relative_humidity_2m,precipitation_probability,weather_code,wind_speed_10m,wind_direction_10m,cloud_cover',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,sunrise,sunset,uv_index_max',
                'current_weather' => true,
                'timezone' => 'auto',
                'forecast_days' => 7,
                'past_days' => 1,
                'models' => 'best_match'
            ];
            
            // Mesclar com opções
            $params = array_merge($defaultParams, $options);
            
            // Construir URL
            $url = OPEN_METEO_URL . '?' . http_build_query($params);
            
            log_message("Buscando dados meteorológicos para $lat,$lon", 'INFO');
            
            // Fazer requisição
            $weatherData = $this->makeRequest($url);
            
            if (!$weatherData) {
                throw new Exception("Dados meteorológicos vazios");
            }
            
            // Adicionar metadados
            $weatherData['metadata'] = [
                'location' => ['lat' => $lat, 'lon' => $lon],
                'timestamp' => date('Y-m-d H:i:s'),
                'units' => $_SESSION['user_settings']['units'] ?? 'metric',
                'api_version' => '1.0'
            ];
            
            // Processar dados
            $processedData = $this->processWeatherData($weatherData);
            
            // Salvar no cache
            $this->saveToCache($cacheKey, $processedData, CACHE_DURATION);
            
            log_message("Dados meteorológicos obtidos com sucesso para $lat,$lon", 'INFO');
            
            return [
                'success' => true,
                'data' => $processedData,
                'cached' => false
            ];
            
        } catch (Exception $e) {
            log_message("Erro ao obter dados meteorológicos: " . $e->getMessage(), 'ERROR');
            
            // Tentar usar dados históricos do cache como fallback
            $fallbackData = $this->getWeatherFallback($lat, $lon);
            if ($fallbackData) {
                return [
                    'success' => true,
                    'data' => $fallbackData,
                    'cached' => true,
                    'fallback' => true,
                    'warning' => 'Dados podem estar desatualizados'
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Não foi possível obter dados meteorológicos',
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Processa dados meteorológicos brutos
     */
    private function processWeatherData($rawData) {
        // Funções de processamento (implementadas em weather-functions.php)
        $processed = [];
        
        // Dados atuais
        if (isset($rawData['current_weather'])) {
            $current = $rawData['current_weather'];
            $processed['current'] = [
                'temperature' => $current['temperature'],
                'windspeed' => $current['windspeed'],
                'winddirection' => $current['winddirection'],
                'weathercode' => $current['weathercode'],
                'time' => $current['time'],
                'temperature_unit' => '°C',
                'windspeed_unit' => 'km/h',
                'winddirection_unit' => '°'
            ];
        }
        
        // Dados horários (próximas 24 horas)
        if (isset($rawData['hourly'])) {
            $hourly = $rawData['hourly'];
            $processed['hourly'] = [];
            
            $hourCount = min(24, count($hourly['time']));
            for ($i = 0; $i < $hourCount; $i++) {
                $hourData = [
                    'time' => $hourly['time'][$i],
                    'temperature' => $hourly['temperature_2m'][$i] ?? null,
                    'humidity' => $hourly['relative_humidity_2m'][$i] ?? null,
                    'precipitation_probability' => $hourly['precipitation_probability'][$i] ?? null,
                    'weather_code' => $hourly['weather_code'][$i] ?? null,
                    'wind_speed' => $hourly['wind_speed_10m'][$i] ?? null,
                    'wind_direction' => $hourly['wind_direction_10m'][$i] ?? null,
                    'cloud_cover' => $hourly['cloud_cover'][$i] ?? null
                ];
                $processed['hourly'][] = $hourData;
            }
        }
        
        // Dados diários (próximos 7 dias)
        if (isset($rawData['daily'])) {
            $daily = $rawData['daily'];
            $processed['daily'] = [];
            
            $dayCount = min(7, count($daily['time']));
            for ($i = 0; $i < $dayCount; $i++) {
                $dayData = [
                    'time' => $daily['time'][$i],
                    'weather_code' => $daily['weather_code'][$i] ?? null,
                    'temperature_max' => $daily['temperature_2m_max'][$i] ?? null,
                    'temperature_min' => $daily['temperature_2m_min'][$i] ?? null,
                    'precipitation_sum' => $daily['precipitation_sum'][$i] ?? null,
                    'wind_speed_max' => $daily['windspeed_10m_max'][$i] ?? null,
                    'sunrise' => $daily['sunrise'][$i] ?? null,
                    'sunset' => $daily['sunset'][$i] ?? null,
                    'uv_index_max' => $daily['uv_index_max'][$i] ?? null
                ];
                $processed['daily'][] = $dayData;
            }
        }
        
        // Adicionar informações processadas
        $processed['alerts'] = $this->generateWeatherAlerts($processed);
        $processed['summary'] = $this->generateWeatherSummary($processed);
        
        return $processed;
    }
    
    /**
     * Gera alertas meteorológicos baseados nos dados
     */
    private function generateWeatherAlerts($weatherData) {
        $alerts = [];
        
        if (!isset($weatherData['current'])) {
            return $alerts;
        }
        
        $current = $weatherData['current'];
        
        // Verificar temperatura extrema
        if ($current['temperature'] > 35) {
            $alerts[] = [
                'type' => 'warning',
                'severity' => 'medium',
                'title' => 'Alerta de Calor',
                'description' => 'Temperatura acima de 35°C. Tome precauções contra o calor.',
                'icon' => 'fas fa-temperature-high',
                'valid_until' => date('Y-m-d H:i:s', strtotime('+3 hours'))
            ];
        } elseif ($current['temperature'] < 5) {
            $alerts[] = [
                'type' => 'warning',
                'severity' => 'medium',
                'title' => 'Alerta de Frio',
                'description' => 'Temperatura abaixo de 5°C. Proteja-se do frio.',
                'icon' => 'fas fa-temperature-low',
                'valid_until' => date('Y-m-d H:i:s', strtotime('+3 hours'))
            ];
        }
        
        // Verificar ventos fortes
        if ($current['windspeed'] > 60) {
            $alerts[] = [
                'type' => 'danger',
                'severity' => 'high',
                'title' => 'Alerta de Ventos Fortes',
                'description' => 'Ventos acima de 60 km/h. Tome cuidado ao circular.',
                'icon' => 'fas fa-wind',
                'valid_until' => date('Y-m-d H:i:s', strtotime('+2 hours'))
            ];
        }
        
        // Verificar alta probabilidade de chuva
        if (isset($weatherData['hourly'][0]['precipitation_probability']) && 
            $weatherData['hourly'][0]['precipitation_probability'] > 80) {
            $alerts[] = [
                'type' => 'info',
                'severity' => 'low',
                'title' => 'Alerta de Chuva',
                'description' => 'Alta probabilidade de chuva nas próximas horas.',
                'icon' => 'fas fa-cloud-rain',
                'valid_until' => date('Y-m-d H:i:s', strtotime('+3 hours'))
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Gera um resumo do clima
     */
    private function generateWeatherSummary($weatherData) {
        if (!isset($weatherData['current'])) {
            return 'Dados indisponíveis';
        }
        
        $current = $weatherData['current'];
        $description = getWeatherDescription($current['weathercode']);
        
        return "Atualmente: {$description}, {$current['temperature']}°C. " .
               "Vento: {$current['windspeed']} km/h.";
    }
    
    /**
     * Fallback para dados meteorológicos
     */
    private function getWeatherFallback($lat, $lon) {
        // Tenta buscar dados mais antigos no cache
        $pattern = $this->cacheDir . "weather_{$lat}_{$lon}_*.cache";
        $files = glob($pattern);
        
        if (empty($files)) {
            return null;
        }
        
        // Pega o arquivo mais recente
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $latestFile = $files[0];
        $data = json_decode(file_get_contents($latestFile), true);
        
        if (isset($data['data'])) {
            log_message("Usando dados de fallback para $lat,$lon", 'WARNING');
            return $data['data'];
        }
        
        return null;
    }
    
    /**
     * Obtém dados de qualidade do ar
     */
    public function getAirQuality($lat, $lon) {
        $cacheKey = "airquality_{$lat}_{$lon}";
        $cachedData = $this->getFromCache($cacheKey);
        
        if ($cachedData) {
            return [
                'success' => true,
                'data' => $cachedData,
                'cached' => true
            ];
        }
        
        try {
            // API de qualidade do ar da Open-Meteo
            $url = "https://air-quality-api.open-meteo.com/v1/air-quality?" . http_build_query([
                'latitude' => $lat,
                'longitude' => $lon,
                'hourly' => 'pm10,pm2_5,carbon_monoxide,nitrogen_dioxide,sulphur_dioxide,ozone',
                'domains' => 'cams_global',
                'timezone' => 'auto'
            ]);
            
            $airQualityData = $this->makeRequest($url);
            
            if (!$airQualityData || !isset($airQualityData['hourly'])) {
                throw new Exception("Dados de qualidade do ar indisponíveis");
            }
            
            // Processar dados
            $processedData = $this->processAirQualityData($airQualityData);
            
            // Salvar no cache
            $this->saveToCache($cacheKey, $processedData, 3600); // 1 hora
            
            return [
                'success' => true,
                'data' => $processedData,
                'cached' => false
            ];
            
        } catch (Exception $e) {
            log_message("Erro ao obter qualidade do ar: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => 'Dados de qualidade do ar indisponíveis'
            ];
        }
    }
    
    /**
     * Processa dados de qualidade do ar
     */
    private function processAirQualityData($rawData) {
        $hourly = $rawData['hourly'];
        
        // Pegar dados atuais (última hora disponível)
        $lastIndex = count($hourly['time']) - 1;
        
        $current = [
            'time' => $hourly['time'][$lastIndex],
            'pm10' => $hourly['pm10'][$lastIndex] ?? 0,
            'pm2_5' => $hourly['pm2_5'][$lastIndex] ?? 0,
            'carbon_monoxide' => $hourly['carbon_monoxide'][$lastIndex] ?? 0,
            'nitrogen_dioxide' => $hourly['nitrogen_dioxide'][$lastIndex] ?? 0,
            'sulphur_dioxide' => $hourly['sulphur_dioxide'][$lastIndex] ?? 0,
            'ozone' => $hourly['ozone'][$lastIndex] ?? 0
        ];
        
        // Calcular AQI (Índice de Qualidade do Ar)
        $aqi = $this->calculateAQI($current);
        
        return [
            'current' => $current,
            'aqi' => $aqi,
            'description' => $this->getAQIDescription($aqi['value']),
            'units' => [
                'pm10' => 'µg/m³',
                'pm2_5' => 'µg/m³',
                'carbon_monoxide' => 'µg/m³',
                'nitrogen_dioxide' => 'µg/m³',
                'sulphur_dioxide' => 'µg/m³',
                'ozone' => 'µg/m³'
            ]
        ];
    }
    
    /**
     * Calcula o Índice de Qualidade do Ar (AQI)
     */
    private function calculateAQI($pollutants) {
        // Implementação simplificada do AQI
        $pm25 = $pollutants['pm2_5'] ?? 0;
        $pm10 = $pollutants['pm10'] ?? 0;
        $no2 = $pollutants['nitrogen_dioxide'] ?? 0;
        
        // Usar o maior valor entre os poluentes
        $values = [$pm25, $pm10 * 0.5, $no2 * 0.2]; // Fatores de ponderação
        $maxValue = max($values);
        
        // Converter para AQI (escala 0-500)
        if ($maxValue <= 12) {
            $aqi = ($maxValue / 12) * 50;
            $level = 'Boa';
            $color = '#2ecc71';
        } elseif ($maxValue <= 35.4) {
            $aqi = 51 + (($maxValue - 12.1) / (35.4 - 12.1)) * 49;
            $level = 'Moderada';
            $color = '#f1c40f';
        } elseif ($maxValue <= 55.4) {
            $aqi = 101 + (($maxValue - 35.5) / (55.4 - 35.5)) * 49;
            $level = 'Insalubre para grupos sensíveis';
            $color = '#e67e22';
        } elseif ($maxValue <= 150.4) {
            $aqi = 151 + (($maxValue - 55.5) / (150.4 - 55.5)) * 99;
            $level = 'Insalubre';
            $color = '#e74c3c';
        } elseif ($maxValue <= 250.4) {
            $aqi = 201 + (($maxValue - 150.5) / (250.4 - 150.5)) * 99;
            $level = 'Muito Insalubre';
            $color = '#8e44ad';
        } else {
            $aqi = 301 + (($maxValue - 250.5) / (500.4 - 250.5)) * 199;
            $level = 'Perigosa';
            $color = '#c0392b';
        }
        
        return [
            'value' => round($aqi),
            'level' => $level,
            'color' => $color,
            'health_effects' => $this->getAQIHealthEffects($aqi)
        ];
    }
    
    /**
     * Obtém descrição do AQI
     */
    private function getAQIDescription($aqi) {
        if ($aqi <= 50) {
            return 'A qualidade do ar é considerada satisfatória.';
        } elseif ($aqi <= 100) {
            return 'A qualidade do ar é aceitável.';
        } elseif ($aqi <= 150) {
            return 'Membros de grupos sensíveis podem sentir efeitos na saúde.';
        } elseif ($aqi <= 200) {
            return 'Todos podem começar a sentir efeitos na saúde.';
        } elseif ($aqi <= 300) {
            return 'Alerta de saúde: todos podem sentir efeitos mais graves.';
        } else {
            return 'Aviso de emergência de saúde.';
        }
    }
    
    /**
     * Obtém efeitos na saúde baseados no AQI
     */
    private function getAQIHealthEffects($aqi) {
        if ($aqi <= 50) {
            return 'Nenhum';
        } elseif ($aqi <= 100) {
            return 'Irritação leve em pessoas muito sensíveis';
        } elseif ($aqi <= 150) {
            return 'Problemas respiratórios em pessoas sensíveis';
        } elseif ($aqi <= 200) {
            return 'Problemas respiratórios para toda a população';
        } elseif ($aqi <= 300) {
            return 'Efeitos graves na saúde';
        } else {
            return 'Efeitos emergenciais na saúde de toda a população';
        }
    }
    
    /**
     * Limpa todo o cache
     */
    public function clearCache() {
        if (!$this->cacheEnabled) return false;
        
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        
        return count($files);
    }
}

// Inicialização
$apiHandler = new APIHandler();

// Limpar cache expirado periodicamente
$apiHandler->cleanExpiredCache();

// Rotas da API
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $endpoint = $_GET['endpoint'] ?? '';
    $lat = $_GET['lat'] ?? '';
    $lon = $_GET['lon'] ?? '';
    $action = $_GET['action'] ?? '';
    
    header('Content-Type: application/json');
    
    switch ($endpoint) {
        case 'weather':
            if (!empty($lat) && !empty($lon)) {
                $options = $_GET;
                unset($options['endpoint'], $options['lat'], $options['lon']);
                echo json_encode($apiHandler->getWeatherData($lat, $lon, $options));
            } else {
                echo json_encode(['success' => false, 'error' => 'Coordenadas não fornecidas']);
            }
            break;
            
        case 'air-quality':
            if (!empty($lat) && !empty($lon)) {
                echo json_encode($apiHandler->getAirQuality($lat, $lon));
            } else {
                echo json_encode(['success' => false, 'error' => 'Coordenadas não fornecidas']);
            }
            break;
            
        case 'cache':
            if ($action === 'clear' && isset($_SESSION['user_id'])) {
                $cleared = $apiHandler->clearCache();
                echo json_encode(['success' => true, 'cleared' => $cleared]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Ação não permitida']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Endpoint não reconhecido']);
    }
}
?>