<?php
session_start();
require('../modelo/m_consultas.php');
$co = new Consultas();

$codigo = $_GET['codigo'];

if ($co->eliminarDireccion($codigo)) {
    $_SESSION['eliminadoOk'] = true;
}

header('location: ../vistaAgente/index.php?accion=listarDirecciones');
