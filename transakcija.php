<?php
/*
CRC: transakcija.php
Odgovornosti: Forma za masovnu izmenu opisa uz transakciju (2 UPDATE + 1 LOG).
Saradnici: ServisKontejner (TransakcijaServis), sesija.
*/

// Provera sesije i uloge (pristup dozvoljen samo adminu)
session_start();
if (!isset($_SESSION['korisnik']) || $_SESSION['uloga'] !== 'admin') { header("Location: prijava.php"); exit(); }

// Učitavanje servisnog kontejnera za pristup TransakcijaServis-u
require_once __DIR__ . "/servis_kontejner.php";

// Inicijalne poruke za prikaz rezultata akcije
$greska=""; $poruka="";

// Obrada POST zahteva: validacija ulaza i poziv transakcione izmene
if($_SERVER["REQUEST_METHOD"]==="POST"){
    $id1=(int)($_POST['id_prijave_1']??0); $id2=(int)($_POST['id_prijave_2']??0);
    $opis1=trim($_POST['opis_1']??''); $opis2=trim($_POST['opis_2']??'');
    if($id1<=0||$id2<=0||$opis1===''||$opis2===''){ $greska="Sva polja su obavezna."; }
    else {
        try{
            (new ServisKontejner())->transakcijaServis()->masovnaIzmenaOpisa($id1,$opis1,$id2,$opis2,(int)($_SESSION['korisnik_id']??0));
            $poruka="Uspešno sačuvano (transakcija izvršena).";
        }catch(Throwable $e){ $greska="Transakcija poništena: ".$e->getMessage(); }
    }
}

// Pomoćna funkcija za bezbedan HTML izlaz (escaping)
function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8"><title>Transakcija</title>
<link rel="stylesheet" href="stil.css">
</head>
<body>
<header>
  <h1 class="welcome">Grupna izmena</h1>
  <nav><a href="admin.php">Nazad</a><a href="odjava.php">Odjava</a></nav>
</header>
<main>
  <div class="kontejner">
    <h2>Ažuriraj 2 prijave u jednoj transakciji</h2>
    <?php if($greska): ?><div class="greska"><?=h($greska)?></div><?php endif; ?>
    <?php if($poruka): ?><div class="uspeh"><?=h($poruka)?></div><?php endif; ?>
    <form method="post">
      <label for="id_prijave_1">ID prijave #1</label><input type="number" id="id_prijave_1" name="id_prijave_1" min="1" required>
      <label for="opis_1">Novi opis za prijavu #1</label><textarea id="opis_1" name="opis_1" rows="3" required></textarea>
      <label for="id_prijave_2">ID prijave #2</label><input type="number" id="id_prijave_2" name="id_prijave_2" min="1" required>
      <label for="opis_2">Novi opis za prijavu #2</label><textarea id="opis_2" name="opis_2" rows="3" required></textarea>
      <button type="submit">Sačuvaj obe izmene</button>
    </form>
  </div>
</main>
<img id="Logo" src="Logo.png" alt="Logo">
</body>
</html>
