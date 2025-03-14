<?php
// File: pages/components/forms/edit_sottoargomento.php

// Form per modificare un sottoargomento
if (isset($_GET['edit']) && isset($_SESSION['user_id'])) {
    $sottoargomento->id = $_GET['edit'];
    if ($sottoargomento->readOne()) {
        // Ottieni gli argomenti prerequisiti
        $argomenti_prereq = $sottoargomento->getArgomentiPrerequisiti($sottoargomento->id);
        $argomenti_prereq_array = [];
        while ($row = $argomenti_prereq->fetch(PDO::FETCH_ASSOC)) {
            $argomenti_prereq_array[] = [
                'id' => $row['id'],
                'text' => $row['titolo']
            ];
        }
        
        // Ottieni i sottoargomenti prerequisiti
        $sottoargomenti_prereq = $sottoargomento->getSottoargomentiPrerequisiti($sottoargomento->id);
        $sottoargomenti_prereq_array = [];
        while ($row = $sottoargomenti_prereq->fetch(PDO::FETCH_ASSOC)) {
            $sottoargomenti_prereq_array[] = [
                'id' => $row['id'],
                'text' => $row['titolo']
            ];
        }
?>
<div id='editFormContainer'>
    <h2>Modifica Sottoargomento</h2>
    <form action='' method='POST'>
        <input type='hidden' name='id' value='<?php echo $sottoargomento->id; ?>'>
        
        <?php if (!$argomento_id): ?>
            <?php $stmt_argomenti = $argomento->readAll(); ?>
            
            <label for='argomento_id'>Argomento</label>
            <select name='argomento_id' required>
                <?php while ($row_argomento = $stmt_argomenti->fetch(PDO::FETCH_ASSOC)): ?>
                    <?php $selected = ($sottoargomento->argomento_id == $row_argomento['id']) ? "selected" : ""; ?>
                    <option value='<?php echo $row_argomento['id']; ?>' <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($row_argomento['titolo']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        <?php else: ?>
            <input type='hidden' name='argomento_id' value='<?php echo $argomento_id; ?>'>
            <div class='form-group'>
                <label>Argomento</label>
                <div class='form-control-static'><?php echo htmlspecialchars($argomento_info['titolo']); ?></div>
            </div>
        <?php endif; ?>
        
        <label for='titolo'>Titolo Sottoargomento</label>
        <input type='text' name='titolo' value='<?php echo htmlspecialchars($sottoargomento->titolo); ?>' required>
        
        <label for='descrizione'>Descrizione</label>
        <textarea name='descrizione'><?php echo htmlspecialchars($sottoargomento->descrizione); ?></textarea>
        
        <label for='livello_profondita'>Livello di Profondità (1-5)</label>
        <select name='livello_profondita'>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php $selected = ($sottoargomento->livello_profondita == $i) ? "selected" : ""; ?>
                <option value='<?php echo $i; ?>' <?php echo $selected; ?>>Livello <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        
        <!-- Sezione per prerequisiti -->
        <h3>Prerequisiti</h3>
        <div class="prerequisiti-section">
            <div class="search-container">
                <label for="prerequisiti-search">Cerca argomenti o sottoargomenti</label>
                <input type="text" id="prerequisiti-search" placeholder="Cerca prerequisiti...">
                <div id="search-results" class="search-results-dropdown"></div>
            </div>
            
            <div class="selected-prerequisites-container">
                <label>Prerequisiti selezionati</label>
                <div id="selected-prerequisites" class="selected-prerequisites"></div>
            </div>
            
            <!-- Input nascosti per memorizzare gli ID selezionati -->
            <input type="hidden" id="selected-argomenti" name="argomenti_prereq" value='<?php echo json_encode(array_column($argomenti_prereq_array, 'id')); ?>'>
            <input type="hidden" id="selected-sottoargomenti" name="sottoargomenti_prereq" value='<?php echo json_encode(array_column($sottoargomenti_prereq_array, 'id')); ?>'>
        </div>
        
        <button type='submit' name='update'>Aggiorna Sottoargomento</button>
        <a href='sottoargomenti.php<?php echo ($argomento_id ? "?argomento_id=$argomento_id" : ""); ?>' class='btn-secondary'>Annulla</a>
    </form>
</div>

<!-- Inizializza i dati per l'autocompletamento -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inizializza gli argomenti prerequisiti preselezionati
        window.preselectedArgomenti = <?php echo json_encode($argomenti_prereq_array); ?>;
        
        // Inizializza i sottoargomenti prerequisiti preselezionati
        window.preselectedSottoargomenti = <?php echo json_encode($sottoargomenti_prereq_array); ?>;
    });
</script>

<!-- Includi lo script per l'autocompletamento -->
<script src="../ui/js/prerequisiti-autocomplete.js"></script>
<?php
    }
}
?>