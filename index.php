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
        <a class="logotype" href="index.php"><img src="./assets/img/logotipo.jpeg" alt="logo"></a>

        <div class="search">
            <form id="search-bar" class="search-bar" action="search-results.php" method="GET">
                <input class="search-input" type="text" name="q" id="search-input-mobile" placeholder="buscar por produtos" required />
                <button class="submit-button" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
        <div class="affiliate-program">
            <img src="./assets/img/afiliados.jpeg" alt="afiliados">
        </div>
        <nav class="nav-links">
            <a href="./pages/login.php" class="login-btn">Área Administrativa</a>
        </nav>
    </header>
    <main class="container">
        <div class="highlights">
            <div class="slideshow-wrapper">
                <div class="slideshow-container">
                    <div class="slides">
                        <img src="assets/img/img1.jpeg" alt="imagem1">
                        <img src="assets/img/img2.jpeg" alt="imagem2">
                    </div>
                    <div class="indicadores">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                    </div>
                </div>

                <!-- botão em cima do slide -->
                <button class="btn-slide">Ver Mais</button>
            </div>

            <div class="highlight-cards">
                <div class="card-1">
                    <h3>Seu fone de ouvido esta aqui</h3>
                    <h4>Escolha agora o seu!</h4>
                    <img src="assets/img/fones.jpeg" alt="fones">
                    <button><a href="https://www.mercadolivre.com.br/social/promofocando1130/lists" target="_blank" rel="noopener noreferrer">Ver Mais</a></button>
                </div>
                <div class="card-2">
                    <h3>Dual Sense</h3>
                    <h4>Para Playstation 5</h4>
                    <img src="assets/img/controle.jpeg" alt="Controle playstation 5">
                    <button><a href="https://www.mercadolivre.com.br/social/promofocando1130?matt_word=promofocando&matt_tool=26300819&forceInApp=true&ref=BF88DvdnpXByfQA98FXyjhWeQET1vHXrWmAk4ez8mXxYc6padvm%2Bwh%2FKKhSBUG%2Fx2cuita2qRQ3TCKD93kf7SOFEHF%2BnceX1VCyCeysfww2kkKFXKyLKcv9D1boW2wvpzlmlGiZKRbpqEzQWfgARE%2FtTiluj5bZR2WCq8wSVK5omU64OFLK02%2BKSfBS4kyD6zyZEAYHIi5AbZHvQti0elU299%2B0k7WLtd8gnr3zAWuLAA5IICyZwFcEwEv%2B%2BnLc4W5h4BdcegindpXXxHBorxZCnCJlVDja6VVliTEWilpKhOO%2FJNKahDEVrTjLpF8WDpBdJ6bk7DJNoNx7a2F%2F7YvNgW4DTpwlXvTQh8QTxgu2Hrt38LHUiGb%2B2vWvMnEX8aOisVOx3JM0tcFlqHyJqLOG7ygcAbhNEXiaQm4H4Rs4hNG8KbJJRGeFLpNR944Y8TSJl89xcd5NytTLr4UKukET7f1oQKRe5fp3iyvveeHnEcKV%2F%2ByV5SQDV3onRRkAxOE7riJaCFeiFyus2G18GL%2FEhr7pn" target="_blank" rel="noopener noreferrer">Comprar</a></button>
                </div>
                <div class="card-3">
                    <h3>Cameras digitais</h3>
                    <h4>Confira produtos incriveis e aproveite as melhores ofertas!</h4>
                    <img src="assets/img/camera.jpeg" alt="Cameras">
                    <button><a href="https://www.mercadolivre.com.br/social/promofocando1130/lists" target="_blank" rel="noopener noreferrer">Comprar</a></button>
                </div>
            </div>
        </div>
    </main>
    <footer>

    </footer>
    <script src="https://kit.fontawesome.com/149b000a36.js" crossorigin="anonymous"></script>
    <script src="assets/scripts/script.js"></script>
</body>

</html>