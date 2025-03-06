<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
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
    $esercizio = new Esercizio($db);
    $sottoargomento = new SottoArgomento($db);
    $argomento = new Argomento($db);
    $esame = new Esame($db);
    
    // Se è stato selezionato un sottoargomento, mostra solo gli esercizi di quel sottoargomento
    $sottoargomento_id = isset($_GET['sottoargomento_id']) ? $_GET['sottoargomento_id'] : null;
    
    if ($sottoargomento_id) {
        $sottoargomento->id = $sottoargomento_id;
        $sottoargomento_info = $sottoargomento->readOne();
        if (!empty($sottoargomento_info)) {
            // Ottieni informazioni su argomento ed esame per il breadcrumb
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
            echo "<li>" . $sottoargomento_info['titolo'] . "</li>";
            echo "</ul>";
            echo "</div>";
        }
    }

    // --- Gestione del form per creare un nuovo esercizio ---
    if (isset($_POST['create'])) {
        $esercizio->sottoargomento_id = $_POST['sottoargomento_id'];
        $esercizio->titolo = $_POST['titolo'];
        $esercizio->testo = $_POST['testo'];
        $esercizio->soluzione = $_POST['soluzione'];
        $esercizio->difficolta = $_POST['difficolta'];

        if ($esercizio->create()) {
            $message = "Esercizio creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare l'esercizio.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un esercizio ---
    if (isset($_POST['update'])) {
        $esercizio->id = $_POST['id'];
        $esercizio->sottoargomento_id = $_POST['sottoargomento_id'];
        $esercizio->titolo = $_POST['titolo'];
        $esercizio->testo = $_POST['testo'];
        $esercizio->soluzione = $_POST['soluzione'];
        $esercizio->difficolta = $_POST['difficolta'];

        if ($esercizio->update()) {
            $message = "Esercizio aggiornato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile aggiornare l'esercizio.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un esercizio ---
    if (isset($_GET['delete'])) {
        $esercizio->id = $_GET['delete'];
        if ($esercizio->delete()) {
            $message = "Esercizio eliminato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile eliminare l'esercizio.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- PRIMA MOSTRA LA LISTA DEGLI ESERCIZI ESISTENTI ---
    echo "<div class='header-with-button'>";
    if ($sottoargomento_id) {
        echo "<h2>Esercizi di: " . $sottoargomento_info['titolo'] . "</h2>";
    } else {
        echo "<h2>Tutti gli Esercizi</h2>";
    }
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Esercizio</button>";
    echo "</div>";
    
    // Leggi tutti gli esercizi o gli esercizi di un sottoargomento specifico
    if ($sottoargomento_id) {
        $stmt = $esercizio->readBySottoArgomento($sottoargomento_id);
    } else {
        $stmt = $esercizio->readAll();
    }
    
    // Conta gli esercizi
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Mostra sottoargomento solo se stiamo visualizzando tutti gli esercizi
            $sottoargomento_info_display = isset($sottoargomento_titolo) ? "<div class='item-meta'>Sottoargomento: $sottoargomento_titolo</div>" : "";
            
            // Determina la classe CSS in base alla difficoltà
            $difficolta_class = "difficulty-$difficolta";
            $difficolta_text = ($difficolta == 1) ? "Facile" : (($difficolta == 2) ? "Media" : "Difficile");
            
            echo "<li class='$difficolta_class'>
                    <div class='item-title'>$titolo</div>
                    $sottoargomento_info_display
                    <div class='item-meta'>Difficoltà: $difficolta_text</div>
                    <div class='item-description'>
                        <strong>Testo:</strong><br>
                        $testo
                    </div>
                    <div class='item-description'>
                        <strong>Soluzione:</strong><br>
                        $soluzione
                    </div>
                    <div class='item-actions'>
                        <a href='requisiti.php?esercizio_id=$id'>Requisiti</a> | 
                        <a href='?edit=$id" . ($sottoargomento_id ? "&sottoargomento_id=$sottoargomento_id" : "") . "'>Modifica</a> | 
                        <a href='?delete=$id" . ($sottoargomento_id ? "&sottoargomento_id=$sottoargomento_id" : "") . "' onclick='return confirm(\"Sei sicuro di voler eliminare questo esercizio?\");'>Elimina</a>
                    </div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun esercizio trovato." . ($sottoargomento_id ? " Aggiungi un esercizio a questo sottoargomento." : "") . "</p>";
    }
    
    // --- POI MOSTRA I FORM DI MODIFICA/CREAZIONE ---
    
    // Form per modificare un esercizio
    if (isset($_GET['edit'])) {
        $esercizio->id = $_GET['edit'];
        if ($esercizio->readOne()) {
            echo "<div id='editFormContainer'>";
            echo "<h2>Modifica Esercizio</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $esercizio->id . "'>";
            
            // Carica tutti i sottoargomenti per il menu a tendina se non siamo in un contesto di sottoargomento specifico
            if (!$sottoargomento_id) {
                $stmt_sottoargomenti = $sottoargomento->readAll();
                
                echo "<label for='sottoargomento_id'>Sottoargomento</label>";
                echo "<select name='sottoargomento_id' required>";
                
                while ($row_sottoargomento = $stmt_sottoargomenti->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($esercizio->sottoargomento_id == $row_sottoargomento['id']) ? "selected" : "";
                    echo "<option value='" . $row_sottoargomento['id'] . "' $selected>" . $row_sottoargomento['titolo'] . "</option>";
                }
                
                echo "</select>";
            } else {
                echo "<input type='hidden' name='sottoargomento_id' value='$sottoargomento_id'>";
                echo "<div class='form-group'>";
                echo "<label>Sottoargomento</label>";
                echo "<div class='form-control-static'>" . $sottoargomento_info['titolo'] . "</div>";
                echo "</div>";
            }
            
            echo "<label for='titolo'>Titolo Esercizio</label>";
            echo "<input type='text' name='titolo' value='" . $esercizio->titolo . "' required>";
            
            echo "<label for='testo'>Testo dell'Esercizio</label>";
            echo "<textarea name='testo' rows='6'>" . $esercizio->testo . "</textarea>";
            
            echo "<label for='soluzione'>Soluzione</label>";
            echo "<textarea name='soluzione' rows='6'>" . $esercizio->soluzione . "</textarea>";
            
            echo "<label for='difficolta'>Livello di Difficoltà</label>";
            echo "<select name='difficolta'>";
            echo "<option value='1'" . ($esercizio->difficolta == 1 ? " selected" : "") . ">Facile</option>";
            echo "<option value='2'" . ($esercizio->difficolta == 2 ? " selected" : "") . ">Media</option>";
            echo "<option value='3'" . ($esercizio->difficolta == 3 ? " selected" : "") . ">Difficile</option>";
            echo "</select>";
            
            echo "<button type='submit' name='update'>Aggiorna Esercizio</button>";
            echo "<a href='esercizi.php" . ($sottoargomento_id ? "?sottoargomento_id=$sottoargomento_id" : "") . "' class='btn-secondary'>Annulla</a>";
            echo "</form>";
            echo "</div>";
        }
    }
    
    // Form per creare un nuovo esercizio (inizialmente nascosto)
    echo "<div id='createFormContainer' style='display: none;'>";
    echo "<h2>Crea Nuovo Esercizio</h2>";
    echo "<form action='' method='POST'>";
    
    // Carica tutti i sottoargomenti per il menu a tendina
    if ($sottoargomento_id) {
        // Se siamo in una pagina di sottoargomento specifico, usa quel sottoargomento
        echo "<input type='hidden' name='sottoargomento_id' value='$sottoargomento_id'>";
        echo "<div class='form-group'>";
        echo "<label>Sottoargomento</label>";
        echo "<div class='form-control-static'>" . $sottoargomento_info['titolo'] . "</div>";
        echo "</div>";
    } else {
        // Altrimenti mostra il menu a tendina
        $stmt_sottoargomenti = $sottoargomento->readAll();
        
        echo "<label for='sottoargomento_id'>Sottoargomento</label>";
        echo "<select name='sottoargomento_id' required>";
        
        while ($row_sottoargomento = $stmt_sottoargomenti->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . $row_sottoargomento['id'] . "'>" . $row_sottoargomento['titolo'] . "</option>";
        }
        
        echo "</select>";
    }
    
    echo "<label for='titolo'>Titolo Esercizio</label>";
    echo "<input type='text' name='titolo' required>";
    
    echo "<label for='testo'>Testo dell'Esercizio</label>";
    echo "<textarea name='testo' rows='6'></textarea>";
    
    echo "<label for='soluzione'>Soluzione</label>";
    echo "<textarea name='soluzione' rows='6'></textarea>";
    
    echo "<label for='difficolta'>Livello di Difficoltà</label>";
    echo "<select name='difficolta'>";
    echo "<option value='1'>Facile</option>";
    echo "<option value='2' selected>Media</option>";
    echo "<option value='3'>Difficile</option>";
    echo "</select>";
    
    echo "<button type='submit' name='create'>Crea Esercizio</button>";
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