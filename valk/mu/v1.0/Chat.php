<?php
class Chat{
    private $Manifest;
    private $Metodo;
    private $Var;
	public function __construct($Manifest, $Gate, $Data){
		$this->Manifest     = $Manifest;
		$this->Metodo       = $Data['Metodo'];
		$this->Var          = $Data['Var'];
	}
    public function Execute(){
        LoadModulo($this->Manifest, ['Steins']);
        $Steins = new Steins($this->Manifest);

        $Datos = [];
        $Procedure = '';
        switch($this->Metodo){
            case 'Contactos':
                $Datos['IdUsuario'] = $this->Var['IdUsuario'];
                $Procedure = 'spRec_Usuario_Contactos';
                break;

            case 'MensajesChat':
                $Datos['IdUsuarioOrigen'] = $this->Var['IdUsuarioOrigen'];
                $Datos['IdUsuarioDestino'] = $this->Var['IdUsuarioDestino'];
                $Procedure = 'spRec_Chat_Chat';
                break;

            case 'AddMensaje':
                $Datos['IdUsuarioOrigen'] = $this->Var['IdUsuarioOrigen'];
                $Datos['IdUsuarioDestino'] = $this->Var['IdUsuarioDestino'];
                $Datos['Mensaje']      = $this->Var['Mensaje'];
                $Procedure = 'spIns_Chat_Chat';
                break;
        }
        if($Procedure !== ''){
            return $Steins->CoreQuery($Procedure,$Datos);
        }
        else{
            return ['Estado' =>'Error Chat'];
        }
    }


}