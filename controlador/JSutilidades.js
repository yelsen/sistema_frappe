$('#btnBuscar').on('click', function (e) {
    e.preventDefault();
    const fe1 = $('#txtFecha1').val();
    const fe2 = $('#txtFecha2').val();
    if (fe1.trim() === "" && fe2.trim() === "") {
        alert('Los campos no pueden estar vacíos');
        return;
    }
    mostrarIngresos(fe1, fe2);
    mostrarEgresos(fe1, fe2);
    setTimeout(function() {
        const ingresoStr = $('#txtIngreso').val().replace(/,/g, '');
        const egresoStr = $('#txtEgreso').val().replace(/,/g, '');
        const ingreso = parseFloat(ingresoStr) || 0;
        const egreso = parseFloat(egresoStr) || 0;
        const utilidad = ingreso - egreso;
        const utilidadFormateada = utilidad.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#txtUtilidad').val(utilidadFormateada);
    }, 500);
});




$('#btnExportar').on('click', function (e) {
    e.preventDefault();
    var fechaInicio = $('#txtFecha1').val();
    var fechaFin = $('#txtFecha2').val();

    if (!fechaInicio || !fechaFin) {
        alert('Por favor, selecciona ambas fechas.');
        return;
    }

    $.ajax({
        url: '../modelo/utilidades.php', 
        type: 'POST',
        data: { 
            fechaInicio: fechaInicio, 
            fechaFin: fechaFin 
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function (response) {
            var blob = new Blob([response], { type: 'application/pdf' });
            var url = window.URL.createObjectURL(blob);
            window.open(url, '_blank');
        },
        error: function (xhr, status, error) {
            alert('Error al generar el reporte: ' + error);
        }
    });
});







function mostrarIngresos(fech1, fech2) {
    $.ajax({
        url: '../modelo/DAOutilidades.php',
        type: 'post',
        data: {
            ev: 0,
            fech1: fech1,
            fech2: fech2
        },
        success: function (response) {
            try {
                const data = JSON.parse(response);
                if (data.tabla && data.total_monto !== undefined) {
                    $("#tabla1").html(data.tabla); 
                    $('#txtIngreso').val(data.total_monto);
                } else {
                    swal('Aviso', 'No se pudieron obtener los datos de ingresos correctamente', 'warning');
                }
            } catch (e) {
                swal('Error', 'Hubo un error al procesar la respuesta del servidor: ' + e.message, 'error');
            }
        },
        error: function (xml, msg) {
            swal('Aviso', msg.trim(), 'error');
        }
    });
}

function mostrarEgresos(fech1, fech2) {
    $.ajax({
        url: '../modelo/DAOutilidades.php',
        type: 'post',
        data: {
            ev: 1,
            fech1: fech1,
            fech2: fech2
        },
        success: function (response) {
            try {
                const data = JSON.parse(response);
                if (data.tabla && data.total_egreso !== undefined) {
                    $("#tabla2").html(data.tabla);  
                    $('#txtEgreso').val(data.total_egreso);
                } else {
                    swal('Aviso', 'No se pudieron obtener los datos de egresos correctamente', 'warning');
                }
            } catch (e) {
                swal('Error', 'Hubo un error al procesar la respuesta del servidor: ' + e.message, 'error');
            }
        },
        error: function (xml, msg) {
            swal('Aviso', msg.trim(), 'error');
        }
    });
}

function toggleDetalle(detalleId) {
    var detalleDiv = document.getElementById(detalleId);
    if (detalleDiv.style.display === 'none' || detalleDiv.style.display === '') {
        detalleDiv.style.display = 'block';
    } else {
        detalleDiv.style.display = 'none';
    }
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
