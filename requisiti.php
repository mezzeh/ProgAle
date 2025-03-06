<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
include_once 'models/requisito.php';
include_once 'models/esercizio.php';
include_once 'models/sottoargomento.php';
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
    $requisito = new Requisito($db);
    $esercizio = new Esercizio($db);
    $sottoargomento = new SottoArgomento($db);
    $argomento = new Argomento($db);
    $esame = new Esame($db);
    
    // Se è stato selezionato un esercizio, mostra solo i requisiti di quell'esercizio
    $esercizio_id = isset($_GET['esercizio_id']) ? $_GET['esercizio_id'] : null;
    
    if ($esercizio_id) {
        $esercizio->id = $esercizio_id;
        $esercizio_info = $esercizio->readOne();
        if (!empty($esercizio_info)) {
            // Ottieni informazioni su sottoargomento, argomento ed esame per il breadcrumb
            $sottoargomento->id = $esercizio_info['sottoargomento_id'];
            $sottoargomento_info = $sottoargomento->readOne();
            
            $argomento->id = $sottoargomento_info['argomento_id'];
            $argomento_info = $argomento->readOne();
            
            $esame->id = $argomento_info['esame_id'];
            $esame_info = $esame->readOne();
            
            echo "<div class='breadcrumb'>";
            echo "<ul>";
            echo "<li><a href='index.php'>Piani di Studio</a></li>";
            echo "<li><a href='esami.php?piano_id=" . $esame_info['piano_id'] . "'>Esami</a></li>";
            echo "<li><a href='argomenti.php?esame_id=" . $esame_info['id'] . "'>" . $esame_info['nome'] . "</a></li>";
            echo "<li><a href='sottoargomenti.php?argomento_id=" . $argomento_info['id'] . "'>" . $argomento_info['titolo'] . "</a></li>";
            echo "<li><a href='esercizi.php?sottoargomento_id=" . $sottoargomento_info['id'] . "'>" . $sottoargomento_info['titolo'] . "</a></li>";
            echo "<li>" . $esercizio_info['titolo'] . "</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<h2>Requisiti dell'Esercizio: " . $esercizio_info['titolo'] . "</h2>";
        }
    } else {
        echo "<h2>Tutti i Requisiti</h2>";
    }

    // --- Gestione del form per creare un nuovo requisito ---
    if (isset($_POST['create'])) {
        $requisito->esercizio_id = $_POST['esercizio_id'];
        $requisito->descrizione = $_POST['descrizione'];

        if ($requisito->create()) {
            $message = "Requisito creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare il requisito.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un requisito ---
    if (isset($_POST['update'])) {
        $requisito->id = $_POST['id'];
        $requisito->esercizio_id = $_POST['esercizio_id'];
        $requisito->descrizione = $_POST['descrizione'];

        if ($requisito->update()) {
            $message = "Requisito aggiornato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile aggiornare il requisito.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un requisito ---
    if (isset($_GET['delete'])) {
        $requisito->id = $_GET['delete'];
        if ($requisito->delete()) {
            $message = "Requisito eliminato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile eliminare il requisito.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- Form per creare/modificare un requisito ---
    if (isset($_GET['edit'])) {
        // Modifica un requisito esistente
        $requisito->id = $_GET['edit'];
        if ($requisito->readOne()) {
            echo "<h2>Modifica Requisito</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $requisito->id . "'>";
        } else {
            echo "<p class='message error'>Requisito non trovato.</p>";
        }
    } else {
        // Crea un nuovo requisito
        echo "<h2>Crea Nuovo Requisito</h2>";
        echo "<form action='' method='POST'>";
    }
    
    if (!isset($_GET['edit']) || (isset($_GET['edit']) && $requisito->readOne())) {
        // Gestione esercizio
        if ($esercizio_id) {
            // Se siamo in una pagina di esercizio specifico, mostra solo quell'esercizio
            $esercizio->id = $esercizio_id;
            $esercizio_info = $esercizio->readOne();
            echo "<input type='hidden' name='esercizio_id' value='$esercizio_id'>";
            echo "<div class='form-group'>";
            echo "<label>Esercizio</label>";
            echo "<div class='form-control-static'>" . $esercizio_info['titolo'] . "</div>";
            echo "</div>";
        } else {
            // Altrimenti mostra il menu a tendina con tutti gli esercizi
            $stmt_esercizi = $esercizio->readAll();
            
            echo "<label for='esercizio_id'>Esercizio</label>";
            echo "<select name='esercizio_id' required>";
            
            while ($row_esercizio = $stmt_esercizi->fetch(PDO::FETCH_ASSOC)) {
                $selected = "";
                if ((isset($_GET['edit']) && $requisito->esercizio_id == $row_esercizio['id']) || 
                    (!isset($_GET['edit']) && isset($_GET['esercizio_id']) && $_GET['esercizio_id'] == $row_esercizio['id'])) {
                    $selected = "selected";
                }
                echo "<option value='" . $row_esercizio['id'] . "' $selected>" . $row_esercizio['titolo'] . "</option>";
            }
            
            echo "</select>";
        }
        
        // Campi per i dati del requisito
        $descrizione_value = isset($requisito->descrizione) ? $requisito->descrizione : "";
        
        echo "<label for='descrizione'>Descrizione del Requisito</label>";
        echo "<textarea name='descrizione' rows='4' required>$descrizione_value</textarea>";
        
        // Pulsanti di submit
        if (isset($_GET['edit'])) {
            echo "<button type='submit' name='update'>Aggiorna Requisito</button>";
        } else {
            echo "<button type='submit' name='create'>Crea Requisito</button>";
        }
        
        echo "<a href='requisiti.php" . ($esercizio_id ? "?esercizio_id=$esercizio_id" : "") . "' class='btn-secondary'>Annulla</a>";
        echo "</form>";
    }

    // --- Leggi tutti i requisiti o i requisiti di un esercizio specifico ---
    if ($esercizio_id) {
        $stmt = $requisito->readByEsercizio($esercizio_id);
    } else {
        $stmt = $requisito->readAll();
    }
    
    echo "<h2>Lista Requisiti</h2>";
    
    // Conta i requisiti
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Mostra esercizio solo se stiamo visualizzando tutti i requisiti
            $esercizio_info = isset($esercizio_titolo) ? "<div class='item-meta'>Esercizio: $esercizio_titolo</div>" : "";
            
            echo "<li>
                    <div class='item-description'>$descrizione</div>
                    $esercizio_info
                    <div class='item-actions'>
                        <a href='?edit=$id" . ($esercizio_id ? "&esercizio_id=$esercizio_id" : "") . "'>Modifica</a> | 
                        <a href='?delete=$id" . ($esercizio_id ? "&esercizio_id=$esercizio_id" : "") . "' onclick='return confirm(\"Sei sicuro di voler eliminare questo requisito?\");'>Elimina</a>
                    </div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun requisito trovato." . ($esercizio_id ? " Aggiungi requisiti per questo esercizio." : "") . "</p>";
    }
}

// Includi footer
include_once 'ui/includes/footer.php';
?>