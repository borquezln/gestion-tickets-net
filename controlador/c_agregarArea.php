<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$area = $_POST['area'];
$descripcion = $_POST['descripcion'];

if ($co->agregarNuevoArea($area, $descripcion)) {
    $_SESSION['areaOk'] = true;
}

header('Location: ../vistaAgente/index.php?accion=listarAreas');
