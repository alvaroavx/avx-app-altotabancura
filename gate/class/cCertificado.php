<?php

LoadVendor(['FPDF']);

class Certificado extends FPDF
{
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';

    function Header() {
        $this->Cell(80);
        $logo = constant('Root_Base').constant('Root_Res').'brand/logo.png';
        $this->Cell( 40, 40, $this->Image($logo,20,15,80), 0, 0, 'L', false );
        $this->Ln(40);
        $this->SetFont('Times','BU',20);
        $this->Cell(0,10,utf8_decode('Certificado de Vacunación'),0,1,'C');
    }
    function Body($Nombre, $Rut, $Carrera, $Sede, $Universidad) {
        $this->Ln(15);
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $this->SetFont('Times');
        //$body = utf8_decode('<p>Procedimientos Clínicos Alto Tabancura, Rut 76.004.217-K, certifica que <b>'.$Nombre.'</b>, Rut <b>'.$Rut.'</b> estudiante de <b>'.$Universidad.'</b> de la carrera <b>'.$Carrera.'</b> fue inmunizado(a) con la(s) siguiente(s) vacuna(s):</p>');
        //$this->WriteHTML($body);
        $body = utf8_decode('Procedimientos Clínicos Alto Tabancura, Rut 76.004.217-K, certifica que '.$Nombre.', Rut '.$Rut.' fue inmunizado(a) con la(s) siguiente(s) vacuna(s):');
        $this->MultiCell(0, 7, $body, 0);
        $this->Ln(5);
    }
    function Vaccines($header, $data)
    {
        $this->SetLeftMargin(20);
        $this->SetFontSize(11);
        $this->SetFont('Times');
        $this->Cell(15, 8, '', 0);
        foreach ($header as $col) {
            $this->Cell(35, 8, $col, 1, 0, 'C');
        }
        $this->Ln();
        foreach ($data as $row) {
            $this->Cell(15, 8, '', 0);
            foreach($row as $col) {
                $this->Cell(35, 7, utf8_decode($col), 1, 0, 'C');
            }
            $this->Ln();
        }
        $this->Ln(5);
    }
    function Body2() {
        $this->SetLeftMargin(20);
        $this->SetFontSize(14);
        $this->SetRightMargin(20);
        $this->SetFont('Times');
        $body = utf8_decode('Se extiende el presente certificado a nombre del interesado para los fines que estime convenientes.');
        $this->MultiCell(0, 7, $body, 0);
        //$body = utf8_decode('<p>Se extiende el presente certificado a nombre del interesado para los fines que estime convenientes.</p>');
        //$this->WriteHTML($body);
        $this->Ln(5);
    }
    function Signature($Vacunas) {
        $signature = constant('Root_Base').constant('Root_Res').'signature2.png';
        if($Vacunas == 1) {
            $this->Cell( 0, 40, $this->Image($signature,70,140,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 2) {
            $this->Cell( 0, 40, $this->Image($signature,70,140,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 3) {
            $this->Cell( 0, 40, $this->Image($signature,70,150,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 4) {
            $this->Cell( 0, 40, $this->Image($signature,70,160,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 5) {
            $this->Cell( 0, 40, $this->Image($signature,70,170,70), 0, 0, 'C', false );
        }
        else if($Vacunas >= 6) {
            $this->Cell( 0, 40, $this->Image($signature,70,180,70), 0, 0, 'C', false );
        }
        /*else if($Vacunas == 7) {
            $this->Cell( 0, 40, $this->Image($signature,70,190,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 8) {
            $this->Cell( 0, 40, $this->Image($signature,70,200,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 9) {
            $this->Cell( 0, 40, $this->Image($signature,70,210,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 10) {
            $this->Cell( 0, 40, $this->Image($signature,70,220,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 11) {
            $this->Cell( 0, 40, $this->Image($signature,70,230,70), 0, 0, 'C', false );
        }
        else if($Vacunas == 12) {
            $this->Cell( 0, 40, $this->Image($signature,70,240,70), 0, 0, 'C', false );
        }
        else if($Vacunas > 12) {
            $this->Cell( 0, 40, $this->Image($signature,70,250,70), 0, 0, 'C', false );
        }*/
    }
    function Codigo($Codigo) {
        $this->Ln(25);
        $this->SetLeftMargin(0);
        $this->SetFont('Times');


        $this->Ln(20);
        $this->SetLeftMargin(20);
        $this->SetFontSize(10);
        $this->Cell(0,5,utf8_decode('Código de validación: '.$Codigo),0,1,'C');
        $this->Cell(0,5,utf8_decode('Fecha de emisión: '.FormatearFecha(date('m/d/Y', time()), 3)),0,1,'C');
        //$this->WriteHTML($body);

    }
    function PiePagina()
    {
        $this->SetLeftMargin(20);
        $this->SetFont('Times');
        //$this->Cell(0,10,utf8_decode('Certificado de Vacunación'),0,1,'C');
        $this->Cell(0,5, utf8_decode('Av. Tabancura 1515, Vitacura, Piso 1° Edificio Chañaral, Santiago, Chile.'),0,1,'C');
        $this->Cell(0,5, utf8_decode('Teléfonos: (02)2371 9062 - (02)22482064 - Email: vacu.altotabancura@gmail.com'),0,1,'C');
        $this->Cell(0,5, utf8_decode('www.vacunatorio.cl'),0,1,'C');
        /*
        $this->SetLeftMargin(80);
        $body = utf8_decode('<p><a href="http://www.vacunatorio.cl">www.vacunatorio.cl</a></p>');
        $this->WriteHTML($body);
        */
    }

    /* Certificado Tipo Tabla */
    function BodyTabla() {
        $this->Ln(15);
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $this->SetFont('Times');
        $body = utf8_decode('Procedimientos Clínicos Alto Tabancura, Rut 76.004.217-K, certifica que las siguientes personas fueron inmunizado(a) con la(s) siguiente(s) vacuna(s): ');
        $this->MultiCell(0, 7, $body, 0);
    }
    function VaccinesTabla()
    {
        // encabezado
        $this->Ln(5);
        $this->SetFontSize(9);
        $this->Cell(60, 8, 'Nombre', 1, 0, 'C');
        $this->Cell(20, 8, 'Rut', 1, 0, 'C');
        $this->Cell(18, 8, 'Dosis', 1, 0, 'C');
        $this->Cell(18, 8, 'Dosis', 1, 0, 'C');
        $this->Cell(18, 8, 'Dosis', 1, 0, 'C');
        $this->Cell(18, 8, 'Dosis', 1, 0, 'C');
        $this->Cell(18, 8, 'Dosis', 1, 0, 'C');
        $this->Ln(8);
    }
    function UsuariosTabla($Usuario) {
        $this->Cell(60, 8, utf8_decode($Usuario[0]['Nombre']), 1, 0, 'L');
        $this->Cell(20, 8, utf8_decode($Usuario[0]['Rut']), 1, 0, 'C');

        for($i=0;$i<5;$i++)
            isset($Usuario[$i]) ?
                $this->Cell(18, 8, isset($Usuario[$i]['Fecha']) ? utf8_encode($Usuario[$i]['Fecha']->format('d-m-Y')) : '-', 1, 0, 'C') :
                $this->Cell(18, 8, '-', 1, 0, 'C');

        $this->Ln(8);
    }
    function PiePaginaTabla() {
        $this->SetLeftMargin(20);
        $this->SetFont('Times');
        $this->SetFontSize(14);
        $this->SetRightMargin(20);
        $this->SetFont('Times');
        $this->Ln(10);
        $body = utf8_decode('Se extiende el presente certificado a nombre del interesado para los fines que estime convenientes.');
        $this->MultiCell(0, 7, $body, 0);
        //$body = utf8_decode('<p>Se extiende el presente certificado a nombre del interesado para los fines que estime convenientes.</p>');
        //$this->WriteHTML($body);
        $this->Ln(5);

        $signature = constant('Root_Base').constant('Root_Res').'signature2.png';
        $this->Cell( 0, 40, $this->Image($signature,70,null,70), 0, 0, 'C', false );
        $this->Ln(5);

        $this->SetLeftMargin(0);
        $this->SetRightMargin(0);
        $this->SetFontSize(12);
        //$this->Cell(0,10,utf8_decode('Certificado de Vacunación'),0,1,'C');
        $this->Cell(0,5, utf8_decode('Av. Tabancura 1515, Vitacura, Piso 1° Edificio Chañaral, Santiago, Chile.'),0,1,'C');
        $this->Cell(0,5, utf8_decode('Teléfonos: (02)2371 9062 - (02)22482064 - Email: vacu.altotabancura@gmail.com'),0,1,'C');
        $this->Cell(0,5, utf8_decode('www.vacunatorio.cl'),0,1,'C');
        /*
        $this->SetLeftMargin(80);
        $body = utf8_decode('<p><a href="http://www.vacunatorio.cl">www.vacunatorio.cl</a></p>');
        $this->WriteHTML($body);
        */
    }

    function SetStyle($tag, $enable)
    {
        // Modificar estilo y escoger la fuente correspondiente
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach(array('B', 'I', 'U') as $s)
        {
            if($this->$s>0)
                $style .= $s;
        }
        $this->SetFont('',$style);
    }
    function OpenTag($tag, $attr)
    {
        // Etiqueta de apertura
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,true);
        if($tag=='A')
            $this->HREF = $attr['HREF'];
        if($tag=='BR')
            $this->Ln(5);
    }
    function CloseTag($tag)
    {
        // Etiqueta de cierre
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF = '';
    }
    function PutLink($URL, $txt)
    {
        // Escribir un hiper-enlace
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }
    function WriteHTML($html)
    {
        // Intérprete de HTML
        $html = str_replace("\n",' ',$html);
        $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                // Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                else
                    $this->Write(5,$e);
            }
            else
            {
                // Etiqueta
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    // Extraer atributos
                    $a2 = explode(' ',$e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }
}