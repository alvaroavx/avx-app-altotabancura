<?php

/** ADMIN **/

/**
 * Obtiene las Vacunas para la Administracion
 * @param $data
 */
function LoadVacunaAdmin($data) {
    $Vacuna = new Vacuna();
    $Vacunas = $Vacuna->GetAll();
    echo '<table id="vacunastable" class="display compact">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Lote</th>
                <th>Usos</th>
                <th></th>
            </tr>
        </thead>
        <tbody>';
    foreach ($Vacunas as $V) {
        $Folio = ($V['Folio'] == '') ? 'Sin lote' : $V['Folio'];
        echo '<tr class="formulario" data-idvacuna="'.$V['IdVacuna'].'">
                <td>
                    <span class="view">'.$V['Vacuna'].'</span>
                    <input type="hidden" name="idvacuna" value="'.$V['IdVacuna'].'">
                    <input class="rework hidden" type="text" name="vacuna" value="'.$V['Vacuna'].'">
                </td>
                <td>
                    <span class="view">'.$Folio.'</span>
                    <input class="rework hidden" type="text" name="folio" value="'.$Folio.'">
                </td>
                <td>
                    <span class="view">'.$V['Cantidad'].'</span>
                    <span class="rework hidden"><i>'.$V['Cantidad'].'</i></span>
                </td>
                <td>
                    <div class="formbotones view">
                        <div class="icon small" onclick="$.EditThis(this)" title="Editar esta Vacuna"><div class="edit right"></div></div>';
        if($V['Cantidad'] == "0")
            echo '<div class="icon small" onclick="$.DeleteVacuna('.$V['IdVacuna'].')" title="Eliminar esta Vacuna"><div class="trash right"></div></div>';
        echo '</div>
                    </div>
                    <div class="formbotones rework hidden">
                        <div class="icon small" onclick="$.SaveVacuna(this)" title="Grabar"><div class="okey right"></div></div>
                        <div class="icon small" onclick="$.EditThis(this)" title="Cancelar"><div class="cancel2 right"></div></div>
                    </div>
                </td>
            </tr>';
    }
    echo '</tbody>
    </table>';
}

/**
 * Recarga el formulario de creacion de Vacuna
 * @param $data
 */
function LoadVacunaForm($data) {
    echo ''
        .'<div class="formulario">'
            .'<div class="titulo">Nueva Vacuna</div>'
            .'<input type="hidden" name="idvacuna" value="0">'
            .'<input class="adminput" type="text" name="vacuna" placeholder="Ingrese nombre de la Vacuna">'
            .'<input class="adminput" type="text" name="folio" placeholder="Ingrese Lote">'
            .'<div class="botonesformulario">'
                .'<button class="clickable adminbtn cancel" onclick="$.LoadVacunaForm()">Cancelar <div class="icon small" title="Cancelar"><div class="cancel right"></div></div></button>'
                .'<button class="clickable adminbtn success" onclick="$.SaveVacuna(this)">Crear <div class="icon small" title="Grabar"><div class="okey2 right"></div></div></button>'
            .'</div>'
        .'</div>'
        .'';
}

function VacunaSelect($data, $IdVacuna=0) {
    $Vacuna = new Vacuna();
    $Vacunas = $Vacuna->GetAll();
    echo '<select name="vacuna" id="vacunas" class="adminput required" onchange="$.UpdateVaccine(this)">'
        .'<option value="">Seleccionar Vacuna</option>';
    foreach ($Vacunas as $V) {
        if($V['IdVacuna'] == $IdVacuna) {
            echo '<option value="'.$V['IdVacuna'].'" selected>'.$V['Vacuna'].' <small>('.$V['Folio'].')</small></option>';
        }
        else {
            echo '<option value="'.$V['IdVacuna'].'">'.$V['Vacuna'].' <small>('.$V['Folio'].')</small></option>';
        }
    }
    echo '</select>';
}

function VacunasFormularioUsuario($data) {

    $Usuario = new Usuario();
    $Vacunas = $Usuario->GetVaccines($data['idusuario']);

    echo '<div class="titulo"><div class="icon small" onclick="" title="Vacunas">Vacunas <div class="syringe right"></div></div></div>';
    echo '<div class=">vacunarow" data-edited="false">'
        .'<div class="formbotones view">'
        .'<div class="icon small" onclick="$.AddVaccine('.$data['idusuario'].')" title="Agregar otra Vacuna"><div class="newblue right"></div></div>'
        .'</div>'
        .'</div>';
    if (!empty($Vacunas)) {
        foreach ($Vacunas as $V) {
            echo '<div class="vacunarow" data-idusuariovacuna="'.$V['IdUsuarioVacuna'].'" data-edited="false">'
                .'<input class="adminput" type="number" name="numero" value="'.$V['Dosis'].'" placeholder="Ingrese Dosis" onchange="$.UpdateVaccine(this)">';
            VacunaSelect($data, $V['IdVacuna']);
            echo '<input class="adminput" type="date" name="fecha" value="'.$V['Fecha']->format('Y-m-d').'" placeholder="Ingrese Fecha de Vacunación" onchange="$.UpdateVaccine(this)">'
                .'<div class="formbotones view">'
                    .'<div class="icon small" onclick="$.RemoveVaccine('.$V['IdUsuarioVacuna'].')" title="Eliminar esta Vacuna"><div class="trashblue right"></div></div>'
                    .'<div class="icon small" onclick="$.AddVaccine('.$data['idusuario'].')" title="Agregar otra Vacuna"><div class="newblue right"></div></div>'
                .'</div>'
                .'</div>';
        }
    }
    else {
        /*echo '<div class="vacunarow">'
            .'<div class="formbotones view">'
            .'<div class="icon small" onclick="$.AddVaccine('.$data['idusuario'].')" title="Agregar otra Vacuna"><div class="newblue right"></div></div>'
            .'</div>'
            .'</div>';*/
    }
}

/**
 * Agrega una vacuna al formulario del usuario
 * @param $data
 */
function AddVaccine($data) {
    $Vacuna = new Vacuna();
    $V = $Vacuna->GetLast($data['IdUsuario'])[0];
    echo '<div class="vacunarow" data-idusuariovacuna="'.$V['IdUsuarioVacuna'].'" data-edited="false">'
            .'<input class="adminput" type="number" name="numero" value="'.$V['Numero'].'" placeholder="Ingrese Dosis">';
    VacunaSelect($data, $V['IdVacuna']);
    echo '<input class="adminput" type="date" name="fecha" value="'.$V['Fecha']->format('Y-m-d').'" placeholder="Ingrese Fecha de Vacunación">'
            .'<div class="formbotones view">'
                .'<div class="icon small" onclick="$.RemoveVaccine('.$V['IdUsuarioVacuna'].')" title="Eliminar esta Vacuna"><div class="trashblue right"></div></div>'
                .'<div class="icon small" onclick="$.AddVaccine('.$data['IdUsuario'].')" title="Agregar otra Vacuna"><div class="newblue right"></div></div>'
            .'</div>'
        .'</div>';
}

function CreateModifyVacuna($data) {
    $Vacuna = new Vacuna();
    $Vacuna->CreateModify(
        $data['IdVacuna'],
        $data['Vacuna'],
        $data['Lote']
    );
}

function RemoveVacuna($data) {
    $Vacuna = new Vacuna();
    $Vacuna->Remove($data['IdVacuna']);
}

function UserVaccine($data) {
    $Vacuna = new Vacuna();
    $Vacuna->UserVaccine(
        $data['IdUsuarioVacuna'],
        $data['IdVacuna'],
        $data['IdUsuario'],
        $data['Numero'],
        $data['Fecha']
    );
}
function DetachVaccine($data) {
    $Vacuna = new Vacuna();
    $Vacuna->DetachVaccine(
        $data['IdUsuarioVacuna']
    );
}