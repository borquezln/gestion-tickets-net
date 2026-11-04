<?php
error_reporting(E_ALL ^ E_NOTICE);
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

            <link rel="stylesheet" href="../styles/styles.css">
            <script>
                function bajaAgente() {
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
                        language: {
                            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                        }
                    })
                })
            </script>
        </head>

        <body>
            <section id="container">
                <?php
                error_reporting(0);
                require "../scripts/alerta.php";

                if (isset($_SESSION['agenteEditado'])) {
                    alerta("Los datos del agente han sido modificados");
                    unset($_SESSION['agenteEditado']);
                } else if (isset($_SESSION['bajaOk'])) {
                    alerta("El agente ha sido dado de baja");
                    unset($_SESSION['bajaOk']);
                }
                ?>

                <p class="fs-5">Lista de Agentes que están de baja del Sistema</p>
                <hr>
                <table class="table table-bordered" id="tablaDinamicaLoad">
                    <thead>
                        <tr>
                            <th scope="col">legajo</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Motivo de la baja</th>
                            <th scope="col">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($listBajas as $list) {
                        ?>
                            <tr>
                                <td><?= $list[0]; ?></td>
                                <td><?= $list[1]; ?></td>
                                <td><?= $list[2]; ?></td>
                                <td><?= $list[3]; ?></td>
                                <td><?= $list[4]; ?></td>
                                <td id="accion">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAlta<?= $list[0]; ?>">
                                        Dar de alta
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Alta -->
                            <div class="modal fade" id="modalAlta<?= $list[0]; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Dar de alta: <?= $list[1] . ' ' . $list[2]; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="../controlador/c_altaAgente.php" method="post" style="display: none;">
                                            <div class="modal-body">

                                                <p class="fs-6">Completar lo siguente.</p>

                                                <input type="hidden" name="legajo" value="<?= $list[0]; ?>">

                                                <div class="form-floating mb-3">
                                                    <select class="form-select" name="selectRol" id="floatingSelect" aria-label="Floating label select example" required>
                                                        <option value="" selected>Seleccione...</option>
                                                        <?php
                                                        foreach ($listRoles as $rol) {
                                                        ?>
                                                            <option value="<?= $rol[0]; ?>"><?= $rol[1]; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <label for="floatingSelect">Elegir tipo de Usuario</label>
                                                </div>


                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-success">Dar de alta</button>
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
    }
} else {
    require "destroySession.php";
}
?>