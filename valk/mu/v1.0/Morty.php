<?php
class Morty{
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
			LoadModulo($this->Manifest, ['Steins']);
			$Return = $this->$Metodo();
		}
		return $Return;
	}
	private function GuardarLog($TipoLog, $DatosLog){
		$Steins = new Steins($this->Manifest);
		$Datos['TipoLog']   = $TipoLog;
		$Datos['IP']        = $this->Manifest['IP'];
		$Datos['Gate']      = $this->Manifest['Gate'];
		$Datos['IdUsuario'] = $this->Manifest['IdUsuario'];
		$Datos['Usuario']   = $this->Manifest['Usuario'];
		$Datos['Datos']     = $DatosLog;

		$Procedure = 'spIns_Log_Log';
		return $Steins->CoreQuery($Procedure,$Datos);
	}
	private function BuscarLog(){
		$Steins = new Steins($this->Manifest);
		$Datos['Gate']      = $this->Var['Gate'];
		$Datos['IdTipoLog'] = $this->Var['IdTipoLog'];
		$Datos['Busqueda']  = $this->Var['Busqueda'];
		$Datos['FechaDesde']= $this->Var['FechaDesde'];
		$Datos['FechaHasta']= $this->Var['FechaHasta'];

		$Procedure = 'spRec_Log_Log';
		return $Steins->CoreQuery($Procedure,$Datos);
	}
	private function DatosLog(){
		$Steins = new Steins($this->Manifest);
		$Datos['IdLog'] = $this->Var['IdLog'];

		$Procedure = 'spRec_Log_Meta';
		$DatosRaw = $Steins->CoreQuery($Procedure,$Datos);

		$RC = [];
		$RC['IdLog'] =      $DatosRaw[0]['IdLog'];
		$RC['Gate'] =       $DatosRaw[0]['Gate'];
		$RC['IdTipoLog'] =  $DatosRaw[0]['IdTipoLog'];
		$RC['TipoLog'] =    $DatosRaw[0]['TipoLog'];
		$RC['Fecha'] =      $DatosRaw[0]['Fecha'];
		$RC['IdUsuario'] =  $DatosRaw[0]['IdUsuario'];
		$RC['Usuario'] =    $DatosRaw[0]['Usuario'];
		$RC['Token'] =      $DatosRaw[0]['Token'];

		foreach($DatosRaw as $dr){
			$RC[$dr['TipoMeta']] = $dr['Meta'];
		}
		return $RC;

	}
	private function LoginLog(){
		$DatosLog[] = [
			'tipo' => 'Usuario',
			'valor' => $this->Var['Usuario']
		];
		$DatosLog[] = [
			'tipo' => 'IdEstadoUsuario',
			'valor' => $this->Var['IdEstadoUsuario']];
		$DatosLog[] = [
			'tipo' => 'Fails',
			'valor' => $this->Var['Fails']
		];
		$DatosLog[] = [
			'tipo' => 'IdUsuario',
			'valor' => $this->Var['IdUsuario']
		];
		$DatosLog[] = [
			'tipo' => 'Clave',
			'valor' => $this->Var['Clave']
		];
		return $this->GuardarLog('Login', ArrayToXml($DatosLog));
	}
}