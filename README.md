# Parking-Inspekcija
Illegal Parking Inspection – Web Application

This project is a modular, SOLID-oriented PHP web application designed for reporting and managing illegal parking cases. It includes role-based access, structured business logic, robust validation, and SQL stored procedures, views, and transactions to ensure data integrity and reliability.

⭐ Features
User Roles

User: submits parking violation reports

Inspector: reviews reports, filters data, views images in fullscreen

Admin: manages reports (edit, delete), reviews logs

Core Functionalities

Submit reports with:

Location

License plate number

Vehicle type

Description

Photos

Validation for completeness, correctness, and duplicate prevention

Automatic priority assignment via business logic

Filtering and table-based browsing

Editing and deleting records (admin)

Photo viewer for inspectors

Printing:

Full report lists

Parameter-based filtering print view

🧠 Architecture & Code Design

Modular PHP architecture (Controller → Service → Repository → Database)

SOLID principles applied

DTO classes for data transfer

Mappers for converting DB rows into objects

Service container for dependency injection

Session security (role checks on every page)

External JSON parameters for business rules

Separated CSS & JS files

Clean Code style (CRC comments, naming conventions)

🗄️ Database Structure
Tables

korisnici — user accounts

prijave — submitted reports

log_akcija — action logs

Stored Procedures

dodaj_prijavu

izmeni_prijavu

obrisi_prijavu

View

pregled_prijava

Transactions

Included for multi-step operations (BEGIN, COMMIT, ROLLBACK)

🔧 Technologies Used

PHP (modular OOP)

MySQL (procedures, views, transactions)

HTML / CSS / JavaScript

JSON configuration files

XAMPP / Apache

🚀 How to Run

Clone or download the project

Import the SQL file with tables, stored procedures, and views

Configure db_konekcija.php with your credentials

Place the project inside /htdocs

Open the app in browser:
http://localhost/Inspekcijski_nadzor/

📁 Key Files & Structure

pocetna.php — homepage

prijava.php — report submission form

admin.php — admin dashboard

inspektor.php — inspector dashboard

servis_prijave.php — business logic endpoint

PrijavaDTO.php — data transfer object

PoslovnaLogika.php — validation & rules

opstine.json — dynamic dropdown data

stil.css — UI styling

stil_stampanja.css — print styling

skripta.js — client-side functionality

📌 Purpose

This project demonstrates how to build a complete web system with real-world requirements, including authentication, validations, modular code architecture, SOLID principles, SQL automation, and structured business logic.
