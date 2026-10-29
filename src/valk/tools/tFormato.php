<?php
/**NJONG 06.11.18: TOOLS FORMATO**/
{
    /**
     * FormatearMoneda: da formato de moneda con decimales
     * @param integer $monto
     * @return null
     */
    function FormatearMoneda($monto){
        return number_format($monto,0,',','.');
    }

    /**
     * Convierte fechas a distintos formatos
     * 1: dd/mm/aaaa
     * 2: dd-mm-aaaa
     * 3: dia dd de mes, yyyy
     * 4: dd de mescorto yyyy
     * 5: dia dd de mes, yyyy, h:mm pm
     * 6: dia dd de mes, yyyy, hh:mm pm
     * 7: dia dd de mes, yyyy, h:mm
     * 8: dia dd de mes, yyyy, hh:mm
     * 9: dd mes <br> corto
     * 10: dia dd de mes
     * 11: mm
     * 12: dia dd
     * 13: mes
     * 14: dia dd de mes, h:mm pm
     * 15: dd/mm/aaaa, hh:mm
     * @param $FechaOriginal
     * @param $FormatoFecha
     * @return false|string
     * @throws Exception
     */
    function FormatearFecha($FechaOriginal, $FormatoFecha){
        $Meses  = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        $Dias = array('','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
        $MesesCorto = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
        $DiasCorto = array('','Lun','Mar','Mier','Jue','Vie','Sab','Dom');
        if(is_array($FechaOriginal)){
            $FechaOriginal = new DateTime($FechaOriginal['date'], new DateTimeZone(constant('TimeZone')));
        }
        if($FechaOriginal instanceof DateTime){
            $date = $FechaOriginal->getTimestamp();
        }
        else {
            $date = strtotime($FechaOriginal);
        }

        switch($FormatoFecha){
            case 1:     //dd/mm/aaaa
                $result = date('d/m/Y',$date);
                break;
            case 2:     //dd-mm-aaaa
                $result = date('d-m-Y',$date);
                break;
            case 3:     //dia dd de mes, yyyy
                $result = $Dias[date('N',$date)].' '.date('d',$date).' de '.$Meses[date('n',$date)].', '.date('Y',$date);
                break;
            case 4:     //dd de mes yyyy
                $result = date('d',$date).' de '.$MesesCorto[date('n',$date)].' '.date('Y',$date);
                break;
            case 5:     //dia dd de mes, yyyy, h:mm pm
                $result = $Dias[date('N',$date)].' '.date('d',$date).' de '.$Meses[date('n',$date)].', '.date('Y',$date).', '.date("g:i a",$date);
                break;
            case 6:     //dia dd de mes, yyyy, hh:mm pm
                $result = $Dias[date('N',$date)].' '.date('d',$date).' de '.$Meses[date('n',$date)].', '.date('Y',$date).', '.date("h:i a",$date);
                break;
            case 7:     //dia dd de mes, yyyy, h:mm
                $result = $Dias[date('N',$date)].' '.date('d',$date).' de '.$Meses[date('n',$date)].', '.date('Y',$date).', '.date("G:i",$date);
                break;
            case 8:     //dia dd de mes, yyyy, hh:mm
                $result = $Dias[date('N',$date)].' '.date('d',$date).' de '.$Meses[date('n',$date)].', '.date('Y',$date).', '.date("H:i",$date);
                break;
            case 9:     //dd <br> mescorto
                $result = date('d',$date).'<br>'.$MesesCorto[date('n',$date)];
                break;
            case 10:     //dia dd de mes
                $result = $Dias[date('N',$date)].' '.date('d',$date).' de '.$Meses[date('n',$date)];
                break;
            case 11:    //mm
                $result = date('m',$date);
                break;
            case 12:    //dia dd
                $result = $Dias[date('N',$date)].' '.date('d',$date);
                break;
            case 13:    //mes
                $result = date('n',$date);
                break;
            case 14: //dia dd de mes, mm pm
                $result = $Dias[date('N',$date)].' '.date('d',$date).' de '.$Meses[date('n',$date)].', '.date("g:i a",$date);
                break;
            case 15: //dd/mm/aaaa, hh:mm
                $result = date('d/m/Y',$date).', '.date("H:i",$date);
                break;
            case 16: //aaaa/mm/dd, hh:mm
                $result = date('Y/m/d',$date).', '.date("H:i",$date);
                break;
            case 17: //aaaa-mm-dd hh:mm
                $result = date('Y-m-d',$date).' '.date("H:i:s",$date);
                break;
            case 18: //hh:mm dd-mm-yyyy
                $result = date("H:i",$date).' '.date('d-m-Y',$date);
                break;
            case 19: // time ago
                $strTime = array("segundo", "minuto", "hora", "día", "mes", "año");
                $length = array("60","60","24","30","12","10");
                $currentTime = time();
                if($currentTime >= $date) {
                    $diff     = time()- $date;
                    for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
                        $diff = $diff / $length[$i];
                    }
                    $diff = round($diff);
                    if($diff > 1) {
                        $result = "Hace " . $diff . " " . $strTime[$i] . "(s)";
                    }
                    else {
                        $result = "Hace " . $diff . " " . $strTime[$i];
                    }
                }
                else {
                    $result = "Ahora mismo";
                }
                break;
            default:
                $result = $FechaOriginal;
        }
        return $result;
    }


	function ArrayToXml($Data, $rootNodeName = 'data', $Xml = null){
		/**
		 * The main function for converting to an XML document.
		 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
		 * @param array $data
		 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
		 * @param SimpleXMLElement $xml - should only be used recursively
		 * @return string XML
		 */
		if (ini_get('zend.ze1_compatibility_mode') == 1){
			ini_set('zend.ze1_compatibility_mode', 0);
		}
		if ($Xml == null){
			$Xml = simplexml_load_string('<?xml version="1.0"?><'.$rootNodeName.'/>');
		}
		if (is_array($Data)){
			foreach($Data as $key => $value){
				if (is_numeric($key)){
					$key = 'row';
				}
				$key = str_replace('&nbsp;',' ',preg_replace('/[^a-z]/i', '', $key));
				if (is_array($value)){
					$node = $Xml->addChild($key);
					ArrayToXml($value, $rootNodeName, $node);
				}
				else{
					$Xml->addChild($key,$value);
				}
			}
		}
		return $Xml->asXML();
	}

	function LimpiaMinimizador($Contenido){
		$Contenido = preg_replace('/(?<!:)\/\/.*/', '', $Contenido);
		$Contenido = str_replace(PHP_EOL, '', $Contenido);
		$Contenido = str_replace('\t', '', $Contenido);
		$Contenido = str_replace('../../','../',$Contenido);
		$Contenido = preg_replace('/\/\*(.|[\r\n])*?\*\//', ' ', $Contenido);
		$Contenido = preg_replace('/\s+/', ' ', $Contenido);
		return $Contenido;
	}
}