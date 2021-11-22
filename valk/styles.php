<?php

$Styles = [];
$Cache = constant('Root_Fisica').constant('Root_Cache').constant('Prefix_Css').constant('Prefix_Valk').constant('Version_Css').'.php';
if(file_exists($Cache)){
    include($Cache);
}
else{
    $StylesCore = $Wiss->Styles();
    if(isset($StylesCore[0])){
        $fp = fopen($Cache, 'a');
        if ($fp) {
            fwrite($fp, '<?php' . PHP_EOL);
            foreach ($StylesCore as $st) {
                $Styles[] = $st;
                fwrite($fp, '$Styles[] = \'' . $st . '\';' . PHP_EOL);
            }
            fclose($fp);
        }
    }
}
foreach ($Styles as $st){
	echo $st;
};
$RootStyles = constant('Root_Css').constant('Prefix_Css');
if(constant('Entorno_Developer')){
	foreach($Construct as $co) {
		/**************************************************************************/
		if(file_exists($RootStyles.constant('Prefix_Omega').$co.'.css')) {
			echo '<link rel="stylesheet" type="text/css" href="'.$RootStyles.constant('Prefix_Omega').$co.'.css?v'.constant('Local_Css').'">';
		}
		/**************************************************************************/
		if(file_exists($RootStyles.constant('Prefix_Alfa').$co.'.css')) {
			echo '<link rel="stylesheet" type="text/css" href="'.$RootStyles.constant('Prefix_Alfa').$co.'.css?v'.constant('Local_Css').'">';
		}
	}
}
else{
	/**************************************************************************/
	$CacheStylesOmega = constant('Root_Cache').constant('Prefix_Css').constant('Prefix_Omega').constant('Local_Css').'.css';
	$CacheStylesAlfa = constant('Root_Cache').constant('Prefix_Css').constant('Prefix_Alfa').constant('Local_Css').'.css';
	/**************************************************************************/
	$FrontStylesOmega = constant('Prefix_Cache').'/'.constant('Prefix_Css').constant('Prefix_Omega').constant('Local_Css').'.css';
	$FrontStylesAlfa = constant('Prefix_Cache').'/'.constant('Prefix_Css').constant('Prefix_Alfa').constant('Local_Css').'.css';
	/**************************************************************************/
	if(! file_exists($CacheStylesOmega)){
		$fomega = fopen($CacheStylesOmega, 'a');
		if($fomega){
			foreach ($Construct as $co) {
				$RootOmega = constant('Root_Fisica').$RootStyles.constant('Prefix_Omega').$co.'.css';
				if(file_exists($RootOmega)){
					$fx = fopen($RootOmega, "rb");
					if($fx && filesize($RootOmega) > 0) {
						$contenido = fread($fx, filesize($RootOmega));

						$contenido = str_replace(PHP_EOL,'',$contenido);
						$contenido = str_replace('\t','',$contenido);
						$contenido = str_replace('../../','../',$contenido);
						$contenido = preg_replace('/\/\*(.|[\r\n])*?\*\//', ' ', $contenido);
						$contenido = preg_replace('/\s+/', ' ', $contenido);
						fwrite($fomega, $contenido);
						fclose($fx);
					}
				}
			}
			fclose($fomega);
		}
	}
	/**************************************************************************/
	if(! file_exists($CacheStylesAlfa)){
		$falfa = fopen($CacheStylesAlfa, 'a');
		if($falfa){
			foreach ($Construct as $co) {
				$RootAlfa = constant('Root_Fisica').$RootStyles.constant('Prefix_Alfa').$co.'.css';
				if(file_exists($RootAlfa)){
					$fa = fopen($RootAlfa, "rb");
					if($fa && filesize($RootAlfa) > 0) {
						$contenido = fread($fa, filesize($RootAlfa));

						$contenido = str_replace(PHP_EOL,'',$contenido);
						$contenido = str_replace('\t','',$contenido);
						$contenido = str_replace('../../','../',$contenido);
						$contenido = preg_replace('/\/\*(.|[\r\n])*?\*\//', ' ', $contenido);
						$contenido = preg_replace('/\s+/', ' ', $contenido);
						fwrite($falfa, $contenido);
						fclose($fa);
					}
				}
			}
			fclose($falfa);
		}
	}
	/**************************************************************************/
	echo '<link rel="stylesheet" type="text/css" href="'.$FrontStylesOmega.'">';
	echo '<link rel="stylesheet" type="text/css" href="'.$FrontStylesAlfa.'">';
}





