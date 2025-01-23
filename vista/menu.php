
<!DOCTYPE html>
<html lang="en">

<head>
    <style>

        
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <?php include '../controlador/link.php'; ?>

    <link rel="stylesheet" href="../imagenes/estilos.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="main-wrapper">
        <?php include '../controlador/header.php'; ?>
        <?php include '../controlador/slider.php'; ?>
        <div class="page-wrapper">
            <div class="">

                <div class="container_all" id="container__all">
                    <div class="cover">

                        <!--INICIO WAVE-->
                        <div class="bg_color"></div>
                        <div class="wave w2"></div>

                        <div class="container__cover">
                            <div class="container__info">
                                <h1>Bienvenidos a FRAPPE S.A.</h1>
                                <h2>Innovando para transformar</h2>
                                <p>Nos enorgullece ser tu aliado estratégico en soluciones innovadoras y de calidad.</p>
                                
                            </div>
                            <div class="container__vector">
                                <img  class="imagen_bg" src="../imagenes/frappe_1.png" alt="">
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

    </div>
   

    <?php include '../controlador/scrips.php'; ?>

    <script>
        window.onscroll = function() {
            scroll = document.documentElement.scrollTop;
            header = document.getElementById("header");
            if (scroll > 20) {

            } else if (scroll < 20) {
                header.classList.remove('nav_mod');
            }
        }
    </script>

</body>

</html>
