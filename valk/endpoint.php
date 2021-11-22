<?php
$Datos = json_decode($Codigo,1);
switch ($Datos['Tipo']){
	case 'Facebook':
		if(isset($Datos['IdUsuario'])){
			SetSesion('idusuario', $Datos['IdUsuario']);
			SetSesion('usuario', $Datos['Usuario']);
		}
		break;
}
LocationRoot();

/* codigo traido de steins */
if (!$_GET == []) {
    require_once('loader.php');

    $DatosGet = json_decode(encrypt_decrypt('decrypt', urldecode($_GET['b'])), 1);
    $Tipo = $DatosGet['Tipo'];
    $Root = $DatosGet['Root'];

    switch ($Tipo) {
        case 'Facebook':
            $ManifestProxy['Version']['Valk'] = constant('Version_Default');
            LoadModulo($ManifestProxy, ['RedSocial', 'Login']);

            $Redes = ['Steins' => 1, 'Google' => '2', 'Facebook' => '3'];
            $RedSocial = new RedSocial($ManifestProxy);

            $DatosUsuario = $RedSocial->Datos($Tipo);

            $Usuario = $DatosUsuario['Correo'];

            $Login = new Login($ManifestProxy, '', ['Metodo' => 'MultiCuenta', 'Var' => ['Usuario' => $Usuario, 'IdOrigen' => $Redes[$Tipo], 'Clave' => null]]);
            $DatosMultiCuenta = $Login->Execute();
            $IdUsuario = $DatosMultiCuenta['IdUsuario'];

            if ($DatosMultiCuenta['Nueva']) {
                $BaseCambio[] = ['idtipo' => 1, 'valor' => $DatosUsuario['Nombre1']];
                $BaseCambio[] = ['idtipo' => 3, 'valor' => $DatosUsuario['Apellido1']];
                $Login = new Login($ManifestProxy, '', ['Metodo' => 'ModificarUsuario', 'Var' => ['IdUsuario' => $IdUsuario, 'Base' => ArrayToXml($BaseCambio)]]);
                $UpdateDatos = $Login->Execute();
            }

            if ($IdUsuario != 0) {
                $Datos = [
                    'Tipo' => $Tipo,
                    'IdUsuario' => $IdUsuario,
                    'Usuario' => $Usuario,
                ];
                $Url_E = urlencode(encrypt_decrypt('encrypt', json_encode($Datos)));

                header('Location:' . $Root . 'e/' . $Url_E);
            }
    }
}