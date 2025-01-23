$(document).ready(function () {
    listado("txtDNI", "lista");
    mostrar("");
    $("#txbuscar").on("keyup", function () {
        mostrar($(this).val());
    });
});


function mostrar(dta) {
    $.ajax({
        url: "../modelo/DAOusuarios.php",
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
    const usuario = $("#txtUsuario").val();
    const psw = $("#txtContraseña").val();


    if (dni.trim() === "" && usuario.trim() === "" && psw.trim() === "") {
        alert("Ningun campo no puede estar vacío");
        return;
    }

    $.ajax({
        url: "../modelo/DAOusuarios.php",
        type: "post",
        data: {
            ev: 1,
            dni: dni,
            usuario: usuario,
            psw: psw
        },
        success: function (msg) {
            $("#txtDNI").val("");
            $("#txtUsuario").val("");
            $("#txtContraseña").val("");
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
    const usuario = $("#txtUsuarioE").val();
    const psw = $("#txtContraseñaE").val();

    if (dni.trim() === "" && apellidos.trim() === "" && nombres.trim() === "" && telefono.trim() === "" && rol.trim() === "") {
        alert("Ningun campo no puede estar vacío");
        return;
    }
    $.ajax({
        url: "../modelo/DAOusuarios.php",
        type: "post",
        data: {
            ev: 2,
            dni: dni,
            usuario: usuario,
            psw: psw
        },

        success: function (msg) {
            $("#txtDNIE").val("");
            $("#txtUsuarioE").val("");
            $("#txtContraseñaE").val("");
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
    if (!dni) {
        alert('No se ha seleccionado un registro para eliminar.');
        return;
    }

    $.ajax({
        url: '../modelo/DAOusuarios.php',
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

function verEditar(idrow) {
    listado("txtDNIE", "listaE");
    $('#txtDNIE').val($('#' + idrow).find("td").eq(1).html());
    $('#txtUsuarioE').val($('#' + idrow).find("td").eq(3).html());
    $('#txtContraseñaE').val($('#' + idrow).find("td").eq(4).html());
    $('#modalEditar').modal('show');
}

function verDetalle(idrow) {
    const dni = $('#' + idrow).find("td").eq(1).html();
    const persona = $('#' + idrow).find("td").eq(2).html();
    const usuario = $('#' + idrow).find("td").eq(3).html();
    const contraseña = $('#' + idrow).find("td").eq(4).html();
    console.log(dni,persona,usuario,contraseña);

    $('#dniV').text(dni);
    $('#ape_nomV').text(persona);
    $('#usuarioV').text(usuario);
    $('#contraseñaV').text(contraseña);
    $('#modalDetalles').modal('show');
}



function listado(idInput, idLista) {
    const input = document.getElementById(idInput);
    const lista = document.getElementById(idLista);

    input.addEventListener("input", () => {
        const entrada = input.value.trim().toLowerCase();
        lista.innerHTML = "";

        if (entrada) {
            $.ajax({
                url: "../modelo/DAOusuarios.php",
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
                                item.dni.includes(entrada) ||
                                item.apellidos.toLowerCase().includes(entrada) ||
                                item.nombres.toLowerCase().includes(entrada)
                        );

                        filteredData.forEach(item => {
                            const li = document.createElement("li");
                            li.classList.add("list-group-item", "list-group-item-action", "border-0");
                            li.textContent = `${item.apellidos} ${item.nombres} - DNI: ${item.dni}`;
                            li.addEventListener("click", () => {
                                input.value = item.dni;
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




/*
function listado(idInput, idLista) {
    const input = document.getElementById(idInput);
    const lista = document.getElementById(idLista);

    input.addEventListener("input", () => {
        const entrada = input.value.trim().toLowerCase();
        lista.innerHTML = "";

        if (entrada) {
            $.ajax({
                url: "../modelo/DAOusuarios.php",
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
                                item.dni.includes(entrada) ||
                                item.apellidos.toLowerCase().includes(entrada) ||
                                item.nombres.toLowerCase().includes(entrada)
                        );

                        filteredData.forEach(item => {
                            const li = document.createElement("li");
                            li.classList.add("list-group-item", "list-group-item-action");
                            li.textContent = `${item.apellidos} ${item.nombres} - DNI: ${item.dni}`;
                            li.addEventListener("click", () => {
                                input.value = item.dni;
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
*/