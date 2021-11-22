<?php
$Preloader = [
	constant('File_D_Manifest'),
	constant('File_D_Keys'),
	constant('File_D_Var'),
	constant('File_D_Meta'),
	constant('File_Ro'),
	constant('File_Manifest'),
	constant('File_Gate_Var'),
	constant('File_Gate_Meta'),
	constant('File_Construct'),
	constant('File_Gate_Keys'),
	constant('File_Shortcut'),
];
/**********************************************************************************************************************/
foreach($Preloader as $pre) {
	require(constant('Root_Fisica').$pre);
}