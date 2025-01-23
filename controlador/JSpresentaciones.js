$(document).ready(function () {
    mostrar("");
    $('#txbuscar').on('keyup', function () {
        mostrar($(this).val());
    });
});

function mostrar(dta) {
    $.ajax({
        url: '../modelo/DAOpresentaciones.php',
        type: 'post',
        data: {
            ev: 0,
            dt: dta
        },
        success: function (msg) {
            $("#tabla").html(msg);
        },
        error: function (xml, msg) {
            swal('Aviso', msg.trim(), 'error');
        }
    });
}

$('#btnAgregar').on('click', function (e) {
    e.preventDefault();
    const presentaciones = $('#txtPresentaciones').val();

    if (presentaciones.trim() === "") {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOpresentaciones.php',
        type: 'post',
        data: {
            ev: 1,
            presentaciones: presentaciones
        },
        success: function (msg) {
            $('#txtPresentaciones').val('');
            $('#modalAgregar').modal('hide');
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha creado correctamente.", "success");
        },
        error: function (xml, msg) {
            mostrarAlerta("Error", "No se pudo crear el registro. Inténtalo de nuevo.", "danger");
        }
    });
});

$('#btnModificar').on('click', function (e) {
    const idpresentacion = $('#txtid').val();
    const presentaciones = $('#txtPresentacionesE').val();

    if (presentaciones.trim() === "") {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOpresentaciones.php',
        type: 'post',
        data: {
            ev: 2,
            idpresentacion: idpresentacion,
            presentaciones: presentaciones
        },

        success: function (msg) {
            $('#txtPresentacionesE').val('');
            $('#modalEditar').modal('hide');
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha modificado correctamente.", "success");
        },
        error: function (xml, msg) {
            mostrarAlerta("Error", "No se pudo modificar el registro. Inténtalo de nuevo.", "danger");
        }
    });
});

$('#btnEliminar').on('click', function (e) {
    const idpresentacion = $(this).attr('data-id');
    if (!idpresentacion) {
        alert('No se ha seleccionado un registro para eliminar.');
        return;
    }

    $.ajax({
        url: '../modelo/DAOpresentaciones.php',
        type: 'post',
        data: {
            ev: 3,
            idpresentacion: idpresentacion
        },
        success: function (msg) {
            $('#modalEliminar').modal('hide');
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha eliminado correctamente.", "success");
        },
        error: function (xml, msg) {
            mostrarAlerta("Error", "No se pudo eliminar el registro. Inténtalo de nuevo.", "danger");
        }
    });
});

$('#btnCancelar').click(function (e) {
    $('#txtPresentaciones').val("");
    $('#txtPresentaciones').focus();
    e.preventDefault();
});



function verDetalle(idrow) {
    const id = $('#' + idrow).find("td").eq(0).html();
    const presentaciones = $('#' + idrow).find("td").eq(1).html();
    $('#detalleId').val(id);
    $('#detallePresentaciones').val(presentaciones);
    $('#modalDetalles').modal('show');
}

function verEditar(idrow) {
    $('#txtid').val(idrow);
    $('#txtPresentacionesE').val($('#' + idrow).find("td").eq(1).html());
    $('#modalEditar').modal('show');
}

function eliminar(id) {
    $('#btnEliminar').attr('data-id', id);
    $('#modalEliminar').modal('show');
}

function mostrarAlerta(titulo, mensaje, tipo = "success") {
    $('#alertTitulo').text(titulo);
    $('#alertMensaje').text(mensaje);
    const alerta = $('#miAlerta');
    alerta.removeClass('alert-success alert-danger alert-warning alert-info');
    alerta.addClass(`alert-${tipo}`);
    alerta.show();

    setTimeout(function () {
        alerta.fadeOut('slow');
    }, 3000);
}
