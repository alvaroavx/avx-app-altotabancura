<?php
function DatosSesion($data){
    $Debug = [];
    $Debug['IP'] = IP();
    $Debug['MANIFEST'] = $data['Manifest'];
    $Debug['VAR'] = $data['Var'];;
    $Debug['SESION'] = $_SESSION;
    $Debug['COOKIE'] = $_COOKIE;
    //$Debug['SERVER'] = $_SERVER;
    print_r($Debug);
}
function CleanCache($data){
	$Wiss = new Wiss();
	$Directorio = constant('Root_Fisica').constant('Root_Cache');
	$Archivos = scandir($Directorio);
	foreach($Archivos as $ar){
		if (is_file($Directorio.$ar)){
			unlink($Directorio.$ar);
		}
	}
	$Wiss->CleanCache();
}
