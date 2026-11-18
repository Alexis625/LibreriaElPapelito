
<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';

$database = new Database();
$conexion = $database->conectar();

require_once __DIR__ . '/../routes/web.php';
