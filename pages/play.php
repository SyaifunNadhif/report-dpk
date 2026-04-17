<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Play YouTube - NGNL</title>
    <style>
        /* Menghilangkan scrollbar dan margin agar full layar */
        body, html { 
            margin: 0; 
            padding: 0; 
            height: 100%; 
            width: 100%;
            overflow: hidden; 
            background-color: #000; 
        }

        .video-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Overlay tipis jika ingin menambahkan teks atau tombol di atas video */
        .overlay {
            position: absolute;
            bottom: 20px;
            right: 20px;
            color: rgba(255, 255, 255, 0.5);
            font-family: sans-serif;
            font-size: 12px;
            pointer-events: none; /* Agar tidak menghalangi klik pada video */
        }
    </style>
</head>
<body>

    <?php
        // ID video dari link yang kamu berikan tadi
        $videoId = "FghoeusbkUk";

        /** * Parameter YouTube:
         * autoplay=1 : Putar otomatis
         * mute=1     : Wajib agar autoplay jalan di Chrome/Edge
         * loop=1     : Ulangi terus videonya
         * playlist   : Wajib ada ID video lagi agar loop berfungsi
         * controls=0 : Sembunyikan tombol kontrol (opsional)
         */
        $params = "?autoplay=1&mute=1&loop=1&playlist=" . $videoId . "&rel=0&controls=1";
        $embedUrl = "https://www.youtube.com/embed/" . $videoId . $params;
    ?>

    <div class="video-container">
        <iframe 
            src="<?php echo $embedUrl; ?>" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    </div>

    <div class="overlay">
        Playing: No Game No Life - This Game
    </div>

</body>
</html>