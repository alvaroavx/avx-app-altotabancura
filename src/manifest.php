<?php
/**
   MANIFEST
   Version 1: Base Inicial en progreso
   Njong Alvax
**/

/**NJONG 25.09.18: Constantes de funcionamiento del Gate**/
/**NJONG 25.09.18: Variables de identificacion**/

$Manifest['Gate']                       = 'appcertificados';

//$Manifest['Root']                       = 'http://localhost/AppCertificados/';
$Manifest['Root']                       = 'http://vacunatorioaltotabancura.cl/';
//$Manifest['Steins']                     = 'http://localhost/appcertificados/';
$Manifest['Steins']                     = 'http://vacunatorioaltotabancura.cl/';


/**NJONG 25.09.18: Variables del Entorno**/
$Manifest['Entorno']['Developer']       = 0;
$Manifest['Entorno']['DBServer']        = 'PRO';
$Manifest['Entorno']['MailServer']      = 'Steins';
$Manifest['Entorno']['Error']           = 0;
$Manifest['Entorno']['BypassCorreo']    = 1;

/**NJONG 25.09.18: Version consulta**/
$Manifest['Version']['Valk']            = '1.0';
$Manifest['Version']['Css']             = '1.0';
$Manifest['Version']['Js']              = '1.0';
$Manifest['Version']['Favicon']         = '1.0';

/**NJONG 26.09.18: Modulos Cargadas**/
$Manifest['Modulo']['Facebook']         = '0';
$Manifest['Modulo']['Google']           = '0';
$Manifest['Modulo']['Jodit']            = '0';

/* Laboratorio local: aísla la aplicación de producción sin cambiar su configuración productiva. */
if (getenv('APP_ENV') === 'local') {
    $BaseUrl = getenv('APP_BASE_URL');
    if ($BaseUrl === false || $BaseUrl === '') {
        $BaseUrl = 'http://127.0.0.1:8080/';
    }
    $BaseUrl = rtrim($BaseUrl, '/') . '/';

    $Manifest['Root']                       = $BaseUrl;
    $Manifest['Steins']                     = $BaseUrl;
    $Manifest['Entorno']['DBServer']        = 'LOCAL';
    $Manifest['Entorno']['BypassCorreo']    = 1;
    $Manifest['Entorno']['Error']           = (getenv('APP_DEBUG') === '1') ? 1 : 0;
    $Manifest['Modulo']['Facebook']         = '0';
    $Manifest['Modulo']['Google']           = '0';
    $Manifest['Modulo']['Jodit']            = '0';
}
