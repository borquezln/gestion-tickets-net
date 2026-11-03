<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$nroArreglo = $_POST['nroArreglo'];
$selectArea = $_POST['selectArea'];
$descripcion = $_POST['descripcion'];
$nota_electronica = $_POST['nota_electronica'];
$selectDireccion = $_POST['selectDireccion'];

if (isset($_POST['nombreApellidoAfectado'])) {
    $nombre_apellido = $_POST['nombreApellidoAfectado'];
} else {
    $nombre_apellido = NULL;
}

if (isset($_POST['cel'])) {
    $cel = $_POST['cel'];
} else {
    $cel = NULL;
}

if ($co->editarTarea($descripcion, $nota_electronica, $nombre_apellido, $cel, $selectDireccion, $selectArea, $nroArreglo)) {
    $_SESSION['tareaEditada'] = true;
}

header('location: ' . $_SERVER['HTTP_REFERER']);
