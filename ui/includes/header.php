<?php
// Assumi che qui ci siano le variabili di sessione e altre configurazioni
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Piani di Studio</title>
    <link rel="stylesheet" href="ui/css/style.css">
    <!-- Font Awesome per le icone -->
     <script src='js/form-manager.js'></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema di Gestione Piani di Studio</h1>
            
            <nav>
                <!-- Menu principale -->
               <ul class="main-menu">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php">Piani di Studio</a></li>
    <li><a href="esami.php">Esami</a></li>
    <li><a href="argomenti.php">Argomenti</a></li>
   
</ul>
                <!-- Barra di ricerca -->
                <div id="search-container">
                    <div class="nav-search">
                        <i class="fas fa-search nav-search-icon"></i>
                        <input type="text" id="nav-search-input" placeholder="Cerca piani, esami, argomenti...">
                    </div>
                    <div id="search-results"></div>
                </div>
            </nav>
             <script src="js/search.js"></script>
        </header>
        
        <main>
            <!-- Il contenuto della pagina verrà inserito qui -->