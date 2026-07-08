<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-award me-2" style="color:#1e40af;"></i>Diplomi/Certificati
    </h4>
    <button type="button" class="btn btn-primary" onclick="alert('Funzione in costruzione');">
        <i class="fas fa-print me-2"></i>Stampa PDF
        <span class="badge ms-2" style="background:rgba(255,255,255,.25);font-size:.65rem;">In costruzione</span>
    </button>
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

<div class="alert alert-info" style="background:#eff6ff;border-color:#bfdbfe;color:#1e40af;">
    <i class="fas fa-info-circle me-2"></i>
    Configura qui il template del diploma. La <strong>generazione del PDF</strong> sarà disponibile in una versione successiva.
    Puoi usare i segnaposto <code>{{NOME}}</code>, <code>{{COGNOME}}</code>, <code>{{PERCORSO}}</code>, <code>{{DATA}}</code>, <code>{{LUOGO}}</code> nel testo.
</div>

<div class="row g-4">

    <!-- ── Colonna sinistra: testi ── -->
    <div class="col-lg-6">
    <form method="POST" action="<?= BASE_URL ?>diplomi/save">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-font me-2" style="color:#1e40af;"></i>Testi del diploma
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Titolo principale</label>
                        <input type="text" name="titolo" class="form-control"
                               value="<?= htmlspecialchars($diploma['titolo'] ?? 'DIPLOMA') ?>"
                               style="font-size:1.1rem;font-weight:700;text-align:center;"
                               oninput="aggiornaPreview()" id="inpTitolo">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Sottotitolo</label>
                        <input type="text" name="sottotitolo" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($diploma['sottotitolo'] ?? '') ?>"
                               oninput="aggiornaPreview()" id="inpSottotitolo">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Intestazione (es. nome accademia)</label>
                        <input type="text" name="intestazione" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($diploma['intestazione'] ?? '') ?>"
                               oninput="aggiornaPreview()" id="inpIntestazione">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Formula principale
                            <span class="text-muted" style="font-weight:400;">— testo centrale</span>
                        </label>
                        <textarea name="formula" class="form-control form-control-sm" rows="4"
                                  oninput="aggiornaPreview()" id="inpFormula"><?= htmlspecialchars($diploma['formula'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Chiusura</label>
                        <textarea name="chiusura" class="form-control form-control-sm" rows="2"
                                  oninput="aggiornaPreview()" id="inpChiusura"><?= htmlspecialchars($diploma['chiusura'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Luogo</label>
                        <input type="text" name="luogo" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($diploma['luogo'] ?? '') ?>"
                               oninput="aggiornaPreview()" id="inpLuogo">
                    </div>
                </div>
            </div>
        </div>

        <!-- Firme nomi/ruoli -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-signature me-2" style="color:#1e40af;"></i>Firmatari
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <small class="fw-semibold text-muted">FIRMA 1</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nome</label>
                        <input type="text" name="firma1_nome" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($diploma['firma1_nome'] ?? '') ?>"
                               oninput="aggiornaPreview()" id="inpFirma1Nome">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ruolo</label>
                        <input type="text" name="firma1_ruolo" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($diploma['firma1_ruolo'] ?? '') ?>"
                               oninput="aggiornaPreview()" id="inpFirma1Ruolo">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <small class="fw-semibold text-muted">FIRMA 2</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nome</label>
                        <input type="text" name="firma2_nome" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($diploma['firma2_nome'] ?? '') ?>"
                               oninput="aggiornaPreview()" id="inpFirma2Nome">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ruolo</label>
                        <input type="text" name="firma2_ruolo" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($diploma['firma2_ruolo'] ?? '') ?>"
                               oninput="aggiornaPreview()" id="inpFirma2Ruolo">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-4">
            <i class="fas fa-save me-2"></i>Salva testi
        </button>

    </form>
    </div>

    <!-- ── Colonna destra: anteprima + immagini ── -->
    <div class="col-lg-6">

        <!-- Upload timbro/firme -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-stamp me-2" style="color:#1e40af;"></i>Timbro e Firme
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $imgs = [
                        ['type' => 'timbro', 'label' => 'Timbro',  'field' => 'timbro_path'],
                        ['type' => 'firma1', 'label' => 'Firma 1', 'field' => 'firma1_path'],
                        ['type' => 'firma2', 'label' => 'Firma 2', 'field' => 'firma2_path'],
                    ];
                    foreach ($imgs as $img):
                        $hasFile = !empty($diploma[$img['field']]);
                    ?>
                    <div class="col-12">
                        <div class="p-3 rounded-3 d-flex align-items-center gap-3"
                             style="background:#f8fafc;border:1px dashed #cbd5e1;">
                            <div style="width:80px;height:60px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#fff;border-radius:6px;border:1px solid #e2e8f0;">
                                <?php if ($hasFile): ?>
                                    <img src="<?= ASSETS_URL ?>public/uploads/diplomi/<?= $diploma[$img['field']] ?>"
                                         style="max-width:70px;max-height:50px;object-fit:contain;">
                                <?php else: ?>
                                    <i class="fas fa-image text-muted" style="font-size:1.3rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                                    <?= $img['label'] ?>
                                </div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    <?= $hasFile ? 'Immagine caricata' : 'Nessuna immagine' ?>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('upl-<?= $img['type'] ?>').click()">
                                    <i class="fas fa-upload"></i>
                                </button>
                                <?php if ($hasFile): ?>
                                <form method="POST" action="<?= BASE_URL ?>diplomi/removeImage" class="d-inline"
                                      onsubmit="return confirm('Rimuovere?')">
                                    <input type="hidden" name="type" value="<?= $img['type'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" action="<?= BASE_URL ?>diplomi/uploadImage"
                                      enctype="multipart/form-data" class="d-none" id="form-upl-<?= $img['type'] ?>">
                                    <input type="hidden" name="type" value="<?= $img['type'] ?>">
                                    <input type="file" name="file" id="upl-<?= $img['type'] ?>"
                                           accept=".png,.jpg,.jpeg"
                                           onchange="document.getElementById('form-upl-<?= $img['type'] ?>').submit()">
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-muted small mt-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Carica immagini PNG con sfondo trasparente per un risultato migliore.
                </div>
            </div>
        </div>

        <!-- Anteprima -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-eye me-2" style="color:#1e40af;"></i>Anteprima
                </h6>
            </div>
            <div class="card-body p-3" style="background:#f1f5f9;">
                <div class="diploma-preview" id="diplomaPreview">

                    <div class="dp-corner dp-tl"></div>
                    <div class="dp-corner dp-tr"></div>
                    <div class="dp-corner dp-bl"></div>
                    <div class="dp-corner dp-br"></div>

                    <div class="dp-intestazione" id="prevIntestazione">
                        <?= htmlspecialchars($diploma['intestazione'] ?? '') ?>
                    </div>
                    <div class="dp-titolo" id="prevTitolo">
                        <?= htmlspecialchars($diploma['titolo'] ?? 'DIPLOMA') ?>
                    </div>
                    <div class="dp-sottotitolo" id="prevSottotitolo">
                        <?= htmlspecialchars($diploma['sottotitolo'] ?? '') ?>
                    </div>

                    <div class="dp-formula" id="prevFormula">
                        <?= nl2br(htmlspecialchars($diploma['formula'] ?? '')) ?>
                    </div>

                    <div class="dp-nome">Mario Rossi</div>

                    <div class="dp-chiusura" id="prevChiusura">
                        <?= nl2br(htmlspecialchars($diploma['chiusura'] ?? '')) ?>
                    </div>

                    <div class="dp-footer">
                        <div class="dp-firma">
                            <?php if (!empty($diploma['firma1_path'])): ?>
                                <img src="<?= ASSETS_URL ?>public/uploads/diplomi/<?= $diploma['firma1_path'] ?>" class="dp-firma-img">
                            <?php else: ?>
                                <div class="dp-firma-line"></div>
                            <?php endif; ?>
                            <div class="dp-firma-nome" id="prevFirma1Nome">
                                <?= htmlspecialchars($diploma['firma1_nome'] ?? '—') ?>
                            </div>
                            <div class="dp-firma-ruolo" id="prevFirma1Ruolo">
                                <?= htmlspecialchars($diploma['firma1_ruolo'] ?? '') ?>
                            </div>
                        </div>

                        <div class="dp-timbro">
                            <?php if (!empty($diploma['timbro_path'])): ?>
                                <img src="<?= ASSETS_URL ?>public/uploads/diplomi/<?= $diploma['timbro_path'] ?>" class="dp-timbro-img">
                            <?php else: ?>
                                <div class="dp-timbro-placeholder">TIMBRO</div>
                            <?php endif; ?>
                            <div class="dp-luogo" id="prevLuogo">
                                <?= htmlspecialchars($diploma['luogo'] ?? '') ?>, <?= date('d/m/Y') ?>
                            </div>
                        </div>

                        <div class="dp-firma">
                            <?php if (!empty($diploma['firma2_path'])): ?>
                                <img src="<?= ASSETS_URL ?>public/uploads/diplomi/<?= $diploma['firma2_path'] ?>" class="dp-firma-img">
                            <?php else: ?>
                                <div class="dp-firma-line"></div>
                            <?php endif; ?>
                            <div class="dp-firma-nome" id="prevFirma2Nome">
                                <?= htmlspecialchars($diploma['firma2_nome'] ?? '—') ?>
                            </div>
                            <div class="dp-firma-ruolo" id="prevFirma2Ruolo">
                                <?= htmlspecialchars($diploma['firma2_ruolo'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.diploma-preview {
    position:relative;
    background: linear-gradient(135deg, #fffaf0 0%, #fff 50%, #fffaf0 100%);
    border:2px solid #c8a96a;
    padding:35px 30px;
    aspect-ratio: 1.414/1;
    box-shadow: 0 4px 18px rgba(0,0,0,.12);
    font-family:'Georgia', serif;
    color:#2a2a2a;
    overflow:hidden;
}
.dp-corner { position:absolute; width:30px; height:30px; border:2px solid #c8a96a; }
.dp-tl { top:8px; left:8px; border-right:none; border-bottom:none; }
.dp-tr { top:8px; right:8px; border-left:none; border-bottom:none; }
.dp-bl { bottom:8px; left:8px; border-right:none; border-top:none; }
.dp-br { bottom:8px; right:8px; border-left:none; border-top:none; }

.dp-intestazione { text-align:center; font-size:.75rem; color:#8b6914; letter-spacing:.15em;
                   text-transform:uppercase; font-weight:700; margin-bottom:6px; }
.dp-titolo { text-align:center; font-size:2rem; font-weight:800; letter-spacing:.08em;
             color:#3d2817; margin-bottom:2px; line-height:1.1; }
.dp-sottotitolo { text-align:center; font-size:.85rem; color:#8b6914; font-style:italic; margin-bottom:14px; }

.dp-formula { text-align:center; font-size:.78rem; line-height:1.55; color:#3d2817;
              padding:0 8px; min-height:40px; }
.dp-nome { text-align:center; font-size:1.5rem; font-weight:700; color:#3d2817;
           font-family:'Brush Script MT', cursive; margin:14px 0 10px; font-style:italic; }
.dp-chiusura { text-align:center; font-size:.72rem; color:#5a4a35;
               font-style:italic; margin-bottom:18px; padding:0 12px; }

.dp-footer { display:flex; justify-content:space-between; align-items:flex-end; gap:8px; margin-top:auto; }
.dp-firma { flex:1; text-align:center; min-height:60px; display:flex; flex-direction:column; justify-content:flex-end; }
.dp-firma-line { height:1px; background:#3d2817; margin:0 8px 4px; }
.dp-firma-img { max-width:100%; max-height:35px; object-fit:contain; margin:0 auto 2px; display:block; }
.dp-firma-nome { font-size:.7rem; font-weight:700; color:#3d2817; }
.dp-firma-ruolo { font-size:.6rem; color:#8b6914; font-style:italic; text-transform:uppercase; letter-spacing:.05em; }

.dp-timbro { flex:0 0 100px; text-align:center; }
.dp-timbro-placeholder {
    width:65px; height:65px; border:2px dashed #c8a96a; border-radius:50%;
    margin:0 auto 4px; display:flex; align-items:center; justify-content:center;
    color:#c8a96a; font-size:.55rem; font-weight:700; letter-spacing:.1em;
}
.dp-timbro-img { max-width:75px; max-height:65px; object-fit:contain; margin:0 auto 4px; display:block; }
.dp-luogo { font-size:.6rem; color:#5a4a35; font-style:italic; }
</style>

<script>
function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}
function setHTML(id, val) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = val.replace(/\n/g,'<br>');
}

function aggiornaPreview() {
    setText('prevTitolo',       document.getElementById('inpTitolo').value || 'DIPLOMA');
    setText('prevSottotitolo',  document.getElementById('inpSottotitolo').value || '');
    setText('prevIntestazione', document.getElementById('inpIntestazione').value || '');
    setHTML('prevFormula',      escHtml(document.getElementById('inpFormula').value || ''));
    setHTML('prevChiusura',     escHtml(document.getElementById('inpChiusura').value || ''));
    setText('prevFirma1Nome',   document.getElementById('inpFirma1Nome').value || '—');
    setText('prevFirma1Ruolo',  document.getElementById('inpFirma1Ruolo').value || '');
    setText('prevFirma2Nome',   document.getElementById('inpFirma2Nome').value || '—');
    setText('prevFirma2Ruolo',  document.getElementById('inpFirma2Ruolo').value || '');

    const luogo = document.getElementById('inpLuogo').value;
    const oggi  = new Date().toLocaleDateString('it-IT');
    setText('prevLuogo', (luogo ? luogo + ', ' : '') + oggi);
}
function escHtml(s) {
    return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
