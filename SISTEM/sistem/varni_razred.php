<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/sistem/varni_razred.php v2.1 - POPRAVLJENA                   *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Definicija varnih razredov za sistem
 *   - Nalagalnik za poti in Seja za upravljanje seje
 *   - Vse metode in lastnosti v slovenščini
 * Povezave:
 *   - Naložen iz SISTEM/globalno.php
 *   - Uporablja poti iz $Globalno arraya
 * Pravila:
 *   - Samo razredi, brez izvajanja logike
 *   - Dosledna uporaba slovenskih imen
 * 
 * POPRAVEK v2.1:
 *   - Popravljeno poimenovanje CSRF funkcionalnosti
 *   - Slovenska imena metod za varnostne zetone
 *   - Dosledno uporabljena slovenska terminologija
 * Datum: 2024-01-15 16:05
 *****************************************************************************************/

/**
 * Razred Nalagalnik - upravlja s potmi in sistemskimi spremenljivkami
 */
class Nalagalnik {
    /**
     * Pridobi globalne poti sistema
     * @return array Asociativno polje s potmi
     */
    public static function pridobi_poti() {
        global $GLOBALNO;
        return [
            'eter'  => $GLOBALNO['Globalno']['eter'],
            'svet'  => $GLOBALNO['Globalno']['svet'],
            'sidro' => $GLOBALNO['Globalno']['sidro'],
            'voda'  => $GLOBALNO['Globalno']['voda'],
            'zrak'  => $GLOBALNO['Globalno']['zrak']
        ];
    }
    
    /**
     * Pridobi privilegije sistema
     * @return array Asociativno polje s privilegijami
     */
    public static function pridobi_privilegije() {
        global $GLOBALNO;
        return [
            'S0' => $GLOBALNO['Globalno']['S0'],
            'S1' => $GLOBALNO['Globalno']['S1'],
            'S2' => $GLOBALNO['Globalno']['S2'],
            'S3' => $GLOBALNO['Globalno']['S3'],
            'S4' => $GLOBALNO['Globalno']['S4'],
            'S5' => $GLOBALNO['Globalno']['S5']
        ];
    }
    
    /**
     * Preveri, ali pot obstaja
     * @param string $tip Tip poti (eter, svet, sidro, voda, zrak)
     * @return bool True, ce pot obstaja
     */
    public static function preveri_pot($tip) {
        $poti = self::pridobi_poti();
        return isset($poti[$tip]) && file_exists($poti[$tip]);
    }
}

/**
 * Razred Seja - upravlja z uporabniškimi sejami in varnostjo
 */
class Seja {
    public $uporabnik_prijavljen;
    public $uporabnik_id;
    public $uporabnik_ime;
    public $uporabnik_vloga;
    public $zadnja_aktivnost;
    
    /**
     * Constructor - inicializira sejne lastnosti
     */
    public function __construct() {
        $this->uporabnik_prijavljen = false;
        $this->uporabnik_id = null;
        $this->uporabnik_ime = null;
        $this->uporabnik_vloga = 'gost';
        $this->zadnja_aktivnost = null;
    }
    
    /**
     * Zacne varno sejo z vsemi varnostnimi ukrepi
     */
    public function zacni_varno_sejo() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true
            ]);
        }
        
        $this->sinhroniziraj_z_sejo();
        $this->posodobi_zadnjo_aktivnost();
    }
    
    /**
     * Sinhronizira lastnosti objekta s sejo
     */
    private function sinhroniziraj_z_sejo() {
        if (isset($_SESSION['uporabnik_prijavljen'])) {
            $this->uporabnik_prijavljen = $_SESSION['uporabnik_prijavljen'];
            $this->uporabnik_id = $_SESSION['uporabnik_id'] ?? null;
            $this->uporabnik_ime = $_SESSION['uporabnik_ime'] ?? null;
            $this->uporabnik_vloga = $_SESSION['uporabnik_vloga'] ?? 'gost';
            $this->zadnja_aktivnost = $_SESSION['zadnja_aktivnost'] ?? null;
        }
    }
    
    /**
     * Shrani spremembe v sejo
     */
    private function shrani_v_sejo() {
        $_SESSION['uporabnik_prijavljen'] = $this->uporabnik_prijavljen;
        $_SESSION['uporabnik_id'] = $this->uporabnik_id;
        $_SESSION['uporabnik_ime'] = $this->uporabnik_ime;
        $_SESSION['uporabnik_vloga'] = $this->uporabnik_vloga;
        $_SESSION['zadnja_aktivnost'] = $this->zadnja_aktivnost;
    }
    
    /**
     * Posodobi cas zadnje aktivnosti
     */
    private function posodobi_zadnjo_aktivnost() {
        $trenutni_cas = time();
        $this->zadnja_aktivnost = $trenutni_cas;
        $this->shrani_v_sejo();
    }
    
    /**
     * Prijavi uporabnika v sistem
     * @param array $uporabnik Podatki uporabnika
     */
    public function prijava($uporabnik) {
        session_regenerate_id(true);
        
        $this->uporabnik_prijavljen = true;
        $this->uporabnik_id = $uporabnik['id'];
        $this->uporabnik_ime = $uporabnik['uporabnisko_ime'];
        $this->uporabnik_vloga = $uporabnik['vloga'];
        $this->posodobi_zadnjo_aktivnost();
        
        g_zabelezi_operacijo("Prijava uporabnika: {$uporabnik['uporabnisko_ime']}", __FILE__, __LINE__);
    }
    
    /**
     * Odjavi uporabnika iz sistema
     */
    public function odjava() {
        $uporabnisko_ime = $this->uporabnik_ime ?? 'Neznan';
        
        g_zabelezi_operacijo("Odjava uporabnika: $uporabnisko_ime", __FILE__, __LINE__);
        
        // Pocisti sejo
        $_SESSION = [];
        
        // Pocisti piskotek
        if (ini_get("session.use_cookies")) {
            $parametri = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $parametri["path"], $parametri["domain"],
                $parametri["secure"], $parametri["httponly"]
            );
        }
        
        session_destroy();
        
        // Resetiraj lastnosti
        $this->__construct();
    }
    
    /**
     * Preveri, ali je uporabnik prijavljen
     * @return bool True, ce je uporabnik prijavljen
     */
    public function preveri_prijavo() {
        if (!$this->uporabnik_prijavljen) {
            return false;
        }
        
        $this->posodobi_zadnjo_aktivnost();
        return true;
    }
    
    /**
     * Preveri pravice uporabnika
     * @param int $zahtevana_raven Zahtevana raven privilegijev
     * @return bool True, ce ima uporabnik zadostne pravice
     */
    public function preveri_pravice($zahtevana_raven) {
        if (!$this->preveri_prijavo()) {
            return false;
        }
        
        global $GLOBALNO;
        $trenutna_raven = $GLOBALNO['Globalno']['S' . $this->uporabnik_vloga] ?? $GLOBALNO['Globalno']['S0'];
        return $trenutna_raven >= $zahtevana_raven;
    }
    
    /**
     * Ustvari varnostni zeton (CSRF)
     * @param string $ime Unikatno ime za zeton
     * @return string Generiran zeton
     */
    public function ustvari_varnostni_zeton($ime = 'osnovni') {
        $zeton = bin2hex(random_bytes(32));
        $_SESSION['varnostni_zetoni'][$ime] = [
            'zeton' => $zeton,
            'cas' => time()
        ];
        return $zeton;
    }
    
    /**
     * Preveri veljavnost varnostnega zetona (CSRF)
     * @param string $zeton Zeton za preverjanje
     * @param string $ime Ime zetona
     * @return bool True, ce je zeton veljaven
     */
    public function preveri_varnostni_zeton($zeton, $ime = 'osnovni') {
        if (!isset($_SESSION['varnostni_zetoni'][$ime])) {
            return false;
        }
        
        $shranjeni_zeton = $_SESSION['varnostni_zetoni'][$ime];
        
        // Preveri cas veljavnosti (15 minut)
        if (time() - $shranjeni_zeton['cas'] > 900) {
            unset($_SESSION['varnostni_zetoni'][$ime]);
            return false;
        }
        
        // Preveri ujemanje zetonov
        if (!hash_equals($shranjeni_zeton['zeton'], $zeton)) {
            return false;
        }
        
        // Obnovi cas za dodatno varnost
        $_SESSION['varnostni_zetoni'][$ime]['cas'] = time();
        return true;
    }
}

// KONEC DATOTEKE: varni_razred.php