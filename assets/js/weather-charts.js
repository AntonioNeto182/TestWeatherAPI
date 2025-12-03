// weather-charts.js - Gerenciamento de gráficos meteorológicos
class WeatherCharts {
    constructor(weatherSystem) {
        this.weatherSystem = weatherSystem;
        this.charts = {};
        this.initialize();
    }

    initialize() {
        this.setupChartDefaults();
        this.createCharts();
    }

    setupChartDefaults() {
        // Configurações padrão para Chart.js
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = 'rgba(255, 255, 255, 0.7)';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
        
        // Plugin para gradientes
        this.registerGradientPlugin();
    }

    registerGradientPlugin() {
        const gradientPlugin = {
            id: 'chartGradient',
            beforeDraw: (chart) => {
                const ctx = chart.ctx;
                const chartArea = chart.chartArea;
                
                if (!chartArea) return;
                
                // Aplicar gradiente de fundo
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(255, 255, 255, 0.05)');
                gradient.addColorStop(1, 'rgba(255, 255, 255, 0.01)');
                
                ctx.save();
                ctx.fillStyle = gradient;
                ctx.fillRect(chartArea.left, chartArea.top, chartArea.right - chartArea.left, chartArea.bottom - chartArea.top);
                ctx.restore();
            }
        };
        
        Chart.register(gradientPlugin);
    }

    createCharts() {
        // Temperatura
        this.createTemperatureChart();
        
        // Precipitação
        this.createPrecipitationChart();
        
        // Vento
        this.createWindChart();
        
        // Umidade
        this.createHumidityChart();
        
        // Pressão
        this.createPressureChart();
    }

    createTemperatureChart() {
        const canvas = document.getElementById('temperature-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        this.charts.temperature = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Temperatura',
                        data: [],
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#e74c3c',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Sensação Térmica',
                        data: [],
                        borderColor: '#f39c12',
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        hidden: true
                    }
                ]
            },
            options: this.getChartOptions('Temperatura (°C)')
        });
    }

    createPrecipitationChart() {
        const canvas = document.getElementById('precipitation-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        this.charts.precipitation = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Precipitação',
                        data: [],
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: '#3498db',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Probabilidade',
                        data: [],
                        type: 'line',
                        borderColor: '#9b59b6',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: this.getChartOptions('Precipitação (mm)', true)
        });
    }

    createWindChart() {
        const canvas = document.getElementById('wind-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        this.charts.wind = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Velocidade do Vento',
                        data: [],
                        borderColor: '#1abc9c',
                        backgroundColor: 'rgba(26, 188, 156, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Rajadas',
                        data: [],
                        borderColor: '#f1c40f',
                        backgroundColor: 'transparent',
                        borderWidth: 1,
                        borderDash: [3, 3],
                        tension: 0.4
                    }
                ]
            },
            options: this.getChartOptions('Vento (km/h)')
        });
    }

    createHumidityChart() {
        const canvas = document.getElementById('humidity-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        this.charts.humidity = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Umidade Relativa',
                    data: [],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: this.getChartOptions('Umidade (%)')
        });
    }

    createPressureChart() {
        const canvas = document.getElementById('pressure-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        this.charts.pressure = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Pressão Atmosférica',
                    data: [],
                    borderColor: '#8e44ad',
                    backgroundColor: 'rgba(142, 68, 173, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: this.getChartOptions('Pressão (hPa)')
        });
    }

    getChartOptions(title, dualY = false) {
        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: 'rgba(255, 255, 255, 0.8)',
                        font: {
                            size: 12
                        },
                        padding: 20,
                        usePointStyle: true
                    }
                },
                title: {
                    display: !!title,
                    text: title,
                    color: '#ffffff',
                    font: {
                        size: 16,
                        weight: '600'
                    },
                    padding: {
                        bottom: 20
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(44, 62, 80, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#3498db',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (context) => {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y;
                            
                            // Adicionar unidade baseada no título
                            if (title.includes('Temperatura')) {
                                label += '°C';
                            } else if (title.includes('Precipitação')) {
                                label += context.datasetIndex === 0 ? ' mm' : ' %';
                            } else if (title.includes('Vento')) {
                                label += ' km/h';
                            } else if (title.includes('Umidade')) {
                                label += ' %';
                            } else if (title.includes('Pressão')) {
                                label += ' hPa';
                            }
                            
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.6)',
                        maxRotation: 0,
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.6)',
                        font: {
                            size: 11
                        },
                        callback: (value) => {
                            if (title.includes('Temperatura')) return value + '°';
                            if (title.includes('Precipitação')) return value + ' mm';
                            if (title.includes('Vento')) return value + ' km/h';
                            if (title.includes('Umidade')) return value + ' %';
                            if (title.includes('Pressão')) return value + ' hPa';
                            return value;
                        }
                    },
                    beginAtZero: title.includes('Precipitação') || title.includes('Umidade')
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            elements: {
                point: {
                    hoverRadius: 6,
                    hoverBorderWidth: 3
                }
            },
            animations: {
                tension: {
                    duration: 1000,
                    easing: 'linear'
                }
            }
        };

        if (dualY) {
            options.scales.y1 = {
                position: 'right',
                grid: {
                    drawOnChartArea: false
                },
                ticks: {
                    color: 'rgba(255, 255, 255, 0.6)',
                    callback: (value) => value + '%'
                },
                beginAtZero: true,
                max: 100
            };
        }

        return options;
    }

    updateAllCharts(weatherData) {
        if (!weatherData) return;

        this.updateTemperatureChart(weatherData);
        this.updatePrecipitationChart(weatherData);
        this.updateWindChart(weatherData);
        this.updateHumidityChart(weatherData);
        this.updatePressureChart(weatherData);
    }

    updateTemperatureChart(weatherData) {
        const chart = this.charts.temperature;
        if (!chart || !weatherData.hourly) return;

        const hourly = weatherData.hourly;
        const hours = 24;
        
        const labels = [];
        const temperatures = [];
        const feelsLike = [];

        for (let i = 0; i < hours; i++) {
            const time = new Date(hourly.time[i]);
            labels.push(time.getHours().toString().padStart(2, '0') + ':00');
            temperatures.push(hourly.temperature_2m[i]);
            
            // Calcular sensação térmica
            const temp = hourly.temperature_2m[i];
            const wind = hourly.windspeed_10m[i];
            const humidity = hourly.relativehumidity_2m[i];
            feelsLike.push(this.calculateFeelsLike(temp, wind, humidity));
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = temperatures;
        chart.data.datasets[1].data = feelsLike;
        chart.update();
    }

    updatePrecipitationChart(weatherData) {
        const chart = this.charts.precipitation;
        if (!chart || !weatherData.hourly) return;

        const hourly = weatherData.hourly;
        const hours = 24;
        
        const labels = [];
        const precipitation = [];
        const probability = [];

        for (let i = 0; i < hours; i++) {
            const time = new Date(hourly.time[i]);
            labels.push(time.getHours().toString().padStart(2, '0') + ':00');
            
            // Para a API Open-Meteo, precipitation é probabilidade
            // Vamos simular valores de precipitação baseados na probabilidade
            precipitation.push(hourly.precipitation_probability[i] * 0.1); // Convertendo para mm
            probability.push(hourly.precipitation_probability[i]);
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = precipitation;
        chart.data.datasets[1].data = probability;
        chart.update();
    }

    updateWindChart(weatherData) {
        const chart = this.charts.wind;
        if (!chart || !weatherData.hourly) return;

        const hourly = weatherData.hourly;
        const hours = 24;
        
        const labels = [];
        const windSpeed = [];
        const windGusts = [];

        for (let i = 0; i < hours; i++) {
            const time = new Date(hourly.time[i]);
            labels.push(time.getHours().toString().padStart(2, '0') + ':00');
            windSpeed.push(hourly.windspeed_10m[i]);
            
            // Para a API Open-Meteo, usar um valor aumentado para rajadas
            windGusts.push(hourly.windspeed_10m[i] * 1.3);
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = windSpeed;
        chart.data.datasets[1].data = windGusts;
        chart.update();
    }

    updateHumidityChart(weatherData) {
        const chart = this.charts.humidity;
        if (!chart || !weatherData.hourly) return;

        const hourly = weatherData.hourly;
        const hours = 24;
        
        const labels = [];
        const humidity = [];

        for (let i = 0; i < hours; i++) {
            const time = new Date(hourly.time[i]);
            labels.push(time.getHours().toString().padStart(2, '0') + ':00');
            humidity.push(hourly.relativehumidity_2m[i]);
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = humidity;
        chart.update();
    }

    updatePressureChart(weatherData) {
        const chart = this.charts.pressure;
        if (!chart) return;

        // A API Open-Meteo não fornece pressão na versão gratuita
        // Vamos simular dados baseados na temperatura
        if (weatherData.hourly) {
            const hourly = weatherData.hourly;
            const hours = 24;
            
            const labels = [];
            const pressure = [];

            const basePressure = 1013; // Pressão padrão ao nível do mar
            
            for (let i = 0; i < hours; i++) {
                const time = new Date(hourly.time[i]);
                labels.push(time.getHours().toString().padStart(2, '0') + ':00');
                
                // Variação símile baseada na temperatura
                const temp = hourly.temperature_2m[i];
                const variation = (temp - 20) * 0.5; // 0.5 hPa por grau de diferença
                pressure.push(basePressure + variation);
            }

            chart.data.labels = labels;
            chart.data.datasets[0].data = pressure;
            chart.update();
        }
    }

    calculateFeelsLike(temperature, windSpeed, humidity) {
        // Fórmula simplificada para sensação térmica
        let feelsLike = temperature;
        
        // Efeito do vento (Wind Chill) - só abaixo de 10°C
        if (temperature < 10 && windSpeed > 5) {
            feelsLike = 13.12 + 0.6215 * temperature - 11.37 * Math.pow(windSpeed, 0.16) + 0.3965 * temperature * Math.pow(windSpeed, 0.16);
        }
        
        // Efeito da umidade (Heat Index) - só acima de 27°C
        if (temperature > 27 && humidity > 40) {
            feelsLike = -42.379 + 2.04901523 * temperature + 10.14333127 * humidity 
                - 0.22475541 * temperature * humidity - 6.83783 * Math.pow(10, -3) * Math.pow(temperature, 2)
                - 5.481717 * Math.pow(10, -2) * Math.pow(humidity, 2) + 1.22874 * Math.pow(10, -3) * Math.pow(temperature, 2) * humidity
                + 8.5282 * Math.pow(10, -4) * temperature * Math.pow(humidity, 2) - 1.99 * Math.pow(10, -6) * Math.pow(temperature, 2) * Math.pow(humidity, 2);
        }
        
        return Math.round(feelsLike * 10) / 10;
    }

    createComparisonChart(citiesData) {
        const canvas = document.getElementById('comparison-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        // Preparar dados
        const datasets = [];
        const colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6'];
        
        citiesData.forEach((cityData, index) => {
            datasets.push({
                label: cityData.name,
                data: cityData.temperatures,
                borderColor: colors[index % colors.length],
                backgroundColor: colors[index % colors.length] + '20',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            });
        });

        // Criar gráfico de comparação
        this.charts.comparison = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                datasets: datasets
            },
            options: this.getChartOptions('Comparação de Temperaturas')
        });
    }

    createRadialGauge(value, max, label) {
        const canvas = document.createElement('canvas');
        canvas.width = 200;
        canvas.height = 200;
        
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 80;
        
        // Fundo do gauge
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
        ctx.lineWidth = 10;
        ctx.stroke();
        
        // Valor
        const percentage = value / max;
        const endAngle = Math.PI * 2 * percentage;
        
        // Arco de valor
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, -Math.PI / 2, -Math.PI / 2 + endAngle);
        ctx.strokeStyle = this.getGaugeColor(percentage);
        ctx.lineWidth = 10;
        ctx.lineCap = 'round';
        ctx.stroke();
        
        // Texto do valor
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 24px Poppins';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(value, centerX, centerY);
        
        // Rótulo
        ctx.font = '14px Poppins';
        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.fillText(label, centerX, centerY + 40);
        
        return canvas;
    }

    getGaugeColor(percentage) {
        if (percentage < 0.3) return '#2ecc71'; // Verde
        if (percentage < 0.7) return '#f39c12'; // Laranja
        return '#e74c3c'; // Vermelho
    }

    createWindRose(windData) {
        const canvas = document.getElementById('wind-rose');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = Math.min(centerX, centerY) - 20;
        
        // Direções
        const directions = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'];
        const directionData = windData || this.generateWindData();
        
        // Desenhar rosa dos ventos
        ctx.save();
        ctx.translate(centerX, centerY);
        
        // Círculos concêntricos
        for (let i = 1; i <= 4; i++) {
            ctx.beginPath();
            ctx.arc(0, 0, radius * i / 4, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
            ctx.lineWidth = 1;
            ctx.stroke();
        }
        
        // Linhas das direções
        for (let i = 0; i < 8; i++) {
            const angle = (i * Math.PI) / 4;
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.lineTo(Math.cos(angle) * radius, Math.sin(angle) * radius);
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 1;
            ctx.stroke();
            
            // Rótulos das direções
            ctx.save();
            ctx.rotate(angle);
            ctx.translate(0, -radius - 15);
            ctx.rotate(-angle);
            ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.font = '12px Poppins';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(directions[i], 0, 0);
            ctx.restore();
        }
        
        // Dados do vento
        directionData.forEach((data, i) => {
            const angle = (i * Math.PI) / 4;
            const length = (data.frequency / 100) * radius;
            
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.lineTo(Math.cos(angle) * length, Math.sin(angle) * length);
            ctx.strokeStyle = '#3498db';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.stroke();
            
            // Valor percentual
            ctx.save();
            ctx.rotate(angle);
            ctx.translate(0, -length - 10);
            ctx.rotate(-angle);
            ctx.fillStyle = '#3498db';
            ctx.font = '10px Poppins';
            ctx.textAlign = 'center';
            ctx.fillText(data.frequency + '%', 0, 0);
            ctx.restore();
        });
        
        ctx.restore();
    }

    generateWindData() {
        // Gerar dados simulados para a rosa dos ventos
        return [
            { direction: 'N', frequency: 15 },
            { direction: 'NE', frequency: 10 },
            { direction: 'L', frequency: 8 },
            { direction: 'SE', frequency: 5 },
            { direction: 'S', frequency: 12 },
            { direction: 'SO', frequency: 18 },
            { direction: 'O', frequency: 20 },
            { direction: 'NO', frequency: 12 }
        ];
    }

    createHeatMap(data) {
        const canvas = document.getElementById('heatmap');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;
        const cellSize = 20;
        
        // Limpar canvas
        ctx.clearRect(0, 0, width, height);
        
        // Desenhar mapa de calor
        data.forEach((row, y) => {
            row.forEach((value, x) => {
                const color = this.getHeatColor(value);
                ctx.fillStyle = color;
                ctx.fillRect(x * cellSize, y * cellSize, cellSize, cellSize);
                
                // Texto do valor
                ctx.fillStyle = '#ffffff';
                ctx.font = '10px Poppins';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(
                    value.toFixed(1),
                    x * cellSize + cellSize / 2,
                    y * cellSize + cellSize / 2
                );
            });
        });
    }

    getHeatColor(value) {
        // Converter valor (0-1) para cor do mapa de calor
        const hue = (1 - value) * 240; // 240 (azul) a 0 (vermelho)
        return `hsl(${hue}, 70%, 50%)`;
    }

    destroyAllCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart) {
                chart.destroy();
            }
        });
        this.charts = {};
    }

    resizeAllCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart) {
                chart.resize();
            }
        });
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart !== 'undefined') {
        window.weatherCharts = new WeatherCharts(window.weatherSystem);
    }
});