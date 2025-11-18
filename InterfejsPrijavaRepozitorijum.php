<?php
/*
CRC: InterfejsPrijavaRepozitorijum
Odgovornosti: Ugovor za rad sa prijavama (CRUD + pretraga).
Saradnici: Implementacije repozitorijuma, DTO/Maper, Servisi/Controlleri.
*/
interface InterfejsPrijavaRepozitorijum {
    public function pretrazi(array $filteri = [], array $paginacija = []): array;
    public function nadjiPoId(int $id): ?array;
    public function dodaj(array $podaci): int;
    public function izmeni(int $id, array $podaci): bool;
    public function obrisi(int $id): bool;
}
