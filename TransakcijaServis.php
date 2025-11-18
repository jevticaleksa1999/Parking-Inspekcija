<?php
/*
CRC: TransakcijaServis
Odgovornosti: Primer poslovne operacije sa transakcijom (2 UPDATE + 1 INSERT u log_akcija).
Saradnici: DBKonekcija, tabela prijave, log_akcija.
*/

// Servis koji obavlja višekoračne izmene u jednoj DB transakciji
class TransakcijaServis {
    // Injekcija mysqli konekcije kroz konstruktor
    public function __construct(private mysqli $conn) {}

    // Masovna izmena opisa za dve prijave + upis loga, sve u jednoj transakciji
    public function masovnaIzmenaOpisa(int $id1, string $opis1, int $id2, string $opis2, int $korisnik_id): void {
        // Početak transakcije
        $this->conn->begin_transaction();
        try {
            // Prvi UPDATE opisa prijave
            $s1=$this->conn->prepare("UPDATE prijave SET opis=? WHERE id=?");
            $s1->bind_param("si",$opis1,$id1); if(!$s1->execute()) throw new Exception($s1->error); $s1->close();

            // Drugi UPDATE opisa prijave
            $s2=$this->conn->prepare("UPDATE prijave SET opis=? WHERE id=?");
            $s2->bind_param("si",$opis2,$id2); if(!$s2->execute()) throw new Exception($s2->error); $s2->close();

            // INSERT u log_akcija sa opisom izvršene masovne izmene
            $log=$this->conn->prepare("INSERT INTO log_akcija (korisnik_id, opis_akcije) VALUES (?,?)");
            $opisLog="Masovna izmena opisa za prijave #$id1 i #$id2";
            $log->bind_param("is",$korisnik_id,$opisLog); if(!$log->execute()) throw new Exception($log->error); $log->close();

            // Potvrda transakcije ako je sve prošlo bez greške
            $this->conn->commit();
        } catch(Throwable $e) {
            // Poništavanje svih promena u slučaju greške i prosleđivanje izuzetka
            $this->conn->rollback();
            throw $e;
        }
    }
}
