$(document).ready(function () {
    mostrar("");
    $('#txbuscar').on('keyup', function () {
        mostrar($(this).val());
    });
});

function mostrar(dta) {
    $.ajax({
        url: '../modelo/DAOempresas.php',
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
    const empresa = $('#txtEmpresas').val();
    const ruc = $('#txtRUC').val();
    const direccion = $('#txtDireccion').val();
    const telefono = $('#txtTelefono').val();

    if (empresa.trim() === "" && ruc.trim() === "" && direccion.trim() === "" && telefono.trim() === "" ) {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOempresas.php',
        type: 'post',
        data: {
            ev: 1,
            empresa: empresa,
            ruc: ruc,
            direccion: direccion,
            telefono: telefono
        },
        success: function (msg) {
            $('#txtEmpresas').val('');
            $('#txtRUC').val('');
            $('#txtDireccion').val('');
            $('#txtTelefono').val('');
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
    const empresa = $('#txtEmpresasE').val();
    const ruc = $('#txtRUCE').val();
    const direccion = $('#txtDireccionE').val();
    const telefono = $('#txtTelefonoE').val();

    if (empresa.trim() === "" && ruc.trim() === "" && direccion.trim() === "" && telefono.trim() === "" ) {
        alert('El campo no puede estar vacío');
        return;
    }
    $.ajax({
        url: '../modelo/DAOempresas.php',
        type: 'post',
        data: {
            ev: 2,
            id: id,
            empresa: empresa,
            ruc: ruc,
            direccion: direccion,
            telefono: telefono
        },

        success: function (msg) {
            $('#txtEmpresasE').val('');
            $('#txtRUCE').val('');
            $('#txtDireccionE').val('');
            $('#txtTelefonoE').val('');
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
        url: '../modelo/DAOempresas.php',
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
    $('#txtEmpresas').val('');
    $('#txtRUC').val('');
    $('#txtDireccion').val('');
    $('#txtTelefono').val('');
    $('#txtEmpresas').focus();
    e.preventDefault();
});



function verDetalle(idrow) {
    const empresa = $('#' + idrow).find("td").eq(1).html();
    const ruc = $('#' + idrow).find("td").eq(2).html();
    const direccion = $('#' + idrow).find("td").eq(3).html();
    const telefono = $('#' + idrow).find("td").eq(4).html();

    $('#txtEmpresasV').val(empresa);
    $('#txtRUCV').val(ruc);
    $('#txtDireccionV').val(direccion);
    $('#txtTelefonoV').val(telefono);

    $('#modalDetalles').modal('show');
}

function verEditar(idrow) {
    $('#txtid').val(idrow);
    $('#txtEmpresasE').val($('#' + idrow).find("td").eq(1).html());
    $('#txtRUCE').val($('#' + idrow).find("td").eq(2).html());
    $('#txtDireccionE').val($('#' + idrow).find("td").eq(3).html());
    $('#txtTelefonoE').val($('#' + idrow).find("td").eq(4).html());
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
