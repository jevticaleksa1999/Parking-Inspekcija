<?php
/*
CRC: moje_prijave.php
Odgovornosti: Tabelarni prikaz sopstvenih prijava (korisnik).
Saradnici: ServisKontejner (PrijavaRepozitorijum), sesija.
*/

// Provera sesije i uloge (pristup imaju samo korisnici sa ulogom 'korisnik')
session_start();
if(!isset($_SESSION['korisnik']) || $_SESSION['uloga']!=='korisnik'){ header("Location: prijava.php"); exit(); }

// Učitavanje servisnog kontejnera i inicijalizacija repozitorijuma prijava
require_once __DIR__ . "/servis_kontejner.php";
$repo=(new ServisKontejner())->prijavaRepozitorijum();

// Dohvatanje prijava prijavljenog korisnika (filter po korisnik_id)
$prijave=$repo->pretrazi(['korisnik_id'=>(int)$_SESSION['korisnik_id']])['podaci'];

// Pomoćna funkcija za HTML escaping (sprečavanje XSS)
function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8"><title>Moje prijave</title>
<link rel="stylesheet" href="stil.css">
</head>
<body>
<header>
  <nav><a href="pocetna.php">Početna</a><a href="odjava.php">Odjava</a></nav>
</header>

<h1 class="page-title">Moje prijave</h1>

<div class="moje-prijave-wrapper">
<?php if(!empty($prijave)): ?>
  <table>
    <thead><tr><th>ID</th><th>Mesto</th><th>Adresa</th><th>Registracija</th><th>Opis</th><th>Slika</th><th>Datum</th></tr></thead>
    <tbody>
      <?php foreach($prijave as $p): ?>
      <tr>
        <td><?= (int)$p['id'] ?></td>
        <td><?= h($p['mesto']) ?></td>
        <td><?= h($p['adresa']) ?></td>
        <td><?= h($p['registracija']) ?></td>
        <td><?= nl2br(h($p['opis'])) ?></td>
        <td><?php if(!empty($p['slika'])): ?><img class="thumb" src="<?=h($p['slika'])?>" alt="Slika"><?php else: ?>Nema slike<?php endif; ?></td>
        <td><?= h($p['datum']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p class="prazno">Još nema prijava.</p>
<?php endif; ?>
</div>

<img id="Logo" src="Logo.png" alt="Logo">
</body>
</html>
