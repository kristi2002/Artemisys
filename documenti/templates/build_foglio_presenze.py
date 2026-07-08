"""
Genera foglio_presenze_template.docx partendo dalla struttura di scheda_allievo_blank.docx
(che funziona correttamente in Word) e sostituendo il body con il foglio presenze.

Placeholder sostituiti in PHP:
  {{ANNO_FORM}}         anno formativo (es. 2024-2025)
  {{COD_CORSO}}         codice corso
  {{TIPOL}}             tipologia
  {{ORE}}               ore corso
  {{ALL}}               All.
  {{COD_DID_REG}}       cod.did.reg.
  {{DENOMINAZIONE}}     nome corso
  {{ENTE_GESTORE}}      ente gestore
  {{GIORNO_ESAME}}      es. "Lunedì 3 Giugno 2024"
  {{ORARIO_MATTINA}}    es. "08:30 – 13:30"
  {{ORARIO_POMERIGGIO}} es. "14:30 – 17:30"
  {{SEDE_NOME}}         nome sede
  {{SEDE_INDIRIZZO}}    indirizzo sede
  {{COMMISSIONE_ROWS}}  blocco <w:tr>…</w:tr> per ogni membro
"""

import zipfile, io, re

BASE  = 'scheda_allievo_blank.docx'   # struttura ZIP valida di riferimento
DST   = 'foglio_presenze_template.docx'

# Apri il base e leggi la struttura dei namespace dal suo document.xml
with zipfile.ZipFile(BASE) as z:
    base_doc = z.read('word/document.xml').decode('utf-8')
    base_files = {name: z.read(name) for name in z.namelist()}

# Estrai il tag di apertura <w:document ...> per riutilizzare i namespace
m = re.match(r'(<w:document[^>]+>)', base_doc)
doc_open_tag = m.group(1) if m else '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'

# ── Helpers XML ───────────────────────────────────────────────────────────────
def rpr_xml(sz=18, bold=False):
    b = '<w:b/><w:bCs/>' if bold else ''
    return (f'<w:rPr>'
            f'<w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/>'
            f'{b}'
            f'<w:sz w:val="{sz}"/><w:szCs w:val="{sz}"/>'
            f'</w:rPr>')

def ppr_xml(align='left', sb=0, sa=0, keep_next=False):
    jc = f'<w:jc w:val="{align}"/>' if align != 'left' else ''
    sp = f'<w:spacing w:before="{sb}" w:after="{sa}"/>'
    kn = '<w:keepNext/>' if keep_next else ''
    return f'<w:pPr>{kn}{sp}{jc}</w:pPr>'

def run(text, sz=18, bold=False):
    sp = ' xml:space="preserve"' if text else ''
    return f'<w:r>{rpr_xml(sz,bold)}<w:t{sp}>{text}</w:t></w:r>'

def para(content_xml='', align='left', sb=0, sa=0):
    return f'<w:p>{ppr_xml(align,sb,sa)}{content_xml}</w:p>'

# ── Costruzione tabelle ───────────────────────────────────────────────────────
# Pagina A4 - margini sx/dx 720 ciascuno = 11906 - 1440 = 10466 DXA
W = 10466

def tcpr(w, shade=None, valign='top', no_wrap=False):
    brd = ('<w:tcBorders>'
           '<w:top w:val="single" w:sz="6" w:color="000000"/>'
           '<w:left w:val="single" w:sz="6" w:color="000000"/>'
           '<w:bottom w:val="single" w:sz="6" w:color="000000"/>'
           '<w:right w:val="single" w:sz="6" w:color="000000"/>'
           '</w:tcBorders>')
    shd = f'<w:shd w:val="clear" w:color="auto" w:fill="{shade}"/>' if shade else ''
    va  = f'<w:vAlign w:val="{valign}"/>' if valign != 'top' else ''
    nw  = '<w:noWrap/>' if no_wrap else ''
    return (f'<w:tcPr>'
            f'<w:tcW w:w="{w}" w:type="dxa"/>'
            f'{brd}{shd}'
            f'<w:tcMar>'
            f'<w:top w:w="40" w:type="dxa"/><w:left w:w="80" w:type="dxa"/>'
            f'<w:bottom w:w="40" w:type="dxa"/><w:right w:w="80" w:type="dxa"/>'
            f'</w:tcMar>'
            f'{va}{nw}'
            f'</w:tcPr>')

def cell(w, *paras, shade=None, valign='top'):
    content = ''.join(paras) if paras else '<w:p/>'
    return f'<w:tc>{tcpr(w, shade=shade, valign=valign)}{content}</w:tc>'

def trow(*cells, hdr=False):
    hd = '<w:trPr><w:tblHeader/></w:trPr>' if hdr else ''
    return f'<w:tr>{hd}{"".join(cells)}</w:tr>'

def tbl(*rows, total=W):
    return (f'<w:tbl>'
            f'<w:tblPr>'
            f'<w:tblW w:w="{total}" w:type="dxa"/>'
            f'<w:tblBorders>'
            f'<w:top w:val="single" w:sz="6" w:color="000000"/>'
            f'<w:left w:val="single" w:sz="6" w:color="000000"/>'
            f'<w:bottom w:val="single" w:sz="6" w:color="000000"/>'
            f'<w:right w:val="single" w:sz="6" w:color="000000"/>'
            f'<w:insideH w:val="single" w:sz="6" w:color="000000"/>'
            f'<w:insideV w:val="single" w:sz="6" w:color="000000"/>'
            f'</w:tblBorders>'
            f'<w:tblCellMar>'
            f'<w:top w:w="40" w:type="dxa"/><w:left w:w="80" w:type="dxa"/>'
            f'<w:bottom w:w="40" w:type="dxa"/><w:right w:w="80" w:type="dxa"/>'
            f'</w:tblCellMar>'
            f'</w:tblPr>'
            f'{"".join(rows)}'
            f'</w:tbl>')

# ── Layout documento ──────────────────────────────────────────────────────────
parts = []

# 1. Riga intestazione: Mod. F-PRES | ANNO FORMATIVO
W_MOD = 1800
parts.append(tbl(
    trow(
        cell(W_MOD,  para(run('Mod. F-PRES', 16, bold=True))),
        cell(W - W_MOD, para(
            run('ANNO FORMATIVO ', 18, bold=True) +
            run('{{ANNO_FORM}}', 18),
            align='right'
        )),
    ), total=W
))

# 2. Tipo corso
parts.append(para(
    run('□  CORSO F.S.E.  Annualità ............. Asse .....  Misura .....    '
        '□  CORSO AUTORIZZATO  (Art. 10 - comma 2 e 3) - L.R. n. 16/90    '
        '□  CORSO PIANO ORDINARIO  (Art. 9 - L.R. n. 16/90)', 15),
    sb=80, sa=40
))
parts.append(para(run('(Barrare la tipologia di corso che ricorre)', 14), sa=40))

# 3. Titolo
parts.append(para(
    run('FOGLIO PRESENZA COMPONENTI COMMISSIONE  ESAME', 24, bold=True),
    align='center', sb=80, sa=80
))

# 4. Dati corso
parts.append(para(run('Dati relativi al corso:', 17, bold=True), sb=40, sa=20))

A=1300; B=1400; C=750; D=800; E=700; F=900; G=700; H=750
rest = W - A - B - C - D - E - F - G - H
parts.append(tbl(
    trow(
        cell(A,  para(run('Cod. corso', 15, bold=True))),
        cell(B,  para(run('{{COD_CORSO}}', 15))),
        cell(C,  para(run('Tipol:', 15, bold=True))),
        cell(D,  para(run('{{TIPOL}}', 15))),
        cell(E,  para(run('Ore:', 15, bold=True))),
        cell(F,  para(run('{{ORE}}', 15))),
        cell(G,  para(run('All.:', 15, bold=True))),
        cell(H,  para(run('{{ALL}}', 15))),
        cell(rest, para(
            run('Cod.Did.Reg: ', 15, bold=True) +
            run('{{COD_DID_REG}}', 15)
        )),
    ), total=W
))

LBL = 1400
parts.append(tbl(
    trow(
        cell(LBL,   para(run('Denominazione:', 15, bold=True))),
        cell(W-LBL, para(run('{{DENOMINAZIONE}}', 15))),
    ), total=W
))
parts.append(tbl(
    trow(
        cell(LBL,   para(run('Ente Gestore:', 15, bold=True))),
        cell(W-LBL, para(run('{{ENTE_GESTORE}}', 15))),
    ), total=W
))

# 5. Calendario + sede
parts.append(para(run('Calendario degli esami:', 17, bold=True), sb=80, sa=20))
CAL_W = 3000
parts.append(tbl(
    trow(
        cell(CAL_W,   para(run('{{GIORNO_ESAME}}', 15))),
        cell(1600,    para(run('Orario mattina:', 15, bold=True))),
        cell(W-CAL_W-1600, para(run('{{ORARIO_MATTINA}}', 15))),
    ),
    trow(
        cell(CAL_W,   para(run('', 15))),
        cell(1600,    para(run('Orario pomeriggio:', 15, bold=True))),
        cell(W-CAL_W-1600, para(run('{{ORARIO_POMERIGGIO}}', 15))),
    ),
    total=W
))
parts.append(tbl(
    trow(
        cell(LBL,   para(run('Sede di esame:', 15, bold=True))),
        cell(W-LBL, para(
            run('{{SEDE_NOME}}', 15) +
            run('  –  ', 15) +
            run('{{SEDE_INDIRIZZO}}', 15)
        )),
    ), total=W
))

# 6. Tabella commissione
parts.append(para('', sb=80))

WC  = 4500   # Componente
WF  = 2200   # Firma mattina / pomeriggio
WA  = W - WC - WF*2   # Assenze = 1566

parts.append(tbl(
    # Header
    trow(
        cell(WC,  para(run('Componente',       17, bold=True), align='center'), shade='D9E1F2'),
        cell(WF,  para(run('Firma mattina',    17, bold=True), align='center'), shade='D9E1F2'),
        cell(WF,  para(run('Firma pomeriggio', 17, bold=True), align='center'), shade='D9E1F2'),
        cell(WA,  para(run('Assenze',          17, bold=True), align='center'), shade='D9E1F2'),
        hdr=True
    ),
    # Riga placeholder: sostituita in PHP con le righe reali della commissione
    trow(
        cell(WC,  para(run('{{COMMISSIONE_ROWS}}', 15))),
        cell(WF,  para('')),
        cell(WF,  para('')),
        cell(WA,  para('')),
    ),
    total=W
))

# 7. Riservato ufficio
parts.append(para('', sb=60))
parts.append(tbl(
    trow(
        cell(2400, para(run('Riservato  Ufficio', 15, bold=True))),
        cell(W-2400, para(
            run('COMMISSIONE ESAME  N. ', 15, bold=True) +
            run('...............', 15)
        )),
    ), total=W
))

# 8. Nota
parts.append(para('', sb=40))
parts.append(tbl(
    trow(
        cell(700,   para(run('Nota', 14, bold=True))),
        cell(W-700, para(run(
            'Il presente modello, redatto per ogni singolo giorno di esame, '
            'deve essere inviato al Servizio formazione professionale '
            'unitamente alla documentazione di fine corso.', 14
        ))),
    ), total=W
))

# ── Assembla body ─────────────────────────────────────────────────────────────
body_content = '\n'.join(parts)

sectPr = (
    '<w:sectPr>'
    '<w:pgSz w:w="11906" w:h="16838"/>'
    '<w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720" '
    'w:header="360" w:footer="360" w:gutter="0"/>'
    '</w:sectPr>'
)

new_document_xml = (
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n'
    + doc_open_tag
    + '\n<w:body>\n'
    + body_content
    + '\n' + sectPr
    + '\n</w:body>\n</w:document>'
)

# ── Scrivi il nuovo docx (struttura del base + nuovo document.xml) ─────────────
buf = io.BytesIO()
with zipfile.ZipFile(buf, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
    for name, data in base_files.items():
        if name == 'word/document.xml':
            zout.writestr(name, new_document_xml.encode('utf-8'))
        else:
            zout.writestr(name, data)

with open(DST, 'wb') as f:
    f.write(buf.getvalue())

print(f'Creato: {DST}')

# ── Verifica placeholder ──────────────────────────────────────────────────────
with zipfile.ZipFile(DST) as z:
    chk = z.read('word/document.xml').decode('utf-8')

phs = ['{{ANNO_FORM}}','{{COD_CORSO}}','{{TIPOL}}','{{ORE}}','{{ALL}}',
       '{{COD_DID_REG}}','{{DENOMINAZIONE}}','{{ENTE_GESTORE}}',
       '{{GIORNO_ESAME}}','{{ORARIO_MATTINA}}','{{ORARIO_POMERIGGIO}}',
       '{{SEDE_NOME}}','{{SEDE_INDIRIZZO}}','{{COMMISSIONE_ROWS}}']

ok = True
for ph in phs:
    if ph not in chk:
        print('  MANCANTE:', ph); ok = False
if ok:
    print('OK: tutti i placeholder presenti!')
