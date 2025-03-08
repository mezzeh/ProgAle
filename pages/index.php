<?php
// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/piano_di_studio.php';

// Inizializza variabili per messaggi
$message = "";
$message_class = "";

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    $message = "Problema di connessione al database.";
    $message_class = "error";
} else {
    // Istanza del modello PianoDiStudio
    $piano = new PianoDiStudio($db);

    // --- Gestione del form per creare un nuovo piano di studio ---
    if (isset($_POST['create'])) {
        $piano->nome = $_POST['nome'];
        $piano->descrizione = $_POST['descrizione'];

        if ($piano->create()) {
            $message = "Piano di studio creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare il piano di studio.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un piano di studio ---
    if (isset($_POST['update'])) {
        $piano->id = $_POST['id'];
        $piano->nome = $_POST['nome'];
        $piano->descrizione = $_POST['descrizione'];

        if ($piano->update()) {
            $message = "Piano di studio aggiornato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile aggiornare il piano di studio.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un piano ---
    if (isset($_GET['delete'])) {
        $piano->id = $_GET['delete'];
        if ($piano->delete()) {
            $message = "Piano di studio eliminato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile eliminare il piano di studio.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- PRIMA MOSTRA LA LISTA DEI PIANI DI STUDIO ESISTENTI ---
    echo "<div class='header-with-button'>";
    echo "<h2>Piani di Studio</h2>";
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Piano</button>";
    echo "</div>";
    
    // Leggi tutti i piani di studio
    $stmt = $piano->readAll();
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            echo "<li>
                    <div class='item-title'>$nome</div>
                    <div class='item-description'>$descrizione</div>
                    <div class='item-actions'>
                        <a href='esami.php?piano_id=$id'>Esami</a> | 
                        <a href='?edit=$id'>Modifica</a> | 
                        <a href='?delete=$id' onclick='return confirm(\"Sei sicuro di voler eliminare questo piano?\");'>Elimina</a>
                    </div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun piano di studio trovato.</p>";
    }
    
    // --- POI MOSTRA I FORM DI CREAZIONE/MODIFICA ---
    
    // Form per modificare un piano di studio (mostrato solo quando si clicca "Modifica")
    if (isset($_GET['edit'])) {
        $piano->id = $_GET['edit'];
        if ($piano->readOne()) {
            echo "<div id='editFormContainer'>";
            echo "<h2>Modifica Piano di Studio</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='$piano->id'>";
            echo "<label for='nome'>Nome</label>";
            echo "<input type='text' name='nome' value='$piano->nome' required>";
            echo "<label for='descrizione'>Descrizione</label>";
            echo "<textarea name='descrizione' required>$piano->descrizione</textarea>";
            echo "<button type='submit' name='update'>Aggiorna Piano</button>";
            echo "<a href='index.php' class='btn-secondary'>Annulla</a>";
            echo "</form>";
            echo "</div>";
        }
    }

    // Form per creare un nuovo piano di studio (inizialmente nascosto)
    echo "<div id='createFormContainer' style='display: " . (isset($_GET['edit']) ? "none" : "none") . ";'>";
    echo "<h2>Crea Nuovo Piano di Studio</h2>";
    echo "<form action='' method='POST'>";
    echo "<label for='nome'>Nome</label>";
    echo "<input type='text' name='nome' required>";
    echo "<label for='descrizione'>Descrizione</label>";
    echo "<textarea name='descrizione' required></textarea>";
    echo "<button type='submit' name='create'>Crea Piano</button>";
    echo "<button type='button' id='cancelCreateBtn' class='btn-secondary'>Annulla</button>";
    echo "</form>";
    echo "</div>";
    
    // JavaScript per mostrare/nascondere il form di creazione
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const showCreateFormBtn = document.getElementById('showCreateFormBtn');
            const createFormContainer = document.getElementById('createFormContainer');
            const cancelCreateBtn = document.getElementById('cancelCreateBtn');
            
            if (showCreateFormBtn && createFormContainer) {
                showCreateFormBtn.addEventListener('click', function() {
                    createFormContainer.style.display = 'block';
                    showCreateFormBtn.style.display = 'none';
                });
            }
            
            if (cancelCreateBtn && createFormContainer && showCreateFormBtn) {
                cancelCreateBtn.addEventListener('click', function() {
                    createFormContainer.style.display = 'none';
                    showCreateFormBtn.style.display = 'inline-block';
                });
            }
        });
    </script>";
}

// Includi footer
include_once '../ui/includes/footer.php';
?>