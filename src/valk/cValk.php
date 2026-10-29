<?php
/**
    VALK
    Version 1.17: Reordenamiento sistema archivos
    Version 1.18: Actualizazion conexion base de datos
    Version 1.19: Incluye Debug console y Log
    Version 1.20: Full cambio a Objetos
    Version 1.21: Sistema Debug
    Version 1.22: Capa debug y back-office
    Version 1.23: Ajustes archivos y limpieza js y css
    Version 1.24: Cambio a tag valk
    Version 1.25: Limpieza Core, aplicacion lang, encapsulado RawData y Automatizacion carga Construct
    --
    Version 2.1: Separacion Steins - Gate - Valk
    Njong Alvax
*/
class Valk{
    private $Gate;
    private $Modo;
    private $Data;
    private $Manifest;
    public function __construct($data){
        $this->Manifest = $data['Manifest'];
        $this->Modo     = $data['Modo'];
        $this->Data     = $data['Data'];
    }
    public function Execute(){
    	return $this->LoadModo();
    }
    private function LoadGate(){
	    LoadModulo($this->Manifest, ['Steins']);
	    $Steins = new Steins($this->Manifest);
		$this->Gate = $Steins->Gate(
            $this->Manifest['Gate'],
            $this->Manifest['Entorno']['DBServer'],
            $this->Manifest['Entorno']['MailServer']
        );
        return $this->Gate;
    }
    private function LoadModo(){
	    $this->LoadGate();
        LoadModulo($this->Manifest, [$this->Modo]);
        if(class_exists($this->Modo)){
        	$Modulo = new $this->Modo($this->Manifest, $this->Gate, $this->Data);
            return $Modulo->Execute();
        }
        else{
            return ['ERROR' => 'Clase no existe'];
        }
    }
}
