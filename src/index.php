<?php
include_once('valk/loader.php');
require_once(constant('File_Alphonse'));
CleanSesionCache();
echo '<!DOCTYPE html>'
    .'<html lang="es">'
    .'<head>';
include_once(constant('File_Meta'));
require_once(constant('File_Styles'));
require_once(constant('File_Snitch'));
echo '</head>'
    .'<body onload="$.LoadCore();">';
require_once(constant('File_Kirito'));
echo '<header></header>'
    .'<div id="constructor" data-load="'.$Load.'" data-idload="'.$IdLoad.'"></div>'
    .'<div id="optionalmodal"><div id="optionalview"><div id="optionalclose"></div><div id="optionalcontenedor"></div></div></div>'
    .'<div id="blockmodal"><div id="blockview"><div id="blockclose"></div><div id="blockcontenedor"></div></div></div>'
    .'<div id="shadowmodal"></div>'
    .'<div id="loadmodal"><div id="loadview"></div></div>'
    .'<div id="fondo"></div>'
    .'<div id="leftcol"></div>'
    .'<div id="rightcol"></div>'
    .'<div id="goup" onclick="$.GoUp();"></div>'
    .'<div id="midblock"></div>'
    .'<footer></footer>';
require_once(constant('File_Scripts'));
echo '</body>'
    .'</html>';
