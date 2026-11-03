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
        <title>Estadísticas - Gestión</title>

        <link rel="stylesheet" href="../styles/styles.css">
        
    </head>

    <body>
        <?php
        error_reporting(0);
        ?>

        <section id="container">
            <p class="fs-5">Estadísticas <?= $periodo ?></p>
            <hr>
            <p class="fs-5">Por estado</p>
            <div class="table-responsive-xxl">
                <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
                    <thead>
                        <th>Estado</th>
                        <th>Cantidad</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Pendientes</td>
                            <td><?= $totalPendientes[0][0] ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">En Progreso</td>
                            <td><?= $totalEnProgreso[0][0] ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Completas</td>
                            <td><?= $totalCompletas[0][0] ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Canceladas</td>
                            <td><?= $totalCanceladas[0][0] ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Eliminadas</td>
                            <td><?= $totalEliminadas[0][0] ?></td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <p class="fs-5">Por área</p>
                <div class="table-responsive-xxl">
                    <table class="table table-responsive table-bordered table-hover" id="tablaDinamicaLoad">
                        <thead>
                            <th>Área</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($areas as $area) {
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= $area[1] ?></td>
                                    <td><?= $area[2] ?></td>
                                    <td><?= $totalAreas[$area[1]][0][0] ?></td>
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
} else {
    require "destroySession.php";
}
?>