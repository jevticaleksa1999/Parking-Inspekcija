<?php
/*
CRC: InterfejsKorisnikRepozitorijum
Odgovornosti: Ugovor za pristup tabeli korisnici (čitanje po korisničkom imenu, ažuriranje lozinke).
Saradnici: AuthServis.
*/
interface InterfejsKorisnikRepozitorijum {
    public function nadjiPoKorisnickomImenu(string $korisnicko_ime): ?array;
    public function azurirajLozinkuHash(int $id, string $hash): bool;
}
