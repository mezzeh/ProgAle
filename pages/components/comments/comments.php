<?php
// File: pages/components/comments/comments.php

// Includi i componenti necessari
include_once '../handlers/comment_handler.php';
include_once 'comment_list.php';
include_once 'comment_scripts.php';

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