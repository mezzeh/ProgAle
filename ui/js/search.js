/**
 * Sistema di ricerca con suggerimenti in tempo reale
 * Versione base dimostrativa
 */
document.addEventListener('DOMContentLoaded', function () {
    // Elementi della ricerca
    const searchInput = document.getElementById('nav-search-input');
    const searchResults = document.getElementById('search-results');

    // Dati di esempio per la dimostrazione
    const exampleData = [
        { id: 1, type: 'piano', name: 'Informatica', description: 'Corso di laurea in Informatica' },
        { id: 2, type: 'piano', name: 'Matematica', description: 'Corso di laurea in Matematica' },
        { id: 3, type: 'esame', name: 'Programmazione', description: 'Corso di programmazione base' },
        { id: 4, type: 'esame', name: 'Analisi Matematica', description: 'Corso di analisi matematica' },
        { id: 5, type: 'esame', name: 'Fisica', description: 'Principi di fisica' },
        { id: 6, type: 'argomento', name: 'Linguaggio C', description: 'Programmazione in C' },
        { id: 7, type: 'argomento', name: 'Java', description: 'Programmazione in Java' },
        { id: 8, type: 'argomento', name: 'Database', description: 'Concetti di database relazionali' },
        { id: 9, type: 'argomento', name: 'Algoritmi', description: 'Algoritmi e strutture dati' }
    ];

    // Icone per i tipi di risultati
    const typeIcons = {
        'piano': '📚',
        'esame': '📝',
        'argomento': '📌'
    };

    // Etichette per i tipi di risultati
    const typeLabels = {
        'piano': 'Piano di Studio',
        'esame': 'Esame',
        'argomento': 'Argomento'
    };

    if (!searchInput || !searchResults) {
        console.error('Elementi di ricerca non trovati nella pagina');
        return;
    }

    console.log('Sistema di ricerca inizializzato');

    // Gestione dell'input di ricerca
    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        // Se la query è vuota, nascondi i risultati
        if (query === '') {
            searchResults.style.display = 'none';
            return;
        }

        // Filtra i risultati in base alla query
        const filteredResults = exampleData.filter(item =>
            item.name.toLowerCase().includes(query) ||
            item.description.toLowerCase().includes(query)
        ).slice(0, 5); // Mostra massimo 5 risultati

        // Mostra i risultati
        if (filteredResults.length > 0) {
            searchResults.innerHTML = '';

            filteredResults.forEach(item => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.innerHTML = `
                    <div class="result-icon">${typeIcons[item.type]}</div>
                    <div class="result-content">
                        <div class="result-title">${highlightMatch(item.name, query)}</div>
                        <div class="result-type">${typeLabels[item.type]}</div>
                    </div>
                `;

                // Simula il click che porterebbe alla pagina del risultato
                resultItem.addEventListener('click', function () {
                    alert(`Hai selezionato: ${item.name} (${typeLabels[item.type]})`);
                    searchResults.style.display = 'none';
                });

                searchResults.appendChild(resultItem);
            });

            searchResults.style.display = 'block';
        } else {
            searchResults.innerHTML = '<div class="no-results">Nessun risultato trovato</div>';
            searchResults.style.display = 'block';
        }
    });

    // Gestione del click fuori dall'area di ricerca
    document.addEventListener('click', function (event) {
        if (!event.target.closest('#search-container')) {
            searchResults.style.display = 'none';
        }
    });

    // Gestione del tasto ESC
    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            searchResults.style.display = 'none';
            this.blur();
        }
    });

    // Funzione per evidenziare le parti corrispondenti
    function highlightMatch(text, query) {
        const index = text.toLowerCase().indexOf(query.toLowerCase());
        if (index >= 0) {
            return text.substring(0, index) +
                '<strong>' + text.substring(index, index + query.length) + '</strong>' +
                text.substring(index + query.length);
        }
        return text;
    }
});