<?php
/*
CRC: PrijavaDTO
Odgovornosti: Prenos podataka o prijavi između slojeva.
Saradnici: MaperPrijava, PoslovnaLogika, Repozitorijumi.
*/

// DTO klasa za prenos podataka o prijavi (enkapsulira sve atribute prijave)
class PrijavaDTO {
    public $id,$korisnik_id,$mesto,$adresa,$registracija,$opis,$slika,$prioritet,$datum;

    // Konstruktor: inicijalizacija svih polja prijave
    public function __construct($id=0,$korisnik_id=0,$mesto='',$adresa='',$registracija='',$opis='',$slika='',$prioritet='normalan',$datum=''){
        $this->id=(int)$id; $this->korisnik_id=(int)$korisnik_id; $this->mesto=(string)$mesto; $this->adresa=(string)$adresa;
        $this->registracija=(string)$registracija; $this->opis=(string)$opis; $this->slika=(string)$slika;
        $this->prioritet=$prioritet?:'normalan'; $this->datum=(string)$datum;
    }

    // Konverzija DTO objekta u asocijativni niz (za upis u bazu ili dalje korišćenje)
    public function uNiz(): array {
        return ['id'=>$this->id,'korisnik_id'=>$this->korisnik_id,'mesto'=>$this->mesto,'adresa'=>$this->adresa,
                'registracija'=>$this->registracija,'opis'=>$this->opis,'slika'=>$this->slika,
                'prioritet'=>$this->prioritet,'datum'=>$this->datum];
    }
}
