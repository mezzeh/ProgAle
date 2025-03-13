<?php
// File: pages/components/comments/comment_list.php

/**
 * Renderizza la lista dei commenti per un elemento
 * 
 * @param PDO $db Connessione al database
 * @param string $tipo_elemento Tipo di elemento (es. 'argomento', 'esame', 'piano')
 * @param int $elemento_id ID dell'elemento a cui è associato il commento
 * @param string $redirect_param Parametro di reindirizzamento (es. 'esame_id=5')
 */
function renderCommenti($db, $tipo_elemento, $elemento_id, $redirect_param) {
    $commento = new Commento($db);
    $comment_count = $commento->countByElemento($tipo_elemento, $elemento_id);
    ?>
    <div class="comments-section">
        <h3>Commenti (<?php echo $comment_count; ?>)</h3>
        
        <?php if(isset($_SESSION['user_id'])): ?>
        <div class="comment-form">
            <form action="" method="POST">
                <input type="hidden" name="comment_tipo" value="<?php echo $tipo_elemento; ?>">
                <input type="hidden" name="comment_elemento_id" value="<?php echo $elemento_id; ?>">
                <textarea name="comment_text" placeholder="Aggiungi un commento..." required></textarea>
                <button type="submit" name="add_comment" class="btn-primary">Commenta</button>
            </form>
        </div>
        <?php else: ?>
        <p><a href="login.php">Accedi</a> per aggiungere un commento.</p>
        <?php endif; ?>
        
        <div class="comments-list">
            <?php
            $stmt_commenti = $commento->readByElemento($tipo_elemento, $elemento_id);
            
            if($stmt_commenti->rowCount() > 0) {
                while($row_commento = $stmt_commenti->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="comment-item" id="comment-'.$row_commento['id'].'">';
                    echo '<div class="comment-header">';
                    echo '<span class="comment-author">'.htmlspecialchars($row_commento['username']).'</span>';
                    echo '<span class="comment-date">'.date('d/m/Y H:i', strtotime($row_commento['data_creazione'])).'</span>';
                    echo '</div>';
                    echo '<div class="comment-content">'.htmlspecialchars($row_commento['testo']).'</div>';
                    
                    // Opzioni per modificare/eliminare
                    if(isset($_SESSION['user_id']) && 
                       ($_SESSION['user_id'] == $row_commento['user_id'] || 
                        (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
                        echo '<div class="comment-actions">';
                        echo '<a href="#" class="edit-comment-btn" data-id="'.$row_commento['id'].'">Modifica</a> | ';
                        echo '<a href="?'.$redirect_param.'&delete_comment='.$row_commento['id'].'" 
                              onclick="return confirm(\'Sei sicuro di voler eliminare questo commento?\');">Elimina</a>';
                        echo '</div>';
                        
                        // Form nascosto per la modifica
                        echo '<div class="edit-comment-form" id="edit-form-'.$row_commento['id'].'" style="display:none;">';
                        echo '<form action="" method="POST">';
                        echo '<input type="hidden" name="comment_id" value="'.$row_commento['id'].'">';
                        echo '<textarea name="comment_text" required>'.htmlspecialchars($row_commento['testo']).'</textarea>';
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
    <?php
}
?>