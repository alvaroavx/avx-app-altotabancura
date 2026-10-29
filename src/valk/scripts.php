<?php
$Scripts = [];
$Cache = constant('Root_Fisica').constant('Root_Cache').constant('Prefix_Js').constant('Prefix_Valk').((constant('Entorno_Developer'))? 'dev' : '').constant('Local_Js').'.php';
if(file_exists($Cache)){
    include($Cache);
}
else{
    $ScriptsCore = $Wiss->Scripts();
        if(isset($ScriptsCore[0])){
        $fp = fopen($Cache, 'a');
        if($fp){
            fwrite($fp, '<?php' . PHP_EOL);
            foreach ($ScriptsCore as $sc) {
	            $Scripts[] = $sc;
                fwrite($fp, '$Scripts[] = \'' . $sc . '\';' . PHP_EOL);
            }
            fclose($fp);
        }
    }
}
if(constant('Entorno_Developer')){
	$Scripts[] = '<script async src="'.constant('Root_JsValk').'v'.constant('Version_Js').'/valk.debug.js'.'"></script>';
}
foreach ($Scripts as $sc){
	echo $sc;
};
$RootScripts = constant('Root_Js').constant('Prefix_Js');
if(constant('Entorno_Developer')){
	foreach($Construct as $co) {
		/**************************************************************************/
		if(file_exists($RootScripts.constant('Prefix_Omega').$co.'.js')) {
			echo '<script src="'.$RootScripts.constant('Prefix_Omega').$co.'.js?v'.constant('Local_Js').'"></script>';
		}
		/**************************************************************************/
		if(file_exists($RootScripts.constant('Prefix_Alfa').$co.'.js')) {
			echo '<script src="'.$RootScripts.constant('Prefix_Alfa').$co.'.js?v'.constant('Local_Js').'"></script>';
		}
	}
}
else{
	/**************************************************************************/
	$CacheScriptsOmega = constant('Root_Cache').constant('Prefix_Js').constant('Prefix_Omega').constant('Local_Js').'.js';
	$CacheScriptsAlfa = constant('Root_Cache').constant('Prefix_Js').constant('Prefix_Alfa').constant('Local_Js').'.js';
	/**************************************************************************/
	$FrontScriptsOmega = constant('Prefix_Cache').'/'.constant('Prefix_Js').constant('Prefix_Omega').constant('Local_Js').'.js';
	$FrontScriptsAlfa = constant('Prefix_Cache').'/'.constant('Prefix_Js').constant('Prefix_Alfa').constant('Local_Js').'.js';
	/**************************************************************************/
	if(! file_exists($CacheScriptsOmega)){
		$fomega = fopen($CacheScriptsOmega, 'a');
		if($fomega){
			foreach ($Construct as $co) {
				$RootOmega = constant('Root_Fisica').$RootScripts.constant('Prefix_Omega').$co.'.js';
				if(file_exists($RootOmega)){
					$fx = fopen($RootOmega, "rb");
					if($fx && filesize($RootOmega) > 0) {
						$contenido = LimpiaMinimizador(fread($fx, filesize($RootOmega)));
						fwrite($fomega, $contenido);
						fclose($fx);
					}
				}
			}
			fclose($fomega);
		}
	}
	/**************************************************************************/
	if(! file_exists($CacheScriptsAlfa)){
		$falfa = fopen($CacheScriptsAlfa, 'a');
		if($falfa){
			foreach ($Construct as $co) {
				$RootAlfa = constant('Root_Fisica').$RootScripts.constant('Prefix_Alfa').$co.'.js';
				if(file_exists($RootAlfa)){
					$fa = fopen($RootAlfa, "rb");
					if($fa && filesize($RootAlfa) > 0) {
						$contenido = LimpiaMinimizador(fread($fa, filesize($RootAlfa)));
						fwrite($falfa, $contenido);
						fclose($fa);
					}
				}
			}
			fclose($falfa);
		}
	}
	echo '<script src="'.$FrontScriptsOmega.'"></script>';
	echo '<script src="'.$FrontScriptsAlfa.'"></script>';
}
echo '<script>'
	.'var plugin=[];';
foreach($Manifest['Plugin'] as $plk => $plv){
	echo 'plugin["'.strtolower($plk).'"]='.$plv.';';
}
echo '</script>';

