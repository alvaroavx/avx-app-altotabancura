/**
 * CORE JS
 * Archivo administrador central de Javascript
*/

$.LoadCore = function(){
    $.LoadMidBlock(0);
    $.LoadModal(0);
};

$.LoadMidBlock = function(init){
    var constructor = $("#constructor");
    var midblock = $("#midblock");
    init = $.IsNull(init,0);
    $.ajax({
        data: $.RawData("LoadMidBlock",
            {
                "load": constructor.attr("data-load"),
                "idload": constructor.attr("data-idload"),
                "init": init
            }),
        beforeSend: function(){
            midblock.addClass("loading");
        },
        success: function (response){
            midblock.html(response);
        },
        complete: function(){
            midblock.removeClass("loading");
        }
    });
};

$.LoadHeader = function(init){
    $.ajax({
        data: $.RawData("LoadHeader", {
            "init": init
        }),
        success: function (response) {
            $("header").html(response);
        }
    });
};


$.LoadFooter = function(init){
    $.ajax({
        data: $.RawData("LoadFooter", {
            "init": init
        }),
        success: function (response) {
            $("footer").html(response);
        }
    });
};

$.LifeNote = function(tipo, valor){
    /*$.AddBreadCrumb(tipo, valor);*/
};