<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$tipoUsuario = $_POST['selectRol'];
$legajo = $_POST['legajo'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$username = $_POST['user'];
$pass = $_POST['pass'];
if (isset($_POST['correo'])) {
    $correo = $_POST['correo'];
} else {
    $correo = '';
}

if ($co->agregarUsuario($tipoUsuario, $legajo, $nombre, $apellido, $correo, $username, $pass)) {
    $_SESSION['usuarioAgregado'] = true;
} else {
    $_SESSION['usuarioError'] = true;
}

header('Location: ../vistaAgente/index.php?accion=listarUsuarios');
