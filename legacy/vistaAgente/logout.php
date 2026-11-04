<?php
session_start();
session_destroy();
header('Location: ../vistaAgente/login.php');
