<?php

/**
 * Se genera un certificado de forma individual
 * @param $IdUsuario
 * @param $Certificado
 * @param $NombreArchivo
 */
function CrearCertificado($IdUsuario, $Certificado, $NombreArchivo) {

    /* Obtencion datos usuario */
    $U = new Usuario();
    $Usuario = $U->GetById($IdUsuario)[0];
    $Vacunas = $U->GetVaccines($IdUsuario);

    /* Vacunas */
    if (!empty($Vacunas)) {
        /* Datos Certificado */
        $Codigo = hash('md5', $Usuario['Rut']);

        if($NombreArchivo=="") {
            $NombreArchivo = str_replace(' ', '.', $Usuario['Rut'] . ' ' . $Usuario['Nombres']);
        }
        $U->IngresarCertificado($Codigo, $Usuario['IdUsuario'], $NombreArchivo);

        /* Generación Certificado */
        $Certificado->SetFont('Arial', '', 14);
        $Certificado->AddPage();
        $Certificado->Body($Usuario['Nombres'] . ' ' . $Usuario['Apellidos'], $Usuario['Rut'], $Usuario['Carrera'], $Usuario['Sede'], $Usuario['Universidad']);

        $Header = array('Vacuna', 'Lote', 'Dosis', 'Fecha');
        $Data = array();
        foreach ($Vacunas as $V) {
            if ($V['Vacuna'] == 'SIN INFORMACIÓN') {
                $V['Vacuna'] = 'SIN INFO';
            }
            if ($V['Lote'] == '') {
                $V['Lote'] = 'SIN INFO';
            }
            $FechaVac = CheckFecha($V['Fecha']);
            array_push($Data, array($V['Vacuna'], $V['Lote'], $V['Dosis'], $FechaVac));
        }
        $Certificado->Vaccines($Header, $Data);

        $Certificado->Body2();
        $Certificado->Signature(count($Vacunas));
        $Certificado->Codigo($Codigo);
        $Certificado->PiePagina();
    }
    else {

    }
}

/**
 * Se envia a generar uno o muchos certificados
 * @param $data
 */
function GenerarCertificado($data) {
    if($data['multiple']) {
        /* Generación PDF */
        $Certificado = new Certificado();
        $fecha = new DateTime();
        $NombreArchivo = 'consolidado'.$fecha->getTimestamp().'.pdf';
        $URL = constant('Root_Base') . constant('Prefix_Out') . '/' . $NombreArchivo;
        $Usuarios = explode(',', $data['usuarios']);
        foreach ($Usuarios as $IdUsuario) {
            CrearCertificado($IdUsuario, $Certificado, $NombreArchivo);
        }
        $Certificado->Output('F', constant('Root_Fisica') . constant('Root_Out') . $NombreArchivo, true);
        echo $URL;
    }
    else {
        /* Generación PDF */
        $Certificado = new Certificado();
        $NombreArchivo = str_replace(' ', '.', 'certificado'.$data['idusuario']);
        $URL = constant('Root_Base') . constant('Prefix_Out') . '/' . $NombreArchivo . '.pdf';
        CrearCertificado($data['idusuario'], $Certificado, $NombreArchivo);
        $Certificado->Output('F', constant('Root_Fisica') . constant('Root_Out') . $NombreArchivo . '.pdf', true);
        echo $URL;
    }
}

function CrearCertificadoTabla($Usuarios, $Certificado, $NombreArchivo) {

    /* Generación Certificado */
    $Certificado->SetFont('Arial', '', 14);
    $Certificado->AddPage();
    $Certificado->BodyTabla();
    $Certificado->VaccinesTabla();

    /* Obtencion datos usuario */
    foreach ($Usuarios as $Usuario) {
        $U = new Usuario();
        $Usuario = $U->GetInfoCertificadoTabla($Usuario);
        /*echo '<pre>'.print_r($Usuario).'</pre>';
        echo '<pre>'.count($Usuario).'</pre>';*/
        $Certificado->UsuariosTabla($Usuario);
        /*echo '<script>console.log('.$Usuario['Vacuna'].');</script>';
        if(!in_array($Usuario['Vacuna'], $Vacunas) && $Usuario['Vacuna'] != '') {
            array_push($Vacunas, $Usuario['Vacuna']);
        }*/
    }

    $Certificado->PiePaginaTabla();

}

/**
 * Se genera certificado masivo tipo tabla
 * @param $data
 */
function GenerarCertificadoTabla($data) {
    /* Generación PDF */
    $Certificado = new Certificado();
    $fecha = new DateTime();
    $NombreArchivo = 'consolidado-tabla'.$fecha->getTimestamp().'.pdf';
    $URL = constant('Root_Base') . constant('Prefix_Out') . '/' . $NombreArchivo;
    $Usuarios = explode(',', $data['usuarios']);
    CrearCertificadoTabla($Usuarios, $Certificado, $NombreArchivo);
    $Certificado->Output('F', constant('Root_Fisica') . constant('Root_Out') . $NombreArchivo, true);
    echo $URL;
}