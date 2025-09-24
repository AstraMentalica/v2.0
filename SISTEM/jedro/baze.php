<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/jedro/baze.php v2.0                                           *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Upravljanje s podatkovnimi bazami
 *   - PDO factory z MySQL in SQLite fallback
 *   - Singleton pattern za varen dostop
 * Povezave:
 *   - Nalozen iz SISTEM/globalno.php
 *   - Uporablja konstante iz konfiguracije.php
 * Pravila:
 *   - Samo povezave z bazo
 *   - Prepared statements za varnost
 *   - Avtomatski fallback na SQLite
 *****************************************************************************************/

class Baza {
    private static $povezave = [];
    private static $instance = null;
    
    /**
     * Pridobi povezavo z bazo
     * @param string $ime Ime povezave (default: 'globalna')
     * @return PDO PDO objekt za delo z bazo
     * @throws Exception Ce povezava ni uspela
     */
    public static function pridobi_povezavo($ime = 'globalna') {
        if (isset(self::$povezave[$ime])) {
            return self::$povezave[$ime];
        }
        
        try {
            // Poskusi MySQL povezavo
            $dsn = "mysql:host=" . GOSTITELJ_BAZE . ";dbname=" . IME_BAZE . ";charset=utf8mb4";
            $moznosti = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $pdo = new PDO($dsn, UPORABNIK_BAZE, GESLO_BAZE, $moznosti);
            self::$povezave[$ime] = $pdo;
            
            si_zabelezi_operacijo("Uspesna MySQL povezava: $ime");
            return $pdo;
            
        } catch (PDOException $napaka) {
            // Fallback na SQLite, ce MySQL ni na voljo
            if ($ime === 'globalna') {
                si_zabelezi_operacijo("MySQL povezava ni uspela, poskusam SQLite fallback");
                return self::pridobi_sqlite_povezavo();
            }
            
            si_zabelezi_napako("Napaka pri povezavi z bazo: " . $napaka->getMessage());
            throw new Exception("Napaka pri povezavi s podatkovno bazo");
        }
    }
    /**
	* Pridobi SQLite povezavo (fallback)
	* @return PDO SQLite PDO objekt
	* @throws Exception Ce SQLite povezava ni uspela
	*/
	private static function pridobi_sqlite_povezavo() {
		global $Globalno;
		$pot_sqlite = $Globalno['zrak'] . '/podatki' . $Globalno['locilo'] . 'sistem.db';
		$mapa = dirname($pot_sqlite);
    
		if (!file_exists($mapa)) {
			mkdir($mapa, 0755, true);
			si_zabelezi_operacijo("Ustvarjena mapa za SQLite: $mapa");
		}
    
		try {
			$pdo = new PDO("sqlite:" . $pot_sqlite);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
			// Inicializiraj bazo, ce se ne obstaja
			self::inicializiraj_sqlite_bazo($pdo);
        
			self::$povezave['sqlite'] = $pdo;
			si_zabelezi_operacijo("Uspesna SQLite povezava");
			return $pdo;
        
		} catch (PDOException $napaka) {
			// Dodaj bolj specificno napako
			if (strpos($napaka->getMessage(), 'unable to open database') !== false) {
				$sporocilo = "Napaka pri dostopu do SQLite baze. Preveri dovoljenja map: $mapa";
				si_zabelezi_napako($sporocilo);
				die($sporocilo);
			}
        
			si_zabelezi_napako("Napaka pri povezavi s SQLite: " . $napaka->getMessage());
			throw new Exception("Napaka pri povezavi s podatkovno bazo");
		}
	}
    
    /**
     * Inicializira SQLite bazo s potrebnimi tabelami
     * @param PDO $pdo PDO objekt
     */
    private static function inicializiraj_sqlite_bazo($pdo) {
        // Preveri, ali tabela uporabniki ze obstaja
        $rezultat = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='uporabniki'");
        if ($rezultat->fetch()) {
            return; // Tabela ze obstaja
        }
        
        si_zabelezi_operacijo("Inicializiram SQLite bazo s tabelami");
        
        // Ustvari tabelo uporabnikov
        $pdo->exec("
            CREATE TABLE uporabniki (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uporabnisko_ime TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                geslo TEXT NOT NULL,
                vloga TEXT DEFAULT 'uporabnik',
                aktiven INTEGER DEFAULT 1,
                tema TEXT DEFAULT 'svetla',
                datum_registracije DATETIME DEFAULT CURRENT_TIMESTAMP,
                zadnja_prijava DATETIME
            )
        ");
        
        // Ustvari tabelo obvestil
        $pdo->exec("
            CREATE TABLE obvestila (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uporabnik_id INTEGER NOT NULL,
                tip TEXT NOT NULL,
                sporocilo TEXT NOT NULL,
                povezava TEXT,
                prebrano INTEGER DEFAULT 0,
                datum_ustvarjeno DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Ustvari tabelo AI pogovorov
        $pdo->exec("
            CREATE TABLE ai_pogovori (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uporabnik_id INTEGER NOT NULL,
                ponudnik TEXT NOT NULL,
                vprasanje TEXT NOT NULL,
                odgovor TEXT NOT NULL,
                cas DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Ustvari privzetega administratorja
        $privzeto_geslo = si_generiraj_hash_gesla('admin123');
        $pdo->prepare("
            INSERT INTO uporabniki (uporabnisko_ime, email, geslo, vloga) 
            VALUES (?, ?, ?, ?)
        ")->execute(['admin', 'admin@example.com', $privzeto_geslo, 'administrator']);
        
        si_zabelezi_operacijo("SQLite baza uspesno inicializirana s privzetim administratorjem");
    }
    
    /**
     * Izvedi varno poizvedbo
     * @param string $sql SQL poizvedba
     * @param array $parametri Parametri za poizvedbo
     * @return PDOStatement Rezultat poizvedbe
     */
    public static function izvedi_poizvedbo($sql, $parametri = []) {
        $povezava = self::pridobi_povezavo();
        $stavek = $povezava->prepare($sql);
        $stavek->execute($parametri);
        return $stavek;
    }
    
    /**
     * Pridobi zadnji vstavljeni ID
     * @return string Zadnji ID
     */
    public static function zadnji_id() {
        $povezava = self::pridobi_povezavo();
        return $povezava->lastInsertId();
    }
    
    /**
     * Zacni transakcijo
     * @return bool True, ce je transakcija uspesno zacetka
     */
    public static function zacni_transakcijo() {
        $povezava = self::pridobi_povezavo();
        return $povezava->beginTransaction();
    }
    
    /**
     * Potrdi transakcijo
     * @return bool True, ce je transakcija uspesno potrjena
     */
    public static function potrdi_transakcijo() {
        $povezava = self::pridobi_povezavo();
        return $povezava->commit();
    }
    
    /**
     * Razveljavi transakcijo
     * @return bool True, ce je transakcija uspesno razveljavljena
     */
    public static function razveljavi_transakcijo() {
        $povezava = self::pridobi_povezavo();
        return $povezava->rollBack();
    }
}

// KONEC DATOTEKE: baze.php
?>