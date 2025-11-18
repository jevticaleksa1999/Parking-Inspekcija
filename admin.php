<?php
/*
CRC: admin.php
Odgovornosti: Pregled/filtriranje svih prijava + linkovi ka izmeni/brisanje/štampa.
Saradnici: ServisKontejner (PrijavaRepozitorijum), sesija.
*/

// Provera sesije i uloge (dozvoljen pristup samo admin korisniku)
session_start();
if (!isset($_SESSION['korisnik']) || $_SESSION['uloga'] !== 'admin') { header("Location: prijava.php"); exit(); }

// Učitavanje servisnog kontejnera i zavisnosti
require_once __DIR__ . "/servis_kontejner.php";

// Učitavanje liste opština iz JSON fajla 
$opstine = json_decode(file_get_contents("opstine.json"), true) ?? [];

// Čitanje ulaznih GET parametara (filteri pretrage)
$mesto=trim($_GET['mesto']??''); $registracija=trim($_GET['registracija']??'');
$datum_od=trim($_GET['datum_od']??''); $datum_do=trim($_GET['datum_do']??'');

// Inicijalizacija repozitorijuma i izvršavanje pretrage sa zadatim filterima
$repo=(new ServisKontejner())->prijavaRepozitorijum();
$filteri=['mesto'=>$mesto,'registracija'=>$registracija,'datum_od'=>$datum_od,'datum_do'=>$datum_do];
$rezultat=$repo->pretrazi($filteri)['podaci'];

// Pomoćna funkcija za bezbedan HTML izlaz (escaping)
function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8"><title>Admin – Pregled i upravljanje prijavama</title>
<link rel="stylesheet" href="stil.css">
<link rel="stylesheet" href="stampa.css" media="print">

</head>
<body>
<header>
  <h1 class="welcome">Dobrodošli, <?= h($_SESSION['korisnik']) ?> (Admin)</h1>
  <nav>
    <a href="transakcija.php">Grupna izmena</a>
    <a href="odjava.php">Odjava</a>
  </nav>
</header>
<main>
  <div class="kontejner">
    <h2>Pregled prijava</h2>
    <form method="get" class="filtri">
      <div>
        <label for="mesto">Opština</label>
        <select id="mesto" name="mesto">
          <option value="">Sve</option>
          <?php // Popunjavanje opcija opština u padajućoj listi iz $opstine ?>
          <?php foreach($opstine as $o): ?>
            <option value="<?= h($o) ?>" <?= $mesto===$o?'selected':''; ?>><?= h($o) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="registracija">Registracija</label>
        <input id="registracija" name="registracija" value="<?= h($registracija) ?>" placeholder="npr. BG-123-AB">
      </div>
      <div><label for="datum_od">Datum od</label><input type="date" id="datum_od" name="datum_od" value="<?= h($datum_od) ?>"></div>
      <div><label for="datum_do">Datum do</label><input type="date" id="datum_do" name="datum_do" value="<?= h($datum_do) ?>"></div>
      <div class="dugmici">
        <button class="dugme" type="submit">Primeni filter</button>
        <a class="dugme sivo" href="admin.php">Poništi</a>
        <a class="dugme zeleno" href="stampaj.php?mesto=<?=urlencode($mesto)?>&registracija=<?=urlencode($registracija)?>&datum_od=<?=urlencode($datum_od)?>&datum_do=<?=urlencode($datum_do)?>">Štampaj</a>
      </div>
    </form>

    <?php // Tabelarni prikaz rezultata pretrage (ako postoje zapisi) ?>
    <?php if(!empty($rezultat)): ?>
      <table>
        <thead><tr>
          <th>ID</th><th>Korisnik ID</th><th>Opština</th><th>Adresa</th><th>Registracija</th>
          <th>Opis</th><th>Prioritet</th><th>Slika</th><th>Datum</th><th>Akcije</th>
        </tr></thead>
        <tbody>
          <?php // Iteracija kroz rezultate i iscrtavanje redova sa akcijama (izmena/brisanje) ?>
          <?php foreach($rezultat as $r): $pr=$r['prioritet']??'normalan'; ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= (int)$r['korisnik_id'] ?></td>
              <td><?= h($r['mesto']) ?></td>
              <td><?= h($r['adresa']) ?></td>
              <td><?= h($r['registracija']) ?></td>
              <td><?= nl2br(h($r['opis'])) ?></td>
              <td><span class="badge <?= $pr==='visok'?'visok':'normalan' ?>"><?= h($pr) ?></span></td>
              <td><?php if(!empty($r['slika'])): ?><img class="thumb" src="<?=h($r['slika'])?>" alt="Slika"><?php else: ?>—<?php endif; ?></td>
              <td><?= h($r['datum']) ?></td>
              <td class="akcije">
                <a href="izmeni_prijavu.php?id=<?= (int)$r['id'] ?>">Izmeni</a>
                <a href="obrisi_prijavu.php?id=<?= (int)$r['id'] ?>" onclick="return confirm('Obrisati prijavu #<?= (int)$r['id'] ?>?');">Obriši</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?><p style="text-align:center;">Nema prijava za zadate filtere.</p><?php endif; ?>
  </div>
  <img id="Logo" src="Logo.png" alt="Logo">
</main>
</body>
</html>
