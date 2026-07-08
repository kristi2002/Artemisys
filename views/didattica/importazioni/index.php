<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-file-import me-2" style="color:#1e40af;"></i>Importa Lezioni da Excel
    </h4>
</div>

<!-- Istruzioni -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Formato del file Excel</h6>
        <p class="text-muted small mb-2">Il file deve contenere le seguenti colonne <strong>nell'ordine indicato</strong> (la prima riga deve essere l'intestazione):</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" style="font-size:0.8rem;">
                <thead style="background:#e8eef8;">
                    <tr>
                        <th>Percorso <span class="text-danger">*</span></th>
                        <th>Anno <span class="text-danger">*</span></th>
                        <th>Codice Materia <span class="text-danger">*</span></th>
                        <th>Titolo <span class="text-danger">*</span></th>
                        <th>Data</th>
                        <th>Durata (min)</th>
                        <th>Online</th>
                        <th>Link Online</th>
                        <th>Argomento</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-muted">
                        <td>OSS 2024/2025</td>
                        <td>1</td>
                        <td>ANAT</td>
                        <td>Apparato Digerente</td>
                        <td>15/01/2025</td>
                        <td>120</td>
                        <td>No</td>
                        <td></td>
                        <td>Organi interni</td>
                        <td>Aula 3</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-2 mb-0">
            <span class="text-danger">*</span> Campi obbligatori.
            Il <strong>Percorso</strong>, <strong>Anno</strong> e <strong>Codice Materia</strong> devono corrispondere ai dati già presenti nel sistema.
        </p>
    </div>
</div>

<!-- Upload -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div id="drop-zone" style="border:2px dashed #cbd5e1;border-radius:12px;padding:40px;text-align:center;cursor:pointer;transition:all .2s;">
            <i class="fas fa-file-excel fa-3x mb-3" style="color:#22c55e;"></i>
            <h5 class="fw-semibold" style="color:#0c1a3a;">Trascina qui il file Excel</h5>
            <p class="text-muted small mb-2">oppure clicca per selezionare un file .xls o .xlsx</p>
            <input type="file" id="file-input" accept=".xls,.xlsx" style="display:none;">
            <span id="file-name" class="badge bg-light text-dark d-none mt-2" style="font-size:0.85rem;"></span>
        </div>
    </div>
</div>

<!-- Anteprima -->
<div id="preview-section" class="d-none">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold" style="color:#0c1a3a;">
                <i class="fas fa-table me-2 text-primary"></i>Anteprima
                <span id="preview-count" class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:0.75rem;"></span>
            </h6>
            <button id="btn-import" class="btn btn-primary btn-sm">
                <i class="fas fa-upload me-1"></i>Importa Lezioni
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" id="preview-table">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Percorso</th>
                            <th>Anno</th>
                            <th>Cod. Materia</th>
                            <th>Titolo</th>
                            <th>Data</th>
                            <th>Durata</th>
                            <th>Online</th>
                            <th>Link</th>
                            <th>Argomento</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody id="preview-body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Risultato importazione -->
<div id="result-section" class="d-none">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div id="result-content"></div>
        </div>
    </div>
</div>

<!-- SheetJS CDN -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropZone  = document.getElementById('drop-zone');
    var fileInput = document.getElementById('file-input');
    var parsedRows = [];

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    dropZone.addEventListener('click', function() { fileInput.click(); });
    fileInput.addEventListener('change', function() { if (this.files[0]) handleFile(this.files[0]); });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#1e40af';
        this.style.background = '#f0f5ff';
    });
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#cbd5e1';
        this.style.background = '';
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#cbd5e1';
        this.style.background = '';
        var file = e.dataTransfer.files[0];
        if (file) handleFile(file);
    });

    function handleFile(file) {
        var ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'xls' && ext !== 'xlsx') {
            alert('Formato non supportato. Carica un file .xls o .xlsx');
            return;
        }

        var fnLabel = document.getElementById('file-name');
        fnLabel.textContent = file.name;
        fnLabel.classList.remove('d-none');
        dropZone.style.borderColor = '#22c55e';

        var reader = new FileReader();
        reader.onload = function(e) {
            var data = new Uint8Array(e.target.result);
            var wb   = XLSX.read(data, { type: 'array', cellDates: false });
            var ws   = wb.Sheets[wb.SheetNames[0]];
            var json = XLSX.utils.sheet_to_json(ws, { header: 1, raw: true, defval: '' });

            if (json.length < 2) {
                alert('Il file sembra vuoto o contiene solo l\'intestazione.');
                return;
            }

            parsedRows = [];
            var tbody = document.getElementById('preview-body');
            tbody.innerHTML = '';

            for (var i = 1; i < json.length; i++) {
                var r = json[i];
                if (!r || r.every(function(c) { return c === '' || c === null || c === undefined; })) continue;

                var row = {
                    percorso:       String(r[0] || '').trim(),
                    anno:           String(r[1] || '').trim(),
                    codice_materia: String(r[2] || '').trim(),
                    titolo:         String(r[3] || '').trim(),
                    data:           formatCellDate(r[4]),
                    durata:         String(r[5] || '').trim(),
                    online:         String(r[6] || '').trim(),
                    link_online:    String(r[7] || '').trim(),
                    argomento:      String(r[8] || '').trim(),
                    note:           String(r[9] || '').trim()
                };
                parsedRows.push(row);

                tbody.insertAdjacentHTML('beforeend',
                    '<tr>' +
                    '<td class="ps-3 text-muted small">' + (i + 1) + '</td>' +
                    '<td>' + esc(row.percorso) + '</td>' +
                    '<td>' + esc(row.anno) + '</td>' +
                    '<td><span class="badge" style="background:#e8eef8;color:#1e40af;">' + esc(row.codice_materia) + '</span></td>' +
                    '<td class="fw-semibold">' + esc(row.titolo) + '</td>' +
                    '<td>' + esc(row.data) + '</td>' +
                    '<td>' + esc(row.durata) + '</td>' +
                    '<td>' + esc(row.online) + '</td>' +
                    '<td class="small">' + esc(row.link_online) + '</td>' +
                    '<td class="small">' + esc(row.argomento) + '</td>' +
                    '<td class="small">' + esc(row.note) + '</td>' +
                    '</tr>'
                );
            }

            document.getElementById('preview-count').textContent = parsedRows.length + ' righe';
            document.getElementById('preview-section').classList.remove('d-none');
            document.getElementById('result-section').classList.add('d-none');
        };
        reader.readAsArrayBuffer(file);
    }

    function formatCellDate(val) {
        if (val === '' || val === null || val === undefined) return '';
        if (typeof val === 'number' && val > 30000 && val < 60000) {
            var d = new Date((val - 25569) * 86400 * 1000);
            var dd = String(d.getUTCDate()).padStart(2, '0');
            var mm = String(d.getUTCMonth() + 1).padStart(2, '0');
            var yy = d.getUTCFullYear();
            return dd + '/' + mm + '/' + yy;
        }
        return String(val).trim();
    }

    document.getElementById('btn-import').addEventListener('click', function() {
        if (!parsedRows.length) return;
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Importazione...';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= BASE_URL ?>importazioni/import-lezioni');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            var html = '';
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.ok) {
                    html += '<div class="alert alert-success mb-3">' +
                        '<i class="fas fa-check-circle me-2"></i>' +
                        '<strong>' + res.importate + '</strong> lezioni importate su <strong>' + res.totale + '</strong> righe.' +
                        '</div>';
                    if (res.errori && res.errori.length) {
                        html += '<div class="alert alert-warning mb-0">' +
                            '<i class="fas fa-exclamation-triangle me-2"></i>' +
                            '<strong>' + res.errori.length + ' righe con errori:</strong>' +
                            '<ul class="mb-0 mt-2">';
                        res.errori.forEach(function(e) { html += '<li class="small">' + esc(e) + '</li>'; });
                        html += '</ul></div>';
                    }
                } else {
                    html = '<div class="alert alert-danger mb-0">' +
                        '<i class="fas fa-times-circle me-2"></i>' + esc(res.errore) + '</div>';
                }
            } catch(e) {
                html = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle me-2"></i>Risposta non valida dal server.</div>';
            }
            document.getElementById('result-content').innerHTML = html;
            document.getElementById('result-section').classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload me-1"></i>Importa Lezioni';
        };
        xhr.onerror = function() {
            document.getElementById('result-content').innerHTML =
                '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle me-2"></i>Errore di comunicazione con il server.</div>';
            document.getElementById('result-section').classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload me-1"></i>Importa Lezioni';
        };
        xhr.send(JSON.stringify({ righe: parsedRows }));
    });
});
</script>
