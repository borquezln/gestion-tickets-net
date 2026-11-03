<?php
session_start();
require('../modelo/m_consultas.php');
$user = $_POST['user'];
$password = $_POST['password'];
$co = new Consultas();

if ($co->autenticarUsuario($user, $password)) {
    $_SESSION['username'] = $user;
    $_SESSION['rol'] = $co->verificarTipoUsuario($user);
    $_SESSION['legajo'] = $co->listarlegajoUserActual($user);
    $_SESSION['nombreApellido'] = $co->mostrarDatosUsuario($user);
    $_SESSION['time'] = time();

    $co->verificarUltimoAcceso($user);

    header('Location: ../vistaAgente/inicio.php');
} else {
    $_SESSION['errLogin'] = true;
    header('Location: ../vistaAgente/login.php');
}
