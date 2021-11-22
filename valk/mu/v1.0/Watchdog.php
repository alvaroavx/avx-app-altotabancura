<?php
class Watchdog{
	private $Manifest;
	private $Metodo;
	private $Var;
	public function __construct($Manifest, $Gate, $Data){
		$this->Manifest     = $Manifest;
		$this->Metodo       = $Data['Metodo'];
		$this->Var          = $Data['Var'];
	}
    public function Execute(){
        return 1;
    }
}