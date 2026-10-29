<?php

class Carrera extends Wiss {

    public function  Get($IdUniversidad) {
        $Datos['IdUniversidad'] = $IdUniversidad;
        return Wiss::Query('spSel_Carrera', $Datos);
    }

    public function GetAll() {
        $Datos = array();
        return Wiss::Query('spSel_Carrera', $Datos);
    }

    public function GetBySede($IdSede) {
        $Datos['IdSede'] = $IdSede;
        $Datos['IdUsuario'] = Sesion('idusuario');
        return Wiss::Query('spSel_CarreraBySede', $Datos);
    }

    public function CreateModify($IdCarrera, $Nombre) {
        $Datos['IdCarrera'] = $IdCarrera;
        $Datos['Nombre'] = utf8_encode($Nombre);
        return Wiss::Query('spIns_Carrera', $Datos);
    }

    public function Remove($IdCarrera) {
        $Datos['IdCarrera'] = $IdCarrera;
        return Wiss::Query('spDel_Carrera', $Datos);
    }
}