<?php
$Commands = [
	'wLogin',
	'wQuery',
	'wChat',
	'wMail',
	'wScripts',
	'wStyles',
	'wKirito',
	'wZeus'
];
/**********************************************************************************************************************/
foreach($Commands as $co){
	$Ruta = constant('Root_Fisica').constant('Root_Commands').$co.'.php';
	if(file_exists($Ruta)){
		include_once($Ruta);
	}
}
/**********************************************************************************************************************/
class Wiss{
/**********************************************************************************************************************/
	use wLogin,
		wQuery,
		wChat,
		wMail,
		wScripts,
		wStyles,
		wKirito,
		wZeus;
/**********************************************************************************************************************/
	private $SteinsGate;
	private $Manifest;
	public function __construct(){
		$Manifest = [];
		require(constant('Root_Fisica').constant('Root_Valk').'preloader.php');
		$this->Manifest = $Manifest;
		$this->SteinsGate = $this->Manifest['SteinsGate'];
	}
	private function Call($Capsule){
		$Capsule['Manifest'] = $this->Manifest;
		$Capsule['Manifest']['IP'] = IP();
		$Capsule['Manifest']['IdUsuario'] = Sesion('idusuario');
		$Capsule['Manifest']['Usuario'] = Sesion('usuario');

		$Valk = new Valk($Capsule);
		$Output = $Valk->Execute();
		return $Output;
	}
	private function Gate($Modo, $Data = ''){
		$Capsule['Modo'] = $Modo;
		$Capsule['Data'] = $Data;
		return $this->Call($Capsule);
	}
	public function Test(){
		return $this->Gate('Test');
	}
}



