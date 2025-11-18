/*
CRC: skripta.js
Klasa/fajl: skripta
Odgovornosti: rukuje ponašanjem na klijentskoj strani (dinamičko ažuriranje stranica, validacija, interakcija sa servisima)
Saradnici: pregled.php, servis_prijave.php
*/

// Keširanje referenci na ključne DOM elemente (forma, labela za ime fajla, zona za poruke)
const forma = document.getElementById('forma-prijava');
const imeFajla = document.getElementById('ime-fajla');
const potvrda = document.getElementById('potvrda');

// Dinamičko učitavanje opština iz JSON-a i popunjavanje <select id="mesto">
fetch('opstine.json')
  .then(response => response.json())
  .then(data => {
    const mestoSelect = document.getElementById('mesto');
    mestoSelect.innerHTML = '<option value="">-- Izaberi opštinu --</option>';
    data.forEach(opstina => {
      const opcija = document.createElement('option');
      opcija.value = opstina;
      opcija.textContent = opstina;
      mestoSelect.appendChild(opcija);
    });
  })
  .catch(error => {
    console.error('Greška pri učitavanju opština:', error);
    document.getElementById('mesto').innerHTML = '<option value="">Greška pri učitavanju</option>';
  });

// Ažuriranje prikaza naziva fajla pri izboru slike
document.getElementById('slika').addEventListener('change', function () {
  if (this.files.length > 0) {
    imeFajla.textContent = this.files[0].name;
  } else {
    imeFajla.textContent = 'Nijedna fotografija nije izabrana';
  }
});

// Validacija obaveznih polja i korisnička poruka pri slanju forme (bez server submit-a)
forma.addEventListener('submit', function (e) {
  e.preventDefault();

  const mesto = document.getElementById('mesto').value.trim();
  const adresa = document.getElementById('adresa').value.trim();
  const registracija = document.getElementById('registracija').value.trim();
  const opis = document.getElementById('opis').value.trim();

  if (mesto === '' || adresa === '' || registracija === '' || opis === '') {
    potvrda.style.display = 'block';
    potvrda.style.backgroundColor = '#f2dede';
    potvrda.style.color = '#a94442';
    potvrda.textContent = 'Molimo popunite sva obavezna polja.';
    return;
  }

  potvrda.style.display = 'block';
  potvrda.style.backgroundColor = '#dff0d8';
  potvrda.style.color = '#3c763d';
  potvrda.textContent = 'Prijava je uspešno poslata. Hvala vam!';

  forma.reset();
  imeFajla.textContent = 'Nijedna fotografija nije izabrana';
});
