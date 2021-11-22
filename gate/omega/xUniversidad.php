<?php

/**
 * Obtiene las universidades segun el Docente
 * @param $data
 */
function LoadSelectUniversidad($data) {
    $Universidad = new Universidad();
    $Universidades = $Universidad->Get(Sesion('universidad'));
    foreach ($Universidades as $U) {
        /* Se actualiza el filtro directamente */
        echo '<script>'
            .'$.ActualizaFiltros(\'universidad\','.$U['IdUniversidad'].');'
            .'</script>';
    }
}

/**
 * Obtiene todas las universidades (Admin)
 * @param $data
 */
function LoadSelectUniversidadAll($data) {
    $Universidad = new Universidad();
    $Universidades = $Universidad->GetAll();
    foreach ($Universidades as $U) {
        echo '<div onclick="$.ActualizaFiltros(\'universidad\', '.$U['IdUniversidad'].')" class="opcion_filtro selectDisable clearable" data-tipo="1" data-valor="'.$U['IdUniversidad'].'">'
            .$U['Nombre'].'<i>('.$U['Cantidad'].')</i>'
            .'</div>';
    }
    echo '<script>
            $( ".opcion_filtro" ).click(function() {
                $(this).parent().parent().find(".nombre .botones div.icon").click();
            });
            </script>';
    }

/** ADMIN **/
/**
 * Obtiene las Universidades y Sedes para la Administracion
 * @param $data
 */
function LoadUniversidadAdmin($data) {
    $Universidad = new Universidad();
    $Universidades = $Universidad->GetAll();

    echo '<table id="universidadtable" class="display compact">
        <thead>
            <tr>
                <th>Universidad</th>
                <th>Usos</th>
                <th></th>
            </tr>
        </thead>
        <tbody>';
    foreach ($Universidades as $U) {
        $formId = "formUniversidad".$U['IdUniversidad'];
        echo '<tr class="" data-iduniversidad="'.$U['IdUniversidad'].'">
                <td>
                    <span class="view">'.$U['Nombre'].'</span>
                    <form id="'.$formId.'">
                        <input type="hidden" name="iduniversidad" value="'.$U['IdUniversidad'].'">
                        <input class="adminput rework hidden" type="text" name="nombre" value="'.$U['Nombre'].'" maxlength="50" required>
                    </form>
                </td>
                <td>
                    <span class="view">'.$U['Cantidad'].'</span>
                    <span class="adminput rework hidden"><i>'.$U['Cantidad'].'</i></span>
                </td>
                <td>
                    <div class="formbotones view">
                        <div class="icon small" onclick="$.EditThis(this)" title="Editar esta Universidad"><div class="edit right"></div></div>';
        if($U['Cantidad'] == "0")
            echo '<div class="icon small" onclick="$.DeleteUniversidad('.$U['IdUniversidad'].')" title="Eliminar esta Universidad"><div class="trash right"></div></div>';
        echo '</div>
                    <div class="formbotones rework hidden">
                        <div class="icon small" onclick="$.SaveUniversidad(\''.$formId.'\')" title="Grabar"><div class="okey right"></div></div>
                        <div class="icon small" onclick="$.EditThis(this)" title="Cancelar"><div class="cancel2 right"></div></div>
                    </div>
                </td>
            </tr>';
    }
    echo '</tbody>
    </table>';
}

/**
 * Recarga el formulario de creacion de universidad
 * @param $data
 */
function LoadUniversidadForm($data) {
    echo ''
        .'<div class="formulario">'
            .'<div class="titulo">Nueva Universidad</div>'
            .'<form id="formUniversidad">'
                .'<input class="adminput" type="hidden" name="iduniversidad" value="0">'
                .'<input class="adminput" type="text" name="nombre" placeholder="Ingrese nombre de Universidad" maxlength="50" required>'
                .'<div class="botonesformulario">'
                    .'<button class="clickable adminbtn cancel" onclick="$.LoadUniversidadForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
                    .'<button class="clickable adminbtn success" onclick="$.SaveUniversidad(\'formUniversidad\');return false;">Crear <div class="icon small" title="Grabar"><div class="okey2 right"></div></div></button>'
                .'</div>'
            .'</form>'
        .'</div>'
        .'';
}

/**
 * Retorna listado de Universidades en formato select
 * @param $data
 */
function UniversidadSelect($data) {
    $Universidad = new Universidad();
    $Universidades = $Universidad->GetAll();
    echo '<select name="universidad" id="universidades" class="adminput">'
        .'<option value="">Seleccionar Universidad</option>';
    foreach ($Universidades as $U) {
        echo '<option value="'.$U['IdUniversidad'].'">'.$U['Nombre'].'</option>';
    }
    echo '</select>';
}

function UniversidadSelectRework($data, $Univ) {
    $Universidad = new Universidad();
    $Universidades = $Universidad->GetAll();
    echo '<select name="universidad" id="universidades" class="rework hidden">';
    foreach ($Universidades as $U) {
        if($Univ == $U['Nombre']) {
            echo '<option value="'.$U['IdUniversidad'].'" selected>'.$U['Nombre'].'</option>';
        }
        else {
            echo '<option value="'.$U['IdUniversidad'].'">'.$U['Nombre'].'</option>';
        }

    }
    echo '</select>';
}

function CreateModifyUniversidad($data) {
    $Universidad = new Universidad();
    $Universidad->CreateModify(
        $data['IdUniversidad'],
        $data['Nombre']
    );
}

function RemoveUniversidad($data) {
    $Universidad = new Universidad();
    $Universidad->Remove($data['IdUniversidad']);
}