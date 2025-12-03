<?php
// sobre.php
$page_title = 'Sobre o Sistema';
include 'header.php';
?>

<div class="container-fluid dashboard-container py-5">
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header bg-gradient-success">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Sobre o WeatherMaster Pro
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="about-icon">
                                <i class="fas fa-cloud-sun-rain fa-5x text-primary"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h4 class="mb-3">WeatherMaster Pro v2.0</h4>
                            <p class="lead">Sistema avançado de previsão meteorológica em tempo real.</p>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-check-circle text-success me-2"></i>Funcionalidades</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fas fa-temperature-half me-2"></i>Previsão do tempo atual
                                        </li>
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fas fa-clock me-2"></i>Previsão horária (24h)
                                        </li>
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fas fa-calendar-day me-2"></i>Previsão diária (7 dias)
                                        </li>
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fas fa-map me-2"></i>Mapas meteorológicos
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="fas fa-database text-info me-2"></i>Tecnologias</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fab fa-php me-2"></i>PHP 7.4+
                                        </li>
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fab fa-js me-2"></i>JavaScript ES6+
                                        </li>
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fas fa-server me-2"></i>API Open-Meteo
                                        </li>
                                        <li class="list-group-item bg-transparent border-light">
                                            <i class="fas fa-palette me-2"></i>Bootstrap 5.3
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5><i class="fas fa-code-branch me-2"></i>Versão</h5>
                                <p>Versão atual: <strong>2.0.0</strong></p>
                                <p>Última atualização: <?php echo date('d/m/Y'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>