<?php
trait wLogin{
	public function Login($Metodo, $Datos){
		$Capsula['Metodo']  = $Metodo;
		$Capsula['Var'] = $Datos;
		return $this->Gate('Login', $Capsula);
	}
	public function ValidaLogin($Usuario, $Clave){
		$Datos['Usuario']           = $Usuario;
		$Datos['Clave']             = $Clave;
		return $this->Login('Autentificar',$Datos)[0];
	}
	public function Recovery($Usuario){
		$Datos['Usuario']           = $Usuario;
		return $this->Login('Recovery',$Datos)[0];
	}
	/*
	public function Registro($Usuario, $Clave, $Nombre1, $Apellido1){
		$Datos['Usuario']           = $Usuario;
		$Datos['Clave']             = $Clave;
		$Datos['Nombre1']           = $Nombre1;
		$Datos['Apellido1']         = $Apellido1;
		return $this->Login('Registro',$Datos);
	}*/
	public function ValidaMultiCuenta($Usuario, $Origen){
		$Datos['Usuario']           = $Usuario;
		$Datos['Origen']            = $Origen;
		return $this->Login('MultiCuenta',$Datos)[0];
	}
	public function LinkLogin(){
		$Datos = [];
		return $this->Login('LinkLogin',$Datos);
	}
	public function DatosRedSocial($Tipo, $Extra = []){
		$Datos['Tipo']            = $Tipo;
		$Datos['Extra']           = $Extra;
		return $this->Login('DatosRedSocial',$Datos);
	}
	public function DatosUsuario($IdUsuario, $Rewrite = 0){
		$Cache = SesionCache('usuario', $IdUsuario);
		if($Cache != null && $Rewrite == 0) {
			$Resultado = $Cache;
		}
		else{
			$Datos['IdUsuario']         = $IdUsuario;
			$Resultado = $this->Login('DatosUsuario',$Datos);
			SetSesionCache('usuario', $IdUsuario, $Resultado);
		}
		return $Resultado;
	}
	public function BuscarUsuarios($Busqueda){
		$Datos['Busqueda']              = $Busqueda;
		return $this->Login('BuscarUsuarios',$Datos);
	}
	public function ModUsuario(){

	}
	public function CrearUsuario(){

	}
	public function UltimosUsuarios(){
		$Datos = [];
		return $this->Login('UltimosUsuarios',$Datos);
	}
	public function ValidarToken($Token){
		$Datos['Token']            = $Token;
		return $this->Login('ValidarToken',$Datos)[0];
	}
	public function CambiarClave($IdUsuario, $Usuario, $Pass, $NewPass){
		$Datos['IdUsuario']         = $IdUsuario;
		$Datos['Usuario']           = $Usuario;
		$Datos['Pass']              = $Pass;
		$Datos['NewPass']           = $NewPass;
		return $this->Login('CambiarClave',$Datos)[0];
	}
}