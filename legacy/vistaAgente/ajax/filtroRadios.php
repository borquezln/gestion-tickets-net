<?php
require('../../modelo/m_conexionPage.php');
$link = conexion();
$valorRadio = $_POST['radioValor'];
$user = $_POST['user'];

$html =
    '<table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
    <thead>
        <tr>
        <th scope="col">#</th>
        <th scope="col">Materiales</th>
        <th scope="col">Estado</th>
        <th scope="col">Fecha Problema</th>
        <th scope="col">Fecha Solución</th>
        <th scope="col">Nombre Afectado</th>
        <th scope="col">Área</th>
        <th scope="col">Asignado</th>
        <th scope="col">Acción</th>
        </tr>
    </thead>
    <tbody>';

switch ($valorRadio) {
    case 'misPendientes':
        $sql = "SELECT t.nroArreglo, t.solucion, et.nombre, t.fechaProblema, t.fechaSolucion, t.nombreAfectado, a.nombre, CONCAT(u.nombre, ' ', u.apellido) AS nombre_apellido 
                FROM tareas AS t, estadotarea AS et, areas AS a, usuario AS u 
                WHERE t.estadoTarea_id = et.id AND t.area_codigo = a.codigo AND t.usuario_legajo = u.legajo 
                AND t.usuario_legajo in (SELECT legajo FROM usuario WHERE usuario = '$user') 
                AND t.estadoTarea_id IN (SELECT id from estadotarea WHERE nombre = 'Pendiente') 
                ORDER BY t.fechaProblema DESC";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_row($result)) {

                $date = date_create($row[4]);
                $fechaProblema = date_format($date, 'd/m/Y H:i:s');

                if ($row[5] != null) {
                    $date = date_create($row[5]);
                    $fechaSolucion = date_format($date, 'd/m/Y H:i:s');
                } else {
                    error_reporting(0);
                }

                $html .= '<tr>
                        <td>' . $row[0] . '</td>
                        <td>' . $row[1] . '</td>
                        <td>' . $row[2] . '</td>
                        <td><span class="badge bg-secondary">' . $row[3] . '</span></td>
                        <td>' . $fechaProblema . '</td>
                        <td>' . $fechaSolucion . '</td>
                        <td>' . $row[6] . '</td>
                        <td>' . $row[7] . '</td>
                        <td><b>' . $row[8] . '</b></td>
                        <td id="accion">
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Acción
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <li>
                                        <a class="dropdown-item" href="index.php?accion=editarTarea&nroArreglo=' . $row[0] . '">
                                            Editar
                                        </a>
                                    </li>
                                 </ul>
                            </div>
                        </td>
                    </tr>';
            }
            $html .= '</tbody></table>';
            echo $html;
        } else {
            $sqlNot = '<p class="text-center fs-5">No se han encontrado resultados</p>';
            echo $sqlNot;
        }
        break;
    case 'enProgreso':
        $sql = "SELECT t.nroArreglo, t.solucion, et.nombre, t.fechaProblema, t.fechaSolucion, t.nombreAfectado, a.nombre, CONCAT(u.nombre, ' ', u.apellido) AS nombre_apellido 
                FROM tareas AS t, estadotarea AS et, areas AS a, usuario AS u 
                WHERE t.estadoTarea_id = et.id AND t.area_codigo = a.codigo AND t.usuario_legajo = u.legajo 
                AND t.usuario_legajo in (SELECT legajo FROM usuario WHERE usuario = '$user') 
                AND t.estadoTarea_id IN (SELECT id from estadotarea WHERE nombre = 'En Progreso') 
                ORDER BY t.fechaProblema DESC";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_row($result)) {

                $date = date_create($row[4]);
                $fechaProblema = date_format($date, 'd/m/Y H:i:s');

                if ($row[5] != null) {
                    $date = date_create($row[5]);
                    $fechaSolucion = date_format($date, 'd/m/Y H:i:s');
                } else {
                    error_reporting(0);
                }
                $html .= '<tr>
                <td>' . $row[0] . '</td>
                <td>' . $row[1] . '</td>
                <td>' . $row[2] . '</td>
                <td><span class="badge badge bg-primary">' . $row[3] . '</span></td>
                <td>' . $fechaProblema . '</td>
                <td>' . $fechaSolucion . '</td>
                <td>' . $row[6] . '</td>
                <td>' . $row[7] . '</td>
                <td><b>' . $row[8] . '</b></td>
                <td id="accion">
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Acción
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <li>
                                <a class="dropdown-item" href="index.php?accion=editarTarea&nroArreglo=' . $row[0] . '">
                                    Editar
                                </a>
                            </li>
                         </ul>
                    </div>
                </td>
            </tr>';
            }
            $html .= '</tbody></table>';
            echo $html;
        } else {
            $sqlNot = '<p class="text-center fs-5">No se han encontrado resultados</p>';
            echo $sqlNot;
        }
        break;

    case 'Completas':
        $sql = "SELECT t.nroArreglo, t.solucion, et.nombre, t.fechaProblema, t.fechaSolucion, t.nombreAfectado, a.nombre, CONCAT(u.nombre, ' ', u.apellido) AS nombre_apellido 
                FROM tareas AS t, estadotarea AS et, areas AS a, usuario AS u 
                WHERE t.estadoTarea_id = et.id AND t.area_codigo = a.codigo AND t.usuario_legajo = u.legajo 
                AND t.usuario_legajo in (SELECT legajo FROM usuario WHERE usuario = '$user') 
                AND t.estadoTarea_id IN (SELECT id from estadotarea WHERE nombre = 'Completo') 
                ORDER BY t.fechaProblema DESC";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_row($result)) {

                $date = date_create($row[4]);
                $fechaProblema = date_format($date, 'd/m/Y H:i:s');

                if ($row[5] != null) {
                    $date = date_create($row[5]);
                    $fechaSolucion = date_format($date, 'd/m/Y H:i:s');
                } else {
                    error_reporting(0);
                }
                $html .= '<tr>
                        <td>' . $row[0] . '</td>
                        <td>' . $row[1] . '</td>
                        <td>' . $row[2] . '</td>
                        <td><span class="badge badge bg-success">' . $row[3] . '</span></td>
                        <td>' . $fechaProblema . '</td>
                        <td>' . $fechaSolucion . '</td>
                        <td>' . $row[6] . '</td>
                        <td>' . $row[7] . '</td>
                        <td><b>' . $row[8] . '</b></td>
                        <td id="accion">
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Acción
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <li>
                                        <a class="dropdown-item" href="index.php?accion=editarTarea&nroArreglo=' . $row[0] . '">
                                            Editar
                                        </a>
                                    </li>
                                 </ul>
                            </div>
                        </td>
                    </tr>';
            }
            $html .= '</tbody></table>';
            echo $html;
        } else {
            $sqlNot = '<p class="text-center fs-5">No se han encontrado resultados</p>';
            echo $sqlNot;
        }
        break;

    default:
        $html = '<script>
                    $("#tPrincipal").show();
                    $("#tResultado").hide();
                </script>';
        echo $html;
}
