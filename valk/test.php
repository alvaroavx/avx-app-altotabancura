<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
require_once('loader.php');

$ManifestProxy['Version']['Valk'] = constant('Version_Default');
$ManifestProxy['Gate'] = 'Spotted';

$ManifestProxy['Root']                       = 'http://localhost/spotted/';
/**NJONG 25.09.18: Variables del Entorno**/
$ManifestProxy['Entorno']['Developer']       = 1;
$ManifestProxy['Entorno']['DBServer']        = 'server';
$ManifestProxy['Entorno']['MailServer']      = 'steins';
$ManifestProxy['Entorno']['Error']           = 1;
$ManifestProxy['Entorno']['BypassCorreo']    = 1;
/**NJONG 25.09.18: Version consulta**/
$ManifestProxy['Version']['Valk']            = '1.0';
$ManifestProxy['Version']['Css']             = '1.0';
$ManifestProxy['Version']['Js']              = '1.0';
$ManifestProxy['Version']['Favicon']         = '1.0';
/**NJONG 26.09.18: Modulos Cargadas**/
$ManifestProxy['Modulo']['Facebook']         = '1';
$ManifestProxy['Modulo']['Google']           = '1';

$ManifestProxy['Usuario']          = '0';
$ManifestProxy['IdUsuario']        = '0';
$ManifestProxy['IP']               = '1.1.0.0';



$Modo = 'Query';
$Capsula['Metodo']  = 'DatosUsuario';
$Capsula['Proc']  = 'spRec_Elemento_Elemento';
$Capsula['Var'] = [
	'IdElemento' => 6,
	'IdUsuario' => 1,
	'Usuario' => 'admin@alvax.cl',
	'Clave' => md5('123456'),
	'Gate' => 'Spotted',
	'IdTipoLog' => 0,
	'Busqueda' => '',
	'FechaDesde' => null,
	'FechaHasta' => null,
	'IdLog' => 1,

];

$Data = $Capsula;
$Valk = new Valk(['Manifest' => $ManifestProxy, 'Modo' => $Modo, 'Data' => $Data]);

print_r([$Modo,$Data]);
echo '<br>';
print_r($Valk->Execute());
