<?php
// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/requisito.php';
include_once '../models/esercizio.php';
include_once '../models/sottoargomento.php';
include_once '../models/argomento.php';
include_once '../models/esame.php';

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
        }
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
    
    // --- PRIMA MOSTRA LA LISTA DEI REQUISITI ESISTENTI ---
    echo "<div class='header-with-button'>";
    if ($esercizio_id) {
        echo "<h2>Requisiti dell'Esercizio: " . $esercizio_info['titolo'] . "</h2>";
    } else {
        echo "<h2>Tutti i Requisiti</h2>";
    }
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Requisito</button>";
    echo "</div>";
    
    // Leggi tutti i requisiti o i requisiti di un esercizio specifico
    if ($esercizio_id) {
        $stmt = $requisito->readByEsercizio($esercizio_id);
    } else {
        $stmt = $requisito->readAll();
    }
    
    // Conta i requisiti
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Mostra esercizio solo se stiamo visualizzando tutti i requisiti
            $esercizio_info_display = isset($esercizio_titolo) ? "<div class='item-meta'>Esercizio: $esercizio_titolo</div>" : "";
            
            echo "<li>
                    <div class='item-description'>$descrizione</div>
                    $esercizio_info_display
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
    
    // --- POI MOSTRA I FORM DI MODIFICA/CREAZIONE ---
    
    // Form per modificare un requisito
    if (isset($_GET['edit'])) {
        $requisito->id = $_GET['edit'];
        if ($requisito->readOne()) {
            echo "<div id='editFormContainer'>";
            echo "<h2>Modifica Requisito</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $requisito->id . "'>";
            
            // Carica tutti gli esercizi per il menu a tendina se non siamo in un contesto di esercizio specifico
            if (!$esercizio_id) {
                $stmt_esercizi = $esercizio->readAll();
                
                echo "<label for='esercizio_id'>Esercizio</label>";
                echo "<select name='esercizio_id' required>";
                
                while ($row_esercizio = $stmt_esercizi->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($requisito->esercizio_id == $row_esercizio['id']) ? "selected" : "";
                    echo "<option value='" . $row_esercizio['id'] . "' $selected>" . $row_esercizio['titolo'] . "</option>";
                }
                
                echo "</select>";
            } else {
                echo "<input type='hidden' name='esercizio_id' value='$esercizio_id'>";
                echo "<div class='form-group'>";
                echo "<label>Esercizio</label>";
                echo "<div class='form-control-static'>" . $esercizio_info['titolo'] . "</div>";
                echo "</div>";
            }
            
            echo "<label for='descrizione'>Descrizione del Requisito</label>";
            echo "<textarea name='descrizione' rows='4' required>" . $requisito->descrizione . "</textarea>";
            
            echo "<button type='submit' name='update'>Aggiorna Requisito</button>";
            echo "<a href='requisiti.php" . ($esercizio_id ? "?esercizio_id=$esercizio_id" : "") . "' class='btn-secondary'>Annulla</a>";
            echo "</form>";
            echo "</div>";
        }
    }
    
    // Form per creare un nuovo requisito (inizialmente nascosto)
    echo "<div id='createFormContainer' style='display: none;'>";
    echo "<h2>Crea Nuovo Requisito</h2>";
    echo "<form action='' method='POST'>";
    
    // Carica tutti gli esercizi per il menu a tendina
    if ($esercizio_id) {
        // Se siamo in una pagina di esercizio specifico, usa quell'esercizio
        echo "<input type='hidden' name='esercizio_id' value='$esercizio_id'>";
        echo "<div class='form-group'>";
        echo "<label>Esercizio</label>";
        echo "<div class='form-control-static'>" . $esercizio_info['titolo'] . "</div>";
        echo "</div>";
    } else {
        // Altrimenti mostra il menu a tendina
        $stmt_esercizi = $esercizio->readAll();
        
        echo "<label for='esercizio_id'>Esercizio</label>";
        echo "<select name='esercizio_id' required>";
        
        while ($row_esercizio = $stmt_esercizi->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . $row_esercizio['id'] . "'>" . $row_esercizio['titolo'] . "</option>";
        }
        
        echo "</select>";
    }
    
    echo "<label for='descrizione'>Descrizione del Requisito</label>";
    echo "<textarea name='descrizione' rows='4' required></textarea>";
    
    echo "<button type='submit' name='create'>Crea Requisito</button>";
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