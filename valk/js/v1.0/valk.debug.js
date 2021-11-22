$.DatosSesion = function(){
    $.ajax({
        data: $.RawData("DatosSesion"),
        success: function(response) {
            $('#midblock').append(response);
        }
    });
};