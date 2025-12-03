<?php
// mapas.php
$page_title = 'Mapas Meteorológicos';
include 'header.php';
?>

<div class="container-fluid dashboard-container py-5">
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-map me-2"></i>
                        Mapas Meteorológicos
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card map-card">
                                <div class="card-body">
                                    <h5><i class="fas fa-thermometer-half me-2"></i>Mapa de Temperatura</h5>
                                    <div id="temperature-map" style="height: 300px; border-radius: 10px; background: linear-gradient(to right, blue, cyan, green, yellow, red);"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card map-card">
                                <div class="card-body">
                                    <h5><i class="fas fa-cloud-rain me-2"></i>Mapa de Precipitação</h5>
                                    <div id="precipitation-map" style="height: 300px; border-radius: 10px; background: linear-gradient(to right, white, lightblue, blue, darkblue);"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card map-card">
                                <div class="card-body">
                                    <h5><i class="fas fa-wind me-2"></i>Mapa de Ventos</h5>
                                    <div id="wind-map" style="height: 300px; border-radius: 10px; background: linear-gradient(to right, lightgray, gray);"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card map-card">
                                <div class="card-body">
                                    <h5><i class="fas fa-satellite me-2"></i>Imagem de Satélite</h5>
                                    <div id="satellite-map" style="height: 300px; border-radius: 10px; background-color: #2c3e50;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>