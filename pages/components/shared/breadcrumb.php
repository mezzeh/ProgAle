<?php
// File: pages/components/shared/breadcrumb.php

// Funzione per generare il breadcrumb
function generaBreadcrumb($items) {
    echo "<div class='breadcrumb'>";
    echo "<ul>";
    foreach ($items as $item) {
        if (isset($item['link'])) {
            echo "<li><a href='" . $item['link'] . "'>" . htmlspecialchars($item['text']) . "</a></li>";
        } else {
            echo "<li>" . htmlspecialchars($item['text']) . "</li>";
        }
    }
    echo "</ul>";
    echo "</div>";
}

// Visualizzazione breadcrumb se necessario
if ($esame_id) {
    $esame->id = $esame_id;
    $esame_info = $esame->readOne();
    
    $breadcrumb_items = [
        ['text' => 'Piani di Studio', 'link' => 'index.php'],
        ['text' => 'Esami', 'link' => 'esami.php'],
        ['text' => $esame_info['nome']]
    ];
    
    generaBreadcrumb($breadcrumb_items);
}
?>