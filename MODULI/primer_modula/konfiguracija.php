<?php
/*****************************************************************************************
 *     pot: /root/MODULI/primer_modula/konfiguracija.php v2.0                           *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Konfiguracija primer modula
 *   - Demonstracija strukture modula
 * Povezave:
 *   - Uporablja se v nalagalniku modulov
 * Pravila:
 *   - Samo konfiguracija, brez logike
 *****************************************************************************************/

return [
    'ime' => 'Primer Modula',
    'opis' => 'Demonstracijski modul za prikaz strukture',
    'verzija' => '1.0.0',
    'avtor' => 'Razvijalec',
    'omogocen' => true,
    'vloga' => 'uporabnik',
    
    // Modul specificne nastavitve
    'maksimalno_stevilo_elementov' => 50,
    'dovoljeni_tipi_datotek' => ['pdf', 'doc', 'docx', 'txt']
];

// KONEC DATOTEKE: konfiguracija.php (primer_modula)
?>