<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$legajo = $_POST['legajo'];
$motivoBaja = $_POST['motivoBaja'];

if ($co->bajaUsuario($legajo, $motivoBaja)) {
    $_SESSION['bajaOk'] = true;
}

header('location: ../vistaAgente/index.php?accion=listarBajas');
