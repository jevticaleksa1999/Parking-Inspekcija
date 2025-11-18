<?php
/*
CRC: korisnik.php
Odgovornosti: Pregled sopstvenih prijava za ulogu 'korisnik'.
Saradnici: ServisKontejner (PrijavaRepozitorijum), sesija.
*/

// Provera sesije i uloge (samo korisnik sa postavljenim korisnik_id ima pristup)
session_start();
if (!isset($_SESSION['korisnik']) || $_SESSION['uloga'] !== 'korisnik' || !isset($_SESSION['korisnik_id'])) {
    header("Location: prijava.php"); exit();
}

// Učitavanje servisnog kontejnera i inicijalizacija repozitorijuma prijava
require_once __DIR__ . "/servis_kontejner.php";
$repo=(new ServisKontejner())->prijavaRepozitorijum();

// Pretraga prijava filtriranih po trenutno prijavljenom korisniku
$rezultat=$repo->pretrazi(['korisnik_id'=>(int)$_SESSION['korisnik_id']])['podaci'];

// Pomoćna funkcija za HTML escaping (sprečavanje XSS)
function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8"><title>Korisnik – Moje prijave</title>
<link rel="stylesheet" href="stil.css">
</head>
<body>
<img id="Logo" src="Logo.png" alt="Logo">
<header>
  <h1 class="welcome">Dobrodošli, <?= h($_SESSION['korisnik']) ?> (Korisnik)</h1>
  <nav><a href="pocetna.php">Početna</a><a href="odjava.php">Odjava</a></nav>
</header>
<main>
  <div class="kontejner">
    <h2><?= !empty($rezultat) ? "Moje prijave" : "Još nema prijava" ?></h2>
    <?php if(!empty($rezultat)): ?>
      <table>
        <thead><tr><th>ID</th><th>Mesto</th><th>Adresa</th><th>Registracija</th><th>Opis</th><th>Slika</th><th>Datum</th></tr></thead>
        <tbody>
          <?php foreach($rezultat as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= h($r['mesto']) ?></td>
            <td><?= h($r['adresa']) ?></td>
            <td><?= h($r['registracija']) ?></td>
            <td><?= nl2br(h($r['opis'])) ?></td>
            <td><?php if(!empty($r['slika'])): ?><img class="thumb" src="<?=h($r['slika'])?>" alt="Slika"><?php else: ?>—<?php endif; ?></td>
            <td><?= h($r['datum']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
