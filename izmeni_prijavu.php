<?php
/*
CRC: izmeni_prijavu.php
Odgovornosti: Učitavanje i izmena prijave (validacija + repo update).
Saradnici: ServisKontejner (PrijavaRepozitorijum), PoslovnaLogika, sesija.
*/

// Provera sesije i uloge (dozvoljeno adminu i inspektoru)
session_start();
if(!isset($_SESSION['korisnik']) || !in_array($_SESSION['uloga'],['admin','inspektor'])){ header("Location: prijava.php"); exit(); }

// Učitavanje servisnog kontejnera i poslovne logike
require_once __DIR__ . "/servis_kontejner.php";
require_once __DIR__ . "/PoslovnaLogika.php";

// Validacija i dohvat ID-a prijave iz GET-a
$id=(int)($_GET['id']??0); if($id<=0){ die("Pogrešan ID."); }

// Inicijalizacija repozitorijuma i dohvat postojeće prijave
$kontejner=new ServisKontejner(); $repo=$kontejner->prijavaRepozitorijum();
$prijava=$repo->nadjiPoId($id); if(!$prijava){ die("Prijava ne postoji."); }

// Učitavanje opština i inicijalizacija poruka o grešci/uspehu
$opstine=json_decode(file_get_contents("opstine.json"), true) ?? [];
$greska=""; $poruka="";

// Obrada POST zahteva: validacija polja, provera formata tablica i upis izmena
if($_SERVER["REQUEST_METHOD"]==="POST"){
    $mesto=trim($_POST['mesto']??''); $adresa=trim($_POST['adresa']??'');
    $registracija=trim($_POST['registracija']??''); $opis=trim($_POST['opis']??'');
    if($mesto===''||$adresa===''||$registracija===''||$opis===''){ $greska="Sva polja su obavezna."; }
    else {
        try{
            $logika=new PoslovnaLogika(); $logika->proveriFormatRegistracije($registracija);
            $ok=$repo->izmeni($id,['mesto'=>$mesto,'adresa'=>$adresa,'registracija'=>$registracija,'opis'=>$opis]);
            if($ok){ $poruka="Uspešno sačuvano."; $prijava=$repo->nadjiPoId($id); }
            else { $greska="Greška pri čuvanju."; }
        }catch(Exception $e){ $greska=$e->getMessage(); }
    }
}

// Pomoćna funkcija za HTML escaping
function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sr">
<head><meta charset="UTF-8"><title>Izmena prijave #<?= (int)$prijava['id'] ?></title><link rel="stylesheet" href="stil.css"></head>
<body>
<div class="kontejner">
  <h1>Izmena prijave #<?= (int)$prijava['id'] ?></h1>
  <?php if($greska): ?><div class="greska"><?= h($greska) ?></div><?php endif; ?>
  <?php if($poruka): ?><div class="uspeh"><?= h($poruka) ?></div><?php endif; ?>
  <form method="post">
    <label for="mesto">Opština</label>
    <select id="mesto" name="mesto" required>
      <option value="">-- Izaberite --</option>
      <?php foreach($opstine as $o): ?><option value="<?=h($o)?>" <?= ($prijava['mesto']===$o)?'selected':''; ?>><?=h($o)?></option><?php endforeach; ?>
    </select>

    <label for="adresa">Adresa</label>
    <input id="adresa" name="adresa" type="text" value="<?= h($prijava['adresa']) ?>" required>

    <label for="registracija">Registracija</label>
    <input id="registracija" name="registracija" type="text" value="<?= h($prijava['registracija']) ?>" required>

    <label for="opis">Opis</label>
    <textarea id="opis" name="opis" rows="4" required><?= h($prijava['opis']) ?></textarea>

    <button type="submit">Sačuvaj</button>
  </form>

  <?php if ($_SESSION['uloga']==='admin'): ?>
    <a class="vrati" href="admin.php">← Nazad na pregled</a>
  <?php else: ?>
    <a class="vrati" href="inspektor.php">← Nazad na pregled</a>
  <?php endif; ?>
</div>
</body>
</html>
