<?php
/*****************************************************************************************
/*****************************************************************************************
 *     pot: /root/SISTEM/sistem/spremenljivke.php v2.1                              *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Centralni array za vse poti, privilegije, module in predpone sistema
 *   - Definira osnovne strukture za celoten sistem
 *   - Vse poimenovano v slovenščini (BREZ ŠUMNIKOV v identifikatorjih)
 * Povezave:
 *   - Naložen iz SISTEM/globalno.php
 *   - Dostopen preko $GLOBALNO (alias na $GLOBALS)
 * Pravila:
 *   - Samo definicije, brez logike
 *   - Vse poti relativne z dirname(__DIR__, 2)
 *   - Enojni vir resnic za celoten sistem
 *****************************************************************************************/

/* POPRAVEK: standardizacija imen, odstranjeni sumniki, dodan alias $GLOBALNO
   Datum popravljka: 2025-09-24  (verzija: v2.0-p0) */

$Globalno = [
    // ==================== GLOBALNE POTI ====================
    'eter'    => dirname(__DIR__, 2) . '/DATOTEKE',      // Dodatne datoteke, helperji, codex
    'svet'    => dirname(__DIR__, 2) . '/GLOBALNO',     // Frontend, UI, layout komponente
    'sidro'   => __DIR__ . '/../jedro',                 // Jedro sistema (kritične funkcije)
    'voda'    => dirname(__DIR__, 2) . '/MODULI',       // Moduli in funkcionalnosti
    'zrak'    => dirname(__DIR__, 2) . '/UPORABNIKI',   // Uporabniki, seje, privilegiji

    // ==================== PRIVILEGIJI UPORABNIKOV ====================
    'S0' => 0,  // Gost (ni prijavljen)
    'S1' => 1,  // Navadni uporabnik
    'S2' => 2,  // VIP uporabnik
    'S3' => 3,  // Moderator
    'S4' => 4,  // Administrator
    'S5' => 5,  // Super administrator

    // ==================== MODULI / NIVOJI SISTEMA ====================
    'K0'   => 'osnovno',
    'K00'  => 'jedro',
    'K01'  => 'sistem',
    'K1'   => 'globalno',
    'K2'   => 'modul',
    'K3'   => 'modul_dodatki',

    // ==================== OSTALE SISTEMSKE SPREMENLJIVKE ====================
    'locilo'      => DIRECTORY_SEPARATOR,   // brez sumnika v imenu kljuca
    'aplikacija'  => 'ASTRAmentalica',
    'razhroscevanje' => true,               // brez sumnika: razhroscevanje namesto razhroščevanje
    'je_globalno' => true,

    // ==================== PREDPONE FUNKCIJ ====================
    'PRED_SI' => 'si_',   // Sistemske funkcije (helper/jedro)
    'PRED_G'  => 'g_',    // Globalne spremenljivke
    'PRED_UI' => 'ui_',   // UI/frontend funkcije
    'PRED_M'  => 'm_',    // Moduli in funkcionalnosti
];

// Alias za $GLOBALS (slovensko ime)
$GLOBALNO = &$GLOBALS;
$GLOBALNO['Globalno'] = $Globalno;
/* KONEC DOKUMENTA */
// Shrani v globalno spremenljivko za dostop iz vseh delov sistema
// DODAJ GLOBALNO FUNKCIJO ZA BELEŽENJE NAPAK
function g_zabelezi_napako($sporocilo, $datoteka = '', $vrstica = '') {
    global $Globalno;
    
    $mapa_dnevnika = $Globalno['zrak'] . '/dnevniki' . $Globalno['locilo'];
    
    if (!file_exists($mapa_dnevnika)) {
        mkdir($mapa_dnevnika, 0755, true);
    }
    
    $datoteka_log = $mapa_dnevnika . 'napake_' . date('Y-m-d') . '.log';
    $cas = date('Y-m-d H:i:s');
    
    $lokacija = '';
    if ($datoteka && $vrstica) {
        $lokacija = " [v datoteki: $datoteka, vrstica: $vrstica]";
    }
    
    $sporocilo = "[$cas]$lokacija $sporocilo" . PHP_EOL;
    error_log($sporocilo, 3, $datoteka_log);
}
function g_zabelezi_operacijo($sporocilo, $datoteka = '', $vrstica = '') {
    global $Globalno;
    
    $mapa_dnevnika = $Globalno['zrak'] . '/dnevniki' . $Globalno['locilo'];
    
    if (!file_exists($mapa_dnevnika)) {
        mkdir($mapa_dnevnika, 0755, true);
    }
    
    $datoteka_log = $mapa_dnevnika . 'operacije_' . date('Y-m-d') . '.log';
    $cas = date('Y-m-d H:i:s');
    $uporabnik = $_SESSION['uporabnik_ime'] ?? 'anonymous';
    
    $lokacija = '';
    if ($datoteka && $vrstica) {
        $lokacija = " [v datoteki: $datoteka, vrstica: $vrstica]";
    }
    
    $sporocilo = "[$cas] [$uporabnik]$lokacija $sporocilo" . PHP_EOL;
    
    error_log($sporocilo, 3, $datoteka_log);
}

// Makro za enostavno uporabo
define('SI_ZABELEZI_NAPAKO', 'g_zabelezi_napako');
define('SI_ZABELEZI_OPERACIJE', 'g_zabelezi_operacijo');

//$GLOBALS['Globalno'] = $Globalno;
// KONEC DATOTEKE: spremenljivke.php