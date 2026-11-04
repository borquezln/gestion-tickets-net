<?php
error_reporting(E_ALL ^ E_NOTICE || E_WARNING);
if (!(time() - $_SESSION['time'] >= 3600)) {
    $_SESSION['time'] = time();
    if ($_SESSION['rol'] == 3) {
?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="3600">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
        </head>

        <link rel="stylesheet" href="../styles/styles.css">
        <script>
            function bajaUsuario() {
                var nombre = $('.nombre').val();
                var apellido = $('.apellido').val();
                event.preventDefault();
                Swal.fire({
                    title: 'Aviso',
                    text: "Dar de baja a " + nombre + ' ' + apellido + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        return true;
                    }
                    return false;
                })
            }

            $(document).ready(function() {
                $('#tablaDinamicaLoad').DataTable({
                    aLengthMenu: [50, 100, 200],
                    order: [
                        [5, 'asc']
                    ],
                    language: {
                        "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                    }
                })
            })
        </script>

        <body>
            <?php
            // error_reporting(0);
            require "../scripts/alerta.php";

            if (isset($_SESSION['usuarioAgregado'])) {
                alerta("El usuario ha sido agregado al sistema");
                unset($_SESSION['usuarioAgregado']);
            } else if (isset($_SESSION['usuarioError'])) {
                alerta("No se ha podido añadir el usuario. Revise los datos e intente nuevamente", "error");
                unset($_SESSION['usuarioError']);
            }
            ?>

            <section id="container">
                <p class="fs-5">Lista de usuarios cargados</p>
                <hr>
                <p>Se muestra el listado de todos los usuarios registrados en el Sistema</p>

                <div class="table-responsive-xxl">
                    <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
                        <thead>
                            <tr>
                                <th scope="col">legajo</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Apellido</th>
                                <th scope="col">Correo</th>
                                <th scope="col">Nombre de usuario</th>
                                <th scope="col">Rol</th>
                                <th scope="col">Último acceso</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($listUsuarios as $usuario) {
                            ?>
                                <tr>
                                    <td><?= $usuario[0]; ?></td>
                                    <td><?= $usuario[1]; ?></td>
                                    <td><?= $usuario[2]; ?></td>
                                    <td><?= $usuario[3]; ?></td>
                                    <td><?= $usuario[4]; ?></td>
                                    <td><?= $usuario[5]; ?></td>
                                    <td>
                                        <?php
                                        if ($usuario[6] != '' || $usuario[6] != null) {
                                            $date = date_create($usuario[6]);
                                            $ultimologin = date_format($date, 'd/m/Y H:i:s');
                                            echo $ultimologin;
                                        } else {
                                            echo $usuario[6];
                                        }
                                        ?>
                                    </td>
                                    <td id="accion">
                                        <div class="btn-group" role="group">
                                            <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acción
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                <li>
                                                    <a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalMotivoCancelacion<?= $usuario[0]; ?>">
                                                        Dar de Baja
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>

                                    <!-- Modal Dar de baja-->
                                    <div class="modal fade" id="modalMotivoCancelacion<?= $usuario[0]; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staticBackdropLabel">Dar de baja: <?= $usuario[1] . ' ' . $usuario[2]; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="../controlador/c_bajaUsuario.php" method="post" style="display: none;">
                                                    <div class="modal-body">

                                                        <p class="fs-6">Para continuar, debe escribir el motivo de la baja</p>

                                                        <input type="hidden" name="legajo" value="<?= $usuario[0]; ?>">

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="motivoBaja" placeholder="Leave a comment" id="floatingTextarea" style="height: 100px" required></textarea>
                                                            <label for="floatingTextarea">Motivo</label>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-danger">Dar de baja</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </tr>
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
    }
} else {
    require "destroySession.php";
}
?>