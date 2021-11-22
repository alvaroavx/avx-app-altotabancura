/**
 * Script Usuario
 * AlvaxVargas 2019
 */

/**
 * Vista de los alumnos
 * @constructor
 */
$.VistaAlumno = function(){
    $.FootPrint('certificado');
    $.ajax({
        data: $.RawData("VistaAlumno"),
        success: function (response) {
            $("#midblock").html(response);
        },
        complete: function(){}
    });
};

/**
 * Carga la vista de los docentes
 * @constructor
 */
$.VistaDocente = function(){
    $.FootPrint('alumnos');
    $.ajax({
        data: $.RawData("VistaDocente"),
        success: function (response) {
            $("#midblock").html(response);
        },
        complete: function(){}
    });
};

/**
 * Carga la seccion superior
 * @constructor
 */
$.LoadTopbar = function(){
    $.ajax({
        data: $.RawData("LoadTopbar"),
        success: function (response) {
            $("#topbar").html(response);
        },
        complete: function(){}
    });
};

/**
 * Carga el menu lateral
 * @constructor
 */
$.LoadSidebar = function(){
    $.ajax({
        data: $.RawData("LoadSidebar"),
        success: function (response) {
            $("#sidebar").html(response);
        },
        complete: function(){}
    });
};

/**
 * Busca resultados con la info del input de usuario
 * @param e
 * @param elem
 * @constructor
 */
$.BuscarEnter = function(e) {
    if (e.key === "Enter") {
        $.LoadResultadosPaginado();
    }
};

/**
 * Muestra y oculta un filtro del menu lateral
 * @param elem
 * @constructor
 */
$.ToggleFiltro = function(elem) {
    var valores = $(elem).parent().parent().siblings();
    var icon = $(elem).children();
    if(valores.css('display') === 'none') {
        valores.css('display', 'block');
        icon.removeClass('right-caret');
        icon.addClass('down-caret');
    }
    else {
        valores.css('display', 'none');
        icon.removeClass('down-caret');
        icon.addClass('right-caret');
    }
};

/**
 * Carga los resultados
 * @constructor
 */
$.LoadResultados = function(){
    $(".cargando").addClass("up");
    $.FootPrint("resultados");
    var filtros = [];
    $(".opcion_filtro.selected").each(function(){
        filtros.push({"idtipo": $(this).attr("data-tipo"),"valor": $(this).attr("data-valor")});
    });
    $.ajax({
        data: $.RawData("LoadResultados",
            {
                "busqueda" : $.IsNull($("#alumno").val().trim(),""),
                "filtros" : JSON.stringify(filtros)
            }
        ),
        success: function (response) {
            $("#resultsrow").html(response);
            $('#resultstable').DataTable({
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningun dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Ultimo",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });
            $.LoadTopbar();
            $(".cargando").removeClass("up");
        },
        complete: function(){}
    });
};

$.ActualizaFiltros = function(tipo, valor){
    $("#filtros_data div[data-tipo='"+tipo+"']").attr("data-valor", valor);
    if(tipo === 'universidad' || tipo === 'carrera' || tipo === 'sede' || tipo === 'tamanopagina')
        $("#filtros_data div[data-tipo='numeropagina']").attr("data-valor", 1);
    $.LoadResultadosPaginado();
};
/**
 * Version 2.0 de la carga de resultados
 * @constructor
 */
$.LoadResultadosPaginado = function(){
    $(".cargando").addClass("up");
    $.FootPrint("resultados2");

    $.ajax({
        data: $.RawData("LoadResultadosPaginado",
            {
                "busqueda" : $.IsNull($("#alumno").val().trim(),""),
                "iduniversidad" : $.IsNull($("#filtros_data div[data-tipo='universidad']").attr("data-valor"),""),
                "idsede" : $.IsNull($("#filtros_data div[data-tipo='sede']").attr("data-valor"),""),
                "idcarrera" : $.IsNull($("#filtros_data div[data-tipo='carrera']").attr("data-valor"),""),
                "fechavacunadesde" : $.IsNull($("#filtros_data div[data-tipo='fechavacunadesde']").attr("data-valor"),""),
                "fechavacunahasta" : $.IsNull($("#filtros_data div[data-tipo='fechavacunahasta']").attr("data-valor"),""),
                "numeropagina" : $.IsNull($("#filtros_data div[data-tipo='numeropagina']").attr("data-valor"), 1),
                "tamanopagina" : $.IsNull($("#filtros_data div[data-tipo='tamanopagina']").attr("data-valor"), 10),
                "orderby" : $.IsNull($("#filtros_data div[data-tipo='orderby']").attr("data-valor"),""),
            }
        ),
        success: function (response) {
            $("#resultsrow").html(response);
            $('#resultstable').DataTable({
                "searching": false,
                "paging": false,
                "ordering": false,
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningun dato disponible en esta tabla",
                    //"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfo": "",
                    //"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoEmpty": "",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                }
            });
            $.LoadTopbar();
            $(".cargando").removeClass("up");
        },
        complete: function(){
            $.LoadPaginacion();
        }
    });
};

$.LoadPaginacion = function(){
    $.ajax({
        data: $.RawData("LoadPaginacion", {
            "busqueda" : $.IsNull($("#alumno").val().trim(),""),
            "iduniversidad" : $.IsNull($("#filtros_data div[data-tipo='universidad']").attr("data-valor"),""),
            "idsede" : $.IsNull($("#filtros_data div[data-tipo='sede']").attr("data-valor"),""),
            "idcarrera" : $.IsNull($("#filtros_data div[data-tipo='carrera']").attr("data-valor"),""),
            "fechavacunadesde" : $.IsNull($("#filtros_data div[data-tipo='fechavacunadesde']").attr("data-valor"),""),
            "fechavacunahasta" : $.IsNull($("#filtros_data div[data-tipo='fechavacunahasta']").attr("data-valor"),""),
            "numeropagina" : $.IsNull($("#filtros_data div[data-tipo='numeropagina']").attr("data-valor"), 1),
            "tamanopagina" : $.IsNull($("#filtros_data div[data-tipo='tamanopagina']").attr("data-valor"), 20),
            "orderby" : $.IsNull($("#filtros_data div[data-tipo='orderby']").attr("data-valor"),"")
        }),
        success: function (response) {
            $("#paginacion").html(response);
        },
        complete: function(){}
    });
};

$.ResultadosPorSede = function (elem, idSede) {
    if($(elem).hasClass('selected')) {
        $.CleanFilters();
    }
    else {
        $(elem).siblings().addClass('hidden');
        setTimeout(function () {
            $.LoadResultadosPaginado();
        }, 500);
        $.ajax({
            data: $.RawData("LoadSelectCarreraBySede",
                {"idSede": idSede}
            ),
            success: function (response) {
                $.ActualizaFiltros('sede', idSede);
                $("#select_carreras").html(response);
            },
            complete: function () {
            }
        });
    }
};
$.ResultadosPorCarrera = function (elem, idCarrera) {
    if($(elem).hasClass('selected')) {
        $.CleanFilters();
    }
    else {
        $(elem).siblings().addClass('hidden');
        setTimeout(function () {
            $.LoadResultadosPaginado();
        }, 500);
        $.ajax({
            data: $.RawData("LoadSelectSedeByCarrera",
                {"idCarrera": idCarrera}
            ),
            success: function (response) {
                $.ActualizaFiltros('carrera', idCarrera);
                $("#select_sedes").html(response);
            },
            complete: function () {
            }
        });
    }
};

/**
 * Limpia los filtros
 * @constructor
 */
$.CleanFilters = function(){
    $('input#alumno').val('');
    $('input[type=date]').val('');
    $(".opcion_filtro.selected.clearable").removeClass("selected");
    $('.opcion_filtro').removeClass('hidden');
    $.ajax({
        data: $.RawData("LoadFilterData"),
        success: function (response) {
            $('#filtros_data').html(response);
            $.LoadSidebar();
        },
        complete: function () {

        }
    });
    //$.LoadSidebar();


};

/**
 * Chequea y muestra o no el boton del certificado
 * @constructor
 */
$.CheckClick = function() {
    var btn_certificado = $('#certificado_button');
    var btn_certificado_tabla = $('#certificado_masivo_button');
    var usuarioadmin = $('#centerrow');
    var checkboxes = $("input[name='usuario']:checked");
    if(checkboxes.length > 0) {
        btn_certificado.removeClass('hidden');
        usuarioadmin.removeClass('hidden');
    }
    else {
        if(!btn_certificado.hasClass('hidden')) {
            btn_certificado.addClass('hidden');
            usuarioadmin.addClass('hidden');
        }
    }
    if(checkboxes.length > 5) {
        btn_certificado_tabla.removeClass('hidden');
    }
    else {
        btn_certificado_tabla.addClass('hidden');
    }
};

/**
 * Des/selecciona todos los checkbox
 * @param elem
 * @constructor
 */
$.SelectAll = function (elem) {
    if($(elem).is(':checked')) {
        $('.check_usuario').prop('checked', true);
    }
    else {
        $('.check_usuario').prop('checked', false);
    }
    $.CheckClick();
};

/** ADMINISTRACION USUARIOS **/
/**
 * Muestra y oculta el formulario del Usuario
 * @constructor
 */
$.LoadAlumnoForm = function() {
    var formulario = $("#usuarioform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    $.ajax({
        data: $.RawData("LoadAlumnoForm"),
        success: function (response) {
            formulario.html(response);
            formulario.addClass("open");
        },
        complete: function () {
            formulario.find('input').first().focus();
        }
    });
};
$.LoadDocenteForm = function() {
    var formulario = $("#docenteform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    $.ajax({
        data: $.RawData("LoadDocenteForm"),
        success: function (response) {
            formulario.html(response);
            formulario.addClass("open");
        },
        complete: function () {
            formulario.find('input').first().focus();
        }
    });
};
$.CloseAlumnoForm = function() {
    var formulario = $("#usuarioform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
};
$.CloseDocenteForm = function() {
    var formulario = $("#docenteform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
};
$.SaveUsuario = function() {
    var form = $('#formAlumno');
    var usuario = [];
    var empty = false;
    var saveVaccines = false;
    var isNew = false;
    /* solo graba vacunas si el formulario esta abierto */
    var vacForm = $('#vacunasformulario');
    if(vacForm.hasClass('open')) {
        $.each($('#vacunasformulario').find('select[name=vacuna]'), function () {
            $(this).css("border-color","inherit");
            if($(this).val()===""){
                $(this).css("border-color","var(--rojo)");
                empty = true;
            }
        });
        $.each($('#vacunasformulario').find('input[name=numero]'), function () {
            $(this).css("border-color","inherit");
            if($(this).val()===""){
                $(this).css("border-color","var(--rojo)");
                empty = true;
            }
        });
        $.each($('#vacunasformulario').find('input[name=fecha]'), function () {
            $(this).css("border-color","inherit");
            if($(this).val()===""){
                $(this).css("border-color","var(--rojo)");
                empty = true;
            }
        });
        if(!empty) {
            $.SaveVaccines();
            saveVaccines = true;
        }
    }
    $.each(form.serializeArray(), function(i, field) {
        var input = $('input[name='+field.name+']');
        field.value = $.trim(field.value);
        if(field.value === "" && field.name !== "fechanacimiento") {
            /*console.log("field: "+field.name+" value: "+field.value);*/
            $('#formAlumno .required').css("border-color",  "var(--azul-principal2)");
            empty = true;
            $('#formAlumno .required').each(function(){
                $(this).css("border-color","inherit");
                if($(this).val() === ""){
                    $(this).css("border-color",  "var(--rojo)");
                }
            });
            $('#formAlumno option:selected').each(function() {
                $(this).css("border-color","inherit");
                if ($(this).val() === "") {
                    $(this).css("border-color", "var(--rojo)");
                }
            });
            return false;
        }
        switch (field.name) {
            case "idusuario":
                usuario["idusuario"] = field.value;
                break;
            case "nombres":
                usuario["nombres"] = field.value;
                break;
            case "apellidos":
                usuario["apellidos"] = field.value;
                break;
            case "username":
                usuario["username"] = field.value ? field.value : '';
                break;
            case "rut":
                usuario["rut"] = field.value ? field.value : '';
                break;
            case "fechanacimiento":
                usuario["fechanacimiento"] = field.value ? field.value : '';
                break;
            case "carrera":
                usuario["idcarrera"] = field.value ? field.value : '';
                break;
            case "sede":
                usuario["idsede"] = field.value ? field.value : '';
                break;
            case "password":
                usuario["password"] = field.value ? field.value : '';
                break;
            default:
                console.log("default: " + field.value);
                break;
        }
    });
    if(usuario["idusuario"]==="0")
        isNew = true;
    if(!empty) {
        $.ajax({
            data: $.RawData('CreateModifyUsuario', {
                'IdUsuario': usuario["idusuario"],
                'Nombres': usuario["nombres"],
                'Apellidos': usuario["apellidos"],
                'Username': "",
                'Rut': usuario["rut"],
                'FechaNacimiento': usuario["fechanacimiento"],
                'IdCarrera': usuario["idcarrera"],
                'IdSede': usuario["idsede"],
                'Password': "",
                'IdTipoUsuario': 1 /* Alumno */
            }),
            success: function (idUsuario) {
                //console.log('idUsuario: ' + idUsuario);
                if (isNew) {
                    $.LoadUsuario(usuario["rut"], "new");
                }
                else if(saveVaccines) {
                    $.LoadUsuario(usuario["rut"], "save");
                }
                else {
                    $.LoadUsuario(usuario["rut"], "edit");
                }
            },
            complete: function () {
            }
        });
    }
    else {
        $.UpdateMessageForm('required');
    }
};
$.SaveDocente = function() {
    var form = $('#formDocente');
    var usuario = [];
    var empty = false;
    var isNew = false;

    $.each(form.serializeArray(), function(i, field) {
        var input = $('input[name='+field.name+']');
        field.value = $.trim(field.value);
        if(field.value === "" && field.name !== "fechanacimiento") {
            $('#formDocente .required').css("border-color",  "var(--azul-principal2)");
            empty = true;
            $('#formDocente .required').each(function(){
                $(this).css("border-color","inherit");
                if($(this).val() === ""){
                    $(this).css("border-color",  "var(--rojo)");
                }
            });
            $('#formDocente option:selected').each(function() {
                $(this).css("border-color","inherit");
                if ($(this).val() === "") {
                    $(this).css("border-color", "var(--rojo)");
                }
            });
            return false;
        }
        console.log(field);
        switch (field.name) {
            case "idusuario":
                usuario["idusuario"] = field.value;
                break;
            case "nombres":
                usuario["nombres"] = field.value;
                break;
            case "apellidos":
                usuario["apellidos"] = field.value;
                break;
            case "username":
                usuario["username"] = field.value ? field.value : '';
                break;
            case "rut":
                usuario["rut"] = field.value ? field.value : '';
                break;
            case "fechanacimiento":
                usuario["fechanacimiento"] = field.value ? field.value : '';
                break;
            case "carrera":
                usuario["idcarrera"] = field.value ? field.value : '';
                break;
            case "institucion":
                usuario["idsede"] = field.value ? field.value : '';
                break;
            case "password":
                usuario["password"] = field.value == '******' ? '' : field.value;
                break;
            default:
                console.log("default: " + field.value);
                break;
        }
    });
    if(usuario["idusuario"]==="0")
        isNew = true;
    if(!empty) {
        $.ajax({
            data: $.RawData('CreateModifyUsuario', {
                'IdUsuario': usuario["idusuario"],
                'Nombres': usuario["nombres"],
                'Apellidos': usuario["apellidos"],
                'Username': usuario["username"],
                'Rut': "",
                'FechaNacimiento': "",
                'IdCarrera': 37, /* Sin Carrera */
                'IdSede': usuario["idsede"],
                'Password': usuario["password"],
                'IdTipoUsuario': 2 /* Docente */
            }),
            success: function (idUsuario) {
                //console.log('idUsuario: ' + idUsuario);
                //$.LoadAdmin();
                //$.LoadDocente(usuario["rut"]);
                $.CloseDocenteForm();
                $.LoadDocenteAdmin();
            },
            complete: function () {
            }
        });
    }
    else {
        $.UpdateMessageForm('required');
    }
};

$.LoadDocenteAdmin = function(){
    $.ajax({
        data: $.RawData("LoadDocenteAdmin"),
        success: function (response) {
            $("#doce_admin").html(response);
        },
        complete: function(){
            $('#docentetable').DataTable({
                paging: true,
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sSearch": "Filtrar Docente:",
                    "sInfo": "Mostrando _TOTAL_ docentes",
                    "sInfoEmpty": "Mostrando 0 docentes",
                    "sZeroRecords": "No se encontraron resultados",
                    "sInfoFiltered": "(filtrado de un total de _MAX_)",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Ultimo",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                }
            });
        }
    });
};
$.LoadUsuario = function(usuario, action) {
    var formulario = $("#usuarioform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    $.ajax({
        data: $.RawData("LoadUsuario", {
            'usuario': usuario
        }),
        success: function (response) {
            formulario.html(response);
            formulario.addClass("open");
            if(action!==""){
                $.UpdateMessageForm(action);
                if (action==="save"||action==="edit") {
                    $('#editvaccines').click();
                }
            }
        },
        complete: function () {
            formulario.find('input').first().focus();
        }
    });
};
$.LoadDocente = function(idUsuario) {
    var formulario = $("#docenteform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    $.ajax({
        data: $.RawData("LoadDocente", {
            'usuario': idUsuario
        }),
        success: function (response) {
            formulario.html(response);
            formulario.addClass("open");
        },
        complete: function () {
            formulario.find('input').first().focus();
        }
    });
};
$.EditUsuario = function(idUsuario) {
};
$.DeleteUsuario = function(idUsuario) {
    $.ajax({
        data: $.RawData('RemoveUsuario', {
            'IdUsuario': idUsuario
        }),
        success: function () {
            $('#resultstable tr[data-idusuario="'+idUsuario+'"]').remove();
        },
        complete: function(){}
    });
};
$.DeleteDocente = function(idUsuario) {
    $.ajax({
        data: $.RawData('RemoveUsuario', {
            'IdUsuario': idUsuario
        }),
        success: function () {
            //$('#docentetable tr[data-idusuario="'+idUsuario+'"]').remove();
            $.LoadDocenteAdmin();
        },
        complete: function(){}
    });
};
/**
 * Actualiza el mensaje del formulario del usuario
 * @constructor
 */
$.UpdateMessageForm = function (action) {
    $.ajax({
        data: $.RawData('UserMessage', {
            'action': action
        }),
        success: function (response) {
            $('#usermessage').html(response);
        },
        complete: function(){}
    });
};