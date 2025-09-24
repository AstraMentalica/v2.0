<?php
/*****************************************************************************************
 *     pot: /root/GLOBALNO/funkcije_ui.php v2.0                                        *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Pomožne funkcije za uporabniški vmesnik
 *   - Vse funkcije z ui_ predpono
 * Povezave:
 *   - Uporabljajo se v UI komponentah
 * Pravila:
 *   - Samo UI funkcije (prikaz, formatiranje)
 *   - Brez poslovne logike
 *****************************************************************************************/

/**
 * Prikaži HTML glavo strani
 * @param array $nastavitve Nastavitve za glavo
 */
function ui_glava($nastavitve = []) {
    global $Seja, $tema, $Globalno;
    
    $nastavitve = array_merge([
        'naslov' => $Globalno['aplikacija'],
        'opis' => '',
        'kljucne_besede' => '',
        'avtor' => '',
        'css' => [],
        'js' => [],
        'kanonicni' => ''
    ], $nastavitve);
    
    $css_teme = ui_pridobi_css_teme($tema);
    $csrf_zeton = $Seja->generiraj_csrf_zeton();
    ?>
    <!DOCTYPE html>
    <html lang="sl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($nastavitve['naslov']) ?></title>
        
        <?php if (!empty($nastavitve['opis'])): ?>
        <meta name="description" content="<?= htmlspecialchars($nastavitve['opis']) ?>">
        <?php endif; ?>
        
        <?php if (!empty($nastavitve['kljucne_besede'])): ?>
        <meta name="keywords" content="<?= htmlspecialchars($nastavitve['kljucne_besede']) ?>">
        <?php endif; ?>
        
        <?php if (!empty($nastavitve['avtor'])): ?>
        <meta name="author" content="<?= htmlspecialchars($nastavitve['avtor']) ?>">
        <?php endif; ?>
        
        <?php if (!empty($nastavitve['kanonicni'])): ?>
        <link rel="canonical" href="<?= htmlspecialchars($nastavitve['kanonicni']) ?>">
        <?php endif; ?>
        
        <!-- Globalni CSS -->
        <link rel="stylesheet" href="<?= $Globalno['svet'] ?>/sredstva/css/globalni.css">
        
        <!-- Tematski CSS -->
        <link rel="stylesheet" href="<?= $css_teme ?>">
        
        <!-- Dodatni CSS -->
        <?php foreach ($nastavitve['css'] as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
        
        <!-- CSRF zeton za AJAX zahteve -->
        <meta name="csrf-token" content="<?= $csrf_zeton ?>">
    </head>
    <body class="tema-<?= $tema ?>">
    <?php
}

/**
 * Pridobi CSS datoteko za temo
 * @param string $tema Ime teme
 * @return string Pot do CSS datoteke
 */
function ui_pridobi_css_teme($tema) {
    global $Globalno;
    $pot_teme = $Globalno['svet'] . '/sredstva/css/' . $tema . '.css';
    
    if (file_exists($pot_teme)) {
        return $pot_teme;
    }
    
    // Privzeta tema
    return $Globalno['svet'] . '/sredstva/css/svetla.css';
}

/**
 * Prikaži navigacijski meni
 */
function ui_navigacija() {
    global $Seja, $aktivni_modul, $Globalno;
    
    $moduli = NalagalnikModulov::pridobi_vse_module();
    $je_prijavljen = $Seja->preveri_prijavo();
    ?>
    <nav class="glavna-navigacija">
        <div class="navigacija-zgornji-del">
            <a href="<?= $Globalno['svet'] ?>" class="logotip">
                <span><?= $Globalno['aplikacija'] ?></span>
            </a>
            
            <button class="gumb-mobilni-menui" aria-label="Preklopi navigacijski meni">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        
        <div class="navigacija-spodnji-del">
            <?php if ($je_prijavljen): ?>
            <ul class="seznam-navigacije">
                <?php foreach ($moduli as $ime => $modul): ?>
                    <?php if ($Seja->preveri_dostop_do_modula($ime, $modul)): ?>
                    <li class="element-navigacije <?= $aktivni_modul === $ime ? 'aktiven' : '' ?>">
                        <a href="?modul=<?= $ime ?>"><?= htmlspecialchars($modul['ime']) ?></a>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            
            <ul class="navigacija-uporabnik">
                <li class="element-navigacije">
                    <a href="?modul=profil" class="uporabnisko-ime">
                        <?= htmlspecialchars($Seja->uporabnik_ime) ?>
                    </a>
                    <ul class="podmeni">
                        <li><a href="?modul=profil">Moj profil</a></li>
                        <li><a href="?modul=nastavitve">Nastavitve</a></li>
                        <li><hr></li>
                        <li><a href="?modul=odjava">Odjava</a></li>
                    </ul>
                </li>
            </ul>
            <?php else: ?>
            <ul class="seznam-navigacije">
                <li class="element-navigacije">
                    <a href="?modul=prijava">Prijava</a>
                </li>
                <li class="element-navigacije">
                    <a href="?modul=registracija">Registracija</a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </nav>
    <?php
}

/**
 * Prikaži HTML nogo strani
 * @param array $nastavitve Nastavitve za nogo
 */
function ui_noga($nastavitve = []) {
    global $Globalno;
    $nastavitve = array_merge([
        'js' => []
    ], $nastavitve);
    ?>
        </main>
        
        <footer class="globalna-noga">
            <div class="vsebina-noge">
                <p>&copy; <?= date('Y') ?> <?= $Globalno['aplikacija'] ?>. Vse pravice pridržane.</p>
                
                <?php if (RAZVOJ): ?>
                <div class="razvojna-porocila">
                    <small>
                        Čas generiranja: <?= round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000) ?>ms |
                        Spomin: <?= round(memory_get_peak_usage() / 1024 / 1024, 2) ?>MB
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </footer>
        
        <!-- Globalni JavaScript -->
        <script src="<?= $Globalno['svet'] ?>/sredstva/js/globalni.js"></script>
        
        <!-- Dodatni JavaScript -->
        <?php foreach ($nastavitve['js'] as $js): ?>
        <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    </body>
    </html>
    <?php
}

/**
 * Prikaži sistemska obvestila
 */
function ui_obvestila() {
    if (!empty($_SESSION['obvestila'])) {
        foreach ($_SESSION['obvestila'] as $obvestilo) {
            $tip = $obvestilo['tip'] ?? 'info';
            $sporocilo = $obvestilo['sporocilo'] ?? '';
            $avtomatsko_skrij = $obvestilo['avtomatsko'] ?? true;
            
            echo "<div class='obvestilo obvestilo-{$tip}' data-avtomatsko='{$avtomatsko_skrij}'>";
            echo "<span>{$sporocilo}</span>";
            echo "<button class='gumb-zapri-obvestilo' aria-label='Zapri'>&times;</button>";
            echo "</div>";
        }
        
        // Po prikazu pocisti obvestila
        unset($_SESSION['obvestila']);
    }
}

/**
 * Dodaj obvestilo za prikaz
 * @param string $sporocilo Besedilo obvestila
 * @param string $tip Tip obvestila (uspeh, napaka, opozorilo, info)
 * @param bool $avtomatsko Ali se obvestilo samodejno skrije
 */
function ui_dodaj_obvestilo($sporocilo, $tip = 'info', $avtomatsko = true) {
    if (!isset($_SESSION['obvestila'])) {
        $_SESSION['obvestila'] = [];
    }
    
    $_SESSION['obvestila'][] = [
        'sporocilo' => $sporocilo,
        'tip' => $tip,
        'avtomatsko' => $avtomatsko
    ];
}

/**
 * Formatiraj datum v slovenski obliki
 * @param string $datum Datum za formatiranje
 * @param string $format Zeleni format (kratek, dolg, datum_cas)
 * @return string Formatiran datum
 */
function ui_formatiraj_datum($datum, $format = 'dolg') {
    if (!$datum || $datum === '0000-00-00 00:00:00') {
        return '/';
    }
    
    $casovni_zig = strtotime($datum);
    
    switch ($format) {
        case 'kratek':
            return date('d. m. Y', $casovni_zig);
        case 'dolg':
            return date('d. m. Y H:i', $casovni_zig);
        case 'datum_cas':
            return date('d. m. Y \ob H:i', $casovni_zig);
        case 'leto':
            return date('Y', $casovni_zig);
        default:
            return date('d. m. Y', $casovni_zig);
    }
}

/**
 * Skrajšaj besedilo na doloceno dolžino
 * @param string $besedilo Besedilo za skrajsanje
 * @param int $dolzina Maksimalna dolžina
 * @return string Skrajsano besedilo
 */
function ui_skrajsaj_besedilo($besedilo, $dolzina = 100) {
    if (mb_strlen($besedilo) <= $dolzina) {
        return $besedilo;
    }
    
    return mb_substr($besedilo, 0, $dolzina) . '...';
}

/**
 * Generiraj paginacijo za sezname
 * @param int $stevilo_strani Skupno stevilo strani
 * @param int $trenutna_stran Trenutna stran
 * @param string $vzorec_url Vzorec za URL (z %d za stevilko strani)
 * @return string HTML paginacija
 */
function ui_paginacija($stevilo_strani, $trenutna_stran, $vzorec_url) {
    if ($stevilo_strani <= 1) return '';
    
    $html = '<nav class="paginacija"><ul>';
    
    // Prejsnja stran
    if ($trenutna_stran > 1) {
        $html .= '<li><a href="' . sprintf($vzorec_url, $trenutna_stran - 1) . '">&laquo; Prejsnja</a></li>';
    }
    
    // Strani
    $zacetek = max(1, $trenutna_stran - 2);
    $konec = min($stevilo_strani, $zacetek + 4);
    
    for ($i = $zacetek; $i <= $konec; $i++) {
        $aktiven = $i == $trenutna_stran ? ' class="aktiven"' : '';
        $html .= '<li' . $aktiven . '><a href="' . sprintf($vzorec_url, $i) . '">' . $i . '</a></li>';
    }
    
    // Naslednja stran
    if ($trenutna_stran < $stevilo_strani) {
        $html .= '<li><a href="' . sprintf($vzorec_url, $trenutna_stran + 1) . '">Naslednja &raquo;</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Prikaži ikono
 * @param string $ime Ime ikone
 * @param string $razred Dodatni CSS razred
 * @return string HTML ikone
 */
function ui_ikona($ime, $razred = '') {
    $ikone = [
        'domov' => '🏠',
        'uporabnik' => '👤',
        'nastavitve' => '⚙️',
        'odjava' => '🚪',
        'pisanje' => '✏️',
        'brisanje' => '🗑️',
        'pogled' => '👁️',
        'iskanje' => '🔍',
        'plus' => '➕',
        'minus' => '➖'
    ];
    
    $ikona = $ikone[$ime] ?? '🔹';
    return "<span class='ikona {$razred}' aria-hidden='true'>{$ikona}</span>";
}

// KONEC DATOTEKE: funkcije_ui.php
?>