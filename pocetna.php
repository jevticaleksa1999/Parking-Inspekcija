<?php
/*
CRC: pocetna.php
Odgovornosti: Unos nove prijave – upload slike, validacije preko PoslovnaLogika, snimanje preko Repo.
Saradnici: ServisKontejner (PrijavaRepozitorijum), PoslovnaLogika, PrijavaDTO, sesija.
*/

// Provera sesije i preusmeravanje po ulozi (korisnik → ostaje; admin/inspektor → svoje stranice)
session_start();
if (!isset($_SESSION['korisnik']) || !isset($_SESSION['uloga'])) { header("Location: prijava.php"); exit(); }
if ($_SESSION['uloga'] === 'admin') { header("Location: admin.php"); exit(); }
if ($_SESSION['uloga'] === 'inspektor') { header("Location: inspektor.php"); exit(); }

// Učitavanje zavisnosti: konekcija ka bazi, servisni kontejner, DTO, poslovna logika
require_once __DIR__ . "/DBKonekcija.php";
require_once __DIR__ . "/servis_kontejner.php";
require_once __DIR__ . "/PrijavaDTO.php";
require_once __DIR__ . "/PoslovnaLogika.php";

// Fallback mehanizam za dobijanje mysqli konekcije (više mogućih metoda + direktno iz JSON konfiguracije)
if (!isset($conn) || !($conn instanceof mysqli)) {
    if (class_exists('DBKonekcija')) {
        if (method_exists('DBKonekcija','get')) { $conn = DBKonekcija::get(); }
        elseif (method_exists('DBKonekcija','konekcija')) { $conn = DBKonekcija::konekcija(); }
        elseif (method_exists('DBKonekcija','getConnection')) { $conn = DBKonekcija::getConnection(); }
    }
    if (!isset($conn) || !($conn instanceof mysqli)) {
        $cfg1 = __DIR__ . '/konfiguracija_baze.json';
        $cfg2 = __DIR__ . '/konfiguracije_baze.json';
        $file = file_exists($cfg1) ? $cfg1 : (file_exists($cfg2) ? $cfg2 : '');
        $p = $file ? (json_decode(@file_get_contents($file), true) ?: []) : [];
        $conn = new mysqli($p['server']??'localhost', $p['korisnik']??'root', $p['lozinka']??'', $p['baza']??'inspekcija');
        $conn->set_charset($p['kodiranje']??'utf8mb4');
    }
}

// Učitavanje opština i inicijalnih poruka; helper za HTML escaping
$opstine = json_decode(@file_get_contents("opstine.json"), true) ?? [];
$poruka=""; $greska="";
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Obrada POST zahteva: validacija unosa + upload fotografije
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mesto=trim($_POST['mesto']??''); $adresa=trim($_POST['adresa']??'');
    $registracija=strtoupper(trim($_POST['registracija']??'')); $opis=trim($_POST['opis']??''); $slika_putanja="";

    // Validacija obaveznih polja
    if($mesto===''||$adresa===''||$registracija===''||$opis===''){ $greska="Molimo popunite sva obavezna polja."; }

    // Upload fotografije (provera formata, kreiranje foldera, pomeranje fajla)
    if($greska===""){
        if(!isset($_FILES['slika']) || $_FILES['slika']['error']!==UPLOAD_ERR_OK){
            $greska="Morate izabrati fotografiju.";
        } else {
            $folder="slike_prijava/"; if(!is_dir($folder)){ @mkdir($folder,0777,true); }
            $ext=strtolower(pathinfo($_FILES['slika']['name'], PATHINFO_EXTENSION));
            if(!in_array($ext,['jpg','jpeg','png'],true)){ $greska="Dozvoljeni formati: JPG, JPEG ili PNG."; }
            else {
                $jedinstveno=time()."_".bin2hex(random_bytes(5)).".".$ext;
                $ciljni=$folder.$jedinstveno;
                if(!move_uploaded_file($_FILES['slika']['tmp_name'],$ciljni)){ $greska="Greška pri otpremanju fotografije."; }
                else { $slika_putanja=$ciljni; }
            }
        }
    }

    // Poslovna logika: validacija tablica, provera duplikata, određivanje prioriteta
    if($greska===""){
        $dto=new PrijavaDTO(); $dto->korisnik_id=(int)$_SESSION['korisnik_id']; $dto->mesto=$mesto;
        $dto->adresa=$adresa; $dto->registracija=$registracija; $dto->opis=$opis; $dto->slika=$slika_putanja;

        $logika=new PoslovnaLogika($conn);
        try {
            $logika->proveriFormatRegistracije($dto->registracija);
            $logika->proveriDuplikat($dto);
            $logika->odrediPrioritet($dto);
        } catch(Exception $e){ $greska=$e->getMessage(); }
    }

    // Upis u bazu preko repozitorijuma i priprema poruke o uspehu
    if($greska===""){
        $kontejner = new ServisKontejner($conn);
        $repo = $kontejner->prijavaRepozitorijum();
        $repo->dodaj([
            'korisnik_id'=>$dto->korisnik_id,'mesto'=>$dto->mesto,'adresa'=>$dto->adresa,
            'registracija'=>$dto->registracija,'opis'=>$dto->opis,'slika'=>$dto->slika,'prioritet'=>$dto->prioritet
        ]);
    
        $poruka="Prijava je uspešno zabeležena. Prioritet: ".h($dto->prioritet);
        $greska="";
        $mesto=$adresa=$registracija=$opis="";
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8"><title>Prijava nepropisnog parkiranja</title>
<link rel="stylesheet" href="stil.css">
</head>
<body>
<nav>
  <a href="moje_prijave.php">Moje prijave</a>
  <a href="odjava.php">Odjava</a>
</nav>
<div class="kontejner">
  <h1>Prijava nepropisnog parkiranja</h1>
  <?php if($poruka): ?><div class="uspeh-box"><?= h($poruka) ?></div><?php elseif($greska): ?><div class="greska-box"><?= h($greska) ?></div><?php endif; ?>
  <form method="post" action="pocetna.php" enctype="multipart/form-data" novalidate>
    <label for="mesto">Opština</label>
    <select id="mesto" name="mesto" required>
      <option value="">-- Izaberite opštinu --</option>
      <?php foreach($opstine as $o): ?>
        <option value="<?= h($o) ?>" <?= (isset($mesto)&&$mesto===$o)?'selected':''; ?>><?= h($o) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="adresa">Adresa</label>
    <input id="adresa" name="adresa" type="text" value="<?= isset($adresa)?h($adresa):'' ?>" required>

    <label for="registracija">Registarski broj</label>
    <input id="registracija" name="registracija" type="text" value="<?= isset($registracija)?h($registracija):'' ?>" required>

    <label for="opis">Opis problema</label>
    <textarea id="opis" name="opis" rows="4" required><?= isset($opis)?h($opis):'' ?></textarea>

    <label for="slika">Fotografija vozila</label>
    <div class="fajl-kutija">
      <label class="custom-file-upload" for="slika">Izaberi fotografiju</label>
      <input type="file" id="slika" name="slika" accept="image/*" required>
      <span id="ime-fajla" class="info">Fotografija nije izabrana</span>
    </div>
    <button type="submit">Pošalji prijavu</button>
  </form>
</div>
<img id="Logo" src="Logo.png" alt="Logo">
<script>
// Ažuriranje prikaza naziva fajla nakon odabira slike
  const inputFile=document.getElementById('slika'); const imeFajla=document.getElementById('ime-fajla');
  if(inputFile&&imeFajla){ inputFile.addEventListener('change',()=>{ imeFajla.textContent = inputFile.files.length>0 ? inputFile.files[0].name : 'Fotografija nije izabrana'; }); }
</script>
</body>
</html>
