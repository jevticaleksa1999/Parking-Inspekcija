<?php
/*
CRC: odjava.php
Odgovornosti: Uništava sesiju i vraća korisnika na prijava.php.
Saradnici: Sve stranice koje koriste sesiju.
*/

session_start(); session_unset(); session_destroy(); header("Location: prijava.php"); exit();
