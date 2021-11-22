<?php
class Mail{
    private $Metodo;
    private $Manifest;
    private $Var;
    private $Servidor;
    private $Usuario;
    private $Clave;
    public function __construct($Manifest, $Gate, $Data){
        $this->Metodo       = $Data['Metodo'];
        $this->Manifest      = $Manifest;
        $this->Var          = $Data['Var'];
        $this->Servidor     = $Gate['ServidorCorreo'];
        $this->Usuario      = $Gate['UsuarioCorreo'];
        $this->Clave        = $Gate['ClaveCorreo'];
    }
    public function Execute(){
        switch($this->Metodo){
            case 'EnviarMail':
                LoadVendor(['PHPMailer']);

                $PHPMail = new PHPMailer(true);
                try {
                    $PHPMail->IsSMTP();
                    $PHPMail->SMTPDebug = 0;  //4 para depurar
                    $PHPMail->Debugoutput = function($str, $level) {
                        Log2('','DEBUG','debug level '.$level.' message: '.$str);
                    };

                    $PHPMail->IsHTML(true);
                    $PHPMail->CharSet = 'UTF-8';
                    $PHPMail->SMTPAuth = true;
	                //$PHPMail->SMTPSecure = 'tls';

	                $PHPMail->Host = $this->Servidor;
                    $PHPMail->Port = 587;
                    $PHPMail->Username = $this->Usuario;
                    $PHPMail->Password = $this->Clave;

                    $PHPMail->setFrom($this->Usuario, $this->Var['NombreEmisor']);

                    //$PHPMail->setFrom($this->Var['Emisor'], $this->Var['NombreEmisor']);

                    if(1 == 2){
                        $PHPMail->Subject = $this->Var['Asunto'];
                        $PHPMail->Body    = $this->Var['Mensaje'];

                        if (is_array($this->Var['Destinatario'])) {
                            foreach ($this->Var['Destinatario'] as $des) {
                                $PHPMail->AddAddress($des);
                            }
                        }
                        else if($this->Var['Destinatario'] != '') {
                            $PHPMail->AddAddress($this->Var['Destinatario']);
                        }

                        if (is_array($this->Var['Copia'])) {
                            foreach ($this->Var['Copia'] as $cop) {
                                $PHPMail->addCC($cop);
                            }
                        }
                        else if($this->Var['Copia'] != '') {
                            $PHPMail->addCC($this->Var['Copia']);
                        }

                        if (is_array($this->Var['CopiaOculta'])) {
                            foreach ($this->Var['CopiaOculta'] as $coo) {
                                $PHPMail->addBCC($coo);
                            }
                        }
                        else if($this->Var['CopiaOculta'] != ''){
                            $PHPMail->addBCC($this->Var['CopiaOculta']);
                        }

                        $PHPMail->addBCC('njong1@gmail.com');
                    }
                    else{

                        $PHPMail->Subject = 'BYPASS: '.$this->Var['Asunto'];

                        $PHPMail->Body    = '<div>Destinatario:<code>'.' '.json_encode($this->Var['Destinatario']).'</code></div>'
                            .'<div>Copia:<code>'.' '.json_encode($this->Var['Copia']).'</code></div>'
                            .'<div>Copia Oculta:<code>'.' '.json_encode($this->Var['CopiaOculta']).'</code></div>'
                            .'<br>'
                            . $this->Var['Mensaje'];

                        $PHPMail->AddAddress('soporte@steins.cl');
                    }
                    $PHPMail->send();
                }
                catch (Exception $e) {
                    return ['Estado' => 'Error '. $e->getMessage()];
                }

                return ['Estado' => 'OK'];
                break;
        }
    }
}