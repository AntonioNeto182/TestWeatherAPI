<?php
// location-functions.php - Funções de manipulação de localização
require_once 'config.php';

/**
 * Valida coordenadas
 */
function validateCoordinates($lat, $lon) {
    if (!is_numeric($lat) || !is_numeric($lon)) {
        return false;
    }
    
    $lat = floatval($lat);
    $lon = floatval($lon);
    
    return ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180);
}

/**
 * Calcula distância entre duas coordenadas (Haversine)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit = 'km') {
    $earthRadius = 6371; // km
    
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    
    $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    $distance = $earthRadius * $c;
    
    if ($unit === 'mi') {
        return $distance * 0.621371;
    }
    
    return $distance;
}

/**
 * Formata distância de forma legível
 */
function formatDistance($distance, $unit = 'km') {
    if ($unit === 'mi') {
        if ($distance < 0.1) {
            return round($distance * 5280) . ' ft';
        } elseif ($distance < 10) {
            return round($distance, 1) . ' mi';
        } else {
            return round($distance) . ' mi';
        }
    } else {
        if ($distance < 0.1) {
            return round($distance * 1000) . ' m';
        } elseif ($distance < 10) {
            return round($distance, 1) . ' km';
        } else {
            return round($distance) . ' km';
        }
    }
}

/**
 * Calcula direção entre dois pontos
 */
function calculateBearing($lat1, $lon1, $lat2, $lon2) {
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    
    $y = sin($lon2 - $lon1) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lon2 - $lon1);
    
    $bearing = atan2($y, $x);
    $bearing = rad2deg($bearing);
    $bearing = fmod(($bearing + 360), 360);
    
    return $bearing;
}

/**
 * Obtém direção em pontos cardeais
 */
function getCardinalDirection($bearing) {
    $directions = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'];
    $index = round($bearing / 45) % 8;
    return $directions[$index];
}

/**
 * Converte coordenadas DMS para decimal
 */
function dmsToDecimal($degrees, $minutes, $seconds, $direction) {
    $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
    
    if ($direction === 'S' || $direction === 'W') {
        $decimal = -$decimal;
    }
    
    return $decimal;
}

/**
 * Converte coordenadas decimal para DMS
 */
function decimalToDMS($decimal, $isLatitude = true) {
    $direction = $decimal >= 0 ? 
        ($isLatitude ? 'N' : 'E') : 
        ($isLatitude ? 'S' : 'W');
    
    $decimal = abs($decimal);
    $degrees = floor($decimal);
    $minutes = floor(($decimal - $degrees) * 60);
    $seconds = round((($decimal - $degrees) * 3600 - $minutes * 60), 2);
    
    return [
        'degrees' => $degrees,
        'minutes' => $minutes,
        'seconds' => $seconds,
        'direction' => $direction
    ];
}

/**
 * Formata coordenadas DMS
 */
function formatDMS($lat, $lon) {
    $latDMS = decimalToDMS($lat, true);
    $lonDMS = decimalToDMS($lon, false);
    
    return sprintf(
        "%d°%d'%.2f\"%s %d°%d'%.2f\"%s",
        $latDMS['degrees'], $latDMS['minutes'], $latDMS['seconds'], $latDMS['direction'],
        $lonDMS['degrees'], $lonDMS['minutes'], $lonDMS['seconds'], $lonDMS['direction']
    );
}

/**
 * Obtém informações de fuso horário
 */
function getTimezoneInfo($lat, $lon) {
    try {
        $timezoneData = json_decode(file_get_contents(
            "https://api.timezonedb.com/v2.1/get-time-zone?key=TIMEZONE_API_KEY&format=json&by=position&lat=$lat&lng=$lon"
        ), true);
        
        if ($timezoneData['status'] === 'OK') {
            return [
                'timezone' => $timezoneData['zoneName'],
                'offset' => $timezoneData['gmtOffset'],
                'dst' => $timezoneData['dst'] == 1
            ];
        }
    } catch (Exception $e) {
        // Fallback: usar fuso horário estimado
        $offset = $lon / 15;
        return [
            'timezone' => 'UTC' . ($offset >= 0 ? '+' : '') . round($offset),
            'offset' => $offset * 3600,
            'dst' => false
        ];
    }
    
    return null;
}

/**
 * Calcula deslocamento baseado em distância e direção
 */
function calculateDisplacement($lat, $lon, $distance, $bearing, $unit = 'km') {
    if ($unit === 'mi') {
        $distance = $distance * 1.60934;
    }
    
    $earthRadius = 6371; // km
    $bearing = deg2rad($bearing);
    $lat1 = deg2rad($lat);
    $lon1 = deg2rad($lon);
    
    $lat2 = asin(sin($lat1) * cos($distance/$earthRadius) + 
                 cos($lat1) * sin($distance/$earthRadius) * cos($bearing));
    
    $lon2 = $lon1 + atan2(sin($bearing) * sin($distance/$earthRadius) * cos($lat1),
                          cos($distance/$earthRadius) - sin($lat1) * sin($lat2));
    
    return [
        'lat' => rad2deg($lat2),
        'lon' => rad2deg($lon2)
    ];
}

/**
 * Verifica se coordenadas estão em terra
 */
function isLandCoordinates($lat, $lon) {
    // Implementação simples baseada em coordenadas conhecidas
    // Em produção, usar um serviço de API ou banco de dados GIS
    
    // Lista de bounding boxes de continentes (simplificado)
    $landAreas = [
        // América do Sul
        ['min_lat' => -56, 'max_lat' => 13, 'min_lon' => -92, 'max_lon' => -34],
        // América do Norte
        ['min_lat' => 15, 'max_lat' => 72, 'min_lon' => -168, 'max_lon' => -52],
        // Europa
        ['min_lat' => 35, 'max_lat' => 72, 'min_lon' => -25, 'max_lon' => 40],
        // África
        ['min_lat' => -35, 'max_lat' => 38, 'min_lon' => -26, 'max_lon' => 60],
        // Ásia
        ['min_lat' => 10, 'max_lat' => 78, 'min_lon' => 25, 'max_lon' => 180],
        // Oceania
        ['min_lat' => -48, 'max_lat' => 0, 'min_lon' => 110, 'max_lon' => 180]
    ];
    
    foreach ($landAreas as $area) {
        if ($lat >= $area['min_lat'] && $lat <= $area['max_lat'] &&
            $lon >= $area['min_lon'] && $lon <= $area['max_lon']) {
            return true;
        }
    }
    
    return false;
}

/**
 * Obtém elevação aproximada
 */
function getElevation($lat, $lon) {
    // Implementação simplificada
    // Em produção, usar API como Open-Elevation ou Google Maps Elevation API
    
    // Valores aproximados
    $baseElevations = [
        // Brasil
        ['lat_range' => [-35, 5], 'lon_range' => [-75, -35], 'elevation' => 500],
        // Andes
        ['lat_range' => [-20, 10], 'lon_range' => [-80, -65], 'elevation' => 3000],
        // Himalaia
        ['lat_range' => [25, 35], 'lon_range' => [75, 100], 'elevation' => 5000],
        // Alpes
        ['lat_range' => [43, 48], 'lon_range' => [5, 15], 'elevation' => 2000]
    ];
    
    $elevation = 100; // Elevação padrão
    
    foreach ($baseElevations as $region) {
        if ($lat >= $region['lat_range'][0] && $lat <= $region['lat_range'][1] &&
            $lon >= $region['lon_range'][0] && $lon <= $region['lon_range'][1]) {
            $elevation = $region['elevation'];
            break;
        }
    }
    
    // Adicionar variação aleatória para simulação
    $variation = rand(-$elevation * 0.2, $elevation * 0.2);
    
    return max(0, $elevation + $variation);
}

/**
 * Formata elevação
 */
function formatElevation($elevation, $units = 'metric') {
    if ($units === 'imperial') {
        $feet = $elevation * 3.28084;
        return round($feet) . ' ft';
    }
    return round($elevation) . ' m';
}

/**
 * Calcula área de influência
 */
function calculateInfluenceArea($lat, $lon, $radius = 50, $unit = 'km') {
    $points = [];
    $steps = 36; // Número de pontos no círculo
    
    for ($i = 0; $i < $steps; $i++) {
        $bearing = $i * (360 / $steps);
        $point = calculateDisplacement($lat, $lon, $radius, $bearing, $unit);
        $points[] = $point;
    }
    
    return $points;
}

/**
 * Verifica se ponto está dentro de polígono
 */
function pointInPolygon($point, $polygon) {
    $x = $point['lon'];
    $y = $point['lat'];
    
    $inside = false;
    $n = count($polygon);
    
    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $xi = $polygon[$i]['lon'];
        $yi = $polygon[$i]['lat'];
        $xj = $polygon[$j]['lon'];
        $yj = $polygon[$j]['lat'];
        
        $intersect = (($yi > $y) != ($yj > $y))
            && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
        
        if ($intersect) {
            $inside = !$inside;
        }
    }
    
    return $inside;
}

/**
 * Obtém proximidade a corpos d'água
 */
function getWaterProximity($lat, $lon) {
    // Coords de principais oceanos e grandes lagos (simplificado)
    $waterBodies = [
        // Oceano Atlântico
        ['lat' => 0, 'lon' => -30, 'radius' => 4000, 'type' => 'oceano'],
        // Oceano Pacífico
        ['lat' => 0, 'lon' => -160, 'radius' => 8000, 'type' => 'oceano'],
        // Mar Mediterrâneo
        ['lat' => 35, 'lon' => 20, 'radius' => 1000, 'type' => 'mar']
    ];
    
    $closest = null;
    $minDistance = PHP_FLOAT_MAX;
    
    foreach ($waterBodies as $water) {
        $distance = calculateDistance($lat, $lon, $water['lat'], $water['lon']);
        
        if ($distance < $minDistance && $distance < $water['radius']) {
            $minDistance = $distance;
            $closest = $water;
        }
    }
    
    if ($closest) {
        return [
            'type' => $closest['type'],
            'distance' => $minDistance,
            'proximity' => $minDistance < 100 ? 'muito perto' : 
                         ($minDistance < 500 ? 'próximo' : 'distante')
        ];
    }
    
    return null;
}

/**
 * Gera hash de localização
 */
function generateLocationHash($lat, $lon, $precision = 4) {
    $roundedLat = round($lat, $precision);
    $roundedLon = round($lon, $precision);
    return md5("{$roundedLat},{$roundedLon}");
}

/**
 * Serializa dados de localização
 */
function serializeLocation($locationData) {
    $required = ['name', 'lat', 'lon'];
    
    foreach ($required as $field) {
        if (!isset($locationData[$field])) {
            throw new Exception("Campo obrigatório '$field' não encontrado");
        }
    }
    
    return json_encode([
        'n' => $locationData['name'],
        'c' => $locationData['country'] ?? '',
        's' => $locationData['state'] ?? '',
        'la' => $locationData['lat'],
        'lo' => $locationData['lon'],
        'e' => $locationData['elevation'] ?? null,
        't' => time()
    ]);
}

/**
 * Deserializa dados de localização
 */
function deserializeLocation($serialized) {
    $data = json_decode($serialized, true);
    
    if (!$data) {
        return null;
    }
    
    return [
        'name' => $data['n'],
        'country' => $data['c'],
        'state' => $data['s'],
        'lat' => $data['la'],
        'lon' => $data['lo'],
        'elevation' => $data['e'],
        'timestamp' => $data['t']
    ];
}
?>