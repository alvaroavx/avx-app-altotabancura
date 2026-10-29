<?php

/**
 * Obtiene las Sedes según la Universidad (Docente)
 * @param $data
 */
function LoadSelectSede($data) {
    $Sede = new Sede();
    $Sedes = $Sede->Get(Sesion('universidad'));
    foreach ($Sedes as $S) {
        echo '<div onclick="$.ResultadosPorSede(this, '.$S['IdSede'].')" class="opcion_filtro selectDisable clearable" data-tipo="2" data-valor="'.$S['IdSede'].'">'.$S['Nombre'].' <i>('.$S['Cantidad'].')</i></div>';
    }
}

/**
 * Obtiene todas las Sedes (Admin)
 * @param $data
 */
function LoadSelectSedeAll($data) {
    $Sede = new Sede();
    $Sedes = $Sede->GetAll();
    foreach ($Sedes as $S) {
        echo '<div onclick="$.ActualizaFiltros(\'sede\', '.$S['IdSede'].')" class="opcion_filtro selectDisable clearable" data-tipo="2" data-valor="'.$S['IdSede'].'">'.$S['Nombre'].' 
                   <small>('.$S['Universidad'].')</small>
                   <i>('.$S['Cantidad'].')</i>
              </div>';
    }
}

/**
 * Retorna listado de Sedes por Universidad (para formulario Docente)
 * @param $data
 */
function LoadSelectSedeByUniversidad($data, $IdUniversidad="") {
    $Sede = new Sede();
    $Sedes = $Sede->GetAllByUniversidad();
    echo '<select name="institucion" id="instituciones" class="adminput required">'
        .'<option value="">Seleccionar Institución</option>';
    foreach ($Sedes as $S) {
        if($IdUniversidad == $S['IdUniversidad']) {
            echo '<option value="'.$S['IdSede'].'" selected>'.$S['Nombre'].'</option>';
        }
        else {
            echo '<option value="'.$S['IdSede'].'">'.$S['Nombre'].'</option>';
        }
    }
    echo '</select>';
}

function LoadSelectSedeByUniversidadRework($data, $IdSede="") {
    $Sede = new Sede();
    $Sedes = $Sede->GetAllByUniversidad();
    echo '<select name="institucion" id="instituciones" class=" rework hidden">'
        .'<option value="">Seleccionar Institución</option>';
    foreach ($Sedes as $S) {
        if($IdSede == $S['IdSede']) {
            echo '<option value="'.$S['IdSede'].'" selected>'.$S['Nombre'].'</option>';
        }
        else {
            echo '<option value="'.$S['IdSede'].'">'.$S['Nombre'].'</option>';
        }
    }
    echo '</select>';
}

/**
 * Obtiene las Carreras filtrado por Sede
 * @param $data
 */
function LoadSelectSedeByCarrera($data) {
    $Sede = new Sede();
    $Sedes = $Sede->GetByCarrera($data['idCarrera']);
    foreach ($Sedes as $S) {
        echo '<div onclick="$.ResultadosPorSede(this, '.$S['IdSede'].')" class="opcion_filtro selectDisable clearable selected" data-tipo="2" data-valor="'.$S['IdSede'].'">'.$S['Nombre'].' <i>('.$S['Cantidad'].')</i></div>';
    }
}

/** ADMIN **/
/**
 * Retorna listado de Sedes en formato select
 * @param $data
 */
function SedeSelect($data, $NombreSede="", $Universidad="") {
    $Sede = new Sede();
    $Sedes = $Sede->GetAll();
    echo '<select name="sede" id="sedes" class="adminput required">'
        .'<option value="">Seleccionar Sede</option>';
    foreach ($Sedes as $S) {
        if($NombreSede == $S['Nombre'] && $Universidad == $S['Universidad']) {
            echo '<option value="'.$S['IdSede'].'" selected>'.$S['Nombre'].' <small>('.$S['Universidad'].')</small></option>';
        }
        else {
            echo '<option value="'.$S['IdSede'].'">'.$S['Nombre'].' <small>('.$S['Universidad'].')</small></option>';
        }
    }
    echo '</select>';
}
/**
 * Obtiene las Universidades y Sedes para la Administracion
 * @param $data
 */
function LoadSedeAdmin($data) {
    $Sede = new Sede();
    $Sedes = $Sede->GetAll();
    /*foreach ($Sedes as $S) {
        echo '<div class="opcion_filtro selectDisable clearable" data-tipo="2" data-valor="'.$S['IdSede'].'">'.$S['Nombre'].' <small>('.$S['Universidad'].')</small><i>('.$S['Cantidad'].')</i></div>';
    }*/
    echo '<table id="sedetable" class="display compact">
        <thead>
            <tr>
                <th>Sede</th>
                <th>Universidad</th>
                <th>Usos</th>
                <th></th>
            </tr>
        </thead>
        <tbody>';
    foreach ($Sedes as $S) {
        echo '<tr class="formulario" data-idsede="'.$S['IdSede'].'">
                <td>
                    <span class="view">'.$S['Nombre'].'</span>
                    <input type="hidden" name="idsede" value="'.$S['IdSede'].'">
                    <input class="rework hidden" type="text" name="nombre" value="'.$S['Nombre'].'" maxlength="100" required>
                </td>
                <td>
                    <span class="view">'.$S['Universidad'].'</span>';
        UniversidadSelectRework($data, $S['Universidad']);
        echo   '</td>
                <td>
                    <span class="view">'.$S['Cantidad'].'</span>
                    <span class="rework hidden"><i>'.$S['Cantidad'].'</i></span>
                </td>
                <td>
                    <div class="formbotones view">
                        <div class="icon small" onclick="$.EditThis(this)" title="Editar esta Sede"><div class="edit right"></div></div>';
        if($S['Cantidad'] == "0")
            echo '<div class="icon small" onclick="$.DeleteSede('.$S['IdSede'].')" title="Eliminar esta Sede"><div class="trash right"></div></div>';
        echo '</div>
                    <div class="formbotones rework hidden">
                        <div class="icon small" onclick="$.SaveSede(this)" title="Grabar"><div class="okey right"></div></div>
                        <div class="icon small" onclick="$.EditThis(this)" title="Cancelar"><div class="cancel2 right"></div></div>
                    </div>
                </td>
            </tr>';
    }
    echo '</tbody>
    </table>';
}
/**
 * Recarga el formulario de creacion de sede
 * @param $data
 */
function LoadSedeForm($data) {
    echo ''
        .'<div class="formulario">'
        .'<div class="titulo">Nueva Sede</div>';
    UniversidadSelect($data);
    echo '<input type="hidden" name="idsede" value="0">'
        .'<input class="adminput" type="text" name="nombre" placeholder="Ingrese nombre de la Sede">'
            .'<div class="botonesformulario">'
                .'<button class="clickable adminbtn cancel" onclick="$.LoadSedeForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
                .'<button class="clickable adminbtn success" onclick="$.SaveSede(this)">Crear <div class="icon small" title="Grabar"><div class="okey2 right"></div></div></button>'
            .'</div>'
        .'</div>'
        .'';
}

function CreateModifySede($data) {
    $Sede = new Sede();
    $Sede->CreateModify(
        $data['IdSede'],
        $data['IdUniversidad'],
        $data['Nombre']
    );
}

function RemoveSede($data) {
    $Sede = new Sede();
    $Sede->Remove($data['IdSede']);
}