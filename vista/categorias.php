<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <?php include '../controlador/link.php'; ?>
</head>

<body>
    <div class="main-wrapper">
        <?php include '../controlador/header.php'; ?>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li class="menu-title">
                            <span>Menu Principal</span>
                        </li>
                        <li class="">
                            <a href="ventas.php"><i class="fas fa-holly-berry"></i><span> Ventas</span></a>
                        </li>
                        <li class="">
                            <a href="compras.php"><i class="fas fa-holly-berry"></i><span> Compras</span></a>
                        </li>
                        <li class="">
                            <a href="utilidades.php"><i class="fas fa-holly-berry"></i><span> Utilidades</span></a>
                        </li>

                        <li class="menu-title">
                            <span>Reportes</span>
                        </li>
                        <li class="submenu ">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Reporte Financiero</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="#">Rentabilidad</a></li>
                                <li><a class="" href="#">Ingresos</a></li>
                                <li><a class="" href="#">Egresos</a></li>
                                <li><a class="" href="#">Planilla</a></li>

                            </ul>
                        </li>
                        <li class="menu-title">
                            <span>Productos</span>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Productos</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="#">Productos</a></li>
                                <li><a class="" href="catalogos.php">Catalogos</a></li>
                                <li><a class="" href="detalle_insumos.php">Regla Insumos</a></li>

                            </ul>
                        </li>


                        <li class="menu-title">
                            <span>Inventarios</span>
                        </li>
                        <li class="submenu active">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Inventarios</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="insumos.php">Insumos</a></li>
                                <li><a class="active" href="categorias.php">Categorias</a></li>
                                <li><a class="" href="sabores.php">Sabores</a></li>
                                <li><a class="" href="presentaciones.php">Presentaciones</a></li>
                            </ul>
                        </li>


                        <li class="menu-title">
                            <span>Proveedores</span>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Proveedores</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="proveedores.php">Proveedores</a></li>
                                <li><a class="" href="empresas.php">Empresas</a></li>
                            </ul>
                        </li>

                        <li class="menu-title">
                            <span>Personal</span>
                        </li>
                        <li class="submenu ">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Personal</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="personal.php">Personal</a></li>

                            </ul>
                        </li>



                        <li class="menu-title">
                            <span>Administración</span>
                        </li>
                        <li class="submenu ">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Administración</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="usuarios.php">Usuarios</a></li>
                                <li><a class="" href="roles.php">Roles</a></li>
                                <li><a class="" href="tipo_comprobante.php">Tipos de Comprobantes</a></li>

                            </ul>
                        </li>

                    </ul>
                </div>
            </div>
        </div>

        <div class="page-wrapper">
            <div class="content container-fluid">
                <!-- grafica -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="page-header ">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="page-title">Gestión de Categorias</h3>
                                        </div>
                                        <div class="col-auto text-end float-end ms-auto download-grp">
                                            <button type="button" title="Registrar datos" class="btn btn-primary w-100" data-bs-toggle="modal"
                                                data-bs-target="#modalAgregar">Registrar</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- notificacion -->
                                <div id="miAlerta" class="alert alert-dismissible fade show" role="alert" style="display: none;">
                                    <strong id="alertTitulo"></strong> <span id="alertMensaje"></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <!-- buscador -->
                                <div class="col">
                                    <div class="form-group input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" name="txbuscar" id="txbuscar" class="form-control" id="" placeholder="Buscar aqui">
                                    </div>
                                </div>
                                <!-- tabla con datos -->
                                <div id="tabla"></div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal añadir-->
                <div class="modal fade" data-bs-backdrop="static" tabindex="-1" id="modalAgregar" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Registro de Categorias</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="field-3" class="form-label">Categorias</label>
                                            <input type="text" id="txtCategorias" class="form-control" placeholder="Categorias">
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" id="btnCancelar" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                                <button type="button" id="btnAgregar" class="btn btn-primary waves-effect waves-light">Guardar Registro</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal editar-->
                <div class="modal fade" id="modalEditar" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Editar Categorias</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="field-3" class="form-label">Categorias</label>
                                            <input type="text" id="txtCategoriasE" class="form-control" placeholder="Categorias">
                                            <input type="hidden" id="txtid" class="form-control">
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" id="btnCancelar" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                                <button type="button" id="btnModificar" class="btn btn-primary waves-effect waves-light">Guardar Cambio</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal eliminar-->
                <div class="modal fade" id="modalEliminar" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalEliminarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                ¿Estás seguro de que deseas eliminar este registro?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" id="btnEliminar" class="btn btn-danger">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Ver Detalles -->
                <div class="modal fade" id="modalDetalles" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalDetallesLabel">Detalles del Registro</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="detalleId" class="form-label">ID</label>
                                    <input type="text" id="detalleId" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="detalleCategorias" class="form-label">Categorias</label>
                                    <input type="text" id="detalleCategorias" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <?php include '../controlador/scrips.php'; ?>

    <script src="../controlador/JScategorias.js"></script>
</body>

</html>