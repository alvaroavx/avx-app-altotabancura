<?php
class Login{
    private $Manifest;
	private $Gate;
    private $Metodo;
    private $Var;
    public function __construct($Manifest, $Gate, $Data){
        $this->Manifest     = $Manifest;
	    $this->Gate         = $Gate;
        $this->Metodo       = $Data['Metodo'];
        $this->Var          = $Data['Var'];
    }
    public function Execute(){
        $Return = [];
        $Metodo = $this->Metodo;
	    if (method_exists($this, $Metodo)){
	        LoadModulo($this->Manifest, ['Steins','RedSocial','Morty']);
	        $Return = $this->$Metodo();
        }
	    return $Return;
    }
    private function Autentificar(){
        $Steins = new Steins($this->Manifest);
        $Datos['Usuario'] = $this->Var['Usuario'];
        $Datos['Clave']   = $this->Var['Clave'];
        $Procedure = 'spRec_Usuario_Autentificar';
	    $Resultado = $Steins->CoreQuery($Procedure,$Datos);
        return $Resultado;
    }
    private function MultiCuenta(){
        $Steins = new Steins($this->Manifest);
        $Datos['Usuario'] = $this->Var['Usuario'];
        $Datos['IdOrigen']  = $this->Var['IdOrigen'];
	    $Datos['Clave']  = $this->Var['Clave'];
        $Procedure = 'spRec_Usuario_MultiCuenta';
        return $Steins->CoreQuery($Procedure,$Datos)[0];
    }
    private function DatosUsuario(){
        $Steins = new Steins($this->Manifest);
        $Datos['IdUsuario'] = $this->Var['IdUsuario'];
        $Procedure = 'spRec_Usuario_Datos';
        $DatosRaw = $Steins->CoreQuery($Procedure,$Datos);
        $RC = [];
        $RC['IdUsuario'] =  $this->Var['IdUsuario'];
        foreach($DatosRaw as $dr){
            $RC[$dr['TipoMeta']] = $dr['Meta'];
        }
        $RC['NombreCorto'] = IsNull($RC['Nombre1'], '').IsNull(' '.$RC['Apellido1'], '');
        $RC['NombreLargo'] = IsNull($RC['Nombre1'], '').IsNull(' '.$RC['Nombre2'], '').IsNull(' '.$RC['Apellido1'], '').IsNull(' '.$RC['Apellido2'], '');
        return $RC;
    }
	private function CambiarClave(){
		$Steins = new Steins($this->Manifest);
		$Datos['IdUsuario'] = $this->Var['IdUsuario'];
		$Datos['Usuario'] = $this->Var['Usuario'];
		$Datos['Pass']      = $this->Var['Pass'];
		$Datos['NewPass']   = $this->Var['NewPass'];
		$Procedure = 'spMod_Usuario_Clave';
		return $Steins->CoreQuery($Procedure,$Datos);
	}
	private function ValidarToken(){
		$Steins = new Steins($this->Manifest);
		$Datos['Token'] = $this->Var['Token'];
		$Procedure = 'spRec_Usuario_ValidarToken';
		return $Steins->CoreQuery($Procedure,$Datos);
	}
	private function Recovery(){
		$Steins = new Steins($this->Manifest);
		$Datos['Usuario'] = $this->Var['Usuario'];
		$Procedure = 'spRec_Usuario_Recuperar';
		return $Steins->CoreQuery($Procedure,$Datos);
	}
    private function UltimosUsuarios(){
        $Steins = new Steins($this->Manifest);
        $Datos = [];
        $Procedure = 'spRec_Usuario_Ultimos';
        return $Steins->CoreQuery($Procedure,$Datos);
    }
    private function ModificarUsuario(){
	    $Steins = new Steins($this->Manifest);
	    $Datos['IdUsuario']   = $this->Var['IdUsuario'];
	    $Datos['Base']      = $this->Var['Base'];
	    $Procedure = 'spMod_Usuario_Usuario';
	    return $Steins->CoreQuery($Procedure,$Datos);
    }
	private function DatosRedSocial(){
		$RedSocial = new RedSocial($this->Manifest );
		return $RedSocial->Datos($this->Var['Tipo']);
	}
    private function BuscarUsuarios(){
        $Steins = new Steins($this->Manifest);
        $Datos['Busqueda'] = $this->Var['Busqueda'];
        $Procedure = 'spRec_Usuario_Buscador';
        return $Steins->CoreQuery($Procedure,$Datos);
    }
    private function LinkLogin(){
        $RedSocial = new RedSocial($this->Manifest);
        return $RedSocial->LinkLogin();
    }
}