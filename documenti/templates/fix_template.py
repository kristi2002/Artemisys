"""
Aggiorna scheda_allievo_template.docx aggiungendo TUTTI i placeholder mancanti.
"""
import zipfile, re, io

SRC = 'scheda_allievo_template.docx'
DST = 'scheda_allievo_template.docx'

with zipfile.ZipFile(SRC, 'r') as z:
    doc = z.read('word/document.xml').decode('utf-8')

def nodes(xml):
    return list(re.finditer(r'<w:t(?:[^>]*)>[^<]*</w:t>', xml))

def inject_before_tab(xml, label_pat, ph, tab_n=1):
    m_label = re.search(label_pat, xml)
    if not m_label:
        print(f'WARN label: {label_pat[:60]}')
        return xml
    after = xml[m_label.end():]
    count = 0
    for m_tab in re.finditer(r'<w:tab/>', after):
        count += 1
        if count == tab_n:
            run = f'<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve"> {ph}</w:t></w:r>'
            pos = m_label.end() + m_tab.start()
            return xml[:pos] + run + xml[pos:]
    print(f'WARN tab({tab_n}): {label_pat[:60]}')
    return xml

def inject_after_label(xml, label_pat, ph, n=1):
    count = 0
    for m in re.finditer(label_pat, xml):
        count += 1
        if count == n:
            run = f'<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve"> {ph}</w:t></w:r>'
            return xml[:m.end()] + run + xml[m.end():]
    print(f'WARN inject_after({n}): {label_pat[:60]}')
    return xml

def inject_into_para(xml, para_id, ph):
    pat = f'w14:paraId="{para_id}"'
    m = re.search(pat, xml)
    if not m:
        print(f'WARN paraId: {para_id}')
        return xml
    ppr_end = xml.find('</w:pPr>', m.start())
    if ppr_end == -1:
        print(f'WARN pPr: {para_id}')
        return xml
    run = f'<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">{ph}</w:t></w:r>'
    pos = ppr_end + len('</w:pPr>')
    return xml[:pos] + run + xml[pos:]

# ── Step 1: Index-based replacement (dall'alto per non shiftare) ──────────────
INDEX_MAP = {
    290: ('{{TEORICA_GIUDIZIO}}', ' xml:space="preserve"'),
    288: ('', ''),
    287: ('', ''),
    283: ('{{TEORICA_GIUDIZIO_B}}', ' xml:space="preserve"'),
    280: ('{{TEORICA_GIUDIZIO_A}}', ' xml:space="preserve"'),
    263: ('{{TEORICA_MAX_B}}', ' xml:space="preserve"'),
    260: ('{{TEORICA_MAX_A}}', ' xml:space="preserve"'),
    216: ('{{REL_GIUDIZIO}}', ' xml:space="preserve"'),
    212: ('{{REL_TUTOR}}', ' xml:space="preserve"'),
    193: ('{{STAGE_GIUDIZIO}}', ' xml:space="preserve"'),
    188: ('{{STAGE_AL_1}}', ' xml:space="preserve"'),
    186: ('{{STAGE_DAL_1}}', ' xml:space="preserve"'),
    180: ('{{STAGE_TUTOR}}', ' xml:space="preserve"'),
    159: ('{{GIUDIZIO_AUTO}}', ' xml:space="preserve"'),
    133: (' {{FIRMA_TEAM_2}}', ' xml:space="preserve"'),
    132: (' {{FIRMA_TEAM_1}}', ' xml:space="preserve"'),
    129: ('{{GIUDIZIO_TEAM}}', ' xml:space="preserve"'),
    98:  ('{{MATERIE_AREA}}', ' xml:space="preserve"'),
    96:  ('', ''), 95: ('', ''), 94: ('', ''), 93: ('', ''),
    92:  ('', ''), 91: ('', ''), 90: ('', ''), 89: ('', ''),
    88:  ('', ''), 87: ('', ''), 86: ('', ''), 85: ('', ''),
    84:  ('', ''), 83: ('', ''), 82: ('', ''), 81: ('', ''),
    80:  ('{{OBIETTIVO}}', ' xml:space="preserve"'),
    72:  ('{{ORGANISMO}}', ' xml:space="preserve"'),
    69:  ('{{ANNO_FORM}}', ' xml:space="preserve"'),
    55:  ('{{TITOLO_CORSO}}', ' xml:space="preserve"'),
    52:  ('{{STUDENTE}}', ' xml:space="preserve"'),
}

all_n = nodes(doc)
for idx, (new_text, attr) in INDEX_MAP.items():
    if idx >= len(all_n):
        print(f'WARN idx {idx} fuori range ({len(all_n)} nodi)')
        continue
    m = all_n[idx]
    doc = doc[:m.start()] + f'<w:t{attr}>{new_text}</w:t>' + doc[m.end():]
    all_n = nodes(doc)

# ── Step 2: Giudizio pratica ──────────────────────────────────────────────────
doc = doc.replace(
    '<w:t>Giudizio: ________/100</w:t>',
    '<w:t xml:space="preserve">Giudizio: {{PRATICA_GIUDIZIO}}/100</w:t>'
)

# ── Step 3: inject_before_tab ────────────────────────────────────────────────
doc = inject_before_tab(doc, r'Codice</w:t></w:r>', '{{CODICE}}', 1)
doc = inject_before_tab(doc, r'Scheda n\S</w:t></w:r>', '{{NUMERO_SCHEDA}}', 1)
doc = inject_before_tab(doc, r'L\S*allievo:</w:t></w:r>', '{{FIRMA_ALLIEVO}}', 1)
doc = inject_before_tab(doc, r'Ore corso</w:t></w:r>', '{{ORE_CORSO}}', 1)

# ── Step 4: inject_after_label ───────────────────────────────────────────────
doc = inject_after_label(doc, r'Valutazione del tutor sullo stage in termini di attenzione', '{{STAGE_VAL_TUTOR}}')

# ── Step 5: materie righe extra (paraId fissi del template) ──────────────────
for para_id, ph in [('0E31373F', '{{MAT_1_0}}'), ('112116AD', '{{MAT_2_0}}'), ('3D6F9376', '{{MAT_3_0}}')]:
    doc = inject_into_para(doc, para_id, ph)

# ── Step 6: prova pratica table cells ────────────────────────────────────────
# La sezione dati è tra la fine dell'header row e 'Valutazione della Commissione' (scheda 5)
# Cerca la fine dell'ultimo header 'max .../100' nella scheda 5
header_end_m = list(re.finditer(r'max \.\.\./100</w:t></w:r></w:p></w:tc></w:tr>', doc))
if header_end_m:
    pratica_start = header_end_m[-1].end()
    pratica_end_m = re.search(r'Valutazione della Commissione esaminatrice:', doc[pratica_start:])
    if pratica_end_m:
        pratica_end = pratica_start + pratica_end_m.start()
        sect = doc[pratica_start:pratica_end]
        col_names = ['COMP', 'ATT', 'INFO', 'PROG', 'ESEC', 'CTRL', 'REG']
        tc_matches = list(re.finditer(r'<w:tc>.*?</w:tc>', sect, re.DOTALL))
        new_sect = sect
        for ci, tc_m in enumerate(tc_matches):
            row = ci // 7
            col = ci % 7
            if row >= 7:
                break
            ph = '{{PRATICA_' + col_names[col] + '_' + str(row+1) + '}}'
            tc_xml = tc_m.group()
            paras = list(re.finditer(r'<w:p[ >].*?</w:p>', tc_xml, re.DOTALL))
            if not paras:
                continue
            p = paras[0].group()
            run = f'<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">{ph}</w:t></w:r>'
            new_p = p.replace('</w:p>', run + '</w:p>', 1)
            new_tc = tc_xml.replace(p, new_p, 1)
            new_sect = new_sect.replace(tc_m.group(), new_tc, 1)
        doc = doc[:pratica_start] + new_sect + doc[pratica_end:]
    else:
        print('WARN: fine sezione pratica non trovata')
else:
    print('WARN: header pratica non trovato')

# ── Salva ────────────────────────────────────────────────────────────────────
buf = io.BytesIO()
with zipfile.ZipFile(SRC, 'r') as zin:
    with zipfile.ZipFile(buf, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            if item.filename == 'word/document.xml':
                zout.writestr(item, doc.encode('utf-8'))
            else:
                zout.writestr(item, zin.read(item.filename))
with open(DST, 'wb') as f:
    f.write(buf.getvalue())
print(f'Salvato: {DST}')

# ── Verifica ─────────────────────────────────────────────────────────────────
with zipfile.ZipFile(DST, 'r') as z:
    chk = z.read('word/document.xml').decode('utf-8')

must = [
    '{{STUDENTE}}','{{TITOLO_CORSO}}','{{CODICE}}','{{NUMERO_SCHEDA}}','{{ANNO_FORM}}',
    '{{ORGANISMO}}','{{ORE_CORSO}}','{{OBIETTIVO}}','{{MATERIE_AREA}}','{{VERIFICHE}}',
    '{{AMMISSIONE}}','{{COMPETENZE}}','{{VALUTAZIONE_FINALE}}','{{GIUDIZIO_TEAM}}',
    '{{FIRMA_TEAM_1}}','{{FIRMA_TEAM_2}}','{{FIRMA_COORD}}',
    '{{AUTO_TESTO}}','{{GIUDIZIO_AUTO}}','{{FIRMA_ALLIEVO}}',
    '{{STAGE_AZIENDA}}','{{STAGE_TUTOR}}','{{STAGE_VAL_TUTOR}}','{{STAGE_GIUDIZIO}}',
    '{{STAGE_FIRMA_DOC}}','{{STAGE_FIRMA_TUT}}','{{STAGE_DAL_1}}','{{STAGE_AL_1}}',
    '{{REL_OGGETTO}}','{{REL_DITTA}}','{{REL_TUTOR}}','{{REL_VAL_COMM}}','{{REL_GIUDIZIO}}',
    '{{REL_PRESIDENTE}}','{{REL_MEMBRO_1}}','{{REL_MEMBRO_2}}',
    '{{PRATICA_COMP_1}}','{{PRATICA_ATT_1}}','{{PRATICA_COMP_2}}',
    '{{PRATICA_VAL_COMM}}','{{PRATICA_GIUDIZIO}}','{{PRATICA_PRESIDENTE}}',
    '{{PRATICA_MEMBRO_1}}','{{PRATICA_MEMBRO_2}}',
    '{{TEORICA_A_1}}','{{TEORICA_B_1}}','{{TEORICA_MAX_A}}','{{TEORICA_MAX_B}}',
    '{{TEORICA_GIUDIZIO_A}}','{{TEORICA_GIUDIZIO_B}}','{{TEORICA_VAL_COMM}}','{{TEORICA_GIUDIZIO}}',
    '{{TEORICA_PRESIDENTE}}','{{TEORICA_MEMBRO_1}}','{{TEORICA_MEMBRO_2}}',
    '{{MAT_0_0}}','{{MAT_1_0}}','{{MAT_2_0}}','{{MAT_3_0}}',
    '{{ASS_0_0}}','{{MAT_0_1}}','{{ASS_0_1}}','{{MAT_0_2}}','{{ASS_0_2}}',
]
all_ok = True
for ph in must:
    if ph not in chk:
        print(f'  MANCANTE: {ph}')
        all_ok = False
if all_ok:
    print('OK: Tutti i placeholder presenti!')
