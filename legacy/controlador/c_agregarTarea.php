<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$area = $_POST['selectArea'];
$descripcion = $_POST['descripcion'];
$nombreApellido = $_POST['nombreApellidoAfectado'];
$celular = $_POST['cel'];
$direccion = $_POST['direccion'];
$rol = $_POST['rol'];
$usuarioCreado = $_POST['nombreApellido'];

if (isset($_POST['nota_electronica'])) {
    $nota_electronica = $_POST['nota_electronica'];
} else {
    $nota_electronica = NULL;
}

if ($co->agregarTarea($descripcion, $nota_electronica, $nombreApellido, $celular, $direccion, $area, $usuarioCreado)) {
    $_SESSION['tareaOK'] = true;
}

header('location: ../vistaAgente/index.php?accion=listarTareasAdmin&lista=actual');
