<?php
require_once('../modelo/m_conexion.php');

class ConsultasEstadisticas extends Conexion
{
    // TAREAS HOY
    public function contarTareasHoy()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT COUNT(*) FROM tareas t WHERE t.fechaCreada = curdate()";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $cantTareas = $row[0];
            }
            return $cantTareas;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarEstadoHoy($estado)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, estadotarea e
                    WHERE t.estadoTarea_id = e.id AND e.nombre = '$estado' AND t.fechaProblema = curdate()";
            $result = mysqli_query($link, $sql);
            $totalTareasEstado = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalTareasEstado = $row[0];
            }
            return $totalTareasEstado;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarAreaHoy($area)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, areas a
                    WHERE t.codigoArea3 = a.codigo AND a.codigo = $area AND t.fechaCreada = curdate()";
            $result = mysqli_query($link, $sql);
            $totalArea = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalArea = $row[0];
            }
            return $totalArea;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    // TAREAS POR FECHAS
    public function contarTareasxFechas($inicio, $fin)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT COUNT(*) FROM tareas t
                    WHERE t.fechaCreada BETWEEN '$inicio' AND '$fin'";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $cantTareas = $row[0];
            }
            return $cantTareas;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarEstadoxFechas($estado, $inicio, $fin)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, estadotarea e 
                    WHERE t.estadoTarea_id = e.id AND e.nombre = '$estado'
                    AND t.fechaCreada BETWEEN '$inicio' AND '$fin'";
            $result = mysqli_query($link, $sql);
            $totalTareasEstado = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalTareasEstado = $row[0];
            }
            return $totalTareasEstado;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarAreaxFechas($area, $inicio, $fin)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, areas a 
                    WHERE t.codigoArea3 = a.codigo AND a.codigo = $area
                    AND t.fechaCreada BETWEEN '$inicio' AND '$fin'";
            $result = mysqli_query($link, $sql);
            $totalArea = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalArea = $row[0];
            }
            return $totalArea;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    // TAREAS AÑO ACTUAL
    public function contarTareasxAnio()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT COUNT(*) FROM tareas t 
                    WHERE year(t.fechaCreada) = (SELECT year(curdate()))";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $cantTareas = $row[0];
            }
            return $cantTareas;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarEstadoxAnio($estado)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, estadotarea e 
                    WHERE t.estadoTarea_id = e.id AND e.nombre = '$estado'
                    AND year(t.fechaCreada) = (SELECT year(curdate()))";
            $result = mysqli_query($link, $sql);
            $totalTareasEstado = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalTareasEstado = $row[0];
            }
            return $totalTareasEstado;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarAreaxAnio($area)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, areas a 
                    WHERE t.codigoArea3 = a.codigo AND a.codigo = $area
                    AND year(t.fechaCreada) = (SELECT year(curdate()))";
            $result = mysqli_query($link, $sql);
            $totalArea = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalArea = $row[0];
            }
            return $totalArea;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    // TOTALES
    public function contarTareasTotal()
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT COUNT(*) FROM tareas";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $cantTareas = $row[0];
            }
            return $cantTareas;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function contarEstadoTotal($estado)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, estadotarea e
                    WHERE t.estadoTarea_id = e.id AND e.nombre = '$estado'";
            $result = mysqli_query($link, $sql);
            $totalTareasEstado = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalTareasEstado = $row[0];
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $totalTareasEstado;
    }

    public function contarAreaTotal($area)
    {
        try {
            $link = parent::conexionBD();
            $sql = "SELECT count(*) FROM tareas t, areas a
                    WHERE t.codigoArea3 = a.codigo AND a.codigo = $area";
            $result = mysqli_query($link, $sql);
            $totalArea = [];
            while ($row = mysqli_fetch_row($result)) {
                $totalArea = $row[0];
            }
            return $totalArea;
        } catch (Exception $e) {
            $e->getMessage();
        }
    }
}
