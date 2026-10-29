<?php
function Excel($DatosRaw, $NombreArchivo = 'Datos', $HeaderStyles = null, $RowStyles= null){
	if(is_array($DatosRaw)) {
		require_once(constant('Root_Fisica').constant('Root_Mu').'cXLSXWriter.php');
		$Datos = [];
		if (isset($DatosRaw[0])) {
			$Datos = $DatosRaw;
		}
		else {
			$Datos[0] = $DatosRaw;
		}
		$NombreArchivo = $NombreArchivo.'_'.date('dm').'_'.date("Hi").'.xlsx';
		$Header = [];
		foreach (array_keys($Datos[0]) as $ke) {
			$Header[$ke] = 'string';
		}
		if ($HeaderStyles == null || !is_array($HeaderStyles)) {
			$HeaderStyles = array('auto_filter' => true, 'font' => 'Calibri', 'font-size' => 11, 'font-style' => 'bold', 'halign' => 'left', 'border' => 'left,right,top,bottom', 'border-style' => 'thin');
		}
		if ($RowStyles == null || !is_array($RowStyles)) {
			$RowStyles = array('font' => 'Calibri', 'font-size' => 11, 'halign' => 'left', 'border' => 'left,right,top,bottom', 'border-style' => 'thin');
		}
		$XLSXWriter = new XLSXWriter();
		$XLSXWriter->writeSheetHeader('Datos', $Header, $HeaderStyles);
		foreach ($Datos as $dat) {
			$XLSXWriter->writeSheetRow('Datos', array_values($dat), $RowStyles);
		}
		$XLSXWriter->writeToFile(constant('Root_Fisica').constant('Root_Out') . $NombreArchivo);
		//$writer->writeToStdOut();
		//return $writer->writeToString();
		$Output['NombreArchivo'] = $NombreArchivo;
		$Output['CodigoOut'] = urlencode(Encode($NombreArchivo));
		$Output['Script'] = Redirect(constant('Root_Base').constant('Prefix_Out').'/'.urlencode(Encode($NombreArchivo)), 2);
	}
	else{
		$Output['NombreArchivo'] = '';
		$Output['Script'] = '';
		$Output['Error'] = 'Datos enviados incorrectos.';
	}
	return $Output;
}
function ReturnFile($FileContent){


}