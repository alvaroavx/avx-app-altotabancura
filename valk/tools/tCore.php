<?php
/**NJONG 06.11.18: TOOLS ACCESO**/
{
    /**
     * Query: funcion que crea constantes
     * @param array $Datos:
     * @param string $Prefix:
     * @return void
     */
    function Params($Datos, $Prefix = ''){
        if ($Prefix != '') {
            $Prefix = $Prefix . '_';
        }
        foreach ($Datos as $clave => $valor) {
            if (is_array($valor)) {
                Params($valor, $Prefix . $clave);
            } else {
                define($Prefix . $clave, $valor);
            }
        }
    }

    function Log2($Log,$var_name, $data_mensaje) {
        $log_file = constant('Root_Fisica');
        switch($Log){
            case 'mail':
                //$log_file .= constant('LogMail');
                break;
            default:
                $log_file .= 'log.txt';//constant('LogDefault');
                break;
        }
        $fp = fopen($log_file, 'a');
        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        $time = @date('[d/M/Y:H:i:s]');
        if(is_array($data_mensaje) || is_object($data_mensaje)){
            $mensaje = json_encode($data_mensaje);
        } else {
            $mensaje = $data_mensaje;
        }
        fwrite($fp, "$time ($script_name)->$var_name: $mensaje" . PHP_EOL);
        fclose($fp);
    }



    /**
     * Query: Trae Lang
     * @return array
     */
    function Lang(){
        $Lang = [];
        $langroot = constant('Root_Fisica').constant('Root_Lang').constant('Prefix_Lang').substr(basename($_SERVER['PHP_SELF']), strlen(constant('Prefix_Construct')));
        if(file_exists($langroot)){
            @include($langroot);
        }
        return $Lang;
    }

    function IsNull($var, $case){
        if($var != null){
            return $var;
        }
        else{
            return $case;
        }
    }
}