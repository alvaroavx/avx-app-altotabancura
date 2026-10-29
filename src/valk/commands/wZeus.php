<?php
trait wZeus{
	public function Zeus($Metodo, $Datos){
		$Capsula['Metodo']         = $Metodo;
		$Capsula['Var']            = $Datos;
		return $this->Gate('Zeus', $Capsula);
	}
	public function PermisosUsuario($IdUsuario, $Rewrite = 0){
		$Cache = SesionCache('permisos', $IdUsuario);
		if($Cache != null && $Rewrite == 0) {
			$Resultado = $Cache;
		}
		else{
			$Datos['IdUsuario'] = $IdUsuario;
			$Permisos = $this->Zeus('PermisosUsuario',$Datos);
			$Resultado = [];
			foreach($Permisos as $per){
				$Resultado[$per['IdPermiso']] = ['Estado' => $per['Estado'], 'Nombre' => $per['Permiso']];
			}
			SetSesionCache('permisos', $IdUsuario, $Resultado);
		}
		return $Resultado;
	}
}