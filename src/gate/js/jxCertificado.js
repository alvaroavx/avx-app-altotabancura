/**
 * JS Certificado
 * **/

/**
 * Genera multiples certificados, para vista docente
 * @constructor
 */
$.GenerarMultiplesCertificados = function() {
    $(".cargando").addClass("up");
    var usuarios = [];
    var counter = 0;
    $.each($("input[name='usuario']:checked"), function(){
        var idusuario = $(this).attr('data-idusuario'); 
        usuarios.push(idusuario);
    });
    if(usuarios.length <= 5) {
        var i;
        for(i=0; i<usuarios.length; i++) {
            $.GenerarCertificado(usuarios[i]);
        }
        $(".cargando").removeClass("up");
    }
    else {
        $.ajax({
            data: $.RawData('GenerarCertificado', {
                'usuarios': usuarios,
                'multiple': 1
            }),
            success: function (fileURL) {
                var fileName = 'consolidado.pdf';
                if (!window.ActiveXObject) {
                    var save = document.createElement('a');
                    save.href = fileURL;
                    console.log(fileURL);
                    save.target = '_blank';
                    save.download = fileName || 'unknown';

                    var evt = new MouseEvent('click', {
                        'view': window,
                        'bubbles': true,
                        'cancelable': false
                    });
                    save.dispatchEvent(evt);

                    (window.URL || window.webkitURL).revokeObjectURL(save.href);
                }
            },
            complete: function () {
                $(".cargando").removeClass("up");
            }
        });
    }
};

/**
 * Genera un certificado, para vista alumno
 * @constructor
 */
$.GenerarCertificado = function (idUsuario) {
    $.ajax({
        data: $.RawData('GenerarCertificado', {
            'idusuario': idUsuario,
            'multiple': 0
        }),
        success: function (fileURL) {
            var fileName = 'certificado.pdf';
            if (!window.ActiveXObject) {
                var save = document.createElement('a');
                save.href = fileURL;
                console.log(fileURL);
                save.target = '_blank';
                save.download = fileName || 'unknown';

                var evt = new MouseEvent('click', {
                    'view': window,
                    'bubbles': true,
                    'cancelable': false
                });
                save.dispatchEvent(evt);

                (window.URL || window.webkitURL).revokeObjectURL(save.href);
            }
        }
    });
};

/**
 * Genera certificado con múltiples vacunas en formato tabla
 * @constructor
 */
$.GenerarCertificadoTabla = function() {
    $(".cargando").addClass("up");
    var usuarios = [];
    var counter = 0;
    $.each($("input[name='usuario']:checked"), function(){
        var idusuario = $(this).attr('data-idusuario');
        usuarios.push(idusuario);
    });
    $.ajax({
        data: $.RawData('GenerarCertificadoTabla', {
            'usuarios': usuarios,
            'multiple': 1
        }),
        success: function (fileURL) {
            var fileName = 'consolidado-tabla.pdf';
            if (!window.ActiveXObject) {
                var save = document.createElement('a');
                save.href = fileURL;
                console.log(fileURL);
                save.target = '_blank';
                save.download = fileName || 'unknown';

                var evt = new MouseEvent('click', {
                    'view': window,
                    'bubbles': true,
                    'cancelable': false
                });
                save.dispatchEvent(evt);

                (window.URL || window.webkitURL).revokeObjectURL(save.href);
            }
        },
        complete: function () {
            $(".cargando").removeClass("up");
        }
    });
};