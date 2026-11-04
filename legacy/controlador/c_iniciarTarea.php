<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$nroArreglo = $_GET['id'];

if ($co->iniciarTarea($nroArreglo)) {
    $_SESSION['tareaAsignada'] = true;
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
