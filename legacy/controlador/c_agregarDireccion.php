<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$codigo = $_POST['codigo'];
$direccion = $_POST['direccion'];
$descripcion = $_POST['descripcion'];

if ($co->agregarDireccion($codigo, $direccion, $descripcion)) {
    $_SESSION['direccionOk'] = true;
}

header('location: ../vistaAgente/index.php?accion=listarDirecciones');
