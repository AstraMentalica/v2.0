<?php
/*****************************************************************************************
 *     pot: /root/GLOBALNO/ui/napaka_404.php v2.0                                      *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Stran z napako 404 (Stran ne obstaja)
 * Povezave:
 *   - Klicana iz globalno.php
 * Pravila:
 *   - Samo HTML za prikaz napake
 *****************************************************************************************/

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ops (: 404 :) Stran ne obstaja? Sorry a jaz pa ne veljam?</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }
        
        .vsebina-napake {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 3rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }
        
        .koda-napake {
            font-size: 6rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .sporocilo-napake {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .gumb {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            transition: background 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .gumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="vsebina-napake">
        <div class="koda-napake">404</div>
        <div class="sporocilo-napake">Stran ne obstaja</div>
		<p>Ooops (: 404 Stran ne obstaja? Jaz samo nadomeščam :)...<p>
        <a href="<?= $GLOBALS['Globalno']['svet'] ?>" class="gumb">Te popeljem nazaj na domačo stran... Bye...</a>
    </div>
</body>
</html>

<!-- KONEC DATOTEKE: napaka_404.php -->