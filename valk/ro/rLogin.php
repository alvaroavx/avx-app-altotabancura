<?php
/**LOGIN**/
function LoadLogin($data){
	$Wiss = new Wiss();
	if(constant('Modulo_Facebook') || constant('Modulo_Google')){
		$LinkLogin = $Wiss->LinkLogin();
	}
	echo '<form id="form_login" class="loginrow">'
		.'<label for="user_login" class="label_login selectDisable">Usuario:</label>'
		.'<input type="text" id="user_login" name="user_login" class="input_login" autocomplete="username" placeholder="Ingrese su Rut sin puntos ni dígito verificador o su usuario">'
		.'<label for="pass_login" class="label_login selectDisable">Contraseña:</label>'
		/*.'<div class="recover_login selectDisable" onclick="$.LoadCoreRecoverPass();">Recuperar Contraseña</div>'*/
		.'<input type="password" id="pass_login" name="pass_login" class="input_login" autocomplete="current-password" placeholder="Ingrese los primeros 4 dígitos de su Rut o su contraseña">'
		.'<div id="error_login"></div>'
		.'<input type="submit" id="submit_login" name="submit_login" class="submit_login boton" value="Entrar">'
		/*.'<div id="register_login" class="submit_login boton" onclick="$.LoadCoreRegistro();">Registrarse</div>'*/
		.'';
	if(constant('Modulo_Facebook')){
		echo '<div class="login_redes login_facebook boton" onclick="'.Redirect(htmlspecialchars($LinkLogin['Facebook'])).'">Ingresa con Facebook</div>';
	}
	if(constant('Modulo_Google')){
		echo  '<div class="login_redes login_google boton" onclick="'.Redirect(htmlspecialchars($LinkLogin['Google'])).'">Ingresa con Google</div>';
	}
	echo '</form>';
	echo '<script>
        $("#form_login").validate({
            rules: {
                user_login:{ required: true},
                pass_login: {required: true}
            },
            messages: {
                user_login: { required: "Debes ingresar tu usuario."},
                pass_login: { required: "Debes ingresar tu contraseña."}
            },
            errorPlacement: function(error, element){
                error.appendTo($("#error_login"));
            },
            submitHandler: function(){
				$.Autentificar();
            }
        });
    </script>';
}
function Autentificar($data){
	$Wiss = new Wiss();
	$RedSocial = $data['redsocial'];
	/* TODO: incluir validacion */
	if($RedSocial == 1){
		$ClaveCode = $data['pass'];
        $ValidaUsuario = $Wiss->ValidaLogin($data['usuario'], $ClaveCode);
	}
	else{
		$ValidaUsuario = $Wiss->ValidaMultiCuenta($data['usuario'], $RedSocial);
	}
	switch($ValidaUsuario['IdUsuario']){
		case 0:
			echo '<label>Usuario o contraseña incorrecta.</label>';
			switch($ValidaUsuario['Fails']){
				case 1:
				case 2:
				case 3:
					echo '<label>Quedan '.(4 - $ValidaUsuario['Fails']).' intentos.</label>';
					break;
				case 4:
					echo '<label>Se superaron los intentos de login, por seguridad la cuenta se ha bloqueado.</label>';
					break;
			}
			break;
		default:
			IniciarSesion($ValidaUsuario['IdUsuario'], $ValidaUsuario['Username'], $ValidaUsuario['IdTipoUsuario'], $ValidaUsuario['IdUniversidad']);
			break;
	}
}
function IniciarSesion($IdUsuario, $Usuario, $IdTipoUsuario, $IdUniversidad){
	SetSesion('idusuario', $IdUsuario);
	SetSesion('usuario', $Usuario);
	switch ($IdTipoUsuario) {
        case 2:
            SetSesion('perfil', 'docente');
            break;
        case 3:
            SetSesion('perfil', 'admin');
            break;
        default:
        case 1:
            SetSesion('perfil', 'alumno');
            break;
            break;
    }
	SetSesion('usuario', $Usuario);
	SetSesion('universidad', $IdUniversidad);

	SetSesion('temp_idusuario');
	SetSesion('temp_usuario');
	SetSesion('temp_token');

	$Wiss = new Wiss();
	$Permisos = $Wiss->PermisosUsuario($IdUsuario);
	$Admin = $Permisos[0]['Estado'];
	if($Admin){
		SetSesion('admin', $Admin);
	}
	//echo '<script>$.LoadCore();</script>';
	echo '<script>$.LoadMidBlock(2);</script>';
}
/**REGISTRO**/
function LoadRegistro($data){
	$Wiss = new Wiss();
	if(constant('Modulo_Facebook') || constant('Modulo_Google')){
		$LinkLogin = $Wiss->LinkLogin();
	}
	echo  '<form id="form_registro" class="loginrow">'
		.'<div class="title_login selectDisable">Registro</div>'
		.'<div class="description_login selectDisable">Ingresa los siguientes datos para solicitar una cuenta, recuerda leer los Terminos y condiciones.</div>'

		.'<label for="user_login" class="label_login selectDisable">Correo:</label>'
		.'<input type="email" id="user_login" name="user_login" class="input_login" autocomplete="username" placeholder="ejemplo@ejemplo.cl">'

		.'<label for="name_login" class="label_login selectDisable">Nombre:</label>'
		.'<input type="text" id="name_login" name="name_login" class="input_login" autocomplete="given-name" placeholder="Primer nombre">'

		.'<label for="surname_login" class="label_login selectDisable">Apellido:</label>'
		.'<input type="text" id="surname_login" name="surname_login" class="input_login" autocomplete="family-name" placeholder="Primer apellido">'

		.'<label for="pass_login" class="label_login selectDisable">Escoge tu contraseña:</label>'
		.'<input type="password" id="pass_login" name="pass_login" class="input_login" autocomplete="new-password" placeholder="*********">'

		.'<label for="repass_login" class="label_login selectDisable">Repite tu contraseña:</label>'
		.'<input type="password" id="repass_login" name="repass_login" class="input_login" autocomplete="new-password" placeholder="*********">'

		.'<label class="label_login selectDisable">'
		.'<input type="checkbox" id="condition_login" name="condition_login" class="checkbox_login">'
		.'Acepto los <a onclick="$.TerminosCondiciones();">Terminos y condiciones.</a></label>'


		.'<div id="error_login"></div>'
		.'<input type="submit" id="submit_login" name="submit_login" class="submit_login boton" value="Registrarse">'
		.'<div class="submit_login boton" onclick="$.LoadCore();">Volver</div>';

	if(constant('Modulo_Facebook')){
		echo '<div class="login_redes login_facebook boton" onclick="'.Redirect(htmlspecialchars($LinkLogin['Facebook'])).'">Registrate con Facebook</div>';
	}
	if(constant('Modulo_Google')){
		echo '<div class="login_redes login_google boton" onclick="'.Redirect(htmlspecialchars($LinkLogin['Google'])).'">Registrate con Google</div>';
	}

	echo '</form>';
	echo '<script>
        $("#form_registro").validate({
            rules: {
                user_login:{ required: true, email: true},
                name_login:{ required: true},
                surname_login:{ required: true},
                pass_login: {required: true},
                repass_login: {required: true,equalTo: "#pass_login"},
                condition_login: {required:true}
            },
            messages: {
                user_login: { required: "Debes ingresar tu correo.", email:"Debes ingresar un correo valido"},
				name_login: { required: "Debes ingresar tu nombre."},
				surname_login: { required: "Debes ingresar tu apellido."},
                pass_login: { required: "Debes ingresar tu contraseña."},
                repass_login: {required: "Debes reingresar tu contraseña.",equalTo: "Las contraseñas no coinciden."},
                condition_login: {required:"Recuerda aceptar los Terminos y Condiciones"}

            },
            errorPlacement: function(error, element){
                error.appendTo($("#error_login"));
            },
            submitHandler: function(){
				$.Registro();
            }
        });
    </script>';
}
function Registrar($data){
	$Wiss = new Wiss();
	$Usuario    = $data['usuario'];
	$Clave      = $data['pass'];
	$Nombre    = $data['nombre'];
	$Apellido  = $data['apellido'];
	$RedSocial  = $data['redsocial'];
}
/**RECOVER**/
function LoadRecoverPass($data){
	echo '<form id="form_recover" class="loginrow">'
		.'<div class="title_login selectDisable">Recuperar contraseña</div>'
		.'<div class="description_login selectDisable">Ingresa tu correo y te enviaremos las instrucciones para recuperar tu contraseña.</div>'
		.'<label for="user_login" class="label_login selectDisable">Correo:</label>'
		.'<input type="email" id="user_login" name="user_login" class="input_login" autocomplete="email" placeholder="ejemplo@ejemplo.cl">'
		.'<div id="error_login"></div>'
		.'<input type="submit" id="submit_login" name="submit_login" class="submit_login boton" value="Recuperar">'
		.'<div class="submit_login boton" onclick="$.LoadCore();">Volver</div>'
		.'';
	echo '</form>';
	echo '<script>
        $("#form_recover").validate({
            rules: {
                user_login:{ required: true, email: true}
            },
            messages: {
                user_login: { required: "Debes ingresar tu correo.", email:"Debes ingresar un correo valido."}
            },
            errorPlacement: function(error, element){
                error.appendTo($("#error_login"));
            },
            submitHandler: function(){
				$.RecoverPass();
            }
        });
    </script>';
}
function RecoverPass($data){
	$Wiss = new Wiss();
	$Usuario = $data['usuario'];
	$Recovery = $Wiss->Recovery($Usuario);
	if($Recovery['IdUsuario']){
		$DatosUsuario = $Wiss->DatosUsuario($Recovery['IdUsuario']);
		$Wiss->EnviarMail('soporte@steins.cl',
			'Soporte Steins',
			$Usuario,
			'Recuperar contraseña Steins',
			'<div style="    border-top: 2px solid grey;
    padding: 10px 20px;
    border-bottom: 2px solid grey;">'
			.'<h1>Has solicitado una recuperación de contraseña</h1><br>'
			.'<h2>Hola '.$DatosUsuario['Nombre1'].',</h2>'
			.'<p>Para recuperar tu contraseña utiliza el boton <b>Recuperar</b> que se encuentra mas abajo,<br>en caso de que no hayas solicitado la recuperación de la contraseña, puedes ignorar este correo.'
			.'</p>'
			.'<br>'
			.'<a style="background: black;
    margin: 10px 0;
    cursor: pointer;
    font-size: 14px;
    font-family: Helvetica,Arial,sans-serif;
    color: #ffffff;
    text-decoration: none;
    border-radius: 4px;
    padding: 12px 14px;
    display: inline-block;
    text-transform: uppercase;" href="'.constant('Root_Base').'recover/'. $Recovery['Token'].'">Recuperar contraseña</a>'
			.'<br><br><h3>Soporte Steins</h3>'
			.'</div>'
		);
	}
}
function WaitRecover($data){
	echo '<div class="loginrow">'
		.'<div class="title_login selectDisable">Recuperar contraseña</div>'
		.'<div class="description_login selectDisable">Hemos enviado las instrucciones para que puedas recuperar tu contraseña al correo indicado, puede tardar unos minutos.</div>'
		.'<div class="submit_login boton" onclick="$.LoadCore();">Volver</div>'
		.'</div>';
}
/**RECOVER PUSH**/
function LoadRecoverPush($data){
	$Wiss = new Wiss();
	$Token = $data['idload'];
	$ValidaToken = $Wiss->ValidarToken($Token);
	if(isset($ValidaToken['IdUsuario'])){
		SetSesion('temp_idusuario',$ValidaToken['IdUsuario']);
		SetSesion('temp_usuario',$ValidaToken['Usuario']);
		SetSesion('temp_token',$Token);
		echo '<form id="form_recoverpush" class="loginrow">'

			.'<div class="title_login">Recuperación de contraseña</div>'
			.'<div class="description_login">Ingresa la nueva contraseña.</div>'

			.'<input type="text" class="hidden_login" autocomplete="username" value="'.$ValidaToken['Usuario'].'">'

			.'<label class="label_login" for="pass_login">Contraseña nueva</label>'
			.'<input id="pass_login" name="pass_login" class="input_login" autocomplete="new-password" type="password" placeholder="* * * * * * *">'


			.'<label class="label_login" for="repass_login">Repetir contraseña nueva</label>'
			.'<input id="repass_login" name="repass_login" class="input_login" autocomplete="new-password" type="password" placeholder="* * * * * * *">'

			.'<div id="error_login"></div>'
			.'<input type="submit" id="submit_login" name="submit_login" class="submit_login boton" value="Cambiar">'
			.'</form>';
		echo '<script>
        $("#form_recoverpush").validate({
            rules: {
                pass_login:{ required: true},
                repass_login: {required: true, equalTo: "#pass_login"}
            },
            messages: {
                pass_login: { required: "Debes ingresar la nueva contraseña."},
                repass_login: { required: "Repite la nueva contraseña.", equalTo: "Las contraseñas deben coincidir."}
            },
            errorPlacement: function(error, element){
                error.appendTo($("#error_login"));
            },
            submitHandler: function(){
				$.RecoverPassPush();
            }
        });
    </script>';
	}
	else{
		echo '<div class="loginrow">'
			.'<div class="title_login">Token no valido</div>'
			.'<div class="description_login">Verifique utilizar la url correcta.</div>'
			.'</div>';
	}
}
function RecoverPassPush($data){
	$ClaveCode = md5($data['pass']);
	$Wiss = new Wiss();
	$CambioClave = $Wiss->CambiarClave(Sesion('temp_idusuario'), Sesion('temp_usuario'),  Sesion('temp_token'), $ClaveCode);
	if($CambioClave['Estado']){
		IniciarSesion(Sesion('temp_idusuario'), Sesion('temp_usuario'));
	}
	else{
		echo '<label>Error al reactivar la cuenta, favor contactar a <a href="mailto: contacto@steins.cl">contacto@steins.cl</a></label>';
	}
}
/**ELIMINA SESION**/
function DeleteSesion($data){
	echo RemoveSesion();
}
function HeartBeat($data){
	if(! ValidaSesion()){
		echo RemoveSesion();
	}
}