$(document).ready(function () {
    mostrar("");
    $('#txbuscar').on('keyup', function () {
        mostrar($(this).val());
    });
});

function mostrar(dta) {
    $.ajax({
        url: '../modelo/DAOinsumos.php',
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
    const insumos = $('#txtInsumos').val();

    if (insumos.trim() === "") {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOinsumos.php',
        type: 'post',
        data: {
            ev: 1,
            insumos: insumos
        },
        success: function (msg) {
            $('#txtInsumos').val('');
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
    const id = $('#txtid').val();
    const insumos = $('#txtInsumosE').val();

    if (insumos.trim() === "") {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOinsumos.php',
        type: 'post',
        data: {
            ev: 2,
            id: id,
            insumos: insumos
        },

        success: function (msg) {
            $('#txtInsumosE').val('');
            $('#txtid').val('');
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
    const id = $(this).attr('data-id');
    if (!id) {
        alert('No se ha seleccionado un registro para eliminar.');
        return;
    }

    $.ajax({
        url: '../modelo/DAOinsumos.php',
        type: 'post',
        data: {
            ev: 3,
            id: id
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
    $('#txtInsumos').val("");
    $('#txtInsumos').focus();
    e.preventDefault();
});



function verDetalle(idrow) {
    const id = $('#' + idrow).find("td").eq(0).html();
    const insumos = $('#' + idrow).find("td").eq(1).html();
    $('#detalleId').val(id);
    $('#detalleInsumos').val(insumos);
    $('#modalDetalles').modal('show');
}

function verEditar(idrow) {
    $('#txtid').val(idrow);
    $('#txtInsumosE').val($('#' + idrow).find("td").eq(1).html());
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
