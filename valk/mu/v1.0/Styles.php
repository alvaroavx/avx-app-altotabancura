<?php
class Styles{
	private $Manifest;
	private $MinorVersion = '1.0';
	public function __construct($Manifest, $Gate, $Data){
		$this->Manifest     = $Manifest;
	}
	public function Execute(){
		/**Base Nombre, Sync o Async, Minimizar, Unir a archivo Valk**/
		//$PreStyles[] = ['Nombre' => 'bootstrap',           'Min' => 1, 'Valk' => 0];
		$PreStyles[] = ['Nombre' => 'valk.core',             'Min' => 1, 'Valk' => 1];
		$PreStyles[] = ['Nombre' => 'jquery.dataTables',     'Min' => 1, 'Valk' => 1];
		$Styles = [];
		if(isset($this->Manifest['Modulo']['Jodit']) && $this->Manifest['Modulo']['Jodit']){
			$Styles[] = '<link rel="stylesheet" type="text/css" href="'.constant('Root_Base').constant('Root_Vendor').'Jodit/jodit.min.css'.'">';
		}
		/**************************************************************************/
		$RutaValkCss = constant('Root_Cache').'sv'.$this->Manifest['Version']['Css'].'.css';
		if($this->Manifest['Entorno']['Developer']) {
			foreach($PreStyles as $ps){
				$Styles[] = '<link rel="stylesheet" type="text/css" href="'.constant('Root_Base').constant('Root_CssValk').'v'.$this->Manifest['Version']['Css'].'/'.$ps['Nombre'].'.css">';
			}
		}
		else{
			$ValkCssCreado = file_exists($RutaValkCss);
			foreach ($PreStyles as $ps) {
				$RutaCache = (($ps['Valk'])? $RutaValkCss
					: constant('Root_Cache').$ps['Nombre'].(($ps['Min'])? '.min': '').'.'.$this->Manifest['Version']['Css'].'.css');
				$RutaReal  = constant('Root_CssValk').'v'.$this->Manifest['Version']['Css'].'/'.$ps['Nombre'].'.css';
				/** no (existe y no es de valk) y ((es de valk y no esta creado) o no existe)**/
				if ((!(file_exists(constant('Root_Fisica').$RutaCache) && !$ps['Valk']))
					&& (($ps['Valk'] && !$ValkCssCreado)
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
					$Styles[] = str_replace('cache','k','<link rel="stylesheet" type="text/css" href="'.constant('Root_Base').$RutaCache.'?v='.$this->MinorVersion.'">');
				}
			}
			$Styles[] = str_replace('cache','k','<link rel="stylesheet" type="text/css" href="'.constant('Root_Base').$RutaValkCss.'?v='.$this->MinorVersion.'">');
		}
		return $Styles;
	}
}