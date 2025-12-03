<?php
// cities.php - Gerenciamento de cidades e localizações
session_start();
require_once 'config.php';

class CityManager {
    private $db;
    
    public function __construct() {
        $this->connectDB();
    }
    
    private function connectDB() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            log_message("Erro na conexão com o banco: " . $e->getMessage(), 'ERROR');
            $this->db = null;
        }
    }
    
    /**
     * Busca cidades por nome
     */
    public function searchCities($query, $limit = 10) {
        if (strlen($query) < 2) {
            return ['success' => false, 'message' => 'Digite pelo menos 2 caracteres'];
        }
        
        try {
            // Buscar no banco de dados
            $stmt = $this->db->prepare("
                SELECT id, name, country, admin1 as state, latitude, longitude 
                FROM cities 
                WHERE name LIKE :query 
                OR country LIKE :query 
                OR admin1 LIKE :query
                ORDER BY population DESC 
                LIMIT :limit
            ");
            
            $searchQuery = "%" . sanitize_input($query) . "%";
            $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Se não encontrou no banco, busca na API
            if (empty($results)) {
                return $this->searchCitiesAPI($query, $limit);
            }
            
            return [
                'success' => true,
                'results' => $results
            ];
            
        } catch(PDOException $e) {
            log_message("Erro na busca de cidades: " . $e->getMessage(), 'ERROR');
            return $this->searchCitiesAPI($query, $limit);
        }
    }
    
    /**
     * Busca cidades usando a API de geocodificação
     */
    private function searchCitiesAPI($query, $limit = 10) {
        try {
            $url = GEOCODING_URL . "?name=" . urlencode($query) . "&count=" . $limit . "&language=pt&format=json";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WeatherMasterPro/2.0');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode !== 200 || !$response) {
                throw new Exception("Erro na API de geocodificação");
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['results']) || empty($data['results'])) {
                return [
                    'success' => false,
                    'message' => 'Nenhuma cidade encontrada'
                ];
            }
            
            $formattedResults = array_map(function($city) {
                return [
                    'name' => $city['name'],
                    'country' => $city['country'],
                    'state' => $city['admin1'] ?? '',
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                    'population' => $city['population'] ?? 0
                ];
            }, $data['results']);
            
            // Salvar no banco para cache
            $this->cacheCities($formattedResults);
            
            return [
                'success' => true,
                'results' => $formattedResults
            ];
            
        } catch(Exception $e) {
            log_message("Erro na API de geocodificação: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => 'Serviço de busca temporariamente indisponível'
            ];
        }
    }
    
    /**
     * Cache de cidades no banco de dados
     */
    private function cacheCities($cities) {
        if (!$this->db) return;
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO cities (name, country, admin1, latitude, longitude, population, last_searched)
                VALUES (:name, :country, :state, :lat, :lon, :pop, NOW())
                ON DUPLICATE KEY UPDATE 
                    last_searched = NOW(),
                    search_count = search_count + 1
            ");
            
            foreach ($cities as $city) {
                $stmt->execute([
                    ':name' => $city['name'],
                    ':country' => $city['country'],
                    ':state' => $city['state'],
                    ':lat' => $city['latitude'],
                    ':lon' => $city['longitude'],
                    ':pop' => $city['population']
                ]);
            }
            
        } catch(PDOException $e) {
            log_message("Erro ao salvar cache de cidades: " . $e->getMessage(), 'WARNING');
        }
    }
    
    /**
     * Obtém cidade pelas coordenadas (reverse geocoding)
     */
    public function getCityByCoordinates($lat, $lon) {
        if (!validate_coordinates($lat, $lon)) {
            return [
                'success' => false,
                'message' => 'Coordenadas inválidas'
            ];
        }
        
        try {
            // Primeiro tenta no banco de dados
            $stmt = $this->db->prepare("
                SELECT name, country, admin1 as state 
                FROM cities 
                WHERE ROUND(latitude, 2) = ROUND(:lat, 2) 
                AND ROUND(longitude, 2) = ROUND(:lon, 2)
                ORDER BY population DESC 
                LIMIT 1
            ");
            
            $stmt->execute([':lat' => $lat, ':lon' => $lon]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'success' => true,
                    'city' => $result
                ];
            }
            
            // Se não encontrou, usa API
            return $this->reverseGeocodeAPI($lat, $lon);
            
        } catch(PDOException $e) {
            log_message("Erro ao buscar cidade por coordenadas: " . $e->getMessage(), 'ERROR');
            return $this->reverseGeocodeAPI($lat, $lon);
        }
    }
    
    /**
     * Reverse geocoding usando API
     */
    private function reverseGeocodeAPI($lat, $lon) {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon&zoom=10&addressdetails=1";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WeatherMasterPro/2.0');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Language: pt-BR,pt;q=0.9']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode !== 200 || !$response) {
                throw new Exception("Erro no reverse geocoding");
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['address'])) {
                return [
                    'success' => false,
                    'message' => 'Localização não encontrada'
                ];
            }
            
            $address = $data['address'];
            $cityName = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? 'Localização desconhecida';
            $state = $address['state'] ?? $address['region'] ?? '';
            $country = $address['country'] ?? '';
            
            $cityData = [
                'name' => $cityName,
                'country' => $country,
                'state' => $state
            ];
            
            // Salva no cache
            $this->cacheCity($cityData, $lat, $lon);
            
            return [
                'success' => true,
                'city' => $cityData
            ];
            
        } catch(Exception $e) {
            log_message("Erro no reverse geocoding API: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => 'Não foi possível identificar a localização'
            ];
        }
    }
    
    /**
     * Salva cidade no cache
     */
    private function cacheCity($cityData, $lat, $lon) {
        if (!$this->db) return;
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO cities (name, country, admin1, latitude, longitude, last_searched)
                VALUES (:name, :country, :state, :lat, :lon, NOW())
                ON DUPLICATE KEY UPDATE last_searched = NOW()
            ");
            
            $stmt->execute([
                ':name' => $cityData['name'],
                ':country' => $cityData['country'],
                ':state' => $cityData['state'],
                ':lat' => $lat,
                ':lon' => $lon
            ]);
            
        } catch(PDOException $e) {
            log_message("Erro ao salvar cidade no cache: " . $e->getMessage(), 'WARNING');
        }
    }
    
    /**
     * Obtém cidades populares/frequentes
     */
    public function getPopularCities($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT name, country, admin1 as state, latitude, longitude 
                FROM cities 
                WHERE search_count > 0 
                ORDER BY search_count DESC, last_searched DESC 
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Se não tem dados, retorna cidades padrão
            if (empty($results)) {
                return $this->getDefaultCities();
            }
            
            return [
                'success' => true,
                'results' => $results
            ];
            
        } catch(PDOException $e) {
            log_message("Erro ao buscar cidades populares: " . $e->getMessage(), 'ERROR');
            return $this->getDefaultCities();
        }
    }
    
    /**
     * Cidades padrão caso o banco esteja vazio
     */
    private function getDefaultCities() {
        $defaultCities = [
            [
                'name' => 'São Paulo',
                'country' => 'Brasil',
                'state' => 'SP',
                'latitude' => -23.5505,
                'longitude' => -46.6333
            ],
            [
                'name' => 'Rio de Janeiro',
                'country' => 'Brasil',
                'state' => 'RJ',
                'latitude' => -22.9068,
                'longitude' => -43.1729
            ],
            [
                'name' => 'Brasília',
                'country' => 'Brasil',
                'state' => 'DF',
                'latitude' => -15.7801,
                'longitude' => -47.9292
            ],
            [
                'name' => 'Nova York',
                'country' => 'EUA',
                'state' => 'NY',
                'latitude' => 40.7128,
                'longitude' => -74.0060
            ],
            [
                'name' => 'Londres',
                'country' => 'Reino Unido',
                'state' => 'Inglaterra',
                'latitude' => 51.5074,
                'longitude' => -0.1278
            ]
        ];
        
        return [
            'success' => true,
            'results' => $defaultCities
        ];
    }
    
    /**
     * Adiciona cidade aos favoritos do usuário
     */
    public function addToFavorites($userId, $cityData) {
        if (!isset($_SESSION['user_settings']['favorites'])) {
            $_SESSION['user_settings']['favorites'] = [];
        }
        
        $favoriteKey = $cityData['latitude'] . ',' . $cityData['longitude'];
        
        $_SESSION['user_settings']['favorites'][$favoriteKey] = [
            'name' => $cityData['name'],
            'country' => $cityData['country'],
            'state' => $cityData['state'] ?? '',
            'latitude' => $cityData['latitude'],
            'longitude' => $cityData['longitude'],
            'added_at' => date('Y-m-d H:i:s')
        ];
        
        // Limita a 50 favoritos
        if (count($_SESSION['user_settings']['favorites']) > 50) {
            array_shift($_SESSION['user_settings']['favorites']);
        }
        
        return [
            'success' => true,
            'message' => 'Cidade adicionada aos favoritos'
        ];
    }
    
    /**
     * Remove cidade dos favoritos
     */
    public function removeFromFavorites($lat, $lon) {
        if (!isset($_SESSION['user_settings']['favorites'])) {
            return [
                'success' => false,
                'message' => 'Nenhum favorito encontrado'
            ];
        }
        
        $favoriteKey = $lat . ',' . $lon;
        
        if (isset($_SESSION['user_settings']['favorites'][$favoriteKey])) {
            unset($_SESSION['user_settings']['favorites'][$favoriteKey]);
            return [
                'success' => true,
                'message' => 'Cidade removida dos favoritos'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Cidade não encontrada nos favoritos'
        ];
    }
    
    /**
     * Obtém cidades favoritas do usuário
     */
    public function getFavorites() {
        if (!isset($_SESSION['user_settings']['favorites']) || empty($_SESSION['user_settings']['favorites'])) {
            return [
                'success' => true,
                'results' => []
            ];
        }
        
        $favorites = array_values($_SESSION['user_settings']['favorites']);
        
        return [
            'success' => true,
            'results' => $favorites
        ];
    }
}

// Inicialização
$cityManager = new CityManager();

// Rotas da API
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'search':
            $query = $_GET['q'] ?? '';
            $limit = $_GET['limit'] ?? 10;
            echo json_encode($cityManager->searchCities($query, $limit));
            break;
            
        case 'popular':
            $limit = $_GET['limit'] ?? 10;
            echo json_encode($cityManager->getPopularCities($limit));
            break;
            
        case 'favorites':
            echo json_encode($cityManager->getFavorites());
            break;
            
        case 'reverse':
            $lat = $_GET['lat'] ?? '';
            $lon = $_GET['lon'] ?? '';
            echo json_encode($cityManager->getCityByCoordinates($lat, $lon));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_favorite':
            $cityData = [
                'name' => $_POST['name'] ?? '',
                'country' => $_POST['country'] ?? '',
                'state' => $_POST['state'] ?? '',
                'latitude' => $_POST['latitude'] ?? '',
                'longitude' => $_POST['longitude'] ?? ''
            ];
            echo json_encode($cityManager->addToFavorites($_SESSION['user_id'] ?? 0, $cityData));
            break;
            
        case 'remove_favorite':
            $lat = $_POST['latitude'] ?? '';
            $lon = $_POST['longitude'] ?? '';
            echo json_encode($cityManager->removeFromFavorites($lat, $lon));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
}
?>