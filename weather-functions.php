<?php
// weather-functions.php - Funções para processamento de dados meteorológicos

/**
 * Converte código meteorológico em descrição
 */
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
        ]
    ];
    
    return $descriptions[$lang][$code] ?? 'Condição desconhecida';
}

/**
 * Obtém ícone correspondente ao código meteorológico
 */
function getWeatherIcon($code, $isDay = true) {
    $icons = [
        0 => $isDay ? 'fas fa-sun' : 'fas fa-moon',
        1 => $isDay ? 'fas fa-cloud-sun' : 'fas fa-cloud-moon',
        2 => 'fas fa-cloud',
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

/**
 * Converte direção do vento em pontos cardeais
 */
function getWindDirection($degrees) {
    $directions = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'];
    $index = round(($degrees % 360) / 45);
    return $directions[$index % 8];
}

/**
 * Calcula sensação térmica
 */
function calculateFeelsLike($temp, $wind, $humidity) {
    // Fórmula simplificada
    $feelsLike = $temp;
    
    // Wind chill (só abaixo de 10°C e vento > 5 km/h)
    if ($temp < 10 && $wind > 5) {
        $feelsLike = 13.12 + 0.6215 * $temp - 11.37 * pow($wind, 0.16) 
                    + 0.3965 * $temp * pow($wind, 0.16);
    }
    
    // Heat index (só acima de 27°C e umidade > 40%)
    if ($temp > 27 && $humidity > 40) {
        $feelsLike = -42.379 + 2.04901523 * $temp + 10.14333127 * $humidity 
                    - 0.22475541 * $temp * $humidity;
    }
    
    return round($feelsLike, 1);
}

/**
 * Formata temperatura com unidade
 */
function formatTemperature($temp, $units = 'metric') {
    if ($units === 'imperial') {
        return round(($temp * 9/5) + 32, 1) . '°F';
    }
    return round($temp, 1) . '°C';
}

/**
 * Formata velocidade do vento
 */
function formatWindSpeed($speed, $units = 'metric') {
    if ($units === 'imperial') {
        return round($speed * 0.621371, 1) . ' mph';
    }
    return round($speed, 1) . ' km/h';
}
?>