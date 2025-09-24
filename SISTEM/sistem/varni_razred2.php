<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/sistem/varni_razred.php v2.0                                   *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Definicija varnih razredov za sistem
 *   - Nalagalnik za poti in Seja za upravljanje seje
 *   - Vse metode in lastnosti v slovenščini (BREZ ŠUMNIKOV v imenih)
 *****************************************************************************************/

/* POPRAVEK: odstranjeni sumniki v imenih metod, centralizacija seje
   Datum popravljka: 2025-09-24 */

class Nalagalnik {
    public static function pridobi_poti() {
        global $GLOBALNO;
        $G = $GLOBALNO['Globalno'];
        return [
            'eter'  => $G['eter'],
            'svet'  => $G['svet'],
            'sidro' => $G['sidro'],
            'voda'  => $G['voda'],
            'zrak'  => $G['zrak']
        ];
    }

    public static function pridobi_privilegije() {
        global $GLOBALNO;
        $G = $GLOBALNO['Globalno'];
        return [
            'S0' => $G['S0'],
            'S1' => $G['S1'],
            'S2' => $G['S2'],
            'S3' => $G['S3'],
            'S4' => $G['S4'],
            'S5' => $G['S5']
        ];
    }

    public static function pridobi_predpone() {
        global $GLOBALNO;
        $G = $GLOBALNO['Globalno'];
        return [
            'PRED_SI' => $G['PRED_SI'],
            'PRED_G'  => $G['PRED_G'],
            'PRED_UI' => $G['PRED_UI'],
            'PRED_M'  => $G['PRED_M']
        ];
    }

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

    public function __construct() {
        $this->uporabnik_prijavljen = false;
        $this->uporabnik_id = null;
        $this->uporabnik_ime = null;
        $this->uporabnik_vloga = 'gost';
        $this->zadnja_aktivnost = null;
    }

    // brez sumnika: zacni_varno_sejo
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

    private function sinhroniziraj_z_sejo() {
        if (isset($_SESSION['uporabnik_prijavljen'])) {
            $this->uporabnik_prijavljen = $_SESSION['uporabnik_prijavljen'];
            $this->uporabnik_id = $_SESSION['uporabnik_id'] ?? null;
            $this->uporabnik_ime = $_SESSION['uporabnik_ime'] ?? null;
            $this->uporabnik_vloga = $_SESSION['uporabnik_vloga'] ?? 'gost';
            $this->zadnja_aktivnost = $_SESSION['zadnja_aktivnost'] ?? null;
        }
    }

    private function shrani_v_sejo() {
        $_SESSION['uporabnik_prijavljen'] = $this->uporabnik_prijavljen;
        $_SESSION['uporabnik_id'] = $this->uporabnik_id;
        $_SESSION['uporabnik_ime'] = $this->uporabnik_ime;
        $_SESSION['uporabnik_vloga'] = $this->uporabnik_vloga;
        $_SESSION['zadnja_aktivnost'] = $this->zadnja_aktivnost;
    }

    private function posodobi_zadnjo_aktivnost() {
        $trenutni_cas = time();
        $this->zadnja_aktivnost = $trenutni_cas;
        $this->shrani_v_sejo();
    }

    public function prijava($uporabnik) {
        session_regenerate_id(true);

        $this->uporabnik_prijavljen = true;
        $this->uporabnik_id = $uporabnik['id'];
        $this->uporabnik_ime = $uporabnik['uporabnisko_ime'];
        $this->uporabnik_vloga = $uporabnik['vloga'];
        $this->posodobi_zadnjo_aktivnost();

        // Zabeleži prijavo
        if (function_exists('si_zabelezi_operacijo')) {
            si_zabelezi_operacijo("Prijava uporabnika: {$uporabnik['uporabnisko_ime']}");
        }
    }

    public function odjava() {
        $uporabnisko_ime = $this->uporabnik_ime ?? 'Neznan';

        if (function_exists('si_zabelezi_operacijo')) {
            si_zabelezi_operacijo("Odjava uporabnika: $uporabnisko_ime");
        }

        // Počisti sejo
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $parametri = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $parametri["path"], $parametri["domain"],
                $parametri["secure"], $parametri["httponly"]
            );
        }

        session_destroy();

        // Resetiraj lastnosti (ponovno konstruktor)
        $this->__construct();
    }

    public function preveri_prijavo() {
        if (!$this->uporabnik_prijavljen) {
            return false;
        }

        $this->posodobi_zadnjo_aktivnost();
        return true;
    }

    public function preveri_pravice($zahtevana_raven) {
        if (!$this->preveri_prijavo()) {
            return false;
        }

        global $GLOBALNO;
        $G = $GLOBALNO['Globalno'];

        // uporabnik_vloga lahko vsebuje string '1' ali 'S1' ali ime vloge - sprejemamo numeriko in string
        $trenutna_raven = $G['S' . $this->uporabnik_vloga] ?? $G['S0'];
        return $trenutna_raven >= $zahtevana_raven;
    }

    // pomocna metoda za centraliziran dostop - vrni vrednost iz $_SESSION ali null
    public function si_dobi_sesijo($kljuc) {
        return $_SESSION[$kljuc] ?? null;
    }
}

/* KONEC DOKUMENTA */
?>
