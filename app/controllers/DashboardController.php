
<?php

require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController {

    private $dashboard;

    public function __construct($conexion) {
        $this->dashboard = new Dashboard($conexion);
    }

public function index() {

    // Obtener indicadores principales
    $resumen = $this->dashboard->obtenerResumen();

    // Obtener ventas de la semana
    $ventasSemana = $this->dashboard->obtenerVentasSemana();

    // Obtener productos con stock bajo
    $productosStockBajo = $this->dashboard->obtenerProductosStockBajo();

    require_once __DIR__ . '/../views/admin/dashboard.php';
}

}
