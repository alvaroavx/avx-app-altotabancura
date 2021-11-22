<?php
trait wQuery{
	public function Query($Procedure, $Datos){
		$Capsula['Proc']  = $Procedure;
		$Capsula['Var'] = $Datos;
		return $this->Gate('Query', $Capsula);
	}
}