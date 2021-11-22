<?php
/***
 * Constructor Usuarios
 *  Alumno|Docente|Administrador
**/

function CheckFecha($Fecha) {
    if($Fecha == '') {
        return '-';
    }/*
    elseif($pos !== false) {
        return '-2';
    }*/
    return FormatearFecha($Fecha, 2);
}

function VistaAlumno($data) {

    FiltrarSesion();

    $U = new Usuario();
    $IdUsuario = Sesion('idusuario');

    echo '<style>body {overflow-y: scroll;}</style>';

    /** USUARIO **/
    $Usuario = $U->GetById($IdUsuario)[0];
    $FechaNac = CheckFecha($Usuario['FechaNacimiento']);
    echo ''
        .'<div class="contenedor">'
            .'<div id="introduccion">'
                .'<div>Hola '.explode(' ', $Usuario['Nombres'])[0].',<br>Verifica si tus datos están correctos y descarga tu certificado de vacunas.</div>'
            .'</div>'
            .'<div id="informacion">'
                .'<div class="titulo">Información personal</div>'
                .'<table class="tablealumno">'
                    .'<tr>'
                        .'<td>Nombre Completo</td>'
                        .'<td><b>'.$Usuario['Nombres'].' '.$Usuario['Apellidos'].'</b></td>'
                    .'</tr>'
                    .'<tr>'
                        .'<td>Rut</td>'
                        .'<td><b>'.$Usuario['Rut'].'</b></td>'
                    .'</tr>'
                    .'<tr>'
                        .'<td>Fecha de Nacimiento</td>'
                        .'<td><b>'.$FechaNac.'</b></td>'
                    .'</tr>'
                    .'<tr>'
                        .'<td>Universidad</td>'
                        .'<td><b>'.$Usuario['Universidad'].'</b></td>'
                    .'</tr>'
                    .'<tr>'
                        .'<td>Sede</td>'
                        .'<td><b>'.$Usuario['Sede'].'</b></td>'
                    .'</tr>'
                    .'<tr>'
                        .'<td>Carrera</td>'
                        .'<td><b>'.$Usuario['Carrera'].'</b></td>'
                    .'</tr>'
                .'</table>'
            .'</div>'
            .'<div id="vacunas">'
                .'<div class="titulo">Vacunas</div>'
                .'<div id="listadovacunas">'
                    .'<table class="tablealumno" style="text-align: center;">'
                        .'<tr>'
                            .'<th>Dosis</th>'
                            .'<th>Vacuna</th>'
                            .'<th>Fecha</th>'
                        .'</tr>';

    /** VACUNAS **/
    $Vacunas = $U->GetVaccines($IdUsuario);
    $Dosis = '';
    foreach ($Vacunas as $V) {
        switch ($V['Dosis']) {
            case 1:
                $Dosis = '1ra dosis';
                break;
            case 2:
                $Dosis = '2da dosis';
                break;
            case 3:
                $Dosis = '3ra dosis';
                break;
        }
        $FechaVac = CheckFecha($V['Fecha']);
        $Lote = trim($V['Lote']) === '' ? '' : '('.trim($V['Lote']).')';
        echo ''
            .'<tr>'
                .'<td>'.$Dosis.'</td>'
                .'<td>'.$V['Vacuna'].' '.$Lote.'</td>'
                .'<td>'.$FechaVac.'</td>'
                .'<td>'.$V['Observacion'].'</td>'
            .'</tr>'
            .'';
    }
    echo '</table>'
                .'</div>'
            .'</div>'
            .'<div id="descarga">'
                .'<div id="certificado_button" class="button file" onclick="$.GenerarCertificado('.$IdUsuario.')">'
                    .'<div class="icon"><div class="file"></div></div>'
                    .'<span>Descargar certificado</span>'
                .'</div>'
            .'</div>'
        .'</div>'
        .'';

}

function VistaDocente($data){

    FiltrarSesion();

    echo ''
        .'<div id="docentes">'
            .'<div id="sidebar"></div>'
            .'<div class="cargando"></div>'
            .'<div id="resultsrow">'
                .'<div class="inicio">'
                .'<span>Seleccione un filtro para comenzar</span>'
                //.'<span><div class="icon"><div class="search"></div></div></span>'
                .'</div>'
            .'</div>'
        .'</div>'
        .'';

    echo ''
        .'<script>$.LoadSidebar()</script>'
        .'';
}

function LoadTopbar($data) {
    echo ''
        .'<div class="leftrow">'
            .'<div class="selectall">'
                .'<input onclick="$.SelectAll(this)" type="checkbox" id="todos" name="todos">'
                .'<span>Seleccionar todos</span>'
            .'</div>';
    if(EsAdmin($data)) {
        echo '<div class="adminbtn selectDisable" onclick="$.LoadAlumnoForm()" title="Nuevo Alumno">'
                    .'<span>Nuevo Alumno</span>'
                    .'<div class="icon"><div class="new"></div></div>'
                .'</div>'
            .'</div>';
    }
    echo '<div class="rightrow">'
            .'<div id="certificado_button" class="button file hidden" onclick="$.GenerarMultiplesCertificados()">'
                .'<div class="icon"><div class="file"></div></div>'
                .'<span>Descargar certificados</span>'
            .'</div>'
            .'<div id="certificado_masivo_button" class="button file hidden" onclick="$.GenerarCertificadoTabla()">'
                .'<div class="icon"><div class="file"></div></div>'
                .'<span>Formato tabla</span>'
            .'</div>'
        .'</div>'
        .'';
}

function LoadResultados($data) {

    $Usuario = new Usuario();
    $Filtros = json_decode($data['filtros'],1);

    echo '<div id="topbar"></div>';

    if(EsAdmin($data)) {
        echo '<div id="usuarioform"></div>';
        echo '<table id="resultstable" class="display">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                    <th>Universidad</th>
                    <th>Vacunas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>';
        $Busqueda = $Usuario->Get(utf8_encode($data['busqueda']), ArrayToXml($Filtros));
        foreach ($Busqueda as $usuario) {
            echo '<tr data-idusuario="'.$usuario['IdUsuario'].'">
                    <td><input class="check_usuario" type="checkbox" name="usuario" data-idusuario="'.$usuario['IdUsuario'].'" onclick="$.CheckClick()"></td>
                    <td>' . $usuario['Nombres'] . ' ' . $usuario['Apellidos'] . '</td>
                    <td>' . $usuario['Rut'] . '</td>
                    <td>' . ($usuario['FechaNacimiento'] == '1900-01-00' ? '' : $usuario['FechaNacimiento']) . '</td>
                    <td>' . $usuario['Carrera'] . '</td>
                    <td>' . $usuario['Sede'] . '</td>
                    <td>' . $usuario['Universidad'] . '</td>
                    <td>' . $usuario['Vacunas'] . '</td>
                    <td>
                        <div class="usuariobotones">
                            <div class="icon small" onclick="$.LoadUsuario('.$usuario['IdUsuario'].')" title="Editar este Usuario"><div class="edit right"></div></div>
                            <div class="icon small" onclick="$.ShowVaccines('.$usuario['IdUsuario'].')" title="Modificar Vacunas"><div class="syringe2 right"></div></div>                            
                        </div>
                    </td>
                </tr>';
        }
        echo '</tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                    <th>Universidad</th>
                    <th>Vacunas</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>';
    }
    else {
        /* SI ES DOCENTE */
        echo '<table id="resultstable" class="display">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                </tr>
            </thead>
            <tbody>';
            $Busqueda = $Usuario->Get($data['busqueda'], ArrayToXml($Filtros));
            foreach ($Busqueda as $usuario) {
                echo '<tr data-idusuario="'.$usuario['IdUsuario'].'">
                    <td><input class="check_usuario" type="checkbox" name="usuario" data-idusuario="'.$usuario['IdUsuario'].'" onclick="$.CheckClick()"></td>
                    <td>'.$usuario['Nombres'].' '.$usuario['Apellidos'].'</td>
                    <td>'.$usuario['Rut'].'</td>
                    <td>'.($usuario['FechaNacimiento'] == '1900-01-00' ? '' : $usuario['FechaNacimiento']).'</td>
                    <td>'.$usuario['Carrera'].'</td>
                    <td>'.$usuario['Sede'].'</td>
                </tr>';
            }
            echo '</tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                </tr>
            </tfoot>
        </table>';
    }
}

function LoadResultadosPaginado($data) {

    $Usuario = new Usuario();

    echo '<div id="topbar"></div>';

    echo '<div id="usuarioform"></div>';
    echo '<div id="pagesizer">'
        .'<span>Mostrar '
            .'<select class="pagesize" name="select" onchange="$.ActualizaFiltros(\'tamanopagina\',this.value)">'
                .'<option value="10">10</option>'
                .'<option value="20">20</option>'
                .'<option value="50">50</option>'
                .'<option value="100">100</option>'
                .'<option value="200">200</option>'
                .'<option value="500">500</option>'
            .'</select>'
        .' registros</span>'
        .'</div>';
    echo '<script>$(".pagesize").val($("#filtros_data div[data-tipo=\'tamanopagina\']").attr("data-valor"));</script>';

    if(EsAdmin($data)) {
        echo '<table id="resultstable" class="display">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                    <th>Universidad</th>
                    <th>Vacunas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>';
        $Busqueda = $Usuario->GetPaginado(
                utf8_encode($data['busqueda']),
                $data['iduniversidad'],
                $data['idsede'],
                $data['idcarrera'],
                $data['fechavacunadesde'],
                $data['fechavacunahasta'],
                $data['numeropagina'],
                $data['tamanopagina'],
                $data['orderby']
        );
        foreach ($Busqueda as $usuario) {
            echo '<tr data-idusuario="'.$usuario['IdUsuario'].'">
                    <td><input class="check_usuario" type="checkbox" name="usuario" data-idusuario="'.$usuario['IdUsuario'].'" onclick="$.CheckClick()"></td>
                    <td>' . $usuario['Nombres'] . ' ' . $usuario['Apellidos'] . '</td>
                    <td>' . $usuario['Rut'] . '</td>
                    <td>' . ($usuario['FechaNacimiento'] == '1900-01-00' ? '' : $usuario['FechaNacimiento']) . '</td>
                    <td>' . $usuario['Carrera'] . '</td>
                    <td>' . $usuario['Sede'] . '</td>
                    <td>' . $usuario['Universidad'] . '</td>
                    <td>' . $usuario['Vacunas'] . '</td>
                    <td>
                        <div class="usuariobotones">
                            <div class="icon small" onclick="$.LoadUsuario('.$usuario['IdUsuario'].')" title="Editar este Usuario"><div class="edit right"></div></div>
                            <div class="icon small" onclick="$.ShowVaccines('.$usuario['IdUsuario'].')" title="Modificar Vacunas"><div class="syringe2 right"></div></div>                            
                        </div>
                    </td>
                </tr>';
        }
        echo '</tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                    <th>Universidad</th>
                    <th>Vacunas</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>';
    }
    else {
        /* SI ES DOCENTE */
        echo '<table id="resultstable" class="display">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                </tr>
            </thead>
            <tbody>';
        $Busqueda = $Usuario->GetPaginado(
            utf8_encode($data['busqueda']),
            $data['iduniversidad'],
            $data['idsede'],
            $data['idcarrera'],
            $data['fechavacunadesde'],
            $data['fechavacunahasta'],
            $data['numeropagina'],
            $data['tamanopagina'],
            $data['orderby']
        );
        foreach ($Busqueda as $usuario) {
            echo '<tr data-idusuario="'.$usuario['IdUsuario'].'">
                    <td><input class="check_usuario" type="checkbox" name="usuario" data-idusuario="'.$usuario['IdUsuario'].'" onclick="$.CheckClick()"></td>
                    <td>'.$usuario['Nombres'].' '.$usuario['Apellidos'].'</td>
                    <td>'.$usuario['Rut'].'</td>
                    <td>'.($usuario['FechaNacimiento'] == '1900-01-00' ? '' : $usuario['FechaNacimiento']).'</td>
                    <td>'.$usuario['Carrera'].'</td>
                    <td>'.$usuario['Sede'].'</td>
                </tr>';
        }
        echo '</tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Rut</th>
                    <th>Fecha Nacimiento</th>
                    <th>Carrera</th>
                    <th>Sede</th>
                </tr>
            </tfoot>
        </table>';
    }

    echo '<div id="paginacion"></div>';
}

function LoadPaginacion($data) {

    $Usuario = new Usuario();
    $Busqueda = $Usuario->GetPaginadoTotal(
        utf8_encode($data['busqueda']),
        $data['iduniversidad'],
        $data['idsede'],
        $data['idcarrera'],
        $data['fechavacunadesde'],
        $data['fechavacunahasta'],
        $data['numeropagina'],
        $data['tamanopagina'],
        $data['orderby']
    );
    $Total = 0;
    foreach ($Busqueda as $T) {
        $Total = $T;
    }
    $PaginaActual = $data['numeropagina'];
    $CantidadPaginas = ceil($Total / $data['tamanopagina']);

    if($data['tamanopagina'] < $Total)
        echo "<div class='info'>Mostrando ".$data['tamanopagina']." de un total de ".$Total." registros </div>";
    else
        echo "<div class='info'>Mostrando ".$Total." registros</div>";
    /*echo "Cantidad de resultados: ".$Total;
    echo "Tamaño pagina: ".$data['tamanopagina'];
    echo "Cantidad de paginas: ".;*/

    echo "<div class='paginas'>";
    //if()
    for($i=1; $i<=$CantidadPaginas; $i++){
        if($PaginaActual == $i)
            echo "<div class='pagina activa' onclick='$.ActualizaFiltros(\"numeropagina\",".$i.")'>".$i."</div>";
        else
            echo "<div class='pagina' onclick='$.ActualizaFiltros(\"numeropagina\",".$i.")'>".$i."</div>";
    }

    echo "</div>";

}

function LoadAlumnoForm($data) {
    $IdUsuario = 0;
    $Form = "formAlumno";
    echo ''
        .'<div class="formulario">'
            .'<div class="titulo"><div class="icon small" onclick="" title="Datos Alumno">Datos Alumno <div class="usercard right"></div></div></div>'
            .'<form id="'.$Form.'">'
                .'<input type="hidden" name="idusuario" value="0">'
                .'<input class="adminput required" type="text" name="nombres" placeholder="Ingrese Nombres del Alumno">'
                .'<input class="adminput required" type="text" name="apellidos" placeholder="Ingrese Apellidos del Alumno">'
                .'<input class="adminput required" type="text" name="rut" placeholder="Ingrese Rut del Alumno">'
                .'<input class="adminput" type="text" name="fechanacimiento" placeholder="Ingrese Fecha de Nacimiento del Alumno" onfocus="(this.type=\'date\')">';
    CarreraSelect($data);
    SedeSelect($data);
    echo '</form>'
        .'<div class="botonesformulario">'
            .'<button class="clickable adminbtn cancel" onclick="$.CloseAlumnoForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
            .'<button class="clickable adminbtn success" onclick="$.SaveUsuario();return false;">Crear <div class="icon small" title="Grabar"><div class="okey2 right"></div></div></button>'
            .'<div id="usermessage"></div>'
        .'</div>'
        .'</div>'
        .'';
}

function LoadDocenteForm($data) {
    $IdUsuario = 0;
    $Form = "formDocente";
    echo ''
        .'<div class="formulario">'
            .'<div class="titulo"><div class="icon small" onclick="" title="Datos Alumno">Datos Docente <div class="usercard right"></div></div></div>'
                .'<form id="'.$Form.'">'
                    .'<input type="hidden" name="idusuario" value="0">'
                    .'<input class="adminput required" type="text" name="nombres" placeholder="Ingrese Nombre del Docente">'
                    .'<input class="adminput required" type="text" name="apellidos" placeholder="Ingrese Apellido del Docente">'
                    .'<input class="adminput required" type="text" name="username" placeholder="Ingrese Nombre de Usuario">';
    LoadSelectSedeByUniversidad($data);
    echo '<input class="adminput required" type="text" name="password" placeholder="Ingrese Contraseña">'
        .'</form>'
        .'<div class="botonesformulario">'
            .'<button class="clickable adminbtn cancel" onclick="$.CloseDocenteForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
            .'<button class="clickable adminbtn success" onclick="$.SaveDocente();return false;">Guardar <div class="icon small" title="Guardar"><div class="okey2 right"></div></div></button>'
            .'<div id="usermessage"></div>'
        .'</div>'
        .'</div>'
        .'';
}

function LoadDocenteAdmin($data) {
    $Docente = new Usuario();
    $Docentes = $Docente->GetDocentes();

    echo '<table id="docentetable" class="display compact">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Username</th>
                <th>Institución</th>
                <th></th>
            </tr>
        </thead>
        <tbody>';
    foreach ($Docentes as $D) {
        $formId = "formDocente".$D['IdUsuario'];
        echo '<tr class="formulario" data-idusuario="'.$D['IdUsuario'].'">
                <td>
                    <span class="view">'.$D['Nombres'].'</span>
                </td>
                <td>
                    <span class="view">'.$D['Apellidos'].'</span>
                </td>
                <td>
                    <span class="view">'.$D['Username'].'</span>
                </td>
                <td>
                    <span class="view">'.$D['Nombre'].'</span>
                </td>
                <td>
                    <div class="formbotones view">
                        <div class="icon small" onclick="$.LoadDocente('.$D['IdUsuario'].')" title="Editar este Docente"><div class="edit right"></div></div>
                        <div class="icon small" onclick="$.DeleteDocente('.$D['IdUsuario'].')" title="Eliminar este Docente"><div class="trash right"></div></div>
                    </div>
                </td>
            </tr>';
    }
    echo '</tbody>
    </table>';
}

function LoadUsuario($data) {

    $Usuario = new Usuario();

    if(is_numeric($data['usuario'])) {
        if($Usuario->GetById($data['usuario'])) {
            $U = $Usuario->GetById($data['usuario'])[0];
        }
        else {
            $U = $Usuario->GetByRut($data['usuario'])[0];
        }
    }
    else {
        $U = $Usuario->GetByRut($data['usuario'])[0];
    }
    $IdUsuario = $U['IdUsuario'];
    $data['idusuario'] = $U['IdUsuario'];
    $Form = "formAlumno";
    echo ''
        .'<div class="formulario" data-idusuario="'.$IdUsuario.'">'
        .'<div class="titulo"><div class="icon small" onclick="" title="Datos Alumno">Datos Alumno <div class="usercard right"></div></div></div>'
        .'<form id="'.$Form.'">'
        .'<input type="hidden" name="idusuario" value="'.$U['IdUsuario'].'">'
        .'<input class="adminput required" type="text" name="nombres" placeholder="Ingrese Nombres del Alumno" value="'.$U['Nombres'].'">'
        .'<input class="adminput required" type="text" name="apellidos" placeholder="Ingrese Apellidos del Alumno" value="'.$U['Apellidos'].'">'
        .'<input class="adminput required" type="text" name="rut" placeholder="Ingrese Rut del Alumno" value="'.$U['Rut'].'">'
        .'<input class="adminput" type="text" name="fechanacimiento" placeholder="Ingrese Fecha de Nacimiento del Alumno" onfocus="(this.type=\'date\')" value="'.$U['FechaNacimiento'].'">';
    CarreraSelect($data, $U['Carrera']);
    SedeSelect($data, $U['Sede'], $U['Universidad']);
    echo '</form>'
        .'<div id="vacunasformulario">';
    VacunasFormularioUsuario($data);
    echo '</div>'
        .'<div class="botonesformulario">'
        .'<button class="clickable adminbtn cancel" onclick="$.CloseAlumnoForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
        .'<button class="clickable adminbtn cancel" onclick="$.DeleteUsuario('.$U['IdUsuario'].')">Eliminar <div class="icon small" title="Eliminar este Usuario"><div class="trashwhite right"></div></div></button>';
    if($IdUsuario > 0) {
        echo '<button id="editvaccines" class="clickable adminbtn" onclick="$.EditVaccines(this)">Editar Vacunas <div class="icon small" onclick="" title="Vacunas"><div class="syringe3 right"></div></div></button>';
    }
    echo '<button class="clickable adminbtn success" onclick="$.SaveUsuario();return false;">Guardar <div class="icon small" title="Grabar"><div class="okey2 right"></div></div></button>'
        .'<button class="clickable adminbtn cancel" onclick="$.GenerarCertificado('.$U['IdUsuario'].')">PDF <div class="icon small" title="Ver Certificado"><div class="file2 right"></div></div></button>'

        .'<div id="usermessage"></div>'
        .'</div>'
        .'</div>'
        .'';
}

function LoadDocente($data) {
    $Usuario = new Usuario();
    if(is_numeric($data['usuario'])) {
        if($Usuario->GetById($data['usuario'])) {
            $U = $Usuario->GetById($data['usuario'])[0];
        }
        else {
            $U = $Usuario->GetByRut($data['usuario'])[0];
        }
    }
    /*else {
        $U = $Usuario->GetByRut($data['usuario'])[0];
    }*/
    $IdUsuario = $U['IdUsuario'];
    $data['idusuario'] = $U['IdUsuario'];
    $Form = "formDocente";
    echo ''
        .'<div class="formulario" data-idusuario="'.$IdUsuario.'">'
        .'<div class="titulo"><div class="icon small" onclick="" title="Datos Alumno">Datos Docente <div class="usercard right"></div></div></div>'
        .'<form id="'.$Form.'">'
        .'<input type="hidden" name="idusuario" value="'.$U['IdUsuario'].'">'
        .'<input class="adminput required" type="text" name="nombres" placeholder="Ingrese Nombre del Docente" value="'.$U['Nombres'].'">'
        .'<input class="adminput required" type="text" name="apellidos" placeholder="Ingrese Apellido del Docente" value="'.$U['Apellidos'].'">'
        .'<input class="adminput required" type="text" name="username" placeholder="Ingrese Nombre de Usuario" value="'.$U['Username'].'">';
    LoadSelectSedeByUniversidad($data, $U['IdUniversidad']);
    echo '<input class="adminput required" type="text" name="password" placeholder="Ingrese Contraseña" value="******">'
        .'</form>'
        .'<div class="botonesformulario">'
        .'<button class="clickable adminbtn cancel" onclick="$.CloseDocenteForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
        .'<button class="clickable adminbtn success" onclick="$.SaveDocente();return false;">Guardar <div class="icon small" title="Guardar"><div class="okey2 right"></div></div></button>'
        .'<div id="usermessage"></div>'
        .'</div>'
        .'</div>'
        .'';
}

function CreateModifyUsuario($data) {
    $Usuario = new Usuario();
    $U = $Usuario->CreateModify(
            $data['IdUsuario'],
            $data['Nombres'],
            $data['Apellidos'],
            $data['Username'],
            $data['Rut'],
            $data['FechaNacimiento'],
            $data['IdCarrera'],
            $data['IdSede'],
            $data['Password'],
            $data['IdTipoUsuario']
    );
}

function RemoveUsuario($data) {
    $Usuario = new Usuario();
    $Usuario->Remove($data['IdUsuario']);
}

function UserMessage($data) {
    $Action = $data['action'];
    $message = $color = "";
    switch ($Action) {
        case "new":
            $color = "green";
            $message = "Usuario creado exitosamente";
            break;
        case "edit":
            $color = "green";
            $message = "Usuario actualizado exitosamente";
            break;
        case "newvac":
            $color = "green";
            $message = "Vacuna creada exitosamente";
            break;
        case "editvac":
            $color = "green";
            $message = "Vacuna actualizada exitosamente";
            break;
        case "delvac":
            $color = "red";
            $message = "Vacuna eliminada exitosamente";
            break;
        case "save":
            $color = "green";
            $message = "Usuario y vacunas actualizadas exitosamente";
            break;
        case "required":
            $color = "blue";
            $message = "Por favor ingrese los valores requeridos";
            break;
    }
    echo '<div class="usermessage '.$color.'">'
            .$message
        .'</div>';
}