<?php

class Universidad extends Wiss {

    public function Get($IdUniversidad) {
        $Datos['IdUniversidad'] = $IdUniversidad;
        return Wiss::Query('spSel_Universidad', $Datos);
    }

    public function GetAll() {
        $Datos = array();
        return Wiss::Query('spSel_Universidad', $Datos);
    }

    public function GetAllWithSedes() {
        $Datos = array();
        return Wiss::Query('spSel_UniversidadSedes', $Datos);
    }

    public function CreateModify($IdUniversidad, $Nombre) {
        $Datos['IdUniversidad'] = $IdUniversidad;
        $Datos['Nombre'] = Latin1ToUtf8($Nombre);
        return Wiss::Query('spIns_Universidad', $Datos);
    }

    public function Remove($IdUniversidad) {
        $Datos['IdUniversidad'] = $IdUniversidad;
        return Wiss::Query('spDel_Universidad', $Datos);
    }
}
