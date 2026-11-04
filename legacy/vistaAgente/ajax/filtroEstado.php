<?php
require('../../modelo/m_conexionPage.php');
$link = conexion();

$opcionEstado = $_POST['opcionEstado'];
$legajo = $_POST['legajoAgente'];

$html = '<table class="table table-responsive table-bordered table-hover" id="tablaAjax">
<thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Descripción</th>
        <th scope="col">Nota Electrónica</th>
        <th scope="col">Afectado/a</th>
        <th scope="col">Estado</th>
        <th scope="col">Fecha Problema</th>
        <th scope="col">Fecha Solución</th>
        <th scope="col">Área</th>
    </tr>
</thead>
<tbody>';


$sql = "SELECT t.nroArreglo, t.descripcion, t.nota_electronica, t.nombreApellidoAfectado, t.celular, t.solucion, e.nombre, t.motivoCancelacion, 
        t.fechaProblema, t.fechaSolucion, d.nombre, concat(u.nombre, ' ', u.apellido) as nombreApellido, t.motivoEliminacion
        from tareas t, estadotarea e, direcciones d, usuario u 
        where t.estadoTarea_id = e.id and t.direccion_codigo = d.codigo and t.usuario_legajo = u.legajo and u.legajo = '$legajo'
        and t.estadoTarea_id = '$opcionEstado'";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_row($result)) {
    if ($row[7] == 'Pendiente') {
        $estado = '<span class="badge bg-secondary">' . $row[7] . '</span>';
    } else if ($row[7] == 'En Progreso') {
        $estado = '<span class="badge bg-primary">' . $row[7] . '</span>';
    } else if ($row[7] == 'Completo') {
        $estado = '<span class="badge bg-success">' . $row[7] . '</span>';
    } else if ($row[7] == 'Cancelado') {
        $estado = '<span class="badge bg-danger">' . $row[7] . '</span>';
    }

    $date = date_create($row[9]);
    $fechaProblema = date_format($date, 'd/m/Y H:i:s');

    if ($row[10] != '') {
        $date = date_create($row[10]);
        $fechaSolucion = date_format($date, 'd/m/Y H:i:s');
    } else {
        $fechaSolucion = '';
    }

    $html .= '<tr>
    <td id="nroArreglo">' . $row[0] . '</td>
    <td>' . $row[1] . '</td>
    <td>' . $row[2] . '</td>
    <td>' . $row[3] . '</td>
    <td>' . $row[4] . '</td>
    <td>' . $estado . '</td>
    <td>' . $fechaProblema . '</td>
    <td>' . $fechaSolucion . '</td>
    <td>' . $row[11] . '</td>

</tr>';
}
$html .= '</tbody></table>';

echo $html;
