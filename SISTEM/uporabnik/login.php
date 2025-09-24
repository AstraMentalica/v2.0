<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/uporabnik/login.php v2.0                                      *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Obdelava prijave uporabnika
 *   - Vse funkcije z si_ predpono (sistemska logika)
 * Povezave:
 *   - Klicana iz UI obrazcev
 * Pravila:
 *   - Samo logika, brez UI
 *****************************************************************************************/

/**
 * Obdelaj prijavo uporabnika
 * @param array $podatki Podatki iz prijavnega obrazca
 * @return array Rezultat prijave
 */
function si_obdelaj_prijavo($podatki) {
    global $Seja;
    
    $napake = [];
    $uspesno = false;
    
    // Preveri CSRF zeton
    if (!$Seja->preveri_csrf_zeton($podatki['csrf_zeton'] ?? '')) {
        $napake[] = 'Neveljaven varnostni zeton';
        return ['uspesno' => false, 'napake' => $napake];
    }
    
    // Preveri omejitev zahtevkov
    if (!$Seja->preveri_omejitev_zahtevkov('prijava', 3, 300)) {
        $napake[] = 'Prevec poskusov prijave. Počakajte 5 minut.';
        return ['uspesno' => false, 'napake' => $napake];
    }
    
    // Validiraj podatke
    $validacija = $Seja->validiraj_podatke($podatki, [
        'uporabnisko_ime' => ['obvezno'],
        'geslo' => ['obvezno']
    ]);
    
    if (!$validacija['uspesno']) {
        return ['uspesno' => false, 'napake' => $validacija['napake']];
    }
    
    $cisti_podatki = $validacija['cisti_podatki'];
    $uporabnisko_ime = $cisti_podatki['uporabnisko_ime'];
    $geslo = $podatki['geslo']; // Geslo ni sanitizirano (hash se primerja)
    
    try {
        // Poisci uporabnika v bazi
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("
            SELECT * FROM uporabniki 
            WHERE uporabnisko_ime = ? AND aktiven = 1
        ");
        $poizvedba->execute([$uporabnisko_ime]);
        $uporabnik = $poizvedba->fetch();
        
        if (!$uporabnik) {
            $napake[] = 'Napačno uporabnisko ime ali geslo';
            return ['uspesno' => false, 'napake' => $napake];
        }
        
        // Preveri geslo
        if (!si_preveri_geslo($geslo, $uporabnik['geslo'])) {
            $napake[] = 'Napačno uporabnisko ime ali geslo';
            return ['uspesno' => false, 'napake' => $napake];
        }
        
        // Prijavi uporabnika
        $Seja->prijava($uporabnik);
        $uspesno = true;
        
        si_zabelezi_operacijo("Uspesna prijava: $uporabnisko_ime");
        
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri prijavi: " . $napaka->getMessage());
        $napake[] = 'Sistemska napaka. Poskusite znova.';
    }
    
    return ['uspesno' => $uspesno, 'napake' => $napake];
}

// KONEC DATOTEKE: login.php
?>