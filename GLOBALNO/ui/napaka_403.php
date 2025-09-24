<?php
/*****************************************************************************************
 *     pot: /root/GLOBALNO/ui/napaka_403.php v2.0                                      *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Stran z napako 403 (Dostop zavrnjen)
 * Povezave:
 *   - Klicana iz nalagalnika_modulov.php
 * Pravila:
 *   - Samo HTML za prikaz napake
 *****************************************************************************************/

http_response_code(403);
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Opaaa Hoooopa StoP</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        <div class="koda-napake">403</div>
        <div class="sporocilo-napake">Opaaa Hoooopa nepooblaščen</div>
        <p>Žal nisi na seznamu ustreznih pravic za dostop do te strani.</p>
        <a href="<?= $GLOBALS['Globalno']['svet'] ?>" class="gumb">Te popeljem nazaj na domačo stran... Bye...</a>
    </div>
</body>
</html>

<!-- KONEC DATOTEKE: napaka_403.php -->