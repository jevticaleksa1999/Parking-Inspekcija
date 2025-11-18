<?php
/*
CRC: obrisi_prijavu.php
Odgovornosti: Brisanje prijave preko repo sloja + brisanje fajla slike.
Saradnici: ServisKontejner (PrijavaRepozitorijum), sesija.
*/

// Provera sesije i uloge (dozvoljeno samo adminu)
session_start();
if (!isset($_SESSION['korisnik']) || $_SESSION['uloga'] !== 'admin') { header("Location: prijava.php"); exit(); }

// Uključivanje servisnog kontejnera
require_once __DIR__ . "/servis_kontejner.php";

// Validacija i čitanje ID-a prijave iz GET parametra
$id=(int)($_GET['id']??0); if($id<=0){ die("Pogrešan ID."); }

// Inicijalizacija repozitorijuma i dohvat prijave (radi saznanja putanje slike)
$repo=(new ServisKontejner())->prijavaRepozitorijum();
$red=$repo->nadjiPoId($id); $slika=$red['slika'] ?? '';

// Brisanje prijave u bazi (stored procedura) 
$ok=$repo->obrisi($id);

// Ako je uspešno obrisano i postoji fajl slike → brisanje fizičke datoteke
if($ok && $slika){
    $fiz=__DIR__.'/'.$slika;
    if(is_file($fiz)){ @unlink($fiz); }
}

// Preusmerenje nazad na admin pregled
header("Location: admin.php"); exit();
