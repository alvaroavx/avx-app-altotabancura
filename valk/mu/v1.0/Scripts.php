<?php
class Scripts{
	private $Manifest;
	private $MinorVersion = '1.0';
	public function __construct($Manifest, $Gate, $Data){
		$this->Manifest     = $Manifest;
	}
	public function Execute(){

		/**Base Nombre, Sync o Async, Minimizar, Unir a archivo Valk**/
		$PreScripts[] = ['Nombre' => 'jquery.min',           'Async' => 0, 'Min' => 0, 'Valk' => 0];
		$PreScripts[] = ['Nombre' => 'jquery.validate.min',  'Async' => 1, 'Min' => 0, 'Valk' => 0];
		$PreScripts[] = ['Nombre' => 'jquery.base64.min',    'Async' => 1, 'Min' => 0, 'Valk' => 0];
		$PreScripts[] = ['Nombre' => 'jquery.ui.widget',     'Async' => 1, 'Min' => 1, 'Valk' => 0];
		//$PreScripts[] = ['Nombre' => 'jquery.fileupload',    'Async' => 1, 'Min' => 1, 'Valk' => 0];
		$PreScripts[] = ['Nombre' => 'jquery.mousewheel.min','Async' => 1, 'Min' => 0, 'Valk' => 0];
		$PreScripts[] = ['Nombre' => 'jquery.dataTables.min','Async' => 1, 'Min' => 0, 'Valk' => 0];
		$PreScripts[] = ['Nombre' => 'valk.login',           'Async' => 1, 'Min' => 1, 'Valk' => 1];
		$PreScripts[] = ['Nombre' => 'valk.core',            'Async' => 1, 'Min' => 1, 'Valk' => 1];
		$PreScripts[] = ['Nombre' => 'valk.watchdog',        'Async' => 1, 'Min' => 1, 'Valk' => 1];
		$Scripts = [];
		if(isset($this->Manifest['Modulo']['Jodit']) && $this->Manifest['Modulo']['Jodit']){
			$Scripts[] = '<script async src="'.constant('Root_Base').constant('Root_Vendor').'Jodit/jodit.min.js'.'"></script>';
			$PreScripts[] = ['Nombre' => 'valk.jodit.config', 'Async' => 1, 'Min' => 1, 'Valk' => 1];
		}
		/**************************************************************************/
		$RutaValkJs = constant('Root_Cache').'jv'.$this->Manifest['Version']['Js'].'.js';
		if($this->Manifest['Entorno']['Developer']) {
			foreach($PreScripts as $ps){
				$Scripts[] = '<script '.(($ps['Async'])?'async':'').' src="'.constant('Root_Base').constant('Root_JsValk').'v'.$this->Manifest['Version']['Js'].'/'.$ps['Nombre'].'.js"></script>';
			}
		}
		else{
			$ValkJsCreado = file_exists($RutaValkJs);
			foreach ($PreScripts as $ps) {
				$RutaCache = (($ps['Valk'])? $RutaValkJs
					: constant('Root_Cache').$ps['Nombre'].(($ps['Min'])? '.min': '').'.'.$this->Manifest['Version']['Js'].'.js');
				$RutaReal  = constant('Root_JsValk').'v'.$this->Manifest['Version']['Js'].'/'.$ps['Nombre'].'.js';
				/** no (existe y no es de valk) y ((es de valk y no esta creado) o no existe)**/
				if ((!(file_exists(constant('Root_Fisica').$RutaCache) && !$ps['Valk']))
					&& (($ps['Valk'] && !$ValkJsCreado)
						|| !file_exists(constant('Root_Fisica').$RutaCache))) {

					$fo = fopen(constant('Root_Fisica').$RutaCache, 'a');
					if ($fo) {
						if (file_exists(constant('Root_Fisica').$RutaReal)) {
							$fx = fopen(constant('Root_Fisica').$RutaReal, "rb");
							if ($fx && filesize($RutaReal) > 0) {
								$contenido = (($ps['Min']) ? LimpiaMinimizador(fread($fx, filesize($RutaReal))) : fread($fx, filesize($RutaReal)));
								fwrite($fo, $contenido);
								fclose($fx);
							}
						}
						fclose($fo);
					}
				}
				if(!$ps['Valk']){
					$Scripts[] = str_replace('cache','k', '<script '.(($ps['Async'])?'async':'').' src="'.$RutaCache.'?v='.$this->MinorVersion.'"></script>');

				}
			}
			$Scripts[] = str_replace('cache','k', '<script '.(($ps['Async'])?'async':'').' src="'.$RutaValkJs.'?v='.$this->MinorVersion.'"></script>');

		}
		return $Scripts;
	}
}