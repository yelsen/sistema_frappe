$(document).ready(function () {
    mostrar("");
    listado("txtEmpresa", "lista");
    
    $("#txbuscar").on("keyup", function () {
        mostrar($(this).val());
    });
});


function mostrar(dta) {
    $.ajax({
        url: "../modelo/DAOproveedores.php",
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
    const empresa = $("#txtEmpresa").val();

    if (dni.trim() === "" && apellidos.trim() === "" && nombres.trim() === "" && telefono.trim() === "" && empresa.trim() === "") {
        alert("Ningun campo no puede estar vacío");
        return;
    }

    $.ajax({
        url: "../modelo/DAOproveedores.php",
        type: "post",
        data: {
            ev: 1,
            dni: dni,
            apellidos: apellidos,
            nombres: nombres,
            telefono: telefono,
            empresa: empresa,
        },
        success: function (msg) {
            $("#txtDNI").val("");
            $("#txtApellidos").val("");
            $("#txtNombres").val("");
            $("#txtTelefono").val("");
            $("#txtEmpresa").val("");
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
    const empresa = $("#txtEmpresaE").val();

    if (dni.trim() === "" && apellidos.trim() === "" && nombres.trim() === "" && telefono.trim() === "" && empresa.trim() === "") {
        alert("Ningun campo no puede estar vacío");
        return;
    }
    $.ajax({
        url: "../modelo/DAOproveedores.php",
        type: "post",
        data: {
            ev: 2,
            dni: dni,
            apellidos: apellidos,
            nombres: nombres,
            telefono: telefono,
            empresa: empresa,
        },

        success: function (msg) {
            $("#txtDNIE").val("");
            $("#txtApellidosE").val("");
            $("#txtNombresE").val("");
            $("#txtTelefonoE").val("");
            $("#txtEmpresaE").val("");
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
    const idrow = $(this).attr('data-id');
    const dni = $('#' + idrow).find("td").eq(1).html();
    console.log(dni);
    if (!dni) {
        alert('No se ha seleccionado un registro para eliminar.');
        return;
    }

    $.ajax({
        url: '../modelo/DAOproveedores.php',
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
    $.ajax({
        url: "../modelo/DAOproveedores.php",
        type: "POST",
        data: {
            ev: 5,
            idp: idrow,
        },
        success: function (data) {
            if (typeof data === "string") {
                data = JSON.parse(data);
            }
            if (data.success) {
                $("#dniV").text(data.dni);
                $("#proveedorV").text(data.apellidos + " " + data.nombres);
                $("#telefonoV").text(data.telefono);
                $("#empresaV").text(data.nombre_empresal);
                $("#RUCV").text(data.RUC);
                $("#direccionV").text(data.direccion);
                $("#tel_empresaV").text(data.telefono_em);

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
    $.ajax({
        url: "../modelo/DAOproveedores.php",
        type: "POST",
        data: {
            ev: 5,
            idp: idrow,
        },
        success: function (data) {
            if (typeof data === "string") {
                data = JSON.parse(data);
            }
            if (data.success) {
                listado("txtEmpresaE", "listaE");
                $("#txtDNIE").val(data.dni);
                $("#txtApellidosE").val(data.apellidos);
                $("#txtNombresE").val(data.nombres);
                $("#txtTelefonoE").val(data.telefono);
                $("#txtEmpresaE").val(data.RUC);

                $("#modalEditar").modal("show");
            } else {
                alert("No se encontraron datos.");
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



function listado(idInput, idLista) {
    const input = document.getElementById(idInput);
    const lista = document.getElementById(idLista);

    input.addEventListener("input", () => {
        const entrada = input.value.trim().toLowerCase();
        lista.innerHTML = "";

        if (entrada) {
            $.ajax({
                url: "../modelo/DAOproveedores.php",
                type: "POST",
                dataType: "json",
                data: {
                    ev: 4,
                    entrada: entrada,
                },
                success: function (response) {
                    if (response.success && Array.isArray(response.data)) {
                        const filteredData = response.data.filter(
                            item =>
                                item.RUC.includes(entrada) ||
                                item.nombre_empresa.toLowerCase().includes(entrada)
                        );

                        filteredData.forEach(item => {
                            const li = document.createElement("li");
                            li.classList.add("list-group-item", "list-group-item-action", "border-0");
                            li.textContent = `${item.nombre_empresa} - RUC: ${item.RUC}`;
                            li.addEventListener("click", () => {
                                input.value = item.RUC;
                                lista.innerHTML = ""; 
                            });
                            lista.appendChild(li);
                        });
                    } else {
                        console.error("El formato de los datos recibidos no es válido");
                    }
                },
                error: function (error) {
                    console.error("Error al consultar los datos del servidor:", error);
                },
            });
        }
    });

    document.addEventListener("click", (event) => {
        if (!input.contains(event.target) && !lista.contains(event.target)) {
            lista.innerHTML = "";
        }
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
