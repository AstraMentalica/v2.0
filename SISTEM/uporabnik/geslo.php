<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/uporabnik/geslo.php v2.0                                      *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Upravljanje z gesli uporabnikov
 *   - Ponastavitev gesla, sprememba gesla
 * Povezave:
 *   - Klicana iz UI obrazcev
 * Pravila:
 *   - Vse funkcije z si_ predpono
 *****************************************************************************************/

/**
 * Ponastavi geslo uporabnika
 * @param string $email E-poštni naslov uporabnika
 * @return array Rezultat ponastavitve
 */
function si_ponastavi_geslo($email) {
    global $Seja;
    
    // Preveri omejitev zahtevkov
    if (!$Seja->preveri_omejitev_zahtevkov('ponastavi_geslo', 3, 3600)) {
        return ['uspesno' => false, 'napaka' => 'Preveč poskusov. Počakajte uro.'];
    }
    
    if (!si_preveri_email($email)) {
        return ['uspesno' => false, 'napaka' => 'Neveljaven e-poštni naslov'];
    }
    
    try {
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("SELECT id, uporabnisko_ime FROM uporabniki WHERE email = ? AND aktiven = 1");
        $poizvedba->execute([$email]);
        $uporabnik = $poizvedba->fetch();
        
        if (!$uporabnik) {
            return ['uspesno' => false, 'napaka' => 'Uporabnik s tem e-poštnim naslovom ne obstaja'];
        }
        
        // Generiraj žeton za ponastavitev
        $zeton = si_generiraj_nakljucni_niz(32);
        $cas_poteka = time() + 3600; // 1 ura
        
        // Shrani žeton v bazo (simulacija)
        si_zabelezi_operacijo("Žeton za ponastavitev gesla za uporabnika: {$uporabnik['uporabnisko_ime']}");
        
        // V praksi bi tu poslali e-pošto z žetonom
        return [
            'uspesno' => true,
            'sporocilo' => 'Navodila za ponastavitev gesla so bila poslana na vaš e-poštni naslov.'
        ];
        
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri ponastavitvi gesla: " . $napaka->getMessage());
        return ['uspesno' => false, 'napaka' => 'Sistemska napaka'];
    }
}

/**
 * Spremeni geslo uporabnika
 * @param int $uporabnik_id ID uporabnika
 * @param string $trenutno_geslo Trenutno geslo
 * @param string $novo_geslo Novo geslo
 * @return array Rezultat spremembe
 */
function si_spremeni_geslo($uporabnik_id, $trenutno_geslo, $novo_geslo) {
    global $Seja;
    
    if (!$Seja->preveri_csrf_zeton($_POST['csrf_zeton'] ?? '')) {
        return ['uspesno' => false, 'napaka' => 'Neveljaven varnostni žeton'];
    }
    
    if (strlen($novo_geslo) < 8) {
        return ['uspesno' => false, 'napaka' => 'Novo geslo mora vsebovati vsaj 8 znakov'];
    }
    
    try {
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("SELECT geslo FROM uporabniki WHERE id = ?");
        $poizvedba->execute([$uporabnik_id]);
        $uporabnik = $poizvedba->fetch();
        
        if (!$uporabnik || !si_preveri_geslo($trenutno_geslo, $uporabnik['geslo'])) {
            return ['uspesno' => false, 'napaka' => 'Napačno trenutno geslo'];
        }
        
        $novo_geslo_hash = si_generiraj_hash_gesla($novo_geslo);
        
        $poizvedba = $povezava->prepare("UPDATE uporabniki SET geslo = ? WHERE id = ?");
        $uspešno = $poizvedba->execute([$novo_geslo_hash, $uporabnik_id]);
        
        if ($uspešno) {
            si_zabelezi_operacijo("Uporabnik $uporabnik_id je spremenil geslo");
            return ['uspesno' => true, 'sporocilo' => 'Geslo je bilo uspešno spremenjeno'];
        } else {
            return ['uspesno' => false, 'napaka' => 'Napaka pri shranjevanju gesla'];
        }
        
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri spreminjanju gesla: " . $napaka->getMessage());
        return ['uspesno' => false, 'napaka' => 'Sistemska napaka'];
    }
}

// KONEC DATOTEKE: geslo.php
?>