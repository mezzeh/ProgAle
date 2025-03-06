<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
include_once 'models/piano_di_studio.php';

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
    
    // --- Form per creare un nuovo piano di studio ---
    if (!isset($_GET['edit'])) {
        echo "<h2>Crea Nuovo Piano di Studio</h2>";
        echo "<form action='' method='POST'>
                <label for='nome'>Nome</label>
                <input type='text' name='nome' required>
                <label for='descrizione'>Descrizione</label>
                <textarea name='descrizione' required></textarea>
                <button type='submit' name='create'>Crea Piano</button>
            </form>";
    }

    // --- Modifica un piano di studio ---
    if (isset($_GET['edit'])) {
        $piano->id = $_GET['edit'];
        if ($piano->readOne()) {
            echo "<h2>Modifica Piano di Studio</h2>";
            echo "<form action='' method='POST'>
                    <input type='hidden' name='id' value='$piano->id'>
                    <label for='nome'>Nome</label>
                    <input type='text' name='nome' value='$piano->nome' required>
                    <label for='descrizione'>Descrizione</label>
                    <textarea name='descrizione' required>$piano->descrizione</textarea>
                    <button type='submit' name='update'>Aggiorna Piano</button>
                    <a href='index.php' class='btn-secondary'>Annulla</a>
                </form>";
        }
    }

    // --- Leggi tutti i piani di studio ---
    $stmt = $piano->readAll();
    
    echo "<h2>Piani di Studio</h2>";
    
    // Conta i piani di studio
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
}

// Includi footer
include_once 'ui/includes/footer.php';
?>