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
                        <li class="active">
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
                        <li class="submenu ">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Inventarios</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="insumos.php">Insumos</a></li>
                                <li><a class="" href="categorias.php">Categorias</a></li>
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
                        <li class="submenu active">
                            <a href="#"><i class="fas fa-file-invoice-dollar"></i><span> Administración</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a class="" href="usuarios.php">Usuarios</a></li>
                                <li><a class="active" href="roles.php">Roles</a></li>
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
                                            <h3 class="page-title">Reporte de Utilidades</h3>

                                        </div>

                                        <div class="col-auto text-end float-end ms-auto download-grp">
                                            <button type="button" id="btnExportar" class="btn btn-primary">Exportar Registro</button>
                                            <button type="button" id="btnBuscar" class="btn btn-primary">Buscar Registro</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Desde</strong></label>
                                            <input type="date" id="txtFecha1" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Hasta</strong></label>
                                            <input type="date" id="txtFecha2" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <!-- notificacion -->
                                <div id="miAlerta" class="alert alert-dismissible fade show" role="alert" style="display: none;">
                                    <strong id="alertTitulo"></strong> <span id="alertMensaje"></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <h5 class="page-title">Utilidad:</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="mb-3">
                                            <input type="text" id="txtUtilidad" class="form-control" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">

                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h5 class="page-title">Ingresos</h5>
                                                </div>
                                                <div class="col-auto text-end float-end ms-auto download-grp">
                                                    <input type="text" id="txtIngreso" class="form-control" placeholder="0.00" readonly>
                                                </div>
                                            </div>

                                            <!-- tabla con datos -->
                                            <div id="tabla1"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h5 class="page-title">Egresos</h5>
                                                </div>
                                                <div class="col-auto text-end float-end ms-auto download-grp">
                                                    <input type="text" id="txtEgreso" class="form-control" placeholder="0.00" readonly>
                                                </div>
                                            </div>
                                            <!-- tabla con datos -->
                                            <div id="tabla2"></div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>

    </div>

    <?php include '../controlador/scrips.php'; ?>

    <script src="../controlador/JSutilidades.js"></script>

</html>