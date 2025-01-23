$(document).ready(function () {
    cargarOpciones("#cbComprobante", "obtenerOpciones");
    listadoProductos("txtProductos", "lista");
    mostrar("");
    $("#txbuscar").on("keyup", function () {
        mostrar($(this).val());
    });

    $("#cbComprobante").on("change", function () {
        const selectedValue = $(this).val();
        if (selectedValue) {
            mostrarNumero(selectedValue);
        }
    });

    $("#txtPagar").on("keyup", function () {
        const pago = parseFloat($("#txtPagar").val().trim());
        const monto = parseFloat($("#txtMonto").val().trim());
        if (!isNaN(pago) && !isNaN(monto) && pago >= monto) {
            const vuelto = pago - monto;
            $("#txtVuelto").val(vuelto.toFixed(2));
        } else {
            $("#txtVuelto").val('Error');
        }
    });

});

function mostrar(dta) {
    $.ajax({
        url: '../modelo/DAOventa.php',
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













let ventanaReporte = null;

$("#btnAgregar").on("click", function (e) {
    e.preventDefault();
    const monto = $("#txtMonto").val();
    const vuelto = $("#txtVuelto").val();
    const letra = numeroALetrasFactura(monto);
    const numero = $("#txtNumero").val();
    const idtipo = $("#cbComprobante").val();

    $.ajax({
        url: "../modelo/DAOventa.php",
        type: "post",
        data: {
            ev: 1,
            monto: monto,
            vuelto: vuelto,
            letra: letra,
            numero: numero,
            idtipo: idtipo
        },
        success: function (msg) {
            const idvent = parseInt(msg.split(":")[1].trim());
            $("#txtid").val(idvent);
            const tableBody = $("#tabla-body");
            const rows = tableBody.find("tr");

            rows.each(function () {
                const idventa = $("#txtid").val();
                const producto = $(this).find("td:nth-child(2) textarea").val();
                const cantidad = $(this).find("td:nth-child(4) input").val();
                const subtotal = $(this).find("td:nth-child(5) input").val();

                $.ajax({
                    url: "../modelo/DAOventa.php",
                    type: "post",
                    data: {
                        ev: 6,
                        idventa: idventa,
                        producto: producto,
                        cantidad: cantidad,
                        subtotal: subtotal
                    },
                    success: function (msg) {
                        // Asegurarse de que la ventana se abre correctamente en una nueva pestaña
                        if (!ventanaReporte || ventanaReporte.closed) {
                            ventanaReporte = window.open('../modelo/comprobante.php?idventa=' + idvent, '_blank');
                        } else {
                            ventanaReporte.close(); // Cerrar la ventana anterior si está abierta
                            ventanaReporte = window.open('../modelo/comprobante.php?idventa=' + idvent, '_blank');
                        }
                        limpiarFormulario();
                        $("#cbComprobante").val("");
                        $("#txtNumero").val("");
                        $("#txtPagar").val("");
                        $("#txtVuelto").val("");
                        $("#txtMonto").val("");
                        //$("#txtid").val("");
                        $("#tabla-body").empty();
                        mostrarAlerta("¡Proceso Exitoso!", "El registro se ha creado correctamente.", "success");

                    },
                    error: function (xml, msg) {
                        console.log("Error al registrar detalle:", xml.responseText);
                    }
                });
            });
        },
        error: function (xml, msg) {
            console.log("Error al registrar la venta:", xml.responseText);
        }
    });
});














function numeroALetrasFactura(num, moneda = "Soles") {
    const unidades = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    const decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    const especiales = ['once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
    const centenas = ['', 'cien', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    function convertirDecenasYUnidades(n) {
        if (n < 10) return unidades[n];
        if (n >= 10 && n < 20) return especiales[n - 11];
        const decena = Math.floor(n / 10);
        const unidad = n % 10;
        return unidad === 0 ? decenas[decena] : `${decenas[decena]} y ${unidades[unidad]}`;
    }

    function convertirCentenas(n) {
        const centena = Math.floor(n / 100);
        const resto = n % 100;
        if (n === 100) return 'cien';
        return `${centenas[centena]} ${convertirDecenasYUnidades(resto)}`.trim();
    }

    function convertirMiles(n) {
        const mil = Math.floor(n / 1000);
        const resto = n % 1000;
        if (mil === 1) return `mil ${convertirCentenas(resto)}`.trim();
        return `${convertirCentenas(mil)} mil ${convertirCentenas(resto)}`.trim();
    }

    function convertirMillones(n) {
        const millon = Math.floor(n / 1000000);
        const resto = n % 1000000;
        const millonesTexto = millon === 1 ? 'un millón' : `${convertirCentenas(millon)} millones`;
        return `${millonesTexto} ${convertirMiles(resto)}`.trim();
    }

    function convertirNumero(n) {
        if (n < 10) return unidades[n];
        if (n < 100) return convertirDecenasYUnidades(n);
        if (n < 1000) return convertirCentenas(n);
        if (n < 1000000) return convertirMiles(n);
        return convertirMillones(n);
    }

    if (isNaN(num)) {
        return "Número inválido";
    }

    const entero = Math.floor(num);
    const decimales = Math.round((num - entero) * 100);
    let textoEntero = convertirNumero(entero);

    if (textoEntero) {
        textoEntero = textoEntero.charAt(0).toUpperCase() + textoEntero.slice(1);
    } else {
        textoEntero = "Cero";
    }

    let resultado = `${textoEntero} Y ${decimales.toString().padStart(2, "0")}/100 ${moneda.toUpperCase()}`;
    return resultado.toUpperCase();
}







$("#btnCancelar").on("click", function () {
    limpiarFormulario();
    $("#cbComprobante").val("");
    $("#txtNumero").val("");
    $("#txtPagar").val("");
    $("#txtVuelto").val("");
    $("#txtMonto").val("");
    $("#tabla-body").empty();
});


$("#btnAñadir").on("click", function (e) {
    e.preventDefault();
    agregarFila();
});

function agregarFila() {
    const producto = $("#txtProductos").val().trim();
    const stock = parseFloat($("#txtStock").val().trim());
    const cantidad = parseInt($("#txtCantidad").val().trim());
    const precio = $("#txtPrecio").val().trim();

    if (cantidad > stock) {
        mostrarAlerta("Error", "La cantidad no puede superar el stock", "danger");
        return;
    }
    if (!producto || isNaN(precio) || isNaN(cantidad) || cantidad <= 0) {
        mostrarAlerta("Error", "Ningún campo puede estar vacío o contener datos inválidos", "danger");
        return;
    }

    const tableBody = $("#tabla-body");
    let existingRow = null;
    tableBody.find("tr").each(function () {
        const rowProducto = $(this).find("td:nth-child(2) textarea").val();
        const rowPrecio = $(this).find("td:nth-child(3) input").val();
        if (rowProducto === producto && rowPrecio === precio) {
            existingRow = $(this);
            return false;
        }
    });

    if (existingRow) {
        const existingCantidad = parseInt(existingRow.find("td:nth-child(4) input").val(), 10);
        const newCantidad = existingCantidad + cantidad;

        if (newCantidad > stock) {
            mostrarAlerta("Error", "La cantidad total no puede superar el stock", "danger");
        } else {
            existingRow.find("td:nth-child(4) input").val(newCantidad);
            const newSubtotal = newCantidad * precio;
            existingRow.find("td:nth-child(5) input").val(newSubtotal.toFixed(2));
            actualizarTotal(newSubtotal - (existingCantidad * precio));
        }
    } else {
        const rowCount = tableBody.find("tr").length + 1;
        const subtotal = precio * cantidad;
        const row = $("<tr>").addClass("add-row");
        actualizarTotal(subtotal);
        row.append(`
            <td>${rowCount}</td>
            <td><textarea class="form-control readonly-input" rows="1" readonly>${producto}</textarea></td>
            <td><input type="number" class="form-control readonly-input" value="${precio}" readonly></td>
            <td><input type="number" class="form-control readonly-input" value="${cantidad}" readonly></td>
            <td><input type="number" class="form-control readonly-input" value="${subtotal.toFixed(2)}" readonly></td>
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
            const cantidadFila = parseInt(row.find("td:nth-child(4) input").val(), 10);
            const subtotalFila = precioFila * cantidadFila;
            actualizarTotal(-subtotalFila);
            row.remove();
        });

        tableBody.append(row);
    }

    limpiarFormulario();
}

function actualizarTotal(cambio) {
    const montoInput = $("#txtMonto");
    let montoActual = parseFloat(montoInput.val()) || 0;
    montoActual += cambio;
    montoInput.val(montoActual.toFixed(2));
    const igv = 0.18 * montoActual;
    const total = igv + montoActual;
    $("#txtIGV").val(igv.toFixed(2));
    $("#txtMonto_total").val(total.toFixed(2));

}



function limpiarFormulario() {
    $("#txtProductos").val("");
    $("#txtPrecio").val("");
    $("#txtStock").val("");
    $("#txtCantidad").val("");
}


function mostrarNumero(idComprobante) {
    $.ajax({
        url: '../modelo/DAOventa.php',
        type: 'POST',
        data: {
            ev: 5,
            idtipo_comprobante: idComprobante
        },
        dataType: 'json',
        success: function (response) {
            if (response.success && response.num_comprobanteV) {
                $("#txtNumero").val(response.num_comprobanteV);
            } else if (response.success) {
                $("#txtNumero").val("");
            } else {
                $("#txtNumero").val("");
                alert(response.message || "Error inesperado.");
            }
        },
        error: function () {
            alert("Error al obtener los datos del comprobante.");
        }
    });
}


function listadoProductos(idInput, idLista) {
    const input = document.getElementById(idInput);
    const lista = document.getElementById(idLista);

    input.addEventListener("input", () => {
        const entrada = input.value.trim().toLowerCase();
        lista.innerHTML = "";

        if (entrada) {
            $.ajax({
                url: "../modelo/DAOventa.php",
                type: "POST",
                dataType: "json",
                data: {
                    ev: 4,
                    entrada: entrada,
                },
                success: function (response) {
                    if (response.success && Array.isArray(response.data)) {
                        const filteredData = response.data.filter(
                            (item) =>
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
                            li.textContent = `${item.val2} - Precio: ${item.val3} (${item.val4})`;

                            li.addEventListener("click", () => {
                                input.value = item.val2;
                                lista.innerHTML = "";
                                mostrarProducto(item.val2);
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


function mostrarProducto(productoDato) {
    $.ajax({
        url: '../modelo/DAOventa.php',
        type: 'POST',
        data: {
            ev: 7,
            producto: productoDato
        },
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                $("#txtPrecio").val(response.data.precio_venta);
                $("#txtStock").val(response.data.stock_producto);
                const stockProducto = response.data.stock_producto;
                $("#txtCantidad").attr("max", stockProducto);
                $("#txtCantidad").val(Math.min($("#txtCantidad").val(), stockProducto));
            } else {
                $("#txtPrecio").val('');
                $("#txtStock").val('');
                alert("Producto no encontrado.");
            }
        },
        error: function () {
            alert("Error al obtener la información del producto.");
        }
    });
}


function cargarOpciones(idCombo, funcionVer) {
    $.ajax({
        url: "../modelo/DAOventa.php",
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
