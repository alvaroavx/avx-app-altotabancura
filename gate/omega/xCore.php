<?php
/**
 * xCore
 * Constructor de la estructura inicial del sitio
 * @AlvaxVargas Steins 2018
 **/

/**
 * EstructuraBase
 * Carga la estructura base dentro del midblock
 */
function EstructuraBase(){
    FiltrarSesion();
    echo '<script>'
        .'$.HeartBeat();'
        .'</script>';
}

/**
 * LoadMidBlock
 * Encargada de actualizar el contenido central del sitio
 * @param $data
 */
function LoadMidBlock($data){
    $Load = $data['load'];
    $IdLoad = $data['idload'];
    $Init = $data['init'];

    if($Init == 0) {
        if(ValidaSesion() == 0)
            $Init = 1;
        else
            $Init = 2;
    }
    echo '<script>'
        .'$.LoadHeader('.$Init.');'
        .'$.LoadFooter('.$Init.');'
        .'</script>';
    switch ($Init) {
        case 2:
            FiltrarSesion();
            /* Login a la App */
            $Perfil = Sesion('perfil');
            switch ($Perfil) {
                case 'alumno':
                    VistaAlumno($data);
                    break;
                case 'docente':
                    VistaDocente($data);
                    break;
                case 'admin':
                    VistaAdmin($data);
                    break;
                default:
                    echo '<script>$.LoadCore();</script>';
                    break;
            }
            //in_array($Load, ['admin'])
            break;
        case 1:
        default:
            /* Sin loguearse */
            LoadPortada($data);
            break;
    }
}

/**
 * LoadHeader
 * Encargada de actualizar la cabecera
 * @param $data
 */
function LoadHeader($data){
    $Init = $data['init'];
    $Perfil = Sesion('perfil');
    switch ($Init) {
        case 2:
            switch ($Perfil) {
                case 'alumno':
                    $Message = 'Bienvenid@ al sistema de descarga de certificados.';
                    break;
                case 'docente':
                    $Message = 'Bienvenid@ al sistema de descarga de certificados.<br>Utilice los filtros para acotar su búsqueda.';
                    break;
                case 'admin':
                    $Message = 'Bienvenid@ al sistema de administración de certificados.<br>Puede ingresar al administrador o buscar usuarios con los filtros de búsqueda.';
                    break;
                default:
                    echo '<script>$.LoadCore();</script>';
                    break;
            }

            echo ''
                .'<div id="header">'
                .'<div id="sidebarbtn" class="button filter" onclick="$.ShowSidebar()"><div class="icon"><div class="bars"></div></div></div>'
                .'<div id="brand" class="clickable" onclick="$.LoadCore()"></div>'
                .'<div id="message">'.$Message.'</div>'
                .'<div id="userpanel">'
                    .'<div id="greets">Bienvenid@, '.ucfirst(Sesion('usuario')).'</div>'
                    .'<div id="signout" class="button filter selectDisable" onclick="$.DeleteSesion();">'
                        .'<span>Cerrar sesión</span>'
                        .'<div class="icon"><div class="logout"></div></div>'
                        .'</div>'
                    .'</div>'
                .'</div>'
                .'';
            break;
        case 1:
        default:
            break;
    }
}

function LoadFooter($data){
    $Init = $data['init'];
    switch ($Init) {
        case 2:
            break;
        case 3:
            break;
        case 1:
        default:
        echo ''
            . '<div id="footer">Implementado por <a href="https://www.avx.cl" target="blank">Alvax Informática</a></div>'
            . '';
            break;
    }
}

function EsAdmin($data){
    return Sesion('perfil') == 'admin';
}

function LoadCoreRegistro($data){
	echo '<div id="fondo_portada">'
		.'<div id="logo_portada"></div>';
	LoadRegistro($data);
	echo '</div>';
}
function LoadCoreRecoverPass($data){
	echo '<div id="fondo_portada">'
		.'<div id="logo_portada"></div>';
	LoadRecoverPass($data);
	echo '</div>';
}
function LoadWaitRecover($data){
	echo '<div id="fondo_portada">'
		.'<div id="logo_portada"></div>';
	WaitRecover($data);
	echo '</div>';
}