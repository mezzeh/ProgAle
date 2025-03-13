<?php
// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/piano_di_studio.php';
include_once '../models/esame.php';
include_once '../models/commento.php';

// Verifica se è stato specificato un piano
if (!isset($_GET['id'])) {
    echo "<div class='message error'>Piano di studio non specificato.</div>";
    include_once '../ui/includes/footer.php';
    exit;
}

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div class='message error'>Problema di connessione al database.</div>";
    include_once '../ui/includes/footer.php';
    exit;
}

// Carica il piano di studio
$piano = new PianoDiStudio($db);
$piano->id = $_GET['id'];
$piano_info = $piano->readOne();

// Verifica se il piano esiste e se l'utente può visualizzarlo
if (!$piano_info) {
    echo "<div class='message error'>Piano di studio non trovato.</div>";
    include_once '../ui/includes/footer.php';
    exit;
}

// Se il piano è privato, verifica se l'utente è il proprietario o admin
if ($piano_info['visibility'] == 'private' && 
    (!isset($_SESSION['user_id']) || 
     ($piano_info['user_id'] != $_SESSION['user_id'] && !$_SESSION['is_admin']))) {
    echo "<div class='message error'>Non hai i permessi per visualizzare questo piano di studio.</div>";
    include_once '../ui/includes/footer.php';
    exit;
}

// Ottieni il nome dell'utente che ha creato il piano
$user_query = "SELECT username FROM users WHERE id = :user_id LIMIT 1";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(":user_id", $piano_info['user_id']);
$user_stmt->execute();
$user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);
$creator = $user_row ? $user_row['username'] : "Utente sconosciuto";

// Crea un'istanza dell'oggetto commento qui
$commento = new Commento($db);
$comment_count = $commento->countByElemento('piano', $piano_info['id']);

// Gestione aggiunta commento
if(isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $commento->user_id = $_SESSION['user_id'];
    $commento->tipo_elemento = $_POST['comment_tipo'];
    $commento->elemento_id = $_POST['comment_elemento_id'];
    $commento->testo = $_POST['comment_text'];
    
    if($commento->create()) {
        echo '<script>window.location.href = "view_piano.php?id='.$piano_info['id'].'&comment_added=1";</script>';
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
    
    echo '<script>window.location.href = "view_piano.php?id='.$piano_info['id'].'&comment_updated=1";</script>';
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
    
    echo '<script>window.location.href = "view_piano.php?id='.$piano_info['id'].'&comment_deleted=1";</script>';
    exit;
}
?>

<div class="breadcrumb">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><?php echo $piano_info['nome']; ?></li>
    </ul>
</div>

<div class="piano-view">
    <h2><?php echo $piano_info['nome']; ?></h2>
    
    <div class="piano-meta">
        <p>Creato da: <?php echo $creator; ?></p>
        <p>Data creazione: <?php echo date('d/m/Y', strtotime($piano_info['data_creazione'])); ?></p>
        <p>Visibilità: <?php echo ($piano_info['visibility'] == 'public') ? "Pubblico" : "Privato"; ?></p>
    </div>
    
    <div class="piano-description">
        <h3>Descrizione</h3>
        <p><?php echo $piano_info['descrizione']; ?></p>
    </div>
    
    <div class="piano-esami">
        <h3>Esami del Piano di Studio</h3>
        
        <?php
        // Carica gli esami associati al piano
        $esame = new Esame($db);
        $stmt = $esame->readByPiano($piano_info['id']);
        
        if ($stmt->rowCount() > 0) {
            echo "<ul class='item-list'>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                echo "<li>
                        <div class='item-title'>$nome</div>
                        <div class='item-meta'>Codice: $codice | Crediti: $crediti</div>
                        <div class='item-description'>$descrizione</div>
                        <div class='item-actions'>";
                
                if (isset($_SESSION['user_id'])) {
                    echo "<a href='esami.php?esame_id=$id'>Visualizza Esame</a>";
                    
                    // Mostra opzioni di modifica se l'utente è proprietario o admin
                    if ($piano_info['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']) {
                        echo " | <a href='esami.php?edit=$id&piano_id=" . $piano_info['id'] . "'>Modifica</a>";
                    }
                } else {
                    echo "<a href='esami.php?esame_id=$id'>Visualizza Esame</a>";
                }
                
                echo "</div>
                    </li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Nessun esame trovato per questo piano di studio.</p>";
        }
        ?>
    </div>
    
    <?php if (isset($_SESSION['user_id']) && ($piano_info['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin'])): ?>
    <div class="piano-actions">
        <a href="esami.php?piano_id=<?php echo $piano_info['id']; ?>" class="btn-primary">Gestisci Esami</a>
        <a href="index.php?edit=<?php echo $piano_info['id']; ?>" class="btn-secondary">Modifica Piano</a>
    </div>
    <?php elseif (!isset($_SESSION['user_id'])): ?>
    <div class="piano-actions">
        <p>Per creare il tuo piano di studio, <a href="login.php">accedi</a> o <a href="register.php">registrati</a>.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Sezione Commenti -->
<div class="comments-section">
    <h3>Commenti (<?php echo $comment_count; ?>)</h3>
    
    <?php if(isset($_SESSION['user_id'])): ?>
    <!-- Form per aggiungere un commento -->
    <div class="comment-form">
        <form action="" method="POST">
            <input type="hidden" name="comment_tipo" value="piano">
            <input type="hidden" name="comment_elemento_id" value="<?php echo $piano_info['id']; ?>">
            <textarea name="comment_text" placeholder="Aggiungi un commento..." required></textarea>
            <button type="submit" name="add_comment" class="btn-primary">Commenta</button>
        </form>
    </div>
    <?php else: ?>
    <p><a href="login.php">Accedi</a> per aggiungere un commento.</p>
    <?php endif; ?>
    
    <!-- Lista dei commenti -->
    <div class="comments-list">
        <?php
        $stmt = $commento->readByElemento('piano', $piano_info['id']);
        
        if($stmt->rowCount() > 0) {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="comment-item" id="comment-'.$row['id'].'">';
                echo '<div class="comment-header">';
                echo '<span class="comment-author">'.$row['username'].'</span>';
                echo '<span class="comment-date">'.date('d/m/Y H:i', strtotime($row['data_creazione'])).'</span>';
                echo '</div>';
                echo '<div class="comment-content">'.$row['testo'].'</div>';
                
                // Opzioni per modificare/eliminare (solo per l'autore o admin)
                if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $row['user_id'] || $_SESSION['is_admin'])) {
                    echo '<div class="comment-actions">';
                    echo '<a href="#" class="edit-comment-btn" data-id="'.$row['id'].'">Modifica</a> | ';
                    echo '<a href="?id='.$piano_info['id'].'&delete_comment='.$row['id'].'" 
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
        ?>
    </div>
</div>

<!-- JavaScript per la gestione dei commenti -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestione pulsanti di modifica
    const editButtons = document.querySelectorAll('.edit-comment-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const commentId = this.getAttribute('data-id');
            const commentContent = document.querySelector(`#comment-${commentId} .comment-content`);
            const editForm = document.querySelector(`#edit-form-${commentId}`);
            
            // Nascondi il contenuto e mostra il form
            commentContent.style.display = 'none';
            editForm.style.display = 'block';
        });
    });
    
    // Gestione pulsanti annulla modifica
    const cancelButtons = document.querySelectorAll('.cancel-edit');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.edit-comment-form');
            const commentId = form.id.replace('edit-form-', '');
            const commentContent = document.querySelector(`#comment-${commentId} .comment-content`);
            
            // Nascondi il form e mostra il contenuto
            form.style.display = 'none';
            commentContent.style.display = 'block';
        });
    });
});
</script>

<?php include_once '../ui/includes/footer.php'; ?>