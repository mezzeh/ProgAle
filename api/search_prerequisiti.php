<?php
// File: api/search_prerequisiti.php

// Includi file di configurazione
include_once '../config/database.php';
include_once '../models/argomento.php';
include_once '../models/sottoargomento.php';

// Abilita CORS per le chiamate AJAX
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verifica se è stata inviata una query di ricerca
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Se la query è vuota, restituisci un array vuoto
if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["error" => "Problema di connessione al database"]);
    exit;
}

// Array per i risultati della ricerca
$results = [];

// Cerca negli argomenti
$argomento = new Argomento($db);
$stmt = $argomento->search($query);

if ($stmt && $stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => $row['id'],
            'type' => 'argomento',
            'text' => $row['titolo'],
            'description' => substr($row['descrizione'], 0, 100) . '...'
        ];
    }
}

// Cerca nei sottoargomenti
$sottoargomento = new SottoArgomento($db);
$stmt = $sottoargomento->search($query);

if ($stmt && $stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => $row['id'],
            'type' => 'sottoargomento',
            'text' => $row['titolo'],
            'description' => substr($row['descrizione'], 0, 100) . '...',
            'parent_id' => $row['argomento_id']
        ];
    }
}

// Restituisci i risultati in formato JSON
echo json_encode($results);
?>