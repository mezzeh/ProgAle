// File: ui/js/prerequisiti-autocomplete.js

document.addEventListener('DOMContentLoaded', function () {
    // Elementi UI
    const searchInput = document.getElementById('prerequisiti-search');
    const searchResults = document.getElementById('search-results');
    const selectedPrerequisitesContainer = document.getElementById('selected-prerequisites');
    const hiddenInputArgomenti = document.getElementById('selected-argomenti');
    const hiddenInputSottoargomenti = document.getElementById('selected-sottoargomenti');

    // Variabili per tenere traccia dei prerequisiti selezionati
    let selectedArgomenti = [];
    let selectedSottoargomenti = [];

    // Se non abbiamo gli elementi necessari, usciamo
    if (!searchInput || !searchResults || !selectedPrerequisitesContainer) {
        console.error('Elementi UI mancanti per l\'autocompletamento');
        return;
    }

    // Inizializza gli array da eventuali valori preesistenti
    if (hiddenInputArgomenti.value) {
        try {
            selectedArgomenti = JSON.parse(hiddenInputArgomenti.value);
        } catch (e) {
            console.error('Errore nel parsing degli argomenti selezionati', e);
        }
    }

    if (hiddenInputSottoargomenti.value) {
        try {
            selectedSottoargomenti = JSON.parse(hiddenInputSottoargomenti.value);
        } catch (e) {
            console.error('Errore nel parsing dei sottoargomenti selezionati', e);
        }
    }

    // Funzione per eseguire la ricerca
    function performSearch(query) {
        if (query.length < 2) {
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
            return;
        }

        // Esegui la chiamata AJAX all'API di ricerca
        fetch(`../api/search_prerequisiti.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    searchResults.innerHTML = '<div class="no-results">Nessun risultato trovato</div>';
                } else {
                    searchResults.innerHTML = '';

                    // Aggiungi i risultati al dropdown
                    data.forEach(item => {
                        // Verifica se l'elemento è già stato selezionato
                        let isSelected = false;
                        if (item.type === 'argomento') {
                            isSelected = selectedArgomenti.some(a => a.id === item.id);
                        } else {
                            isSelected = selectedSottoargomenti.some(s => s.id === item.id);
                        }

                        // Se non è già selezionato, mostralo nei risultati
                        if (!isSelected) {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'search-result-item';
                            resultItem.dataset.id = item.id;
                            resultItem.dataset.type = item.type;
                            resultItem.dataset.text = item.text;

                            // Badge per il tipo (argomento o sottoargomento)
                            const typeBadge = document.createElement('span');
                            typeBadge.className = `badge badge-${item.type}`;
                            typeBadge.textContent = item.type === 'argomento' ? 'Argomento' : 'Sottoargomento';

                            // Titolo del risultato
                            const titleDiv = document.createElement('div');
                            titleDiv.className = 'result-title';
                            titleDiv.textContent = item.text;

                            // Contenitore principale
                            const contentDiv = document.createElement('div');
                            contentDiv.className = 'result-content';
                            contentDiv.appendChild(typeBadge);
                            contentDiv.appendChild(titleDiv);

                            resultItem.appendChild(contentDiv);

                            // Evento di click per selezionare il risultato
                            resultItem.addEventListener('click', function () {
                                const id = this.dataset.id;
                                const type = this.dataset.type;
                                const text = this.dataset.text;

                                // Aggiungi l'elemento selezionato
                                addSelectedPrerequisite(id, type, text);

                                // Pulisci la ricerca
                                searchInput.value = '';
                                searchResults.innerHTML = '';
                                searchResults.style.display = 'none';
                            });

                            searchResults.appendChild(resultItem);
                        }
                    });
                }

                searchResults.style.display = 'block';
            })
            .catch(error => {
                console.error('Errore nella ricerca:', error);
                searchResults.innerHTML = '<div class="error">Errore nella ricerca</div>';
                searchResults.style.display = 'block';
            });
    }

    // Funzione per aggiungere un prerequisito selezionato
    function addSelectedPrerequisite(id, type, text) {
        // Crea il tag per il prerequisito selezionato
        const tag = document.createElement('div');
        tag.className = 'selected-tag';
        tag.dataset.id = id;
        tag.dataset.type = type;

        // Badge per il tipo
        const typeBadge = document.createElement('span');
        typeBadge.className = `badge badge-${type}`;
        typeBadge.textContent = type === 'argomento' ? 'A' : 'S';

        // Testo del tag
        const textSpan = document.createElement('span');
        textSpan.textContent = text;

        // Pulsante per rimuovere
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-tag';
        removeBtn.innerHTML = '&times;';
        removeBtn.addEventListener('click', function () {
            removeSelectedPrerequisite(id, type);
            tag.remove();
        });

        // Assembla il tag
        tag.appendChild(typeBadge);
        tag.appendChild(textSpan);
        tag.appendChild(removeBtn);

        // Aggiungi il tag al container
        selectedPrerequisitesContainer.appendChild(tag);

        // Aggiungi l'elemento all'array appropriato
        if (type === 'argomento') {
            selectedArgomenti.push({ id: id, text: text });
        } else {
            selectedSottoargomenti.push({ id: id, text: text });
        }

        // Aggiorna gli input nascosti
        updateHiddenInputs();
    }

    // Funzione per rimuovere un prerequisito selezionato
    function removeSelectedPrerequisite(id, type) {
        if (type === 'argomento') {
            selectedArgomenti = selectedArgomenti.filter(a => a.id !== id);
        } else {
            selectedSottoargomenti = selectedSottoargomenti.filter(s => s.id !== id);
        }

        // Aggiorna gli input nascosti
        updateHiddenInputs();
    }

    // Funzione per aggiornare gli input nascosti
    function updateHiddenInputs() {
        hiddenInputArgomenti.value = JSON.stringify(selectedArgomenti.map(a => a.id));
        hiddenInputSottoargomenti.value = JSON.stringify(selectedSottoargomenti.map(s => s.id));
    }

    // Inizializza gli eventi

    // Evento input per la ricerca in tempo reale
    let debounceTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(this.value);
        }, 300); // 300ms di debounce per evitare troppe richieste
    });

    // Evento blur per nascondere i risultati quando si perde il focus
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Evento focus per mostrare i risultati quando il campo ottiene il focus
    searchInput.addEventListener('focus', function () {
        if (this.value.length >= 2) {
            performSearch(this.value);
        }
    });

    // Popola i tag selezionati dall'inizializzazione
    function initSelectedTags() {
        // Pulisci il container
        selectedPrerequisitesContainer.innerHTML = '';

        // Aggiungi gli argomenti preselezionati
        selectedArgomenti.forEach(arg => {
            addSelectedPrerequisite(arg.id, 'argomento', arg.text);
        });

        // Aggiungi i sottoargomenti preselezionati
        selectedSottoargomenti.forEach(sottoarg => {
            addSelectedPrerequisite(sottoarg.id, 'sottoargomento', sottoarg.text);
        });
    }

    // Inizializza i tag selezionati
    initSelectedTags();
});