<?php
session_start();
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : null;
$user_logged_in = isset($_SESSION['user_id']);

// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CineWeeknd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obter a lista de filmes
$result = $conn->query("SELECT id, titulo, ano, genero FROM filmes");
$filmes = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    
    <!--====== Title ======-->
    <title>CineWeeknd</title>
    
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--====== Favicon Icon ======-->
    <link rel="shortcut icon" href="assets/images/icon.jpg" type="image/png">
        
    <!--====== Animate CSS ======-->
    <link rel="stylesheet" href="assets/css/animate.css">
        
    <!--====== Glide CSS ======-->
    <link rel="stylesheet" href="assets/css/tiny-slider.css">
        
    <!--====== Line Icons CSS ======-->
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css">
        
    <!--====== Bootstrap CSS ======-->
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    
    <!--====== Default CSS ======-->
    <link rel="stylesheet" href="assets/css/default.css">
    
    <!--====== Style CSS ======-->
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>

<body>
    <!--[if IE]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->

    <!--====== PRELOADER PART START ======-->
    <div class="preloader">
        <div class="loader">
            <div class="ytp-spinner">
                <div class="ytp-spinner-container">
                    <div class="ytp-spinner-rotator">
                        <div class="ytp-spinner-left">
                            <div class="ytp-spinner-circle"></div>
                        </div>
                        <div class="ytp-spinner-right">
                            <div class="ytp-spinner-circle"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--====== PRELOADER PART ENDS ======-->

    <!--====== HEADER PART START ======-->
    <section id="home" class="header_area">
        <div id="header_navbar" class="header_navbar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <nav class="navbar navbar-expand-lg">
                            <a class="navbar-brand" href="index.php">
                                <img id="logo" src="assets/images/logo.svg" alt="Logo">
                            </a>
                            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <ul id="nav" class="navbar-nav ms-auto">
                                    <li class="nav-item">
                                        <a class="page-scroll active" href="#home">INICIAL</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" href="#about">SOBRE NÓS</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" href="#portfolio">CARTAZ</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" href="#team">NOSSA EQUIPE</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" href="#contact">CONTATO</a>
                                    </li>
                                    <li class="nav-item">
                                        <?php if ($user_logged_in): ?>
                                            <a id="history" href="purchase_history.php">HISTÓRICO DE COMPRAS</a><br>
                                            <a id="profile" href="profile.php"><?php echo htmlspecialchars($_SESSION['user_name']); ?></a><br>
                                            <a id="logout" href="logout.php">LOGOUT</a><br>
                                        <?php else: ?>
                                            <a id="loginn" name="loginn" href="paglog.php">LOGIN</a><br>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </div> <!-- navbar collapse -->
                        </nav> <!-- navbar -->
                    </div>
                </div> <!-- row -->
            </div> <!-- container -->
        </div> <!-- header navbar -->
    </section>
    <div class="header_hero">
        <div class="single_hero bg_cover d-flex align-items-center" style="background-image: url(assets/images/hero.jpg)">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="hero_content text-center">
                            <h2 class="hero_title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">CineWeeknd</br> </h2>
                            <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.5s">Bem-vindo ao nosso mundo do cinema! Explore os últimos lançamentos, clássicos imperdíveis e novidades exclusivas em um só lugar. Prepare a pipoca e embarque nessa aventura cinematográfica!<br class="d-none d-xl-block"> </p>
                            <a href="registro.php" target="_blank" class="main-btn wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.8s">Registrar</a>
                        </div> <!-- hero content -->
                    
                    </div>
                </div> <!-- row -->
            </div> <!-- container -->
        </div> <!-- single hero -->
    </div> <!-- header hero -->
    </section>
    <!--====== HEADER PART ENDS ======-->

    <!--====== FEATURES PART START ======-->
    <section id="features" class="features_area pt-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="section_title text-center pb-25">
                        <h4 class="title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">Por quê nos escolher?</h4>
                        <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">.</p>
                    </div> <!-- section title -->
                </div>
            </div> <!-- row -->
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-7">
                    <div class="single_features text-center mt-30 wow fadeInUp" data-wow-duration="1.3s"
                        data-wow-delay="0.2s">
                        <i class="<?xml version="1.0" encoding="utf-8"?>
<!-- Generator: Adobe Illustrator 22.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-star-half" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
     y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
<g>
    <path d="M45.2,27.4c1.2,0,2.2-1,2.2-2.2c0-1.2-0.9-2.3-2.2-2.3l-4.3-0.2l-1.5-3.8c-0.4-1.2-1.7-1.7-2.9-1.3
        c-1.2,0.4-1.7,1.8-1.3,2.9l2,5.2c0.3,0.8,1.1,1.4,2,1.4L45.2,27.4C45.1,27.4,45.2,27.4,45.2,27.4z"/>
    <path d="M62.1,25.3c-0.3-1.1-1.3-1.8-2.4-1.8l-5.9-0.2c-1.2-0.1-2.3,0.9-2.3,2.2c0,1.2,0.9,2.3,2.2,2.3l0.7,0l-3,2.3
        c-1,0.7-1.2,2.2-0.4,3.2c0.4,0.6,1.1,0.9,1.8,0.9c0.5,0,0.9-0.1,1.4-0.5l7.2-5.4C62.1,27.5,62.5,26.3,62.1,25.3z"/>
    <path d="M46.1,33.9l-3.5,2.6c-0.7,0.6-1.1,1.5-0.8,2.4l1.2,4.2c0.3,1,1.2,1.6,2.2,1.6c0.2,0,0.4,0,0.6-0.1c1.2-0.4,1.9-1.6,1.5-2.8
        l-0.8-2.7l2.3-1.7c1-0.7,1.2-2.2,0.4-3.2C48.5,33.4,47.1,33.2,46.1,33.9z"/>
    <path d="M50,50.9c-0.3-1.2-1.6-1.9-2.8-1.5c-1.2,0.4-1.9,1.6-1.5,2.8l0.2,0.7L43.1,51c-1-0.7-2.4-0.4-3.1,0.7
        c-0.7,1-0.4,2.4,0.7,3.1l7,4.5c0.4,0.3,0.9,0.4,1.4,0.4c0.6,0,1.1-0.2,1.6-0.5c0.9-0.7,1.3-1.8,0.9-2.8L50,50.9z"/>
    <path d="M38,47.8l-4.8-3.1c0,0,0,0-0.1,0c-0.1-0.1-0.3-0.2-0.5-0.2c0,0-0.1,0-0.1,0c-0.2,0-0.4-0.1-0.6-0.1c0,0-0.1,0-0.1,0
        c-0.2,0-0.3,0-0.5,0.1c0,0-0.1,0-0.1,0c-0.2,0.1-0.4,0.1-0.5,0.2l-12.7,8.1L22.2,39c0.3-0.9-0.1-1.9-0.8-2.4L9.7,27.8l15-0.6
        c0.9,0,1.7-0.6,2-1.4L32,12.1l0.5,1.4c0.3,0.9,1.2,1.4,2.1,1.4c0.3,0,0.5,0,0.8-0.2c1.2-0.4,1.7-1.8,1.3-2.9L34.5,6
        c-0.4-1-1.4-1.7-2.5-1.7c-1.1,0-2.1,0.7-2.5,1.7l-6.5,16.8L4.3,23.5c-1.1,0-2.1,0.8-2.4,1.8c-0.3,1,0,2.2,0.9,2.9l14.7,11.1
        l-5,17.1c-0.3,1.1,0.1,2.2,0.9,2.8c0.5,0.4,1,0.5,1.6,0.5c0.5,0,1-0.1,1.4-0.4l15.6-10l3.6,2.3c0.4,0.2,0.8,0.4,1.2,0.4
        c0.7,0,1.5-0.4,1.9-1C39.4,49.9,39.1,48.5,38,47.8z"/>
</g>
</svg>
</i>
                        <h4 class="features_title">Experiência 10/10</a></h4>
                        <p>CineWeeknd proporciona a melhor experiência para todas as idades.</p>
                    </div> <!-- single features -->
                </div>
                <div class="col-lg-4 col-md-7">
                    <div class="single_features text-center mt-30 wow fadeInUp" data-wow-duration="1.3s"
                        data-wow-delay="0.4s">
                        <i class="<?xml version="1.0" encoding="utf-8"?>
<!-- Generator: Adobe Illustrator 22.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-map-marker" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
     y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
<g>
    <path d="M32,1.8C18.2,1.8,7,12.6,7,25.9C7,36,20.4,52,28.3,60.6c1,1.1,2.3,1.6,3.7,1.6c1.4,0,2.7-0.6,3.7-1.6
        C43.6,52,57,36,57,25.9C57,12.6,45.8,1.8,32,1.8z M32.4,57.6c-0.2,0.2-0.5,0.2-0.8,0C21.9,47,11.5,33.2,11.5,25.9
        c0-10.8,9.2-19.6,20.5-19.6s20.5,8.8,20.5,19.6C52.5,33.2,42.1,47,32.4,57.6z"/>
    <path d="M32,15.7c-6,0-10.9,4.9-10.9,10.9S26,37.6,32,37.6s10.9-4.9,10.9-10.9S38,15.7,32,15.7z M32,33.1c-3.6,0-6.4-2.9-6.4-6.4
        s2.9-6.4,6.4-6.4s6.4,2.9,6.4,6.4S35.6,33.1,32,33.1z"/>
</g>
</svg>
</i>
                        <h4 class="features_title">Fácil Localização</a></h4>
                        <p>Estamos presente em todos os Estados do Brasil!</p>
                    </div> <!-- single features -->
                </div>
                <div class="col-lg-4 col-md-7">
                    <div class="single_features text-center mt-30 wow fadeInUp" data-wow-duration="1.3s"
                        data-wow-delay="0.6s">
                        <i class="<?xml version="1.0" encoding="utf-8"?>
<!-- Generator: Adobe Illustrator 25.2.1, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-coin" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
     y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
<g>
    <path d="M35.8,29.8h-7.1c-2.1,0-3.8-1.6-3.8-3.6c0-2,1.7-3.6,3.8-3.6H39c1.2,0,2.2-1,2.2-2.2s-1-2.2-2.2-2.2h-4.8v-2.4
        c0-1.2-1-2.2-2.2-2.2c-1.2,0-2.2,1-2.2,2.2v2.4h-1.1c-4.6,0-8.3,3.6-8.3,8.1s3.7,8.1,8.3,8.1h7.1c2.1,0,3.8,1.6,3.8,3.6
        c0,2-1.7,3.6-3.8,3.6H25c-1.2,0-2.2,1-2.2,2.2s1,2.2,2.2,2.2h4.8v2.4c0,1.2,1,2.2,2.2,2.2c1.2,0,2.2-1,2.2-2.2v-2.4h1.6
        c4.6,0,8.3-3.6,8.3-8.1C44.2,33.4,40.4,29.8,35.8,29.8z"/>
    <path d="M32,1.8C15.3,1.8,1.8,15.3,1.8,32S15.3,62.2,32,62.2S62.2,48.7,62.2,32S48.7,1.8,32,1.8z M32,57.8
        C17.8,57.8,6.2,46.2,6.2,32C6.2,17.8,17.8,6.2,32,6.2c14.2,0,25.8,11.6,25.8,25.8C57.8,46.2,46.2,57.8,32,57.8z"/>
</g>
</svg>
</i>
                        <h4 class="features_title">Promoção semanal</a></h4>
                        <p>Toda semana temos promoções abaixos dos R$10,00!</p>
                    </div> <!-- single features -->
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </section>
    <!--====== FEATURES PART ENDS ======-->

    <!--====== ABOUT PART START ======-->
    <section id="about" class="pt-130">
        <div class="about_area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="about_content pt-120 pb-130">
                            <div class="section_title pb">
                                <h4 class="title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">CineWeeknd: Sua Porta para o Mundo dos Filmes</h4>
                                <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">Há cinco anos, o CineWeeknd começou sua jornada em uma pequena cidade do interior. Com uma proposta inovadora e um ambiente acolhedor, rapidamente conquistou o coração dos cinéfilos locais. Hoje, o CineWeeknd está presente em todas as regiões do Brasil, proporcionando uma experiência única para os amantes da sétima arte.</p>
                                <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.6s">Cada unidade do CineWeeknd oferece não apenas as últimas novidades do cinema, mas também eventos especiais e sessões temáticas que celebram a diversidade cultural do país. Com um compromisso inabalável com a qualidade e a satisfação do público, o CineMundo continua a expandir seu legado, tornando-se referência no cenário cinematográfico nacional.</p>
                            </div> <!-- section title -->
                        </div> <!-- about content -->
                    </div>
                </div> <!-- row -->
            </div> <!-- container -->
            
            <div class="about_image bg_cover wow fadeInLeft" data-wow-duration="1.3s" data-wow-delay="0.2s"
                style="background-image: url(assets/images/pessoas.jpg)">
                <div class="image_content">
                    <h4 class="experience"><span>5</span> Anos de Diversão </h4>
                </div>
            </div> <!-- about image -->
        </div>
    </section>
    <!--====== ABOUT PART ENDS ======-->

    <!--====== PORTFOLIO PART START ======-->
    <section id="portfolio" class="portfolio_area pt-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="section_title text-center pb-60">
                        <h4 class="title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">Filmes em cartaz</h4>
                        <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">Todos os filmes estarão em promoção a partir do dia 11/06, com preços abaixo de R$20,00 (inteira)!</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="portfolio_wrapper d-flex flex-wrap">
            <?php foreach ($filmes as $index => $filme): ?>
                <div class="single_portfolio wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.<?= 2 + $index % 4 ?>s">
                    <a href="<?php echo $user_logged_in ? 'purchase.php?filme_id=' . $filme['id'] : 'paglog.php'; ?>">
                        <img src="assets/images/portfolio-<?php echo $filme['id']; ?>.jpg" alt="<?php echo htmlspecialchars($filme['titulo']); ?>">
                        <div class="portfolio_content">
                            <ul class="meta">
                                <li><i class="lni lni-link"></i></li>
                            </ul>
                            <h5 class="portfolio_title"><?php echo htmlspecialchars($filme['titulo']); ?></h5>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <!--====== PORTFOLIO PART ENDS ======-->

    <!--====== PRICING PART START ======-->
    <section id="pricing" class="pricing_area pt-120 pb-130">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="section_title text-center pb-25">
                        <h4 class="title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">Combos!</h4>
                        <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">Dê uma olhada nos nossos combos de pipoca e aperitivos.</p>
                    </div> <!-- section title -->
                </div>
            </div> <!-- row -->
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-8 col-sm-10">
                    <div class="single_pricing text-center mt-30 wow fadeInUp" data-wow-duration="1.3s"
                        data-wow-delay="0.2s">
                        <h4 class="pricing_title">Pipoca M <br>+ Coca-Cola 300Ml</h4>
                        <span class="price">R$20</span>
                        <h4 class="pricing_title">Opções:</h4>
                        <ul class="pricing_list">
                            <li>Pipoca Doce</li>
                            <li>Pipoca S/ Sal</li>
                            <li>Pipoca com Nutella (+R$5)</li>
                            <li>Coca-Cola 500Ml (+R$3)</li>
                        </ul>
                        <a href="purchase_form.php?combo_id=1" class="mian-btn">EU QUEROO!!</a>
                    </div> <!-- single pricing -->
                </div>
                <div class="col-lg-4 col-md-8 col-sm-10">
                    <div class="single_pricing text-center mt-30 active wow fadeInUp" data-wow-duration="1.3s"
                        data-wow-delay="0.3s">
                        <h4 class="pricing_title">2 Pipocas G <br>+ 2 Coca-Cola 500Ml <br> (Combo Casal)</h4>
                        <span class="price">R$40</span>
                        <h4 class="pricing_title">Opções:</h4>
                        <ul class="pricing_list">
                            <li>Pipoca Doce</li>
                            <li>Pipoca S/ Sal</li>
                            <li>Pipoca com Nutella (+R$7)</li>
                            <li>Coca-Cola 700Ml (+R$5)</li>
                        </ul>
                        <a href="purchase_form.php?combo_id=2" class="mian-btn">EU QUEROO!!</a>
                    </div> <!-- single pricing -->
                </div>
                <div class="col-lg-4 col-md-8 col-sm-10">
                    <div class="single_pricing text-center mt-30 wow fadeInUp" data-wow-duration="1.3s"
                        data-wow-delay="0.4s">
                        <h4 class="pricing_title">Pipoca GG <br>+ Coca-Cola 1L </h4>
                        <span class="price">R$35</span>
                        <h4 class="pricing_title">Opções:</h4>
                        <ul class="pricing_list">
                            <li>Pipoca Doce</li>
                            <li>Pipoca S/ Sal</li>
                            <li>Pipoca com Nutella (+R$10)</li>
                            <li>Coca-Cola 1,5L (+R$3)</li>
                        </ul>
                        <a href="purchase_form.php?combo_id=3" class="mian-btn">EU QUEROO!!</a>
                    </div> <!-- single pricing -->
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </section>
    <!--====== PRICING PART ENDS ======-->

    <!--====== TEAM PART START ======-->
    <section id="team" class="team_area pt-120 pb-130">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="section_title text-center pb-25">
                        <h4 class="title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">O TIME</h4>
                        <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">3 grandes gênios da modernidade da programação.</p>
                    </div> <!-- section title -->
                </div>
            </div> <!-- row -->
            <div class="row justify-content-center team_active">
                <div class="col-lg-4 col-md-8 col-sm-10">
                    <div class="single_team mt-30 wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">
                        <img src="assets/images/team-1.jpg" alt="team">
                        
                        <div class="team_content">
                            <h4 class="team_name"><a href="#0">HaazR.</a></h4>
                            <p>Desenvolvedor do Site</p>
                            <ul class="social">
                               
                                <li><a target="_blank" href="https://www.instagram.com/luccasfes_?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="><i class="lni lni-instagram-original"></i></a></li>
                                
                            </ul>
                        </div>
                    </div> <!-- single team -->
                </div>
                <div class="col-lg-4 col-md-8 col-sm-10">
                    <div class="single_team mt-30 wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.3s">
                        <img src="assets/images/team-2.jpg" alt="team">
                        
                        <div class="team_content">
                            <h4 class="team_name"><a href="#0">pedringameplay4k</a></h4>
                            <p>UI Designer</p>
                            <ul class="social">
                               
                                <li><a target="_blank" href="https://www.instagram.com/pedrohsc__/"><i class="lni lni-instagram-original"></i></a></li>
                                
                            </ul>
                        </div>
                    </div> <!-- single team -->
                </div>
                <div class="col-lg-4 col-md-8 col-sm-10">
                    <div class="single_team mt-30 wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">
                        <img src="assets/images/team-3.jpg" alt="team">
                        
                        <div class="team_content">
                            <h4 class="team_name"><a href="#0">Kaimochizuki</a></h4>
                            <p>Criador de Designs</p>
                            <ul class="social">
                               
                                <li><a target="_blank" href="https://www.instagram.com/____m4theus____?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="><i class="lni lni-instagram-original"></i></a></li>
                                
                            </ul>
                        </div>
                    </div> <!-- single team -->
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </section>
    <!--====== TEAM PART ENDS ======-->

    <!--====== CONTACT PART START ======-->
    <section id="contact" class="contact_area bg_cover pt-120 pb-130" style="background-image: url(assets/images/contact_bg.gif)">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="section_title section_title_2 text-center pb-25">
                        <h4 class="title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">Chama "nóis"!</h4>
                        <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">Quaisquer críticas, elogios, feedbacks, é só nos contatar através desse formulário abaixo!</p>
                    </div> <!-- section title -->
                </div>
            </div> <!-- row -->
            
            <form id="contact-form" action="assets/contact.php" method="post" class="wow fadeInUp"
                data-wow-duration="1.3s" data-wow-delay="0.4s">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="single_form">
                            <input type="text" placeholder="Nome" name="name" id="name" required>
                        </div> <!-- single form -->
                    </div>
                    <div class="col-lg-6">
                        <div class="single_form">
                            <input type="email" placeholder="Email" name="email" id="email" required>
                        </div> <!-- single form -->
                    </div>
                    <div class="col-lg-6">
                        <div class="single_form">
                            <input type="text" placeholder="N° de telefone" name="number" id="number" required>
                        </div> <!-- single form -->
                    </div>
                    <div class="col-lg-6">
                        <div class="single_form">
                            <input type="text" placeholder="Assunto" name="subject" id="subject" required>
                        </div> <!-- single form -->
                    </div>
                    <div class="col-lg-12">
                        <div class="single_form">
                            <textarea placeholder="Mensagem" name="message" id="message" required></textarea>
                        </div> <!-- single form -->
                    </div>
                    
                    <p class="form-message"></p>
                    
                    <div class="col-lg-12">
                        <div class="single_form text-center">
                            <button class="main-btn" type="submit">ENVIAR</button>
                        </div> <!-- single form -->
                    </div>
                </div> <!-- row -->
            </form>
        </div> <!-- container -->
    </section>
    <!--====== CONTACT PART ENDS ======-->

    <!--====== FOOTER PART START ======-->
    <footer id="footer" class="footer_area">
        <div class="container">
            <div class="footer_wrapper text-center d-lg-flex align-items-center justify-content-between">
                <p class="credit">Desenvolvido por HaazR, pedringameplay4k e Kaimochizuki <a href="https://uideck.com" rel="nofollow"></a></p>
                <div class="footer_social pt-15">
                    <ul>
                        <li><a target="_blank" href="https://www.instagram.com/colegio_projecao/"><i class="lni lni-instagram-original"></i></a></li>
                    </ul>
                </div> <!-- footer social -->
            </div> <!-- footer wrapper -->
        </div> <!-- container -->
    </footer>
    <!--====== FOOTER PART ENDS ======-->

    <!--====== BACK TO TOP PART START ======-->
    <a href="#" class="back-to-top"><i class="lni lni-chevron-up"></i></a>
    <!--====== BACK TO TOP PART ENDS ======-->

    <!--====== Bootstrap js ======-->
    <script src="assets/js/bootstrap.bundle-5.0.0-beta1.min.js"></script>

    <!--====== glide js ======-->
    <script src="assets/js/tiny-slider.js"></script>

    <!--====== wow js ======-->
    <script src="assets/js/wow.min.js"></script>

    <!--====== Main js ======-->
    <script src="assets/js/main.js"></script>

    <!--====== Contact Form JS ======-->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("contact-form");

            form.addEventListener("submit", function (event) {
                event.preventDefault(); // Prevent the default form submission

                const formData = new FormData(form);

                fetch("assets/contact.php", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(error => {
                    alert("Enviado!");
                });
            });
        });
    </script>
</body>
</html>
