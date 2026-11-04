<?php
error_reporting(E_ALL ^ E_NOTICE);
session_start();
if (!(time() - $_SESSION['time'] >= 3600)) {
    $_SESSION['time'] = time();
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="3600">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inicio - Gestión de tareas</title>
        <?php require('libreriaEstilos.php'); ?>

        <link rel="stylesheet" href="../styles/styles.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js" integrity="sha512-sW/w8s4RWTdFFSduOTGtk4isV1+190E/GghVffMA9XczdJ2MDzSzLEubKAs5h0wzgSJOQTRYyaz73L3d6RtJSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>

    <body>
        <?php
        require('headerNav.php');
        // error_reporting(0);
        require "../scripts/alerta.php";

        if (isset($_SESSION['datosAct'])) {
            alerta("Los datos han sido modificados");
            unset($_SESSION['datosAct']);
        } else if (isset($_SESSION['datosActError'])) {
            alerta("No se han podido modificar los datos", "error");
            unset($_SESSION['datosActError']);
        }
        ?>

        <?php
        require_once('../modelo/m_estadisticas.php');
        require_once('../modelo/m_consultas.php');
        $co = new Consultas();
        $est = new ConsultasEstadisticas();
        $totalTareasSistemas = $est->contarTareasTotal();
        $tareasPendientes = $co->contarTareasEstado("Pendiente");
        $tareasEnProgreso = $co->contarTareasEstado("En Progreso");
        $tareasCompletas = $co->contarTareasEstado("Completo");
        $tareasCanceladas = $co->contarTareasEstado("Cancelado");
        $totalTareasEliminadas = $co->contarTareasEstado("Eliminado");
        //Usuarios
        $totalUsuarios = $co->contarTotalUsuarios();
        $totalAdmin = $co->contarUsuariosRol(3);
        $totalSuperv = $co->contarUsuariosRol(4);
        //Areas
        $totalDirecciones = $co->contarDirecciones();
        ?>

        <section id="container">
            <p class="fs-5">Bienvenido <?= $_SESSION['nombreApellido']; ?></p>

            <p class="fs-5 text-center">Tareas</p>
            <div class="row justify-content-center">
                <div class="card border-dark mb-3 col-auto">
                    <div class="card-body text-dark d-flex flex-column align-items-center">
                        <i class="bi bi-clipboard-data" style="font-size: 40px;"></i>
                        <h1 class="card-title"><?= $totalTareasSistemas; ?></h1>
                        <p class="card-text">Total de tareas</p>
                    </div>
                </div>

                <a href="index.php?accion=listarTareasEliminadas" class="col-auto text-dark">
                    <div class="card border-dark mb-3 ms-5">
                        <div class="card-body d-flex flex-column align-items-center">
                            <i class="bi bi-clipboard-data" style="font-size: 40px;"></i>
                            <h1 class="card-title"><?= $totalTareasEliminadas; ?></h1>
                            <p class="card-text">Total de eliminadas</p>

                        </div>
                    </div>
                </a>
            </div>


            <div id="estados" class="row justify-content-evenly">
                <a href="index.php?accion=listarTareasAdmin&lista=actual" class="col-auto text-secondary">
                    <div class="card border-secondary mb-3">
                        <div class="card-body d-flex flex-column align-items-center">
                            <i class="bi bi-list-task" style="font-size: 40px;"></i>
                            <h1 class="card-title"><?= $tareasPendientes; ?></h1>
                            <p class="card-text">Total de tareas 'Pendientes'</p>
                        </div>
                    </div>
                </a>

                <a href="index.php?accion=listarTareasAdmin&lista=actual" class="col-auto text-primary">
                    <div class="card border-primary mb-3">
                        <div class="card-body d-flex flex-column align-items-center">
                            <i class="bi bi-list-ul" style="font-size: 40px;"></i>
                            <h1 class="card-title"><?= $tareasEnProgreso; ?></h1>
                            <p class="card-text">Total de tareas 'En progreso'</p>
                        </div>
                    </div>
                </a>

                <a href="index.php?accion=listarTareasAdmin&lista=completas" class="col-auto text-success">
                    <div class="card border-success mb-3">
                        <div class="card-body d-flex flex-column align-items-center">
                            <i class="bi bi-list-check" style="font-size: 40px;"></i>
                            <h1 class="card-title"><?= $tareasCompletas; ?></h1>
                            <p class="card-text">Total de tareas 'Completas'</p>
                        </div>
                    </div>
                </a>

                <a href="index.php?accion=listarTareasAdmin&lista=canceladas" class="col-auto text-danger">
                    <div class="card border-danger mb-3">
                        <div class="card-body d-flex flex-column align-items-center">
                            <i class="bi bi-clipboard-x" style="font-size: 40px;"></i>
                            <h1 class="card-title"><?= $tareasCanceladas; ?></h1>
                            <p class="card-text">Total de tareas 'Canceladas'</p>
                        </div>
                    </div>
                </a>
            </div>

            <?php
            if ($_SESSION['rol'] == 3) {
            ?>
                <p class="fs-5 text-center">Usuarios</p>

                <div class="card border-dark mb-3">
                    <div class="card-body text-dark row justify-content-evenly">
                        <a href="index.php?accion=listarUsuarios" class="mb-3 text-dark">
                            <h1 class="card-title"><?= $totalUsuarios; ?></h1>
                            <p class="card-text">Total de Usuarios</p>
                        </a>

                        <div class="card border-info mb-3 col-auto">
                            <div class="card-body text-info d-flex flex-column align-items-center">
                                <i class="bi bi-person-lines-fill" style="font-size: 40px;"></i>
                                <h1 class="card-title"><?= $totalAdmin; ?></h1>
                                <p class="card-text">Administradores</p>
                            </div>
                        </div>

                        <div class="card border-info mb-3 col-auto">
                            <div class="card-body text-info d-flex flex-column align-items-center">
                                <i class="bi bi-person-lines-fill" style="font-size: 40px;"></i>
                                <h1 class="card-title"><?= $totalSuperv; ?></h1>
                                <p class="card-text">Administrativos</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>

            <p class="fs-5 text-center">Direcciones/Entidades</p>

            <a href="index.php?accion=listarDirecciones" class="text-dark">
                <div class="card border-dark mb-3 col-md-12">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h1 class="card-title"><?= $totalDirecciones; ?></h1>
                            <i class="bi bi-list" style="font-size: 40px;"></i>
                        </div>
                        <p class="card-text">Total</p>
                    </div>
                </div>
            </a>

        </section>
    </body>

    </html>
<?php
} else {
    require "destroySession.php";
}
?>