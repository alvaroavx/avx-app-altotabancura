/**
 * JS Carrera
 * **/

$.LoadSelectCarrera = function() {
    $.ajax({
        data: $.RawData("LoadSelectCarrera"),
        success: function (response) {
            $("#carreras").html(response);
        },
        complete: function(){}
    });
};

/** ADMINISTRACION CARRERAS **/
/**
 * Recarga el listado de Sedes en el Admin
 * @constructor
 */
$.LoadCarreraAdmin = function(){
    $.ajax({
        data: $.RawData("LoadCarreraAdmin"),
        success: function (response) {
            $("#carr_admin").html(response);
        },
        complete: function(){
            $('#carrerastable').DataTable({
                paging: true,
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sSearch": "Filtrar Carrera:",
                    "sInfo": "Mostrando _TOTAL_ carreras",
                    "sInfoEmpty": "Mostrando 0 carreras",
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
/**
 * Muestra y oculta el formulario de nueva Sede
 * @constructor
 */
$.LoadCarreraForm = function() {
    var formulario = $("#carreraform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    else {
        $.ajax({
            data: $.RawData("LoadCarreraForm"),
            success: function (response) {
                formulario.html(response);
                formulario.addClass("open");
            },
            complete: function () {

                formulario.find('input').focus();
            }
        });
    }
};

$.SaveCarrera = function(formId) {
    var form = $('#'+formId);
    var carrera = [];
    if($("#carreraform").hasClass("open"))
        $.LoadCarreraForm();
    $.each(form.serializeArray(), function(i, field) {
        var input = $('input[name='+field.name+']');
        field.value = $.trim(field.value);
        switch(field.name){
            case "idcarrera": carrera["idcarrera"] = field.value; break;
            case "nombre": carrera["nombre"] = field.value; break;
            default:
                console.log("default: "+field.value);
                break;
        }
    });
    $.ajax({
        data: $.RawData('CreateModifyCarrera', {
            'IdCarrera': carrera["idcarrera"],
            'Nombre': carrera["nombre"]
        }),
        success: function () {
            $.LoadCarreraAdmin();
        },
        complete: function(){}
    });
};

$.DeleteCarrera = function(idCarrera) {
    $.ajax({
        data: $.RawData('RemoveCarrera', {
            'IdCarrera': idCarrera
        }),
        success: function () {
            $.LoadCarreraAdmin();
        },
        complete: function(){}
    });
};