// geolocation.js - Gerenciamento de geolocalização
class GeolocationManager {
    constructor(weatherSystem) {
        this.weatherSystem = weatherSystem;
        this.currentPosition = null;
        this.watchId = null;
        this.permissionStatus = null;
        this.initialize();
    }

    initialize() {
        this.checkGeolocationSupport();
        this.setupEventListeners();
        this.loadSavedLocation();
    }

    checkGeolocationSupport() {
        if (!navigator.geolocation) {
            console.warn('Geolocalização não suportada pelo navegador');
            this.showGeolocationWarning();
            return false;
        }
        return true;
    }

    setupEventListeners() {
        // Botão de localização
        const locationButton = document.getElementById('useLocation');
        if (locationButton) {
            locationButton.addEventListener('click', () => this.getCurrentPosition());
        }

        // Monitorar mudanças de permissão
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' })
                .then(permissionStatus => {
                    this.permissionStatus = permissionStatus;
                    permissionStatus.onchange = () => this.handlePermissionChange();
                });
        }

        // Monitorar conexão
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }

    async getCurrentPosition(options = {}) {
        if (!this.checkGeolocationSupport()) {
            return Promise.reject(new Error('Geolocalização não suportada'));
        }

        const defaultOptions = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        const finalOptions = { ...defaultOptions, ...options };

        return new Promise((resolve, reject) => {
            this.weatherSystem.showLoading(true);
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.handleSuccess(position);
                    this.weatherSystem.showLoading(false);
                    resolve(position);
                },
                (error) => {
                    this.handleError(error);
                    this.weatherSystem.showLoading(false);
                    reject(error);
                },
                finalOptions
            );
        });
    }

    startWatchingPosition(options = {}) {
        if (!this.checkGeolocationSupport() || this.watchId !== null) {
            return;
        }

        const defaultOptions = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        const finalOptions = { ...defaultOptions, ...options };

        this.watchId = navigator.geolocation.watchPosition(
            (position) => this.handleSuccess(position),
            (error) => this.handleError(error),
            finalOptions
        );

        console.log('Monitoramento de localização iniciado:', this.watchId);
    }

    stopWatchingPosition() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
            console.log('Monitoramento de localização parado');
        }
    }

    handleSuccess(position) {
        this.currentPosition = position;
        
        const coords = position.coords;
        const timestamp = new Date(position.timestamp);
        
        console.log('Localização obtida:', {
            latitude: coords.latitude,
            longitude: coords.longitude,
            accuracy: coords.accuracy,
            timestamp: timestamp.toLocaleString()
        });

        // Salvar localização
        this.saveLocation(coords);
        
        // Atualizar interface
        this.updatePositionDisplay(coords);
        
        // Buscar nome da localização
        this.getLocationName(coords.latitude, coords.longitude)
            .then(locationName => {
                this.weatherSystem.currentLocation = {
                    lat: coords.latitude,
                    lon: coords.longitude,
                    name: locationName
                };
                
                // Buscar dados do clima
                this.weatherSystem.fetchWeatherData(coords.latitude, coords.longitude);
                
                // Mostrar notificação
                this.weatherSystem.showNotification(`Localização detectada: ${locationName}`, 'success');
            })
            .catch(error => {
                console.warn('Não foi possível obter nome da localização:', error);
                
                this.weatherSystem.currentLocation = {
                    lat: coords.latitude,
                    lon: coords.longitude,
                    name: 'Minha Localização'
                };
                
                this.weatherSystem.fetchWeatherData(coords.latitude, coords.longitude);
            });

        // Atualizar mapa
        this.updateMap(coords);
    }

    handleError(error) {
        console.error('Erro de geolocalização:', error);
        
        let errorMessage = 'Erro ao obter localização';
        
        switch (error.code) {
            case error.PERMISSION_DENIED:
                errorMessage = 'Permissão de localização negada. Ative a localização nas configurações do navegador.';
                this.showPermissionInstructions();
                break;
            case error.POSITION_UNAVAILABLE:
                errorMessage = 'Informação de localização indisponível.';
                break;
            case error.TIMEOUT:
                errorMessage = 'Tempo esgotado ao buscar localização.';
                break;
            default:
                errorMessage = 'Erro desconhecido ao obter localização.';
        }
        
        this.weatherSystem.showNotification(errorMessage, 'error');
        
        // Tentar usar IP para localização aproximada
        this.fallbackToIPLocation();
    }

    async getLocationName(latitude, longitude) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=10&addressdetails=1`,
                {
                    headers: {
                        'Accept-Language': 'pt-BR',
                        'User-Agent': 'WeatherSystem/1.0'
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error('Erro na API de geocodificação reversa');
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Extrair nome da localização
            const address = data.address;
            let locationName = '';
            
            if (address.city) {
                locationName = address.city;
            } else if (address.town) {
                locationName = address.town;
            } else if (address.village) {
                locationName = address.village;
            } else if (address.municipality) {
                locationName = address.municipality;
            }
            
            if (address.state && locationName) {
                locationName += `, ${address.state}`;
            }
            
            if (address.country && locationName) {
                locationName += `, ${address.country}`;
            }
            
            return locationName || 'Localização desconhecida';
            
        } catch (error) {
            console.error('Erro ao obter nome da localização:', error);
            throw error;
        }
    }

    async fallbackToIPLocation() {
        try {
            this.weatherSystem.showNotification('Usando localização por IP...', 'info');
            
            const response = await fetch('https://ipapi.co/json/');
            
            if (!response.ok) {
                throw new Error('Erro ao obter localização por IP');
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            const locationName = `${data.city}, ${data.region}, ${data.country_name}`;
            
            this.weatherSystem.currentLocation = {
                lat: data.latitude,
                lon: data.longitude,
                name: locationName
            };
            
            this.weatherSystem.fetchWeatherData(data.latitude, data.longitude);
            this.weatherSystem.showNotification(`Localização aproximada: ${locationName}`, 'warning');
            
        } catch (error) {
            console.error('Erro na localização por IP:', error);
            this.weatherSystem.showNotification('Não foi possível determinar sua localização', 'error');
        }
    }

    saveLocation(coords) {
        const locationData = {
            latitude: coords.latitude,
            longitude: coords.longitude,
            accuracy: coords.accuracy,
            altitude: coords.altitude,
            altitudeAccuracy: coords.altitudeAccuracy,
            heading: coords.heading,
            speed: coords.speed,
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem('lastKnownLocation', JSON.stringify(locationData));
    }

    loadSavedLocation() {
        try {
            const saved = localStorage.getItem('lastKnownLocation');
            if (saved) {
                const locationData = JSON.parse(saved);
                this.currentPosition = {
                    coords: locationData,
                    timestamp: new Date(locationData.timestamp).getTime()
                };
                
                // Verificar se a localização ainda é recente (menos de 1 hora)
                const age = Date.now() - new Date(locationData.timestamp).getTime();
                if (age < 3600000) { // 1 hora em milissegundos
                    console.log('Usando localização salva:', locationData);
                    return locationData;
                }
            }
        } catch (error) {
            console.warn('Erro ao carregar localização salva:', error);
        }
        
        return null;
    }

    updatePositionDisplay(coords) {
        const elements = {
            'current-lat': coords.latitude.toFixed(6),
            'current-lon': coords.longitude.toFixed(6),
            'current-accuracy': `${Math.round(coords.accuracy)}m`,
            'current-altitude': coords.altitude ? `${Math.round(coords.altitude)}m` : 'N/A',
            'current-heading': coords.heading ? `${Math.round(coords.heading)}°` : 'N/A',
            'current-speed': coords.speed ? `${Math.round(coords.speed * 3.6)} km/h` : 'N/A'
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    updateMap(coords) {
        if (!this.weatherSystem.map || typeof L === 'undefined') return;
        
        // Atualizar vista do mapa
        this.weatherSystem.map.setView([coords.latitude, coords.longitude], 13);
        
        // Remover marcador anterior
        if (this.marker) {
            this.weatherSystem.map.removeLayer(this.marker);
        }
        
        // Adicionar novo marcador
        this.marker = L.marker([coords.latitude, coords.longitude])
            .addTo(this.weatherSystem.map)
            .bindPopup('Sua localização atual')
            .openPopup();
        
        // Adicionar círculo de precisão
        if (this.accuracyCircle) {
            this.weatherSystem.map.removeLayer(this.accuracyCircle);
        }
        
        this.accuracyCircle = L.circle([coords.latitude, coords.longitude], {
            color: '#3498db',
            fillColor: '#3498db',
            fillOpacity: 0.1,
            radius: coords.accuracy || 50
        }).addTo(this.weatherSystem.map);
    }

    handlePermissionChange() {
        if (this.permissionStatus) {
            console.log('Status da permissão alterado:', this.permissionStatus.state);
            
            switch (this.permissionStatus.state) {
                case 'granted':
                    this.weatherSystem.showNotification('Permissão de localização concedida', 'success');
                    this.getCurrentPosition();
                    break;
                case 'denied':
                    this.weatherSystem.showNotification('Permissão de localização negada', 'error');
                    this.showPermissionInstructions();
                    break;
                case 'prompt':
                    this.weatherSystem.showNotification('Aguardando permissão de localização', 'info');
                    break;
            }
        }
    }

    showPermissionInstructions() {
        const instructions = `
            <div class="permission-instructions">
                <h4>Ativar Localização</h4>
                <p>Para usar a localização precisa, permita o acesso:</p>
                <ul>
                    <li><strong>Chrome:</strong> Configurações → Privacidade e segurança → Configurações do site → Localização</li>
                    <li><strong>Firefox:</strong> Opções → Privacidade e segurança → Permissões → Localização</li>
                    <li><strong>Safari:</strong> Preferências → Websites → Localização</li>
                </ul>
                <p>Ou clique no ícone de cadeado na barra de endereços.</p>
            </div>
        `;
        
        this.weatherSystem.showModal('Configurar Localização', instructions);
    }

    showGeolocationWarning() {
        const warning = `
            <div class="geolocation-warning">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h4>Geolocalização Não Suportada</h4>
                <p>Seu navegador não suporta geolocalização ou está bloqueado.</p>
                <p>Você pode:</p>
                <ul>
                    <li>Atualizar para um navegador mais recente</li>
                    <li>Permitir geolocalização nas configurações</li>
                    <li>Buscar uma cidade manualmente</li>
                </ul>
                <button class="btn btn-primary mt-3" onclick="weatherSystem.searchCity()">
                    <i class="fas fa-search me-2"></i>Buscar Cidade
                </button>
            </div>
        `;
        
        this.weatherSystem.showModal('Aviso de Geolocalização', warning);
    }

    handleOnline() {
        console.log('Conexão restabelecida');
        
        if (this.currentPosition) {
            // Tentar obter localização mais precisa
            this.getCurrentPosition({ enableHighAccuracy: false });
        }
    }

    handleOffline() {
        console.log('Conexão perdida');
        this.weatherSystem.showNotification('Modo offline. Usando última localização conhecida.', 'warning');
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        // Fórmula de Haversine para calcular distância em metros
        const R = 6371000; // Raio da Terra em metros
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    }

    getBearing(lat1, lon1, lat2, lon2) {
        // Calcular direção (bearing) entre dois pontos
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const λ1 = lon1 * Math.PI / 180;
        const λ2 = lon2 * Math.PI / 180;

        const y = Math.sin(λ2 - λ1) * Math.cos(φ2);
        const x = Math.cos(φ1) * Math.sin(φ2) -
                Math.sin(φ1) * Math.cos(φ2) * Math.cos(λ2 - λ1);
        
        let θ = Math.atan2(y, x);
        θ = θ * 180 / Math.PI;
        
        return (θ + 360) % 360;
    }

    formatDistance(meters) {
        if (meters < 1000) {
            return `${Math.round(meters)} metros`;
        } else {
            return `${(meters / 1000).toFixed(1)} km`;
        }
    }

    formatBearing(degrees) {
        const directions = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'];
        const index = Math.round((degrees % 360) / 45);
        return directions[index % 8];
    }

    // Métodos estáticos para uso externo
    static async getCityCoordinates(cityName) {
        try {
            const response = await fetch(
                `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(cityName)}&count=1&language=pt`
            );
            
            if (!response.ok) {
                throw new Error('Erro na geocodificação');
            }
            
            const data = await response.json();
            
            if (!data.results || data.results.length === 0) {
                throw new Error('Cidade não encontrada');
            }
            
            return {
                lat: data.results[0].latitude,
                lon: data.results[0].longitude,
                name: data.results[0].name
            };
            
        } catch (error) {
            console.error('Erro ao obter coordenadas da cidade:', error);
            throw error;
        }
    }

    static async searchCities(query, limit = 5) {
        try {
            const response = await fetch(
                `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(query)}&count=${limit}&language=pt`
            );
            
            if (!response.ok) {
                throw new Error('Erro na busca de cidades');
            }
            
            const data = await response.json();
            
            return data.results || [];
            
        } catch (error) {
            console.error('Erro na busca de cidades:', error);
            return [];
        }
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    window.geolocationManager = new GeolocationManager(window.weatherSystem);
});

// Expor métodos globais
window.getCurrentPosition = (options) => geolocationManager.getCurrentPosition(options);
window.startWatchingPosition = (options) => geolocationManager.startWatchingPosition(options);
window.stopWatchingPosition = () => geolocationManager.stopWatchingPosition();