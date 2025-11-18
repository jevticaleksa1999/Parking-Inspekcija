<?php
/*
CRC: pregled.php
Odgovornosti: Brz pregled svih prijava (za admin/inspektor).
Saradnici: ServisKontejner (PrijavaRepozitorijum), sesija.
*/

// Provera sesije i uloge (pristup imaju admin i inspektor)
session_start();
if (!isset($_SESSION['korisnik']) || !in_array($_SESSION['uloga'], ['admin','inspektor'])) { header("Location: prijava.php"); exit(); }

// Učitavanje servisnog kontejnera i dohvat svih prijava (bez paginacije)
require_once __DIR__ . "/servis_kontejner.php";
$rezultat=(new ServisKontejner())->prijavaRepozitorijum()->pretrazi([],['po_stranici'=>0])['podaci'];

// Pomoćna funkcija za HTML-escaping (sprečavanje XSS)
function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sr">
<head><meta charset="UTF-8"><title>Pregled prijava</title><link rel="stylesheet" href="stil.css"></head>
<body>
<header><h1>Pregled prijava</h1><div class="header-menu"><a href="pocetna.php">Početna</a><a href="odjava.php">Odjava</a></div></header>
<div class="kontejner">
<?php if(!empty($rezultat)): ?>
<table>
  <tr><th>ID</th><th>Korisnik</th><th>Opština</th><th>Adresa</th><th>Registracija</th><th>Opis</th><th>Slika</th><th>Datum</th></tr>
  <?php foreach($rezultat as $red): ?>
    <tr>
      <td><?= (int)$red['id'] ?></td>
      <td><?= (int)$red['korisnik_id'] ?></td>
      <td><?= h($red['mesto']) ?></td>
      <td><?= h($red['adresa']) ?></td>
      <td><?= h($red['registracija']) ?></td>
      <td><?= h($red['opis']) ?></td>
      <td><?= $red['slika'] ? '<img src="'.h($red['slika']).'" width="80">' : 'Nema slike' ?></td>
      <td><?= h($red['datum']) ?></td>
    </tr>
  <?php endforeach; ?>
</table>
<?php else: ?><p>Još nema prijava.</p><?php endif; ?>
</div>
</body>
</html>
