<?php
// File: pages/handlers/sottoargomento_handler.php

// Inizializza variabili di messaggio
$message = "";
$message_class = "";

// --- Gestione del form per creare un nuovo sottoargomento ---
if (isset($_POST['create'])) {
    $sottoargomento->argomento_id = $_POST['argomento_id'];
    $sottoargomento->titolo = $_POST['titolo'];
    $sottoargomento->descrizione = $_POST['descrizione'];
    $sottoargomento->livello_profondita = $_POST['livello_profondita'];

    if ($sottoargomento->create()) {
        $message = "Sottoargomento creato con successo!";
        $message_class = "success";
    } else {
        $message = "Impossibile creare il sottoargomento.";
        $message_class = "error";
    }
}

// --- Gestione della modifica di un sottoargomento ---
if (isset($_POST['update'])) {
    $sottoargomento->id = $_POST['id'];
    $sottoargomento->argomento_id = $_POST['argomento_id'];
    $sottoargomento->titolo = $_POST['titolo'];
    $sottoargomento->descrizione = $_POST['descrizione'];
    $sottoargomento->livello_profondita = $_POST['livello_profondita'];

    if ($sottoargomento->update()) {
        $message = "Sottoargomento aggiornato con successo!";
        $message_class = "success";
    } else {
        $message = "Impossibile aggiornare il sottoargomento.";
        $message_class = "error";
    }
}

// --- Gestione della cancellazione di un sottoargomento ---
if (isset($_GET['delete'])) {
    $sottoargomento->id = $_GET['delete'];
    if ($sottoargomento->delete()) {
        $message = "Sottoargomento eliminato con successo!";
        $message_class = "success";
    } else {
        $message = "Impossibile eliminare il sottoargomento.";
        $message_class = "error";
    }
}
?>