<?php
include_once('valk/loader.php');
require_once(constant('Root_Fisica').constant('File_Class'));


$NombreArchivo = 'NOMBRETEMPORAL';
$fp = fopen(constant('Root_Fisica').constant('Root_Out').$NombreArchivo.'.pdf', 'w+');
if ($fp) {
    fwrite($fp, 'CUALQUIERCOSA');
    fclose($fp);
}
echo constant('Root_Fisica').constant('Root_Out').$NombreArchivo.'.pdf';

die();

$W = new Wiss();
print_r($W->Test());
die();

function CheckFecha($Fecha) {
    if($Fecha == '') {
        return '-';
    }/*
    elseif($pos !== false) {
        return '-2';
    }*/
    return FormatearFecha($Fecha, 2);
}

echo CheckFecha('1900-01-01');
echo CheckFecha('1988-02-17');
echo CheckFecha('');

die();

$U = new Usuario();
$Vacunas = $U->GetVaccines(14698);
print_r(empty($Vacunas));

die();

echo '<br>';
echo FormatearFecha(date('m/d/Y', time()), 3);

die();

$U = new Usuario();
$Usuario = $U->GetById(9268)[0];
//print_r($Usuario);



$Header = array('VACUNA', 'LOTE', 'DOSIS', 'FECHA', 'OBS');
//print_r($Header);

$Vacunas = $U->GetVaccines(9268);
$Data = array();
foreach($Vacunas as $V) {
    array_push($Data, array($V['Vacuna'], $V['Lote'], $V['Dosis'], FormatearFecha($V['Fecha'], 2)));
}

print_r($Data);

echo '<br>==================<br>';

foreach ($Data as $row) {
    echo '<br>___<br>';
    foreach($row as $col) {
        echo $col;
        echo '//';
    }
}