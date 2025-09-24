<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/jedro/orodja.php v2.0                                         *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Pomožne funkcije in utilityji sistema
 *   - Logiranje, sanitizacija, validacija, formatiranje
 *   - Vse funkcije z si_ predpono (sistemske)
 * Povezave:
 *   - Naložen iz SISTEM/globalno.php
 *   - Uporablja se v celotnem sistemu
 * Pravila:
 *   - Samo čiste utility funkcije
 *   - Brez UI logike ali poslovne logike
 *****************************************************************************************/

/**
 * Zabeleži napako v dnevnik
 * @param string $sporočilo Sporočilo za logiranje
 */
function si_zabeleži_napako($sporočilo) {
    global $Globalno;
    $mapa_dnevnika = $Globalno['zrak'] . '/dnevniki' . $Globalno['ločilo'];
    
    if (!file_exists($mapa_dnevnika)) {
        mkdir($mapa_dnevnika, 0755, true);
    }
    
    $datoteka = $mapa_dnevnika . 'napake_' . date('Y-m-d') . '.log';
    $čas = date('Y-m-d H:i:s');
    $sporočilo = "[$čas] $sporočilo" . PHP_EOL;
    
    error_log($sporočilo, 3, $datoteka);
}

/**
 * Zabeleži operacijo v dnevnik
 * @param string $sporočilo Sporočilo za logiranje
 */
function si_zabeleži_operacijo($sporočilo) {
    global $Globalno;
    $mapa_dnevnika = $Globalno['zrak'] . '/dnevniki' . $Globalno['ločilo'];
    
    if (!file_exists($mapa_dnevnika)) {
        mkdir($mapa_dnevnika, 0755, true);
    }
    
    $datoteka = $mapa_dnevnika . 'operacije_' . date('Y-m-d') . '.log';
    $čas = date('Y-m-d H:i:s');
    $uporabnik = $_SESSION['uporabnik_ime'] ?? 'anonymous';
    $sporočilo = "[$čas] [$uporabnik] $sporočilo" . PHP_EOL;
    
    error_log($sporočilo, 3, $datoteka);
}

/**
 * Debug funkcija (samo v razvojnem načinu)
 * @param mixed $spremenljivka Spremenljivka za prikaz
 */
function si_razhrošči($spremenljivka) {
    if (RAZVOJ) {
        echo '<pre>';
        var_dump($spremenljivka);
        echo '</pre>';
        die();
    }
}

/**
 * Preveri, ali je zahteva AJAX
 * @return bool True, če je zahteva AJAX
 */
function si_je_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Preusmeri na drugo stran
 * @param string $lokacija Lokacija za preusmeritev
 */
function si_preusmeri($lokacija) {
    header("Location: $lokacija");
    exit;
}

/**
 * Preberi JSON datoteko
 * @param string $pot Pot do JSON datoteke
 * @return array Asociativno polje z vsebino
 */
function si_preberi_json($pot) {
    if (!file_exists($pot)) {
        return [];
    }
    
    $vsebina = file_get_contents($pot);
    return json_decode($vsebina, true) ?? [];
}

/**
 * Shrani JSON datoteko
 * @param string $pot Pot do JSON datoteke
 * @param array $podatki Podatki za shranjevanje
 * @return bool True, če je shranjevanje uspelo
 */
function si_shrani_json($pot, $podatki) {
    global $Globalno;
    $mapa = dirname($pot);
    
    if (!file_exists($mapa)) {
        mkdir($mapa, 0755, true);
    }
    
    $json = json_encode($podatki, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($pot, $json) !== false;
}

/**
 * Generiraj naključni niz
 * @param int $dolžina Dolžina niza
 * @return string Naključni niz
 */
function si_generiraj_naključni_niz($dolžina = 16) {
    return bin2hex(random_bytes(ceil($dolžina / 2)));
}

/**
 * Sanitiziraj vhodne podatke
 * @param mixed $vhod Vhodni podatki
 * @return mixed Sanitizirani podatki
 */
function si_sanitiziraj_vhod($vhod) {
    if (is_array($vhod)) {
        return array_map('si_sanitiziraj_vhod', $vhod);
    }
    
    return htmlspecialchars(trim($vhod), ENT_QUOTES, 'UTF-8');
}

/**
 * Preveri veljavnost e-poštnega naslova
 * @param string $email E-poštni naslov za preverjanje
 * @return bool True, če je e-poštni naslov veljaven
 */
function si_preveri_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generiraj hash gesla
 * @param string $geslo Geslo za hashiranje
 * @return string Hashirano geslo
 */
function si_generiraj_hash_gesla($geslo) {
    return password_hash($geslo, ALGORITEM_GESLA, ['cost' => CENA_GESLA]);
}

/**
 * Preveri geslo
 * @param string $geslo Geslo za preverjanje
 * @param string $hash Hash za primerjavo
 * @return bool True, če geslo ustreza hashu
 */
function si_preveri_geslo($geslo, $hash) {
    return password_verify($geslo, $hash);
}

// KONEC DATOTEKE: orodja.php
?>