<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$legajo = $_POST['legajo'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$correo = $_POST['correo'];
$user = $_POST['user'];
$pass = $_POST['pass'];
$userAnterior = $_POST['userAnterior'];
$rol = $_POST['rol'];

if ($co->editarDatosUsuario($legajo, $nombre, $apellido, $correo, $user, $pass, $userAnterior)) {
    $_SESSION['datosAct'] = true;
} else {
    $_SESSION['datosActError'] = true;
}

header('Location: ../vistaAgente/inicio.php');
