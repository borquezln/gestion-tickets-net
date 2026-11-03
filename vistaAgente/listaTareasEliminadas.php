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
                $(document).ready(function() {
                    $('#tablaDinamicaLoad').DataTable({
                        aLengthMenu: [25, 50, 100, 200],
                        aaSorting: [
                            [0, "desc"]
                        ],
                        language: {
                            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                        }
                    })
                })
            </script>

        </head>

        <body>
            <?php
            error_reporting(0);
            require "../scripts/alerta.php";

            if (isset($_SESSION['tareaEliminada'])) {
                alerta("La tarea ha sido eliminada");
                unset($_SESSION['tareaEliminada']);
            }
            ?>

            <section id="container">
                <p class="fs-5">Lista de tareas eliminadas</p>
                <hr>

                <div class="table-responsive-xxl">
                    <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Afectado/a</th>
                                <th scope="col">Fecha Eliminada</th>
                                <th scope="col">Dirección</th>
                                <th scope="col">Motivo Eliminación</th>

                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($listTareasEliminadas as $tarea) {
                            ?>
                                <tr>
                                    <td style="text-align: center;"><?= $tarea[0]; ?></td>
                                    <td><?= $tarea[1]; ?></td>
                                    <td><?= $tarea[3]; ?></td>
                                    <td>
                                        <?php
                                        $date = date_create($tarea[13]);
                                        $fechaProblema = date_format($date, 'd/m/Y H:i:s');
                                        echo $fechaProblema;
                                        ?>
                                    </td>
                                    <td><?= $tarea[10]; ?></td>
                                    <td><?= $tarea[12]; ?></td>

                                    <td id="accion">
                                        <div class="btn-group" role="group">
                                            <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acción
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                <li>
                                                    <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalInfoTarea<?= $tarea[0]; ?>">
                                                        Ver más info
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>


                                <!-- Modal Ver Info Tarea -->
                                <div class="modal fade modalEditar" id="modalInfoTarea<?= $tarea[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="staticBackdropLabel">Información de la tarea</h5>
                                                <button type="button" class="btn-close btnCerrarModalEditar" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body bodyModalInfo">

                                                <p class="fs-6">Tarea N° <?= $tarea[0]; ?></p>

                                                <input type="hidden" name="nroArreglo" value="<?= $tarea[0]; ?>">

                                                <div class="form-floating mb-3">
                                                    <textarea class="form-control" name="descripcion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 100px" disabled><?= $tarea[1]; ?></textarea>
                                                    <label for="floatingTextarea">Descripción</label>
                                                </div>

                                                <?php
                                                if ($tarea[2] == '' || $tarea[2] == null) {
                                                    $ne = 'No proporcionado';
                                                } else {
                                                    $ne = $tarea[2];
                                                }
                                                ?>

                                                <div class="form-floating mb-3">
                                                    <input type="text" name="ne" value="<?= $ne; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                    <label for="floatingInput">Nota Electrónica</label>
                                                </div>

                                                <?php
                                                if ($tarea[3] == '' || $tarea[3] == null) {
                                                    $nombreApellidoAfectado = 'No proporcionado';
                                                } else {
                                                    $nombreApellidoAfectado = $tarea[3];
                                                }
                                                ?>

                                                <div class="form-floating mb-3">
                                                    <input type="text" name="nombreApellido" value="<?= $nombreApellidoAfectado; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                    <label for="floatingInput">Nombre y apellido del afectado/a</label>
                                                </div>

                                                <?php
                                                if ($tarea[4] == '' || $tarea[4] == null) {
                                                    $cel = 'No proporcionado';
                                                } else {
                                                    $cel = $tarea[4];
                                                }
                                                ?>

                                                <div class="form-floating mb-3">
                                                    <input type="text" name="cel" value="<?= $cel; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                    <label for="floatingInput">Nro de celular de contacto</label>
                                                </div>

                                                <?php
                                                if ($tarea[5] != '') {
                                                ?>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" name="solucion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 150px" disabled><?= $tarea[6]; ?>    
                                                            </textarea>
                                                        <label for="floatingTextarea">Materiales utilizados</label>
                                                    </div>
                                                <?php
                                                }

                                                if ($tarea[7] != '') {
                                                ?>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" name="solucion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 100px" disabled><?= $tarea[8]; ?>    
                                                            </textarea>
                                                        <label for="floatingTextarea">Motivo de la cancelación</label>
                                                    </div>
                                                <?php
                                                }

                                                $date = date_create($tarea[8]);
                                                $fechaProblema = date_format($date, 'd/m/Y H:i:s');
                                                ?>
                                                <div class="form-floating mb-3">
                                                    <input type="text" name="fechaProblema" value="<?= $fechaProblema; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                    <label for="floatingInput">Fecha del problema</label>
                                                </div>

                                                <?php
                                                if ($tarea[9] != '') {
                                                    $date = date_create($tarea[9]);
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
                                                    <input type="text" name="area" value="<?= $tarea[10]; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                    <label for="floatingInput">Área donde se desempeña</label>
                                                </div>

                                                <hr>
                                                <div class="form-floating mb-3">
                                                    <textarea class="form-control" name="motivoEliminacion" placeholder="Leave a comment here" id="floatingTextarea" style="height: 150px" disabled><?= $tarea[12]; ?>    
                                                            </textarea>
                                                    <label for="floatingTextarea">Motivo de la eliminación</label>
                                                </div>

                                                <?php
                                                $date = date_create($tarea[13]);
                                                $fechaEliminado = date_format($date, 'd/m/Y H:i:s');
                                                ?>

                                                <div class="form-floating mb-3">
                                                    <input type="text" name="fechaEliminado" value="<?= $fechaEliminado; ?>" class="form-control" id="floatingInput" placeholder="..." disabled>
                                                    <label for="floatingInput">Fecha de eliminación</label>
                                                </div>


                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btnCerrarModalEditar" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </body>

        </html>
<?php
} else {
    require "destroySession.php";
}
?>