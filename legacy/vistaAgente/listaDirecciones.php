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
                function comprobarCodigo(codigo) {
                    $.ajax({
                        type: 'POST',
                        url: 'validacionDatosRepetidos/validarAreaExistente.php',
                        data: 'codigo=' + codigo.value,
                        success: function(r) {
                            $('#codigoExistente').html(r);
                        }
                    });
                }

                $(document).ready(function() {
                    $('#tablaDinamicaLoad').DataTable({
                        aLengthMenu: [30, 50, 100, 200],
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

            if (isset($_SESSION['direccionOk'])) {
                alerta("La dirección ha sido añadida exitosamente");
                unset($_SESSION['direccionOk']);
            } else if (isset($_SESSION['direccionEditada'])) {
                alerta("La dirección ha sido modificada");
                unset($_SESSION['direccionEditada']);
            } else if (isset($_SESSION['direccionEditadaError'])) {
                alerta("La Dirección no se ha podido modificar. Verificar el código", "error");
                unset($_SESSION['direccionEditadaError']);
            } else if (isset($_SESSION['eliminadoOk'])) {
                alerta("La Dirección ha sido eliminada");
                unset($_SESSION['eliminadoOk']);
            }

            ?>

            <section id="container">
                <p class="fs-5">Lista de Direcciones/Entidades</p>
                <hr>

                <?php
                if ($_SESSION['rol'] == 3) {
                ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                        Agregar Dirección
                    </button>
                <?php
                }
                ?>

                <!--Modal Nueva Dirección-->
                <form action="../controlador/c_agregarDireccion.php" method="post">

                    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">Agregar Dirección</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="fs-6">Ingrese la tarea con sus respectivos datos y asignación a agentes</p>

                                    <div class="form-floating mb-3">
                                        <input type="text" name="codigo" class="form-control codigo" id="floatingInput" placeholder="ejemplo" onkeyup="comprobarCodigo(this)" required>
                                        <label for="floatingInput">Código</label>
                                        <span id="codigoExistente" style="color: #dc3545;">
                                        </span>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="text" name="direccion" class="form-control" id="floatingInput" placeholder="ejemplo" required>
                                        <label for="floatingInput">Nombre de la Dirección o Entidad</label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="text" name="descripcion" class="form-control" id="floatingInput" placeholder="ejemplo">
                                        <label for="floatingInput">Descripción</label>
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

                <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
                    <thead>
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Descripción</th>
                            <?php
                            if ($_SESSION['rol'] == 3) {
                            ?>
                                <th scope="col">Acción</th>
                            <?php
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($listDirecciones as $direccion) {
                        ?>
                            <tr>
                                <td><?= $direccion[0]; ?></td>
                                <td><?= $direccion[1]; ?></td>
                                <td><?= $direccion[2]; ?></td>
                                <?php
                                if ($_SESSION['rol'] == 3) {
                                ?>
                                    <td id="accion">
                                        <div class="btn-group" role="group">
                                            <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acción
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                <li>
                                                    <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $direccion[0]; ?>">
                                                        Editar
                                                    </a>
                                                </li>

                                            </ul>
                                        </div>
                                    </td>
                                <?php
                                }
                                ?>
                            </tr>

                            <!-- Modal Editar Area-->
                            <div class="modal fade" id="modalEditar<?= $direccion[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Editar Dirección o Entidad</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="../controlador/c_editarDireccion.php" method="post" style="display: none;">
                                            <div class="modal-body">

                                                <p class="fs-6">Editar en caso de un error o algún cambio</p>

                                                <input type="hidden" name="codigoAnterior" value="<?= $direccion[0]; ?>">

                                                <div class="form-floating mb-3">
                                                    <input type="number" name="codigo" value="<?= $direccion[0]; ?>" class="form-control codigo" id="floatingInput" placeholder="ejemplo" onkeyup="comprobarCodigo(this)" required>
                                                    <label for="floatingInput">Código (Debe ser númerico)</label>
                                                </div>

                                                <div class="form-floating mb-3">
                                                    <input type="text" name="direccion" value="<?= $direccion[1]; ?>" class="form-control" id="floatingInput" placeholder="ejemplo" required>
                                                    <label for="floatingInput">Nombre de la Direccón o Entidad</label>
                                                </div>

                                                <div class="form-floating mb-3">
                                                    <textarea class="form-control" name="descripcion" value="<?= $direccion[2]; ?>" placeholder="Leave a comment" id="floatingTextarea" style="height: 100px"></textarea>
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
                        <?php
                        }
                        ?>
                    </tbody>
                </table>


            </section>
        </body>

        </html>
<?php
} else {
    require "destroySession.php";
}
?>