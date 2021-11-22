<?php
/**NJONG 06.11.18: TOOLS ACCESO**/

{
    function ValidaAcceso($raw_data){
        if($raw_data){
            return 1;
        }
        else {
            return 0;
        }
    }
    /**
     * FiltroAcceso: filtra el acceso al sitio
     * @param null $Destino
     */
    function FiltroAcceso($Destino = null)
    {
        if (!isset($Destino)) {
            $Destino = constant('Root_Base');
        }
        if (empty($_SERVER['HTTP_REFERER'])) {
            header("Location:" . $Destino);
        }
    }
    function FiltroAdmin($Destino = null)
    {
        if (!isset($Destino)) {
            $Destino = constant('Root_Base');
        }
        if (!ValidaAdmin()) {
            header("Location:" . $Destino);
        }
        return null;
    }
    /**NJONG 30.09.2018 Tools Sesion**/
    /**
     * IP: retorna ip
     * @return string
     */
    function IP(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $IP = $_SERVER['HTTP_CLIENT_IP'];
        }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $IP = $_SERVER['REMOTE_ADDR'];
        }
        return $IP;
    }
	function LocationRoot(){
        //echo constant('Root_Base');
		header("Location:" . constant('Root_Base'));
	}
    function Redirect($UrlObjetivo, $Tipo = 1){
        switch ($Tipo){
            case '1'://Misma pestaña
                $link = 'location.href=\''.$UrlObjetivo.'\';';
                break;
            case '2'://Nueva pestaña
                $link = 'window.open(\''.$UrlObjetivo.'\',\'_blank\');';
                break;
            default:
                $link = '';
        }
        return $link;
    }
}