<?php
error_reporting(E_ALL ^ E_NOTICE);

if (!(time() - $_SESSION['time'] >= 3600)) {
    $_SESSION['time'] = time();
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="3600">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>

            <link rel="stylesheet" href="../styles/styles.css">
            <script>
                //DATATABLE
                $(document).ready(function() {
                    function addZero(i) {
                        if (i < 10) {
                            i = "0" + i
                        }
                        return i;
                    }

                    const dNow = new Date();
                    let h = addZero(dNow.getHours());
                    let m = addZero(dNow.getMinutes());

                    let time = h + ":" + m;
                    var localdate = dNow.getDate() + '/' + (dNow.getMonth() + 1) + '/' + dNow.getFullYear() + ' ' + time;

                    $('#tablaDinamicaLoad').DataTable({
                        aLengthMenu: [25, 50, 100, 200],
                        dom: 'lBfrtip',
                        aaSorting: [
                            [0, "asc"]
                        ],
                        buttons: [{
                                extend: 'excelHtml5',
                                title: 'Listado de Tareas - Gestión de tareas',
                                messageTop: 'Reporte: ' + localdate,
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                download: 'open',
                                messageTop: 'Reporte: ' + localdate,
                                title: 'Listado de Tareas - Gestión de tareas',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                                },
                            },
                        ],
                        language: {
                            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                        }
                    })

                    $('#tablaDinamicaLoad2').DataTable({
                        aLengthMenu: [10, 25, 50, 100, 200],
                        aaSorting: [
                            [0, "asc"]
                        ],
                        language: {
                            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                        }
                    })
                })

                function mostrarNE(valor) {
                    if (valor.checked) {
                        $('#neSeccion').show(200);
                        $('input[name=ne]').prop('required', true);
                    } else {
                        $('#neSeccion').hide(200);
                        $('input[name=ne]').prop('required', false);
                        $('input[name=ne]').val('');
                    }
                }

                function validarInputNumerico(valor) {
                    const num = /^[0-9.]+$/;
                    if (!num.test(valor.value)) {
                        valor.value = valor.value.substring(0, valor.value.length - 1);
                    }
                }

                function validarComillas(valor) {
                    valor.value = valor.value.replace('\'', '"');
                }

                function buscarComillaSimple(valor) {
                    if (valor.value.includes('\'')) {
                        alert('Debe tener comillas dobles');
                        valor.focus();
                    }
                }
            </script>
        </head>

        <body>
            <?php
            error_reporting(0);
            require "../scripts/alerta.php";
            ?>

            <section id="container">
                <input type="hidden" id="usuario" name="usuario" value="<?= $_SESSION['username']; ?>">
                <?php
                if (isset($_SESSION['tareaOK'])) {
                    alerta("La tarea ha sido agregada");
                    unset($_SESSION['tareaOK']);
                } else if (isset($_SESSION['tareaAsignada'])) {
                    alerta("La tarea ha sido iniciada");
                    unset($_SESSION['tareaAsignada']);
                } else if (isset($_SESSION['tareaTerminada'])) {
                    alerta("La tarea ha sido concluida");
                    unset($_SESSION['tareaTerminada']);
                } else if (isset($_SESSION['tareaCancelada'])) {
                    alerta("La tarea ha sido cancelada");
                    unset($_SESSION['tareaCancelada']);
                } else if (isset($_SESSION['tareaEditada'])) {
                    alerta("La tarea ha sido editada");
                    unset($_SESSION['tareaEditada']);
                }
                ?>

                <p class="fs-5">Tareas <?= $estado; ?></p>
                <hr>
                <p class="fs-6">
                    Para ver las opciones de las tareas, presione <b>Acción</b>
                    <br>
                    Para obtener más información acerca de la tarea presione <b>Acción > Ver más Info</b>
                </p>

                <div class="table-responsive-xxl">

                    <div id="btnTarea">
                        <form action="../controlador/c_agregarTarea.php" method="post">
                            <!--Modal Nueva TAREA-->
                            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Nueva Tarea</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="fs-6">Ingrese la tarea con sus respectivos datos</p>

                                            <input type="hidden" name="rol" value="<?= $_SESSION['rol']; ?>">

                                            <input type="hidden" name="nombreApellido" value="<?= $_SESSION['nombreApellido']; ?>">

                                            <div class="form-floating mb-3">
                                                <select class="form-select" name="selectArea" id="floatingSelect" aria-label="Floating label select example" required>
                                                    <option value="" selected>Seleccione...</option>
                                                    <?php
                                                    foreach ($listAreas as $area) {
                                                    ?>
                                                        <option value="<?= $area[0]; ?>">
                                                            <?= $area[1]; ?>
                                                        </option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                                <label for="floatingSelect">Seleccione el Área involucrada</label>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" name="descripcion" placeholder="Leave a comment" id="floatingTextarea" style="height: 200px;" onchange="buscarComillaSimple(this)" oninput="validarComillas(this);" required></textarea>
                                                <label for="floatingTextarea">Descripción</label>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" onclick="mostrarNE(this);">
                                                <label class="form-check-label" for="flexCheckChecked">
                                                    Con Nota Electrónica
                                                </label>
                                            </div>

                                            <div class="form-floating mb-3" id="neSeccion" style="display: none;">
                                                <input type="text" name="ne" class="form-control" id="floatingInput" placeholder="Nombre del Afectado">
                                                <label for="floatingInput">Nota Electrónica</label>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input type="text" name="nombreApellidoAfectado" onchange="buscarComillaSimple(this)" oninput="validarComillas(this);" class="form-control" id="floatingInput" placeholder="ejemplo">
                                                <label for="floatingInput">Nombre y apellido del afectado/a (Opcional)</label>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input type="tel" name="cel" oninput="validarInputNumerico(this);" class="form-control" id="floatingInput" placeholder="ejemplo">
                                                <label for="floatingInput">Nro de celular (Opcional)</label>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <select class="form-select" name="direccion" id="floatingSelect" aria-label="Floating label select example" required>
                                                    <option value="" selected>Seleccione...</option>
                                                    <?php
                                                    foreach ($listDirecciones as $direccion) {
                                                    ?>
                                                        <option value="<?= $direccion[0]; ?>">
                                                            <?= $direccion[1]; ?>
                                                        </option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                                <label for="floatingSelect">Seleccione la Dirección o Entidad donde se desempeña</label>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" id="btnGuardar" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            Nueva Tarea
                        </button>
                    </div>

                    <?php
                    if ($_GET['lista'] == 'completas') {
                    ?>
                        <div id="tCompletadosActual">
                            <p class="fs-5 text-center">Tareas completadas hoy día</p>
                            <hr>
                            <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad2">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nota Electrónica</th>
                                        <th scope="col">Descripción</th>

                                        <th scope="col">Afectado/a</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Fecha Problema</th>

                                        <th scope="col">Dirección/Entidad</th>
                                        <th scope="col">Área encargada</th>
                                        <th scope="col">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($listTareasCompletasActual as $completas) {
                                    ?>
                                        <tr>
                                            <td><?= $completas[0]; ?></td>
                                            <td><?= $completas[2]; ?></td>
                                            <td><?= $completas[1]; ?></td>
                                            <td><?= $completas[3]; ?></td>
                                            <td>
                                                <?php
                                                if ($completas[7] == 'Completo') {
                                                    echo '<span class="badge text-bg-success">' . $completas[7] . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $date = date_create($completas[9]);
                                                $fechaProblema = date_format($date, 'd/m/Y H:i:s');
                                                echo $fechaProblema;
                                                ?>
                                            </td>
                                            <td><?= $completas[12]; ?></td>
                                            <td><?= $completas[16]; ?></td>
                                            <td id="accion">
                                                <div class="btn-group" role="group">
                                                    <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Acción
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">

                                                        <li>
                                                            <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalVerInfo<?= $completas[0]; ?>">
                                                                Ver más info
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" role="button" data-bs-toggle="modal" data-bs-target="#modalEliminarTarea<?= $completas[0]; ?>">
                                                                Eliminar tarea
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- MODALES TERMINADAS HOY -->
                                        <!-- Modal Ver Info Tarea -->
                                        <div class="modal fade modalEditar" id="modalVerInfo<?= $completas[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">Información de la tarea</h5>
                                                        <button type="button" class="btn-close btnCerrarModalEditar" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body bodyModalInfo">

                                                        <p class="fs-6">Tarea N° <?= $completas[0]; ?></p>

                                                        <input type="hidden" name="nroArreglo" value="<?= $completas[0]; ?>">

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="descripcion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 170px" disabled><?= $completas[1]; ?></textarea>
                                                            <label for="floatingTextarea">Descripción</label>
                                                        </div>

                                                        <?php
                                                        if ($completas[2] == '' || $completas[2] == null) {
                                                            $ne = 'No proporcionado';
                                                        } else {
                                                            $ne = $completas[2];
                                                        }
                                                        ?>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="ne" value="<?= $ne; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Nota Electrónica</label>
                                                        </div>

                                                        <?php
                                                        if ($completas[3] == '' || $completas[3] == null) {
                                                            $nombreApellidoAfectado = 'No proporcionado';
                                                        } else {
                                                            $nombreApellidoAfectado = $completas[3];
                                                        }
                                                        ?>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="nombreApellido" value="<?= $nombreApellidoAfectado; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Nombre y apellido del afectado/a</label>
                                                        </div>

                                                        <?php
                                                        if ($completas[4] == '' || $completas[4] == null) {
                                                            $cel = 'No proporcionado';
                                                        } else {
                                                            $cel = $completas[4];
                                                        }
                                                        ?>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="cel" value="<?= $cel; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Nro de celular de contacto</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="solucion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 100px" disabled><?= $completas[5]; ?></textarea>
                                                            <label for="floatingTextarea">Materiales utilizados</label>
                                                        </div>

                                                        <div class="form-control mb-3 bg-light">
                                                            <a href="<?= $completas[18]; ?>" download="comprobante-arreglo-<?= $completas[0]; ?>">Descargar comprobante</a>
                                                        </div>

                                                        <?php
                                                        $date = date_create($completas[9]);
                                                        $fechaProblema = date_format($date, 'd/m/Y H:i:s');
                                                        ?>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="fechaProblema" value="<?= $fechaProblema; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Fecha del problema</label>
                                                        </div>

                                                        <?php
                                                        $date = date_create($completas[10]);
                                                        $fechaSolucion = date_format($date, 'd/m/Y H:i:s');
                                                        ?>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="fechaSolucion" value="<?= $fechaSolucion; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Fecha de la solución</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="area" value="<?= $completas[12]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Dirección o Entidad donde se desempeña el/la afectado/a</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="area" value="<?= $completas[16]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Área que lo lleva a cabo</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="area" value="<?= $completas[17]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Creado por</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btnCerrarModalEditar" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Eliminar Tarea -->
                                        <div class="modal fade" id="modalEliminarTarea<?= $completas[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">Eliminar tarea N°<?= $completas[0]; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <form action="../controlador/c_eliminarTarea.php" id="formEliminarTarea" method="post" style="display: none;">

                                                        <div class="modal-body">

                                                            <p class="fs-6">Para eliminar la tarea debe completar lo siguiente</p>

                                                            <input type="hidden" name="id" value="<?= $completas[0]; ?>">

                                                            <div class="form-floating mb-3">
                                                                <textarea class="form-control" onchange="buscarComillaSimple(this)" oninput="validarComillas(this);" name="motivoEliminacion" placeholder="Leave a comment" id="floatingTextarea" style="height: 100px" required></textarea>
                                                                <label for="floatingTextarea">Motivo de la eliminación</label>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                            <button type="submit" class="btn btn-danger">Eliminar tarea</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    <?php
                    }
                    ?>

                    <br>

                    <div id="tPrincipal">
                        <?php
                        if ($_GET['lista'] == 'completas') {
                        ?>
                            <p class="fs-5 text-center">Tareas completadas en total</p>

                            <hr>
                        <?php
                        }
                        ?>
                        <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nota Electrónica</th>
                                    <th scope="col">Descripción</th>

                                    <th scope="col">Afectado/a</th>
                                    <th scope="col">Contacto</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Fecha Problema</th>

                                    <th scope="col">Dirección/Entidad</th>
                                    <th scope="col">Área encargada</th>
                                    <th scope="col">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($listTareasAgentes as $listTarea) {
                                ?>
                                    <tr>
                                        <td style="text-align: center;"><?= $listTarea[0]; ?></td>
                                        <td><?= $listTarea[2]; ?></td>
                                        <td><?= $listTarea[1]; ?></td>
                                        <td><?= $listTarea[3]; ?></td>
                                        <td><?= $listTarea[4]; ?></td>

                                        <td>
                                            <?php
                                            if ($listTarea[7] == 'Pendiente') {
                                                echo '<span class="badge bg-secondary">' . $listTarea[7] . '</span>';
                                            } else if ($listTarea[7] == 'En Progreso') {
                                                echo '<span class="badge bg-primary">' . $listTarea[7] . '</span>';
                                            } else if ($listTarea[7] == 'Completo') {
                                                echo '<span class="badge bg-success">' . $listTarea[7] . '</span>';
                                            } else if ($listTarea[7] == 'Cancelado') {
                                                echo '<span class="badge bg-danger">' . $listTarea[7] . '</span>';
                                            }
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            $date = date_create($listTarea[9]);
                                            $fechaProblema = date_format($date, 'd/m/Y H:i:s');
                                            echo $fechaProblema;
                                            ?>
                                        </td>

                                        <td><?= $listTarea[12]; ?></td>
                                        <td><?= $listTarea[16]; ?></td>
                                        <td id="accion">
                                            <div class="btn-group" role="group">
                                                <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Acción
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">

                                                    <li>
                                                        <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalVerInfo<?= $listTarea[0]; ?>">
                                                            Ver más info
                                                        </a>
                                                    </li>

                                                    <?php
                                                    if ($listTarea[7] == 'Pendiente') {
                                                    ?>
                                                        <li>
                                                            <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalAsignarTarea<?= $listTarea[0]; ?>">
                                                                Iniciar la tarea
                                                            </a>
                                                        </li>
                                                    <?php
                                                    }
                                                    if ($listTarea[7] == 'En Progreso') {
                                                    ?>
                                                        <li>
                                                            <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalTareaCompletada<?= $listTarea[0]; ?>">
                                                                Terminar tarea
                                                            </a>
                                                        </li>

                                                    <?php
                                                    }
                                                    if ($listTarea[7] != 'Cancelado' && $listTarea[7] != 'Completo') {
                                                    ?>
                                                        <li>
                                                            <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalCancelarTarea<?= $listTarea[0]; ?>">
                                                                Cancelar tarea
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalEditarTarea<?= $listTarea[0]; ?>">
                                                                Editar tarea
                                                            </a>
                                                        </li>
                                                    <?php
                                                    }
                                                    ?>

                                                    <li>
                                                        <a class="dropdown-item" role="button" data-bs-toggle="modal" data-bs-target="#modalEliminarTarea<?= $listTarea[0]; ?>">
                                                            Eliminar tarea
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- MODALES TAREAS GENERALES -->
                                    <!-- Modal Ver Info Tarea -->
                                    <div class="modal fade modalEditar" id="modalVerInfo<?= $listTarea[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staticBackdropLabel">Información de la tarea</h5>
                                                    <button type="button" class="btn-close btnCerrarModalEditar" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body bodyModalInfo">

                                                    <p class="fs-6">Tarea N° <?= $listTarea[0]; ?></p>

                                                    <input type="hidden" name="nroArreglo" value="<?= $listTarea[0]; ?>">

                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" name="descripcion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 170px" disabled><?= $listTarea[1]; ?>
                                                        </textarea>
                                                        <label for="floatingTextarea">Descripción</label>
                                                    </div>

                                                    <?php
                                                    if ($listTarea[2] == '' || $listTarea[2] == null) {
                                                        $ne = 'No proporcionado';
                                                    } else {
                                                        $ne = $listTarea[2];
                                                    }
                                                    ?>

                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="ne" value="<?= $ne; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Nota Electrónica</label>
                                                    </div>

                                                    <?php
                                                    if ($listTarea[3] == '' || $listTarea[3] == null) {
                                                        $nombreApellidoAfectado = 'No proporcionado';
                                                    } else {
                                                        $nombreApellidoAfectado = $listTarea[3];
                                                    }
                                                    ?>

                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="nombreApellido" value="<?= $nombreApellidoAfectado; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Nombre y apellido del afectado/a</label>
                                                    </div>

                                                    <?php
                                                    if ($listTarea[4] == '' || $listTarea[4] == null) {
                                                        $cel = 'No proporcionado';
                                                    } else {
                                                        $cel = $listTarea[4];
                                                    }
                                                    ?>

                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="cel" value="<?= $cel; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Nro de celular de contacto</label>
                                                    </div>


                                                    <?php
                                                    if ($listTarea[5] != '') {
                                                    ?>
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="solucion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 100px" disabled><?= $listTarea[5]; ?>
                                                            </textarea>
                                                            <label for="floatingTextarea">Materiales utilizados</label>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>

                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="estadoTarea" value="<?= $listTarea[7]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Estado de la tarea</label>
                                                    </div>

                                                    <?php
                                                    if ($listTarea[8] != '') {
                                                    ?>
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="solucion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 100px" disabled>
                                                                <?= $listTarea[8]; ?>
                                                            </textarea>
                                                            <label for="floatingTextarea">Motivo de la cancelación</label>
                                                        </div>
                                                    <?php
                                                    }

                                                    $date = date_create($listTarea[9]);
                                                    $fechaProblema = date_format($date, 'd/m/Y H:i:s');
                                                    ?>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="fechaProblema" value="<?= $fechaProblema; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Fecha del problema</label>
                                                    </div>

                                                    <?php
                                                    if ($listTarea[10] != '') {
                                                        $date = date_create($listTarea[10]);
                                                        $fechaSolucion = date_format($date, 'd/m/Y H:i:s');
                                                    ?>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="fechaSolucion" value="<?= $fechaSolucion; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                            <label for="floatingInput">Fecha de la solución</label>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>

                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="area" value="<?= $listTarea[12]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Dirección o Entidad donde se desempeña el/la afectado/a</label>
                                                    </div>

                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="area" value="<?= $listTarea[16]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Área donde se lleva a cabo</label>
                                                    </div>

                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="area" value="<?= $listTarea[17]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                        <label for="floatingInput">Creado por</label>
                                                    </div>

                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btnCerrarModalEditar" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--Modal Iniciar Tarea-->
                                    <div class="modal fade" id="modalAsignarTarea<?= $listTarea[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staticBackdropLabel">Iniciar tarea - <?= $listTarea[16]; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="../controlador/c_iniciarTarea.php" method="get">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $listTarea[0]; ?>">
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="descripcion" placeholder="Leave a comment" id="floatingTextarea" style="height: 170px" disabled><?= $listTarea[1]; ?></textarea>
                                                            <label for="floatingTextarea">Descripción</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-primary">Aceptar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Cancelar Tarea -->
                                    <div class="modal fade" id="modalCancelarTarea<?= $listTarea[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staticBackdropLabel">Terminar tarea</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <form action="../controlador/c_cancelarTarea.php" method="post" style="display: none;">

                                                    <div class="modal-body">

                                                        <p class="fs-6">Para cancelar la tarea debe completar lo siguiente</p>

                                                        <input type="hidden" name="id" value="<?= $listTarea[0]; ?>">

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" onchange="buscarComillaSimple(this)" oninput="validarComillas(this);" name="motivoCancelacion" placeholder="Leave a comment" id="floatingTextarea" style="height: 100px" required></textarea>
                                                            <label for="floatingTextarea">Motivo de la cancelación</label>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-danger">Cancelar tarea</button>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Editar Tarea -->
                                    <div class="modal fade" id="modalEditarTarea<?= $listTarea[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staticBackdropLabel">Editar tarea</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <form action="../controlador/c_editarTarea.php" method="post" style="display: none;">

                                                    <div class="modal-body">

                                                        <p class="fs-6">Editar en caso de que haya habido un cambio o un error.</p>

                                                        <input type="hidden" name="nroArreglo" value="<?= $listTarea[0]; ?>">

                                                        <div class="form-floating mb-3">
                                                            <select class="form-select" name="selectArea" id="floatingSelect" aria-label="Floating label select example" required>
                                                                <option value="<?= $listTarea[15]; ?>" selected><?= $listTarea[16]; ?> (Actual)</option>
                                                                <?php
                                                                foreach ($listAreas as $area) {
                                                                ?>
                                                                    <option value="<?= $area[0]; ?>">
                                                                        <?= $area[1]; ?>
                                                                    </option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            <label for="floatingSelect">Seleccione el Área involucrada</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="descripcion" placeholder="Leave a comment" id="floatingTextarea" style="height: 170px" required><?= $listTarea[1]; ?></textarea>
                                                            <label for="floatingTextarea">Descripción</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="ne" value="<?= $listTarea[2]; ?>" class="form-control" id="floatingInput" placeholder="Nombre del Afectado">
                                                            <label for="floatingInput">Nota Electrónica</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="nombreApellidoAfectado" value="<?= $listTarea[3]; ?>" class="form-control" id="floatingInput" placeholder="ejemplo">
                                                            <label for="floatingInput">Nombre y apellido del afectado/a (Opcional)</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <input type="tel" name="cel" value="<?= $listTarea[4]; ?>" oninput="validarInputNumerico(this);" class="form-control" id="floatingInput" placeholder="ejemplo">
                                                            <label for="floatingInput">Nro de celular (Opcional)</label>
                                                        </div>

                                                        <div class="form-floating mb-3">
                                                            <select class="form-select" name="selectDireccion" id="floatingSelect" aria-label="Floating label select example" required>
                                                                <option value="<?= $listTarea[11]; ?>" selected><?= $listTarea[12]; ?> (Actual)</option>
                                                                <?php
                                                                foreach ($listDirecciones as $direccion) {
                                                                ?>
                                                                    <option value="<?= $direccion[0]; ?>">
                                                                        <?= $direccion[1]; ?>
                                                                    </option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            <label for="floatingSelect">Seleccione la Dirección o Entidad donde se desempeña</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Terminar Tarea -->
                                    <div class="modal fade" id="modalTareaCompletada<?= $listTarea[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staticBackdropLabel">Terminar tarea</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <form enctype="multipart/form-data" action="../controlador/c_terminarTarea.php" method="post" style="display: none;">

                                                    <div class="modal-body">

                                                        <p class="fs-6">Para terminar la tarea debe completar lo siguiente</p>

                                                        <input type="hidden" name="id" value="<?= $listTarea[0]; ?>">

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" onchange="buscarComillaSimple(this)" oninput="validarComillas(this);" name="solucion" placeholder="Leave a comment" id="floatingTextarea" style="height: 100px" required></textarea>
                                                            <label for="floatingTextarea">Materiales utilizados</label>
                                                        </div>

                                                        <label for="floatingInputfile">Comprobante</label>
                                                        <div class="form-floating">
                                                            <input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
                                                            <input class="form-control" id="floatingInputfile" type="file" name="comprobante" accept=".pdf" required>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-success">Terminar tarea</button>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Eliminar Tarea -->
                                    <div class="modal fade" id="modalEliminarTarea<?= $listTarea[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staticBackdropLabel">Eliminar tarea N°<?= $listTarea[0]; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <form action="../controlador/c_eliminarTarea.php" id="formEliminarTarea" method="post" style="display: none;">

                                                    <div class="modal-body">

                                                        <p class="fs-6">Para eliminar la tarea debe completar lo siguiente</p>

                                                        <input type="hidden" name="id" value="<?= $listTarea[0]; ?>">

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="motivoEliminacion" placeholder="Leave a comment" id="floatingTextarea" style="height: 100px" required></textarea>
                                                            <label for="floatingTextarea">Motivo de la eliminación</label>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-danger">Eliminar tarea</button>
                                                    </div>


                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            </section>
        </body>

        </html>
<?php
} else {
    require "destroySession.php";
}
?>