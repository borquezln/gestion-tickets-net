<?php
require('../../modelo/m_conexionPage.php');
$link = conexion();
$codigo = $_POST['codigo'];
$sql = "SELECT codigo, nombre FROM direcciones WHERE codigo = '$codigo'";
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) > 0) {
    $html = 'El código escrito ya se encuentra registrado con un área.
                <script>
                    $("#btnGuardar").prop("disabled", "true");    
                </script>';
    echo $html;
} else {
    $html = '<script>
                    $("#btnGuardar").removeAttr("disabled");    
                </script>';
    echo $html;
}
