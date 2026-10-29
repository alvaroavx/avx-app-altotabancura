/**
 * DASHBOARD JS
 * Archivo encargado de las vistas dentro de la aplicacion, como panel de control
*/

/**
 * Carga pagina inicial
 * @constructor
 */
$.LoadDashboard = function(){
    $.FootPrint('repositorio');
    $.ajax({
        data: $.RawData("LoadDashboard"),
        success: function (response) {
            $("#midblock").html(response);
        },
        complete: function(){
            $.GoUp($("#midblock"), 0);

        }
    });
};

/**
 * Recarga contenido del menu lateral
 * @constructor
 */
$.LoadMenu = function(){
    $.ajax({
        data: $.RawData("LoadMenu"),
        success: function (response) {
            $("#topbar").html(response);
        },
        complete: function(){
            $.GoUp($("#midblock"), 0);
        }
    });
};

/**
 * Muestra u oculta secciones del menu lateral
 * @param elem
 * @constructor
 */
$.ToggleSeccion = function(elem, seccion) {
    var contenido = $('#contenido_'+seccion);
    var icon = $(elem).children();

    if(contenido.css('display') === 'none') {
        contenido.css('display', 'block');
        icon.removeClass('right-caret');
        icon.addClass('down-caret');
    }
    else {
        contenido.css('display', 'none');
        icon.removeClass('down-caret');
        icon.addClass('right-caret');
    }
};