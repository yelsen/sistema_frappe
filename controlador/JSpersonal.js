$(document).ready(function () {
    mostrar("");
    cargarOpciones("#cbRol","obtenerOpciones");
    $("#txbuscar").on("keyup", function () {
        mostrar($(this).val());
    });
});


function mostrar(dta) {
    $.ajax({
        url: "../modelo/DAOpersonal.php",
        type: "post",
        data: {
            ev: 0,
            dt: dta,
        },
        success: function (msg) {
            $("#tabla").html(msg);
        },
        error: function (xml, msg) {
            swal("Aviso", msg.trim(), "error");
        },
    });
}

$("#btnAgregar").on("click", function (e) {
    e.preventDefault();
    const dni = $("#txtDNI").val();
    const apellidos = $("#txtApellidos").val();
    const nombres = $("#txtNombres").val();
    const telefono = $("#txtTelefono").val();
    const rol = $("#cbRol").val();

    if (dni.trim() === "" && apellidos.trim() === "" && nombres.trim() === "" && telefono.trim() === "" && rol.trim() === "") {
        alert("Ningun campo no puede estar vacío");
        return;
    }

    $.ajax({
        url: "../modelo/DAOpersonal.php",
        type: "post",
        data: {
            ev: 1,
            dni: dni,
            apellidos: apellidos,
            nombres: nombres,
            telefono: telefono,
            rol: rol,
        },
        success: function (msg) {
            $("#txtDNI").val("");
            $("#txtApellidos").val("");
            $("#txtNombres").val("");
            $("#txtTelefono").val("");
            $("#cbRol").val("");
            $("#modalAgregar").modal("hide");
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha creado correctamente.", "success");
        },

        error: function (xml, msg) {
            mostrarAlerta("Error", "No se pudo crear el registro. Inténtalo de nuevo.", "danger");
        },
    });
});

$("#btnModificar").on("click", function (e) {
    const dni = $("#txtDNIE").val();
    const apellidos = $("#txtApellidosE").val();
    const nombres = $("#txtNombresE").val();
    const telefono = $("#txtTelefonoE").val();
    const rol = $("#cbRolE").val();

    if (dni.trim() === "" && apellidos.trim() === "" && nombres.trim() === "" && telefono.trim() === "" && rol.trim() === "") {
        alert("Ningun campo no puede estar vacío");
        return;
    }
    $.ajax({
        url: "../modelo/DAOpersonal.php",
        type: "post",
        data: {
            ev: 2,
            dni: dni,
            apellidos: apellidos,
            nombres: nombres,
            telefono: telefono,
            rol: rol,
        },

        success: function (msg) {
            $("#txtDNIE").val("");
            $("#txtApellidosE").val("");
            $("#txtNombresE").val("");
            $("#txtTelefonoE").val("");
            $("#cbRolE").val("");
            $("#modalEditar").modal("hide");
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha modificado correctamente.", "success");
        },
        error: function (xml, msg) {
            mostrarAlerta("Error", "No se pudo modificar el registro. Inténtalo de nuevo.", "danger");
        },
    });
});

$('#btnEliminar').on('click', function (e) {
    const dni = $(this).attr('data-id');
    console.log(dni);
    if (!dni) {
        alert('No se ha seleccionado un registro para eliminar.');
        return;
    }

    $.ajax({
        url: '../modelo/DAOpersonal.php',
        type: 'post',
        data: {
            ev: 3,
            dni: dni
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

function eliminar(dni) {
    $('#btnEliminar').attr('data-id', dni);
    $('#modalEliminar').modal('show');
}


function verDetalle(idrow) {
    const dni = $("#" + idrow).find("td").eq(1).html();
    $.ajax({
        url: "../modelo/DAOpersonal.php",
        type: "POST",
        data: {
            ev: 4,
            dni: dni,
        },
        success: function (data) {
            if (typeof data === "string") {
                data = JSON.parse(data);
            }
            if (data.success) {
                $("#dniV").text(data.dni);
                $("#nombreV").text(data.apellidos + " " + data.nombres);
                $("#telefonoV").text(data.telefono);
                $("#rolV").text(data.nombre_rol);
                $("#fecha_ingresoV").text(data.fecha_ingresoP);

                $("#modalDetalles").modal("show");
            } else {
                alert("No se encontraron datos para el empleado.");
            }
        },
        error: function (xhr, status, error) {
            console.log("Estado: " + status);
            console.log("Error: " + error);
            console.log("Respuesta: " + xhr.responseText);
            alert("Error al consultar los datos del servidor.");
        },
    });
}

function verEditar(idrow) {
    const dni = $("#" + idrow).find("td").eq(1).html();

    $.ajax({
        url: "../modelo/DAOpersonal.php",
        type: "POST",
        data: {
            ev: 4,
            dni: dni,
        },
        success: function (data) {
            if (typeof data === "string") {
                data = JSON.parse(data);
            }
            if (data.success) {
                console.log(data);
                $("#txtDNIE").val(data.dni);
                $("#txtApellidosE").val(data.apellidos);
                $("#txtNombresE").val(data.nombres);
                $("#txtTelefonoE").val(data.telefono);

                cargarOpciones("#cbRolE","obtenerOpciones");
                setTimeout(function () {
                    $("#cbRolE").val(data.idrol);
                }, 100);

                $("#modalEditar").modal("show");
            } else {
                alert("No se encontraron datos para el DNI proporcionado.");
            }
        },
        error: function (xhr, status, error) {
            console.log("Estado: " + status);
            console.log("Error: " + error);
            console.log("Respuesta: " + xhr.responseText);
            alert("Error al consultar los datos del servidor.");
        },
    });
}



function cargarOpciones(idCombo, funcionVer) {
    $.ajax({
        url: "../modelo/DAOpersonal.php",
        method: "GET",
        data: {
            funcion: funcionVer,
        },
        dataType: "json",
        success: function (response) {
            if (response.error) {
                alert(response.error);
            } else {
                let select = $(idCombo);
                select.empty();
                select.append('<option value="" disabled selected>Seleccione...</option>');
                response.data.forEach(function (item) {
                    select.append('<option value="' + item.val1 + '">' + item.val2 + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            console.log("Estado: " + status);
            console.log("Error: " + error);
            console.log("Respuesta: " + xhr.responseText);
            alert("Error al cargar los roles.");
        },
    });
}








function mostrarAlerta(titulo, mensaje, tipo = "success") {
    $("#alertTitulo").text(titulo);
    $("#alertMensaje").text(mensaje);
    const alerta = $("#miAlerta");
    alerta.removeClass("alert-success alert-danger alert-warning alert-info");
    alerta.addClass(`alert-${tipo}`);
    alerta.show();

    setTimeout(function () {
        alerta.fadeOut("slow");
    }, 3000);
}
