<?php
/*
CRC: prijava.php
Odgovornosti: Login forma + poziv AuthServis-a; otvara sesiju i preusmerava po ulozi.
Saradnici: ServisKontejner (AuthServis), sesija.
*/

// Start sesije i inicijalna poruka o greški
session_start();
$greska = '';

// Obrada POST zahteva: validacija unosa i pokušaj autentifikacije preko AuthServis-a
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $korime = trim($_POST['korisnicko_ime'] ?? '');
    $lozinka = trim($_POST['lozinka'] ?? '');
    if ($korime==='' || $lozinka==='') {
        $greska = 'Popunite korisničko ime i lozinku.';
    } else {
        require_once __DIR__ . "/servis_kontejner.php";
        $auth = (new ServisKontejner())->authServis();
        $u = $auth->prijavi($korime, $lozinka);

        // Na uspeh: regeneracija sesije, čuvanje identiteta i preusmeravanje po ulozi
        if ($u) {
            session_regenerate_id(true);
            $_SESSION['korisnik'] = $u['korisnicko_ime'];
            $_SESSION['uloga'] = $u['uloga'];
            $_SESSION['korisnik_id'] = (int)$u['id'];
            switch ($u['uloga']) {
                case 'admin': header("Location: admin.php"); break;
                case 'inspektor': header("Location: inspektor.php"); break;
                default: header("Location: pocetna.php"); break;
            }
            exit();
        } else {
            // Na neuspeh: poruka o grešci
            $greska = 'Pogrešno korisničko ime ili lozinka.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8"><title>Prijava</title>
<link rel="stylesheet" href="stil.css">
</head>
<body>
<div class="login-box">
    <h2>Prijava</h2>
    <?php if($greska): ?><p class="error"><?= htmlspecialchars($greska,ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="prijava.php" novalidate>
        <input type="text" name="korisnicko_ime" placeholder="Korisničko ime">
        <input type="password" name="lozinka" placeholder="Lozinka">
        <button type="submit">Prijava</button>
    </form>
</div>
<img id="Logo" src="Logo.png" alt="Logo">
</body>
</html>
