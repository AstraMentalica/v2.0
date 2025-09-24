<?php
/*****************************************************************************************
 *     pot: /root/MODULI/ai_pomocnik/funkcije.php v2.0                                 *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Funkcije AI pomocnika modula
 *   - Vse funkcije z m_ predpono
 * Povezave:
 *   - Klicane iz index.php modula
 * Pravila:
 *   - Samo modul specificne funkcije
 *****************************************************************************************/

/**
 * Poslji zahtevo Google AI
 * @param string $sporocilo Sporocilo za AI
 * @return array Odgovor AI
 */
function m_google_ai($sporocilo) {
    if (empty(GOOGLE_API_KLJUC)) {
        return ['napaka' => 'Google API kljuc ni nastavljen'];
    }
    
    // Simulacija AI odgovora (v praksi bi bil pravi API klic)
    $odgovori = [
        "Pozdravljen! Kako ti lahko pomagam?",
        "To je zanimivo vprasanje. Kaj še bi rad vedel?",
        "Na podlagi mojega znanja lahko rečem, da...",
        "Trenutno nimam dovolj informacij za popoln odgovor."
    ];
    
    $nakljucni_odgovor = $odgovori[array_rand($odgovori)];
    
    return [
        'uspeh' => true,
        'odgovor' => $nakljucni_odgovor,
        'ponudnik' => 'google',
        'cas' => date('Y-m-d H:i:s')
    ];
}

/**
 * Poslji zahtevo DeepSeek AI
 * @param string $sporocilo Sporocilo za AI
 * @return array Odgovor AI
 */
function m_deepseek_ai($sporocilo) {
    if (empty(DEEPSEEK_API_KLJUC)) {
        return ['napaka' => 'DeepSeek API kljuc ni nastavljen'];
    }
    
    // Simulacija AI odgovora
    $odgovori = [
        "Zanima me tvoje vprasanje. Lahko poveš vec?",
        "Kot AI model lahko predlagam naslednje...",
        "To vprasanje zahteva dodatno analizo.",
        "Na podlagi trenutnega konteksta lahko rečem..."
    ];
    
    $nakljucni_odgovor = $odgovori[array_rand($odgovori)];
    
    return [
        'uspeh' => true,
        'odgovor' => $nakljucni_odgovor,
        'ponudnik' => 'deepseek',
        'cas' => date('Y-m-d H:i:s')
    ];
}

/**
 * Obdelaj zahtevo AI
 * @param string $sporocilo Sporocilo za AI
 * @param string $ponudnik Izbrani ponudnik AI
 * @return array Rezultat obdelave
 */
function m_obdelaj_ai_zahtevo($sporocilo, $ponudnik = 'google') {
    global $Seja;
    
    // Preveri omejitev zahtevkov
    if (!$Seja->preveri_omejitev_zahtevkov('ai_zahteva', 10, 3600)) {
        return ['napaka' => 'Prevec zahtevkov. Počakajte uro.'];
    }
    
    // Sanitiziraj vhod
    $sporocilo = si_sanitiziraj_vhod(trim($sporocilo));
    
    if (empty($sporocilo)) {
        return ['napaka' => 'Sporocilo ne sme biti prazno'];
    }
    
    if (strlen($sporocilo) > 1000) {
        return ['napaka' => 'Sporocilo je predolgo (max 1000 znakov)'];
    }
    
    // Poslji zahtevo izbranemu ponudniku
    switch ($ponudnik) {
        case 'google':
            $rezultat = m_google_ai($sporocilo);
            break;
        case 'deepseek':
            $rezultat = m_deepseek_ai($sporocilo);
            break;
        default:
            $rezultat = ['napaka' => 'Nepodprt ponudnik AI'];
    }
    
    // Zabeleži uspesno zahtevo
    if (isset($rezultat['uspeh']) && $rezultat['uspeh']) {
        m_shrani_ai_pogovor($Seja->uporabnik_id, $ponudnik, $sporocilo, $rezultat['odgovor']);
        si_zabelezi_operacijo("AI zahteva uporabnika {$Seja->uporabnik_ime}: $sporocilo");
    }
    
    return $rezultat;
}

/**
 * Shrani AI pogovor v bazo
 * @param int $uporabnik_id ID uporabnika
 * @param string $ponudnik Ponudnik AI
 * @param string $vprasanje Vprasanje uporabnika
 * @param string $odgovor Odgovor AI
 * @return bool True, ce je shranjevanje uspelo
 */
function m_shrani_ai_pogovor($uporabnik_id, $ponudnik, $vprasanje, $odgovor) {
    try {
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("
            INSERT INTO ai_pogovori (uporabnik_id, ponudnik, vprasanje, odgovor) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $poizvedba->execute([$uporabnik_id, $ponudnik, $vprasanje, $odgovor]);
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri shranjevanju AI pogovora: " . $napaka->getMessage());
        return false;
    }
}

/**
 * Pridobi zgodovino AI pogovora
 * @param int $uporabnik_id ID uporabnika
 * @param int $omejitev Stevilo zadnjih vnosov
 * @return array Zgodovina pogovora
 */
function m_pridobi_zgodovino_ai($uporabnik_id, $omejitev = 10) {
    try {
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("
            SELECT * FROM ai_pogovori 
            WHERE uporabnik_id = ? 
            ORDER BY cas DESC 
            LIMIT ?
        ");
        $poizvedba->bindValue(1, $uporabnik_id, PDO::PARAM_INT);
        $poizvedba->bindValue(2, $omejitev, PDO::PARAM_INT);
        $poizvedba->execute();
        
        return $poizvedba->fetchAll();
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri pridobivanju zgodovine AI: " . $napaka->getMessage());
        return [];
    }
}

// KONEC DATOTEKE: funkcije.php (ai_pomocnik)
?>