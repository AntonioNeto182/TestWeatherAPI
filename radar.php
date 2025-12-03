<?php
// radar.php
$page_title = 'Radar Meteorológico';
include 'header.php';
?>

<div class="container-fluid dashboard-container py-5">
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-satellite-dish me-2"></i>
                        Radar Meteorológico
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        O radar meteorológico está em desenvolvimento. Em breve você poderá visualizar em tempo real a movimentação das chuvas.
                    </div>
                    
                    <div id="radar-container" style="height: 500px; border-radius: 10px; background: linear-gradient(to bottom, #000033, #000066); position: relative;">
                        <!-- Simulação de radar -->
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white;">
                            <i class="fas fa-satellite fa-4x mb-3"></i>
                            <h4>Radar em Desenvolvimento</h4>
                            <p>Funcionalidade disponível em breve</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>