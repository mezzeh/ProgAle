<?php
ob_start();

// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/argomento.php';
include_once '../models/esame.php';
include_once '../models/piano_di_studio.php';
include_once '../models/commento.php';
include_once 'sezioni/commenti.php';

// Inizializza variabili
$message = "";
$message_class = "";

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div class='message error'>Problema di connessione al database.</div>";
    include_once '../ui/includes/footer.php';
    exit;
}

// Inizializza modelli
$argomento = new Argomento($db);
$esame = new Esame($db);
$piano = new PianoDiStudio($db);

// Parametri GET
$esame_id = isset($_GET['esame_id']) ? $_GET['esame_id'] : null;
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;

// Funzione per verificare i permessi
function verificaPermessiPiano($db, $esame_id) {
    $esame = new Esame($db);
    $piano = new PianoDiStudio($db);
    
    $esame->id = $esame_id;
    $esame_info = $esame->readOne();
    
    if (!$esame_info) {
        return false;
    }
    
    $piano->id = $esame_info['piano_id'];
    $piano_details = $piano->readOne();
    
    return ($piano_details && 
        (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] || 
         $piano_details['user_id'] == $_SESSION['user_id']));
}

// Gestione operazioni CRUD
if (isset($_SESSION['user_id'])) {
    // Creazione argomento
    if (isset($_POST['create']) && $esame_id) {
        if (verificaPermessiPiano($db, $esame_id)) {
            $argomento->esame_id = $esame_id;
            $argomento->titolo = $_POST['titolo'];
            $argomento->descrizione = $_POST['descrizione'];
            $argomento->livello_importanza = $_POST['livello_importanza'];

            if ($argomento->create()) {
                $message = "Argomento creato con successo!";
                $message_class = "success";
                header("Location: argomenti.php?esame_id=" . $esame_id);
                exit;
            } else {
                $message = "Impossibile creare l'argomento.";
                $message_class = "error";
            }
        } else {
            $message = "Non hai i permessi per creare un argomento in questo esame.";
            $message_class = "error";
        }
    }

    // Modifica argomento
    if (isset($_POST['update']) && $edit_id) {
        $argomento->id = $edit_id;
        $argomento_info = $argomento->readOne();
        
        if ($argomento_info) {
            $esame->id = $argomento_info['esame_id'];
            $esame_info = $esame->readOne();
            
            if (verificaPermessiPiano($db, $esame_info['id'])) {
                $argomento->titolo = $_POST['titolo'];
                $argomento->descrizione = $_POST['descrizione'];
                $argomento->livello_importanza = $_POST['livello_importanza'];

                if ($argomento->update()) {
                    $message = "Argomento aggiornato con successo!";
                    $message_class = "success";
                    header("Location: argomenti.php?esame_id=" . $esame_info['id']);
                    exit;
                } else {
                    $message = "Impossibile aggiornare l'argomento.";
                    $message_class = "error";
                }
            } else {
                $message = "Non hai i permessi per modificare questo argomento.";
                $message_class = "error";
            }
        }
    }

    // Eliminazione argomento
    if (isset($_GET['delete'])) {
        $argomento->id = $_GET['delete'];
        $argomento_info = $argomento->readOne();
        
        if ($argomento_info) {
            if (verificaPermessiPiano($db, $argomento_info['esame_id'])) {
                if ($argomento->delete()) {
                    $message = "Argomento eliminato con successo!";
                    $message_class = "success";
                    header("Location: argomenti.php?esame_id=" . $argomento_info['esame_id']);
                    exit;
                } else {
                    $message = "Impossibile eliminare l'argomento.";
                    $message_class = "error";
                }
            } else {
                $message = "Non hai i permessi per eliminare questo argomento.";
                $message_class = "error";
            }
        }
    }
}

// Visualizzazione breadcrumb
if ($esame_id) {
    $esame->id = $esame_id;
    $esame_info = $esame->readOne();
    
    echo "<div class='breadcrumb'>";
    echo "<ul>";
    echo "<li><a href='index.php'>Piani di Studio</a></li>";
    echo "<li><a href='esami.php'>Esami</a></li>";
    echo "<li>" . htmlspecialchars($esame_info['nome']) . "</li>";
    echo "</ul>";
    echo "</div>";
}

// Mostra messaggio
if (!empty($message)) {
    echo "<div class='message {$message_class}'>{$message}</div>";
}

// Intestazione pagina
echo "<div class='header-with-button'>";
echo "<h2>Argomenti" . ($esame_id ? " dell'Esame: " . htmlspecialchars($esame_info['nome']) : "") . "</h2>";

// Pulsante di aggiunta condizionale
if ($esame_id && verificaPermessiPiano($db, $esame_id)) {
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Argomento</button>";
}
echo "</div>";

// Recupera argomenti
$stmt = $esame_id ? $argomento->readByEsame($esame_id) : $argomento->readAll();
$num = $stmt->rowCount();

// Visualizzazione argomenti
if ($num > 0) {
    echo "<ul class='item-list'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        echo "<li class='importance-{$livello_importanza}'>";
        echo "<div class='item-title'>" . htmlspecialchars($titolo) . "</div>";
        echo "<div class='item-meta'>Importanza: {$livello_importanza}</div>";
        echo "<div class='item-description'>" . htmlspecialchars($descrizione) . "</div>";
        
        echo "<div class='item-actions'>";
        echo "<a href='sottoargomenti.php?argomento_id={$id}'>Sottoargomenti</a>";
        
        // Azioni di modifica/eliminazione condizionali
        if ($esame_id && verificaPermessiPiano($db, $esame_id)) {
            echo " | <a href='?edit={$id}&esame_id={$esame_id}'>Modifica</a>";
            echo " | <a href='?delete={$id}&esame_id={$esame_id}' onclick='return confirm(\"Sei sicuro di voler eliminare questo argomento?\");'>Elimina</a>";
        }
        echo "</div></li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nessun argomento trovato.</p>";
}

// Form di creazione argomento (nascosto di default)
if ($esame_id && verificaPermessiPiano($db, $esame_id)) {
    ?>
    <div id='createFormContainer' style='display: none;'>
        <h2>Crea Nuovo Argomento</h2>
        <form action="" method="POST">
            <input type="hidden" name="esame_id" value="<?php echo $esame_id; ?>">
            
            <label for="titolo">Titolo Argomento</label>
            <input type="text" name="titolo" required>
            
            <label for="descrizione">Descrizione</label>
            <textarea name="descrizione"></textarea>
            
            <label for="livello_importanza">Livello di Importanza</label>
            <select name="livello_importanza">
                <option value="1">Molto importante</option>
                <option value="2">Importante</option>
                <option value="3" selected>Media importanza</option>
                <option value="4">Poco importante</option>
                <option value="5">Marginale</option>
            </select>
            
            <button type="submit" name="create">Crea Argomento</button>
            <button type="button" id="cancelCreateBtn" class="btn-secondary">Annulla</button>
        </form>
    </div>

    <!-- Form di modifica (se richiesto) -->
    <?php if ($edit_id && verificaPermessiPiano($db, $esame_id)): 
        $argomento->id = $edit_id;
        $edit_argomento = $argomento->readOne();
    ?>
    <div id='editFormContainer'>
        <h2>Modifica Argomento</h2>
        <form action="?edit=<?php echo $edit_id; ?>&esame_id=<?php echo $esame_id; ?>" method="POST">
            <label for="titolo">Titolo Argomento</label>
            <input type="text" name="titolo" value="<?php echo htmlspecialchars($edit_argomento['titolo']); ?>" required>
            
            <label for="descrizione">Descrizione</label>
            <textarea name="descrizione"><?php echo htmlspecialchars($edit_argomento['descrizione']); ?></textarea>
            
            <label for="livello_importanza">Livello di Importanza</label>
            <select name="livello_importanza">
                <option value="1" <?php echo $edit_argomento['livello_importanza'] == 1 ? 'selected' : ''; ?>>Molto importante</option>
                <option value="2" <?php echo $edit_argomento['livello_importanza'] == 2 ? 'selected' : ''; ?>>Importante</option>
                <option value="3" <?php echo $edit_argomento['livello_importanza'] == 3 ? 'selected' : ''; ?>>Media importanza</option>
                <option value="4" <?php echo $edit_argomento['livello_importanza'] == 4 ? 'selected' : ''; ?>>Poco importante</option>
                <option value="5" <?php echo $edit_argomento['livello_importanza'] == 5 ? 'selected' : ''; ?>>Marginale</option>
            </select>
            
            <button type="submit" name="update">Aggiorna Argomento</button>
            <a href="argomenti.php?esame_id=<?php echo $esame_id; ?>" class="btn-secondary">Annulla</a>
        </form>
    </div>
    <?php endif; ?>
    <script>
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
    </script>
    <?php
}
// Nel file argomenti.php, sostituisci la sezione commenti con:
if ($esame_id) {
    // Gestione dei commenti
    $risultato_commenti = gestioneCommentiArgomenti($db, $esame_id, $argomento->id);

    // Se c'è un risultato con redirect, esegui il redirect
    if ($risultato_commenti && isset($risultato_commenti['redirect'])) {
        header("Location: " . $risultato_commenti['redirect']);
        exit;
    }

    // Mostra eventuali messaggi
    if ($risultato_commenti && !empty($risultato_commenti['message'])) {
        echo "<div class='message {$risultato_commenti['message_class']}'>{$risultato_commenti['message']}</div>";
    }

    // Rendering dei commenti
    renderCommentiArgomenti($db, $esame_id, $argomento->id);
}

ob_end_flush();

include_once '../ui/includes/footer.php';
?>