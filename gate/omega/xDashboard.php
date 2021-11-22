<?php
/**
 * xDashboard
 * Constructor del contenido general del sitio
 * @AlvaxVargas 2018
**/

/**
 * Se recargan los filtros de búsqueda
 * @param $data
 */
function LoadFilterData($data) {
    $IdUniversidad = "";
    if(Sesion('perfil') == 'docente') {
        $IdUniversidad = Sesion('universidad');
    }
    echo ''
        .'<div data-tipo="universidad" data-valor="'.$IdUniversidad.'"></div>'
        .'<div data-tipo="sede" data-valor=""></div>'
        .'<div data-tipo="carrera" data-valor=""></div>'
        .'<div data-tipo="fechavacunadesde" data-valor=""></div>'
        .'<div data-tipo="fechavacunahasta" data-valor=""></div>'
        .'<div data-tipo="numeropagina" data-valor="1"></div>'
        .'<div data-tipo="tamanopagina" data-valor="10"></div>'
        .'<div data-tipo="orderby" data-valor="1"></div>';
}

function LoadSidebar($data) {
    if(EsAdmin($data)) {
        /* Muestra sección de Administración */
        echo ''
            .'<div id="admin">'
            .'<div class="title">'
                .'<div id="adminbutton" class="icon small" onclick="$.ToggleSeccion(this, \'admin\')">Administración <div class="right-caret right"></div></div>'
            .'</div>'
            .'<div id="contenido_admin">'
            .'<div class="filtro">'
                .'<div class="nombre clickable" onclick="$.VistaAdmin()">VER ADMINISTRADOR  <div class="icon small"><div class="config right"></div></div></div>'
            .'</div>'
            .'<div class="filtro">'
                .'<div class="nombre">Alumnos <div class="icon small"><div class="user-student right"></div></div></div>'
                .'<ul class="valores">'
                    .'<li><div class="opcion_admin" onclick="$.AddAlumno()">Agregar un Alumno</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.EditAlumno()">Modificar un Alumno</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.AddDocente()">Agregar un Docente</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.SidebarFocus(\'docente\')">Modificar un Docente</div></li>'
                .'</ul>'
            .'</div>'
            .'<div class="filtro">'
                .'<div class="nombre">Universidades <div class="icon small"><div class="university right"></div></div></div>'
                .'<ul class="valores">'
                    .'<li><div class="opcion_admin" onclick="$.SidebarNew(\'universidad\')">Agregar una Universidad</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.SidebarFocus(\'universidad\')">Modificar Universidades</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.SidebarNew(\'sede\')">Agregar una Sede</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.SidebarFocus(\'sede\')">Modificar Sedes</div></li>'
                .'</ul>'
            .'</div>'
            .'<div class="filtro">'
            .'<div class="nombre">Carreras <div class="icon small"><div class="graduation right"></div></div></div>'
                .'<ul class="valores">'
                    .'<li><div class="opcion_admin" onclick="$.SidebarNew(\'carrera\')">Agregar una Carrera</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.SidebarFocus(\'carrera\')">Modificar una Carrera</div></li>'
                .'</ul>'
            .'</div>'
            .'<div class="filtro">'
                .'<div class="nombre">Vacunas <div class="icon small"><div class="syringe right"></div></div></div>'
                .'<ul class="valores">'
                    .'<li><div class="opcion_admin" onclick="$.SidebarNew(\'vacuna\')">Agregar una Vacuna</div></li>'
                    .'<li><div class="opcion_admin" onclick="$.SidebarFocus(\'vacuna\')">Modificar una Vacuna</div></li>'
                .'</ul>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'';
    }

    /* Guardado temporal de los filtros seleccionados */
    echo '<div id="filtros_data">';
    LoadFilterData($data);
    echo '</div>';

    echo ''
        .'<div id="filtros">'
        .'<div class="title">';
    if(EsAdmin($data)) {
        echo '<div class="icon small" onclick="$.ToggleSeccion(this, \'filtros\')">Filtros <div class="down-caret right"></div></div>';
    }
    else {
        echo 'Filtros';
    }
    echo '</div>'
        .'<div id="contenido_filtros">'
        .'<div class="filtro">'
            .'<div class="nombre clickable" onclick="$.LoadResultadosPaginado()">VER ALUMNOS  <div class="icon small"><div class="users right"></div></div></div>'
        .'</div>'
        .'<div class="filtro">'
            .'<div class="nombre">Alumno</div>'
            .'<div class="valores">'
                .'<input type="text" id="alumno" name="alumno" onkeyup="$.BuscarEnter(event);" spellcheck="false" placeholder="Nombre, apellido o rut">'
            .'</div>'
        .'</div>';


    if(Sesion('perfil') == 'admin') {
        echo '<div class="filtro">'
            .'<div class="nombre">'
                .'<span>Universidades</span>'
                .'<div class="botones">'
                    .'<div class="icon small" onclick="$.ToggleFiltro(this)"><div class="down-caret"></div></div>'
                .'</div>'
            .'</div>'
            .'<div class="valores">';
        LoadSelectUniversidadAll($data);
        echo '</div>'
            .'</div>';
    }
    else {
        echo '<div class="hidden">';
        LoadSelectUniversidad($data);
        echo '</div>';
    }

    echo '<div class="filtro">'
            .'<div class="nombre">'
                .'<span>Sedes</span>'
                .'<div class="botones">'
                    .'<div class="icon small" onclick="$.ToggleFiltro(this)"><div class="right-caret"></div></div>'
                .'</div>'
            .'</div>'
		.'<div id="select_sedes" class="valores hidden">';
    if(Sesion('perfil') == 'admin') {
        LoadSelectSedeAll($data);
    }
    else {
        LoadSelectSede($data);
    }
	echo '</div>'
		.'</div>'
        .'<div class="filtro">'
            .'<div class="nombre">'
                .'<span>Carreras</span>'
                .'<div class="botones">'
                    .'<div class="icon small" onclick="$.ToggleFiltro(this)"><div class="right-caret"></div></div>'
                .'</div>'
            .'</div>'
		.'<div id="select_carreras" class="valores hidden">';
    if(Sesion('perfil') == 'admin') {
        LoadSelectCarreraAll($data);
    }
    else {
        LoadSelectCarrera($data);
    }
	echo '</div>'
		.'</div>'
        .'';
    echo '<div class="filtro">'
        .'<div class="nombre">'
            .'<span>Fecha Vacunación</span>'
            .'<div class="botones">'
            .'<div class="icon small" onclick="$.ToggleFiltro(this)"><div class="right-caret"></div></div>'
        .'</div>'
        .'</div>'
        .'<div class="valores hidden">'
            .'<div class="date_filter">'
                .'<div class="opcion_admin_date">Desde: </div>'
                .'<input type="date" onchange="$.ActualizaFiltros(\'fechavacunadesde\', this.value)">'
            .'</div>'
            .'<div class="date_filter">'
                .'<div class="opcion_admin_date">Hasta: </div>'
        .'<input type="date" onchange="$.ActualizaFiltros(\'fechavacunahasta\', this.value)">'
            .'</div>'
        .'</div>'
        .'</div>';

    if(Sesion('perfil') == 'admin') {
        echo '<div class="button filter selectDisable" onclick="$.LoadResultadosPaginado();">'
            . '<span>Aplicar filtros</span>'
            . '<div class="icon"><div class="filter"></div></div>'
            . '</div>';
    }

    echo '<div class="button cancel selectDisable" onclick="$.CleanFilters();">'
            .'<span>Eliminar filtros</span>'
            .'<div class="icon"><div class="cancel"></div></div>'
        .'</div>'
        .'</div>'
        .'</div>'; /*#filtros*/
    echo '<script>
	    $(".opcion_filtro").click(function(){
	        $(this).siblings().removeClass("selected");
	        $(this).addClass("selected");
	        /*
	    	if($(this).hasClass("selected")){
	    		$(this).removeClass("selected");
	    	}
	    	else{
	    		$(this).addClass("selected");
	    	}*/
	    });
		</script>';

    if(Sesion('perfil') != 'admin') {
        echo '<script>'
            .'$.LoadResultadosPaginado();'
		    .'</script>';
    }
}

/**
 * Carga de la barra de contenido derecha
 * @param $data
 * @throws Exception
 */
function LoadRightCol($data){
    echo '';
}

/**
 *
 * @param $data
 */
function LoadPortada($data) {
    echo '<div id="fondo_portada">'
            .'<div id="brand" class="portada"></div>'
            .'<div id="contenedor_portada">'
                .'<div class="portada_ayuda">'
                    .'<div class="bloque_texto">'
                        .'<h1 class="center">Bienvenid@ a la Plataforma de Certificados</h1>'
                        .'<p><b>¿Eres alumn@ y te vacunaste con nosotros?</b></p>'
                        .'<p>En el campo usuario ingresa tu Rut sin puntos ni dígito verificador:<br/><i><b>Ejemplo: 12345678</b></i></p>'
                        .'<p>En el campo contraseña, ingresa los primeros 4 dígitos de tu Rut:<br/><i><b>Ejemplo: 1234</b></i></p>'
                        .'<p>¡Y listo!</p>'
                        .'<p><b>¿Eres docente y buscas los certificados de alumnos de tu institución?</b></p>'
                        .'<p>Necesitarás una cuenta creada especialmente para ti, por favor contáctanos a nuestro correo <b><a href="mailto:vacu.altotabancura@gmail.com">vacu.altotabancura@gmail.com</a></b></p>'
                        .'<p>¡Muchas gracias!</p>'
                    .'</div>'
                .'</div>'
                .'<div class="portada_login">'
                    .'<div id="logo_portada" class="center">Ingresar al sistema</div>'
                    .'<div id="contenedor_form">';
    LoadLogin($data);
    echo '</div>'
        .'</div>'
        .'</div>'
        .'</div>';
}