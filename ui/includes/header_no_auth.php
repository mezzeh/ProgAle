<?php
session_start();

// Determina il percorso base dell'applicazione
$base_path = '/ProgAle'; // Modifica questo percorso in base alla tua configurazione
$css_path = $base_path . '/ui/css/style.css';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Gestione Piani di Studio</title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema Gestione Piani di Studio</h1>
            <!-- Menu semplificato per pagine che non richiedono autenticazione -->
            <nav>
                <ul class="main-menu">
                    <li><a href="<?php echo $base_path; ?>/pages/index.php">Home</a></li>
                    <li><a href="<?php echo $base_path; ?>/pages/login.php">Accedi</a></li>
                    <li><a href="<?php echo $base_path; ?>/pages/register.php">Registrati</a></li>
                </ul>
            </nav>
        </header>
        <main>