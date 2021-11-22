<?php
/**
 * Class Usuario
 * ALVAX 02.04.2019
 */

class Usuario extends Wiss {

    /**
     * Get general, para los filtros
     * @param $Busqueda
     * @param $Filtros
     * @return mixed
     */
    public function Get($Busqueda, $Filtros) {
        $Datos['Busqueda'] = $Busqueda;
        $Datos['Filtros'] = $Filtros;
        $Datos['IdUsuario'] = Sesion('idusuario');
        return Wiss::Query('spSel_Usuario', $Datos);
    }

    /**
     * Get general version 2: paginado
     * @param $Busqueda
     * @param $IdUniversidad
     * @param $IdSede
     * @param $IdCarrera
     * @param $FechaVacunaDesde
     * @param $FechaVacunaHasta
     * @param $NumeroPagina
     * @param $TamanoPagina
     * @param $OrderBy
     * @return mixed
     */
    public function GetPaginado($Busqueda, $IdUniversidad, $IdSede, $IdCarrera, $FechaVacunaDesde, $FechaVacunaHasta, $NumeroPagina, $TamanoPagina, $OrderBy) {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['Busqueda'] = $Busqueda;
        $Datos['IdUniversidad'] = $IdUniversidad;
        $Datos['IdSede'] = $IdSede;
        $Datos['IdCarrera'] = $IdCarrera;
        $Datos['FechaVacunaDesde'] = $FechaVacunaDesde;
        $Datos['FechaVacunaHasta'] = $FechaVacunaHasta;
        $Datos['NumeroPagina'] = $NumeroPagina;
        $Datos['TamanoPagina'] = $TamanoPagina;
        $Datos['OrderBy'] = $OrderBy;
        return Wiss::Query('spSel_Usuario_Paginado', $Datos);
    }

    /**
     * Obtiene el numero total de resultados para calcular la paginacion
     * @param $Busqueda
     * @param $IdUniversidad
     * @param $IdSede
     * @param $IdCarrera
     * @param $FechaVacunaDesde
     * @param $FechaVacunaHasta
     * @return mixed
     */
    public function GetPaginadoTotal($Busqueda, $IdUniversidad, $IdSede, $IdCarrera, $FechaVacunaDesde, $FechaVacunaHasta) {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['Busqueda'] = $Busqueda;
        $Datos['IdUniversidad'] = $IdUniversidad;
        $Datos['IdSede'] = $IdSede;
        $Datos['IdCarrera'] = $IdCarrera;
        $Datos['FechaVacunaDesde'] = $FechaVacunaDesde;
        $Datos['FechaVacunaHasta'] = $FechaVacunaHasta;
        return Wiss::Query('spSel_Usuario_Total', $Datos)[0];
    }

    public function GetDocentes() {
        $Datos = array();
        return Wiss::Query('spSel_Docente', $Datos);
    }

    public function GetById($IdUsuario) {
        $Datos['IdUsuario'] = $IdUsuario;
        return Wiss::Query('spSel_Usuario_Certificado', $Datos);
    }

    public function GetByRut($Rut) {
        $Datos['Rut'] = $Rut;
        return Wiss::Query('spSel_Usuario_Rut', $Datos);
    }

    /**
     * Método para obtener informacion del usuario para el certificado tabla
     * @param $IdUsuario
     * @return mixed
     */
    public function GetInfoCertificadoTabla($IdUsuario) {
        $Datos['IdUsuario'] = $IdUsuario;
        return Wiss::Query('spSel_Usuario_CertificadoTabla', $Datos);
    }

    /**
     * Ingresar certificado emitido a la base de datos
     * @param $Codigo
     * @param $IdUsuario
     * @param $Url
     * @return mixed
     */
    public function IngresarCertificado($Codigo, $IdUsuario, $Url) {
        $Datos['Codigo'] = utf8_encode($Codigo);
        $Datos['IdUsuario'] = $IdUsuario;
        $Datos['Url'] = utf8_encode($Url);
        return Wiss::Query('spIns_Certificado', $Datos);
    }

    public function GetVaccines($IdUsuario) {
        $Datos['IdUsuario'] = $IdUsuario;
        return Wiss::Query('spSel_Usuario_Vacunas', $Datos);
    }

    public function CreateModify($IdUsuario, $Nombres, $Apellidos, $Username, $Rut, $FechaNacimiento, $IdCarrera, $IdSede, $Password, $IdTipoUsuario) {
        $Datos['IdUsuario'] = $IdUsuario;
        $Datos['Nombres'] = utf8_encode($Nombres);
        $Datos['Apellidos'] = utf8_encode($Apellidos);
        $Datos['Username'] = utf8_encode($Username);
        $Datos['Rut'] = $Rut;
        $Datos['FechaNacimiento'] = $FechaNacimiento;
        $Datos['IdCarrera'] = $IdCarrera;
        $Datos['IdSede'] = $IdSede;
        $Datos['Password'] = $Password;
        $Datos['IdTipoUsuario'] = $IdTipoUsuario;
        return Wiss::Query('spIns_Usuario', $Datos);
    }

    public function Remove($IdUsuario) {
        $Datos['IdUsuario'] = $IdUsuario;
        return Wiss::Query('spDel_Usuario', $Datos);
    }
}