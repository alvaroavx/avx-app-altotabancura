<?php
/**NJONG 06.11.18: TOOLS ENCODE**/
{
    /**ALVAX 07.10.2018 LE CAMBIARIA EL NOMBRE A: EstandarizaSaltosLinea **/
    /**
     * SaltoLinea: cambia los saltos de linea, se debe enviar el formato de salida deseado: string o html
     * @param string $texto
     * @param string $salida
     * @return string
     */
    function SaltoLinea($texto, $salida=null){
        // lineas a reemplazar
        $reemplazar = array(
            "\n",
            "\\r\\n",
            "\\r",
            "\\n",
            "<br>",
            "<br/>"
        );
        $reemplazo = "";
        if (null === $salida or $salida == "html") {
            $reemplazo = "<br>";
        }
        else if($salida == "string"){
            $reemplazo = "\\n";
        }
        return str_replace($reemplazar, $reemplazo, $texto);
    }

    /** ALVAX 07.10.2018LE CAMBIARIA EL NOMBRE A: ReemplazaCaracteresHtml**/
    /**
     * LimpiaHtml: reemplaza caracteres HTML, la salida puede ser html o string
     * @param string $texto
     * @param string $salida
     * @return string
     */
    function LimpiaHtml($texto, $salida=null){
        $reemplazos = [
            "&aacute;"  => "á",
            "&eacute;"  => "é",
            "&iacute;"  => "í",
            "&oacute;"  => "ó",
            "&uacute;"  => "ú",
            "&ntilde;"  => "ñ",
            "&Aacute;"  => "Á",
            "&Eacute;"  => "É",
            "&Iacute;"  => "Í",
            "&Oacute;"  => "Ó",
            "&Uacute;"  => "Ú",
            "&Ntilde;"  => "Ñ",
            "&ldquo;"   => "&quot;",
            "&rdquo;"   => "&quot;",
            "&hellip;"  => "...",
            "&iquest;"  => "¿",
            "&nbsp;"    => " ",
            "&#39;"     => "'",
            "&quot;"    => "&quot;",
            "&mdash;"   =>"-",
            /**ALVAX 07.10.2018 tener ojo con este arreglo**/
            "<br>"      => "\\n",
            "<br/>"     => "\\n"
        ];

        $Output = '';

        if (null === $salida || $salida == "html") {
            $Output = str_replace(array_keys($reemplazos), $reemplazos, $texto);
        }
        else if($salida == "string"){
            $Output = str_replace($reemplazos, array_keys($reemplazos), $texto);
        }

        return $Output;
    }

    /**
     * DecodePost:
     * @param string $request
     * @return array
     */
    function DecodePost($request){
        $raw_data = array();
        $decode_request = utf8_decode(LimpiaHtml(urldecode(base64_decode($request))));
        $temp = explode('<njong>', $decode_request);
        foreach($temp as $tval) {
            $t = explode('<alvax>', $tval);
            if(isset($t[1])) {
                $raw_data[$t[0]] = $t[1];
            }
        }
        return $raw_data;
    }

    /**
     * DecodeEspecial:
     * @param string $base
     * @param string $separador
     * @return array
     */
    function DecodeEspecial($base, $separador){
        $raw_data = array();
        $temp = explode($separador,$base);
        foreach($temp as $tval) {
            if( $tval != ''){
                $raw_data[]['value'] = $tval;
            }
        }
        return $raw_data;
    }

    /**
     * TranscodePost:
     * @param string $request
     * @return array
     */
    function TranscodePost($request){
        $raw_data = array();
        $temp = explode('</valk>', $request);
        foreach($temp as $tval) {
            if($tval != 'undefined') {
                $t = explode('<valk>', $tval);
                $raw_data[$t[0]] = utf8_decode(LimpiaHtml($t[1]));
            }
        }
        return $raw_data;
    }

    /**
     * Encode: encripta un string segun el metodo enviado (puede ser: base64, rfc, url)
     * @param string $request
     * @param string $metodo
     * @return string
     */
    function Encode($request, $metodo = null){
        switch($metodo) {
            case 'base64':
                return base64_encode($request);
                break;
            case 'rfc':
                return rawurlencode($request);
                break;
            case 'url':
                return urlencode($request);
                break;
            default:
                return base64_encode($request);
                break;
        }
    }

    /**
     * Decode: desencripta un string segun el metodo enviado (puede ser: base64, rfc, url)
     * @param string $request
     * @param string $metodo base64, rfc, url
     * @return string
     */
    function Decode($request, $metodo = null){
        switch($metodo) {
            case 'base64':
                return base64_decode($request);
                break;
            case 'rfc':
                return rawurldecode($request);
                break;
            case 'url':
                return urldecode($request);
                break;
            default:
                return base64_decode($request);
                break;
        }
    }
	/**
	 * Metodo de encriptacion y desencriptacion de un texto plano
	 *
	 * @param string $action: 'encrypt' o 'decrypt'
	 * @param string $string: texto
	 *
	 * @return string
	 */
	function encrypt_decrypt($action, $string) {
	$output = false;
	$encrypt_method = "AES-256-CBC";
	$Keys = [];
	require(constant('Root_Fisica').'valk/preloader.php');
	// hash
	$key = hash('sha256', $Keys['OpenSSL']['Key']);
	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr(hash('sha256', $Keys['OpenSSL']['IV']), 0, 16);
	if ( $action == 'encrypt' ) {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	} else if( $action == 'decrypt' ) {
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}
	return $output;
}
}