<?php
/*
CRC: servis_prijave.php
Odgovornosti: JSON API za filtriranje/paginaciju prijava (GET parametri).
Saradnici: ServisKontejner (PrijavaRepozitorijum).
*/

// HTTP zaglavlje za JSON odgovor (UTF-8)
header('Content-Type: application/json; charset=utf-8');

// Učitavanje servisnog kontejnera (DI) i repozitorijuma
require_once __DIR__ . "/servis_kontejner.php";

// Čitanje i normalizacija GET parametara (filteri + paginacija)
$mesto=trim($_GET['mesto'] ?? ''); $registracija=trim($_GET['registracija'] ?? '');
$datum_od=trim($_GET['datum_od'] ?? ''); $datum_do=trim($_GET['datum_do'] ?? '');
$stranica=max(1,(int)($_GET['stranica'] ?? 1)); $po_stranici=min(100,max(1,(int)($_GET['po_stranici'] ?? 20)));

// Izvršavanje pretrage preko repozitorijuma sa prosleđenim filterima i paginacijom
$repo=(new ServisKontejner())->prijavaRepozitorijum();
$rez=$repo->pretrazi(['mesto'=>$mesto,'registracija'=>$registracija,'datum_od'=>$datum_od,'datum_do'=>$datum_do],['stranica'=>$stranica,'po_stranici'=>$po_stranici]);

// JSON odgovor: meta-informacije (strana, po_stranici, ukupno) + podaci
echo json_encode([
  'stranica'=>$stranica,'po_stranici'=>$po_stranici,'ukupno'=>$rez['ukupno'],
  'rezultata'=>count($rez['podaci']),'filteri'=>['mesto'=>$mesto,'registracija'=>$registracija,'datum_od'=>$datum_od,'datum_do'=>$datum_do],
  'podaci'=>$rez['podaci']
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
