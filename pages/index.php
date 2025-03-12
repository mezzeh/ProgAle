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
    if (isset($_POST['create']) && isset($_SESSION['user_id'])) {
        $piano->nome = $_POST['nome'];
        $piano->descrizione = $_POST['descrizione'];
        $piano->user_id = $_SESSION['user_id'];
        $piano->visibility = $_POST['visibility'];

        if ($piano->create()) {
            $message = "Piano di studio creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare il piano di studio.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un piano di studio ---
    if (isset($_POST['update']) && isset($_SESSION['user_id'])) {
        $piano->id = $_POST['id'];
        $piano->nome = $_POST['nome'];
        $piano->descrizione = $_POST['descrizione'];
        $piano->visibility = $_POST['visibility'];

        // Verifica se l'utente è il proprietario o admin
        $owner_info = $piano->readOne();
        if($owner_info && ($owner_info['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin'])) {
            if ($piano->update()) {
                $message = "Piano di studio aggiornato con successo!";
                $message_class = "success";
            } else {
                $message = "Impossibile aggiornare il piano di studio.";
                $message_class = "error";
            }
        } else {
            $message = "Non hai i permessi per modificare questo piano di studio.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un piano ---
    if (isset($_GET['delete']) && isset($_SESSION['user_id'])) {
        $piano->id = $_GET['delete'];
        
        // Verifica se l'utente è il proprietario o admin
        $owner_info = $piano->readOne();
        if($owner_info && ($owner_info['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin'])) {
            if ($piano->delete()) {
                $message = "Piano di studio eliminato con successo!";
                $message_class = "success";
            } else {
                $message = "Impossibile eliminare il piano di studio.";
                $message_class = "error";
            }
        } else {
            $message = "Non hai i permessi per eliminare questo piano di studio.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- PRIMA MOSTRA I PIANI DI STUDIO PUBBLICI ---
    echo "<div class='header-with-button'>";
    echo "<h2>Piani di Studio Pubblici</h2>";
    if(isset($_SESSION['user_id'])) {
        echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Piano</button>";
    } else {
        echo "<p>Per creare il tuo piano di studio, <a href='login.php'>accedi</a> o <a href='register.php'>registrati</a>.</p>";
    }
    echo "</div>";
    
    // Leggi tutti i piani di studio pubblici
    $stmt = $piano->readPublic();
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            // Ottieni il nome dell'utente che ha creato il piano
            $user_query = "SELECT username FROM users WHERE id = :user_id LIMIT 1";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(":user_id", $user_id);
            $user_stmt->execute();
            $user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);
            $creator = $user_row ? $user_row['username'] : "Utente sconosciuto";
            
            echo "<li>
                    <div class='item-title'>$nome</div>
                    <div class='item-meta'>Creato da: $creator</div>
                    <div class='item-description'>$descrizione</div>
                    <div class='item-actions'>
                        <a href='view_piano.php?id=$id'>Visualizza</a>";
            
            // Mostra i pulsanti di modifica solo se l'utente è loggato ed è il proprietario o admin
            if(isset($_GET['action']) && $_GET['action'] == 'redirected') {
    $message = "Sei stato reindirizzato alla pagina 'I Miei Piani' per modificare il tuo piano di studio.";
    $message_class = "info";
}
            
            echo "</div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun piano di studio pubblico trovato.</p>";
    }
    
    // Se l'utente è loggato, mostra anche i suoi piani privati
    if(isset($_SESSION['user_id'])) {
    echo '<div class="header-with-button">';
    echo '<h2><a href="my_piani.php" class="btn-primary">I miei piani di studio</a></h2>';
    echo '</div>';
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        // contenuto della lista
        echo "</ul>";
    } else {
        echo "<p>Non hai ancora creato piani di studio.</p>";
    


        }
        
          
        // Form per creare un nuovo piano di studio (inizialmente nascosto)
        echo "<div id='createFormContainer' style='display: none;'>";
        echo "<h2>Crea Nuovo Piano di Studio</h2>";
        echo "<form action='' method='POST'>";
        echo "<label for='nome'>Nome</label>";
        echo "<input type='text' name='nome' required>";
        echo "<label for='descrizione'>Descrizione</label>";
        echo "<textarea name='descrizione' required></textarea>";
        
        // Selector for visibility
        echo "<label for='visibility'>Visibilità</label>";
        echo "<select name='visibility'>";
        echo "<option value='private'>Privato</option>";
        echo "<option value='public'>Pubblico</option>";
        echo "</select>";
        
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
}

// Includi footer
include_once '../ui/includes/footer.php';
?>