<?php

class Sede extends Wiss {

    public function Get($IdUniversidad) {
        $Datos['IdUniversidad'] = $IdUniversidad;
        return Wiss::Query('spSel_Sede', $Datos);
    }

    public function GetAll()
    {
        $Datos = array();
        return Wiss::Query('spSel_Sede', $Datos);
    }

    public function GetAllByUniversidad() {
        $Datos = array();
        return Wiss::Query('spSel_SedeByUniversidad', $Datos);
    }

    public function GetByCarrera($IdCarrera) {
        $Datos['IdCarrera'] = $IdCarrera;
        $Datos['IdUsuario'] = Sesion('idusuario');
        return Wiss::Query('spSel_SedeByCarrera', $Datos);
    }

    public function CreateModify($IdSede, $IdUniversidad, $Nombre) {
        $Datos['IdSede'] = $IdSede;
        $Datos['IdUniversidad'] = $IdUniversidad;
        $Datos['Nombre'] = utf8_encode($Nombre);
        return Wiss::Query('spIns_Sede', $Datos);
    }

    public function Remove($IdSede) {
        $Datos['IdSede'] = $IdSede;
        return Wiss::Query('spDel_Sede', $Datos);
    }

}