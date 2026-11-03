<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$codigoAnterior = $_POST['codigoAnterior'];
$codigo = $_POST['codigo'];
$direccion = $_POST['direccion'];
$descripcion = $_POST['descripcion'];

if ($co->editarDireccion($codigo, $direccion, $descripcion, $codigoAnterior)) {
    $_SESSION['direccionEditada'] = true;
} else {
    $_SESSION['direccionEditadaError'] = true;
}

header('location: ../vistaAgente/index.php?accion=listarDirecciones');
