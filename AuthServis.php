<?php
/*
CRC: AuthServis
Odgovornosti: Prijava korisnika uz verifikaciju lozinke i migraciju na hash.
Saradnici: InterfejsKorisnikRepozitorijum, sesije.
*/

// Uključivanje interfejsa repozitorijuma korisnika (zavisnost)
require_once __DIR__ . "/InterfejsKorisnikRepozitorijum.php";

// Servis za autentifikaciju i migraciju lozinki na bcrypt
class AuthServis {
    // Injekcija repozitorijuma korisnika preko konstruktora
    public function __construct(private InterfejsKorisnikRepozitorijum $korRepo) {}

    // Prijava korisnika: verifikacija lozinke + eventualna migracija na bcrypt
    public function prijavi(string $korisnicko_ime, string $lozinka): ?array {
        // Dohvat korisnika po korisničkom imenu
        $u = $this->korRepo->nadjiPoKorisnickomImenu($korisnicko_ime);
        if(!$u) return null;

        // Preuzimanje sačuvane lozinke (hash ili plain-text)
        $hash = $u['lozinka'];
        $validno = false;

        // Provera da li je lozinka već u bcrypt formatu
        if (preg_match('/^\$2y\$/', $hash)) {
            // Verifikacija bcrypt hash-a
            $validno = password_verify($lozinka, $hash);
        } else {
            // Legacy slučaj: plain-text lozinka u bazi → poređenje i migracija na bcrypt
            if (hash_equals($hash, $lozinka)) {
                $validno = true;
                $novi = password_hash($lozinka, PASSWORD_BCRYPT);
                $this->korRepo->azurirajLozinkuHash((int)$u['id'], $novi);
            }
        }

        // Vraćanje korisnika na uspeh, inače null
        return $validno ? $u : null;
    }
}
