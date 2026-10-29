<?php

/**
 * Obtiene las Carreras según la Universidad (Docente)
 * @param $data
 */
function LoadSelectCarrera($data) {
    $Carrera = new Carrera();
    $Carreras = $Carrera->Get(Sesion('universidad'));
    foreach ($Carreras as $C) {
	    echo '<div onclick="$.ResultadosPorCarrera(this, '.$C['IdCarrera'].')" class="opcion_filtro selectDisable clearable" data-tipo="3" data-valor="'.$C['IdCarrera'].'">'
                .$C['Nombre'].' <i>('.$C['Cantidad'].')</i>'
            .'</div>';
    }
}

/**
 * Obtiene todas las Carreras (Admin)
 * @param $data
 */
function LoadSelectCarreraAll($data) {
    $Carrera = new Carrera();
    $Carreras = $Carrera->GetAll();
    foreach ($Carreras as $C) {
        echo '<div onclick="$.ActualizaFiltros(\'carrera\', '.$C['IdCarrera'].')" class="opcion_filtro selectDisable clearable" data-tipo="3" data-valor="'.$C['IdCarrera'].'">'
            .$C['Nombre'].' <i>('.$C['Cantidad'].')</i>'
            .'</div>';
    }
}

/**
 * Obtiene las Carreras según la Sede
 * @param $data
 */
function LoadSelectCarreraBySede($data) {
    $Carrera = new Carrera();
    $Carreras = $Carrera->GetBySede($data['idSede']);
    foreach ($Carreras as $C) {
        echo '<div onclick="$.ResultadosPorCarrera(this, '.$C['IdCarrera'].')" class="opcion_filtro selectDisable clearable selected" data-tipo="3" data-valor="'.$C['IdCarrera'].'">'
            .$C['Nombre'].' <i>('.$C['Cantidad'].')</i>'
            .'</div>';
    }
}

/**
 * Obtiene las Carreras para la Administracion
 * @param $data
 */
function LoadCarreraAdmin($data) {
    $Carrera = new Carrera();
    $Carreras = $Carrera->GetAll();

    echo '<table id="carrerastable" class="display compact">
        <thead>
            <tr>
                <th>Carrera</th>
                <th>Usos</th>
                <th></th>
            </tr>
        </thead>
        <tbody>';
    foreach ($Carreras as $C) {
        $formId = "formCarrera".$C['IdCarrera'];
        echo '<tr class="" data-idcarrera="'.$C['IdCarrera'].'">
                <td>
                    <span class="view">'.$C['Nombre'].'</span>
                    <form id="'.$formId.'">
                        <input type="hidden" name="idcarrera" value="'.$C['IdCarrera'].'">
                        <input class="adminput rework hidden" type="text" name="nombre" value="'.$C['Nombre'].'" maxlength="50" required>
                    </form>
                <td>
                    <span class="view">'.$C['Cantidad'].'</span>
                    <span class="adminput rework hidden"><i>'.$C['Cantidad'].'</i></span>
                </td>
                <td>
                    <div class="formbotones view">
                        <div class="icon small" onclick="$.EditThis(this)" title="Editar esta Carrera"><div class="edit right"></div></div>';
        if($C['Cantidad'] == "0")
            echo '<div class="icon small" onclick="$.DeleteCarrera('.$C['IdCarrera'].')" title="Eliminar esta Carrera"><div class="trash right"></div></div>';
        echo '</div>
                    <div class="formbotones rework hidden">
                        <div class="icon small" onclick="$.SaveCarrera(\''.$formId.'\')" title="Grabar"><div class="okey right"></div></div>
                        <div class="icon small" onclick="$.EditThis(this)" title="Cancelar"><div class="cancel2 right"></div></div>
                    </div>
                </td>
                </td>
            </tr>';
    }
    echo '</tbody>
    </table>';
}

/** ADMIN **/
/**
 * Retorna listado de Carreras en formato select
 * @param $data
 */
function CarreraSelect($data, $NombreCarrera="") {
    $Carrera = new Carrera();
    $Carreras = $Carrera->GetAll();
    echo '<select name="carrera" id="carreras" class="adminput required">'
        .'<option value="">Seleccionar Carrera</option>';
    foreach ($Carreras as $C) {
        if($NombreCarrera == $C['Nombre']) {
            echo '<option value="'.$C['IdCarrera'].'" selected>'.$C['Nombre'].'</option>';
        }
        else {
            echo '<option value="'.$C['IdCarrera'].'">'.$C['Nombre'].'</option>';
        }
    }
    echo '</select>';
}
/**
 * Recarga el formulario de creacion de Carrera
 * @param $data
 */
function LoadCarreraForm($data) {
    echo ''
        .'<div class="formulario">'
            .'<div class="titulo">Nueva Carrera</div>'
            .'<form id="formCarrera">'
                .'<input class="adminput" type="hidden" name="idcarrera" value="0">'
                .'<input class="adminput" type="text" name="nombre" placeholder="Ingrese nombre de la Carrera">'
                .'<div class="botonesformulario">'
                    .'<button class="clickable adminbtn cancel" onclick="$.LoadCarreraForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
                    .'<button class="clickable adminbtn success" onclick="$.SaveCarrera(\'formCarrera\');return false;">Crear <div class="icon small" title="Grabar"><div class="okey2 right"></div></div></button>'
                .'</div>'
            .'</form>'
        .'</div>'
        .'';
}

function CreateModifyCarrera($data) {
    $Carrera = new Carrera();
    $Carrera->CreateModify(
        $data['IdCarrera'],
        $data['Nombre']
    );
}

function RemoveCarrera($data) {
    $Carrera = new Carrera();
    $Carrera->Remove($data['IdCarrera']);
}