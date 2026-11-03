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
                    order: [
                        [1, 'asc']
                    ],
                    aLengthMenu: [25, 50, 100, 200],
                    language: {
                        "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                    }
                });
            })
        </script>
    </head>

    <body>
        <?php
        error_reporting(0);
        require "../scripts/alerta.php";

        if (isset($_SESSION['areaOk'])) {
            alerta("El área ha sido agregada");
            unset($_SESSION['areaOk']);
        } else if (isset($_SESSION['editadoOk'])) {
            alerta("El área ha sido modificada");
            unset($_SESSION['editadoOk']);
        }
        ?>

        <section id="container">
            <p class="fs-5">Áreas</p>
            <hr>
            <div id="btnNuevo">
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#nuevoArea">Nueva área</button>
            </div>
            <div class="table-responsive-xxl">
                <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
                    <thead>
                        <th>Nombre del área</th>
                        <th>Descripción</th>
                        <th>Acción</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($listAreas as $area) {
                        ?>
                            <tr>
                                <td><?= $area[1]; ?></td>
                                <td><?= $area[2]; ?></td>
                                <td id="accion">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarArea<?= $area[0]; ?>"><i class="bi bi-pencil-square"></i>Editar</button>
                                </td>
                            </tr>

                            <!-- Modal Editar Area-->
                            <div class="modal fade" id="editarArea<?= $area[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Editar área</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <form action="../controlador/c_editarArea.php" method="post" style="display: none;">

                                            <input type="hidden" name="id" value="<?= $area[0]; ?>">

                                            <div class="modal-body">

                                                <p class="fs-6">Completar los siguientes campos</p>

                                                <div class="form-floating mb-3">
                                                    <textarea class="form-control" placeholder="Leave a comment here" name="area" id="floatingTextarea2" style="height: 75px" required><?= $area[1]; ?></textarea>
                                                    <label for="floatingTextarea2">Nombre del área</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <textarea class="form-control" placeholder="Leave a comment here" name="descripcion" id="floatingTextarea2" style="height: 150px" required><?= $area[2]; ?></textarea>
                                                    <label for="floatingTextarea2">Descripción del área</label>
                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>

                        <!-- Modal Nueva Area-->
                        <div class="modal fade" id="nuevoArea" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="staticBackdropLabel">Nueva área</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="../controlador/c_agregarArea.php" method="post" style="display: none;">
                                        <div class="modal-body">
                                            <p class="fs-6">Completar los siguientes campos</p>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" placeholder="Leave a comment here" name="area" id="floatingTextarea2" style="height: 75px" required></textarea>
                                                <label for="floatingTextarea2">Escribir el nombre de la nueva área</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" placeholder="Leave a comment here" name="descripcion" id="floatingTextarea2" style="height: 150px" required></textarea>
                                                <label for="floatingTextarea2">Escribir una breve descripción del área</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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