<?php
/**
VAR
Version 1: Base Inicial en progreso
Njong Alvax
 */
/**NJONG 25.09.18: Variables del Entorno**/
{
    $Var['Title'] = 'Aplicación de Certificados';
    $Var['Description'] = 'Aplicación de Certificados';
    $Var['Keyword'] = 'aplicacion,certificados,alto,tabancura,clinica,vacunas,alumno,universidad,descarga,certificado';
    $Var['Prefix_Fisico'] = '/AppCertificados';
    //$Var['Prefix_Fisico'] = '';
}

{
    $Var['Default']['Load'] = 'dashboard';
    $Var['Default']['IdLoad'] = '1';
}

/**NJONG 25.11.05: ROOT**/
{
    $Var['Root']['Base']            = ((defined('Root'))? constant('Root'): $Manifest['Root'] );
    $Var['Root']['AvatarUsuario']   = 'static/avatar/';
    $Var['Root']['BordeAvatar']     = 'static/superAvatar/';
}

/**NJONG 25.11.05: URL**/
{
    $Var['Url']['AvatarDefault']    = 'user_nn.jpg';
}

/**NJONG 25.11.05: LOCAL**/
{
    $Var['Local']['Css']            = '1.0';
    $Var['Local']['Js']             = '1.0';
    $Var['Local']['Favicon']        = '1.0';
}

/** Facebook **/
{
    $Var['Facebook']['Id']      = '';
    $Var['Facebook']['Secret']  = '';
}