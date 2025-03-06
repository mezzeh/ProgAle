<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
include_once 'models/esame.php';
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
    // Istanza dei modelli
    $esame = new Esame($db);
    $piano = new PianoDiStudio($db);
    
    // Se è stato selezionato un piano di studio, mostra solo gli esami di quel piano
    $piano_id = isset($_GET['piano_id']) ? $_GET['piano_id'] : null;
    
    if ($piano_id) {
        $piano->id = $piano_id;
        $piano_info = $piano->readOne();
        if (!empty($piano_info)) {
            echo "<h2>Esami del Piano: " . $piano_info['nome'] . "</h2>";
            echo "<p><a href='index.php'>← Torna ai Piani di Studio</a></p>";
        }
    } else {
        echo "<h2>Tutti gli Esami</h2>";
    }

    // --- Gestione del form per creare un nuovo esame ---
    if (isset($_POST['create'])) {
        $esame->piano_id = $_POST['piano_id'];
        $esame->nome = $_POST['nome'];
        $esame->codice = $_POST['codice'];
        $esame->crediti = $_POST['crediti'];
        $esame->descrizione = $_POST['descrizione'];

        if ($esame->create()) {
            $message = "Esame creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare l'esame.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un esame ---
    if (isset($_POST['update'])) {
        $esame->id = $_POST['id'];
        $esame->piano_id = $_POST['piano_id'];
        $esame->nome = $_POST['nome'];
        $esame->codice = $_POST['codice'];
        $esame->crediti = $_POST['crediti'];
        $esame->descrizione = $_POST['descrizione'];

        if ($esame->update()) {
            $message = "Esame aggiornato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile aggiornare l'esame.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un esame ---
    if (isset($_GET['delete'])) {
        $esame->id = $_GET['delete'];
        if ($esame->delete()) {
            $message = "Esame eliminato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile eliminare l'esame.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- Form per creare/modificare un esame ---
    if (isset($_GET['edit'])) {
        // Modifica un esame esistente
        $esame->id = $_GET['edit'];
        if ($esame->readOne()) {
            echo "<h2>Modifica Esame</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $esame->id . "'>";
        } else {
            echo "<p class='message error'>Esame non trovato.</p>";
        }
    } else {
        // Crea un nuovo esame
        echo "<h2>Crea Nuovo Esame</h2>";
        echo "<form action='' method='POST'>";
    }
    
    if (!isset($_GET['edit']) || (isset($_GET['edit']) && $esame->readOne())) {
        // Carica tutti i piani di studio per il menu a tendina
        $stmt_piani = $piano->readAll();
        
        echo "<label for='piano_id'>Piano di Studio</label>";
        echo "<select name='piano_id' required>";
        
        while ($row_piano = $stmt_piani->fetch(PDO::FETCH_ASSOC)) {
            $selected = "";
            if ((isset($_GET['edit']) && $esame->piano_id == $row_piano['id']) || 
                (!isset($_GET['edit']) && isset($_GET['piano_id']) && $_GET['piano_id'] == $row_piano['id'])) {
                $selected = "selected";
            }
            echo "<option value='" . $row_piano['id'] . "' $selected>" . $row_piano['nome'] . "</option>";
        }
        
        echo "</select>";
        
        // Campi per i dati dell'esame
        $nome_value = isset($esame->nome) ? $esame->nome : "";
        $codice_value = isset($esame->codice) ? $esame->codice : "";
        $crediti_value = isset($esame->crediti) ? $esame->crediti : "";
        $descrizione_value = isset($esame->descrizione) ? $esame->descrizione : "";
        
        echo "<label for='nome'>Nome Esame</label>";
        echo "<input type='text' name='nome' value='$nome_value' required>";
        
        echo "<label for='codice'>Codice Esame</label>";
        echo "<input type='text' name='codice' value='$codice_value'>";
        
        echo "<label for='crediti'>Crediti</label>";
        echo "<input type='number' name='crediti' value='$crediti_value' min='1' max='30'>";
        
        echo "<label for='descrizione'>Descrizione</label>";
        echo "<textarea name='descrizione'>$descrizione_value</textarea>";
        
        // Pulsanti di submit
        if (isset($_GET['edit'])) {
            echo "<button type='submit' name='update'>Aggiorna Esame</button>";
        } else {
            echo "<button type='submit' name='create'>Crea Esame</button>";
        }
        
        echo "<a href='esami.php" . ($piano_id ? "?piano_id=$piano_id" : "") . "' class='btn-secondary'>Annulla</a>";
        echo "</form>";
    }

    // --- Leggi tutti gli esami o gli esami di un piano specifico ---
    if ($piano_id) {
        $stmt = $esame->readByPiano($piano_id);
    } else {
        $stmt = $esame->readAll();
    }
    
    echo "<h2>Lista Esami</h2>";
    
    // Conta gli esami
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Mostra piano di studio solo se stiamo visualizzando tutti gli esami
            $piano_info = isset($piano_nome) ? "<div class='item-meta'>Piano: $piano_nome</div>" : "";
            
            echo "<li>
                    <div class='item-title'>$nome</div>
                    <div class='item-meta'>Codice: $codice | Crediti: $crediti</div>
                    $piano_info
                    <div class='item-description'>$descrizione</div>
                    <div class='item-actions'>
                        <a href='argomenti.php?esame_id=$id'>Argomenti</a> | 
                        <a href='?edit=$id" . ($piano_id ? "&piano_id=$piano_id" : "") . "'>Modifica</a> | 
                        <a href='?delete=$id" . ($piano_id ? "&piano_id=$piano_id" : "") . "' onclick='return confirm(\"Sei sicuro di voler eliminare questo esame?\");'>Elimina</a>
                    </div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun esame trovato." . ($piano_id ? " Aggiungi un esame a questo piano di studio." : "") . "</p>";
    }
}

// Includi footer
include_once 'ui/includes/footer.php';
?>