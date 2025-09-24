<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/sistem/spremenljivke.php v2.1 - POPRAVLJENA                  *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Centralni array za vse poti, privilegije, module in predpone sistema
 *   - Definira osnovne strukture za celoten sistem
 *   - Vse poimenovano v slovenščini
 * Povezave:
 *   - Naložen iz SISTEM/globalno.php
 *   - Dostopen preko $GLOBALNO['Globalno']
 * Pravila:
 *   - Samo definicije, brez logike
 *   - Vse poti relativne z dirname(__DIR__, 2)
 *   - Enojni vir resnic za celoten sistem
 * 
 * POPRAVEK v2.1: 
 *   - Popravljeno vse poimenovanje (brez šumnikov)
 *   - Dodane globalne funkcije za beleženje napak
 *   - Standardizirano poimenovanje konstant
 * Datum: 2024-01-15 16:00
 *****************************************************************************************/

$Globalno = [
    // ==================== GLOBALNE POTI ====================
    'eter'    => dirname(__DIR__, 2) . '/DATOTEKE',
    'svet'    => dirname(__DIR__, 2) . '/GLOBALNO', 
    'sidro'   => __DIR__ . '/../jedro',
    'voda'    => dirname(__DIR__, 2) . '/MODULI',
    'zrak'    => dirname(__DIR__, 2) . '/UPORABNIKI',

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
    'locilo'      => DIRECTORY_SEPARATOR,
    'aplikacija'  => 'ASTRAmentalica',
    'razhroscevanje' => true,
    'je_globalno' => true,

    // ==================== PREDPONE FUNKCIJ ====================
    'PRED_SI' => 'si_',   // Sistemske funkcije (SISTEM/)
    'PRED_UI' => 'ui_',   // Uporabniski vmesnik (GLOBALNO/)
    'PRED_G'  => 'g_',    // Globalne funkcije (vec kot en direktorij)
    'PRED_M'  => 'm_',    // Modulske funkcije (MODULI/)
];

// Shrani v globalno spremenljivko za dostop iz vseh delov sistema
$GLOBALNO['Globalno'] = $Globalno;

// Kratek alias za lazji dostop
$G = $GLOBALNO['Globalno'];

/**
 * Globalna funkcija za belezenje napak z informacijami o izvoru
 * @param string $sporocilo Sporocilo napake
 * @param string $datoteka Ime datoteke (__FILE__)
 * @param int $vrstica Stevilka vrstice (__LINE__) 
 */
function g_zabelezi_napako($sporocilo, $datoteka = '', $vrstica = 0) {
    global $GLOBALNO;
    
    $izvor = '';
    if ($datoteka && $vrstica) {
        $ime_datoteke = basename($datoteka);
        $izvor = " [$ime_datoteke:$vrstica]";
    }
    
    $mapa_dnevnika = $GLOBALNO['Globalno']['zrak'] . '/dnevniki' . $GLOBALNO['Globalno']['locilo'];
    
    if (!file_exists($mapa_dnevnika)) {
        mkdir($mapa_dnevnika, 0755, true);
    }
    
    $datoteka_dnevnika = $mapa_dnevnika . 'napake_' . date('Y-m-d') . '.log';
    $cas = date('Y-m-d H:i:s');
    
    $vnos = "[$cas]$izvor $sporocilo" . PHP_EOL;
    error_log($vnos, 3, $datoteka_dnevnika);
}

/**
 * Globalna funkcija za belezenje operacij
 * @param string $sporocilo Sporocilo operacije  
 * @param string $datoteka Ime datoteke
 * @param int $vrstica Stevilka vrstice
 */
function g_zabelezi_operacijo($sporocilo, $datoteka = '', $vrstica = 0) {
    global $GLOBALNO;
    
    $izvor = '';
    if ($datoteka && $vrstica) {
        $ime_datoteke = basename($datoteka);
        $izvor = " [$ime_datoteke:$vrstica]";
    }
    
    $uporabnik = isset($GLOBALNO['Seja']) && $GLOBALNO['Seja']->uporabnik_ime ? 
                $GLOBALNO['Seja']->uporabnik_ime : 'anonymous';
    
    $mapa_dnevnika = $GLOBALNO['Globalno']['zrak'] . '/dnevniki' . $GLOBALNO['Globalno']['locilo'];
    
    if (!file_exists($mapa_dnevnika)) {
        mkdir($mapa_dnevnika, 0755, true);
    }
    
    $datoteka_dnevnika = $mapa_dnevnika . 'operacije_' . date('Y-m-d') . '.log';
    $cas = date('Y-m-d H:i:s');
                
    $vnos = "[$cas] [$uporabnik]$izvor $sporocilo" . PHP_EOL;
    error_log($vnos, 3, $datoteka_dnevnika);
}

// KONEC DATOTEKE: spremenljivke.php