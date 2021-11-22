<?php
class Steins{
    private $CoreGate;
    private $Manifest;
    public function __construct($Manifest){
        $DB = constant('Entorno_DBServer');
        $this->Manifest = $Manifest;
        switch($DB){
            case 'PRO':
                $this->CoreGate['Tipo']    = 'mssql';
                $this->CoreGate['Host']    = 'sql7004.site4now.net';
                $this->CoreGate['Nombre']  = 'DB_A42699_altotabancura';
                $this->CoreGate['Usuario'] = 'DB_A42699_altotabancura_admin';
                $this->CoreGate['Clave']   = 'altotabancura01.';
                $this->CoreGate['Port']    = '';
                break;
            case 'LOCAL':
                $this->CoreGate['Tipo']    = 'mssql';
                $this->CoreGate['Host']    = 'localhost';
                $this->CoreGate['Nombre']  = 'DB_A42699_altotabancura';
                $this->CoreGate['Usuario'] = 'usr_local';
                $this->CoreGate['Clave']   = 'local01.';
                $this->CoreGate['Port']    = '';
                break;
        }
    }
    public function Gate($Gate, $DbServer, $MailServer){
    	return $this->CoreGate;
    }
    public function CoreQuery($Procedure, $Datos){
        LoadModulo($this->Manifest, ['Query']);
        $Capsula['Proc'] =  $Procedure;
        $Capsula['Var'] =  $Datos;
        $Query = new Query($this->Manifest, $this->CoreGate, $Capsula);
        return $Query->Execute();
    }
}
