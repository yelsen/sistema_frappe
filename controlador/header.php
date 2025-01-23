<?php
    session_start();
    // Verificar si hay datos en la sesión
    if (!isset($_SESSION['apellidos']) || !isset($_SESSION['nombres'])) {
        header("Location: ../vista/login.php");
        exit();
    }
    $apellidos = $_SESSION['apellidos'];
    $nombres = $_SESSION['nombres'];
?>

<div class="header">
    <div class="header-left">
        <a href="menu.php" class="logo">
            <img src="../imagenes/logo_frase.png" alt="Logo">
        </a>
        <a href="menu.php" class="logo logo-small">
            <img src="../imagenes/logo_small.png" alt="Logo" width="30" height="30">
        </a>
    </div>

    <div class="menu-toggle">
        <a href="javascript:void(0);" id="toggle_btn">
            <i class="fas fa-bars"></i>
        </a>
    </div>

    <!--
    <div class="top-nav-search">
        <form>
            <input type="text" class="form-control" placeholder="Buscar aqui">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
        -->

    <div class="top-nav-search">
        <form>
            <a href="https://www.sunat.gob.pe/" class="logo" target="_blank">
                <img src="../imagenes/sunat.png" alt="Logo" width="150" height="40">
            </a>
        </form>
    </div>


    <a class="mobile_btn" id="mobile_btn">
        <i class="fas fa-bars"></i>
    </a>

    <ul class="nav user-menu">

        <li class="nav-item dropdown noti-dropdown me-2">
            <a href="#" class="dropdown-toggle nav-link header-nav-list" data-bs-toggle="dropdown">
                <img src="../imagenes/icons/header-icon-05.svg" alt="">
            </a>
        </li>

        <li class="nav-item zoom-screen me-2">
            <a href="#" class="nav-link header-nav-list win-maximize">
                <img src="../imagenes/icons/header-icon-04.svg" alt="">
            </a>
        </li>

        <li class="nav-item dropdown has-arrow new-user-menus">
            <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                <span class="user-img">
                    <img class="rounded-circle" src="../imagenes/img.jpeg" width="31"
                        alt="Soeng Souy">
                    <div class="user-text">
                        <h6><?php echo $nombres . ' ' . $apellidos; ?></h6> <!-- afuera-->
                        <p class="text-muted mb-0">Administrator</p>
                    </div>
                </span>
            </a>
            <div class="dropdown-menu">
                <div class="user-header">
                    <div class="avatar avatar-sm">
                        <img src="../imagenes/img.jpeg" alt="User Image"
                            class="avatar-img rounded-circle">
                    </div>
                    <div class="user-text">
                        <h6><?php echo $nombres . ' ' . $apellidos; ?></h6><!-- dentro-->
                        <p class="text-muted mb-0">Administrator</p>
                    </div>
                </div>
                <a class="dropdown-item" href="profile.html">Mi perfil</a>
                <a class="dropdown-item" href="inbox.html">Bandej de entrada</a>
                <a class="dropdown-item" href="login.html">Cerrar sesión</a>
            </div>
        </li>
    </ul>
</div>