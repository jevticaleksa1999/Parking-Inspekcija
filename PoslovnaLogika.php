<?php
/*
CRC: PoslovnaLogika
Odgovornosti: Validacije (format, duplikat), određivanje prioriteta po JSON pravilima.
Saradnici: OsnovnaLogika, PrijavaDTO, parametri_logike.json, DBKonekcija.
*/

// Učitavanje osnovne logike, konekcije i DTO klase
require_once __DIR__ . "/OsnovnaLogika.php";
require_once __DIR__ . "/DBKonekcija.php";
require_once __DIR__ . "/PrijavaDTO.php";

// Klasa poslovnih pravila: validacije i određivanje prioriteta prijave
class PoslovnaLogika extends OsnovnaLogika {
    // Držanje mysqli konekcije i parametara iz JSON-a u memoriji
    private mysqli $konekcija; private array $parametri;

    // Konstruktorska inicijalizacija: konekcija (ili DBKonekcija::get) + učitavanje parametara iz JSON fajla
    public function __construct(?mysqli $konekcija=null, string $putanja='parametri_logike.json'){
        $this->konekcija = $konekcija ?? DBKonekcija::get();
        $sadrzaj = @file_get_contents($putanja);
        $this->parametri = $sadrzaj ? (json_decode($sadrzaj, true) ?: []) : [];
    }

    // Validacija formata registarskih tablica (npr. BG-123-AB, sa srpskim slovima)
    public function proveriFormatRegistracije(string $registracija): void {
        if (!preg_match("/^[A-ZČĆŠĐŽ]{2}-\d{3,4}-[A-ZČĆŠĐŽ]{2}$/u", $registracija)) {
            throw new Exception("Neispravan format registracije!");
        }
    }

    // Provera duplikata u poslednjih N sati (čitanje prozora iz parametri_logike.json)
    public function proveriDuplikat(PrijavaDTO $p): void {
        $sati = $this->parametri['prozor_duplikata_sati'] ?? 24;
        $sql="SELECT COUNT(*) FROM prijave WHERE registracija=? AND mesto=? AND datum>=NOW()-INTERVAL ? HOUR";
        $st=$this->konekcija->prepare($sql); $st->bind_param("ssi",$p->registracija,$p->mesto,$sati);
        $st->execute(); $st->bind_result($n); $st->fetch(); $st->close();
        if (($n??0)>0) throw new Exception("Duplikat: ova registracija je već prijavljena u poslednjih $sati sati!");
    }

    // Određivanje prioriteta na osnovu kritičnih opština i ključnih reči iz opisa (parametri iz JSON-a)
    public function odrediPrioritet(PrijavaDTO $p): void {
        $krit=$this->parametri['kriticne_opstine_prioritet']??[]; $klj=$this->parametri['kljucne_reci_visok_prioritet']??[];
        if (in_array($p->mesto,$krit,true)) { $p->prioritet='visok'; return; }
        foreach($klj as $rec){ if(stripos($p->opis,$rec)!==false){ $p->prioritet='visok'; return; } }
        $p->prioritet='normalan';
    }
}
