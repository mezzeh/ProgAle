<?php
// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/esame.php';
include_once '../models/piano_di_studio.php';
include_once '../models/commento.php';


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
    $commento = new Commento($db);
    
    // Verifica se stiamo visualizzando un singolo esame
    $single_view = isset($_GET['id']);
    
    if ($single_view) {
        $esame->id = $_GET['id'];
        $esame_info = $esame->readOne();
        
        if (empty($esame_info)) {
            echo "<div class='message error'>Esame non trovato.</div>";
            include_once '../ui/includes/footer.php';
            exit;
        }
        
        // Carica informazioni del piano per controllo permessi
        if (isset($esame_info['piano_id'])) {
            $piano->id = $esame_info['piano_id'];
            $piano_details = $piano->readOne();
        }
        
        // Conteggio commenti per questo esame
        $comment_count = $commento->countByElemento('esame', $esame_info['id']);
        
        // Gestione aggiunta commento
        if(isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
            $commento->user_id = $_SESSION['user_id'];
            $commento->tipo_elemento = $_POST['comment_tipo'];
            $commento->elemento_id = $_POST['comment_elemento_id'];
            $commento->testo = $_POST['comment_text'];
            
            if($commento->create()) {
                header("Location: esami.php?id=" . $esame_info['id'] . "&comment_added=1");
                exit;
            }
        }

        // Gestione aggiornamento commento
        if(isset($_POST['update_comment']) && isset($_SESSION['user_id'])) {
            $commento->id = $_POST['comment_id'];
            $commento->user_id = $_SESSION['user_id'];
            $commento->testo = $_POST['comment_text'];
            
            // Se è admin, può modificare qualsiasi commento
            if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                $commento->readOne();
                $commento->update();
            } else {
                $commento->update();
            }
            
            header("Location: esami.php?id=" . $esame_info['id'] . "&comment_updated=1");
            exit;
        }

        // Gestione eliminazione commento
        if(isset($_GET['delete_comment']) && isset($_SESSION['user_id'])) {
            $commento->id = $_GET['delete_comment'];
            $commento->user_id = $_SESSION['user_id'];
            
            // Se è admin, può eliminare qualsiasi commento
            if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                $commento->deleteByAdmin();
            } else {
                $commento->delete();
            }
            
            header("Location: esami.php?id=" . $esame_info['id'] . "&comment_deleted=1");
            exit;
        }
    }
    
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
    if (isset($_POST['create']) && isset($_SESSION['user_id'])) {
        $esame->piano_id = $_POST['piano_id'];
        $esame->nome = $_POST['nome'];
        $esame->codice = $_POST['codice'];
        $esame->crediti = $_POST['crediti'];
        $esame->descrizione = $_POST['descrizione'];

        // Verifica che l'utente sia il proprietario del piano o admin
        $piano->id = $esame->piano_id;
        $piano_details = $piano->readOne();
        
        if ($piano_details && ($piano_details['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            if ($esame->create()) {
                $message = "Esame creato con successo!";
                $message_class = "success";
            } else {
                $message = "Impossibile creare l'esame.";
                $message_class = "error";
            }
        } else {
            $message = "Non hai i permessi per creare un esame in questo piano di studio.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un esame ---
    if (isset($_POST['update']) && isset($_SESSION['user_id'])) {
        $esame->id = $_POST['id'];
        $esame->piano_id = $_POST['piano_id'];
        $esame->nome = $_POST['nome'];
        $esame->codice = $_POST['codice'];
        $esame->crediti = $_POST['crediti'];
        $esame->descrizione = $_POST['descrizione'];

        // Verifica che l'utente sia il proprietario del piano o admin
        $piano->id = $esame->piano_id;
        $piano_details = $piano->readOne();
        
        if ($piano_details && ($piano_details['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            if ($esame->update()) {
                $message = "Esame aggiornato con successo!";
                $message_class = "success";
            } else {
                $message = "Impossibile aggiornare l'esame.";
                $message_class = "error";
            }
        } else {
            $message = "Non hai i permessi per modificare questo esame.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un esame ---
    if (isset($_GET['delete']) && isset($_SESSION['user_id'])) {
        $esame->id = $_GET['delete'];
        $temp_info = $esame->readOne();
        
        if ($temp_info) {
            // Verifica che l'utente sia il proprietario del piano o admin
            $piano->id = $temp_info['piano_id'];
            $piano_details = $piano->readOne();
            
            if ($piano_details && ($piano_details['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
                if ($esame->delete()) {
                    $message = "Esame eliminato con successo!";
                    $message_class = "success";
                } else {
                    $message = "Impossibile eliminare l'esame.";
                    $message_class = "error";
                }
            } else {
                $message = "Non hai i permessi per eliminare questo esame.";
                $message_class = "error";
            }
        } else {
            $message = "Esame non trovato.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- MOSTRA UN SINGOLO ESAME O LA LISTA DEGLI ESAMI ---
    
    if ($single_view) {
        // VISUALIZZAZIONE SINGOLO ESAME
        echo "<div class='breadcrumb'>";
        echo "<ul>";
        echo "<li><a href='index.php'>Piani di Studio</a></li>";
        if (isset($esame_info['piano_id'])) {
            echo "<li><a href='esami.php?piano_id=" . $esame_info['piano_id'] . "'>" . $esame_info['piano_nome'] . "</a></li>";
        }
        echo "<li>" . $esame_info['nome'] . "</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='esame-view'>";
        echo "<h2>" . $esame_info['nome'] . "</h2>";
        
        echo "<div class='esame-meta'>";
        echo "<p>Codice: " . $esame_info['codice'] . "</p>";
        echo "<p>Crediti: " . $esame_info['crediti'] . "</p>";
        echo "</div>";
        
        echo "<div class='esame-description'>";
        echo "<h3>Descrizione</h3>";
        echo "<p>" . $esame_info['descrizione'] . "</p>";
        echo "</div>";
        
        echo "<div class='esame-actions'>";
        echo "<a href='argomenti.php?esame_id=" . $esame_info['id'] . "' class='btn-primary'>Visualizza Argomenti</a>";
        
        // Verifica se l'utente è il proprietario del piano o admin
        if (isset($_SESSION['user_id']) && isset($piano_details) && 
            ($piano_details['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            echo " <a href='?edit=" . $esame_info['id'] . "' class='btn-secondary'>Modifica Esame</a>";
        }
        
        echo "</div>";
        echo "</div>";
        
        // Sezione Commenti (solo in visualizzazione singolo esame)
        echo "<div class='comments-section'>";
        echo "<h3>Commenti (" . $comment_count . ")</h3>";
        
        if(isset($_SESSION['user_id'])) {
            // Form per aggiungere un commento
            echo "<div class='comment-form'>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='comment_tipo' value='esame'>";
            echo "<input type='hidden' name='comment_elemento_id' value='" . $esame_info['id'] . "'>";
            echo "<textarea name='comment_text' placeholder='Aggiungi un commento...' required></textarea>";
            echo "<button type='submit' name='add_comment' class='btn-primary'>Commenta</button>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<p><a href='login.php'>Accedi</a> per aggiungere un commento.</p>";
        }
        
        // Lista dei commenti
        echo "<div class='comments-list'>";
        $stmt = $commento->readByElemento('esame', $esame_info['id']);
        
        if($stmt->rowCount() > 0) {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="comment-item" id="comment-'.$row['id'].'">';
                echo '<div class="comment-header">';
                echo '<span class="comment-author">'.$row['username'].'</span>';
                echo '<span class="comment-date">'.date('d/m/Y H:i', strtotime($row['data_creazione'])).'</span>';
                echo '</div>';
                echo '<div class="comment-content">'.$row['testo'].'</div>';
                
                // Opzioni per modificare/eliminare (solo per l'autore o admin)
                if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $row['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
                    echo '<div class="comment-actions">';
                    echo '<a href="#" class="edit-comment-btn" data-id="'.$row['id'].'">Modifica</a> | ';
                    echo '<a href="?id='.$esame_info['id'].'&delete_comment='.$row['id'].'" 
                          onclick="return confirm(\'Sei sicuro di voler eliminare questo commento?\');">Elimina</a>';
                    echo '</div>';
                    
                    // Form nascosto per la modifica (verrà mostrato con JavaScript)
                    echo '<div class="edit-comment-form" id="edit-form-'.$row['id'].'" style="display:none;">';
                    echo '<form action="" method="POST">';
                    echo '<input type="hidden" name="comment_id" value="'.$row['id'].'">';
                    echo '<textarea name="comment_text" required>'.$row['testo'].'</textarea>';
                    echo '<button type="submit" name="update_comment" class="btn-primary">Aggiorna</button>';
                    echo '<button type="button" class="cancel-edit btn-secondary">Annulla</button>';
                    echo '</form>';
                    echo '</div>';
                }
                
                echo '</div>';
            }
        } else {
            echo '<p>Nessun commento presente. Sii il primo a commentare!</p>';
        }
        echo "</div>"; // Fine comments-list
        echo "</div>"; // Fine comments-section
        
        // JavaScript per la gestione dei commenti
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestione pulsanti di modifica
            const editButtons = document.querySelectorAll('.edit-comment-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const commentId = this.getAttribute('data-id');
                    const commentContent = document.querySelector('#comment-' + commentId + ' .comment-content');
                    const editForm = document.querySelector('#edit-form-' + commentId);
                    
                    // Nascondi il contenuto e mostra il form
                    if (commentContent && editForm) {
                        commentContent.style.display = 'none';
                        editForm.style.display = 'block';
                    }
                });
            });
            
            // Gestione pulsanti annulla modifica
            const cancelButtons = document.querySelectorAll('.cancel-edit');
            cancelButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.edit-comment-form');
                    if (form) {
                        const commentId = form.id.replace('edit-form-', '');
                        const commentContent = document.querySelector('#comment-' + commentId + ' .comment-content');
                        
                        // Nascondi il form e mostra il contenuto
                        if (commentContent) {
                            form.style.display = 'none';
                            commentContent.style.display = 'block';
                        }
                    }
                });
            });
        });
        </script>";
    } else {
        // VISUALIZZAZIONE LISTA ESAMI
        echo "<div class='header-with-button'>";
        if ($piano_id) {
            echo "<h2>Esami del Piano: " . $piano_info['nome'] . "</h2>";
        } else {
            echo "<h2>Tutti gli Esami</h2>";
        }
        
        // Mostra il pulsante di aggiunta solo se l'utente è il proprietario del piano o admin
        if (isset($_SESSION['user_id']) && $piano_id && isset($piano_info) && 
            ($piano_info['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Esame</button>";
        } elseif (isset($_SESSION['user_id']) && !$piano_id && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Esame</button>";
        }
        
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
                            <a href='?id=$id'>Visualizza Dettagli</a> | 
                            <a href='argomenti.php?esame_id=$id'>Argomenti</a>";
                
                // Verifica se l'utente è il proprietario del piano o admin per mostrare le opzioni di modifica
                if (isset($_SESSION['user_id'])) {
                    $can_edit = false;
                    
                    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                        // Admin può sempre modificare
                        $can_edit = true;
                    } elseif ($piano_id && isset($piano_info) && $piano_info['user_id'] == $_SESSION['user_id']) {
                        // L'utente è il proprietario del piano specificato
                        $can_edit = true;
                    } elseif (!$piano_id) {
                        // Siamo nella lista di tutti gli esami, controlliamo il proprietario del piano di ciascun esame
                        if (isset($piano_id)) {
                            $temp_piano = new PianoDiStudio($db);
                            $temp_piano->id = $piano_id;
                            $temp_piano_info = $temp_piano->readOne();
                            
                            if ($temp_piano_info && $temp_piano_info['user_id'] == $_SESSION['user_id']) {
                                $can_edit = true;
                            }
                        }
                    }
                    
                    if ($can_edit) {
                        echo " | <a href='?edit=$id" . ($piano_id ? "&piano_id=$piano_id" : "") . "'>Modifica</a> | 
                                <a href='?delete=$id" . ($piano_id ? "&piano_id=$piano_id" : "") . "' onclick='return confirm(\"Sei sicuro di voler eliminare questo esame?\");'>Elimina</a>";
                    }
                }
                
                echo "</div>
                    </li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Nessun esame trovato." . ($piano_id ? " Aggiungi un esame a questo piano di studio." : "") . "</p>";
        }
        
        // --- FORM DI MODIFICA ---
        
        // Form per modificare un esame
        if (isset($_GET['edit']) && isset($_SESSION['user_id'])) {
            $esame->id = $_GET['edit'];
            $edit_esame_info = $esame->readOne();
            
            if ($edit_esame_info) {
                // Verifica che l'utente sia il proprietario del piano o admin
                $piano->id = $edit_esame_info['piano_id'];
                $edit_piano_info = $piano->readOne();
                
                if ($edit_piano_info && ($edit_piano_info['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
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
                } else {
                    echo "<div class='message error'>Non hai i permessi per modificare questo esame.</div>";
                }
            }
        }
        
        // --- FORM DI CREAZIONE ---
        
        // Form per creare un nuovo esame (inizialmente nascosto)
        if (isset($_SESSION['user_id']) && 
            (($piano_id && isset($piano_info) && 
             ($piano_info['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) ||
             (!$piano_id && isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            
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
    }
}

include_once '../ui/includes/footer.php';
?>