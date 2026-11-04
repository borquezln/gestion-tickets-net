<?php
class ControladorAdmin
{

    public function verEstadisticasContr($time, $fechaInicio, $fechaFin)
    {
        echo ('<title>Estadísticas</title>');
        require('../modelo/m_estadisticas.php');
        $est = new ConsultasEstadisticas();
        require "../modelo/m_consultas.php";
        $co = new Consultas;

        $areas = $co->listarAreas();

        if ($time == 'now') {
            $periodo = "de hoy";
            $totalTareas = $est->contarTareasHoy();

            $totalPendientes = $est->contarEstadoHoy('Pendiente');
            $totalEnProgreso = $est->contarEstadoHoy('En Progreso');
            $totalCompletas = $est->contarEstadoHoy('Completo');
            $totalCanceladas = $est->contarEstadoHoy('Cancelado');
            $totalEliminadas = $est->contarEstadoHoy('Eliminado');

            $totalAreas = [];
            foreach ($areas as $area) {
                $totalAreas[$area[1]] = $est->contarAreaHoy($area[0]);
            }
        } else if ($time == 'month') {
            $periodo = "del " . $fechaInicio . " al " . $fechaFin;
            $totalTareas = $est->contarTareasxFechas($fechaInicio, $fechaFin);

            $totalPendientes = $est->contarEstadoxFechas('Pendiente', $fechaInicio, $fechaFin);
            $totalEnProgreso = $est->contarEstadoxFechas('En Progreso', $fechaInicio, $fechaFin);
            $totalCompletas = $est->contarEstadoxFechas('Completo', $fechaInicio, $fechaFin);
            $totalCanceladas = $est->contarEstadoxFechas('Cancelado', $fechaInicio, $fechaFin);
            $totalEliminadas = $est->contarEstadoxFechas('Eliminado', $fechaInicio, $fechaFin);

            $totalAreas = [];
            foreach ($areas as $area) {
                $totalAreas[$area[1]] = $est->contarAreaxFechas($area[0], $fechaInicio, $fechaFin);
            }
        } else if ($time == 'year') {
            $periodo = "del año";
            $totalTareas = $est->contarTareasxAnio();

            $totalPendientes = $est->contarEstadoxAnio('Pendiente');
            $totalEnProgreso = $est->contarEstadoxAnio('En Progreso');
            $totalCompletas = $est->contarEstadoxAnio('Completo');
            $totalCanceladas = $est->contarEstadoxAnio('Cancelado');
            $totalEliminadas = $est->contarEstadoxAnio('Eliminado');

            $totalAreas = [];
            foreach ($areas as $area) {
                $totalAreas[$area[1]] = $est->contarAreaxAnio($area[0]);
            }
        } else if ($time == 'all') {
            $periodo = "totales";
            $totalTareas = $est->contarTareasTotal();

            $totalPendientes = $est->contarEstadoTotal('Pendiente');
            $totalEnProgreso = $est->contarEstadoTotal('En Progreso');
            $totalCompletas = $est->contarEstadoTotal('Completo');
            $totalCanceladas = $est->contarEstadoTotal('Cancelado');
            $totalEliminadas = $est->contarEstadoTotal('Eliminado');

            $totalAreas = [];
            foreach ($areas as $area) {
                $totalAreas[$area[1]] = $est->contarAreaTotal($area[0]);
            }
        }

        require('libreriaEstilos.php');
        require('headerNav.php');
        require('estadisticas.php');
    }

    public function listarTareasAdminContr($lista)
    {
        echo ('<title>Listado de Tareas - Gestión Requerimientos</title>');
        require('../modelo/m_consultas.php');
        $co = new Consultas();
        if ($lista == 'actual') {
            $estado = "Pendientes y en Progreso";
            $listTareasAgentes = $co->listarTareasAdmin($lista);
        } else if ($lista == 'completas') {
            $estado = "Completadas";
            $listTareasAgentes = $co->listarTareasAdmin($lista);
            $listTareasCompletasActual = $co->listarTareasCompletasActual();
        } else if ($lista == 'canceladas') {
            $estado = "Canceladas";
            $listTareasAgentes = $co->listarTareasAdmin($lista);
        } else {
            header("location:javascript:history.go(-1)");
        }

        $listDirecciones = $co->listarDirecciones();
        $listAreas = $co->listarAreas();
        require('libreriaEstilos.php');
        require('headerNav.php');
        require('listarTareasAdmin.php');
    }

    public function listarTareasEliminadasContr()
    {
        echo ('<title>Listado de Tareas Eliminadas - Gestión de tareas</title>');
        require('../modelo/m_consultas.php');
        $co = new Consultas();
        $listTareasEliminadas = $co->listarTareasEliminadas();
        require('libreriaEstilos.php');
        require('headerNav.php');
        require('listaTareasEliminadas.php');
    }

    public function listarAreasContr()
    {
        echo ('<title>Listado de Agentes - Gestión de tareas</title>');
        require('../modelo/m_consultas.php');
        $co = new Consultas();
        $listAreas = $co->listarAreas();
        require('libreriaEstilos.php');
        require('headerNav.php');
        require('listaAreas.php');
    }

    public function listarDireccionesContr()
    {
        echo ('<title>Listado de Direcciones - Gestión de tareas</title>');
        require('../modelo/m_consultas.php');
        $co = new Consultas();
        $listDirecciones = $co->listarDirecciones();
        require('libreriaEstilos.php');
        require('headerNav.php');
        require('listaDirecciones.php');
    }

    public function listarUsuariosBajaContr()
    {
        echo ('<title>Lista de Usuarios de baja - Gestión de tareas</title>');
        require('../modelo/m_consultas.php');
        $co = new Consultas();
        $listBajas = $co->listarBajasUsuarios();
        $listRoles = $co->listarRoles();
        require('libreriaEstilos.php');
        require('headerNav.php');
        require('listarUsuariosBaja.php');
    }

    public function listarUsuariosCargadosContr()
    {
        echo ('<title>Lista de Usuarios cargados - Gestión de tareas</title>');
        require('../modelo/m_consultas.php');
        $co = new Consultas();
        $listUsuarios = $co->listarUsuariosCargados();
        require('libreriaEstilos.php');
        require('headerNav.php');
        require('usuariosCargados.php');
    }

    public function pageBlanquearPassContr()
    {
        echo ('<title>Blanquear Contraseña - Gestión de tareas</title>');
        require('../modelo/m_consultas.php');
        $co = new Consultas();
        $listUsuarios = $co->listarUsuarios();
        require('libreriaEstilos.php');
        require('headerNav.php');
        require('blanqueoPass.php');
    }
}
