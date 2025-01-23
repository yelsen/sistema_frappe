$(document).ready(function () {
    cargarOpciones("#cbInsumos", "obtenerOpciones");
    listado("txtProductos", "lista");
    mostrar("");
    $('#txbuscar').on('keyup', function () {
        mostrar($(this).val());
    });
});


function mostrar(dta) {
    $.ajax({
        url: '../modelo/DAOdetalle_insumos.php',
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





$("#btnAñadir").on("click", function (e) {
    e.preventDefault();
    const insumo = $("#cbInsumos").val().trim();
    const cantidad = parseFloat($("#txtCantidad").val().trim());
    if (insumo === "" || isNaN(cantidad) || cantidad <= 0) {
        mostrarAlerta("Error", "Ningún campo puede estar vacío y la cantidad debe ser un número mayor a 0", "danger");
        return;
    }
    const tableBody = $("#table-body");
    let existingRow = null;
    tableBody.find("tr").each(function () {
        const rowInsumo = $(this).find("td:nth-child(2) input").val();
        if (rowInsumo === insumo) {
            existingRow = $(this);
            return false;
        }
    });

    if (existingRow) {
        const existingCantidad = parseFloat(existingRow.find("td:nth-child(3) input").val());
        const newCantidad = existingCantidad + cantidad;
        existingRow.find("td:nth-child(3) input").val(newCantidad.toFixed(2));
    } else {
        const rowCount = tableBody.find("tr").length + 1;
        const row = $("<tr>").addClass("add-row");
        row.append(`
            <td><input type="text" class="form-control readonly-input" value="${rowCount}" readonly></td>
            <td><input type="text" class="form-control readonly-input" value="${insumo}" readonly></td>
            <td><input type="number" class="form-control" value="${cantidad.toFixed(2)}" step="any"></td>
            <td class="text-end">
                <div class="btn-group">
                    <button title="Eliminar datos" type="button" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `);
        const deleteButton = row.find(".btn-danger");
        deleteButton.on("click", function () {
            row.remove();
        });
        tableBody.append(row);
    }
    $("#txtCantidad").val("");
    $("#cbInsumos").val("");
});


$("#btnAgregar").on("click", function (e) {
    e.preventDefault();
    const producto = $("#txtProductos").val().trim();
    if (producto === "") {
        alert("El campo de producto no puede estar vacío");
        return;
    }
    const tableBody = $("#table-body");
    const rows = tableBody.find("tr");

    rows.each(function () {
        const insumo = $(this).find("td:nth-child(2) input").val().trim();
        const cantidad = $(this).find("td:nth-child(3) input").val().trim();
        if (insumo !== "" && cantidad !== "") {
            $.ajax({
                url: "../modelo/DAOdetalle_insumos.php",
                type: "post",
                data: {
                    ev: 1,
                    insumo: insumo,
                    cantidad: cantidad,
                    producto: producto
                },
                success: function (msg) {
                    $("#txtProductos").val("");
                    tableBody.empty();
                    mostrar("");
                    mostrarAlerta("¡Proceso Exitoso!", "El registro se ha creado correctamente.", "success");
                },
                error: function (xml, msg) {
                    console.log("Error al registrar insumo: " + insumo);
                }
            });
        }
    });

});

$("#btnCancelar").on("click", function (e) {
    $("#txtProductos").val("");
    $("#table-body").empty();
    $("#modalAgregar").modal("hide");
});

function eliminar(catalogoId, idinsumoList) {
    $('#btnEliminar').data('id', catalogoId);
    $('#btnEliminar').data('insumos', idinsumoList.split(", "));
    $('#modalEliminar').modal('show');
}



$('#btnEliminar').on('click', function () {
    const idcatalogo = $(this).data('id');
    const idinsumoArray = $(this).data('insumos');

    if (!idcatalogo || !idinsumoArray || idinsumoArray.length === 0) {
        alert("No se encontraron datos para eliminar.");
        return;
    }

    $(this).prop('disabled', true);

    let errores = [];
    let solicitudesCompletadas = 0;

    idinsumoArray.forEach((idinsumo) => {
        $.ajax({
            url: "../modelo/DAOdetalle_insumos.php",
            type: "POST",
            data: {
                ev: 3,
                idcatalogo: idcatalogo,
                idinsumo: idinsumo
            },
            success: function (response) {
                mostrar("");
                mostrarAlerta("¡Proceso Exitoso!", "El registro se ha eliminado correctamente.", "success");
            },
            error: function (xhr, status, error) {
                errores.push(`Insumo ID ${idinsumo}`);
            },
            complete: function () {
                solicitudesCompletadas++;
                if (solicitudesCompletadas === idinsumoArray.length) {
                    $('#btnEliminar').prop('disabled', false);
                    $('#modalEliminar').modal('hide');
                }
            }
        });
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
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha modificado correctamente.", "success");
        },
        error: function (xml, msg) {
            mostrarAlerta("Error", "No se pudo modificar el registro. Inténtalo de nuevo.", "danger");
        },
    });
});







function verEditar(idrow) {
    listado("txtDNIE", "listaE");
    $('#txtDNIE').val($('#' + idrow).find("td").eq(1).html());
    $('#txtUsuarioE').val($('#' + idrow).find("td").eq(3).html());
    $('#txtContraseñaE').val($('#' + idrow).find("td").eq(4).html());
    $('#modalEditar').modal('show');
}
























function cargarOpciones(idCombo, funcionVer) {
    $.ajax({
        url: "../modelo/DAOdetalle_insumos.php",
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
                    select.append('<option value="' + item.val2 + '">' + item.val2 + '</option>');
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


function listado(idInput, idLista) {
    const input = document.getElementById(idInput);
    const lista = document.getElementById(idLista);

    input.addEventListener("input", () => {
        const entrada = input.value.trim().toLowerCase();
        lista.innerHTML = "";

        if (entrada) {
            $.ajax({
                url: "../modelo/DAOdetalle_insumos.php",
                type: "POST",
                dataType: "json",
                data: {
                    ev: 4,
                    entrada: entrada,
                },
                success: function (response) {
                    console.log(response);
                    if (response.success && Array.isArray(response.data)) {
                        const filteredData = response.data.filter(
                            item =>
                                item.val2.toLowerCase().includes(entrada)
                        );

                        filteredData.forEach(item => {
                            const li = document.createElement("li");
                            li.classList.add("list-group-item", "list-group-item-action", "border-0");
                            li.textContent = `${item.val2}`;
                            li.addEventListener("click", () => {
                                input.value = item.val2;
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