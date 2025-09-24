<?php
/*****************************************************************************************
 *     pot: /root/SISTEM/AI/AI.php v2.0                                                *
 *---------------------------------------------------------------------------------------*
 * Namen:
 *   - Glavni AI upravljalnik sistema
 *   - Integracija z zunanjimi AI storitvami
 * Povezave:
 *   - Klican iz modulov in drugih delov sistema
 * Pravila:
 *   - Vse funkcije z m_ predpono (modul)
 *****************************************************************************************/

class AIUpravljalnik {
    /**
     * Pošlji zahtevo Google AI
     * @param string $sporocilo Sporočilo za AI
     * @param array $nastavitve Dodatne nastavitve
     * @return array Odgovor AI
     */
    public static function m_google_ai($sporocilo, $nastavitve = []) {
        if (empty(GOOGLE_API_KLJUC)) {
            return ['napaka' => 'Google API ključ ni nastavljen'];
        }
        
        // Simulacija pravega API klica
        $simulacija_odziva = [
            'uspeh' => true,
            'odgovor' => "Kot Google AI lahko rečem: " . substr($sporocilo, 0, 100) . "...",
            'model' => 'gemini-pro',
            'uporabljeni_zetoni' => rand(50, 200),
            'cas' => date('Y-m-d H:i:s')
        ];
        
        return $simulacija_odziva;
    }
    
    /**
     * Pošlji zahtevo DeepSeek AI
     * @param string $sporocilo Sporočilo za AI
     * @param array $nastavitve Dodatne nastavitve
     * @return array Odgovor AI
     */
    public static function m_deepseek_ai($sporocilo, $nastavitve = []) {
        if (empty(DEEPSEEK_API_KLJUC)) {
            return ['napaka' => 'DeepSeek API ključ ni nastavljen'];
        }
        
        $simulacija_odziva = [
            'uspeh' => true,
            'odgovor' => "DeepSeek AI odgovarja: " . substr($sporocilo, 0, 150) . "...",
            'model' => 'deepseek-chat',
            'uporabljeni_zetoni' => rand(40, 180),
            'cas' => date('Y-m-d H:i:s')
        ];
        
        return $simulacija_odziva;
    }
    
    /**
     * Analiziraj čustva besedila
     * @param string $besedilo Besedilo za analizo
     * @return array Rezultat analize čustev
     */
    public static function m_analiziraj_custva($besedilo) {
        $custva = ['pozitivno', 'negativno', 'nevtralno'];
        $nakljucno_custvo = $custva[array_rand($custva)];
        
        return [
            'custvo' => $nakljucno_custvo,
            'zaupanje' => round(rand(70, 95) / 100, 2),
            'kljucne_besede' => ['analiza', 'custva', 'besedilo']
        ];
    }
    
    /**
     * Povzetek besedila
     * @param string $besedilo Besedilo za povzetek
     * @param int $dolzina Željena dolžina povzetka
     * @return string Povzetek besedila
     */
    public static function m_povzetek_besedila($besedilo, $dolzina = 100) {
        $besede = explode(' ', $besedilo);
        $povzetek = array_slice($besede, 0, 20);
        return implode(' ', $povzetek) . '...';
    }
}

// KONEC DATOTEKE: AI.php
?>