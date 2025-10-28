<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css">
    <title>Promofocando</title>
</head>

<body>
    <header>
        <a class="logotype" href="index.php"><img src="./assets/img/Image.png" alt="logo"></a>

        <div class="search">
            <form id="search-bar" class="search-bar" action="search-results.php" method="GET">
                <input class="search-placeholder" type="text" name="q" id="search-input-mobile" placeholder="buscar por produtos" required />
                <button class="submit-button" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
        <div class="affiliate-program">
            <h3>Programa de Afiliados</h3>
        </div>
    </header>
    <main class="container">
        <button class="main-content-button" onclick="HomeFunction()" id="myBtn" title="Home">Início</button>
        <div class="highlights">
            <!-- container onde ficará o carrossel com os destaques e os links direto de imagem -->
            <div class="slideshow-container">

                <!-- Full-width images with number and caption text -->
                <div class="mySlides fade">
                    <div class="numbertext">1 / 3</div>
                    <img src="img1.jpg" style="width:100%">
                    <div class="text">imagem1</div>
                </div>

                <div class="mySlides fade">
                    <div class="numbertext">2 / 3</div>
                    <img src="img2.jpg" style="width:100%">
                    <div class="text">imagem2</div>
                </div>

                <div class="mySlides fade">
                    <div class="numbertext">3 / 3</div>
                    <img src="img3.jpg" style="width:100%">
                    <div class="text">imagem3</div>
                </div>

                <!-- Next and previous buttons -->
                <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                <a class="next" onclick="plusSlides(1)">&#10095;</a>
            </div>
            <br>

            <!-- The dots/circles -->
            <div style="text-align:center">
                <span class="dot" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
                <span class="dot" onclick="currentSlide(3)"></span>
            </div>
        </div>
    </main>
    <footer>

    </footer>
    <script src="https://kit.fontawesome.com/149b000a36.js" crossorigin="anonymous"></script>
</body>

</html>