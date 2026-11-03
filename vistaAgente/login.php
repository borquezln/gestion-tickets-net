<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Gestion de Tareas</title>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="../styles/styles.css">
    <style>
        
    </style>
</head>

<body>
    <nav class="login">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Gestión de Tareas</span>
        </div>
    </nav>
    <div class="contenedor">

        <form class="login" action="../controlador/c_login.php" method="post">
            <h4>Acceder</h4>
            <p style="color: white;" class="fs-6">Ingrese sus credenciales para acceder</p>

            <?php
            error_reporting(0);
            session_start();
            if (isset($_SESSION['errLogin'])) {
            ?>
                <div class="alert alert-danger" role="alert">
                    Datos Erróneos. Ingrese nuevamente
                </div>
            <?php
                unset($_SESSION['errLogin']);
            }

            if (isset($_SESSION['session_caducada'])) {
            ?>

                <div class="alert alert-warning" role="alert">
                    Sesión caducada.
                    <br>
                    Iniciar sesión nuevamente.
                </div>

            <?php
                unset($_SESSION['session_caducada']);
            }
            ?>

            <div class="form-floating mb-3">
                <input type="text" name="user" class="form-control" id="floatingInput" placeholder="Usuario">
                <label for="floatingInput">Usuario</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Contraseña">
                <label for="floatingPassword">Contraseña</label>
            </div>
            <div id="btn">
                <button id="login" type="submit">Acceder</button>
            </div>
        </form>

    </div>
</body>

</html>