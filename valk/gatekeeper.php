<?php
require_once(constant('Root_Fisica').constant('File_Class'));
if(isset($_POST['rawdata'])){
    $Origen = str_replace(' ','+',$_POST['rawdata']);
    $raw_data = DecodePost($Origen);
}
elseif(isset($_POST['rawestdata'])){
    $Origen = $_POST['rawestdata'];
    $raw_data = TranscodePost($Origen);
}
foreach($Ro as $ro_i){
	$FileRo = constant('Root_Fisica').constant('Root_Ro').constant('Prefix_Ro').$ro_i.'.php';
	if(file_exists($FileRo)){
		require_once($FileRo);
	}
}
foreach($Construct as $co){
	$FileAlfa = constant('Root_Fisica').constant('Root_Alfa').constant('Prefix_Alfa').$co.'.php';
	$FileOmega = constant('Root_Fisica').constant('Root_Omega').constant('Prefix_Omega').$co.'.php';
	$FileAO = constant('Root_Fisica').constant('Root_Omega').$co.'.php';
	if(file_exists($FileAlfa) && Sesion('admin')){
		require_once($FileAlfa);
	}
	if(file_exists($FileOmega)){
		require_once($FileOmega);
	}
	if(file_exists($FileAO)){
		require_once($FileAO);
	}
}
$Variable = $raw_data;
$raw_data['Manifest'] = $Manifest;
$raw_data['Var'] = $Var;
unset($Variable['valk'],$Variable['fact'],$Variable['edward'],$Variable['load'],$Variable['idload']);
if(isset($raw_data['valk']) && $raw_data['valk']!=''){
	if(constant('Entorno_Developer') && $raw_data['edward']){
		Edward($raw_data['valk'], $Variable);
	}
	call_user_func($raw_data['valk'],$raw_data);
}
