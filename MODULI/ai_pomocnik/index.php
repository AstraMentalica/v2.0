<?php
/*****************************************************************************************
 *     pot: /root/MODULI/ai_pomocnik/index.php v2.0                                    *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Glavna vsebina AI pomočnika modula
 *   - Prikazuje vmesnik in obdeluje zahteve
 * Povezave:
 *   - Klican iz nalagalnika modulov
 * Pravila:
 *   - Samo prikaz in osnovna obdelava obrazcev
 *****************************************************************************************/

// Preveri, ali je modul klican v globalnem kontekstu
if (!defined('ASTRA')) {
    // Samostojni način - prikaži napako
    header('HTTP/1.0 403 Dostop zavrnjen');
    echo "Dostop dovoljen samo preko glavnega sistema.";
    exit;
}

// Obdelaj obrazce
$napake = [];
$uspešno = false;
$ai_odgovor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_vprasanje'])) {
    global $Seja;
    
    // Preveri CSRF žeton
    if (!$Seja->preveri_csrf_zeton($_POST['csrf_zeton'] ?? '')) {
        $napake[] = 'Neveljaven varnostni žeton';
    } else {
        $vprasanje = $_POST['ai_vprasanje'] ?? '';
        $ponudnik = $_POST['ai_ponudnik'] ?? 'google';
        
        $rezultat = m_obdelaj_ai_zahtevo($vprasanje, $ponudnik);
        
        if (isset($rezultat['napaka'])) {
            $napake[] = $rezultat['napaka'];
        } else {
            $uspešno = true;
            $ai_odgovor = $rezultat;
            ui_dodaj_obvestilo('AI je odgovoril na vaše vprašanje', 'uspeh');
        }
    }
}

// Vrnemo vsebino, ki jo bo globalni layout prikazal
ob_start();
?>
<div class="vsebina-modula">
    <h1>AI Pomocnik</h1>
    <p class="opis-modula">Modul za delo z umetno inteligenco</p>
    
    <?php if (!empty($napake)): ?>
        <div class="napake">
            <?php foreach ($napake as $napaka): ?>
                <div class="obvestilo obvestilo-napaka"><?= $napaka ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="vsebina-modula-poln">
        <section class="ai-pogovor">
            <h2>Pogovor z AI</h2>
            
            <form method="post" class="obrazec-ai">
                <input type="hidden" name="csrf_zeton" value="<?= $Seja->generiraj_csrf_zeton() ?>">
                
                <div class="skupina-vnosa">
                    <label for="ai_ponudnik">Ponudnik AI:</label>
                    <select id="ai_ponudnik" name="ai_ponudnik">
                        <option value="google">Google AI</option>
                        <option value="deepseek">DeepSeek AI</option>
                    </select>
                </div>
                
                <div class="skupina-vnosa">
                    <label for="ai_vprasanje">Vaše vprašanje:</label>
                    <textarea id="ai_vprasanje" name="ai_vprasanje" rows="4" 
                              placeholder="Vnesite vaše vprašanje za AI..." required></textarea>
                </div>
                
                <button type="submit" class="gumb gumb-primarni">
                    <?= ui_ikona('iskanje') ?> Pošlji vprašanje
                </button>
            </form>
            
            <?php if ($ai_odgovor): ?>
                <div class="ai-odgovor">
                    <h3>Odgovor AI:</h3>
                    <div class="odgovor-vsebina">
                        <p><?= nl2br(htmlspecialchars($ai_odgovor['odgovor'])) ?></p>
                        <div class="meta-podatki">
                            Ponudnik: <?= $ai_odgovor['ponudnik'] ?> | 
                            Čas: <?= ui_formatiraj_datum($ai_odgovor['cas'], 'dolg') ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
        
        <section class="ai-informacije">
            <h2>O AI pomočniku</h2>
            <div class="informacije-vsebina">
                <p>AI pomočnik vam omogoča komunikacijo z naprednimi modeli umetne inteligence.</p>
                
                <h3>Podprti ponudniki:</h3>
                <ul>
                    <li><strong>Google AI:</strong> Napreden model za splošna vprašanja</li>
                    <li><strong>DeepSeek AI:</strong> Specializiran za tehnična vprašanja</li>
                </ul>
                
                <h3>Omejitve:</h3>
                <ul>
                    <li>Maksimalno 10 vprašanj na uro</li>
                    <li>Dolžina sporočila do 1000 znakov</li>
                    <li>Podpora za tekstualne odgovore</li>
                </ul>
            </div>
        </section>
    </div>
</div>

<style>
.ai-odgovor {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #27ae60;
}

.odgovor-vsebina {
    line-height: 1.6;
}

.obrazec-ai {
    margin-bottom: 2rem;
}

.informacije-vsebina ul {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.informacije-vsebina li {
    margin-bottom: 0.5rem;
}
</style>
<?php
return ob_get_clean();

// KONEC DATOTEKE: index.php (ai_pomocnik)
?>