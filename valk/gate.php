<?php
/**
    GATE
    Version 1: Base Inicial en progreso
    Njong Alvax
 **/
require_once('loader.php');
$raw_data = file_get_contents('php://input');
$Data = json_decode(urldecode(encrypt_decrypt('decrypt', $raw_data)),1);
if(ValidaAcceso($Data)){
    LineaAlfa($Data);
}
else{
    LineaBeta();
}
function LineaAlfa($data){
	header("Content-Type: text/html");
	$Valk = new Valk($data);
	$Output = $Valk->Execute();
	echo encrypt_decrypt('encrypt', json_encode($Output));
}
function LineaBeta(){
	header("content-type: application/json");
	echo json_encode('ERROR ACCESO');
}