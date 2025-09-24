<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/globalno.php v2.1 - POPRAVLJENA                              *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Glavni bootstrap loader sistema
 *   - Inicializira vse komponente in nastavitve
 *   - Centralna kontrolna toka aplikacije
 * Povezave:
 *   - Klican iz GLOBALNO/index.php
 *   - Nalaga vse sistemske komponente
 * Pravila:
 *   - Brez direktnih poti (vse preko $Globalno)
 *   - Samo inicializacijska logika
 *   - Definira konstantno ASTRA za preprečevanje neposrednega dostopa
 * 
 * POPRAVEK v2.1:
 *   - Popravljeno vse poimenovanje (brez šumnikov)
 *   - DIE zabelezi napako pred izpisom
 *   - Uporaba $GLOBALNO namesto $GLOBALS
 *   - Robusten error handling
 * Datum: 2024-01-15 16:10
 *****************************************************************************************/

// Prepreci neposreden dostop in zabelezi napako
if (!defined('ASTRA')) {
    if (function_exists('g_zabelezi_napako')) {
        g_zabelezi_napako("Neposreden dostop do " . __FILE__, __FILE__, __LINE__);
    }
    die('Neposreden dostop ni dovoljen');
}

// Error handling za fatalne napake
register_shutdown_function(function() {
    $zadnja_napaka = error_get_last();
    if ($zadnja_napaka && in_array($zadnja_napaka['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        g_zabelezi_napako("FATALNA NAPAKA: " . print_r($zadnja_napaka, true), __FILE__, __LINE__);
        
        if (headers_sent() === false) {
            http_response_code(500);
            echo "<h1>Napaka v sistemu</h1>";
            echo "<p>Prislo je do nepričakovane napake. Prosimo, poskusite znova.</p>";
        }
    }
});

// Error reporting za razvoj
if (file_exists(__DIR__ . '/sistem/.env')) {
    $env = parse_ini_file(__DIR__ . '/sistem/.env');
    if ($env['RAZVOJ'] ?? false) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
}

// Preveri PHP razsiritve
$zahtevane_razsiritve = ['pdo', 'pdo_mysql', 'session'];
foreach ($zahtevane_razsiritve as $razsiritev) {
    if (!extension_loaded($razsiritev)) {
        g_zabelezi_napako("Manjkajoca PHP razsiritev: $razsiritev", __FILE__, __LINE__);
        die("Manjkajoca PHP razsiritev: $razsiritev");
    }
}

// Nalozi sistemske spremenljivke
require_once __DIR__ . '/sistem/spremenljivke.php';

// Nalozi varne razrede
require_once __DIR__ . '/sistem/varni_razred.php';

// Uporabi $GLOBALNO namesto $GLOBALS
$Globalno = $GLOBALNO['Globalno'];
$poti = Nalagalnik::pridobi_poti();

// Nalozi jedrne komponente v pravilnem vrstnem redu
$jedro_datoteke = [
    'konfiguracije.php',
    'orodja.php', 
    'varnost.php',
    'baze.php',
    'nalagalnik_modulov.php'
];

foreach ($jedro_datoteke as $datoteka) {
    $polna_pot = $poti['sidro'] . '/' . $datoteka;
    if (file_exists($polna_pot)) {
        require_once $polna_pot;
        g_zabelezi_operacijo("Nalozena jedrna datoteka: $datoteka", __FILE__, __LINE__);
    } else {
        g_zabelezi_napako("Manjkajoca jedrna datoteka: $datoteka", __FILE__, __LINE__);
        die("Kriticna napaka: Manjkajoca sistemska datoteka");
    }
}

// Inicializiraj sejo
$Seja = new Seja();
$Seja->zacni_varno_sejo();

// Shrani v $GLOBALNO za globalni dostop
$GLOBALNO['Seja'] = $Seja;

// Inicializiraj globalne spremenljivke
$trenutni_uporabnik = null;
$aktivni_modul = $_GET['modul'] ?? null;
$tema = 'svetla';

// Preveri in nalozi prijavljenega uporabnika
if ($Seja->preveri_prijavo()) {
    try {
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("
            SELECT id, uporabnisko_ime, email, vloga, tema, aktiven 
            FROM uporabniki 
            WHERE id = ? AND aktiven = 1
        ");
        $poizvedba->execute([$Seja->uporabnik_id]);
        $trenutni_uporabnik = $poizvedba->fetch();
        
        if ($trenutni_uporabnik) {
            // Nastavi temo iz uporabniških nastavitev
            $tema = $trenutni_uporabnik['tema'] ?? $tema;
            
            // Posodobi cas zadnje prijave
            $povezava->prepare("UPDATE uporabniki SET zadnja_prijava = NOW() WHERE id = ?")
                    ->execute([$trenutni_uporabnik['id']]);
                    
            g_zabelezi_operacijo("Naložen uporabnik: {$trenutni_uporabnik['uporabnisko_ime']}", __FILE__, __LINE__);
        } else {
            // Uporabnik ne obstaja vec - odjavi
            $Seja->odjava();
            g_zabelezi_operacijo("Avtomatska odjava - uporabnik ne obstaja v bazi", __FILE__, __LINE__);
        }
    } catch (Exception $napaka) {
        g_zabelezi_napako("Napaka pri nalaganju uporabnika: " . $napaka->getMessage(), __FILE__, __LINE__);
    }
}

// Inicializiraj nalagalnik modulov
NalagalnikModulov::inicializiraj();

// Obdelaj zahtevo za modul
if ($aktivni_modul && NalagalnikModulov::modul_obstaja($aktivni_modul)) {
    try {
        $izhod_modula = NalagalnikModulov::izvedi_modul($aktivni_modul);
        g_zabelezi_operacijo("Izveden modul: $aktivni_modul", __FILE__, __LINE__);
    } catch (Exception $napaka) {
        g_zabelezi_napako("Napaka pri izvajanju modula $aktivni_modul: " . $napaka->getMessage(), __FILE__, __LINE__);
        http_response_code(404);
        include $poti['svet'] . '/ui/napaka_404.php';
        exit;
    }
}

// KONEC DATOTEKE: globalno.php