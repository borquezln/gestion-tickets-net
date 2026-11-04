<?php
error_reporting(E_ALL ^ E_NOTICE);
session_start();
if (isset($_SESSION['rol'])) {
?>
    <link rel="stylesheet" href="../styles/headerNav.css">
    <script>
        $(document).ready(function() {

            $('#iconToggle').click(function() {
                $('#navHeader').toggleClass('expandHeader');
                $('#navSidebar').toggleClass('hideSidebar');
                $('#container').toggleClass('expandContainer');
                //$('.sidebar').hide(200);
            });

            $('#menu').hide();
            $('#showMenu').click(function() {
                $('#menu').toggle(100);
                $('#showMenu i').toggleClass('bi bi-caret-up');
            })


            $('.mostrarPass').click(function() {
                if ($('.mostrarPass').is(':checked')) {
                    $('.pass').prop('type', 'text');
                } else {
                    $('.pass').prop('type', 'password');
                }
            });

            $('#btnCambiarPass').click(function() {
                $('#btnCambiarPass').hide();
                $('#cambiarPass').show();
                $('#passRepetido').prop('required', 'required');
                $('#btnCancelarPass').show();
            })

            $('#btnCancelarPass').click(function() {
                $('#btnCancelarPass').hide();
                $('#btnCambiarPass').show();
                $('#cambiarPass').hide();
                $('#passRepetido').removeAttr('required');
                $('.pass').val('');
                if ($('.mostrarPass').is(':checked')) {
                    $('.mostrarPass').prop('checked', false);
                    $('#cambiarPass input[type=text]').val('');
                    $('.pass').prop('type', 'password');
                }
            })

            $('#desplegarMenuEst').hide();
            $('#menuEst').click(function() {
                $('#desplegarMenuEst').toggle(150);
                $('#menuEst i').toggleClass('bi bi-caret-up');
            });


            $('#desplegarMenuTarea').hide();
            $('#menuTarea').click(function() {
                $('#desplegarMenuTarea').toggle(150);
                $('#menuTarea i').toggleClass('bi bi-caret-up');

            });

            $('#desplegarMenuUsuarios').hide();
            $('#menuUsuarios').click(function() {
                $('#desplegarMenuUsuarios').toggle(150);
                $('#menuUsuarios i').toggleClass('bi bi-caret-up');

            });

        });

        function verificarPassIguales(confirmarPass) {
            var pass = $('#nuevaPass').val();
            if (pass != confirmarPass.value) {
                document.getElementById('passRepetido').setCustomValidity('Las contraseñas deben ser iguales');
            } else {
                document.getElementById('passRepetido').setCustomValidity('');
            }
        }

        //MODAL NUEVO USUARIO
        function validarUsernameExistente(username) {
            $.ajax({
                type: 'POST',
                url: 'validacionDatosRepetidos/validarUsernameExistente.php',
                data: 'usuario=' + username.value,
                success: function(r) {
                    $('#userExistente').html(r);
                }
            });
        }
    </script>

    <nav id="navHeader" class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top header">
        <div class="container-fluid">
            <div id="buttonText">
                <button id="iconToggle" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand" style="margin-left: 5px;">Gestión de tareas</a>
            </div>

            <ul class="navbar-nav settingsUser">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $_SESSION['nombreApellido']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="navbarDarkDropdownMenuLink">
                        <li><a class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">Gestionar usuario actual</a></li>
                        <li><a class="dropdown-item" href="logout.php">Cerrar sesión</a></li>
                    </ul>

                </li>
            </ul>
        </div>
    </nav>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form action="../controlador/c_editarUsuario.php" method="post">
            <input type="hidden" name="userAnterior" value="<?= $_SESSION['username']; ?>">
            <input type="hidden" name="rol" value="<?= $_SESSION['rol']; ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Gestionar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <?php
                        require_once('../modelo/m_consultas.php');
                        $co = new Consultas();
                        $datosPersonales = $co->listarDatosPersonales($_SESSION['username']);
                        $fechaActual = $co->fechaActual();
                        foreach ($datosPersonales as $row) {
                        ?>

                            <div class="form-floating mb-3 input">
                                <input type="number" name="legajo" class="form-control" value="<?= $row[0]; ?>" id="floatingInput" placeholder="legajo" readonly>
                                <label for="floatingInput">Legajo</label>
                            </div>
                            <div class="form-floating mb-3 input">
                                <input type="text" name="nombre" class="form-control" value="<?= $row[1]; ?>" id="floatingInput" placeholder="Nombre">
                                <label for="floatingInput">Nombre</label>
                            </div>
                            <div class="form-floating mb-3 input">
                                <input type="text" name="apellido" class="form-control" value="<?= $row[2]; ?>" id="floatingInput" placeholder="Apellido">
                                <label for="floatingInput">Apellido</label>
                            </div>
                            <div class="form-floating mb-3 input">
                                <input type="email" name="correo" class="form-control" value="<?= $row[3]; ?>" id="floatingInput" placeholder="Correo">
                                <label for="floatingInput">Correo</label>
                            </div>
                            <div class="form-floating mb-3 input">
                                <input type="text" name="user" class="form-control" value="<?= $row[4]; ?>" id="floatingInput" placeholder="Usuario">
                                <label for="floatingInput">Usuario</label>
                            </div>
                            <button type="button" id="btnCambiarPass" class="btn btn-warning">Cambiar Contraseña</button>
                            <div id="cambiarPass" style="display: none;">
                                <hr>
                                <span>Ingrese la nueva contraseña</span>
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control pass" id="nuevaPass" placeholder="Contraseña">
                                    <label for="floatingInput">Contraseña</label>
                                </div>
                                <span>Repetir la nueva contraseña</span>
                                <div class="form-floating mb-3">
                                    <input type="password" name="pass" class="form-control pass" id="passRepetido" onchange="verificarPassIguales(this);" placeholder="Contraseña">
                                    <label for="floatingInput">Contraseña</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input mostrarPass" type="checkbox" value="" id="flexCheckDefault">
                                    <label class="form-check-label" for="flexCheckDefault">
                                        Mostrar Contraseña
                                    </label>
                                </div>
                                <button id="btnCancelarPass" type="button" class="btn btn-warning" title="Cancela el cambio de contraseña">Cancelar</button>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>

                <?php
                        }
                ?>

                </div>
            </div>
        </form>
    </div>

    <!-- Modal Filtros Fechas Estadisticas -->
    <div class="modal fade" id="fechas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Estadisticas por fecha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?accion=verEstadisticas&time=month" style="margin-bottom: 0" method="post">
                    <div class="modal-body">

                        <p class="fs-6">Definir el rango de fechas para luego visualizar las estadísticas durante ese período</p>

                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" name="fechaInicio" id="floatingInput" placeholder="example">
                            <label for="floatingInput">Fecha Inicio</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" name="fechaFin" id="floatingInput" placeholder="example">
                            <label for="floatingInput">Fecha Fin</label>
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


    <!--SIDEBAR-->
    <nav id="navSidebar" class="navbar-dark bg-dark fixed-top sidebar" style="display: flex; flex-direction: column; justify-content: space-between;">

        <div id="sidebarSuperior">
            <div id="txtRol">
                <?php
                if ($_SESSION['rol'] == 3) {
                ?>
                    <span class="fs-5 titlePage">Administrador</span>
                <?php
                } else {
                ?>
                    <span class="fs-5 titlePage">Administrativo</span>
                <?php
                }
                ?>
            </div>


            <div class="navbar-nav bg-dark">
                <a class="nav-link" aria-current="page" href="inicio.php"><i class="bi bi-gear"></i>Panel de Control</a>


                <a class="nav-link" role="button" id="menuEst" aria-current="page" style="display: flex; justify-content: space-between; align-items: center;">
                    <div id="textItem">
                        <i class="bi bi-list-check"></i>
                        <span style="margin-left: -5px;">
                            Estadísticas
                        </span>
                    </div>
                    <i class="bi bi-caret-down"></i>
                </a>

                <ul class="navbar-nav" id="desplegarMenuEst">

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php?accion=verEstadisticas&time=now">Creados en el día actual</a>
                    </li>
                    <!-- Button trigger modal -->
                    <li>
                        <a class="nav-link" role="button" aria-current="page" data-bs-toggle="modal" data-bs-target="#fechas">Rango por fechas</a>
                    </li>
                    <li>
                        <a class="nav-link" aria-current="page" href="index.php?accion=verEstadisticas&time=year">Creados en el año actual</a>
                    </li>
                    <li>
                        <a class="nav-link" aria-current="page" href="index.php?accion=verEstadisticas&time=all">Requerimientos en total</a>
                    </li>
                </ul>


                <a class="nav-link" role="button" id="menuTarea" aria-current="page" style="display: flex; justify-content: space-between; align-items: center;">
                    <div id="textItem">
                        <i class="bi bi-list-check"></i>
                        <span style="margin-left: -5px;">
                            Tareas
                        </span>
                    </div>
                    <i class="bi bi-caret-down"></i>
                </a>

                <ul class="navbar-nav" id="desplegarMenuTarea">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php?accion=listarTareasAdmin&lista=actual">Tareas pendientes y En progreso</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php?accion=listarTareasAdmin&lista=completas">Tareas completadas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php?accion=listarTareasAdmin&lista=canceladas">Tareas canceladas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php?accion=listarTareasEliminadas">Tareas eliminadas</a>
                    </li>
                </ul>

                <a class="nav-link" aria-current="page" href="index.php?accion=listarAreas">
                    <i class="bi bi-list-nested"></i>Listado de áreas
                </a>

                <a class="nav-link" aria-current="page" href="index.php?accion=listarDirecciones"><i class="bi bi-list-ul"></i>Direcciones/Entidades</a>

                <?php
                if ($_SESSION['rol'] == 3) {
                ?>
                    <a class="nav-link" role="button" id="menuUsuarios" aria-current="page" style="display: flex; justify-content: space-between; align-items: center;">
                        <div id="textItem">
                            <i class="bi bi-people"></i>
                            <span style="margin-left: -5px;">
                                Usuarios
                            </span>
                        </div>
                        <i class="bi bi-caret-down"></i>
                    </a>

                    <ul class="navbar-nav" id="desplegarMenuUsuarios">
                        <li class="nav-item">
                            <a class="nav-link" role="button" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">Agregar nuevo Usuario</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="index.php?accion=listarUsuarios">Listado de Usuarios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="index.php?accion=listarBajas">Listado de bajas</a>
                        </li>
                    </ul>

                    <a class="nav-link" aria-current="page" href="index.php?accion=blanqueoPass"><i class="bi bi-key"></i>Blanquear contraseña</a>
                <?php
                }
                ?>
            </div>

        </div>

        <div id="sidebarInferior">
            <p class="fs-6 fw-bold text-center">
                <?php
                $format = date_create($fechaActual);
                $fechaActual = date_format($format, 'd/m/Y');
                echo $fechaActual;
                ?>
            </p>
        </div>



    </nav>

    <!-- Modal Nuevo Usuario -->
    <form action="../controlador/c_agregarUsuario.php" method="post">
        <div class="modal fade" id="modalNuevoUsuario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Agregar Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <?php
                        require_once('../modelo/m_consultas.php');
                        $co = new Consultas();
                        $listRoles = $co->listarRoles();
                        ?>

                        <div class="form-floating mb-3">
                            <select class="form-select" onchange="mostrarSelectArea(this);" name="selectRol" id="floatingSelect" aria-label="Floating label select example" required>
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

                        <div class="form-floating mb-3">
                            <input type="number" name="legajo" min="1000" max="19999" class="form-control" id="floatingInput" placeholder="ejemplo" required>
                            <label for="floatingInput">Legajo</label>

                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="nombre" class="form-control" id="floatingInput" placeholder="ejemplo" required>
                            <label for="floatingInput">Nombre</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="apellido" class="form-control" id="floatingInput" placeholder="ejemplo" required>
                            <label for="floatingInput">Apellido</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" name="correo" class="form-control" id="floatingInput" placeholder="ejemplo">
                            <label for="floatingInput">Correo (Opcional)</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="user" oninput="validarUsernameExistente(this);" class="form-control" id="floatingInput" placeholder="ejemplo" required>
                            <label for="floatingInput">Nombre de Usuario</label>
                            <span id="userExistente" style="color: #dc3545;"></span>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="pass" class="form-control" id="floatingInput" placeholder="ejemplo" required>
                            <label for="floatingInput">Contraseña</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary">Agregar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?php
}
?>