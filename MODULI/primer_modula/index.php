<?php
/*****************************************************************************************
 *     pot: /root/MODULI/primer_modula/index.php v2.0                                  *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Glavna vsebina primer modula
 *   - Demonstracija delovanja modula
 * Povezave:
 *   - Klican iz nalagalnika modulov
 * Pravila:
 *   - Samo prikaz in osnovna obdelava obrazcev
 *****************************************************************************************/

// Preveri, ali je modul klican v globalnem kontekstu
if (!defined('ASTRA')) {
    header('HTTP/1.0 403 Dostop zavrnjen');
    echo "Dostop dovoljen samo preko glavnega sistema.";
    exit;
}

// Obdelaj obrazce
$napake = [];
$uspešno = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_element'])) {
    global $Seja;
    
    // Preveri CSRF žeton
    if (!$Seja->preveri_csrf_zeton($_POST['csrf_zeton'] ?? '')) {
        $napake[] = 'Neveljaven varnostni žeton';
    } else {
        $naziv = $_POST['naziv'] ?? '';
        $vsebina = $_POST['vsebina'] ?? '';
        
        if (empty($naziv)) {
            $napake[] = 'Naziv je obvezen';
        }
        
        if (empty($napake)) {
            $uspeh = m_dodaj_element($naziv, $vsebina);
            if ($uspeh) {
                $uspešno = true;
                ui_dodaj_obvestilo('Element uspešno dodan', 'uspeh');
            } else {
                $napake[] = 'Napaka pri dodajanju elementa';
            }
        }
    }
}

// Pridobi elemente za prikaz
$stevilo_elementov = m_prestej_elemente();
$strani = ceil($stevilo_elementov / 10);
$trenutna_stran = max(1, min($strani, intval($_GET['stran'] ?? 1)));
$zamik = ($trenutna_stran - 1) * 10;

$elementi = m_pridobi_elemente($zamik, 10);

// Vrnemo vsebino
ob_start();
?>
<div class="vsebina-modula">
    <h1>Primer Modula</h1>
    <p class="opis-modula">Demonstracija delovanja modula</p>
    
    <?php if (!empty($napake)): ?>
        <div class="napake">
            <?php foreach ($napake as $napaka): ?>
                <div class="obvestilo obvestilo-napaka"><?= $napaka ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($uspešno): ?>
        <div class="obvestilo obvestilo-uspeh">Element uspešno dodan!</div>
    <?php endif; ?>
    
    <div class="vsebina-modula-poln">
        <section class="dodaj-element">
            <h2>Dodaj nov element</h2>
            <form method="post">
                <input type="hidden" name="csrf_zeton" value="<?= $Seja->generiraj_csrf_zeton() ?>">
                
                <div class="skupina-vnosa">
                    <label for="naziv">Naziv:</label>
                    <input type="text" id="naziv" name="naziv" required>
                </div>
                
                <div class="skupina-vnosa">
                    <label for="vsebina">Vsebina:</label>
                    <textarea id="vsebina" name="vsebina" rows="4"></textarea>
                </div>
                
                <button type="submit" name="dodaj_element" class="gumb">Dodaj element</button>
            </form>
        </section>
        
        <section class="seznam-elementov">
            <h2>Seznam elementov (<?= $stevilo_elementov ?> skupaj)</h2>
            
            <?php if (empty($elementi)): ?>
                <p>Ni elementov za prikaz.</p>
            <?php else: ?>
                <div class="seznam-elementi">
                    <?php foreach ($elementi as $element): ?>
                        <div class="element">
                            <h3><?= htmlspecialchars($element['naziv']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($element['vsebina'])) ?></p>
                            <div class="meta-podatki">
                                Avtor: <?= htmlspecialchars($element['uporabnisko_ime']) ?> |
                                Datum: <?= ui_formatiraj_datum($element['datum_ustvarjen']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($strani > 1): ?>
                    <?= ui_paginacija($strani, $trenutna_stran, '?modul=primer_modula&stran=%d') ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php
return ob_get_clean();

// KONEC DATOTEKE: index.php (primer_modula)
?>