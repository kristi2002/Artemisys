"""
Ricostruisce scheda_allievo_template.docx da scheda_allievo_blank.docx
aggiungendo tutti i placeholder {{...}} per tutti i campi del form.
"""
import zipfile, re, io

src = 'scheda_allievo_blank.docx'
dst = 'scheda_allievo_template.docx'

with zipfile.ZipFile(src, 'r') as z:
    doc_xml = z.read('word/document.xml').decode('utf-8')

# ── Debug: mostra tutti i nodi <w:t> ────────────────────────────────────────
def show_nodes(xml, start=0, end=300):
    parts = re.split(r'(<w:t(?:[^>]*)>[^<]*</w:t>)', xml)
    idx = 0
    for part in parts:
        if re.match(r'<w:t(?:[^>]*)>[^<]*</w:t>', part):
            if start <= idx < end:
                text = re.sub(r'<[^>]+>', '', part)
                print(f'{idx}: {repr(text)}')
            idx += 1

# Decomment to debug:
# show_nodes(doc_xml, 50, 260)

# ── Step 1: sostituisce nodi <w:t> per indice ───────────────────────────────
# Indici da svuotare = '', indici da rimpiazzare = placeholder
# Nota: gli indici qui si riferiscono alla sequenza ORIGINALE (blank)
index_replace = {
    # Scheda 1 – dettagli
    53: '',   # valore codice vecchio
    58: '', 59: '', 60: '', 61: '', 62: '',  # varie righe vuote
    63: '',   # anno (riga superiore da svuotare)
    64: '', 65: '', 66: '',
    # Non toccare 50-52 (label), non toccare 55,56 (codice/n.scheda label)
}

parts = re.split(r'(<w:t(?:[^>]*)>[^<]*</w:t>)', doc_xml)
idx = 0
new_parts = []
for part in parts:
    if re.match(r'<w:t(?:[^>]*)>[^<]*</w:t>', part):
        if idx in index_replace:
            val = index_replace[idx]
            attr = ' xml:space="preserve"' if ' ' in val else ''
            new_parts.append(f'<w:t{attr}>{val}</w:t>')
        else:
            new_parts.append(part)
        idx += 1
    else:
        new_parts.append(part)

new_doc = ''.join(new_parts)

# ── Step 2: inject_after – aggiunge run DOPO un'etichetta ───────────────────
def inject_after_label(xml, pattern, ph, n=1, font_size=20):
    count = 0
    for m in re.finditer(pattern, xml):
        count += 1
        if count == n:
            run = (f'<w:r><w:rPr><w:sz w:val="{font_size}"/><w:szCs w:val="{font_size}"/></w:rPr>'
                   f'<w:t xml:space="preserve"> {ph}</w:t></w:r>')
            return xml[:m.end()] + run + xml[m.end():]
    print(f'WARN inject_after({n}): "{pattern[:60]}"')
    return xml

def inject_before_tab(xml, label_pat, ph, tab_n=1, font_size=20):
    """Inserisce un run prima dell'n-esimo <w:tab/> che segue il label."""
    m_label = re.search(label_pat, xml)
    if not m_label:
        print(f'WARN label not found: "{label_pat[:60]}"')
        return xml
    after = xml[m_label.end():]
    count = 0
    for m_tab in re.finditer(r'<w:tab/>', after):
        count += 1
        if count == tab_n:
            run = (f'<w:r><w:rPr><w:sz w:val="{font_size}"/><w:szCs w:val="{font_size}"/></w:rPr>'
                   f'<w:t xml:space="preserve"> {ph}</w:t></w:r>')
            pos = m_label.end() + m_tab.start()
            return xml[:pos] + run + xml[pos:]
    print(f'WARN tab({tab_n}) not found after: "{label_pat[:60]}"')
    return xml

def inject_cell(tc_xml, ph, para_idx=0, font_size=20):
    """Aggiunge un run nel para_idx-esimo paragrafo di una cella."""
    paras = list(re.finditer(r'(<w:p[ >].*?</w:p>)', tc_xml, re.DOTALL))
    if para_idx >= len(paras):
        print(f'WARN: para {para_idx} not found in cell')
        return tc_xml
    run = (f'<w:r><w:rPr><w:sz w:val="{font_size}"/><w:szCs w:val="{font_size}"/></w:rPr>'
           f'<w:t xml:space="preserve">{ph}</w:t></w:r>')
    p = paras[para_idx].group()
    new_p = p.replace('</w:p>', run + '</w:p>', 1)
    return tc_xml.replace(p, new_p, 1)

# ── Scheda 1 ─────────────────────────────────────────────────────────────────
new_doc = inject_after_label(new_doc, r'Studente: </w:t></w:r>', '{{STUDENTE}}')
new_doc = inject_after_label(new_doc, r'Titolo del corso: </w:t></w:r>', '{{TITOLO_CORSO}}')
new_doc = inject_after_label(new_doc, r'Codice: </w:t></w:r>', '{{CODICE}}')
new_doc = inject_after_label(new_doc, r'Scheda n\.</w:t></w:r>', '{{NUMERO_SCHEDA}}')
new_doc = inject_after_label(new_doc, r'Anno </w:t></w:r>', '{{ANNO_FORM}}')
new_doc = inject_after_label(new_doc, r'Organismo </w:t></w:r>', '{{ORGANISMO}}')
new_doc = inject_after_label(new_doc, r'[Oo]re </w:t></w:r>', '{{ORE_CORSO}}')
new_doc = inject_after_label(new_doc, r'Obiettivo prefissato: </w:t></w:r>', '{{OBIETTIVO}}')
new_doc = inject_after_label(new_doc, r'Materie dell.area didattica specifica: </w:t></w:r>', '{{MATERIE_AREA}}')
new_doc = inject_after_label(new_doc, r'Verifiche didattiche: </w:t></w:r>', '{{VERIFICHE}}')
new_doc = inject_after_label(new_doc, r'Ammissione esami finali: </w:t></w:r>', '{{AMMISSIONE}}')
new_doc = inject_after_label(new_doc, r'Competenze acquisite dall.allievo: </w:t></w:r>', '{{COMPETENZE}}')
new_doc = inject_after_label(new_doc, r'Valutazione finale del team docenti: </w:t></w:r>', '{{VALUTAZIONE_FINALE}}')

# Giudizio /100 scheda 1 (before tab)
new_doc = inject_before_tab(new_doc, r'Giudizio: </w:t></w:r>(?:.*?)</w:r>', '{{GIUDIZIO_TEAM}}', 1)

# Firme scheda 1
new_doc = inject_before_tab(new_doc, r'Il Team dei docenti:</w:t></w:r>', '{{FIRMA_TEAM_1}}', 1)
new_doc = inject_before_tab(new_doc, r'Il Team dei docenti:</w:t></w:r>', '{{FIRMA_TEAM_2}}', 2)
new_doc = inject_before_tab(new_doc, r'Il Coordinatore del corso:</w:t></w:r>', '{{FIRMA_COORD}}', 1)

# ── Scheda 2 – autovalutazione ───────────────────────────────────────────────
new_doc = inject_before_tab(new_doc, r'suggerimenti e proposte\.</w:t></w:r>', '{{AUTO_TESTO}}', 1)
new_doc = inject_before_tab(new_doc, r'Giudizio: </w:t></w:r>(?:(?!</w:p>).)*?</w:r>', '{{GIUDIZIO_AUTO}}', 1)
new_doc = inject_before_tab(new_doc, r"L'allievo:</w:t></w:r>", '{{FIRMA_ALLIEVO}}', 1)

# ── Scheda 3 – stage ─────────────────────────────────────────────────────────
new_doc = inject_after_label(new_doc, r'Azienda in cui .+ stato effettuato lo stage: </w:t></w:r>', '{{STAGE_AZIENDA}}')
new_doc = inject_after_label(new_doc, r'Tutor aziendale: </w:t></w:r>', '{{STAGE_TUTOR}}')

# Date stage: dal / al per 3 periodi (i pattern cercano le celle della tabella stage)
# Periodi 1,2,3 – inject nella cella "Dal"/"Al" corrispondente
new_doc = inject_before_tab(new_doc, r'Dal </w:t></w:r>', '{{STAGE_DAL_1}}', 1)
new_doc = inject_before_tab(new_doc, r'Al </w:t></w:r>', '{{STAGE_AL_1}}', 1)
new_doc = inject_before_tab(new_doc, r'Dal </w:t></w:r>', '{{STAGE_DAL_2}}', 2)
new_doc = inject_before_tab(new_doc, r'Al </w:t></w:r>', '{{STAGE_AL_2}}', 2)
new_doc = inject_before_tab(new_doc, r'Dal </w:t></w:r>', '{{STAGE_DAL_3}}', 3)
new_doc = inject_before_tab(new_doc, r'Al </w:t></w:r>', '{{STAGE_AL_3}}', 3)

# Ore stage
new_doc = inject_before_tab(new_doc, r'Ore: </w:t></w:r>', '{{STAGE_ORE_1}}', 1)
new_doc = inject_before_tab(new_doc, r'Ore: </w:t></w:r>', '{{STAGE_ORE_2}}', 2)
new_doc = inject_before_tab(new_doc, r'Ore: </w:t></w:r>', '{{STAGE_ORE_3}}', 3)

# Note docente / tutor per ogni periodo
new_doc = inject_before_tab(new_doc, r'Note del docente: </w:t></w:r>', '{{STAGE_NOTE_DOC_1}}', 1)
new_doc = inject_before_tab(new_doc, r'Note del tutor: </w:t></w:r>', '{{STAGE_NOTE_TUT_1}}', 1)
new_doc = inject_before_tab(new_doc, r'Note del docente: </w:t></w:r>', '{{STAGE_NOTE_DOC_2}}', 2)
new_doc = inject_before_tab(new_doc, r'Note del tutor: </w:t></w:r>', '{{STAGE_NOTE_TUT_2}}', 2)
new_doc = inject_before_tab(new_doc, r'Note del docente: </w:t></w:r>', '{{STAGE_NOTE_DOC_3}}', 3)
new_doc = inject_before_tab(new_doc, r'Note del tutor: </w:t></w:r>', '{{STAGE_NOTE_TUT_3}}', 3)

# Valutazione tutor
new_doc = inject_after_label(new_doc, r'[Vv]alutazione del tutor.{0,100}</w:t></w:r>', '{{STAGE_VAL_TUTOR}}')

# Giudizio stage /100
new_doc = inject_before_tab(new_doc, r'Giudizio /100 </w:t></w:r>', '{{STAGE_GIUDIZIO}}', 1)

# Firme docente / tutor stage
new_doc = inject_before_tab(new_doc, r'Il docente\b.{0,30}</w:t></w:r>', '{{STAGE_FIRMA_DOC}}', 1)
new_doc = inject_before_tab(new_doc, r'Il tutor\b.{0,30}</w:t></w:r>', '{{STAGE_FIRMA_TUT}}', 1)

# ── Scheda 4 – relazione ─────────────────────────────────────────────────────
new_doc = inject_after_label(new_doc, r'Oggetto e/o titolo della relazione: </w:t></w:r>', '{{REL_OGGETTO}}')
new_doc = inject_after_label(new_doc, r'Ditta in cui .+ stato realizzato lo stage: </w:t></w:r>', '{{REL_DITTA}}')
new_doc = inject_after_label(new_doc, r'Tutor aziendale: </w:t></w:r>', '{{REL_TUTOR}}', 2)
new_doc = inject_after_label(new_doc, r'Valutazione della Commissione esaminatrice: </w:t></w:r>', '{{REL_VAL_COMM}}', 1)
new_doc = inject_before_tab(new_doc, r'Giudizio /100 </w:t></w:r>', '{{REL_GIUDIZIO}}', 2)
new_doc = inject_before_tab(new_doc, r'Il Presidente:</w:t></w:r>', '{{REL_PRESIDENTE}}', 1)
new_doc = inject_before_tab(new_doc, r'Membro:</w:t></w:r>', '{{REL_MEMBRO_1}}', 1)
new_doc = inject_before_tab(new_doc, r'Membro:</w:t></w:r>', '{{REL_MEMBRO_2}}', 2)

# ── Scheda 5 – prova pratica ─────────────────────────────────────────────────
# Tabella 7 colonne × 7 righe (dati in celle) – trovo la sezione fra Regolazione e Scheda n. 6
pratica_start_m = re.search(r'Regolazione</w:t>', new_doc)
pratica_end_m   = re.search(r'Valutazione della Commissione esaminatrice: </w:t></w:r>.*?Scheda n\. 6',
                             new_doc, re.DOTALL)
if pratica_start_m and pratica_end_m:
    sect = new_doc[pratica_start_m.end():pratica_end_m.start()]
    col_ph = {0:'COMP', 1:'ATT', 2:'INFO', 3:'PROG', 4:'ESEC', 5:'CTRL', 6:'REG'}
    cells = list(re.finditer(r'<w:tc>.*?</w:tc>', sect, re.DOTALL))
    replacements = []
    for ci, tc_m in enumerate(cells):
        row = ci // 7
        col = ci % 7
        if row < 7 and col in col_ph:
            ph = '{{PRATICA_' + col_ph[col] + '_' + str(row+1) + '}}'
            new_tc = inject_cell(tc_m.group(), ph, 0)
            if new_tc != tc_m.group():
                replacements.append((tc_m.group(), new_tc))
    new_sect = sect
    for old, new in replacements:
        new_sect = new_sect.replace(old, new, 1)
    new_doc = new_doc[:pratica_start_m.end()] + new_sect + new_doc[pratica_end_m.start():]
else:
    print('WARN: sezione prova pratica non trovata')

new_doc = inject_after_label(new_doc, r'Valutazione della Commissione esaminatrice: </w:t></w:r>', '{{PRATICA_VAL_COMM}}', 2)
new_doc = inject_before_tab(new_doc, r'Giudizio /100 </w:t></w:r>', '{{PRATICA_GIUDIZIO}}', 3)
new_doc = inject_before_tab(new_doc, r'Il Presidente:</w:t></w:r>', '{{PRATICA_PRESIDENTE}}', 2)
new_doc = inject_before_tab(new_doc, r'Membro:</w:t></w:r>', '{{PRATICA_MEMBRO_1}}', 3)
new_doc = inject_before_tab(new_doc, r'Membro:</w:t></w:r>', '{{PRATICA_MEMBRO_2}}', 4)

# ── Scheda 6 – prova teorica ─────────────────────────────────────────────────
teorica_start_m = re.search(r'B\) Area socio', new_doc)
teorica_end_m   = re.search(r'Valutazione della Commissione esaminatrice: </w:t></w:r>(?!.*Valutazione della Commissione)',
                             new_doc, re.DOTALL)
if teorica_start_m and teorica_end_m:
    sect = new_doc[teorica_start_m.start():teorica_end_m.start()]
    cells = list(re.finditer(r'<w:tc>.*?</w:tc>', sect, re.DOTALL))
    replacements = []
    for ci, tc_m in enumerate(cells):
        col = ci % 2   # 0=A, 1=B
        row = ci // 2
        if row < 7:
            ph = '{{TEORICA_' + ('A' if col == 0 else 'B') + '_' + str(row+1) + '}}'
            new_tc = inject_cell(tc_m.group(), ph, 0)
            if new_tc != tc_m.group():
                replacements.append((tc_m.group(), new_tc))
    new_sect = sect
    for old, new in replacements:
        new_sect = new_sect.replace(old, new, 1)
    new_doc = new_doc[:teorica_start_m.start()] + new_sect + new_doc[teorica_end_m.start():]
else:
    print('WARN: sezione prova teorica non trovata')

new_doc = inject_before_tab(new_doc, r'Max\. </w:t></w:r>', '{{TEORICA_MAX_A}}', 1)
new_doc = inject_before_tab(new_doc, r'Max\. </w:t></w:r>', '{{TEORICA_MAX_B}}', 2)
new_doc = inject_before_tab(new_doc, r'Giudizio A\) </w:t></w:r>', '{{TEORICA_GIUDIZIO_A}}', 1)
new_doc = inject_before_tab(new_doc, r'Giudizio B\) </w:t></w:r>', '{{TEORICA_GIUDIZIO_B}}', 1)
new_doc = inject_after_label(new_doc, r'Valutazione della Commissione esaminatrice: </w:t></w:r>', '{{TEORICA_VAL_COMM}}', 3)
new_doc = inject_before_tab(new_doc, r'Giudizio /100 </w:t></w:r>', '{{TEORICA_GIUDIZIO}}', 4)
new_doc = inject_before_tab(new_doc, r'Il Presidente:</w:t></w:r>', '{{TEORICA_PRESIDENTE}}', 3)
new_doc = inject_before_tab(new_doc, r'Membro:</w:t></w:r>', '{{TEORICA_MEMBRO_1}}', 5)
new_doc = inject_before_tab(new_doc, r'Membro:</w:t></w:r>', '{{TEORICA_MEMBRO_2}}', 6)

# ── Tabella materie (scheda 1) ───────────────────────────────────────────────
# La tabella materie inizia dopo l'header "Assenze" e finisce prima di "Competenze"
tbl_start_m = list(re.finditer(r'<w:t[^>]*>Assenze</w:t>', new_doc))
tbl_end_m   = re.search(r'Competenze acquisite', new_doc)
if tbl_start_m and tbl_end_m:
    # Prendo l'ultima occorrenza di "Assenze" (quella nella tabella)
    ts = tbl_start_m[-1]
    sect = new_doc[ts.end():tbl_end_m.start()]
    cell_map = {
        (0,0):'{{MAT_0_0}}',(0,1):'{{MAT_1_0}}',(0,2):'{{MAT_2_0}}',(0,3):'{{MAT_3_0}}',
        (1,0):'{{ASS_0_0}}',
        (2,0):'{{MAT_0_1}}',
        (3,0):'{{ASS_0_1}}',
        (4,0):'{{MAT_0_2}}',
        (5,0):'{{ASS_0_2}}',
    }
    cells = list(re.finditer(r'<w:tc>.*?</w:tc>', sect, re.DOTALL))
    replacements = []
    for ci, tc_m in enumerate(cells):
        paras = list(re.finditer(r'(<w:p[ >].*?</w:p>)', tc_m.group(), re.DOTALL))
        for pi in range(len(paras)):
            if (ci, pi) in cell_map:
                ph = cell_map[(ci, pi)]
                new_tc = inject_cell(tc_m.group(), ph, pi)
                if new_tc != tc_m.group():
                    replacements.append((tc_m.group(), new_tc))
                    break
    new_sect = sect
    for old, new in replacements:
        new_sect = new_sect.replace(old, new, 1)
    new_doc = new_doc[:ts.end()] + new_sect + new_doc[tbl_end_m.start():]
else:
    print('WARN: tabella materie non trovata')

# ── Salva ────────────────────────────────────────────────────────────────────
buf = io.BytesIO()
with zipfile.ZipFile(src, 'r') as zin:
    with zipfile.ZipFile(buf, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            if item.filename == 'word/document.xml':
                zout.writestr(item, new_doc.encode('utf-8'))
            else:
                zout.writestr(item, zin.read(item.filename))
with open(dst, 'wb') as f:
    f.write(buf.getvalue())
print(f'Salvato: {dst}')

# ── Verifica presenza placeholder ───────────────────────────────────────────
with zipfile.ZipFile(dst, 'r') as z:
    chk = z.read('word/document.xml').decode('utf-8')

must_have = [
    '{{STUDENTE}}','{{TITOLO_CORSO}}','{{CODICE}}','{{NUMERO_SCHEDA}}','{{ANNO_FORM}}',
    '{{ORGANISMO}}','{{ORE_CORSO}}','{{OBIETTIVO}}','{{MATERIE_AREA}}','{{VERIFICHE}}',
    '{{AMMISSIONE}}','{{COMPETENZE}}','{{VALUTAZIONE_FINALE}}','{{GIUDIZIO_TEAM}}',
    '{{FIRMA_TEAM_1}}','{{FIRMA_TEAM_2}}','{{FIRMA_COORD}}',
    '{{AUTO_TESTO}}','{{GIUDIZIO_AUTO}}','{{FIRMA_ALLIEVO}}',
    '{{STAGE_AZIENDA}}','{{STAGE_TUTOR}}','{{STAGE_VAL_TUTOR}}','{{STAGE_GIUDIZIO}}',
    '{{STAGE_FIRMA_DOC}}','{{STAGE_FIRMA_TUT}}',
    '{{STAGE_DAL_1}}','{{STAGE_AL_1}}','{{STAGE_ORE_1}}',
    '{{STAGE_NOTE_DOC_1}}','{{STAGE_NOTE_TUT_1}}',
    '{{REL_OGGETTO}}','{{REL_DITTA}}','{{REL_VAL_COMM}}','{{REL_GIUDIZIO}}',
    '{{REL_PRESIDENTE}}','{{REL_MEMBRO_1}}','{{REL_MEMBRO_2}}',
    '{{PRATICA_COMP_1}}','{{PRATICA_ATT_1}}','{{PRATICA_VAL_COMM}}',
    '{{PRATICA_PRESIDENTE}}','{{PRATICA_MEMBRO_1}}','{{PRATICA_MEMBRO_2}}',
    '{{TEORICA_A_1}}','{{TEORICA_B_1}}','{{TEORICA_MAX_A}}','{{TEORICA_MAX_B}}',
    '{{TEORICA_GIUDIZIO_A}}','{{TEORICA_GIUDIZIO_B}}','{{TEORICA_VAL_COMM}}',
    '{{TEORICA_PRESIDENTE}}','{{TEORICA_MEMBRO_1}}','{{TEORICA_MEMBRO_2}}',
    '{{MAT_0_0}}','{{MAT_1_0}}','{{MAT_0_1}}','{{MAT_0_2}}',
    '{{ASS_0_0}}','{{ASS_0_1}}','{{ASS_0_2}}',
]
ok = all_ok = True
for ph in must_have:
    found = ph in chk
    if not found:
        print(f'  MANCANTE: {ph}')
        all_ok = False
if all_ok:
    print('Tutti i placeholder trovati nel template!')
