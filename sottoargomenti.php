<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
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
    $sottoargomento = new SottoArgomento($db);
    $argomento = new Argomento($db);
    $esame = new Esame($db);
    
    // Se è stato selezionato un argomento, mostra solo i sottoargomenti di quell'argomento
    $argomento_id = isset($_GET['argomento_id']) ? $_GET['argomento_id'] : null;
    
    if ($argomento_id) {
        $argomento->id = $argomento_id;
        $argomento_info = $argomento->readOne();
        if (!empty($argomento_info)) {
            // Ottieni informazioni sull'esame per il breadcrumb
            $esame->id = $argomento_info['esame_id'];
            $esame_info = $esame->readOne();
            
            echo "<div class='breadcrumb'>";
            echo "<ul>";
            echo "<li><a href='index.php'>Piani di Studio</a></li>";
            echo "<li><a href='esami.php?piano_id=" . $esame_info['piano_id'] . "'>Esami</a></li>";
            echo "<li><a href='argomenti.php?esame_id=" . $esame_info['id'] . "'>" . $esame_info['nome'] . "</a></li>";
            echo "<li>" . $argomento_info['titolo'] . "</li>";
            echo "</ul>";
            echo "</div>";
        }
    }

    // --- Gestione del form per creare un nuovo sottoargomento ---
    if (isset($_POST['create'])) {
        $sottoargomento->argomento_id = $_POST['argomento_id'];
        $sottoargomento->titolo = $_POST['titolo'];
        $sottoargomento->descrizione = $_POST['descrizione'];
        $sottoargomento->livello_profondita = $_POST['livello_profondita'];

        if ($sottoargomento->create()) {
            $message = "Sottoargomento creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare il sottoargomento.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un sottoargomento ---
    if (isset($_POST['update'])) {
        $sottoargomento->id = $_POST['id'];
        $sottoargomento->argomento_id = $_POST['argomento_id'];
        $sottoargomento->titolo = $_POST['titolo'];
        $sottoargomento->descrizione = $_POST['descrizione'];
        $sottoargomento->livello_profondita = $_POST['livello_profondita'];

        if ($sottoargomento->update()) {
            $message = "Sottoargomento aggiornato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile aggiornare il sottoargomento.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un sottoargomento ---
    if (isset($_GET['delete'])) {
        $sottoargomento->id = $_GET['delete'];
        if ($sottoargomento->delete()) {
            $message = "Sottoargomento eliminato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile eliminare il sottoargomento.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- PRIMA MOSTRA LA LISTA DEI SOTTOARGOMENTI ESISTENTI ---
    echo "<div class='header-with-button'>";
    if ($argomento_id) {
        echo "<h2>Sottoargomenti di: " . $argomento_info['titolo'] . "</h2>";
    } else {
        echo "<h2>Tutti i Sottoargomenti</h2>";
    }
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Sottoargomento</button>";
    echo "</div>";
    
    // Leggi tutti i sottoargomenti o i sottoargomenti di un argomento specifico
    if ($argomento_id) {
        $stmt = $sottoargomento->readByArgomento($argomento_id);
    } else {
        $stmt = $sottoargomento->readAll();
    }
    
    // Conta i sottoargomenti
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Mostra argomento solo se stiamo visualizzando tutti i sottoargomenti
            $argomento_info_display = isset($argomento_titolo) ? "<div class='item-meta'>Argomento: $argomento_titolo</div>" : "";
            
            echo "<li class='depth-$livello_profondita'>
                    <div class='item-title'>$titolo</div>
                    $argomento_info_display
                    <div class='item-meta'>Livello di profondità: $livello_profondita</div>
                    <div class='item-description'>$descrizione</div>
                    <div class='item-actions'>
                        <a href='esercizi.php?sottoargomento_id=$id'>Esercizi</a> | 
                        <a href='?edit=$id" . ($argomento_id ? "&argomento_id=$argomento_id" : "") . "'>Modifica</a> | 
                        <a href='?delete=$id" . ($argomento_id ? "&argomento_id=$argomento_id" : "") . "' onclick='return confirm(\"Sei sicuro di voler eliminare questo sottoargomento?\");'>Elimina</a>
                    </div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun sottoargomento trovato." . ($argomento_id ? " Aggiungi un sottoargomento a questo argomento." : "") . "</p>";
    }
    
    // --- POI MOSTRA I FORM DI MODIFICA/CREAZIONE ---
    
    // Form per modificare un sottoargomento
    if (isset($_GET['edit'])) {
        $sottoargomento->id = $_GET['edit'];
        if ($sottoargomento->readOne()) {
            echo "<div id='editFormContainer'>";
            echo "<h2>Modifica Sottoargomento</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $sottoargomento->id . "'>";
            
            // Carica tutti gli argomenti per il menu a tendina se non siamo in un contesto di argomento specifico
            if (!$argomento_id) {
                $stmt_argomenti = $argomento->readAll();
                
                echo "<label for='argomento_id'>Argomento</label>";
                echo "<select name='argomento_id' required>";
                
                while ($row_argomento = $stmt_argomenti->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($sottoargomento->argomento_id == $row_argomento['id']) ? "selected" : "";
                    echo "<option value='" . $row_argomento['id'] . "' $selected>" . $row_argomento['titolo'] . "</option>";
                }
                
                echo "</select>";
            } else {
                echo "<input type='hidden' name='argomento_id' value='$argomento_id'>";
                echo "<div class='form-group'>";
                echo "<label>Argomento</label>";
                echo "<div class='form-control-static'>" . $argomento_info['titolo'] . "</div>";
                echo "</div>";
            }
            
            echo "<label for='titolo'>Titolo Sottoargomento</label>";
            echo "<input type='text' name='titolo' value='" . $sottoargomento->titolo . "' required>";
            
            echo "<label for='descrizione'>Descrizione</label>";
            echo "<textarea name='descrizione'>" . $sottoargomento->descrizione . "</textarea>";
            
            echo "<label for='livello_profondita'>Livello di Profondità (1-5)</label>";
            echo "<select name='livello_profondita'>";
            for ($i = 1; $i <= 5; $i++) {
                $selected = ($sottoargomento->livello_profondita == $i) ? "selected" : "";
                echo "<option value='$i' $selected>Livello $i</option>";
            }
            echo "</select>";
            
            echo "<button type='submit' name='update'>Aggiorna Sottoargomento</button>";
            echo "<a href='sottoargomenti.php" . ($argomento_id ? "?argomento_id=$argomento_id" : "") . "' class='btn-secondary'>Annulla</a>";
            echo "</form>";
            echo "</div>";
        }
    }
    
    // Form per creare un nuovo sottoargomento (inizialmente nascosto)
    echo "<div id='createFormContainer' style='display: none;'>";
    echo "<h2>Crea Nuovo Sottoargomento</h2>";
    echo "<form action='' method='POST'>";
    
    // Carica tutti gli argomenti per il menu a tendina
    if ($argomento_id) {
        // Se siamo in una pagina di argomento specifico, usa quell'argomento
        echo "<input type='hidden' name='argomento_id' value='$argomento_id'>";
        echo "<div class='form-group'>";
        echo "<label>Argomento</label>";
        echo "<div class='form-control-static'>" . $argomento_info['titolo'] . "</div>";
        echo "</div>";
    } else {
        // Altrimenti mostra il menu a tendina
        $stmt_argomenti = $argomento->readAll();
        
        echo "<label for='argomento_id'>Argomento</label>";
        echo "<select name='argomento_id' required>";
        
        while ($row_argomento = $stmt_argomenti->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . $row_argomento['id'] . "'>" . $row_argomento['titolo'] . "</option>";
        }
        
        echo "</select>";
    }
    
    echo "<label for='titolo'>Titolo Sottoargomento</label>";
    echo "<input type='text' name='titolo' required>";
    
    echo "<label for='descrizione'>Descrizione</label>";
    echo "<textarea name='descrizione'></textarea>";
    
    echo "<label for='livello_profondita'>Livello di Profondità (1-5)</label>";
    echo "<select name='livello_profondita'>";
    for ($i = 1; $i <= 5; $i++) {
        $selected = ($i == 1) ? "selected" : ""; // Default: livello 1
        echo "<option value='$i' $selected>Livello $i</option>";
    }
    echo "</select>";
    
    echo "<button type='submit' name='create'>Crea Sottoargomento</button>";
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
include_once 'ui/includes/footer.php';
?>