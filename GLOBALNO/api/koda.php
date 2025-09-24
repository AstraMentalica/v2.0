<?php
// GLOBALNO/api/koda.php
// Preprost API za branje, pisanje in dodajanje vsebine datotek v projektu
// VARNO: Dovoli samo dostop do datotek znotraj projekta (brez ../ ali sistemskih poti)

header('Content-Type: application/json; charset=utf-8');

// Preveri metodo
$metoda = $_SERVER['REQUEST_METHOD'];

// Preveri parametre
$pot = $_GET['pot'] ?? null;
if (!$pot || strpos($pot, '..') !== false || strpos($pot, '/etc/') !== false) {
    http_response_code(400);
    echo json_encode(['status' => 'napaka', 'sporocilo' => 'Neveljavna pot.']);
    exit;
}

$osnovna_mapa = realpath(__DIR__ . '/../../');
$ciljna_pot = realpath($osnovna_mapa . '/' . $pot);
if (!$ciljna_pot || strpos($ciljna_pot, $osnovna_mapa) !== 0) {
    http_response_code(403);
    echo json_encode(['status' => 'napaka', 'sporocilo' => 'Dostop zavrnjen.']);
    exit;
}

switch ($metoda) {
    case 'GET':
        // Branje vsebine ali seznam datotek
        if (isset($_GET['seznam'])) {
            // Pridobi seznam vseh datotek in map (rekurzivno)
            function seznam_datotek($mapa) {
                $rez = [];
                $vsi = scandir($mapa);
                foreach ($vsi as $dat) {
                    if ($dat === '.' || $dat === '..') continue;
                    $polna = $mapa . DIRECTORY_SEPARATOR . $dat;
                    if (is_dir($polna)) {
                        $rez[] = [
                            'tip' => 'mapa',
                            'ime' => $dat,
                            'vsebina' => seznam_datotek($polna)
                        ];
                    } else {
                        $rez[] = [
                            'tip' => 'datoteka',
                            'ime' => $dat
                        ];
                    }
                }
                return $rez;
            }
            $seznam = seznam_datotek($ciljna_pot);
            echo json_encode(['status' => 'ok', 'seznam' => $seznam]);
            break;
        }
        // Branje vsebine
        if (!file_exists($ciljna_pot)) {
            http_response_code(404);
            echo json_encode(['status' => 'napaka', 'sporocilo' => 'Datoteka ne obstaja.']);
            exit;
        }
        $vsebina = file_get_contents($ciljna_pot);
        echo json_encode(['status' => 'ok', 'vsebina' => $vsebina]);
        break;
    case 'POST':
        // Pisanje, dodajanje, ustvarjanje datotek/map
        $vhod = json_decode(file_get_contents('php://input'), true);
        $nova_vsebina = $vhod['vsebina'] ?? null;
        $nacin = $vhod['nacin'] ?? 'zamenjaj'; // zamenjaj | dodaj | ustvari | mapa
        if ($nacin === 'ustvari') {
            if (file_exists($ciljna_pot)) {
                http_response_code(409);
                echo json_encode(['status' => 'napaka', 'sporocilo' => 'Datoteka že obstaja.']);
                exit;
            }
            file_put_contents($ciljna_pot, $nova_vsebina ?? '');
            echo json_encode(['status' => 'ok', 'sporocilo' => 'Datoteka ustvarjena.']);
            break;
        }
        if ($nacin === 'mapa') {
            if (file_exists($ciljna_pot)) {
                http_response_code(409);
                echo json_encode(['status' => 'napaka', 'sporocilo' => 'Mapa že obstaja.']);
                exit;
            }
            mkdir($ciljna_pot, 0755, true);
            echo json_encode(['status' => 'ok', 'sporocilo' => 'Mapa ustvarjena.']);
            break;
        }
        if ($nova_vsebina === null) {
            http_response_code(400);
            echo json_encode(['status' => 'napaka', 'sporocilo' => 'Ni vsebine za zapis.']);
            exit;
        }
        if ($nacin === 'dodaj') {
            file_put_contents($ciljna_pot, $nova_vsebina, FILE_APPEND);
        } else {
            file_put_contents($ciljna_pot, $nova_vsebina);
        }
        echo json_encode(['status' => 'ok', 'sporocilo' => 'Vsebina zapisana.']);
        break;
    case 'DELETE':
        // Brisanje datoteke ali prazne mape
        if (!file_exists($ciljna_pot)) {
            http_response_code(404);
            echo json_encode(['status' => 'napaka', 'sporocilo' => 'Datoteka ali mapa ne obstaja.']);
            exit;
        }
        if (is_dir($ciljna_pot)) {
            // Briši le prazne mape
            if (count(scandir($ciljna_pot)) > 2) {
                http_response_code(409);
                echo json_encode(['status' => 'napaka', 'sporocilo' => 'Mapa ni prazna.']);
                exit;
            }
            rmdir($ciljna_pot);
            echo json_encode(['status' => 'ok', 'sporocilo' => 'Mapa izbrisana.']);
        } else {
            unlink($ciljna_pot);
            echo json_encode(['status' => 'ok', 'sporocilo' => 'Datoteka izbrisana.']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => 'napaka', 'sporocilo' => 'Metoda ni dovoljena.']);
}
