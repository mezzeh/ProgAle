<?php
ob_start();
// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/piano_di_studio.php';
include_once '../models/esame.php';
include_once '../models/comments.php';
include_once 'components/comments/comments.php';

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
                    echo "<a href='esami.php?id=$id'>Visualizza Argomenti</a>";
                    
                    // Mostra opzioni di modifica se l'utente è proprietario o admin
                    if ($piano_info['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']) {
                        echo " | <a href='esami.php?edit=$id&piano_id=" . $piano_info['id'] . "'>Modifica</a>";
                    }
                } else {
                    echo "<a href='esame.php?id=$id'>Visualizza Argomenti</a>";
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
<?php
// Assicurati di aver incluso questi file all'inizio di view_piano.php


// Gestione e rendering dei commenti
$piano_id = $piano_info['id'];

// Gestione dei commenti (aggiunta, modifica, eliminazione)
$risultato_commenti = gestioneCommentiPiani($db, $piano_id);

// Se c'è un risultato con redirect, esegui il redirect
if ($risultato_commenti && isset($risultato_commenti['redirect'])) {
    header("Location: " . $risultato_commenti['redirect']);
    exit;
}

// Mostra eventuali messaggi
if ($risultato_commenti && !empty($risultato_commenti['message'])) {
    echo "<div class='message {$risultato_commenti['message_class']}'>{$risultato_commenti['message']}</div>";
}
ob_end_flush();
// Rendering dei commenti
renderCommentiPiani($db, $piano_id);
?>

<?php include_once '../ui/includes/footer.php'; ?>