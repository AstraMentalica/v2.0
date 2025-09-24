<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/jedro/varnost.php v2.0                                        *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Razsirjen varnostni razred za upravljanje seje
 *   - CSRF zascita, rate limiting, avtorizacija
 *   - Vse metode v slovenscini (brez csz)
 * Povezave:
 *   - Nalozen iz SISTEM/globalno.php
 *   - Razsirjuje osnovni razred Seja iz varni_razred.php
 * Pravila:
 *   - Samo varnostna logika
 *   - Brez direktnega dela z bazo
 *****************************************************************************************/

/**
 * Razsirjen varnostni razred za dodatne funkcionalnosti
 */
class Varnost extends Seja {
    private $csrf_zetoni;
    
    /**
     * Constructor - inicializira dodatne varnostne lastnosti
     */
    public function __construct() {
        parent::__construct();
        $this->csrf_zetoni = [];
        $this->inicializiraj_csrf();
    }
    
    /**
     * Inicializira CSRF zascito
     */
    private function inicializiraj_csrf() {
        if (!isset($_SESSION['csrf_zetoni'])) {
            $_SESSION['csrf_zetoni'] = [];
        }
        $this->csrf_zetoni = &$_SESSION['csrf_zetoni'];
        
        // Ciscenje starih zetonov
        $trenutni_cas = time();
        foreach ($this->csrf_zetoni as $kljuc => $zeton) {
            if ($trenutni_cas - $zeton['cas'] > CAS_POTEKA_ZETONA) {
                unset($this->csrf_zetoni[$kljuc]);
            }
        }
    }
    
    /**
     * Generiraj nov CSRF zeton
     * @param string $ime Unikatno ime za zeton
     * @return string Generiran zeton
     */
    public function generiraj_csrf_zeton($ime = 'osnovni') {
        $zeton = bin2hex(random_bytes(DOLZINA_ZETONA_CSRF));
        $this->csrf_zetoni[$ime] = [
            'zeton' => $zeton,
            'cas' => time()
        ];
        return $zeton;
    }
    
    /**
     * Preveri veljavnost CSRF zetona
     * @param string $zeton Zeton za preverjanje
     * @param string $ime Ime zetona
     * @return bool True, ce je zeton veljaven
     */
    public function preveri_csrf_zeton($zeton, $ime = 'osnovni') {
        if (!isset($this->csrf_zetoni[$ime])) {
            return false;
        }
        
        $shranjeni_zeton = $this->csrf_zetoni[$ime];
        
        // Preveri cas veljavnosti
        if (time() - $shranjeni_zeton['cas'] > CAS_POTEKA_ZETONA) {
            unset($this->csrf_zetoni[$ime]);
            return false;
        }
        
        // Preveri ujemanje zetonov
        if (!hash_equals($shranjeni_zeton['zeton'], $zeton)) {
            return false;
        }
        
        // Obnovi cas za dodatno varnost
        $this->csrf_zetoni[$ime]['cas'] = time();
        return true;
    }
    
    /**
     * Preveri omejitev zahtevkov (rate limiting)
     * @param string $kljuc Unikatni kljuc za omejitev
     * @param int $omejitev Stevilo dovoljenih zahtevkov
     * @param int $casovno_obdobje Casovno obdobje v sekundah
     * @return bool True, ce je zahtevek dovoljen
     */
    public function preveri_omejitev_zahtevkov($kljuc, $omejitev = 5, $casovno_obdobje = 60) {
        if (!isset($_SESSION['omejitve_zahtevkov'])) {
            $_SESSION['omejitve_zahtevkov'] = [];
        }
        
        $trenutni_cas = time();
        $zaznamovani_cas = $_SESSION['omejitve_zahtevkov'][$kljuc]['cas'] ?? 0;
        $stevec = $_SESSION['omejitve_zahtevkov'][$kljuc]['stevec'] ?? 0;
        
        // Resetiraj, ce je obdobje poteklo
        if ($trenutni_cas - $zaznamovani_cas > $casovno_obdobje) {
            $_SESSION['omejitve_zahtevkov'][$kljuc] = [
                'cas' => $trenutni_cas,
                'stevec' => 1
            ];
            return true;
        }
        
        // Preveri omejitev
        if ($stevec < $omejitev) {
            $_SESSION['omejitve_zahtevkov'][$kljuc]['stevec']++;
            return true;
        }
        
        return false;
    }
    
    /**
     * Preveri dostop do modula glede na pravice
     * @param string $modul_ime Ime modula
     * @param array $modul_konfig Konfiguracija modula
     * @return bool True, ce je dostop dovoljen
     */
    public function preveri_dostop_do_modula($modul_ime, $modul_konfig) {
        $zahtevana_vloga = $modul_konfig['vloga'] ?? 'gost';
        
        // Pretvorba imena vloge v stevilsko raven
        $mapiranje_vlog = [
            'gost' => 0,
            'uporabnik' => 1,
            'moderator' => 3,
            'administrator' => 4,
            'superadmin' => 5
        ];
        
        $zahtevana_raven = $mapiranje_vlog[$zahtevana_vloga] ?? 0;
        
        return $this->preveri_pravice($zahtevana_raven);
    }
    
    /**
     * Validiraj in sanitiziraj uporabniski vnos
     * @param array $podatki Podatki za validacijo
     * @param array $pravila Pravila validacije
     * @return array [uspesno => bool, napake => array, cisti_podatki => array]
     */
    public function validiraj_podatke($podatki, $pravila) {
        $napake = [];
        $cisti_podatki = [];
        
        foreach ($pravila as $polje => $pravila_polja) {
            $vrednost = $podatki[$polje] ?? '';
            $cista_vrednost = si_sanitiziraj_vhod($vrednost);
            
            foreach ($pravila_polja as $pravilo) {
                switch ($pravilo) {
                    case 'obvezno':
                        if (empty(trim($cista_vrednost))) {
                            $napake[$polje][] = "Polje $polje je obvezno";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($cista_vrednost) && !si_preveri_email($cista_vrednost)) {
                            $napake[$polje][] = "Neveljaven e-poštni naslov";
                        }
                        break;
                        
                    case preg_match('/^min:(\d+)$/', $pravilo, $ujemanje) ? true : false:
                        $min_dolzina = (int)$ujemanje[1];
                        if (strlen($cista_vrednost) < $min_dolzina) {
                            $napake[$polje][] = "Minimalna dolžina je $min_dolzina znakov";
                        }
                        break;
                        
                    case preg_match('/^max:(\d+)$/', $pravilo, $ujemanje) ? true : false:
                        $max_dolzina = (int)$ujemanje[1];
                        if (strlen($cista_vrednost) > $max_dolzina) {
                            $napake[$polje][] = "Maksimalna dolžina je $max_dolzina znakov";
                        }
                        break;
                }
            }
            
            if (!isset($napake[$polje])) {
                $cisti_podatki[$polje] = $cista_vrednost;
            }
        }
        
        return [
            'uspesno' => empty($napake),
            'napake' => $napake,
            'cisti_podatki' => $cisti_podatki
        ];
    }
}

// KONEC DATOTEKE: varnost.php
?>