<?php
/*
CRC: MaperPrijava
Odgovornosti: Pretvara DB red ↔ PrijavaDTO.
Saradnici: PrijavaDTO.
*/

// Učitavanje DTO klase za prijavu
require_once __DIR__ . "/PrijavaDTO.php";

// Klasa zadužena za mapiranje između baze i DTO objekta
class MaperPrijava {
    // Kreira PrijavaDTO objekat iz asocijativnog niza (jedan red iz baze)
    public static function izNiza(array $red): PrijavaDTO {
        return new PrijavaDTO(
            $red['id'] ?? 0,$red['korisnik_id'] ?? 0,$red['mesto'] ?? '',$red['adresa'] ?? '',
            $red['registracija'] ?? '',$red['opis'] ?? '',$red['slika'] ?? '',
            $red['prioritet'] ?? 'normalan',$red['datum'] ?? ''
        );
    }

    // Vraća asocijativni niz iz DTO objekta (za upis u bazu ili dalje korišćenje)
    public static function nizIzDTO(PrijavaDTO $dto): array { return $dto->uNiz(); }
}
