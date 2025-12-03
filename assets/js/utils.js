// utils.js - Utilitários simplificados
class WeatherUtils {
    static formatDate(date, format = 'short') {
        const d = new Date(date);
        if (format === 'short') {
            return d.toLocaleDateString('pt-BR');
        }
        return d.toLocaleString('pt-BR');
    }
    
    static formatNumber(num, decimals = 1) {
        return num.toFixed(decimals);
    }
    
    static getWeatherIcon(code, isDay = true) {
        // Mesma lógica do script.js
        const icons = {
            0: isDay ? 'fas fa-sun' : 'fas fa-moon',
            1: isDay ? 'fas fa-cloud-sun' : 'fas fa-cloud-moon',
            2: 'fas fa-cloud',
            3: 'fas fa-cloud',
            45: 'fas fa-smog',
            48: 'fas fa-smog',
            51: 'fas fa-cloud-rain',
            53: 'fas fa-cloud-rain',
            55: 'fas fa-cloud-rain',
            61: 'fas fa-cloud-showers-heavy',
            63: 'fas fa-cloud-showers-heavy',
            65: 'fas fa-cloud-showers-heavy',
            71: 'fas fa-snowflake',
            73: 'fas fa-snowflake',
            75: 'fas fa-snowflake',
            77: 'fas fa-snowflake',
            80: 'fas fa-cloud-showers-heavy',
            81: 'fas fa-cloud-showers-heavy',
            82: 'fas fa-cloud-showers-heavy',
            85: 'fas fa-snowflake',
            86: 'fas fa-snowflake',
            95: 'fas fa-bolt',
            96: 'fas fa-bolt',
            99: 'fas fa-bolt',
        };
        
        return icons[code] || 'fas fa-question-circle';
    }
}

window.weatherUtils = WeatherUtils;