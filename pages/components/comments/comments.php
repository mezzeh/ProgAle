<?php
// File: pages/components/comments/comments.php

// Includi i componenti necessari
include_once 'C:\xampp\htdocs\ProgAle\pages\handlers\argomento_handler.php';
include_once 'comment_list.php';
include_once 'comment_scripts.php';
/**
 * Base function for comment management
 * 
 * @param PDO $db Database connection
 * @param string $tipo_elemento Element type (e.g., 'esame', 'piano', 'argomento')
 * @param int $elemento_id ID of the element
 * @param string $redirect_base Base URL for redirects
 * @return array|null Operation result or null
 */
function gestioneCommenti($db, $tipo_elemento, $elemento_id, $redirect_base) {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        return ['message' => 'Accesso negato', 'message_class' => 'error'];
    }

    $commento = new Commento($db);

    // Add comment
    if (isset($_POST['add_comment'])) {
        $nuovo_commento = new Commento($db);
        $nuovo_commento->user_id = $_SESSION['user_id'];
        $nuovo_commento->tipo_elemento = $tipo_elemento;
        $nuovo_commento->elemento_id = $elemento_id;
        $nuovo_commento->testo = $_POST['comment_text'];
        
        if ($nuovo_commento->create()) {
            return [
                'message' => 'Commento aggiunto con successo', 
                'message_class' => 'success',
                'redirect' => "{$redirect_base}&comment_added=1"
            ];
        }
    }

    // Update comment
    if (isset($_POST['update_comment'])) {
        $commento->id = $_POST['comment_id'];
        $commento->user_id = $_SESSION['user_id'];
        $commento->testo = $_POST['comment_text'];
        
        // Check if the user is the author or admin
        $commento_esistente = $commento->readOne();
        if ($commento_esistente && 
            ($commento_esistente['user_id'] == $_SESSION['user_id'] || 
            (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            
            if ($commento->update()) {
                return [
                    'message' => 'Commento aggiornato con successo', 
                    'message_class' => 'success',
                    'redirect' => "{$redirect_base}&comment_updated=1"
                ];
            }
        }
    }

    // Delete comment
    if (isset($_GET['delete_comment'])) {
        $commento->id = $_GET['delete_comment'];
        $commento_esistente = $commento->readOne();
        
        // Check if the user is the author or admin
        if ($commento_esistente && 
            ($commento_esistente['user_id'] == $_SESSION['user_id'] || 
            (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            
            if ($commento->delete()) {
                return [
                    'message' => 'Commento eliminato con successo', 
                    'message_class' => 'success',
                    'redirect' => "{$redirect_base}&comment_deleted=1"
                ];
            }
        }
    }

    return null;
}
/**
 * Funzione di compatibilità per gli argomenti
 * Mantiene la vecchia interfaccia per evitare di modificare tutto il codice esistente
 */function gestioneCommentiArgomenti($db, $esame_id, $argomento_id) {
    // Modifica il reindirizzamento per puntare alla pagina di dettaglio
    $redirect_base = "view_pages/view_argomento.php?id={$argomento_id}";
    return gestioneCommenti($db, 'argomento', $argomento_id, $redirect_base);
}

function renderCommentiArgomenti($db, $esame_id, $argomento_id) {
    // Aggiorna il parametro di reindirizzamento per riflettere la nuova struttura URL
    $redirect_param = "id={$argomento_id}";
    renderCommenti($db, 'argomento', $argomento_id, $redirect_param);
    
    // Include gli script JS
    include 'comment_scripts.php';
}

/**
 * Funzione per gestire i commenti degli esami
 */
function gestioneCommentiEsami($db, $esame_id) {
    $redirect_base = "esami.php?id={$esame_id}";
    return gestioneCommenti($db, 'esame', $esame_id, $redirect_base);
}

/**
 * Funzione per renderizzare i commenti degli esami
 */
function renderCommentiEsami($db, $esame_id) {
    $redirect_param = "id={$esame_id}";
    renderCommenti($db, 'esame', $esame_id, $redirect_param);
    
    // Include gli script JS
    include 'comment_scripts.php';
}

/**
/**
 * Funzione per renderizzare i commenti dei piani di studio
 */
function renderCommentiPiani($db, $piano_id) {
    $redirect_param = "id={$piano_id}";
    renderCommenti($db, 'piano', $piano_id, $redirect_param);
    
    // Include gli script JS
    include 'comment_scripts.php';
}
/**
 * Funzione per gestire i commenti dei piani di studio
 */
function gestioneCommentiPiani($db, $piano_id) {
    $redirect_base = "view_pages/view_piano.php?id={$piano_id}";
    return gestioneCommenti($db, 'piano', $piano_id, $redirect_base);
}
/**
 * Funzione per gestire i commenti degli esercizi
 */
function gestioneCommentiEsercizi($db, $esercizio_id) {
    $redirect_base = "view_pages/view_esercizio.php?id={$esercizio_id}";
    return gestioneCommenti($db, 'esercizio', $esercizio_id, $redirect_base);
}

/**
 * Funzione per renderizzare i commenti degli esercizi
 */
function renderCommentiEsercizi($db, $esercizio_id) {
    $redirect_param = "id={$esercizio_id}";
    renderCommenti($db, 'esercizio', $esercizio_id, $redirect_param);
    
    // Include gli script JS
    include 'comment_scripts.php';
}
/**
 * Funzione per renderizzare i commenti dei piani di studio
 */

?>