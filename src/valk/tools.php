<?php
$Tools = [
    'Core',
    'Sesion',
    'Formato',
    'Encode',
    'Acceso',
	'Debug',
	'Valk',
	'Excel'
];
foreach ($Tools as $too) {
    include_once(constant('Root_Fisica').$Constants['Root']['Tools'].$Constants['Prefix']['Tools'].$too.'.php');
}






