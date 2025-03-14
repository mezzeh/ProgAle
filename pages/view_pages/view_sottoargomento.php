<?php
// File: pages/view_pages/view_sottoargomento.php

ob_start();

// Includi header
include_once '../../ui/includes/header.php'; // Aggiornato il percorso

// Includi file di configurazione e modelli
include_once '../../config/database.php'; // Aggiornato il percorso
include_once '../../models/sottoargomento.php'; // Aggiornato il percorso
include_once '../../models/argomento.php'; // Aggiornato il percorso
include_once '../../models/esame.php'; // Aggiornato il percorso
include_once '../../models/esercizio.php'; // Aggiornato il percorso

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div class='message error'>Problema di connessione al database.</div>";
    include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
    exit;
}

// Inizializza modelli
$sottoargomento = new SottoArgomento($db);
$argomento = new Argomento($db);
$esame = new Esame($db);
$esercizio = new Esercizio($db);

// Parametri GET
$sottoargomento_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sottoargomento_id) {
    echo "<div class='message error'>Nessun sottoargomento specificato.</div>";
    include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
    exit;
}

// Carica i dettagli del sottoargomento
$sottoargomento->id = $sottoargomento_id;
$sottoargomento_info = $sottoargomento->readOne();

if (!$sottoargomento_info) {
    echo "<div class='message error'>Sottoargomento non trovato.</div>";
    include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
    exit;
}

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
    ['text' => $sottoargomento_info['titolo']]
];
generaBreadcrumb($breadcrumb_items);
?>

<div class="sottoargomento-view">
    <h2><?php echo htmlspecialchars($sottoargomento_info['titolo']); ?></h2>
    
    <div class="sottoargomento-meta">
        <p>Livello di profondità: <?php echo $sottoargomento_info['livello_profondita']; ?></p>
    </div>
    
    <div class="sottoargomento-description">
        <h3>Descrizione</h3>
        <div class="description-content">
            <?php echo nl2br(htmlspecialchars($sottoargomento_info['descrizione'])); ?>
        </div>
    </div>
    <?php
// Aggiungi questa sezione in pages/view_pages/view_sottoargomento.php dopo la descrizione del sottoargomento

// Mostra i prerequisiti
echo "<div class='prerequisites-section'>";
echo "<h3>Prerequisiti</h3>";

// Ottieni gli argomenti prerequisiti
$argomenti_prereq = $sottoargomento->getArgomentiPrerequisiti($sottoargomento_id);
$sottoargomenti_prereq = $sottoargomento->getSottoargomentiPrerequisiti($sottoargomento_id);

$has_prerequisites = false;

// Mostra argomenti prerequisiti
if ($argomenti_prereq && $argomenti_prereq->rowCount() > 0) {
    echo "<div class='prerequisite-group'>";
    echo "<h4>Argomenti</h4>";
    echo "<ul class='prerequisites-list'>";
    
    while ($row = $argomenti_prereq->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>";
        echo "<span class='badge badge-argomento'>A</span> ";
        echo "<a href='view_argomento.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['titolo']) . "</a>";
        echo "</li>";
    }
    
    echo "</ul>";
    echo "</div>";
    
    $has_prerequisites = true;
}

// Mostra sottoargomenti prerequisiti
if ($sottoargomenti_prereq && $sottoargomenti_prereq->rowCount() > 0) {
    echo "<div class='prerequisite-group'>";
    echo "<h4>Sottoargomenti</h4>";
    echo "<ul class='prerequisites-list'>";
    
    while ($row = $sottoargomenti_prereq->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>";
        echo "<span class='badge badge-sottoargomento'>S</span> ";
        echo "<a href='view_sottoargomento.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['titolo']) . "</a>";
        echo "</li>";
    }
    
    echo "</ul>";
    echo "</div>";
    
    $has_prerequisites = true;
}

if (!$has_prerequisites) {
    echo "<p>Questo sottoargomento non ha prerequisiti.</p>";
}

echo "</div>";
?>
    <div class="related-exercises">
        <h3>Esercizi relativi a questo sottoargomento</h3>
        
        <?php
        // Carica gli esercizi associati al sottoargomento
        $stmt = $esercizio->readBySottoArgomento($sottoargomento_id);
        
        if ($stmt->rowCount() > 0) {
            echo "<ul class='item-list'>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                // Determina la classe CSS in base alla difficoltà
                $difficolta_class = "difficulty-$difficolta";
                $difficolta_text = ($difficolta == 1) ? "Facile" : (($difficolta == 2) ? "Media" : "Difficile");
                
                echo "<li class='$difficolta_class'>
                        <div class='item-title'>" . htmlspecialchars($titolo) . "</div>
                        <div class='item-meta'>Difficoltà: $difficolta_text</div>
                        <div class='item-description'>" . nl2br(htmlspecialchars(substr($testo, 0, 150))) . (strlen($testo) > 150 ? "..." : "") . "</div>
                        <div class='item-actions'>
                            <a href='view_esercizio.php?id=$id'>Visualizza Esercizio</a>
                        </div>
                    </li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Nessun esercizio trovato per questo sottoargomento.</p>";
            if (isset($_SESSION['user_id'])) {
                echo "<a href='../esercizi.php?sottoargomento_id={$sottoargomento_id}' class='btn-primary'>Aggiungi Esercizi</a>"; // Aggiornato il percorso
            }
        }
        ?>
    </div>
    
    <div class="sottoargomento-actions">
        <a href="../esercizi.php?sottoargomento_id=<?php echo $sottoargomento_id; ?>" class="btn-primary">Gestisci Esercizi</a> <!-- Aggiornato il percorso -->
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../sottoargomenti.php?edit=<?php echo $sottoargomento_id; ?>&argomento_id=<?php echo $argomento_info['id']; ?>" class="btn-secondary">Modifica Sottoargomento</a> <!-- Aggiornato il percorso -->
        <?php endif; ?>
    </div>
</div>

<?php
ob_end_flush();

include_once '../../ui/includes/footer_view.php'; // Aggiornato il percorso
?>