<?php

function VistaAdmin($data) {

    FiltrarSesion();

    echo ''
        .'<div id="docentes">'
            .'<div id="sidebar"></div>'
            .'<div class="cargando"></div>'
            .'<div id="resultsrow"></div>'
        .'</div>'
        .'';

    echo ''
        .'<script>$.LoadSidebar()</script>'
        .'<script>$.LoadAdmin()</script>'
        .'';
}

function LoadAdmin($data) {

    echo '<div id="usergrid">'
             .'<div class="adminbtn selectDisable" onclick="$.LoadAlumnoForm()" title="Nuevo Alumno">'
                .'<span>Nuevo Alumno</span>'
                .'<div class="icon"><div class="new"></div></div>'
            .'</div>'

            .'<div class="adminbtn selectDisable" onclick="$.LoadDocenteForm()" title="Nuevo Docente">'
                .'<span>Nuevo Docente</span>'
                .'<div class="icon"><div class="new"></div></div>'
            .'</div>'

        .'</div>';

    echo ''
        .'<div id="usuarioform" class="adminform"></div>'
        .'<div id="docenteform" class="adminform"></div>';

    echo '<div id="admingrid">';

    /* ADMIN DOCENTES */
    echo '<div class="adminrow">'
            .'<div id="docentesrow" class="admincol">'
                .'<div class="titulo">Docentes</div>'
                .'<div class="botonesadmin">'
                    .'<div class="adminbtn selectDisable" onclick="$.LoadDocenteForm()" title="Nuevo Docente">'
                        .'<span>Docente</span>'
                        .'<div class="icon"><div class="new"></div></div>'
                    .'</div>'
                .'</div>'
            .'<div id="doce_admin" class="results_admin"></div>'
        .'</div>';

    /* ADMIN UNIVERSIDADES CARRERAS SEDES */
    echo ''
        .'<div class="adminrow">'
            .'<div id="universidadesrow" class="admincol">'
                .'<div class="titulo">Universidades</div>'
                .'<div class="botonesadmin">'
                    .'<div class="adminbtn selectDisable" onclick="$.LoadUniversidadForm()" title="Nueva Universidad">'
                        .'<span>Universidad</span>'
                        .'<div class="icon"><div class="new"></div></div>'
                    .'</div>'
                .'</div>'
                .'<div id="universidadform"></div>'
                .'<div id="univ_admin" class="results_admin"></div>'
            .'</div>'
            .'<div id="carrerasrow" class="admincol">'
                .'<div class="titulo">Carreras</div>'
                    .'<div class="botonesadmin">'
                    .'<div class="adminbtn selectDisable" onclick="$.LoadCarreraForm()" title="Nueva Carrera">'
                    .'<span>Carrera</span>'
                    .'<div class="icon"><div class="new"></div></div>'
                .'</div>'
            .'</div>'
            .'<div id="carreraform"></div>'
            .'<div id="carr_admin" class="results_admin"></div>'
            .'</div>'
        .'</div>'
        .'<div class="adminrow">'
            .'<div id="sedesrow" class="admincol">'
                .'<div class="titulo">Sedes</div>'
                    .'<div class="botonesadmin">'
                    .'<div class="adminbtn selectDisable" onclick="$.LoadSedeForm()" title="Nueva Sede">'
                    .'<span>Sede</span>'
                    .'<div class="icon"><div class="new"></div></div>'
                .'</div>'
            .'</div>'
            .'<div id="sedeform"></div>'
            .'<div id="sede_admin" class="results_admin"></div>'
            .'</div>'
            .'<div id="vacunasrow" class="admincol">'
                .'<div class="titulo">Vacunas</div>'
                .'<div class="botonesadmin">'
                    .'<div class="adminbtn selectDisable" onclick="$.LoadVacunaForm()" title="Nueva Vacuna">'
                        .'<span>Vacuna</span>'
                        .'<div class="icon"><div class="new"></div></div>'
                    .'</div>'
                .'</div>'
                .'<div id="vacunaform"></div>'
                .'<div id="vacu_admin" class="results_admin"></div>'
            .'</div>'
        .'</div>'
        .'</div>'
        .'';

    echo ''
        .'<script>$.LoadDocenteAdmin()</script>'
        .'<script>$.LoadUniversidadAdmin()</script>'
        .'<script>$.LoadSedeAdmin()</script>'
        .'<script>$.LoadCarreraAdmin()</script>'
        .'<script>$.LoadVacunaAdmin()</script>'
        .'';
}