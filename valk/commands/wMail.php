<?php
trait wMail{
	public function Mail($Metodo, $Datos){
		$Capsula['Metodo']         = $Metodo;
		$Capsula['Var']            = $Datos;
		return $this->Gate('Mail', $Capsula);
	}
	public function EnviarMail($Emisor, $NombreEmisor, $Destinatario, $Asunto, $Mensaje, $Copia = '', $CopiaOculta = '' ){
		$Datos['Emisor']                = $Emisor;
		$Datos['NombreEmisor']          = $NombreEmisor;
		$Datos['Destinatario']          = $Destinatario;
		$Datos['Copia']                 = $Copia;
		$Datos['CopiaOculta']           = $CopiaOculta;
		$Datos['Asunto']                = $Asunto;
		$Datos['Mensaje']               = $Mensaje;
		return $this->Mail('EnviarMail',$Datos);
	}
}