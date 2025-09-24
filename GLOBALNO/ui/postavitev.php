<?php
/*****************************************************************************************
 *     pot: /root/GLOBALNO/ui/postavitev.php v2.0                                      *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Glavni layout wrapper za celotno stran
 *   - Združuje vse UI komponente
 * Povezave:
 *   - Klican iz GLOBALNO/index.php
 *   - Uporablja vse UI funkcije
 * Pravila:
 *   - Samo združevanje komponent
 *   - Brez dodatne logike
 *****************************************************************************************/

// Nastavitve za glavo
$nastavitve_glave = [
    'naslov' => $Globalno['aplikacija'] . ($aktivni_modul ? ' - ' . ucfirst($aktivni_modul) : ''),
    'css' => [],
    'js' => []
];

// Ce je modul aktiven, dodaj njegove vire
if ($aktivni_modul) {
    $modul = NalagalnikModulov::pridobi_modul($aktivni_modul);
    if ($modul) {
        $modul_css = $modul['pot'] . 'sredstva/css/slog.css';
        $modul_js = $modul['pot'] . 'sredstva/js/skripta.js';
        
        if (file_exists($modul_css)) {
            $nastavitve_glave['css'][] = $modul_css;
        }
        
        if (file_exists($modul_js)) {
            $nastavitve_glave['js'][] = $modul_js;
        }
    }
}

// Prikaži glavo
ui_glava($nastavitve_glave);

// Prikaži navigacijo
ui_navigacija();

// Glavna vsebina
?>
<main class="glavna-vsebina">
    <?php
    // Prikaži obvestila, ce obstajajo
    ui_obvestila();
    
    // Prikaži vsebino modula ali privzeto vsebino
    if (isset($izhod_modula)) {
        echo $izhod_modula;
    } else if (!$aktivni_modul) {
        // Privzeta domaca stran
        ?>
        <div class="dobrodoslica">
            <h1>Dobrodosli v <?= $Globalno['aplikacija'] ?></h1>
            <p>Izberite modul iz navigacijskega menija za zacetek.</p>
            
            <?php if (!$Seja->preveri_prijavo()): ?>
            <div class="poziv-dejanje">
                <p>Še nimate racuna?</p>
                <a href="?modul=registracija" class="gumb-primarni">Registrirajte se</a>
                <a href="?modul=prijava" class="gumb-sekundarni">Prijava</a>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    ?>
</main>

<?php
// Prikaži nogo
ui_noga(['js' => $nastavitve_glave['js']]);

// KONEC DATOTEKE: postavitev.php
?>