
$(document).ready(function () {
    mostrar("");
    $('#txbuscar').on('keyup', function () {
        mostrar($(this).val());
    });
});

function mostrar(dta) {
    $.ajax({
        url: '../modelo/DAOtipo_comprobante.php',
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
    const tipoComprobante = $('#txtTipo_comprobante').val();

    if (tipoComprobante.trim() === "") {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOtipo_comprobante.php',
        type: 'post',
        data: {
            ev: 1,
            tipo_comprobante: tipoComprobante
        },
        success: function (msg) {
            $('#txtTipo_comprobante').val('');
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
    const idtipo_comprobante = $('#txtid').val();
    const tipoComprobante = $('#txtTipo_comprobanteE').val();

    if (tipoComprobante.trim() === "") {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOtipo_comprobante.php',
        type: 'post',
        data: {
            ev: 2,
            idtipo_comprobante: idtipo_comprobante,
            tipo_comprobante: tipoComprobante
        },

        success: function (msg) {
            $('#txtTipo_comprobanteE').val('');
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
    const idEliminar = $(this).attr('data-id');
    if (!idEliminar) {
        alert('No se ha seleccionado un registro para eliminar.');
        return;
    }

    $.ajax({
        url: '../modelo/DAOtipo_comprobante.php',
        type: 'post',
        data: {
            ev: 3,
            idtipo_comprobante: idEliminar
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

function verEditar(idrow) {
    $('#txtid').val(idrow);
    $('#txtTipo_comprobanteE').val($('#' + idrow).find("td").eq(1).html());
    $('#modalEditar').modal('show');
}

function eliminar(id) {
    $('#btnEliminar').attr('data-id', id);
    $('#modalEliminar').modal('show');
}

function verDetalle(idrow) {
    const id = $('#' + idrow).find("td").eq(0).html();
    const tipoComprobante = $('#' + idrow).find("td").eq(1).html(); 
    $('#detalleId').val(id);
    $('#detalleTipo').val(tipoComprobante);
    $('#modalDetalles').modal('show');
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







$('#btnCancelar').click(function (e) {
    $('#txtTipo_comprobante').val("");
    $('#txtTipo_comprobante').focus();
    e.preventDefault();
});
