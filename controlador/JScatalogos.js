$(document).ready(function () {
    mostrar("");
    visualizarImagen('inputImagen', 'previewImagen');
    visualizarImagen('inputImagenE', 'previewImagenE');

    cargarOpciones("#cbCategoria", "obtenerCategorias");
    cargarOpciones("#cbSabor", "obtenerSabores");
    cargarOpciones("#cbPresentacion", "obtenerPresentaciones");

    $("#txbuscar").on("keyup", function () {
        mostrar($(this).val());
    });
});


function mostrar(dta) {
    $.ajax({
        url: "../modelo/DAOcatalogos.php",
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
    const img = $("#inputImagen")[0].files[0];
    const sabor = $("#cbSabor").val();
    const categoria = $("#cbCategoria").val();
    const presentacion = $("#cbPresentacion").val();

    if (!img || !sabor.trim() || !categoria.trim() || !presentacion.trim()) {
        alert("Todos los campos son obligatorios, incluida la imagen.");
        return;
    }
    const formData = new FormData();
    formData.append("ev", 1);
    formData.append("img", img);
    formData.append("sabor", sabor);
    formData.append("categoria", categoria);
    formData.append("presentacion", presentacion);
    $.ajax({
        url: "../modelo/DAOcatalogos.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (msg) {
            $("#cbSabor").val("");
            $("#cbCategoria").val("");
            $("#cbPresentacion").val("");
            $("#inputImagen").val("");
            $("#previewImagen").addClass("d-none").attr("src", "#");
            $("#modalAgregar").modal("hide");
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha creado correctamente.", "success");
        },
        error: function () {
            mostrarAlerta("Error", "No se pudo crear el registro. Inténtalo de nuevo.", "danger");
        },
    });
});


$("#btnModificar").on("click", function (e) {
    e.preventDefault();
    const ids = $("#txtid").val();
    const img = $("#inputImagenE")[0].files[0]; 
    const sabor = $("#cbSaborE").val();
    const categoria = $("#cbCategoriaE").val();
    const presentacion = $("#cbPresentacionE").val();

    if (!sabor.trim() || !categoria.trim() || !presentacion.trim()) {
        alert("Todos los campos son obligatorios.");
        return;
    }
    const formData = new FormData();
    formData.append("ev", 2);
    formData.append("img", img);
    formData.append("sabor", sabor);
    formData.append("categoria", categoria);
    formData.append("presentacion", presentacion);
    formData.append("ids", ids);

    $.ajax({
        url: "../modelo/DAOcatalogos.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (msg) {
            $("#cbSaborE").val("");
            $("#cbCategoriaE").val("");
            $("#cbPresentacionE").val("");
            $("#inputImagenE").val("");
            $("#previewImagenE").addClass("d-none").attr("src", "#");
            $('#txtid').val("");
            $("#modalEditar").modal("hide");
            mostrar("");
            mostrarAlerta("¡Proceso Exitoso!", "El registro se ha modificado correctamente.", "success");
        },
        error: function () {
            mostrarAlerta("Error", "No se pudo modificar el registro. Inténtalo de nuevo.", "danger");
        },
    });
});

$('#btnEliminar').on('click', function (e) {
    const ids = $(this).attr('data-id');
    if (!ids) {
        alert('No se ha seleccionado un registro para eliminar.');
        return;
    }

    $.ajax({
        url: '../modelo/DAOcatalogos.php',
        type: 'post',
        data: {
            ev: 3,
            ids: ids
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
    const ids = idrow;
    $('#txtid').val(idrow);

    $.ajax({
        url: "../modelo/DAOcatalogos.php",
        type: "POST",
        data: {
            ev: 4,
            ids: ids,
        },
        success: function (data) {
            if (typeof data === "string") {
                data = JSON.parse(data);
            }
            
            if (data.success) {
                cargarOpciones("#cbCategoriaE", "obtenerCategorias");
                cargarOpciones("#cbSaborE", "obtenerSabores");
                cargarOpciones("#cbPresentacionE", "obtenerPresentaciones");
                setTimeout(function () {
                    $("#cbSaborE").val(data.idsabor);
                }, 100);
                setTimeout(function () {
                    $("#cbCategoriaE").val(data.idcategoria);
                }, 100);
                setTimeout(function () {
                    $("#cbPresentacionE").val(data.idpresentacion);
                }, 100); 
                $("#previewImagenE").attr("src", "../image/" + data.img).removeClass("d-none");
                $("#nombreImagenE").text(data.img);
                $("#modalEditar").modal("show");
            } else {
                alert("No se encontraron datos.");
            }
        },
        error: function (xhr, status, error) {
            console.log(data);
            console.log("Estado: " + status);
            console.log("Error: " + error);
            console.log("Respuesta: " + xhr.responseText);
            alert("Error al consultar los datos del servidor.");
        },
    });
}



function verDetalle(idrow) {
    const ids = idrow;
    $.ajax({
        url: "../modelo/DAOcatalogos.php",
        type: "POST",
        data: {
            ev: 4,
            ids: ids,
        },
        success: function (data) {
            if (typeof data === "string") {
                data = JSON.parse(data);
            }
            if (data.success) {
                $("#categoriaV").text(data.categoria);
                $("#saborV").text(data.sabor);
                $("#presentacionV").text(data.presentacion);
                $("#previewImagenV").attr("src", "../image/" + data.img).removeClass("d-none");
 
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

function cargarOpciones(idCombo, funcionVer) {
    $.ajax({
        url: "../modelo/DAOcatalogos.php",
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

function visualizarImagen(imput, imgPreview) {
    document.getElementById(imput).addEventListener('change', function (event) {
        const preview = document.getElementById(imgPreview);
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.classList.add('d-none');
        }
    });
}
