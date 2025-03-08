<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Gestione Piani di Studio</title>
    <link rel="stylesheet" href="../ui/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema Gestione Piani di Studio</h1>
            <!-- Menu semplificato per pagine che non richiedono autenticazione -->
            <nav>
                <ul class="main-menu">
                    <li><a href="../pages/index.php">Home</a></li>
                    <li><a href="login.php">Accedi</a></li>
                    <li><a href="register.php">Registrati</a></li>
                </ul>
            </nav>
        </header>
        <main>