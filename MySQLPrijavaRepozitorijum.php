<?php
/*
CRC: MySQLPrijavaRepozitorijum
Odgovornosti: MySQL (mysqli) implementacija InterfejsPrijavaRepozitorijum (CRUD + pretraga).
Napomena: koristi stored procedure za dodaj/izmeni/obrisi; SELECT za pretragu/čitanje.
Saradnici: InterfejsPrijavaRepozitorijum, MaperPrijava, PrijavaDTO, mysqli.
*/

// Uključivanje interfejsa i pomoćnih klasa (maper, DTO)
require_once __DIR__ . "/InterfejsPrijavaRepozitorijum.php";
require_once __DIR__ . "/MaperPrijava.php";
require_once __DIR__ . "/PrijavaDTO.php";

// Repozitorijum prijava: implementacija nad MySQL/mysqli
class MySQLPrijavaRepozitorijum implements InterfejsPrijavaRepozitorijum
{
    // Injekcija mysqli konekcije
    public function __construct(private mysqli $conn) {}

    // Pomoćna funkcija: bind_param sa dinamičkim brojem argumenata (referencama)
    private static function bindParams(mysqli_stmt $stmt, string $types, array $values): void
    {
        $refs = [];
        foreach ($values as $k => $v) {
            $refs[$k] = &$values[$k];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    // Pretraga sa opcionim filterima i paginacijom (COUNT + SELECT)
    public function pretrazi(array $filteri = [], array $paginacija = []): array
    {
        $mesto        = trim($filteri['mesto'] ?? '');
        $registracija = trim($filteri['registracija'] ?? '');
        $datum_od     = trim($filteri['datum_od'] ?? '');
        $datum_do     = trim($filteri['datum_do'] ?? '');
        $korisnik_id  = (int)($filteri['korisnik_id'] ?? 0);

        $uslovi = [];
        $params = [];
        $tipovi = '';

        if ($mesto !== '') {
            $uslovi[] = "mesto = ?";
            $params[] = $mesto;       $tipovi .= 's';
        }
        if ($registracija !== '') {
            $uslovi[] = "registracija LIKE CONCAT('%', ?, '%')";
            $params[] = $registracija; $tipovi .= 's';
        }
        if ($datum_od !== '') {
            $uslovi[] = "DATE(datum) >= ?";
            $params[] = $datum_od;     $tipovi .= 's';
        }
        if ($datum_do !== '') {
            $uslovi[] = "DATE(datum) <= ?";
            $params[] = $datum_do;     $tipovi .= 's';
        }
        if ($korisnik_id > 0) {
            $uslovi[] = "korisnik_id = ?";
            $params[] = $korisnik_id;  $tipovi .= 'i';
        }

        $osnovniSql = "FROM prijave";
        if ($uslovi) {
            $osnovniSql .= " WHERE " . implode(" AND ", $uslovi);
        }

        // 1) COUNT ukupnog broja zapisa za zadate uslove
        $sqlBroj = "SELECT COUNT(*) AS ukupno " . $osnovniSql;
        $stmtBroj = $this->conn->prepare($sqlBroj);
        if ($tipovi !== '') {
            self::bindParams($stmtBroj, $tipovi, $params);
        }
        $stmtBroj->execute();
        $rezBroj = $stmtBroj->get_result();
        $ukupno  = ($rezBroj && $r = $rezBroj->fetch_assoc()) ? (int)$r['ukupno'] : 0;
        $stmtBroj->close();

        // 2) SELECT sa paginacijom (LIMIT/OFFSET) i sortiranjem po datumu
        $limit  = isset($paginacija['po_stranici']) ? max(0, (int)$paginacija['po_stranici']) : 0;
        $strana = isset($paginacija['stranica']) ? max(1, (int)$paginacija['stranica']) : 1;
        $offset = $limit > 0 ? ($strana - 1) * $limit : 0;

        $sql = "SELECT id, korisnik_id, mesto, adresa, registracija, opis, slika, prioritet, datum "
             . $osnovniSql
             . " ORDER BY datum DESC";

        $stmt = null;
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);

            // Dodavanje limit/offset parametara u bind skup
            $types = $tipovi . 'ii';
            $vals  = $params;
            $limitVar = $limit;   // lokalne promenljive da bi se referencirale
            $offsetVar = $offset;
            $vals[] = $limitVar;
            $vals[] = $offsetVar;

            if ($types !== '') {
                self::bindParams($stmt, $types, $vals);
            }
        } else {
            $stmt = $this->conn->prepare($sql);
            if ($tipovi !== '') {
                self::bindParams($stmt, $tipovi, $params);
            }
        }

        $stmt->execute();
        $rez = $stmt->get_result();

        $podaci = [];
        while ($rez && $row = $rez->fetch_assoc()) {
            $podaci[] = $row;
        }
        $stmt->close();

        return ['ukupno' => $ukupno, 'podaci' => $podaci];
    }

    // Dohvat pojedinačne prijave po ID-u (SELECT)
    public function nadjiPoId(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT id, korisnik_id, mesto, adresa, registracija, opis, slika, prioritet, datum FROM prijave WHERE id = ?");
        self::bindParams($stmt, 'i', [$id]);
        $stmt->execute();
        $rez = $stmt->get_result();
        $row = $rez ? $rez->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    // Dodavanje nove prijave (stored procedura: dodaj_prijavu)
    public function dodaj(array $d): int
    {
        $st = $this->conn->prepare("CALL dodaj_prijavu(?, ?, ?, ?, ?, ?, ?)");
        self::bindParams($st, 'issssss', [
            $d['korisnik_id'],
            $d['mesto'],
            $d['adresa'],
            $d['registracija'],
            $d['opis'],
            $d['slika'],
            $d['prioritet']
        ]);
        $ok = $st->execute();
        $st->close();

        // Čišćenje eventualnih dodatnih rezultata nakon CALL
        while ($this->conn->more_results() && $this->conn->next_result()) { /* flush */ }

        // Vraćanje ID-a unete prijave (ako je dostupan)
        return $ok ? ($this->conn->insert_id ?: 0) : 0;
    }

    // Izmena postojeće prijave (stored procedura: izmeni_prijavu)
    public function izmeni(int $id, array $d): bool
    {
        $m = $d['mesto'] ?? '';
        $a = $d['adresa'] ?? '';
        $r = $d['registracija'] ?? '';
        $o = $d['opis'] ?? '';

        $st = $this->conn->prepare("CALL izmeni_prijavu(?, ?, ?, ?, ?)");
        self::bindParams($st, 'issss', [$id, $m, $a, $r, $o]);
        $ok = $st->execute();
        $st->close();

        // Čišćenje dodatnih rezultata nakon CALL
        while ($this->conn->more_results() && $this->conn->next_result()) { /* flush */ }
        return (bool)$ok;
    }

    // Brisanje prijave (stored procedura: obrisi_prijavu)
    public function obrisi(int $id): bool
    {
        $st = $this->conn->prepare("CALL obrisi_prijavu(?)");
        self::bindParams($st, 'i', [$id]);
        $ok = $st->execute();
        $st->close();

        // Čišćenje dodatnih rezultata nakon CALL
        while ($this->conn->more_results() && $this->conn->next_result()) { /* flush */ }
        return (bool)$ok;
    }
}
