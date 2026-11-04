<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$legajo = $_POST['legajo'];
$pass = $_POST['pass'];

if ($co->blanquearPass($legajo, $pass)) {
    $_SESSION['passBlanqueada'] = true;
}

header('location: ../vistaAgente/index.php?accion=blanqueoPass');
