<?php
// File: pages/view_pages/view_esercizio.php

ob_start();

// Includi header
include_once '../../ui/includes/header.php'; // Aggiornato il percorso

// Includi file di configurazione e modelli
include_once '../../config/database.php'; // Aggiornato il percorso
include_once '../../models/esercizio.php'; // Aggiornato il percorso
include_once '../../models/sottoargomento.php'; // Aggiornato il percorso
include_once '../../models/argomento.php'; // Aggiornato il percorso
include_once '../../models/esame.php'; // Aggiornato il percorso
include_once '../../models/comments.php'; // Aggiornato il percorso
include_once '../components/comments/comments.php'; // Aggiornato il percorso

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div class='message error'>Problema di connessione al database.</div>";
    include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
    exit;
}

// Inizializza modelli
$esercizio = new Esercizio($db);
$sottoargomento = new SottoArgomento($db);
$argomento = new Argomento($db);
$esame = new Esame($db);

// Parametri GET
$esercizio_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$esercizio_id) {
    echo "<div class='message error'>Nessun esercizio specificato.</div>";
    include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
    exit;
}

// Carica i dettagli dell'esercizio
$esercizio->id = $esercizio_id;
$esercizio_info = $esercizio->readOne();

if (!$esercizio_info) {
    echo "<div class='message error'>Esercizio non trovato.</div>";
    include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
    exit;
}

// Carica le informazioni sul sottoargomento
$sottoargomento->id = $esercizio_info['sottoargomento_id'];
$sottoargomento_info = $sottoargomento->readOne();

// Carica le informazioni sull'argomento
$argomento->id = $sottoargomento_info['argomento_id'];
$argomento_info = $argomento->readOne();

// Carica le informazioni sull'esame
$esame->id = $argomento_info['esame_id'];
$esame_info = $esame->readOne();

// Includi breadcrumb
include_once '../components/shared/breadcrumb.php'; // Aggiornato il percorso

// Genera il breadcrumb
$breadcrumb_items = [
    ['text' => 'Home', 'link' => '../index.php'], // Aggiornato il percorso
    ['text' => $esame_info['nome'], 'link' => 'view_esame.php?id=' . $esame_info['id']],
    ['text' => $argomento_info['titolo'], 'link' => 'view_argomento.php?id=' . $argomento_info['id']],
    ['text' => $sottoargomento_info['titolo'], 'link' => '../sottoargomenti.php?argomento_id=' . $argomento_info['id']], // Aggiornato il percorso
    ['text' => $esercizio_info['titolo']]
];
generaBreadcrumb($breadcrumb_items);
?>

<div class="esercizio-view">
    <h2><?php echo htmlspecialchars($esercizio_info['titolo']); ?></h2>
    
    <div class="esercizio-meta">
        <p>Difficoltà: 
            <?php 
            switch($esercizio_info['difficolta']) {
                case 1: echo "Facile"; break;
                case 2: echo "Media"; break;
                case 3: echo "Difficile"; break;
                default: echo "Non specificata";
            }
            ?>
        </p>
    </div>
    
    <div class="esercizio-content">
        <h3>Testo dell'Esercizio</h3>
        <div class="esercizio-text">
            <?php echo nl2br(htmlspecialchars($esercizio_info['testo'])); ?>
        </div>
        
        <div class="solution-toggle">
            <button id="show-solution" class="btn-primary">Mostra Soluzione</button>
        </div>
        
        <div id="solution-content" class="esercizio-solution" style="display: none;">
            <h3>Soluzione</h3>
            <?php echo nl2br(htmlspecialchars($esercizio_info['soluzione'])); ?>
        </div>
    </div>
    
    <div class="esercizio-actions">
        <a href="../requisiti.php?esercizio_id=<?php echo $esercizio_info['id']; ?>" class="btn-primary">Requisiti</a> <!-- Aggiornato il percorso -->
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../esercizi.php?edit=<?php echo $esercizio_info['id']; ?>&sottoargomento_id=<?php echo $sottoargomento_info['id']; ?>" class="btn-secondary">Modifica Esercizio</a> <!-- Aggiornato il percorso -->
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showSolutionBtn = document.getElementById('show-solution');
    const solutionContent = document.getElementById('solution-content');
    
    if (showSolutionBtn && solutionContent) {
        showSolutionBtn.addEventListener('click', function() {
            if (solutionContent.style.display === 'none') {
                solutionContent.style.display = 'block';
                showSolutionBtn.textContent = 'Nascondi Soluzione';
            } else {
                solutionContent.style.display = 'none';
                showSolutionBtn.textContent = 'Mostra Soluzione';
            }
        });
    }
});
</script>
<?php

// Gestione e rendering dei commenti
$risultato_commenti = gestioneCommentiEsercizi($db, $esercizio_id);

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
renderCommentiEsercizi($db, $esercizio_id);

ob_end_flush();

include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
?>