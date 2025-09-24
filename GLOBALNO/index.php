<?php
/*****************************************************************************************
 *     pot: /root/GLOBALNO/index.php v2.0                                              *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Glavna vstopna tocka spletne strani
 *   - Nalozi globalno.php in prikaze UI
 * Povezave:
 *   - Prva datoteka, ki se nalozi ob obisku spletnega mesta
 *   - Kliče SISTEM/globalno.php
 * Pravila:
 *   - Minimalna koda
 *   - Samo include in UI render
 *****************************************************************************************/

// Definiraj konstantno za preprečevanje neposrednega dostopa
define('ASTRA', true);

// Nalozi glavni bootstrap sistem
require_once __DIR__ . '/../SISTEM/globalno.php';

// Določi, ali je zahteva za API ali UI
$pot = $_SERVER['REQUEST_URI'];
if (strpos($pot, '/api/') !== false) {
    // API zahteva - obdelaj posebej
    header('Content-Type: application/json');
    
    $odgovor = [
        'status' => 'napaka',
        'sporocilo' => 'API ni implementiran',
        'cas' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($odgovor);
    exit;
}

// Vkljuci UI layout
global $Globalno;
require_once $Globalno['svet'] . '/ui/postavitev.php';

// KONEC DATOTEKE: index.php
?>