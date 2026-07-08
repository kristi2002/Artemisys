"""
Ricostruisce scheda_allievo_template.docx da scheda_allievo_blank.docx (NUOVO BLANK).
Struttura nuova: label + paragrafo vuoto sotto, o label + tab-underscore inline.
"""
import zipfile, re, io

SRC = 'scheda_allievo_blank.docx'
DST = 'scheda_allievo_template.docx'

with zipfile.ZipFile(SRC, 'r') as z:
    doc = z.read('word/document.xml').decode('utf-8')

# ── Funzioni ──────────────────────────────────────────────────────────────────
def wt_nodes(xml):
    return list(re.finditer(r'<w:t(?:[^>]*)>[^<]*</w:t>', xml))

def inject_before_tab(xml, label_pat, ph, tab_n=1, occ_n=1, sz=20):
    """Inserisce un run PRIMA del <w:r> che contiene il tab_n-esimo <w:tab/>
    dopo l'occ_n-esima occorrenza del label.
    Inserisce dopo il </w:r> precedente per evitare run annidati."""
    count = 0
    for m_label in re.finditer(label_pat, xml):
        count += 1
        if count == occ_n:
            after_pos = m_label.end()
            after = xml[after_pos:]
            tc = 0
            for m_tab in re.finditer(r'<w:tab/>', after):
                tc += 1
                if tc == tab_n:
                    tab_abs = after_pos + m_tab.start()
                    before_tab = xml[:tab_abs]
                    r_close = before_tab.rfind('</w:r>')
                    insert_pos = r_close + len('</w:r>') if r_close != -1 else tab_abs
                    run = (f'<w:r><w:rPr><w:sz w:val="{sz}"/></w:rPr>'
                           f'<w:t xml:space="preserve"> {ph}</w:t></w:r>')
                    return xml[:insert_pos] + run + xml[insert_pos:]
            print(f'WARN tab({tab_n}) occ({occ_n}): {label_pat[:50]}')
            return xml
    print(f'WARN occ({occ_n}): {label_pat[:50]}')
    return xml

def inject_after_label(xml, label_pat, ph, occ_n=1, sz=20):
    """Aggiunge un run dopo l'occ_n-esima occorrenza del label."""
    count = 0
    for m in re.finditer(label_pat, xml):
        count += 1
        if count == occ_n:
            run = (f'<w:r><w:rPr><w:sz w:val="{sz}"/></w:rPr>'
                   f'<w:t xml:space="preserve"> {ph}</w:t></w:r>')
            return xml[:m.end()] + run + xml[m.end():]
    print(f'WARN inject_after occ({occ_n}): {label_pat[:50]}')
    return xml

def inject_into_next_para(xml, label_pat, ph, occ_n=1, sz=20):
    """Trova il label, poi inietta all'inizio del PROSSIMO paragrafo (dopo </w:pPr>)."""
    count = 0
    for m_label in re.finditer(label_pat, xml):
        count += 1
        if count == occ_n:
            after = xml[m_label.end():]
            para_end = after.find('</w:p>')
            if para_end == -1:
                print(f'WARN next_para no </w:p>: {label_pat[:50]}')
                return xml
            after2 = after[para_end:]
            ppr_end = after2.find('</w:pPr>')
            if ppr_end == -1:
                # Paragrafo senza pPr: inserisci dopo il tag <w:p...>
                p_open = after2.find('<w:p')
                p_close = after2.find('>', p_open)
                insert_local = p_close + 1
            else:
                insert_local = ppr_end + len('</w:pPr>')
            pos = m_label.end() + para_end + insert_local
            run = (f'<w:r><w:rPr><w:sz w:val="{sz}"/></w:rPr>'
                   f'<w:t xml:space="preserve">{ph}</w:t></w:r>')
            return xml[:pos] + run + xml[pos:]
    print(f'WARN inject_next_para occ({occ_n}): {label_pat[:50]}')
    return xml

def inject_cell(tc_xml, ph, para_idx=0, sz=20):
    """Aggiunge un run nel para_idx-esimo paragrafo della cella."""
    paras = list(re.finditer(r'(<w:p[ >].*?</w:p>)', tc_xml, re.DOTALL))
    if para_idx >= len(paras):
        return tc_xml
    p = paras[para_idx].group()
    run = f'<w:r><w:rPr><w:sz w:val="{sz}"/></w:rPr><w:t xml:space="preserve">{ph}</w:t></w:r>'
    return tc_xml.replace(p, p.replace('</w:p>', run + '</w:p>', 1), 1)

# ── Step 1: sostituzioni per indice ───────────────────────────────────────────
# NOTA: indici riferiti al nuovo scheda_allievo_blank.docx (151 nodi totali)
IDX = {
    # TITOLO_CORSO: primo run blank dopo label 'Titolo del corso'
    8:   ('{{TITOLO_CORSO}}',),
    # OBIETTIVO: il nodo [18] è il solo label → inline placeholder
    18:  ('Obiettivo prefissato: {{OBIETTIVO}}',),
    # [19] = 'Materie e/o area didattica:'  → NON azzerare
    # [20] = 'Verifiche didattiche:'        → NON azzerare
    # Giudizio team: nodo [31] = '______ '
    31:  ('{{GIUDIZIO_TEAM}}',),
    # Giudizio autovalutazione: nodo [58] = '_______'
    58:  ('{{GIUDIZIO_AUTO}}',),
    # Stage giudizio: nodo [82] = '_________'
    82:  ('{{STAGE_GIUDIZIO}}',),
    # Relazione giudizio: nodo [100] = '________'
    100: ('{{REL_GIUDIZIO}}',),
    # Pratica giudizio: nodo [123] = 'Giudizio: ________/100'
    123: ('{{PRATICA_GIUDIZIO}}',),
    # Teorica max / giudizi
    132: ('{{TEORICA_MAX_A}}',),
    135: ('{{TEORICA_MAX_B}}',),
    138: ('{{TEORICA_GIUDIZIO_A}}',),
    141: ('{{TEORICA_GIUDIZIO_B}}',),
    145: ('{{TEORICA_GIUDIZIO}}',),
}

for idx in sorted(IDX.keys(), reverse=True):
    ns = wt_nodes(doc)
    if idx >= len(ns):
        print(f'WARN idx {idx} fuori range ({len(ns)})'); continue
    val = IDX[idx][0]
    m = ns[idx]
    attr = ' xml:space="preserve"' if val else ''
    tag = f'<w:t{attr}>{val}</w:t>' if val else '<w:t/>'
    doc = doc[:m.start()] + tag + doc[m.end():]

# ── Step 2: inject_into_next_para (label su riga, valore nel para sotto) ──────
doc = inject_into_next_para(doc, r'Allievo</w:t></w:r>',              '{{STUDENTE}}',  sz=22)
doc = inject_into_next_para(doc, r'Organismo gestore</w:t></w:r>',    '{{ORGANISMO}}', sz=22)
# Stage tutor: blank para (con tab underscore) dopo label
doc = inject_into_next_para(doc, r'incaricato a seguire l\S+allievo nello stage:</w:t></w:r>', '{{STAGE_TUTOR}}')
# Firma tutor stage: blank para dopo 'Il tutor:'
doc = inject_into_next_para(doc, r'Il tutor:</w:t></w:r>',            '{{STAGE_FIRMA_TUT}}')
# Firma team 2: blank para dopo il para-firma (dopo 'Il Coordinatore del corso:')
doc = inject_into_next_para(doc, r'Il Coordinatore del corso:</w:t></w:r>', '{{FIRMA_TEAM_2}}')

# ── Step 3: inject_before_tab ─────────────────────────────────────────────────
# Header
doc = inject_before_tab(doc, r'Codice</w:t></w:r>',       '{{CODICE}}',        tab_n=1, sz=22)
doc = inject_before_tab(doc, r'Scheda n\S</w:t></w:r>',   '{{NUMERO_SCHEDA}}', tab_n=1, sz=22)
doc = inject_before_tab(doc, r'Ore corso</w:t></w:r>',    '{{ORE_CORSO}}',     tab_n=1, sz=22)
# Scheda 1 firme
doc = inject_before_tab(doc, r'Il Team dei docenti: </w:t></w:r>', '{{FIRMA_TEAM_1}}', tab_n=1)
# AUTO_TESTO: primo paragrafo con leader underscore dopo 'suggerimenti e proposte.'
# Non si usa inject_before_tab (il </w:r> ref è nel para sbagliato)
_m_prop = re.search(r'suggerimenti e proposte\.</w:t></w:r></w:p>', doc)
if _m_prop:
    _after = doc[_m_prop.end():]
    # Trova la prima pPr con 'w:leader="underscore"'
    _m_under = re.search(r'<w:pPr>(?:(?!</w:pPr>).)*w:leader="underscore"(?:(?!</w:pPr>).)*</w:pPr>', _after, re.DOTALL)
    if _m_under:
        _pos = _m_prop.end() + _m_under.end()
        _run = '<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">{{AUTO_TESTO}}</w:t></w:r>'
        doc = doc[:_pos] + _run + doc[_pos:]
    else:
        print('WARN: paragrafo underscore per AUTO_TESTO non trovato')
else:
    print('WARN: "suggerimenti e proposte." non trovato')
# L'allievo firma
doc = inject_before_tab(doc, r'allievo:</w:t></w:r>',     '{{FIRMA_ALLIEVO}}', tab_n=1)
# Stage firma docente
doc = inject_before_tab(doc, r'Il docente :     </w:t></w:r>', '{{STAGE_FIRMA_DOC}}', tab_n=1)
# Presidenti commissioni (3 occorrenze)
doc = inject_before_tab(doc, r'Il Presidente:</w:t></w:r>', '{{REL_PRESIDENTE}}',     occ_n=1)
doc = inject_before_tab(doc, r'Il Presidente:</w:t></w:r>', '{{PRATICA_PRESIDENTE}}', occ_n=2)
doc = inject_before_tab(doc, r'Il Presidente:</w:t></w:r>', '{{TEORICA_PRESIDENTE}}', occ_n=3)

# ── Step 4: inject_after_label ────────────────────────────────────────────────
# Header
doc = inject_after_label(doc, r'anno</w:t></w:r>',                              '{{ANNO_FORM}}', sz=22)
# Scheda 1
doc = inject_after_label(doc, r'Materie e/o area didattica: </w:t></w:r>',      '{{MATERIE_AREA}}')
doc = inject_after_label(doc, r'Verifiche didattiche: </w:t></w:r>',            '{{VERIFICHE}}')
doc = inject_after_label(doc, r'Ammissione esami finali: </w:t></w:r>',         '{{AMMISSIONE}}')
doc = inject_after_label(doc, r'Competenze acquisite dall\S+allievo: </w:t></w:r>', '{{COMPETENZE}}')
doc = inject_after_label(doc, r'finale del team docenti: </w:t></w:r>',         '{{VALUTAZIONE_FINALE}}')
# Firma Coordinatore: va nella stessa riga della label, dopo 'Il Coordinatore del corso:'
doc = inject_after_label(doc, r'Il Coordinatore del corso:</w:t></w:r>',        '{{FIRMA_COORD}}')
# Stage
doc = inject_after_label(doc, r'Azienda in cui \S+ stato effettuato lo stage: </w:t></w:r>', '{{STAGE_AZIENDA}}')
doc = inject_after_label(doc, r'attenzione, </w:t></w:r>',                      '{{STAGE_VAL_TUTOR}}')
# Relazione
doc = inject_after_label(doc, r'Oggetto e/o titolo della relazione: </w:t></w:r>', '{{REL_OGGETTO}}')
doc = inject_after_label(doc, r'Ditta in cui \S+ stato realizzato lo stage: </w:t></w:r>', '{{REL_DITTA}}')
doc = inject_after_label(doc, r'incaricato di seguire l\S+allievo nello stage:</w:t></w:r>', '{{REL_TUTOR}}')
# Valutazioni commissioni (3 occorrenze di 'inatrice: ')
doc = inject_after_label(doc, r'inatrice: </w:t></w:r>',  '{{REL_VAL_COMM}}',    occ_n=1)
doc = inject_after_label(doc, r'Valutazione della Commissione esaminatrice: </w:t></w:r>', '{{PRATICA_VAL_COMM}}', occ_n=1)
doc = inject_after_label(doc, r'Valutazione della Commissione esaminatrice: </w:t></w:r>', '{{TEORICA_VAL_COMM}}', occ_n=2)
# Membri commissioni (pattern specifici per ogni scheda)
# Membro: usa regex flessibile con \s+ per gestire variazioni di spazi tra versioni
# Scheda 4 relazione: Membro con spazi dopo (es. 'Membro:   ...   ')
doc = inject_after_label(doc, r'Membro:\s+</w:t></w:r>',  '{{REL_MEMBRO_1}}',    occ_n=1)
doc = inject_after_label(doc, r'Membro:</w:t></w:r>',     '{{REL_MEMBRO_2}}',    occ_n=1)
# Scheda 5 pratica
doc = inject_after_label(doc, r'Membro:</w:t></w:r>',     '{{PRATICA_MEMBRO_1}}', occ_n=2)
doc = inject_after_label(doc, r'\s+Membro:</w:t></w:r>',  '{{PRATICA_MEMBRO_2}}', occ_n=1)
# Scheda 6 teorica
doc = inject_after_label(doc, r'Membro:</w:t></w:r>',     '{{TEORICA_MEMBRO_1}}', occ_n=3)
doc = inject_after_label(doc, r'\s+Membro:</w:t></w:r>',  '{{TEORICA_MEMBRO_2}}', occ_n=2)

# ── Step 5: tabella materie ───────────────────────────────────────────────────
# Struttura: 6 celle (3 coppie Materie/Assenze)
# Cella 0 (Materie col 1) ha 4 paragrafi: MAT_0_0..MAT_3_0
# Cella 1 (Assenze col 1) para 0: ASS_0_0
# Cella 2 (Materie col 2) para 0: MAT_0_1
# Cella 3 (Assenze col 2) para 0: ASS_0_1
# Cella 4 (Materie col 3) para 0: MAT_0_2
# Cella 5 (Assenze col 3) para 0: ASS_0_2
tbl_hdr_matches = list(re.finditer(r'Assenze</w:t></w:r></w:p></w:tc></w:tr>', doc))
tbl_end_m = re.search(r'Competenze acquisite dall', doc)
if tbl_hdr_matches and tbl_end_m:
    tbl_start = tbl_hdr_matches[-1].end()
    tbl_end   = tbl_end_m.start()
    sect = doc[tbl_start:tbl_end]
    tcs = list(re.finditer(r'<w:tc>.*?</w:tc>', sect, re.DOTALL))
    new_sect = sect
    # Cella 0: 4 paragrafi → MAT_0_0..MAT_3_0
    if len(tcs) >= 1:
        tc0 = tcs[0].group()
        paras = list(re.finditer(r'(<w:p[ >].*?</w:p>)', tc0, re.DOTALL))
        mat_phs = ['{{MAT_0_0}}', '{{MAT_1_0}}', '{{MAT_2_0}}', '{{MAT_3_0}}']
        new_tc0 = tc0
        for pi, ph in enumerate(mat_phs[:len(paras)]):
            p = paras[pi].group()
            run = f'<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">{ph}</w:t></w:r>'
            new_tc0 = new_tc0.replace(p, p.replace('</w:p>', run + '</w:p>', 1), 1)
        new_sect = new_sect.replace(tc0, new_tc0, 1)
    # Celle 1-5: para 0 di ognuna
    other_map = {1: '{{ASS_0_0}}', 2: '{{MAT_0_1}}', 3: '{{ASS_0_1}}',
                 4: '{{MAT_0_2}}', 5: '{{ASS_0_2}}'}
    for ci, ph in other_map.items():
        if ci < len(tcs):
            new_tc = inject_cell(tcs[ci].group(), ph, 0)
            if new_tc != tcs[ci].group():
                new_sect = new_sect.replace(tcs[ci].group(), new_tc, 1)
    doc = doc[:tbl_start] + new_sect + doc[tbl_end:]
else:
    print('WARN: tabella materie non trovata')

# ── Step 5b: tabella stage (3 righe dati) ─────────────────────────────────────
# La tabella stage ha 1 riga dati originale; la sostituiamo con 3 righe (stage 1-3).
_stage_tbl_m = None
for _tm in re.finditer(r'<w:tbl>.*?</w:tbl>', doc, re.DOTALL):
    if 'Ore presenza' in _tm.group() and 'VEDI REGISTRO' in _tm.group():
        _stage_tbl_m = _tm
        break
if _stage_tbl_m:
    _stage_tbl = _stage_tbl_m.group()
    _rows = re.findall(r'<w:tr[ >].*?</w:tr>', _stage_tbl, re.DOTALL)
    if len(_rows) >= 2:
        _data_row = _rows[1]
        _cells    = re.findall(r'<w:tc>.*?</w:tc>', _data_row, re.DOTALL)

        def _make_stage_row(n):
            # Cell 0: sostituisce "     /       /" con STAGE_DAL_n e STAGE_AL_n
            c0 = _cells[0]
            paras0 = list(re.finditer(r'<w:p[ >].*?</w:p>', c0, re.DOTALL))
            new_c0 = c0
            for pi, ph_name in [(1, f'STAGE_DAL_{n}'), (3, f'STAGE_AL_{n}')]:
                if pi < len(paras0):
                    para_orig = paras0[pi].group()
                    new_para = re.sub(
                        r'<w:t[^>]*>[^<]*</w:t>',
                        f'<w:t xml:space="preserve">{{{{{ph_name}}}}}</w:t>',
                        para_orig)
                    new_c0 = new_c0.replace(para_orig, new_para, 1)
                    paras0 = list(re.finditer(r'<w:p[ >].*?</w:p>', new_c0, re.DOTALL))
            # Cell 1: sostituisce "VEDI REGISTRO STAGE ALLEGATO" con STAGE_ORE_n
            c1 = _cells[1]
            new_c1 = re.sub(
                r'<w:t[^>]*>VEDI REGISTRO STAGE ALLEGATO</w:t>',
                f'<w:t xml:space="preserve">{{{{STAGE_ORE_{n}}}}}</w:t>', c1)
            # Cell 2: annotazioni docente (para vuoto – aggiungi placeholder)
            c2 = _cells[2]
            paras2 = list(re.finditer(r'<w:p[ >].*?</w:p>', c2, re.DOTALL))
            new_c2 = c2
            if paras2:
                p = paras2[0].group()
                run = (f'<w:r><w:rPr><w:sz w:val="20"/></w:rPr>'
                       f'<w:t xml:space="preserve">{{{{STAGE_NOTE_DOC_{n}}}}}</w:t></w:r>')
                new_c2 = new_c2.replace(p, p.replace('</w:p>', run + '</w:p>', 1), 1)
            # Cell 3: annotazioni tutor (para vuoto – aggiungi placeholder)
            c3 = _cells[3]
            paras3 = list(re.finditer(r'<w:p[ >].*?</w:p>', c3, re.DOTALL))
            new_c3 = c3
            if paras3:
                p = paras3[0].group()
                run = (f'<w:r><w:rPr><w:sz w:val="20"/></w:rPr>'
                       f'<w:t xml:space="preserve">{{{{STAGE_NOTE_TUT_{n}}}}}</w:t></w:r>')
                new_c3 = new_c3.replace(p, p.replace('</w:p>', run + '</w:p>', 1), 1)
            # Ricomponi la riga
            new_row = _data_row
            new_row = new_row.replace(_cells[0], new_c0, 1)
            new_row = new_row.replace(_cells[1], new_c1, 1)
            new_row = new_row.replace(_cells[2], new_c2, 1)
            new_row = new_row.replace(_cells[3], new_c3, 1)
            return new_row

        _new_rows = _make_stage_row(1) + _make_stage_row(2) + _make_stage_row(3)
        _new_tbl  = _stage_tbl.replace(_data_row, _new_rows, 1)
        doc = doc[:_stage_tbl_m.start()] + _new_tbl + doc[_stage_tbl_m.end():]
    else:
        print('WARN: tabella stage: meno di 2 righe')
else:
    print('WARN: tabella stage non trovata')

# ── Step 6: tabella prova pratica ─────────────────────────────────────────────
pratica_hdr = re.search(r'max \.\.\./100</w:t></w:r></w:p></w:tc></w:tr>', doc)
if pratica_hdr:
    p_start = pratica_hdr.end()
    pratica_end_m = re.search(r'Valutazione della Commissione esaminatrice: </w:t></w:r>', doc[p_start:])
    if pratica_end_m:
        p_end = p_start + pratica_end_m.start()
        sect = doc[p_start:p_end]
        col_names = ['COMP', 'ATT', 'INFO', 'PROG', 'ESEC', 'CTRL', 'REG']
        tcs = list(re.finditer(r'<w:tc>.*?</w:tc>', sect, re.DOTALL))
        new_sect = sect
        for ci, tc_m in enumerate(tcs):
            row, col = divmod(ci, 7)
            if row >= 2: break
            ph = '{{PRATICA_' + col_names[col] + '_' + str(row+1) + '}}'
            new_tc = inject_cell(tc_m.group(), ph, 0)
            if new_tc != tc_m.group():
                new_sect = new_sect.replace(tc_m.group(), new_tc, 1)
        doc = doc[:p_start] + new_sect + doc[p_end:]
    else:
        print('WARN: fine sezione pratica non trovata')
else:
    print('WARN: header pratica non trovato')

# ── Step 7: tabella prova teorica ─────────────────────────────────────────────
teorica_hdr_m = re.search(r'B\) Area socio-culturale', doc)
vce_matches = list(re.finditer(r'Valutazione della Commissione esaminatrice: </w:t></w:r>', doc))
teorica_end_start = vce_matches[1].start() if len(vce_matches) >= 2 else None

if teorica_hdr_m and teorica_end_start:
    t_start = teorica_hdr_m.start()
    t_end   = teorica_end_start
    sect = doc[t_start:t_end]
    tcs = list(re.finditer(r'<w:tc>.*?</w:tc>', sect, re.DOTALL))
    new_sect = sect
    for ci, tc_m in enumerate(tcs):
        col = ci % 2
        row = ci // 2
        if row >= 7: break
        ph = '{{TEORICA_' + ('A' if col == 0 else 'B') + '_' + str(row+1) + '}}'
        new_tc = inject_cell(tc_m.group(), ph, 0)
        if new_tc != tc_m.group():
            new_sect = new_sect.replace(tc_m.group(), new_tc, 1)
    doc = doc[:t_start] + new_sect + doc[t_end:]
else:
    print('WARN: tabella teorica non trovata')

# ── Salva ─────────────────────────────────────────────────────────────────────
buf = io.BytesIO()
with zipfile.ZipFile(SRC, 'r') as zin:
    with zipfile.ZipFile(buf, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            zout.writestr(item, doc.encode('utf-8') if item.filename == 'word/document.xml'
                          else zin.read(item.filename))
with open(DST, 'wb') as f:
    f.write(buf.getvalue())
print(f'Salvato: {DST}')

# ── Verifica ──────────────────────────────────────────────────────────────────
with zipfile.ZipFile(DST, 'r') as z:
    chk = z.read('word/document.xml').decode('utf-8')

must = [
    '{{STUDENTE}}','{{TITOLO_CORSO}}','{{CODICE}}','{{NUMERO_SCHEDA}}','{{ANNO_FORM}}',
    '{{ORGANISMO}}','{{ORE_CORSO}}','{{OBIETTIVO}}','{{MATERIE_AREA}}','{{VERIFICHE}}',
    '{{AMMISSIONE}}','{{COMPETENZE}}','{{VALUTAZIONE_FINALE}}','{{GIUDIZIO_TEAM}}',
    '{{FIRMA_TEAM_1}}','{{FIRMA_TEAM_2}}','{{FIRMA_COORD}}',
    '{{AUTO_TESTO}}','{{GIUDIZIO_AUTO}}','{{FIRMA_ALLIEVO}}',
    '{{STAGE_AZIENDA}}','{{STAGE_TUTOR}}','{{STAGE_VAL_TUTOR}}','{{STAGE_GIUDIZIO}}',
    '{{STAGE_DAL_1}}','{{STAGE_AL_1}}','{{STAGE_DAL_2}}','{{STAGE_AL_2}}',
    '{{STAGE_DAL_3}}','{{STAGE_AL_3}}',
    '{{STAGE_ORE_1}}','{{STAGE_ORE_2}}','{{STAGE_ORE_3}}',
    '{{STAGE_NOTE_DOC_1}}','{{STAGE_NOTE_DOC_2}}','{{STAGE_NOTE_DOC_3}}',
    '{{STAGE_NOTE_TUT_1}}','{{STAGE_NOTE_TUT_2}}','{{STAGE_NOTE_TUT_3}}',
    '{{STAGE_FIRMA_DOC}}','{{STAGE_FIRMA_TUT}}',
    '{{REL_OGGETTO}}','{{REL_DITTA}}','{{REL_TUTOR}}','{{REL_VAL_COMM}}','{{REL_GIUDIZIO}}',
    '{{REL_PRESIDENTE}}','{{REL_MEMBRO_1}}','{{REL_MEMBRO_2}}',
    '{{PRATICA_COMP_1}}','{{PRATICA_ATT_1}}','{{PRATICA_COMP_2}}',
    '{{PRATICA_VAL_COMM}}','{{PRATICA_GIUDIZIO}}','{{PRATICA_PRESIDENTE}}',
    '{{PRATICA_MEMBRO_1}}','{{PRATICA_MEMBRO_2}}',
    '{{TEORICA_A_1}}','{{TEORICA_A_2}}','{{TEORICA_B_1}}','{{TEORICA_B_7}}',
    '{{TEORICA_MAX_A}}','{{TEORICA_MAX_B}}',
    '{{TEORICA_GIUDIZIO_A}}','{{TEORICA_GIUDIZIO_B}}','{{TEORICA_VAL_COMM}}','{{TEORICA_GIUDIZIO}}',
    '{{TEORICA_PRESIDENTE}}','{{TEORICA_MEMBRO_1}}','{{TEORICA_MEMBRO_2}}',
    '{{MAT_0_0}}','{{MAT_1_0}}','{{MAT_2_0}}','{{MAT_3_0}}',
    '{{ASS_0_0}}','{{MAT_0_1}}','{{ASS_0_1}}','{{MAT_0_2}}','{{ASS_0_2}}',
]

missing = [ph for ph in must if ph not in chk]
duplicates = [f'{ph}x{chk.count(ph)}' for ph in must if chk.count(ph) > 1]

if not missing and not duplicates:
    print('OK: tutti i placeholder presenti, nessun duplicato!')
else:
    if missing:    print('MANCANTI:', missing)
    if duplicates: print('DUPLICATI:', duplicates)
