<?php
class RedSocial{

    private $Manifest;

    private $Facebook;
    private $Google;

    private $Facebook_Id;
	private $Facebook_Secret;

	public function __construct($Manifest){
		$this->Manifest = $Manifest;
		$this->Facebook_Id = constant('Facebook_Id');
		$this->Facebook_Secret = constant('Facebook_Secret');
	}
	private function Conectar($Tipo){
		switch($Tipo){
			case 'Facebook':
				LoadVendor(['Facebook']);
				if($this->Facebook === null){
					try {
						$this->Facebook = new Facebook\Facebook([
							'app_id'        => $this->Facebook_Id,
							'app_secret'    => $this->Facebook_Secret,
							'default_graph_version' => 'v3.2',
						]);
					}
					catch (\Facebook\Exceptions\FacebookSDKException $e) {
						return $e->getMessage();
					}
				}

				break;
		}
	}
	public function LinkLogin(){
		$Link = [];
		$this->Conectar('Facebook');
		$helper = $this->Facebook->getRedirectLoginHelper();
		$permissions = ['public_profile,email'];
		
		$DatoUrl = [
			'Tipo' => 'Facebook',
			'Root' => $this->Manifest['Root']
		];
		
		$Link['Facebook'] = $helper->getLoginUrl(constant('Root_Base').'endpoint.php/?b='.urlencode(encrypt_decrypt('encrypt', json_encode($DatoUrl))), $permissions);
		//$Link['Facebook'] = $helper->getLoginUrl('http://localhost/spotted/valk/endpoint.php/', $permissions);


		//$this->Conectar('Google');
		//$Link['Google'] = $this->Google->createAuthUrl();
		$Link['Google'] = 'PENDIENTE';
		return $Link;
	}
	private function Login($Tipo){
		$Output = [];
		if(! $this->EstadoLogin($Tipo)){
			$Output = $this->SetToken($Tipo);
		}
		return $Output;
	}
	private function EstadoLogin($Tipo){
		$Estado = 0;
		if($this->Token($Tipo) != '0'){
			$Estado = 1;
		}
		return $Estado;
	}
	/*
	public function Permisos($tipo){
		$Permisos = [];
		switch($tipo){
			case 'facebook':
				$Permisos = $this->PermisosFacebook();
				break;

			case 'google':

				break;
		}
		return $Permisos;
	}*/
	public function Datos($Tipo){
		$this->Login($Tipo);
		switch($Tipo){
			case 'Facebook':
				$DatosRaw = $this->DatosFacebook();

				$Datos['Correo'] = $DatosRaw['email'];
				$Datos['Nombre1'] =  $DatosRaw['first_name'];
				$Datos['Apellido1'] = $DatosRaw['last_name'];
				$Datos['Foto'] = $DatosRaw['picture']['data']['url'];
				break;
				/*
			case 'google':
				$DatosRaw = $this->DatosGoogle();
				$Datos['correo'] = $DatosRaw['token']['email'];
				$Datos['nombre1'] = $DatosRaw['token']['given_name'];
				$Datos['apellido1'] = $DatosRaw['token']['family_name'];
				$Datos['foto'] = $DatosRaw['token']['picture'];
				break;*/
		}
		return $Datos;
	}
	private function Token($Tipo){
		switch($Tipo) {
			case 'Facebook':
				$Token = Sesion('token_facebook');
				break;
			default:
				$Token = '0';
		}
		return $Token;
	}
	private function SetToken($Tipo){
		$GetToken = $this->GetToken($Tipo);
		if(isset($GetToken['Token'])){
			switch($Tipo) {
				case 'Facebook':
					SetSesion('token_facebook', $GetToken['Token']);
					break;
			}
		}
		return $GetToken;
	}
	private function GetToken($Tipo){
		$Output = [];
		$Output['Error'] = '';
		switch($Tipo){
			case 'Facebook':

				$this->Conectar('Facebook');
				$helper = $this->Facebook->getRedirectLoginHelper();
				if (isset($_GET['state'])) {
					$helper->getPersistentDataHandler()->set('state', $_GET['state']);
				}
				try {
					$accessToken = $helper->getAccessToken();
				}
				catch(Facebook\Exceptions\FacebookResponseException $e) {
					$Output['Error'] .= 'Graph returned an error: ' . $e->getMessage();
				}
				catch(Facebook\Exceptions\FacebookSDKException $e) {
					$Output['Error'] .= 'Facebook SDK returned an error: ' . $e->getMessage();
				}

				if (! isset($accessToken) ) {
					if ($helper->getError()) {
						$Output['Error'] .= "Error: " . $helper->getError() . PHP_EOL;
						$Output['Error'] .= "Error Code: " . $helper->getErrorCode() . PHP_EOL;
						$Output['Error'] .= "Error Reason: " . $helper->getErrorReason() . PHP_EOL;
						$Output['Error'] .= "Error Description: " . $helper->getErrorDescription() . PHP_EOL;
					} else {
						$Output['Error'] .= 'Bad request';
					}
				}
				else {
					$oAuth2Client = $this->Facebook->getOAuth2Client();
					$tokenMetadata = $oAuth2Client->debugToken($accessToken);
					$tokenMetadata->validateAppId($this->Facebook_Id);

					// If you know the user ID this access token belongs to, you can validate it here
					//$tokenMetadata->validateUserId('123');
					$tokenMetadata->validateExpiration();
					if (! $accessToken->isLongLived()) {
						try {
							$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
						}
						catch (Facebook\Exceptions\FacebookSDKException $e) {
							$Output['Error'] .= "<p>Error getting long-lived access token: " . $e->getMessage() . PHP_EOL;
						}
					}
					$Output['Token'] = (string) $accessToken;
				}
				break;
/*
			case 'google':
				$this->Conectar($tipo);
				$Token = $this->Google->fetchAccessTokenWithAuthCode($extras['code']);
				break;*/

		}
		return $Output;
	}

	/*
	private function PermisosFacebook(){
		$Permisos = [];
		$this->Conectar('facebook');
		$this->Facebook->setDefaultAccessToken($this->Token('facebook'));
		$requestPermisos = $this->Facebook->request('GET', '/me/permissions');
		$batch = ['permisos' => $requestPermisos];
		try {
			$responses = $this->Facebook->sendBatchRequest($batch);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			if($this->Funciones->DebugMode()) {
				echo 'Graph returned an error: ' . $e->getMessage();
			}
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			if($this->Funciones->DebugMode()) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
			}
			exit;
		}
		$raw = json_decode($responses['permisos']->getBody(),1);
		foreach ($raw['data'] as $pe){
			$Permisos[$pe['permission']] = $pe['status'];
		}
		return $Permisos;
	}*/

	private function DatosFacebook(){

		$this->Conectar('Facebook');
		$this->Facebook->setDefaultAccessToken($this->Token('Facebook'));
		$requestDatos = $this->Facebook->request('GET', '/me?fields=id,first_name,last_name,middle_name,picture,name,short_name,email');
		$batch = ['datos' => $requestDatos];
		try {
			$responses = $this->Facebook->sendBatchRequest($batch);
			$raw = json_decode($responses['datos']->getBody(),1);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			$raw = 'Graph returned an error: ' . $e->getMessage();
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			$raw = 'Facebook SDK returned an error: ' . $e->getMessage();
		}
		return $raw;
	}
	/*

	private function DatosGoogle(){
		$datos = [];
		$this->Conectar('google');
		$service = new Google_Service_PeopleService($this->Google);
		$optParams = array(
			'resourceNames' => 'people/me',
			'personFields' => 'names,phoneNumbers'
		);
		$raw = $service->people->getBatchGet($optParams);

		$datos['names'] = $raw['responses'][0]['person']['names'];
		$datos['phoneNumbers'] = $raw['responses'][0]['person']['phoneNumbers'];
		$datos['token'] = $token_data = $this->Google->verifyIdToken();



		return $datos;
	}
*/





}