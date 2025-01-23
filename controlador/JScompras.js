$(document).ready(function () {
    cargarOpciones("#cbComprobante", "obtenerOpciones");
    listadoInsumo("txtProductos", "listaProductos");
    listadoProveedor("txtProveedor", "listaProveedor");
    mostrar("");
    $("#txbuscar").on("keyup", function () {
        mostrar($(this).val());
    });
});



function mostrar(dta) {
    $.ajax({
        url: '../modelo/DAOcompras.php',
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
    agregarFila();
});


$("#btnCancelar").on("click", function () {
    limpiarFormulario();
    $("#cbComprobante").val("");
    $("#txtNumero").val("");
    $("#txtTransporte").val("");
    $("#txtProveedor").val("");
    $("#txtMonto_total").val("");
    $("#tabla-body").empty();
    $("#modalAgregar").modal("hide");
});


$("#btnAgregar").on("click", function (e) {
    e.preventDefault();
    const comprobante = $("#cbComprobante").val().trim();
    const numero = $("#txtNumero").val().trim();
    const monto = $("#txtMonto_total").val().trim();
    const proveedor = $("#txtProveedor").val().trim();
    const transporte = $("#txtTransporte").val().trim();

    if (comprobante === "" && numero === "" && monto === "" && proveedor === "") {
        alert("El campo de producto no puede estar vacío");
        return;
    }
    $.ajax({
        url: "../modelo/DAOcompras.php",
        type: "post",
        data: {
            ev: 1,
            comprobante: comprobante,
            numero: numero,
            proveedor: proveedor,
            monto: monto,
            transporte: transporte
        },
        success: function (msg) {
            const tableBody = $("#tabla-body");
            const rows = tableBody.find("tr");
            rows.each(function () {
                const insumo = $(this).find("td:nth-child(2) input").val().trim();
                const precio = $(this).find("td:nth-child(3) input").val().trim();
                const cantidad = $(this).find("td:nth-child(4) input").val().trim();
                const fecha = $(this).find("td:nth-child(5) input").val().trim(); 

                if (!fecha) {
                    console.error("Fecha no válida encontrada en la fila:", fecha);
                    return;
                }

                $.ajax({
                    url: "../modelo/DAOcompras.php",
                    type: "post",
                    data: {
                        ev: 6,
                        monto: monto,
                        numero: numero,
                        comprobante: comprobante,
                        cantidad: cantidad,
                        precio: precio,
                        fecha: fecha,
                        insumo: insumo                 
                    },
                    success: function (msg) {
                        limpiarFormulario();
                        $("#cbComprobante").val("");
                        $("#txtNumero").val("");
                        $("#txtTransporte").val("");
                        $("#txtProveedor").val("");
                        $("#txtMonto_total").val("");
                        mostrar();
                        mostrarAlerta("¡Proceso Exitoso!", "El registro se ha creado correctamente.", "success");
                        console.log("Registro exitoso para detalle compra: " + insumo);
                    },
                    error: function (xml, msg) {
                        console.log("Error al registrar insumo: " + insumo);
                    }
                });

            });
        },
        error: function (xml, msg) {
        }
    });
});









function limpiarFormulario() {
    $("#txtProductos").val("");
    $("#txtPrecio").val("");
    $("#txtStock").val("");
    $("#txtFecha").val("");
}

function agregarFila() {
    const insumo = $("#txtProductos").val().trim();
    const precio = parseFloat($("#txtPrecio").val().trim());
    const stock = parseFloat($("#txtStock").val().trim());
    const fecha = $("#txtFecha").val().trim();

    if (!insumo || isNaN(precio) || isNaN(stock) || !fecha) {
        mostrarAlerta("Error", "Ningún campo puede estar vacío o contener datos inválidos", "danger");
        return;
    }

    const tableBody = $("#tabla-body");
    let existingRow = null;
    tableBody.find("tr").each(function () {
        const rowInsumo = $(this).find("td:nth-child(2) input").val();
        const rowPrecio = parseFloat($(this).find("td:nth-child(3) input").val());
        if (rowInsumo === insumo && rowPrecio === precio) {
            existingRow = $(this);
            return false;
        }
    });

    if (existingRow) {
        const existingCantidad = parseFloat(existingRow.find("td:nth-child(4) input").val());
        const newCantidad = existingCantidad + stock;
        existingRow.find("td:nth-child(4) input").val(newCantidad.toFixed(2));
        actualizarTotal(precio * stock);
    } else {
        const rowCount = tableBody.find("tr").length + 1;
        const row = $("<tr>").addClass("add-row");
        row.append(`
            <td><input type="text" class="form-control readonly-input" value="${rowCount}" readonly></td>
            <td><input type="text" class="form-control readonly-input" value="${insumo}" readonly></td>
            <td><input type="number" class="form-control readonly-input" value="${precio.toFixed(2)}" step="any"></td>
            <td><input type="number" class="form-control readonly-input" value="${stock}" step="any"></td>
            <td><input type="date" class="form-control readonly-input" value="${fecha}" ></td>
            <td class="text-end">
                <div class="btn-group">
                    <button title="Eliminar datos" type="button" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `);
        row.find(".btn-danger").on("click", function () {
            const precioFila = parseFloat(row.find("td:nth-child(3) input").val());
            const cantidadFila = parseFloat(row.find("td:nth-child(4) input").val());
            const subtotalFila = precioFila * cantidadFila;

            actualizarTotal(-subtotalFila);
            row.remove();
        });
        tableBody.append(row);
        actualizarTotal(precio * stock);
    }
    limpiarFormulario();
}


function actualizarTotal(cambio) {
    const montoInput = $("#txtMonto_total");
    let montoActual = parseFloat(montoInput.val()) || 0; 
    montoActual += cambio;
    console.log("Monto actualizado:", montoActual);
    montoInput.val(montoActual.toFixed(2)); 
}



function listadoInsumo(idInput, idLista) {
    const input = document.getElementById(idInput);
    const lista = document.getElementById(idLista);

    input.addEventListener("input", () => {
        const entrada = input.value.trim().toLowerCase();
        lista.innerHTML = "";

        if (entrada) {
            $.ajax({
                url: "../modelo/DAOcompras.php",
                type: "POST",
                dataType: "json",
                data: {
                    ev: 4,
                    entrada: entrada,
                },
                success: function (response) {
                    if (response.success && Array.isArray(response.data)) {
                        const filteredData = response.data.filter((item) =>
                            item.val2.toLowerCase().includes(entrada)
                        );

                        filteredData.forEach((item) => {
                            const li = document.createElement("li");
                            li.classList.add(
                                "list-group-item",
                                "list-group-item-action",
                                "border-0"
                            );
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

function listadoProveedor(idInput, idLista) {
    const input = document.getElementById(idInput);
    const lista = document.getElementById(idLista);

    input.addEventListener("input", () => {
        const entrada = input.value.trim().toLowerCase();
        lista.innerHTML = "";

        if (entrada) {
            $.ajax({
                url: "../modelo/DAOcompras.php",
                type: "POST",
                dataType: "json",
                data: {
                    ev: 5,
                    entrada: entrada,
                },
                success: function (response) {
                    if (response.success && Array.isArray(response.data)) {
                        const filteredData = response.data.filter(
                            (item) =>
                                item.val1.toLowerCase().includes(entrada) ||
                                item.val2.toLowerCase().includes(entrada) ||
                                item.val3.toLowerCase().includes(entrada) ||
                                item.val4.toLowerCase().includes(entrada)
                        );

                        filteredData.forEach((item) => {
                            const li = document.createElement("li");
                            li.classList.add(
                                "list-group-item",
                                "list-group-item-action",
                                "border-0"
                            );
                            li.textContent = `${item.val2} - DNI: ${item.val1}'`;
                            li.addEventListener("click", () => {
                                input.value = item.val1;
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

function cargarOpciones(idCombo, funcionVer) {
    $.ajax({
        url: "../modelo/DAOcompras.php",
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
                select.append(
                    '<option value="" disabled selected>Seleccione...</option>'
                );
                response.data.forEach(function (item) {
                    select.append(
                        '<option value="' + item.val1 + '">' + item.val2 + "</option>"
                    );
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
