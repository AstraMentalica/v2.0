<?php
/*****************************************************************************************
 *     pot: /root/MODULI/ai_pomocnik/konfiguracija.php v2.0                            *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Konfiguracija AI pomocnika modula
 *   - Definicija modula in njegovih nastavitev
 * Povezave:
 *   - Uporablja se v nalagalniku modulov
 * Pravila:
 *   - Samo konfiguracija, brez logike
 *****************************************************************************************/

return [
    'ime' => 'AI Pomocnik',
    'opis' => 'Modul za delo z umetno inteligenco',
    'verzija' => '1.0.0',
    'avtor' => 'Razvijalec',
    'omogocen' => true,
    'vloga' => 'uporabnik',
    
    // Modul specificne nastavitve
    'maksimalno_stevilo_zahtev' => 50,
    'dovoljeni_tipi_odgovorov' => ['tekst', 'json', 'html'],
    'privzeti_ponudnik_ai' => 'google',
    
    // API kljuci (referenca na .env)
    'google_api_kljuc' => GOOGLE_API_KLJUC,
    'deepseek_api_kljuc' => DEEPSEEK_API_KLJUC
];

// KONEC DATOTEKE: konfiguracija.php (ai_pomocnik)
?>