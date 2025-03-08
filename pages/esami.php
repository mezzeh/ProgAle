<?php
// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/esame.php';
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
    // Istanza dei modelli
    $esame = new Esame($db);
    $piano = new PianoDiStudio($db);
    
    // Se è stato selezionato un piano di studio, mostra solo gli esami di quel piano
    $piano_id = isset($_GET['piano_id']) ? $_GET['piano_id'] : null;
    
    if ($piano_id) {
        $piano->id = $piano_id;
        $piano_info = $piano->readOne();
        if (!empty($piano_info)) {
            echo "<div class='breadcrumb'>";
            echo "<ul>";
            echo "<li><a href='index.php'>Piani di Studio</a></li>";
            echo "<li>" . $piano_info['nome'] . "</li>";
            echo "</ul>";
            echo "</div>";
        }
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
    
    // --- PRIMA MOSTRA LA LISTA DEGLI ESAMI ESISTENTI ---
    echo "<div class='header-with-button'>";
    if ($piano_id) {
        echo "<h2>Esami del Piano: " . $piano_info['nome'] . "</h2>";
    } else {
        echo "<h2>Tutti gli Esami</h2>";
    }
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Esame</button>";
    echo "</div>";
    
    // Leggi tutti gli esami o gli esami di un piano specifico
    if ($piano_id) {
        $stmt = $esame->readByPiano($piano_id);
    } else {
        $stmt = $esame->readAll();
    }
    
    // Conta gli esami
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Mostra piano di studio solo se stiamo visualizzando tutti gli esami
            $piano_info_display = isset($piano_nome) ? "<div class='item-meta'>Piano: $piano_nome</div>" : "";
            
            echo "<li>
                    <div class='item-title'>$nome</div>
                    <div class='item-meta'>Codice: $codice | Crediti: $crediti</div>
                    $piano_info_display
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
    
    // --- POI MOSTRA I FORM DI MODIFICA/CREAZIONE ---
    
    // Form per modificare un esame
    if (isset($_GET['edit'])) {
        $esame->id = $_GET['edit'];
        if ($esame->readOne()) {
            echo "<div id='editFormContainer'>";
            echo "<h2>Modifica Esame</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $esame->id . "'>";
            
            // Carica tutti i piani di studio per il menu a tendina
            $stmt_piani = $piano->readAll();
            
            echo "<label for='piano_id'>Piano di Studio</label>";
            echo "<select name='piano_id' required>";
            
            while ($row_piano = $stmt_piani->fetch(PDO::FETCH_ASSOC)) {
                $selected = ($esame->piano_id == $row_piano['id']) ? "selected" : "";
                echo "<option value='" . $row_piano['id'] . "' $selected>" . $row_piano['nome'] . "</option>";
            }
            
            echo "</select>";
            
            echo "<label for='nome'>Nome Esame</label>";
            echo "<input type='text' name='nome' value='" . $esame->nome . "' required>";
            
            echo "<label for='codice'>Codice Esame</label>";
            echo "<input type='text' name='codice' value='" . $esame->codice . "'>";
            
            echo "<label for='crediti'>Crediti</label>";
            echo "<input type='number' name='crediti' value='" . $esame->crediti . "' min='1' max='30'>";
            
            echo "<label for='descrizione'>Descrizione</label>";
            echo "<textarea name='descrizione'>" . $esame->descrizione . "</textarea>";
            
            echo "<button type='submit' name='update'>Aggiorna Esame</button>";
            echo "<a href='esami.php" . ($piano_id ? "?piano_id=$piano_id" : "") . "' class='btn-secondary'>Annulla</a>";
            echo "</form>";
            echo "</div>";
        }
    }
    
    // Form per creare un nuovo esame (inizialmente nascosto)
    echo "<div id='createFormContainer' style='display: none;'>";
    echo "<h2>Crea Nuovo Esame</h2>";
    echo "<form action='' method='POST'>";
    
    // Carica tutti i piani di studio per il menu a tendina
    if ($piano_id) {
        // Se siamo in una pagina di piano specifico, usa quel piano
        echo "<input type='hidden' name='piano_id' value='$piano_id'>";
        echo "<div class='form-group'>";
        echo "<label>Piano di Studio</label>";
        echo "<div class='form-control-static'>" . $piano_info['nome'] . "</div>";
        echo "</div>";
    } else {
        // Altrimenti mostra il menu a tendina
        $stmt_piani = $piano->readAll();
        
        echo "<label for='piano_id'>Piano di Studio</label>";
        echo "<select name='piano_id' required>";
        
        while ($row_piano = $stmt_piani->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . $row_piano['id'] . "'>" . $row_piano['nome'] . "</option>";
        }
        
        echo "</select>";
    }
    
    echo "<label for='nome'>Nome Esame</label>";
    echo "<input type='text' name='nome' required>";
    
    echo "<label for='codice'>Codice Esame</label>";
    echo "<input type='text' name='codice'>";
    
    echo "<label for='crediti'>Crediti</label>";
    echo "<input type='number' name='crediti' min='1' max='30' value='6'>";
    
    echo "<label for='descrizione'>Descrizione</label>";
    echo "<textarea name='descrizione'></textarea>";
    
    echo "<button type='submit' name='create'>Crea Esame</button>";
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