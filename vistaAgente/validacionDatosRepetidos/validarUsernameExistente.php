<?php
require('../../modelo/m_conexionPage.php');
$link = conexion();

$usuario = $_POST['usuario'];

$sql = "SELECT * from usuario u where u.usuario = '$usuario'";

$result = mysqli_query($link, $sql);

$html = "";

if(mysqli_num_rows($result) > 0){
    $html .= 'Este nombre de usuario ya existe. Intentar nuevamente.
                <script>$("#btnGuardar").prop("disabled", true)</script>';
}else{
    $html .= '<script>$("#btnGuardar").prop("disabled", false)</script>';
}

echo $html;
