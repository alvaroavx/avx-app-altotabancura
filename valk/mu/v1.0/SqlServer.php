<?php
class SqlServer{
    private $Host;
    private $Nombre;
    private $Usuario;
    private $Password;
    private $Port;
    private $Procedure;
    private $Variables;

    public function __construct($Capsule){
        $this->Host     = $Capsule['Host'];
        $this->Nombre   = $Capsule['Nombre'];
        $this->Usuario  = $Capsule['Usuario'];
        $this->Password = $Capsule['Password'];
        $this->Port     = $Capsule['Port'];
        $this->Procedure    = $Capsule['Procedure'];
        $this->Variables    = $Capsule['Variables'];
    }
    public function Ejecutar(){

        $Params = [];
        $Vars = '';
        foreach($this->Variables as $Key => $Value){
            $Vars .= '@' . $Key . '= ? ,';
            $Params[] = $Value;
        }
        $Vars = (($Vars != '') ? substr($Vars, 0, -1) :  '' );

        $ConnInfo = array( 'UID'=> $this->Usuario,
            'PWD'       =>  $this->Password,
            'Database'  =>  $this->Nombre,
            'CharacterSet' => 'UTF-8'
        );
        $Conexion = sqlsrv_connect($this->Host, $ConnInfo);
        //$Conexion = mssql_connect($this->Host, $this->Usuario, $this->Password);

        $terrors = '';
        $Call = ' exec '.$this->Procedure.' '.$Vars.' ';
        $Options = array('SendStreamParamsAtExec' => 0);
        $Stmt = sqlsrv_query($Conexion, $Call, $Params, $Options);

        if ($Stmt === false){

            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $terrors .= 'SQLSTATE: '.$error[ 'SQLSTATE'].'<br>';
                    $terrors .= 'code: '.$error[ 'code'].'<br>';
                    $terrors .= 'message: '.$error[ 'message'].'<br>';
                }
            }
	        return $terrors;
        }
        $Output = [];
        while ($Row = sqlsrv_fetch_array($Stmt, SQLSRV_FETCH_ASSOC)) {
            $Output[] = $Row;
        }
        sqlsrv_free_stmt($Stmt);
        sqlsrv_close($Conexion);


        return $Output;
    }
    /* TODO: encapsular error para enviar watchdog */
    public function Error(){

    }
}