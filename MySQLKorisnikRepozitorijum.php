<?php
/*
CRC: MySQLKorisnikRepozitorijum
Odgovornosti: MySQL pristup tabeli korisnici (čitanje/izmena lozinke).
Saradnici: InterfejsKorisnikRepozitorijum, AuthServis.
*/

// Uključivanje interfejsa repozitorijuma korisnika
require_once __DIR__ . "/InterfejsKorisnikRepozitorijum.php";

// Repozitorijum za pristup korisnicima u MySQL bazi (mysqli)
class MySQLKorisnikRepozitorijum implements InterfejsKorisnikRepozitorijum {
    // Injekcija mysqli konekcije kroz konstruktor
    public function __construct(private mysqli $conn) {}

    // Dohvat korisnika po korisničkom imenu (SELECT sa prepared statementom)
    public function nadjiPoKorisnickomImenu(string $u): ?array {
        $st=$this->conn->prepare("SELECT id, korisnicko_ime, lozinka, uloga FROM korisnici WHERE korisnicko_ime=?");
        $st->bind_param("s",$u); $st->execute(); $rez=$st->get_result(); $row=$rez?->fetch_assoc(); $st->close(); return $row?:null;
    }

    // Ažuriranje lozinke korisnika na bcrypt hash (UPDATE sa prepared statementom)
    public function azurirajLozinkuHash(int $id, string $hash): bool {
        $st=$this->conn->prepare("UPDATE korisnici SET lozinka=? WHERE id=?"); $st->bind_param("si",$hash,$id);
        $ok=$st->execute(); $st->close(); return (bool)$ok;
    }
}
