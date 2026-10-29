<?php
trait wKirito{
	public function Kirito($Metodo, $Datos){
		$Capsula['Metodo'] = $Metodo;
		$Capsula['Var'] = $Datos;
		return $this->Gate('Kirito', $Capsula);
	}
	public function CleanCache(){
		$Capsula = [];
		return $this->Kirito('CleanCache', $Capsula);
	}

}