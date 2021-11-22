<?php
trait wChat{
	public function Chat($Metodo, $Datos){
		$Capsula['Metodo']  = $Metodo;
		$Capsula['Var'] = $Datos;
		return $this->Gate('Chat', $Capsula);
	}
	public function Contactos(){
		$Datos['IdUsuario']         = Sesion('idusuario');
		return $this->Chat('Contactos',$Datos);
	}
	public function MensajesChat($UsuarioDestino){
		$Datos['IdUsuarioOrigen']     = Sesion('idusuario');
		$Datos['IdUsuarioDestino']    = $UsuarioDestino;
		return $this->Chat('MensajesChat',$Datos);
	}
	public function AgregarChat($UsuarioDestino, $Mensaje){
		$Datos['IdUsuarioOrigen']     = Sesion('idusuario');
		$Datos['IdUsuarioDestino']    = $UsuarioDestino;
		$Datos['Mensaje']             = $Mensaje;
		return $this->Chat('AddMensaje',$Datos);
	}
}