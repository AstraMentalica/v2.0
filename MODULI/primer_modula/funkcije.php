<?php
/*****************************************************************************************
 *     pot: /root/MODULI/primer_modula/funkcije.php v2.0                               *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Funkcije primer modula
 *   - Demonstracija modul specificnih funkcij
 * Povezave:
 *   - Klicane iz index.php modula
 * Pravila:
 *   - Samo modul specificne funkcije z m_ predpono
 *****************************************************************************************/

/**
 * Dodaj element v primer modula
 * @param string $naziv Naziv elementa
 * @param string $vsebina Vsebina elementa
 * @return int|false ID elementa ali false ob napaki
 */
function m_dodaj_element($naziv, $vsebina) {
    global $Seja;
    
    try {
        $povezava = Baza::pridobi_povezavo();
        
        // Preveri, ali tabela obstaja
        $tabelaObstaja = $povezava->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='primer_elementi'")->fetch();
        
        if (!$tabelaObstaja) {
            $povezava->exec("
                CREATE TABLE primer_elementi (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    naziv TEXT NOT NULL,
                    vsebina TEXT,
                    uporabnik_id INTEGER NOT NULL,
                    datum_ustvarjen DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
        
        $poizvedba = $povezava->prepare("
            INSERT INTO primer_elementi (naziv, vsebina, uporabnik_id) 
            VALUES (?, ?, ?)
        ");
        
        $poizvedba->execute([$naziv, $vsebina, $Seja->uporabnik_id]);
        
        return $povezava->lastInsertId();
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri dodajanju elementa: " . $napaka->getMessage());
        return false;
    }
}

/**
 * Pridobi elemente iz primer modula
 * @param int $zamik Začetni zamik
 * @param int $omejitev Število elementov
 * @return array Seznam elementov
 */
function m_pridobi_elemente($zamik = 0, $omejitev = 10) {
    try {
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("
            SELECT pe.*, u.uporabnisko_ime 
            FROM primer_elementi pe 
            LEFT JOIN uporabniki u ON pe.uporabnik_id = u.id 
            ORDER BY pe.datum_ustvarjen DESC 
            LIMIT ? OFFSET ?
        ");
        
        $poizvedba->bindValue(1, $omejitev, PDO::PARAM_INT);
        $poizvedba->bindValue(2, $zamik, PDO::PARAM_INT);
        $poizvedba->execute();
        
        return $poizvedba->fetchAll();
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri pridobivanju elementov: " . $napaka->getMessage());
        return [];
    }
}

/**
 * Preštej vse elemente v primer modulu
 * @return int Število elementov
 */
function m_prestej_elemente() {
    try {
        $povezava = Baza::pridobi_povezavo();
        $poizvedba = $povezava->prepare("SELECT COUNT(*) as skupaj FROM primer_elementi");
        $poizvedba->execute();
        
        return $poizvedba->fetch()['skupaj'] ?? 0;
    } catch (Exception $napaka) {
        si_zabelezi_napako("Napaka pri štetju elementov: " . $napaka->getMessage());
        return 0;
    }
}

// KONEC DATOTEKE: funkcije.php (primer_modula)
?>