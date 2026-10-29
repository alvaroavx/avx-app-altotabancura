<?php
class Zeus{
	private $Metodo;
	private $Manifest;
	private $Var;
	public function __construct($Manifest, $Gate, $Data){
		$this->Manifest      = $Manifest;
		$this->Metodo       = $Data['Metodo'];
		$this->Var          = $Data['Var'];
	}
	public function Execute(){
		$Return = [];
		$Metodo = $this->Metodo;
		if (method_exists($this, $Metodo)){
			LoadModulo($this->Manifest, ['Steins']);
			$Return = $this->$Metodo();
		}
		return $Return;
	}
	private function PermisosUsuario(){
		$Steins = new Steins($this->Manifest);
		$Datos['IdUsuario'] = $this->Var['IdUsuario'];
		$Datos['Gate'] = $this->Manifest['Gate'];

		$Procedure = 'spRec_Permiso_Permiso';
		$return = $Steins->CoreQuery($Procedure,$Datos);
		return $return;
	}
}