<?php

function LoadModulo($Manifest, $Modulos){
	foreach($Modulos as $mod){
		$ModRoot = constant('Root_Fisica').constant('Root_Mu').'v'.$Manifest['Version']['Valk'].'/'.$mod.'.php';
		if(file_exists($ModRoot)) {
			require_once($ModRoot);
		}
		else{
			return 0;
		}
	}
	return 1;
}

function LoadVendor($Vendors){
	foreach($Vendors as $ven){
		$VenRoot = constant('Root_Fisica').constant('Root_Vendor').$ven.'/doorlock.php';
		if(file_exists($VenRoot)) {
			require_once($VenRoot);
		}
	}
}