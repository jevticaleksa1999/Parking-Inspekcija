<?php
/*
CRC: stampaj.php
Odgovornosti: Parametarska štampa spiska prijava (filteri → repo → tabela).
Saradnici: ServisKontejner (PrijavaRepozitorijum), sesija, stil_stampanja.css.
*/

// Provera sesije i uloge (pristup dozvoljen adminu i inspektoru)
session_start();
if(!isset($_SESSION['korisnik']) || !in_array($_SESSION['uloga'],['admin','inspektor'])){ header("Location: prijava.php"); exit(); }

// Uključivanje servisnog kontejnera (DI) i pomoćne funkcije za HTML escaping
require_once __DIR__ . "/servis_kontejner.php";
function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }

// Čitanje filter parametara iz GET zahteva (opština, registracija, period)
$mesto=trim($_GET['mesto']??''); $registracija=trim($_GET['registracija']??'');
$datum_od=trim($_GET['datum_od']??''); $datum_do=trim($_GET['datum_do']??'');

// Pretraga prijava preko repozitorijuma na osnovu filtera (bez paginacije)
$repo=(new ServisKontejner())->prijavaRepozitorijum();
$rezultat=$repo->pretrazi(['mesto'=>$mesto,'registracija'=>$registracija,'datum_od'=>$datum_od,'datum_do'=>$datum_do],['po_stranici'=>0])['podaci'];
?>
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8"><title>Parametarska štampa prijava</title>
<link rel="stylesheet" href="stampa.css">
</head>
<body>
<div class="kontrole only-screen">
  <a class="btn svetlo" href="<?= $_SESSION['uloga']==='admin'?'admin.php':'inspektor.php' ?>">Nazad</a>
  <button class="btn" onclick="window.print()">Štampaj</button>
</div>

<h1>Spisak prijava</h1>
<div class="meta">
  <?php
    $meta=[];
    if($mesto!=='') $meta[]="Opština: ".h($mesto);
    if($registracija!=='') $meta[]="Registracija sadrži: ".h($registracija);
    if($datum_od!=='') $meta[]="Datum od: ".h($datum_od);
    if($datum_do!=='') $meta[]="Datum do: ".h($datum_do);
    echo $meta ? implode(" &nbsp;|&nbsp; ", $meta) : "Bez filtera";
  ?>
</div>

<?php if(!empty($rezultat)): ?>
<table class="tabela">
  <thead><tr>
    <th>ID</th><th>Korisnik ID</th><th>Opština</th><th>Adresa</th><th>Registracija</th>
    <th>Opis</th><th>Prioritet</th><th>Slika</th><th>Datum</th>
  </tr></thead>
  <tbody>
  <?php foreach($rezultat as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td><td><?= (int)$r['korisnik_id'] ?></td>
      <td><?= h($r['mesto']) ?></td><td><?= h($r['adresa']) ?></td>
      <td><?= h($r['registracija']) ?></td><td><?= nl2br(h($r['opis'])) ?></td>
      <td><?= h($r['prioritet'] ?? 'normalan') ?></td>
      <td><?php if(!empty($r['slika'])): ?><img class="thumb" src="<?= h($r['slika']) ?>" alt="slika"><?php endif; ?></td>
      <td><?= h($r['datum']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?><p style="text-align:center;">Nema rezultata za izabrane filtere.</p><?php endif; ?>
</body>
</html>
