<?php
/**NJONG 06.11.18: TOOLS SESION**/
{
    /**
     * FiltrarSesion: si no estas validado desloguea
     */
    function FiltrarSesion(){
        if (! ValidaSesion()) {
            echo '<script>$.LoadMidBlock(1);</script>';
        }
    }
    /**
     * ValidaSesion: retorna sesion si existe, si no 0
     * @return integer
     */
    function ValidaAdmin()    {
        return (Sesion('admin') != '0') ? 1 : 0;
    }
    /**
     * ValidaSesion: retorna sesion si existe, si no 0
     * @return integer
     */
    function ValidaSesion()    {
        return (Sesion('idusuario') != '0') ? 1 : 0;
    }
    /**
     * Sesion: obtiene una variable de sesion
     * @param string $Nombre
     * @return string
     */
    function Sesion($Nombre){
        $Variable = constant('Gate') . '_' . $Nombre;
        return (isset($_SESSION[$Variable])) ? $_SESSION[$Variable] : '0';
    }
    /**
     * SetSesion: guarda una variable en sesion
     * @param string $Nombre
     * @param string $Valor
     * @return null
     */
    function SetSesion($Nombre, $Valor = null){
    	if($Valor != null){
		    $_SESSION[constant('Gate') . '_' . $Nombre] = $Valor;
	    }
    	else{
    		unset($_SESSION[constant('Gate') . '_' . $Nombre]);
	    }
        return null;
    }
    function SetSesionCache($Nombre, $Id, $Valor){
        $_SESSION[constant('Gate') . '_Cache'][$Nombre][$Id] = $Valor;
        return null;
    }
    function SesionCache($Nombre, $Id){
        return ((isset($_SESSION[constant('Gate') . '_Cache'][$Nombre][$Id]))? $_SESSION[constant('Gate') . '_Cache'][$Nombre][$Id] : null);
    }
    function CleanSesionCache(){
        unset($_SESSION[constant('Gate') . '_Cache']);
    }
    /**
     * SetSesion: elimina sesion
     * @return null
     */
    function RemoveSesion(){
        session_destroy();
        return '<script>clearTimeout(HeartBeat);$.LoadCore();</script>';
    }
}