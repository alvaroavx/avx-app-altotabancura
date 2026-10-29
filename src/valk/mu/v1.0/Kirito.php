<?php
class Kirito{
	private $Manifest;
	private $Metodo;
	private $Var;
	public function __construct($Manifest, $Gate, $Data){
		$this->Manifest     = $Manifest;
		$this->Metodo       = $Data['Metodo'];
		$this->Var          = $Data['Var'];
	}
	public function Execute(){
		$Return = [];
		$Metodo = $this->Metodo;
		if (method_exists($this, $Metodo)){
			$Return = $this->$Metodo();
		}
		return $Return;
	}
	private function CleanCache(){
		$Directorio = constant('Root_Fisica').constant('Root_Cache');
		$Archivos = scandir($Directorio);
		foreach($Archivos as $ar){
			if (is_file($Directorio.$ar)){
				unlink($Directorio.$ar);
			}
		}
	}
}