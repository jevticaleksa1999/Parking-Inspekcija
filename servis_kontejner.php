<?php
/*
CRC: ServisKontejner
Odgovornosti: Jednostavan DI – instancira repozitorijume i servise.
Saradnici: DBKonekcija, MySQLPrijavaRepozitorijum, MySQLKorisnikRepozitorijum, servisi.
*/

// Uključivanje potrebnih klasa za repozitorijume i servise
require_once __DIR__ . "/DBKonekcija.php";
require_once __DIR__ . "/MySQLPrijavaRepozitorijum.php";
require_once __DIR__ . "/MySQLKorisnikRepozitorijum.php";
require_once __DIR__ . "/AuthServis.php";
require_once __DIR__ . "/TransakcijaServis.php";

// ServisKontejner: služi kao centralno mesto za instanciranje i distribuciju zavisnosti (DI container)
class ServisKontejner {
    private mysqli $conn;

    // Konstruktor: čuva instancu konekcije (uzima iz DBKonekcija ako nije prosleđena)
    public function __construct(?mysqli $conn = null) { 
        $this->conn = $conn ?? DBKonekcija::get(); 
    }

    // Fabrika za repozitorijum prijava
    public function prijavaRepozitorijum(): MySQLPrijavaRepozitorijum {
        return new MySQLPrijavaRepozitorijum($this->conn);
    }

    // Fabrika za repozitorijum korisnika
    public function korisnikRepozitorijum(): MySQLKorisnikRepozitorijum {
        return new MySQLKorisnikRepozitorijum($this->conn);
    }

    // Fabrika za servis autentikacije
    public function authServis(): AuthServis {
        return new AuthServis($this->korisnikRepozitorijum());
    }

    // Fabrika za servis transakcija
    public function transakcijaServis(): TransakcijaServis {
        return new TransakcijaServis($this->conn);
    }
}
