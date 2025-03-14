<?php
session_start();

// Pagine accessibili a tutti (anche senza login)
$public_pages = ['index.php', 'login.php', 'register.php', 'view_piano.php', 'view_esame.php', 'view_argomento.php'];

// Verifica se la pagina corrente è riservata agli utenti autenticati
$current_page = basename($_SERVER['PHP_SELF']);
$requires_auth = !in_array($current_page, $public_pages);

// Se la pagina richiede autenticazione e l'utente non è loggato, reindirizza al login
if($requires_auth && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Gestione Piani di Studio</title>
    <link rel="stylesheet" href="../ui/css/style.css">
    <link rel="stylesheet" href="../ui/css/search.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema Gestione Piani di Studio</h1>
            
            <nav>
                <ul class="main-menu">
                    <li><a href="index.php">Home</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="my_piani.php">I Miei Piani</a></li>
                        <li><a href="esami.php">Esami</a></li>
                        <li><a href="argomenti.php">Argomenti</a></li>
                        <li><a href="sottoargomenti.php">Sottoargomenti</a></li>
                        <li><a href="esercizi.php">Esercizi</a></li>
                        <li><a href="requisiti.php">Requisiti</a></li>
                        <li><a href="formule.php">Formule</a></li>
                        <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <li><a href="admin/users.php">Gestione Utenti</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Accedi</a></li>
                        <li><a href="register.php">Registrati</a></li>
                    <?php endif; ?>
                    <li class="search-item">
                        <form action="search.php" method="GET" class="search-form">
                            <input type="text" name="q" placeholder="Cerca in tutto il sistema..." required>
                            <button type="submit">Cerca</button>
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
            
        <!-- Aggiungiamo lo script per la ricerca in tempo reale -->
        <script src="../ui/js/search.js"></script>