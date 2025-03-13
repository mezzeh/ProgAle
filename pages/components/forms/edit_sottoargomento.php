<?php
// File: pages/components/forms/edit_sottoargomento.php

// Form per modificare un sottoargomento
if (isset($_GET['edit']) && isset($_SESSION['user_id'])) {
    $sottoargomento->id = $_GET['edit'];
    if ($sottoargomento->readOne()) {
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
        
        <button type='submit' name='update'>Aggiorna Sottoargomento</button>
        <a href='sottoargomenti.php<?php echo ($argomento_id ? "?argomento_id=$argomento_id" : ""); ?>' class='btn-secondary'>Annulla</a>
    </form>
</div>
<?php
    }
}
?>