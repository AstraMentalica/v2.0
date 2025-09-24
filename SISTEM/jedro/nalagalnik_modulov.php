<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/jedro/nalagalnik_modulov.php v2.0                             *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Nalaganje in upravljanje modulov sistema
 *   - Avtomatsko odkrivanje in registracija modulov
 *   - Preverjanje pravic za dostop do modulov
 * Povezave:
 *   - Nalozen iz SISTEM/globalno.php
 *   - Uporablja $Globalno array za poti
 * Pravila:
 *   - Samo nalaganje modulov
 *   - Lazy loading funkcionalnosti
 *   - Avtomatsko preverjanje pravic
 *****************************************************************************************/

class NalagalnikModulov {
    private static $moduli = [];
    private static $inicializiran = false;
    
    /**
     * Inicializira nalagalnik modulov
     */
    public static function inicializiraj() {
        if (self::$inicializiran) {
            return;
        }
        
        self::preveri_mapo_modulov();
        self::nalozi_module();
        self::$inicializiran = true;
        
        si_zabelezi_operacijo("Nalagalnik modulov uspesno inicializiran");
    }
    
    /**
     * Preveri in ustvari mapo modulov, ce ne obstaja
     */
    private static function preveri_mapo_modulov() {
        global $Globalno;
        
        if (!file_exists($Globalno['voda'])) {
            mkdir($Globalno['voda'], 0755, true);
            si_zabelezi_operacijo("Ustvarjena mapa modulov: " . $Globalno['voda']);
        }
    }
    
    /**
     * Nalozi vse module iz mape modulov
     */
    private static function nalozi_module() {
        global $Globalno;
        $mape = glob($Globalno['voda'] . $Globalno['locilo'] . '*', GLOB_ONLYDIR);
        
        foreach ($mape as $mapa) {
            $ime_modula = basename($mapa);
            
            // Preskoci sistemske mape (z _datoteke)
            if (strpos($ime_modula, '_datoteke') !== false) {
                continue;
            }
            
            // Preveri konfiguracijo modula
            $konfiguracija = self::preberi_konfiguracijo_modula($ime_modula);
            
            if ($konfiguracija && ($konfiguracija['omogocen'] ?? true)) {
                self::$moduli[$ime_modula] = $konfiguracija;
                self::registriraj_modul($ime_modula, $konfiguracija);
            }
        }
        
        si_zabelezi_operacijo("Nalozeni moduli: " . implode(', ', array_keys(self::$moduli)));
    }
    
    /**
     * Preberi konfiguracijo modula
     * @param string $ime_modula Ime modula
     * @return array|null Konfiguracija modula ali null
     */
    private static function preberi_konfiguracijo_modula($ime_modula) {
        global $Globalno;
        $pot_konfiguracije = $Globalno['voda'] . $Globalno['locilo'] . $ime_modula . $Globalno['locilo'] . 'konfiguracija.php';
        
        if (!file_exists($pot_konfiguracije)) {
            si_zabelezi_napako("Modul $ime_modula nima konfiguracijske datoteke");
            return null;
        }
        
        $konfiguracija = include $pot_konfiguracije;
        
        // Zagotovi osnovne nastavitve
        return array_merge([
            'ime' => $ime_modula,
            'opis' => '',
            'verzija' => '1.0.0',
            'avtor' => '',
            'omogocen' => true,
            'vloga' => 'uporabnik',
            'pot' => $Globalno['voda'] . $Globalno['locilo'] . $ime_modula . $Globalno['locilo']
        ], $konfiguracija);
    }
    
    /**
     * Registriraj modul v sistemu
     * @param string $ime_modula Ime modula
     * @param array $konfiguracija Konfiguracija modula
     */
    private static function registriraj_modul($ime_modula, $konfiguracija) {
        // Dodaj pot do modula v globalno konfiguracijo
        define('MODUL_' . strtoupper($ime_modula) . '_POT', $konfiguracija['pot']);
        
        // Vkljuci funkcije modula, ce obstajajo
        $pot_funkcij = $konfiguracija['pot'] . 'funkcije.php';
        if (file_exists($pot_funkcij)) {
            include_once $pot_funkcij;
            si_zabelezi_operacijo("Naložene funkcije modula: $ime_modula");
        }
    }
    
    /**
     * Pridobi modul po imenu
     * @param string $ime_modula Ime modula
     * @return array|null Konfiguracija modula ali null
     */
    public static function pridobi_modul($ime_modula) {
        return self::$moduli[$ime_modula] ?? null;
    }
    
    /**
     * Pridobi vse module
     * @return array Seznam vseh modulov
     */
    public static function pridobi_vse_module() {
        return self::$moduli;
    }
    
    /**
     * Preveri, ali modul obstaja in je omogocen
     * @param string $ime_modula Ime modula
     * @return bool True, ce modul obstaja in je omogocen
     */
    public static function modul_obstaja($ime_modula) {
        return isset(self::$moduli[$ime_modula]);
    }
    
    /**
     * Izvedi modul
     * @param string $ime_modula Ime modula
     * @return string Izhod modula
     * @throws Exception Ce modul ne obstaja ali ni omogocen
     */
    public static function izvedi_modul($ime_modula) {
        global $Seja;
        
        $modul = self::pridobi_modul($ime_modula);
        
        if (!$modul) {
            throw new Exception("Modul $ime_modula ne obstaja ali ni omogocen");
        }
        
        // Preveri pravice za dostop do modula
        if (!$Seja->preveri_dostop_do_modula($ime_modula, $modul)) {
            http_response_code(403);
            global $Globalno;
            include $Globalno['svet'] . '/ui/napaka_403.php';
            exit;
        }
        
        // Vkljuci glavno datoteko modula
        $glavna_datoteka = $modul['pot'] . 'index.php';
        if (file_exists($glavna_datoteka)) {
            ob_start();
            include $glavna_datoteka;
            $izhod = ob_get_clean();
            return $izhod;
        }
        
        throw new Exception("Modul $ime_modula nima glavne datoteke");
    }
    
    /**
     * Omogoci/onemogoci modul
     * @param string $ime_modula Ime modula
     * @param bool $omogocen Ali je modul omogocen
     */
    public static function nastavi_stanje_modula($ime_modula, $omogocen) {
        if (isset(self::$moduli[$ime_modula])) {
            self::$moduli[$ime_modula]['omogocen'] = $omogocen;
            si_zabelezi_operacijo("Modul $ime_modula " . ($omogocen ? "omogocen" : "onemogocen"));
        }
    }
}

// KONEC DATOTEKE: nalagalnik_modulov.php
?>