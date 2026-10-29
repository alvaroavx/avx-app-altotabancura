<?php
class PostgreSql{
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

        //MODO PDO
        if(1==1){
            echo (constant('Debug_Steins'))? ' --> MODO PDO ' : '';

            $Params = [];
            $Vars = '(';
            foreach($this->Variables as $Key => $Value){
                $Vars .= '?'.',';
                $Params[] = $Value;
            }
            $Vars = ((count($Params) > 0)? substr($Vars, 0, -1).')' : '()');

            try {
                $ConnString = 'pgsql:'
                    .'dbname='.$this->Nombre.';'
                    .'host='.$this->Host.';'
                    .'port='.$this->Port.';'
                    .'user='.$this->Usuario.';'
                    .'password='.$this->Password
                    .'';

                echo (constant('Debug_Steins'))? ' --> ConnString' . $ConnString : '';

                $pdo = new PDO($ConnString);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                if ($pdo) {
                    $Call = 'SELECT * FROM '
                        . $this->Procedure
                        . $Vars
                        . ';';
                    $Stmt = $pdo->prepare($Call);
                    $Stmt->execute($Params);
                    $Output = $Stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null;
                }
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        //MODO PG
        else {
            echo (constant('Debug_Steins'))? ' --> MODO PG ' : '';

            $Params = [];
            $Vars = '(';
            $VarCounter = 1;
            foreach($this->Variables as $Key => $Value){
                $Vars .= '$' . $VarCounter . ',';
                $Params[] = $Value;
                $VarCounter++;
            }
            $Vars = ((count($Params) > 0)? substr($Vars, 0, -1).')' : '()');

            $ConnString = 'host='.$this->Host
                .' port='.$this->Port
                .' dbname='.$this->Nombre
                .' user='.$this->Usuario
                .' password='.$this->Password;

            $Conexion = pg_connect($ConnString);
            if ($Conexion) {
                $Call = 'SELECT * FROM '
                    . $this->Procedure
                    . $Vars
                    . ';';
                $Stmt = pg_query_params($Conexion, $Call, $Params);
                $Output = pg_fetch_all($Stmt, PGSQL_ASSOC);
                pg_free_result($Stmt);
                pg_close($Conexion);
            }
        }
        return (isset($Output))? $Output : ['VACIO'];
    }
    public function Error(){

    }

}