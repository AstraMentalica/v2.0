<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/sistem/kljuci.php v2.0                                        *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Varnostni ključi in šifrirane poti
 *   - Generiranje in upravljanje kriptografskih ključev
 * Povezave:
 *   - Naložen iz SISTEM/globalno.php (če obstaja)
 *   - Opcijski za dodatno varnost
 * Pravila:
 *   - Samo definicije ključev
 *   - Brez izvajanja logike
 *****************************************************************************************/

// Šifrirni ključi za sistem
define('KLJUC_SEJA', 'astra_mentalica_seja_kljuc_' . bin2hex(random_bytes(16)));
define('KLJUC_COOKIE', 'astra_mentalica_cookie_kljuc_' . bin2hex(random_bytes(16)));
define('KLJUC_BAZA', 'astra_mentalica_baza_kljuc_' . bin2hex(random_bytes(16)));

// Ključi za hashiranje
define('HASH_PEAKER', 'astra_mentalica_hash_peaker_' . bin2hex(random_bytes(8)));

// Varnostni salt vrednosti
define('SALT_GESLO', 'astra_mentalica_geslo_salt_' . bin2hex(random_bytes(12)));
define('SALT_CSRF', 'astra_mentalica_csrf_salt_' . bin2hex(random_bytes(12)));

// Ključi za šifriranje datotek
define('KLJUC_DATOTEKE', 'astra_mentalica_datoteke_kljuc_' . bin2hex(random_bytes(24)));

// KONEC DATOTEKE: kljuci.php
?>