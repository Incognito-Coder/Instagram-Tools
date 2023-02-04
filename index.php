<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" href="icon/instagram_128.png">
    <meta name="theme-color" content="black" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="InstaTaker">
    <meta name="msapplication-TileImage" content="icon/instagram_128.png">
    <meta name="msapplication-TileColor" content="#000">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsDown - Instagram Downloader</title>
    <link rel="shortcut icon" type="image/x-icon" href="https://www.instagram.com/static/images/ico/favicon.ico/36b3ee2d91ed.ico">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="css/styles.css" type="text/css" />
</head>

<body class="p-3 mb-2 gradient-custom text-white" style="font-family: Vazir;">
    <div class="container-fluid">
        <div class="text-center h1" style="font-family: Instagram;">Instagram Downloader</div><br>
        <div class="input-group shadow">
            <input type="text" class="form-control" id="url-field" placeholder="Instagram Post Link" aria-label="Instagram Post Link" aria-describedby="button-search">
            <button class="btn btn-primary" type="button" id="button-search" onclick="Run()">Search</button>
        </div>
        <br>
        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel" hidden>

            <div class="carousel-inner" id="carousel-child">
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <img id="thumb" class="w-100 shadow bg-light text-dark p-2 mb-2 rounded" hidden />
        <p id="caption" class="shadow bg-light text-dark p-2 rounded" hidden></p>
        <div id="files" class="shadow bg-light text-dark p-2 rounded" hidden>Files:
        </div>
    </div>
    <footer class="text-center text-white">
        <!-- Copyright -->
        <div class="text-center m-3 p-2" style="background-color: rgba(0, 0, 0, 0.2);">
            © 2021-2023 Copyright : <a class="text-white" href="https://mr-alireza.ir/">Alireza Ahmand</a> | Designed with <i class="fas fa-heart" style="color:red"></i> by
            Alireza
        </div>
        <!-- Copyright -->
    </footer>
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.6.0.min.js"></script>
    <script src="sw.js"></script>
    <script src="js/script.js"></script>
    <script>
        $("#button-search").click(function() {
            $("#url-field").val('');

            $("#thumb").hide('3500');
            $("#caption").hide('3500');
            $("#files").hide('3500');


            $("#caption").html('');
            $("#files").html('');

            $("#thumb").show('4500');
            $("#caption").show('4500');
            $("#files").show('4500');

        });
        navigator.clipboard.readText()

            // (A2) PUT CLIPBOARD INTO TEXT FIELD
            .then(txt => {
                document.getElementById("url-field").value = txt;
            })

            // (A3) OPTIONAL - CANNOT ACCESS CLIPBOARD
            .catch(err => {
                alert("Please allow clipboard access permission");
            });

        if (!navigator.serviceWorker.controller) {
            navigator.serviceWorker.register("sw.js").then(function(reg) {
                console.log("Service worker has been registered for scope: " + reg.scope);
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>

</html>