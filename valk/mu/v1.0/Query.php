<?php
class Query{
    private $Manifest;
    private $Gate;
    private $Procedure = '';
    private $Variables = '';
    public function __construct($Manifest, $Gate, $Data){
        $this->Manifest      = $Manifest;
        $this->Gate         = $Gate;
        $this->Procedure    = $Data['Proc'];
        $this->Variables    = $Data['Var'];
    }
    public function Execute(){
        $DB = '';
        $Capsule = [
            'Host'      => $this->Gate['Host'],
            'Nombre'    => $this->Gate['Nombre'],
            'Usuario'   => $this->Gate['Usuario'],
            'Password'  => $this->Gate['Clave'],
            'Port'      => $this->Gate['Port'],
            'Procedure' => $this->Procedure,
            'Variables' => $this->Variables
        ];
        switch ($this->Gate['Tipo']) {
            case 'mssql':
                LoadModulo($this->Manifest, ['SqlServer']);
                $DB = new SqlServer($Capsule);
                break;
            case 'postgresql':
                LoadModulo($this->Manifest, ['PostgreSql']);
                $DB = new PostgreSql($Capsule);
                break;
            case 'mysql':
                break;
        }
        if($DB){
            $Output = $DB->Ejecutar();
        }
        else{
            $Output = ['Estado' =>'Error conexion query'];
        }
        return $Output;
    }

}