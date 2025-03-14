<?php
session_start();

// Pagine accessibili a tutti (anche senza login)
$public_pages = ['index.php', 'login.php', 'register.php', 'view_piano.php', 'view_esame.php', 'view_argomento.php', 'view_esercizio.php', 'view_sottoargomento.php'];

// Verifica se la pagina corrente è riservata agli utenti autenticati
$current_page = basename($_SERVER['PHP_SELF']);
$requires_auth = !in_array($current_page, $public_pages);

// Se la pagina richiede autenticazione e l'utente non è loggato, reindirizza al login
if($requires_auth && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Determina il percorso base corretto in base al nome del file
$base_path = "";
if (strpos($current_page, 'view_') === 0) {
    // Pagine view_
    $base_path = "../../";
} else {
    // Pagine standard
    $base_path = "../";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Gestione Piani di Studio</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>ui/css/style.css">
    <!-- Aggiungi qui eventuali altri file CSS -->
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema Gestione Piani di Studio</h1>
            
            <nav>
                <ul class="main-menu">
                    <li><a href="<?php echo $base_path; ?>pages/index.php">Home</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo $base_path; ?>pages/my_piani.php">I Miei Piani</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/esami.php">Esami</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/argomenti.php">Argomenti</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/sottoargomenti.php">Sottoargomenti</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/esercizi.php">Esercizi</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/requisiti.php">Requisiti</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/formule.php">Formule</a></li>
                        <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <li><a href="<?php echo $base_path; ?>pages/admin/users.php">Gestione Utenti</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $base_path; ?>pages/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_path; ?>pages/login.php">Accedi</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/register.php">Registrati</a></li>
                    <?php endif; ?>
                    <li class="search-item">
                        <form action="<?php echo $base_path; ?>pages/search.php" method="GET" class="search-form">
                            <input type="text" name="q" placeholder="Cerca in tutto il sistema..." required>
                            <button type="submit">Cerca</button>
                            <!-- Container per i risultati della ricerca in tempo reale -->
                            <div id="search-results" class="search-results-dropdown"></div>
                        </form>
                    </li>
                </ul>
            </nav>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    Benvenuto, <?php echo $_SESSION['username']; ?> 
                    [<?php echo isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? 'Admin' : 'Utente'; ?>]
                </div>
            <?php endif; ?>
        </header>
        <main>