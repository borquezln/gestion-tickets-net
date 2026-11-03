<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$solucion = $_POST['solucion'];
$nroArreglo = $_POST['id'];
$comprobante = $_FILES["comprobante"];

$fichero = "../comprobantes/";
$ruta = $fichero . basename($comprobante["name"]);
move_uploaded_file($comprobante["tmp_name"], $ruta);

if ($co->terminarTarea($solucion, $ruta, $nroArreglo)) {
    $_SESSION['tareaTerminada'] = true;
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
