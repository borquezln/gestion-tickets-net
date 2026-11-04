<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$area = $_POST['area'];
$descripcion = $_POST['descripcion'];
$id = $_POST['id'];

if ($co->editarArea($area, $descripcion, $id)) {
    $_SESSION['editadoOk'] = true;
}

header('Location: ../vistaAgente/index.php?accion=listarAreas');
