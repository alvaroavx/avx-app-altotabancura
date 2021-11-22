<?php

class Vacuna extends Wiss {

    public function  Get($IdUniversidad) {
        $Datos['IdUniversidad'] = $IdUniversidad;
        return Wiss::Query('spSel_Carrera', $Datos);
    }

    public function GetAll() {
        $Datos = array();
        return Wiss::Query('spSel_Vacuna', $Datos);
    }

    public function CreateModify($IdVacuna, $Vacuna, $Lote) {
        $Datos['IdVacuna'] = $IdVacuna;
        $Datos['Vacuna'] = utf8_encode($Vacuna);
        $Datos['Lote'] = utf8_encode($Lote);
        return Wiss::Query('spIns_Vacuna', $Datos);
    }

    public function Remove($IdVacuna) {
        $Datos['IdVacuna'] = $IdVacuna;
        return Wiss::Query('spDel_Vacuna', $Datos);
    }

    /**
     * Crea, actualiza y asocia vacunas a los usuarios
     * @param $IdUsuarioVacuna
     * @param $IdVacuna
     * @param $IdUsuario
     * @param $Numero
     * @param $Fecha
     * @return mixed
     */
    public function UserVaccine($IdUsuarioVacuna, $IdVacuna, $IdUsuario, $Numero, $Fecha) {
        $Datos['IdUsuarioVacuna'] = $IdUsuarioVacuna;
        $Datos['IdVacuna'] = $IdVacuna;
        $Datos['IdUsuario'] = $IdUsuario;
        $Datos['Numero'] = $Numero;
        $Datos['Fecha'] = $Fecha;
        return Wiss::Query('spIns_UsuarioVacuna', $Datos);
    }

    public function DetachVaccine($IdUsuarioVacuna) {
        $Datos['IdUsuarioVacuna'] = $IdUsuarioVacuna;
        return Wiss::Query('spDel_UsuarioVacuna', $Datos);
    }

    /**
     * Obtiene la ultima vacuna ingresada al usuario
     * @param $IdUsuario
     * @return mixed
     */
    public function GetLast($IdUsuario) {
        $Datos['IdUsuario'] = $IdUsuario;
        return Wiss::Query('spSel_VacunaUltima', $Datos);
    }

}