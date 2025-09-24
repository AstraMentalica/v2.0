<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/jedro/konfiguracije.php v2.0                                  *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Branje in upravljanje konfiguracijskih nastavitev
 *   - Obdelava .env datoteke in definicija konstant
 *   - Varnostno preverjanje obveznih nastavitev
 * Povezave:
 *   - Naložen iz SISTEM/globalno.php
 *   - Uporablja .env datoteko iz SISTEM/sistem/
 * Pravila:
 *   - Vse konstante v slovenščini
 *   - Obvezno preverjanje manjkajočih nastavitev
 *****************************************************************************************/

/**
 * Prebere .env datoteko in vrne asociativno polje
 * @param string $pot Pot do .env datoteke
 * @return array Asociativno polje z nastavitvami
 * @throws Exception Če datoteka ne obstaja ali manjkajo obvezne nastavitve
 */
function si_preberi_env($pot) {
    if (!file_exists($pot)) {
        throw new Exception("Konfiguracijska datoteka .env ne obstaja: $pot");
    }
    
    $vrstice = file($pot, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $rezultat = [];
    $obvezne_nastavitve = ['GOSTITELJ_BAZE', 'IME_BAZE', 'UPORABNIK_BAZE', 'GESLO_BAZE'];
    $manjkajoce = [];
    
    foreach ($vrstice as $vrstica) {
        // Preskoči komentarje in prazne vrstice
        if (strpos(trim($vrstica), '#') === 0 || trim($vrstica) === '') {
            continue;
        }
        
        // Obdelaj vrstice z enačaji
        if (strpos($vrstica, '=') !== false) {
            list($ključ, $vrednost) = explode('=', $vrstica, 2);
            $ključ = trim($ključ);
            $vrednost = trim($vrednost);
            
            // Odstrani navednice, če obstajajo
            if (preg_match('/^"(.+)"$/', $vrednost, $ujemanje) || preg_match('/^\'(.+)\'$/', $vrednost, $ujemanje)) {
                $vrednost = $ujemanje[1];
            }
            
            $rezultat[$ključ] = $vrednost;
        }
    }
    
    // Preveri obvezne nastavitve
    foreach ($obvezne_nastavitve as $nastavitev) {
        if (!isset($rezultat[$nastavitev]) || empty($rezultat[$nastavitev])) {
            $manjkajoce[] = $nastavitev;
        }
    }
    
    if (!empty($manjkajoce)) {
        throw new Exception("Manjkajo obvezne nastavitve v .env: " . implode(', ', $manjkajoce));
    }
    
    return $rezultat;
}

// Preberi .env nastavitve
try {
    $env_podatki = si_preberi_env(dirname(__DIR__, 2) . '/sistem/.env');
    
    // ==================== BAZA PODATKOV ====================
    define('GOSTITELJ_BAZE', $env_podatki['GOSTITELJ_BAZE']);
    define('IME_BAZE', $env_podatki['IME_BAZE']);
    define('UPORABNIK_BAZE', $env_podatki['UPORABNIK_BAZE']);
    define('GESLO_BAZE', $env_podatki['GESLO_BAZE']);
    
    // ==================== OKOLJE ====================
    define('RAZVOJ', isset($env_podatki['RAZVOJ']) ? $env_podatki['RAZVOJ'] === 'true' : false);
    define('PRODUKCIJA', isset($env_podatki['PRODUKCIJA']) ? $env_podatki['PRODUKCIJA'] === 'true' : true);
    
    // ==================== ČASOVNE NASTAVITVE ====================
    define('ČAS_POTEKA_SEJE', isset($env_podatki['ČAS_POTEKA_SEJE']) ? (int)$env_podatki['ČAS_POTEKA_SEJE'] : 3600);
    define('ČAS_POTEKA_ŽETONA', isset($env_podatki['ČAS_POTEKA_ŽETONA']) ? (int)$env_podatki['ČAS_POTEKA_ŽETONA'] : 900);
    
    // ==================== VARNOSTNE NASTAVITVE ====================
    define('DOLŽINA_ŽETONA_CSRF', isset($env_podatki['DOLŽINA_ŽETONA_CSRF']) ? (int)$env_podatki['DOLŽINA_ŽETONA_CSRF'] : 32);
    define('ALGORITEM_GESLA', isset($env_podatki['ALGORITEM_GESLA']) ? $env_podatki['ALGORITEM_GESLA'] : PASSWORD_DEFAULT);
    define('CENA_GESLA', isset($env_podatki['CENA_GESLA']) ? (int)$env_podatki['CENA_GESLA'] : 12);
    
    // ==================== API KLJUČI ====================
    define('GOOGLE_API_KLJUČ', $env_podatki['GOOGLE_API_KLJUČ'] ?? '');
    define('DEEPSEEK_API_KLJUČ', $env_podatki['DEEPSEEK_API_KLJUČ'] ?? '');
    
    si_zabeleži_operacijo("Uspešno naložene konfiguracijske nastavitve");
    
} catch (Exception $napaka) {
    si_zabeleži_napako("Napaka pri branju konfiguracije: " . $napaka->getMessage());
    die("Kritična napaka v konfiguraciji: " . $napaka->getMessage());
}

// KONEC DATOTEKE: konfiguracije.php
?>