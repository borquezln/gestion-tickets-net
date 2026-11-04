<?php
require_once('m_conexion.php');

class Consultas extends Conexion
{
    // AUTENTICACION
    public function autenticarUsuario($usuario, $pass)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM usuario WHERE usuario = '$usuario' OR correo = '$usuario'";
            $result = mysqli_query($link, $sql);
            while ($registro = mysqli_fetch_row($result)) {
                return password_verify($pass, $registro[5]);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function verificarTipoUsuario($usuario)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM tipousuario WHERE idrol IN
                        (SELECT idRol2 FROM usuario WHERE usuario = '$usuario' OR correo = '$usuario')";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $id = $row[0];
            }
            return $id;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    // USUARIOS
    public function listarUsuarios()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT u.legajo, u.nombre, u.apellido, u.correo, u.usuario, tu.nombre
                        FROM usuario AS u, tipousuario AS tu
                        WHERE u.idRol2 = tu.idRol ORDER BY tu.nombre";
            $result = mysqli_query($link, $sql);
            $listUsuarios = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listUsuarios[$i] = $row;
                $i++;
            }
            return $listUsuarios;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function listarUsuariosCargados()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT u.legajo, u.nombre, u.apellido, u.correo, u.usuario, t.nombre, u.ultimoAcceso
                        FROM usuario u, tipousuario t
                        WHERE u.idRol2 = t.idrol";
            $result = mysqli_query($link, $sql);
            $listUsuarios = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listUsuarios[$i] = $row;
                $i++;
            }
            return $listUsuarios;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function listarRoles()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM tipousuario";
            $result = mysqli_query($link, $sql);
            $listRoles = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listRoles[$i] = $row;
                $i++;
            }
            return $listRoles;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function listarBajasUsuarios()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT u.legajo, u.nombre, u.apellido, u.correo, u.motivoBaja FROM usuario u WHERE u.idRol2 IS NULL AND u.legajo != 0";
            $result = mysqli_query($link, $sql);
            $listBajas = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listBajas[$i] = $row;
                $i++;
            }
            return $listBajas;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function agregarUsuario($tipoUsuario, $legajo, $nombre, $apellido, $correo, $user, $pass)
    {
        try {
            $passFuerte = password_hash($pass, PASSWORD_DEFAULT);
            $link = parent::conexionBD();
            $sql = "INSERT INTO usuario(legajo, nombre, apellido, correo, usuario, contraseña, idRol2)
                        values ('$legajo', '$nombre', '$apellido', '$correo', '$user', '$passFuerte', '$tipoUsuario')";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }


    // GESTIONAR USUARIO
    public function listarlegajoUserActual($user)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT u.legajo FROM usuario u WHERE u.usuario = '$user'";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $legajo = $row[0];
            }
            return $legajo;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function listarUserActual($legajo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT concat(u.nombre, ' ', u.apellido) FROM usuario u WHERE u.legajo = '$legajo'";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $userActual = $row[0];
            }
            return $userActual;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function verificarUltimoAcceso($usuario)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE usuario SET ultimoAcceso = now() WHERE usuario = '$usuario'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function listarDatosPersonales($usuario)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM usuario WHERE usuario = '$usuario' OR correo = '$usuario'";
            $result = mysqli_query($link, $sql);
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $datosPersonales[$i] = $row;
                $i++;
            }
            return $datosPersonales;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function mostrarDatosUsuario($usuario)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT concat(u.nombre, ' ', u.apellido) FROM usuario u WHERE u.usuario = '$usuario' OR u.correo = '$usuario'";
            $result = mysqli_query($link, $sql);
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $nombreApellido = $row[0];
                $i++;
            }
            return $nombreApellido;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function editarDatosUsuario($legajo, $nombre, $apellido, $correo, $user, $pass, $userAnterior)
    {
        try {
            if ($pass == NULL || $pass == '') {
                $sql = "UPDATE usuario SET legajo = '$legajo', nombre = '$nombre', apellido = '$apellido', correo = '$correo', usuario = '$user'
                                WHERE usuario = '$userAnterior'";
            } else {
                $passFuerte = password_hash($pass, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario SET legajo = '$legajo', nombre = '$nombre', apellido = '$apellido', correo = '$correo', usuario = '$user', contraseña = '$passFuerte'
                                WHERE usuario = '$userAnterior'";
            }
            $link = parent::conexionBD();
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function asignarRolUsuario($idRol, $legajo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE usuario SET idRol2 = '$idRol' WHERE legajo = '$legajo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function blanquearPass($legajo, $pass)
    {
        try {
            $passFuerte = password_hash($pass, PASSWORD_DEFAULT);
            $link = parent::conexionBD();
            $sql = "UPDATE usuario SET contraseña = '$passFuerte' WHERE legajo = '$legajo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function bajaUsuario($legajo, $motivoBaja)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE usuario SET idRol2 = NULL, motivoBaja = '$motivoBaja' WHERE legajo = '$legajo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function altaUsuario($idRol, $legajo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE usuario SET idRol2 = '$idRol', motivoBaja = NULL WHERE legajo = '$legajo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }


    // LISTADOS
    public function listarAreas()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM areas a";
            $result = mysqli_query($link, $sql);
            $listAreas = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listAreas[$i] = $row;
                $i++;
            }
            return $listAreas;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function listarEstadoTarea()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM estadotarea";
            $result = mysqli_query($link, $sql);
            $listEstado = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listEstado[$i] = $row;
                $i++;
            }
            return $listEstado;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    // TAREAS
    public function listarTareasAdmin($estado)
    {
        try {
            $link = parent::conexionBD();
            if ($estado == 'actual') {
                $sql = "SELECT t.nroArreglo, t.descripcion, t.nota_electronica, t.nombreApellidoAfectado, t.celular, t.solucion, e.id, e.nombre, t.motivoCancelacion,
                            t.fechaProblema, t.fechaSolucion, d.codigo, d.nombre, u.legajo, concat(u.nombre, ' ', u.apellido) AS nombre_apellido, a.codigo, a.nombre, t.usuarioCreado
                            FROM tareas t, estadotarea e, direcciones d, usuario u, areas a
                            WHERE t.estadoTarea_id = e.id AND t.direccion_codigo = d.codigo AND t.usuarioCreado = concat(u.nombre, ' ', u.apellido)
                            AND t.codigoArea3 = a.codigo AND (t.estadoTarea_id = 1 OR t.estadoTarea_id = 2)";
            } else if ($estado == 'completas') {
                $sql = "SELECT t.nroArreglo, t.descripcion, t.nota_electronica, t.nombreApellidoAfectado, t.celular, t.solucion, e.id, e.nombre, t.motivoCancelacion,
                            t.fechaProblema, t.fechaSolucion, d.codigo, d.nombre, u.legajo, concat(u.nombre, ' ', u.apellido) AS nombre_apellido, a.codigo, a.nombre, t.usuarioCreado, t.comprobante
                            FROM tareas t, estadotarea e, direcciones d, usuario u, areas a
                            WHERE t.estadoTarea_id = e.id AND t.direccion_codigo = d.codigo AND t.usuarioCreado = concat(u.nombre, ' ', u.apellido)
                            AND t.codigoArea3 = a.codigo AND t.estadoTarea_id = 3";
            } else if ($estado == 'canceladas') {
                $sql = "SELECT t.nroArreglo, t.descripcion, t.nota_electronica, t.nombreApellidoAfectado, t.celular, t.solucion, e.id, e.nombre, t.motivoCancelacion,
                            t.fechaProblema, t.fechaSolucion, d.codigo, d.nombre, u.legajo, concat(u.nombre, ' ', u.apellido) AS nombre_apellido, a.codigo, a.nombre, t.usuarioCreado
                            FROM tareas t, estadotarea e, direcciones d, usuario u, areas a
                            WHERE t.estadoTarea_id = e.id AND t.direccion_codigo = d.codigo AND t.usuarioCreado = concat(u.nombre, ' ', u.apellido)
                            AND t.codigoArea3 = a.codigo AND t.estadoTarea_id = 4";
            }

            $result = mysqli_query($link, $sql);
            $listTareas = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listTareas[$i] = $row;
                $i++;
            }
            return $listTareas;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listarTareasCompletasActual()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT t.nroArreglo, t.descripcion, t.nota_electronica, t.nombreApellidoAfectado, t.celular, t.solucion, e.id, e.nombre, t.motivoCancelacion,
                        t.fechaProblema, t.fechaSolucion, d.codigo, d.nombre, u.legajo, concat(u.nombre, ' ', u.apellido) AS nombre_apellido, a.codigo, a.nombre, t.usuarioCreado, t.comprobante
                        FROM tareas t, estadotarea e, direcciones d, usuario u, areas a
                        WHERE t.estadoTarea_id = e.id AND t.direccion_codigo = d.codigo AND t.usuarioCreado = concat(u.nombre, ' ', u.apellido)
                        AND t.codigoArea3 = a.codigo AND t.estadoTarea_id = 3 AND date(t.fechaSolucion) = curdate()";
            $result = mysqli_query($link, $sql);
            $listTareasCompletas = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listTareasCompletas[$i] = $row;
                $i++;
            }
            return $listTareasCompletas;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listarTareasEliminadas()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT t.nroArreglo, t.descripcion, t.nota_electronica, t.nombreApellidoAfectado, t.celular, t.solucion, e.nombre, t.motivoCancelacion,
                        t.fechaProblema, t.fechaSolucion, d.nombre, concat(u.nombre, ' ', u.apellido) AS nombreApellido, t.motivoEliminacion, t.fechaEliminado
                        FROM tareas t, estadotarea e, direcciones d, usuario u
                        WHERE t.estadoTarea_id = e.id AND t.usuarioCreado = concat(u.nombre, ' ', u.apellido)
                        AND t.direccion_codigo = d.codigo AND t.estadoTarea_id = 5";
            $result = mysqli_query($link, $sql);
            $listTareasEliminadas = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listTareasEliminadas[$i] = $row;
                $i++;
            }
            return $listTareasEliminadas;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function agregarTarea($descripcion, $nota_electronica, $nombreApellido, $celular, $direccion, $area, $usuarioCreado)
    {
        try {
            $link = parent::conexionBD();
            $sql = "INSERT INTO tareas(descripcion, nota_electronica, nombreApellidoAfectado, celular, estadoTarea_id, fechaProblema, direccion_codigo, codigoArea3, fechaCreada, usuarioCreado)
                    VALUES('$descripcion', '$nota_electronica', '$nombreApellido', '$celular', '1', NOW(), '$direccion', '$area', curdate(), '$usuarioCreado')";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function editarTarea($descripcion, $nota_electronica, $nombreApellido, $celular, $codDireccion, $selectArea, $nroArreglo)
    {
        try {
            $sql = "UPDATE tareas SET nombreApellidoAfectado = '$nombreApellido', celular = '$celular', descripcion = '$descripcion',
                    nota_electronica = '$nota_electronica', direccion_codigo = '$codDireccion', codigoArea3 = '$selectArea'
                    WHERE nroArreglo = '$nroArreglo'";
            $link = parent::conexionBD();
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function iniciarTarea($nroArreglo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE tareas SET estadoTarea_id = '2' WHERE nroArreglo = '$nroArreglo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function terminarTarea($solucion, $comprobante, $nroArreglo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE tareas SET solucion = '$solucion', comprobante = '$comprobante', fechaSolucion = now(), estadoTarea_id = 3
                    WHERE nroArreglo = '$nroArreglo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function cancelarTarea($motivoCancelacion, $nroArreglo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE tareas SET motivoCancelacion = '$motivoCancelacion', estadoTarea_id = 4 WHERE nroArreglo = '$nroArreglo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function eliminarTarea($motivoEliminacion, $nroArreglo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE tareas SET estadoTarea_id = 5, motivoEliminacion = '$motivoEliminacion', fechaEliminado = NOW() WHERE nroArreglo = '$nroArreglo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    // AREAS
    public function agregarNuevoArea($area, $descripcion)
    {
        try {
            $link = parent::conexionBD();
            $sql = "INSERT INTO areas(nombre, descripcion) values ('$area', '$descripcion')";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function editarArea($area, $descripcion, $id)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE areas SET nombre = '$area', descripcion = '$descripcion' WHERE codigo = $id";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    // DIRECCIONES
    public function listarDirecciones()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM direcciones d ORDER BY d.nombre asc";
            $result = mysqli_query($link, $sql);
            $listDirecciones = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listDirecciones[$i] = $row;
                $i++;
            }
            return $listDirecciones;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function listarDireccionesCodigo($codigo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT * FROM direcciones WHERE codigo = '$codigo'";
            $result = mysqli_query($link, $sql);
            $listDirecciones = [];
            $i = 0;
            while ($row = mysqli_fetch_row($result)) {
                $listDirecciones[$i] = $row;
                $i++;
            }
            return $listDirecciones;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function agregarDireccion($codigo, $nombre, $descripcion)
    {
        try {
            $link = parent::conexionBD();
            $sql = "INSERT INTO direcciones VALUES ('$codigo', '$nombre', '$descripcion')";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function editarDireccion($codigo, $direccion, $descripcion, $codigoDireccionAnterior)
    {
        try {
            $link = parent::conexionBD();
            $sql = "UPDATE direcciones SET codigo = '$codigo', nombre = '$direccion', descripcion = '$descripcion' WHERE codigo = '$codigoDireccionAnterior'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }

    public function eliminarDireccion($codigo)
    {
        try {
            $link = parent::conexionBD();
            $sql = "DELETE FROM direcciones WHERE codigo = '$codigo'";
            $result = mysqli_query($link, $sql);
            return $result;
        } catch (Exception $e) {
            die('Error ' . $e->getMessage());
        }
    }


    // QUERYS inicio.php
    public function contarTareasEstado($estado)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas WHERE estadoTarea_id IN (SELECT id FROM estadotarea e WHERE nombre = '$estado')";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $nroCompletas = $row[0];
            }
            return $nroCompletas;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function contarDirecciones()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT COUNT(*) FROM direcciones";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $cantDirecciones = $row[0];
            }
            return $cantDirecciones;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarTotalUsuarios()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM usuario u WHERE u.legajo != 0";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $totalUsuarios = $row[0];
            }
            return $totalUsuarios;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function contarUsuariosRol($rol)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM usuario u WHERE u.idRol2 = $rol";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $totalAdmin = $row[0];
            }
            return $totalAdmin;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function fechaActual()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT CURDATE()";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $fechaActual = $row[0];
            }
            return $fechaActual;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
