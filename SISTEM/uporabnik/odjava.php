<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/uporabnik/odjava.php v2.0                                     *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Obdelava odjave uporabnika
 *   - Vse funkcije z si_ predpono (sistemska logika)
 * Povezave:
 *   - Klicana iz UI obrazcev
 * Pravila:
 *   - Samo logika odjave, brez UI
 *****************************************************************************************/

/**
 * Obdelaj zahtevo za odjavo
 * @return array Rezultat odjave
 */
function si_obdelaj_odjavo() {
    global $Seja;
    
    // Preveri CSRF žeton
    if (!$Seja->preveri_csrf_zeton($_POST['csrf_zeton'] ?? '')) {
        return ['uspesno' => false, 'napaka' => 'Neveljaven varnostni žeton'];
    }
    
    try {
        $Seja->odjava();
        return ['uspesno' => true, 'sporocilo' => 'Uspešno odjavljeni'];
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri odjavi: " . $napaka->getMessage());
        return ['uspesno' => false, 'napaka' => 'Sistemska napaka pri odjavi'];
    }
}

// KONEC DATOTEKE: odjava.php
?>