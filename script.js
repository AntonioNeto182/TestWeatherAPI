// script.js - Arquivo principal JavaScript simplificado
class WeatherSystem {
    constructor() {
        this.currentLocation = {
            lat: DEFAULT_LAT,
            lon: DEFAULT_LON,
            name: DEFAULT_CITY
        };
        
        this.weatherData = null;
        this.map = null;
        this.charts = {};
        
        this.initialize();
    }

    async initialize() {
        console.log('Inicializando WeatherSystem...');
        
        // Remover classe preload
        document.body.classList.remove('preload');
        
        // Configurar eventos
        this.setupEventListeners();
        
        // Carregar dados iniciais
        await this.loadInitialWeather();
        
        // Esconder tela de carregamento
        this.hideLoadingScreen();
    }

    setupEventListeners() {
        // Busca de cidade
        const searchBtn = document.getElementById('search-button');
        const searchInput = document.getElementById('global-search');
        
        if (searchBtn) {
            searchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.searchCity();
            });
        }
        
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.searchCity();
                }
            });
        }

        // Localização atual
        const locationBtns = document.querySelectorAll('[onclick*="getCurrentLocation"]');
        locationBtns.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                this.getCurrentLocation();
            };
        });

        // Refresh
        const refreshBtns = document.querySelectorAll('[onclick*="refreshWeather"]');
        refreshBtns.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                this.refreshWeather();
            };
        });

        // Dark mode toggle
        const darkModeSwitch = document.getElementById('darkModeSwitch');
        if (darkModeSwitch) {
            darkModeSwitch.addEventListener('change', (e) => {
                this.toggleDarkMode(e.target.checked);
            });
        }

        // Units change
        const unitRadios = document.querySelectorAll('input[name="units"]');
        unitRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.changeUnits(e.target.value);
            });
        });

        // Language change
        const langSelect = document.getElementById('languageSelect');
        if (langSelect) {
            langSelect.addEventListener('change', (e) => {
                this.changeLanguage(e.target.value);
            });
        }

        // Location select
        const locationSelect = document.getElementById('location-select');
        if (locationSelect) {
            locationSelect.addEventListener('change', (e) => {
                this.handleLocationSelect(e.target.value);
            });
        }

        // Load history chart
        const loadHistoryBtn = document.querySelector('[onclick="loadHistory()"]');
        if (loadHistoryBtn) {
            loadHistoryBtn.onclick = (e) => {
                e.preventDefault();
                this.loadHistory();
            };
        }
    }

    async loadInitialWeather() {
        try {
            this.showLoading(true);
            
            // Usar localização padrão
            await this.fetchWeatherData(this.currentLocation.lat, this.currentLocation.lon);
            
            this.showLoading(false);
        } catch (error) {
            console.error('Erro ao carregar dados iniciais:', error);
            this.showLoading(false);
            this.showNotification('Erro ao carregar dados do clima', 'error');
        }
    }

    async fetchWeatherData(lat, lon, cityName = '') {
        try {
            this.showLoading(true);
            
            // Atualizar localização atual
            this.currentLocation.lat = lat;
            this.currentLocation.lon = lon;
            if (cityName) {
                this.currentLocation.name = cityName;
            }
            
            console.log(`Buscando dados para: ${lat}, ${lon}`);
            
            // Construir URL da API
            const params = {
                latitude: lat,
                longitude: lon,
                hourly: 'temperature_2m,relative_humidity_2m,precipitation_probability,weather_code,wind_speed_10m,wind_direction_10m',
                daily: 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max',
                current_weather: true,
                timezone: 'auto',
                forecast_days: 7
            };
            
            const queryString = new URLSearchParams(params).toString();
            const url = `${OPEN_METEO_URL}?${queryString}`;
            
            console.log('URL da API:', url);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Dados recebidos:', data);
            
            this.weatherData = data;
            
            // Atualizar interface
            this.updateWeatherDisplay();
            this.updateLocationInfo();
            this.updateHourlyForecast();
            this.updateDailyForecast();
            
            // Inicializar mapa se não existir
            if (!this.map && typeof L !== 'undefined') {
                this.initMap();
            } else if (this.map) {
                this.updateMap();
            }
            
            this.showLoading(false);
            this.showNotification('Dados atualizados com sucesso!', 'success');
            
        } catch (error) {
            console.error('Erro ao buscar dados do clima:', error);
            this.showLoading(false);
            this.showNotification('Erro ao buscar dados do clima. Tente novamente.', 'error');
            
            // Usar dados de exemplo
            this.useSampleData();
        }
    }

    updateWeatherDisplay() {
        if (!this.weatherData?.current_weather) return;
        
        const current = this.weatherData.current_weather;
        
        // Temperatura atual
        const tempElement = document.getElementById('current-temperature');
        if (tempElement) {
            tempElement.textContent = `${Math.round(current.temperature)}°`;
        }
        
        // Descrição do clima
        const descElement = document.getElementById('weather-description');
        if (descElement) {
            descElement.textContent = this.getWeatherDescription(current.weathercode);
        }
        
        // Ícone do clima
        const iconElement = document.getElementById('current-weather-icon');
        if (iconElement) {
            const isDay = this.isDayTime();
            iconElement.innerHTML = `<i class="${this.getWeatherIcon(current.weathercode, isDay)} fa-5x"></i>`;
        }
        
        // Detalhes
        this.updateWeatherDetails(current);
    }

    updateWeatherDetails(current) {
        if (!this.weatherData?.hourly) return;
        
        const hourly = this.weatherData.hourly;
        
        // Verificar se temos dados suficientes
        if (hourly.wind_speed_10m && hourly.wind_speed_10m.length > 0) {
            const windSpeed = document.getElementById('wind-speed');
            if (windSpeed) {
                windSpeed.textContent = `${Math.round(hourly.wind_speed_10m[0])} km/h`;
            }
        }
        
        if (hourly.relative_humidity_2m && hourly.relative_humidity_2m.length > 0) {
            const humidity = document.getElementById('humidity');
            if (humidity) {
                humidity.textContent = `${hourly.relative_humidity_2m[0]}%`;
            }
        }
        
        if (hourly.precipitation_probability && hourly.precipitation_probability.length > 0) {
            const precipitation = document.getElementById('precipitation');
            if (precipitation) {
                precipitation.textContent = `${hourly.precipitation_probability[0]}%`;
            }
        }
        
        // Direção do vento
        const windDirElement = document.getElementById('wind-direction');
        if (windDirElement && current.winddirection) {
            windDirElement.textContent = `Direção: ${this.getWindDirection(current.winddirection)}`;
        }
        
        // Sensação térmica
        const feelsLikeElement = document.getElementById('feels-like');
        if (feelsLikeElement && hourly.relative_humidity_2m && hourly.relative_humidity_2m[0] && hourly.wind_speed_10m && hourly.wind_speed_10m[0]) {
            const feelsLike = this.calculateFeelsLike(
                current.temperature,
                hourly.wind_speed_10m[0],
                hourly.relative_humidity_2m[0]
            );
            feelsLikeElement.textContent = `Sensação: ${Math.round(feelsLike)}°`;
        }
    }

    updateHourlyForecast() {
        if (!this.weatherData?.hourly) return;
        
        const hourly = this.weatherData.hourly;
        const slider = document.getElementById('hourly-slider');
        
        if (!slider) return;
        
        let html = '<div class="hourly-slider-container">';
        
        // Mostrar próximas 12 horas
        const hoursToShow = Math.min(12, hourly.time.length);
        
        for (let i = 0; i < hoursToShow; i++) {
            const time = new Date(hourly.time[i]);
            const hour = time.getHours().toString().padStart(2, '0');
            const temp = Math.round(hourly.temperature_2m[i]);
            const weatherCode = hourly.weather_code[i];
            const precipitation = hourly.precipitation_probability[i] || 0;
            const isDay = this.isDayTime(time);
            
            html += `
                <div class="hourly-item">
                    <div class="hour">${hour}:00</div>
                    <div class="weather-icon">
                        <i class="${this.getWeatherIcon(weatherCode, isDay)}"></i>
                    </div>
                    <div class="temperature">${temp}°</div>
                    <div class="precipitation">${precipitation}%</div>
                </div>
            `;
        }
        
        html += '</div>';
        slider.innerHTML = html;
    }

    updateDailyForecast() {
        if (!this.weatherData?.daily) return;
        
        const daily = this.weatherData.daily;
        const table = document.getElementById('daily-forecast-table')?.querySelector('tbody');
        
        if (!table) return;
        
        let html = '';
        
        const daysToShow = Math.min(7, daily.time.length);
        
        for (let i = 0; i < daysToShow; i++) {
            const date = new Date(daily.time[i]);
            const dayName = date.toLocaleDateString('pt-BR', { weekday: 'short' });
            const dayNumber = date.getDate();
            const month = date.getMonth() + 1;
            
            const maxTemp = Math.round(daily.temperature_2m_max[i]);
            const minTemp = Math.round(daily.temperature_2m_min[i]);
            const weatherCode = daily.weather_code[i];
            const precipitation = daily.precipitation_sum[i] || 0;
            const windSpeed = daily.wind_speed_10m_max ? Math.round(daily.wind_speed_10m_max[i]) : 0;

            html += `
                <tr>
                    <td>
                        <strong>${dayName}</strong><br>
                        <small>${dayNumber}/${month}</small>
                    </td>
                    <td>
                        <i class="${this.getWeatherIcon(weatherCode, true)} me-2"></i>
                        ${this.getWeatherDescription(weatherCode)}
                    </td>
                    <td><strong>${maxTemp}°</strong></td>
                    <td>${minTemp}°</td>
                    <td>${precipitation} mm</td>
                    <td>${windSpeed} km/h</td>
                    <td>06:00</td>
                    <td>18:00</td>
                </tr>
            `;
        }
        
        table.innerHTML = html;
    }

    updateLocationInfo() {
        // Nome da cidade
        const cityElement = document.getElementById('city-name');
        if (cityElement) {
            cityElement.textContent = this.currentLocation.name;
        }
        
        // Coordenadas
        const coordElement = document.getElementById('coordinates');
        if (coordElement) {
            coordElement.textContent = 
                `${this.currentLocation.lat.toFixed(4)}°, ${this.currentLocation.lon.toFixed(4)}°`;
        }
    }

    initMap() {
        const mapElement = document.getElementById('location-map');
        if (!mapElement || typeof L === 'undefined') return;
        
        // Verificar se o mapa já foi inicializado
        if (this.map) {
            this.map.remove();
        }
        
        // Corrigir ícones do Leaflet
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        });
        
        // Criar mapa
        this.map = L.map('location-map').setView([this.currentLocation.lat, this.currentLocation.lon], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(this.map);
        
        // Adicionar marcador
        L.marker([this.currentLocation.lat, this.currentLocation.lon])
            .addTo(this.map)
            .bindPopup(this.currentLocation.name)
            .openPopup();
    }

    updateMap() {
        if (!this.map || !this.currentLocation) return;
        
        this.map.setView([this.currentLocation.lat, this.currentLocation.lon], 10);
        
        // Limpar marcadores existentes
        this.map.eachLayer((layer) => {
            if (layer instanceof L.Marker) {
                this.map.removeLayer(layer);
            }
        });
        
        // Adicionar novo marcador
        L.marker([this.currentLocation.lat, this.currentLocation.lon])
            .addTo(this.map)
            .bindPopup(this.currentLocation.name)
            .openPopup();
    }

    async searchCity() {
        const input = document.getElementById('global-search');
        const cityName = input?.value.trim();
        
        if (!cityName) {
            this.showNotification('Digite o nome de uma cidade', 'warning');
            return;
        }
        
        try {
            this.showLoading(true);
            
            // Usar geocoding API
            const url = `${GEOCODING_URL}?name=${encodeURIComponent(cityName)}&count=1&language=pt&format=json`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error('Cidade não encontrada');
            }
            
            const data = await response.json();
            
            if (!data.results || data.results.length === 0) {
                throw new Error('Cidade não encontrada');
            }
            
            const result = data.results[0];
            const lat = result.latitude;
            const lon = result.longitude;
            const name = result.name;
            
            // Atualizar localização
            this.currentLocation = { lat, lon, name };
            
            // Buscar dados do clima
            await this.fetchWeatherData(lat, lon, name);
            
            // Limpar campo de busca
            if (input) input.value = '';
            
        } catch (error) {
            console.error('Erro ao buscar cidade:', error);
            this.showLoading(false);
            this.showNotification('Cidade não encontrada. Tente novamente.', 'error');
        }
    }

    getCurrentLocation() {
        if (!navigator.geolocation) {
            this.showNotification('Geolocalização não suportada pelo navegador', 'error');
            return;
        }
        
        this.showLoading(true);
        
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                try {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    // Usar geocoding reverso para obter nome da cidade
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10`;
                    const response = await fetch(url, {
                        headers: {
                            'Accept-Language': 'pt-BR'
                        }
                    });
                    
                    let cityName = 'Minha Localização';
                    if (response.ok) {
                        const data = await response.json();
                        cityName = data.address?.city || data.address?.town || data.address?.village || 'Localização Atual';
                    }
                    
                    this.currentLocation = { lat, lon, name: cityName };
                    await this.fetchWeatherData(lat, lon, cityName);
                    
                } catch (error) {
                    console.error('Erro ao obter localização:', error);
                    this.showLoading(false);
                    this.showNotification('Erro ao obter localização', 'error');
                }
            },
            (error) => {
                console.error('Erro de geolocalização:', error);
                this.showLoading(false);
                this.showNotification('Não foi possível obter sua localização', 'error');
            }
        );
    }

    handleLocationSelect(value) {
        if (value === 'auto') {
            this.getCurrentLocation();
        } else if (value) {
            const [lat, lon] = value.split(',').map(Number);
            const name = this.getCityNameFromCoords(lat, lon);
            this.currentLocation = { lat, lon, name };
            this.fetchWeatherData(lat, lon, name);
        }
    }

    getCityNameFromCoords(lat, lon) {
        const cities = {
            '-23.5505,-46.6333': 'São Paulo, Brasil',
            '-22.9068,-43.1729': 'Rio de Janeiro, Brasil',
            '-15.7801,-47.9292': 'Brasília, Brasil',
            '40.7128,-74.0060': 'Nova York, EUA',
            '51.5074,-0.1278': 'Londres, UK'
        };
        
        const key = `${lat},${lon}`;
        return cities[key] || `Lat: ${lat}, Lon: ${lon}`;
    }

    toggleDarkMode(enabled) {
        const themeCss = document.getElementById('theme-css');
        const body = document.body;
        
        if (enabled) {
            body.setAttribute('data-theme', 'dark');
            themeCss.href = 'assets/css/themes/dark.css';
            localStorage.setItem('theme', 'dark');
        } else {
            body.setAttribute('data-theme', 'light');
            themeCss.href = 'assets/css/themes/light.css';
            localStorage.setItem('theme', 'light');
        }
    }

    changeUnits(units) {
        console.log('Mudando unidades para:', units);
        // Recarregar dados com novas unidades
        if (this.currentLocation) {
            this.fetchWeatherData(this.currentLocation.lat, this.currentLocation.lon);
        }
    }

    changeLanguage(lang) {
        console.log('Mudando idioma para:', lang);
        this.showNotification('Idioma alterado para ' + lang, 'info');
    }

    refreshWeather() {
        if (this.currentLocation) {
            this.fetchWeatherData(this.currentLocation.lat, this.currentLocation.lon);
        }
    }

    loadHistory() {
        console.log('Carregando histórico...');
        this.showNotification('Funcionalidade de histórico em desenvolvimento', 'info');
    }

    // Helper functions
    getWeatherIcon(weatherCode, isDay = true) {
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
        
        return icons[weatherCode] || 'fas fa-question-circle';
    }

    getWeatherDescription(weatherCode) {
        const descriptions = {
            0: 'Céu limpo',
            1: 'Principalmente limpo',
            2: 'Parcialmente nublado',
            3: 'Nublado',
            45: 'Nevoeiro',
            48: 'Nevoeiro com geada',
            51: 'Chuvisco leve',
            53: 'Chuvisco moderado',
            55: 'Chuvisco denso',
            61: 'Chuva leve',
            63: 'Chuva moderada',
            65: 'Chuva forte',
            71: 'Queda de neve leve',
            73: 'Queda de neve moderada',
            75: 'Queda de neve forte',
            77: 'Grãos de neve',
            80: 'Pancadas de chuva leves',
            81: 'Pancadas de chuva moderadas',
            82: 'Pancadas de chuva violentas',
            85: 'Pancadas de neve leves',
            86: 'Pancadas de neve fortes',
            95: 'Tempestade',
            96: 'Tempestade com granizo leve',
            99: 'Tempestade com granizo forte'
        };
        
        return descriptions[weatherCode] || 'Condição desconhecida';
    }

    getWindDirection(degrees) {
        const directions = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'];
        const index = Math.round((degrees % 360) / 45);
        return directions[index % 8];
    }

    calculateFeelsLike(temperature, windSpeed, humidity) {
        // Fórmula simplificada
        let feelsLike = temperature;
        
        if (temperature < 10 && windSpeed > 5) {
            feelsLike = 13.12 + 0.6215 * temperature - 11.37 * Math.pow(windSpeed, 0.16) 
                      + 0.3965 * temperature * Math.pow(windSpeed, 0.16);
        }
        
        if (temperature > 27 && humidity > 40) {
            feelsLike = -42.379 + 2.04901523 * temperature + 10.14333127 * humidity 
                      - 0.22475541 * temperature * humidity;
        }
        
        return Math.round(feelsLike);
    }

    isDayTime(date = new Date()) {
        const hour = date.getHours();
        return hour >= 6 && hour < 18;
    }

    showLoading(show) {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
            loadingScreen.style.display = show ? 'flex' : 'none';
        }
    }

    hideLoadingScreen() {
        setTimeout(() => {
            this.showLoading(false);
        }, 1000);
    }

    showNotification(message, type = 'info') {
        const container = document.getElementById('notification-container');
        if (!container) return;
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(notification);
        
        // Adicionar evento de fechar
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
        
        // Auto-remover após 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    useSampleData() {
        console.log('Usando dados de exemplo');
        
        // Dados de exemplo para desenvolvimento
        this.weatherData = {
            current_weather: {
                temperature: 22,
                windspeed: 12,
                winddirection: 180,
                weathercode: 1,
                time: new Date().toISOString()
            },
            hourly: {
                time: Array.from({ length: 24 }, (_, i) => {
                    const date = new Date();
                    date.setHours(date.getHours() + i);
                    return date.toISOString();
                }),
                temperature_2m: Array.from({ length: 24 }, () => 20 + Math.random() * 10),
                relative_humidity_2m: Array.from({ length: 24 }, () => 50 + Math.random() * 30),
                precipitation_probability: Array.from({ length: 24 }, () => Math.random() * 30),
                weather_code: Array.from({ length: 24 }, () => Math.floor(Math.random() * 4)),
                wind_speed_10m: Array.from({ length: 24 }, () => 5 + Math.random() * 15),
                wind_direction_10m: Array.from({ length: 24 }, () => Math.random() * 360)
            },
            daily: {
                time: Array.from({ length: 7 }, (_, i) => {
                    const date = new Date();
                    date.setDate(date.getDate() + i);
                    return date.toISOString().split('T')[0];
                }),
                weather_code: Array.from({ length: 7 }, () => Math.floor(Math.random() * 4)),
                temperature_2m_max: Array.from({ length: 7 }, () => 25 + Math.random() * 10),
                temperature_2m_min: Array.from({ length: 7 }, () => 15 + Math.random() * 5),
                precipitation_sum: Array.from({ length: 7 }, () => Math.random() * 5),
                wind_speed_10m_max: Array.from({ length: 7 }, () => 10 + Math.random() * 15)
            }
        };
        
        this.updateWeatherDisplay();
        this.updateHourlyForecast();
        this.updateDailyForecast();
        this.showNotification('Usando dados de exemplo', 'warning');
    }
}

// Inicializar sistema quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }
    
    // Inicializar contadores de estatísticas
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count')) || 0;
        let count = 0;
        const increment = target / 50;
        
        const updateCount = () => {
            if (count < target) {
                count += increment;
                counter.innerText = Math.ceil(count);
                setTimeout(updateCount, 20);
            } else {
                counter.innerText = target;
            }
        };
        
        updateCount();
    });
    
    // Inicializar sistema meteorológico
    window.weatherSystem = new WeatherSystem();
    
    // Expor funções globais
    window.getCurrentLocation = () => weatherSystem.getCurrentLocation();
    window.refreshWeather = () => weatherSystem.refreshWeather();
    window.searchCity = () => weatherSystem.searchCity();
    window.toggleDarkMode = (enabled) => weatherSystem.toggleDarkMode(enabled);
    window.changeUnits = (units) => weatherSystem.changeUnits(units);
    window.changeLanguage = (lang) => weatherSystem.changeLanguage(lang);
    window.loadHistory = () => weatherSystem.loadHistory();
    
    console.log('WeatherMaster Pro inicializado com sucesso!');
});

// Remover preload class após carregamento
window.addEventListener('load', function() {
    setTimeout(() => {
        document.body.classList.remove('preload');
    }, 500);
});