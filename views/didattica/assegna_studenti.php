<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

$mappaAnni = [];
foreach ($percorsoAnni as $pa) {
    $mappaAnni[$pa['percorso_id']][] = ['id' => $pa['id'], 'numero' => $pa['numero']];
}

$totNuovi     = count(array_filter($studenti, fn($s) => empty($s['iscrizioni'])));
$totAssegnati = count($studenti) - $totNuovi;
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-user-check me-2" style="color:#1e40af;"></i>Assegna Studenti a Percorso
    </h4>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- Form selezione -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3" style="color:#0c1a3a;">
                    <i class="fas fa-sliders-h me-2" style="color:#1e40af;"></i>Destinazione
                </h6>
                <form method="POST" action="<?= BASE_URL ?>assegna-studenti/store" id="formAssegna">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Percorso <span class="text-danger">*</span></label>
                        <?php if (empty($percorsi)): ?>
                            <div class="alert alert-warning py-2 small">Nessun percorso disponibile.</div>
                        <?php else: ?>
                            <select name="percorso_id" id="sel_percorso" class="form-select" required onchange="aggiornaAnni()">
                                <option value="">— Seleziona —</option>
                                <?php foreach ($percorsi as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-as="<?= $p['anno_scolastico_id'] ?>">
                                        <?= htmlspecialchars($p['nome']) ?>
                                        <br><?= htmlspecialchars($p['anno_label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Anno di corso <span class="text-danger">*</span></label>
                        <select name="percorso_anno_id" id="sel_anno" class="form-select" required disabled>
                            <option value="">— Prima seleziona il percorso —</option>
                        </select>
                        <input type="hidden" name="anno_scolastico_id" id="hidden_as" value="">
                    </div>

                    <!-- Riepilogo selezione -->
                    <div class="rounded p-3 mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Totale studenti</span>
                            <strong><?= count($studenti) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Senza percorso</span>
                            <strong style="color:#16a34a;"><?= $totNuovi ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Già assegnati</span>
                            <strong style="color:#c2410c;"><?= $totAssegnati ?></strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" <?= empty($percorsi) ? 'disabled' : '' ?>>
                        <i class="fas fa-user-check me-2"></i>Assegna selezionati
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista studenti -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-users me-2" style="color:#1e40af;"></i>Studenti
                </h6>
                <div class="d-flex gap-3" style="font-size:.82rem;">
                    <a href="#" onclick="setTutti(true);return false;" style="color:#1e40af;">Tutti</a>
                    <a href="#" onclick="setTutti(false);return false;" style="color:#64748b;">Nessuno</a>
                    <a href="#" onclick="setSoloNuovi();return false;" style="color:#16a34a;">Solo nuovi</a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($studenti)): ?>
                    <div class="p-4 text-center text-muted">Nessuno studente attivo.</div>
                <?php else: ?>
                    <div style="max-height:520px;overflow-y:auto;">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8fafc;position:sticky;top:0;">
                                <tr>
                                    <th style="width:40px;" class="ps-4">
                                        <input type="checkbox" id="checkAll" onchange="setTutti(this.checked)"
                                               style="width:15px;height:15px;cursor:pointer;">
                                    </th>
                                    <th>Studente</th>
                                    <th>Percorso attuale</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studenti as $s):
                                    $haPercorso = !empty($s['iscrizioni']);
                                ?>
                                    <tr onclick="toggleCheck(this)" style="cursor:pointer;">
                                        <td class="ps-4 align-middle" onclick="event.stopPropagation()">
                                            <input type="checkbox"
                                                   form="formAssegna"
                                                   name="studenti_ids[]"
                                                   value="<?= $s['id'] ?>"
                                                   class="stud-check <?= $haPercorso ? 'has-percorso' : 'no-percorso' ?>"
                                                   <?= !$haPercorso ? 'checked' : '' ?>
                                                   style="width:15px;height:15px;cursor:pointer;">
                                        </td>
                                        <td class="align-middle fw-semibold" style="color:#0c1a3a;">
                                            <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                        </td>
                                        <td class="align-middle" style="font-size:.85rem;">
                                            <?php if ($haPercorso): ?>
                                                <?php foreach ($s['iscrizioni'] as $isc): ?>
                                                    <span class="badge me-1" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-weight:500;">
                                                        <?= htmlspecialchars($isc['percorso_nome']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="badge" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-weight:500;">
                                                    Nuovo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
const mappaAnni = <?= json_encode($mappaAnni) ?>;
const ordinali  = <?= json_encode($ordinali) ?>;

function aggiornaAnni() {
    const sel      = document.getElementById('sel_percorso');
    const selAnno  = document.getElementById('sel_anno');
    const hiddenAs = document.getElementById('hidden_as');
    const opt      = sel.options[sel.selectedIndex];
    const pid      = sel.value;

    hiddenAs.value = opt ? opt.dataset.as : '';
    selAnno.innerHTML = '';

    if (!pid || !mappaAnni[pid]) {
        selAnno.disabled = true;
        selAnno.innerHTML = '<option value="">— Prima seleziona il percorso —</option>';
        return;
    }
    selAnno.disabled = false;
    selAnno.innerHTML = '<option value="">— Seleziona anno —</option>';
    mappaAnni[pid].forEach(a => {
        const label = ordinali[a.numero] ?? (a.numero + '° Anno');
        selAnno.innerHTML += `<option value="${a.id}">${label}</option>`;
    });
}

function setTutti(v) {
    document.querySelectorAll('.stud-check').forEach(c => c.checked = v);
    document.getElementById('checkAll').checked = v;
}

function setSoloNuovi() {
    document.querySelectorAll('.stud-check').forEach(c => {
        c.checked = c.classList.contains('no-percorso');
    });
    document.getElementById('checkAll').checked = false;
}

function toggleCheck(row) {
    const cb = row.querySelector('.stud-check');
    if (cb) cb.checked = !cb.checked;
}
</script>
