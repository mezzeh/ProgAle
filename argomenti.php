<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
include_once 'models/argomento.php';
include_once 'models/esame.php';

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
    $argomento = new Argomento($db);
    $esame = new Esame($db);
    
    // Se è stato selezionato un esame, mostra solo gli argomenti di quell'esame
    $esame_id = isset($_GET['esame_id']) ? $_GET['esame_id'] : null;
    
    if ($esame_id) {
        $esame->id = $esame_id;
        $esame_info = $esame->readOne();
        if (!empty($esame_info)) {
            echo "<div class='breadcrumb'>";
            echo "<ul>";
            echo "<li><a href='index.php'>Piani di Studio</a></li>";
            echo "<li><a href='esami.php?piano_id=" . $esame_info['piano_id'] . "'>Esami</a></li>";
            echo "<li>" . $esame_info['nome'] . "</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<h2>Argomenti dell'Esame: " . $esame_info['nome'] . "</h2>";
        }
    } else {
        echo "<h2>Tutti gli Argomenti</h2>";
    }

    // --- Gestione del form per creare un nuovo argomento ---
    if (isset($_POST['create'])) {
        $argomento->esame_id = $_POST['esame_id'];
        $argomento->titolo = $_POST['titolo'];
        $argomento->descrizione = $_POST['descrizione'];
        $argomento->livello_importanza = $_POST['livello_importanza'];

        if ($argomento->create()) {
            $message = "Argomento creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare l'argomento.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un argomento ---
    if (isset($_POST['update'])) {
        $argomento->id = $_POST['id'];
        $argomento->esame_id = $_POST['esame_id'];
        $argomento->titolo = $_POST['titolo'];
        $argomento->descrizione = $_POST['descrizione'];
        $argomento->livello_importanza = $_POST['livello_importanza'];

        if ($argomento->update()) {
            $message = "Argomento aggiornato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile aggiornare l'argomento.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un argomento ---
    if (isset($_GET['delete'])) {
        $argomento->id = $_GET['delete'];
        if ($argomento->delete()) {
            $message = "Argomento eliminato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile eliminare l'argomento.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- Form per creare/modificare un argomento ---
    if (isset($_GET['edit'])) {
        // Modifica un argomento esistente
        $argomento->id = $_GET['edit'];
        if ($argomento->readOne()) {
            echo "<h2>Modifica Argomento</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $argomento->id . "'>";
        } else {
            echo "<p class='message error'>Argomento non trovato.</p>";
        }
    } else {
        // Crea un nuovo argomento
        echo "<h2>Crea Nuovo Argomento</h2>";
        echo "<form action='' method='POST'>";
    }
    
    if (!isset($_GET['edit']) || (isset($_GET['edit']) && $argomento->readOne())) {
        // Carica tutti gli esami per il menu a tendina
        if ($esame_id) {
            // Se siamo in una pagina di esame specifico, mostra solo quell'esame
            $esame->id = $esame_id;
            $esame_info = $esame->readOne();
            echo "<input type='hidden' name='esame_id' value='$esame_id'>";
            echo "<div class='form-group'>";
            echo "<label>Esame</label>";
            echo "<div class='form-control-static'>" . $esame_info['nome'] . "</div>";
            echo "</div>";
        } else {
            // Altrimenti mostra il menu a tendina con tutti gli esami
            $stmt_esami = $esame->readAll();
            
            echo "<label for='esame_id'>Esame</label>";
            echo "<select name='esame_id' required>";
            
            while ($row_esame = $stmt_esami->fetch(PDO::FETCH_ASSOC)) {
                $selected = "";
                if ((isset($_GET['edit']) && $argomento->esame_id == $row_esame['id']) || 
                    (!isset($_GET['edit']) && isset($_GET['esame_id']) && $_GET['esame_id'] == $row_esame['id'])) {
                    $selected = "selected";
                }
                echo "<option value='" . $row_esame['id'] . "' $selected>" . $row_esame['nome'] . "</option>";
            }
            
            echo "</select>";
        }
        
        // Campi per i dati dell'argomento
        $titolo_value = isset($argomento->titolo) ? $argomento->titolo : "";
        $descrizione_value = isset($argomento->descrizione) ? $argomento->descrizione : "";
        $livello_value = isset($argomento->livello_importanza) ? $argomento->livello_importanza : "3";
        
        echo "<label for='titolo'>Titolo Argomento</label>";
        echo "<input type='text' name='titolo' value='$titolo_value' required>";
        
        echo "<label for='descrizione'>Descrizione</label>";
        echo "<textarea name='descrizione'>$descrizione_value</textarea>";
        
        echo "<label for='livello_importanza'>Livello di Importanza (1-5)</label>";
        echo "<select name='livello_importanza'>";
        for ($i = 1; $i <= 5; $i++) {
            $selected = ($livello_value == $i) ? "selected" : "";
            $label = "";
            switch ($i) {
                case 1: $label = "Molto importante"; break;
                case 2: $label = "Importante"; break;
                case 3: $label = "Media importanza"; break;
                case 4: $label = "Poco importante"; break;
                case 5: $label = "Marginale"; break;
            }
            echo "<option value='$i' $selected>$i - $label</option>";
        }
        echo "</select>";
        
        // Pulsanti di submit
        if (isset($_GET['edit'])) {
            echo "<button type='submit' name='update'>Aggiorna Argomento</button>";
        } else {
            echo "<button type='submit' name='create'>Crea Argomento</button>";
        }
        
        echo "<a href='argomenti.php" . ($esame_id ? "?esame_id=$esame_id" : "") . "' class='btn-secondary'>Annulla</a>";
        echo "</form>";
    }

    // --- Leggi tutti gli argomenti o gli argomenti di un esame specifico ---
    if ($esame_id) {
        $stmt = $argomento->readByEsame($esame_id);
    } else {
        $stmt = $argomento->readAll();
    }
    
    echo "<h2>Lista Argomenti</h2>";
    
    // Conta gli argomenti
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Mostra esame solo se stiamo visualizzando tutti gli argomenti
            $esame_info = isset($esame_nome) ? "<div class='item-meta'>Esame: $esame_nome</div>" : "";
            
            echo "<li class='importance-$livello_importanza'>
                    <div class='item-title'>$titolo</div>
                    $esame_info
                    <div class='item-meta'>Importanza: $livello_importanza</div>
                    <div class='item-description'>$descrizione</div>
                    <div class='item-actions'>
                        <a href='sottoargomenti.php?argomento_id=$id'>Sottoargomenti</a> | 
                        <a href='?edit=$id" . ($esame_id ? "&esame_id=$esame_id" : "") . "'>Modifica</a> | 
                        <a href='?delete=$id" . ($esame_id ? "&esame_id=$esame_id" : "") . "' onclick='return confirm(\"Sei sicuro di voler eliminare questo argomento?\");'>Elimina</a>
                    </div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun argomento trovato." . ($esame_id ? " Aggiungi un argomento a questo esame." : "") . "</p>";
    }
}

// Includi footer
include_once 'ui/includes/footer.php';
?>