<?php
/*
CRC: DBKonekcija
Odgovornosti: Centralizovana kreacija i povraćaj mysqli konekcije iz JSON konfiguracije.
Saradnici: ServisKontejner, svi *Repozitorijum* fajlovi, Servisi.
*/

// Klasa za jedinstvenu konekciju sa MySQL bazom
final class DBKonekcija {
    // Privatna statička instanca konekcije
    private static ?mysqli $instanca = null;

    // Metoda za dobijanje aktivne mysqli konekcije
    public static function get(): mysqli {
        // Provera da li konekcija već postoji
        if (self::$instanca === null) {
            // Putanja do JSON fajla sa konfiguracijom baze
            $putanja = __DIR__ . "/konfiguracija_baze.json";
            if (!file_exists($putanja)) {
                throw new Exception("Nedostaje konfiguracija_baze.json");
            }

            // Učitavanje i parsiranje konfiguracionih parametara
            $cfg = json_decode(file_get_contents($putanja), true) ?: [];

            // Kreiranje nove mysqli konekcije sa parametrima iz JSON-a
            $db = new mysqli(
                $cfg["server"] ?? "localhost",
                $cfg["korisnik"] ?? "root",
                $cfg["lozinka"] ?? "",
                $cfg["baza"] ?? ""
            );

            // Provera da li je došlo do greške pri konekciji
            if ($db->connect_error) {
                throw new Exception("Greška u konekciji: " . $db->connect_error);
            }

            // Postavljanje karakter seta (podrazumevano utf8mb4)
            $db->set_charset($cfg["kodiranje"] ?? "utf8mb4");

            // Čuvanje instance za dalju upotrebu
            self::$instanca = $db;
        }

        // Vraćanje postojeće ili nove konekcije
        return self::$instanca;
    }
}
