<?php
class Odin{
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
			LoadModulo($this->Manifest, ['Steins','RedSocial']);
			$Return = $this->$Metodo();
		}
		return $Return;
	}
}