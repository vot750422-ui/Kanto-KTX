"""Chuyen bao cao tien do (Markdown) sang .docx theo mau BM 1.8.a voi ho tro Muc luc va Hyperlink."""
import re
import sys
from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.shared import Pt, Cm, RGBColor
from docx.oxml import OxmlElement
from docx.oxml.ns import qn


def set_font(run, size=13, bold=False, italic=False, color=None):
    run.font.name = "Times New Roman"
    run.font.size = Pt(size)
    run.bold = bold
    run.italic = italic
    if color:
        run.font.color.rgb = color
    rpr = run._element.get_or_add_rPr()
    rfonts = rpr.find(qn("w:rFonts"))
    if rfonts is None:
        rfonts = rpr.makeelement(qn("w:rFonts"), {})
        rpr.append(rfonts)
    rfonts.set(qn("w:ascii"), "Times New Roman")
    rfonts.set(qn("w:hAnsi"), "Times New Roman")
    rfonts.set(qn("w:cs"), "Times New Roman")


_bm_id = 100


def add_bookmark(paragraph, name):
    global _bm_id
    _bm_id += 1
    bm_id_str = str(_bm_id)
    clean_name = re.sub(r"[^a-zA-Z0-9_]", "_", name.strip("#").replace("-", "_"))[:40]

    b_start = OxmlElement('w:bookmarkStart')
    b_start.set(qn('w:id'), bm_id_str)
    b_start.set(qn('w:name'), clean_name)
    paragraph._p.append(b_start)

    b_end = OxmlElement('w:bookmarkEnd')
    b_end.set(qn('w:id'), bm_id_str)
    paragraph._p.append(b_end)


def add_internal_hyperlink(paragraph, text, anchor, size=13, bold=False):
    clean_anchor = re.sub(r"[^a-zA-Z0-9_]", "_", anchor.strip("#").replace("-", "_"))[:40]

    hyperlink = OxmlElement('w:hyperlink')
    hyperlink.set(qn('w:anchor'), clean_anchor)
    hyperlink.set(qn('w:history'), '1')

    new_run = OxmlElement('w:r')
    rPr = OxmlElement('w:rPr')

    rFonts = OxmlElement('w:rFonts')
    rFonts.set(qn('w:ascii'), 'Times New Roman')
    rFonts.set(qn('w:hAnsi'), 'Times New Roman')
    rFonts.set(qn('w:cs'), 'Times New Roman')
    rPr.append(rFonts)

    sz = OxmlElement('w:sz')
    sz.set(qn('w:val'), str(int(size * 2)))
    rPr.append(sz)

    u = OxmlElement('w:u')
    u.set(qn('w:val'), 'single')
    rPr.append(u)

    c = OxmlElement('w:color')
    c.set(qn('w:val'), '0563C1')  # Standard Word hyperlink blue
    rPr.append(c)

    if bold:
        b = OxmlElement('w:b')
        rPr.append(b)

    new_run.append(rPr)
    text_elem = OxmlElement('w:t')
    text_elem.text = text
    new_run.append(text_elem)
    hyperlink.append(new_run)
    paragraph._p.append(hyperlink)


def add_rich_para(doc, text, size=13, align=None, space_after=6, left_indent=0, style=None, bookmarks=None, italic=False):
    """Ho tro **bold**, `code`, [link](#anchor) va bookmarks."""
    if style and style in doc.styles:
        p = doc.add_paragraph(style=style)
    else:
        p = doc.add_paragraph()

    if align is not None:
        p.alignment = align
    p.paragraph_format.space_after = Pt(space_after)
    if left_indent > 0:
        p.paragraph_format.left_indent = Cm(left_indent)

    if bookmarks:
        for bm in bookmarks:
            add_bookmark(p, bm)

    bt = chr(96)
    pattern = re.compile(r'(\[[^\]]+\]\(#?[^\)]+\)|\*\*[^*]+\*\*|' + bt + r'[^' + bt + r']+' + bt + r')')

    for part in pattern.split(text):
        if not part:
            continue
        m_link = re.match(r'^\[([^\]]+)\]\((#?[^\)]+)\)$', part)
        if m_link:
            label = m_link.group(1)
            target = m_link.group(2)
            add_internal_hyperlink(p, label, target, size=size)
        elif part.startswith("**") and part.endswith("**"):
            run = p.add_run(part[2:-2])
            set_font(run, size, bold=True, italic=italic)
        elif part.startswith(bt) and part.endswith(bt):
            run = p.add_run(part[1:-1])
            set_font(run, size, italic=italic)
        else:
            run = p.add_run(part)
            set_font(run, size, italic=italic)
    return p


def add_table(doc, rows):
    table = doc.add_table(rows=len(rows), cols=len(rows[0]))
    table.style = "Table Grid"
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    for r, row in enumerate(rows):
        for c, cell_text in enumerate(row):
            cell = table.cell(r, c)
            cell.text = ""
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(2)
            for part in re.split(r"(\*\*[^*]+\*\*|`[^`]+`)", cell_text):
                if not part:
                    continue
                if part.startswith("**") and part.endswith("**"):
                    run = p.add_run(part[2:-2])
                    set_font(run, 11, bold=True)
                elif part.startswith("`") and part.endswith("`"):
                    run = p.add_run(part[1:-1])
                    set_font(run, 11)
                else:
                    run = p.add_run(part)
                    set_font(run, 11, bold=(r == 0))
    return table


def convert(md_path, docx_path):
    with open(md_path, encoding="utf-8") as f:
        lines = f.read().splitlines()

    doc = Document()
    section = doc.sections[0]
    section.top_margin = Cm(2)
    section.bottom_margin = Cm(2)
    section.left_margin = Cm(3)
    section.right_margin = Cm(2)

    style = doc.styles["Normal"]
    style.font.name = "Times New Roman"
    style.font.size = Pt(13)

    # Thiet lap font Times New Roman va mau den cho cac cap Heading
    for lvl in range(1, 5):
        h_style = f"Heading {lvl}"
        if h_style in doc.styles:
            st = doc.styles[h_style]
            st.font.name = "Times New Roman"
            st.font.color.rgb = RGBColor(0, 0, 0)
            st.font.size = Pt(16 if lvl == 1 else (14 if lvl == 2 else 13))
            st.font.bold = True

    pending_bookmarks = []
    i = 0
    while i < len(lines):
        line = lines[i].rstrip()

        if line.strip() == "---":
            i += 1
            continue

        if not line.strip():
            i += 1
            continue

        # The neo HTML (bookmark anchor): <a id="..."></a>
        m_anchor = re.match(r'^\s*<a\s+(?:id|name)="([^"]+)"><\/a>\s*$', line)
        if m_anchor:
            clean_anchor = re.sub(r"[^a-zA-Z0-9_]", "_", m_anchor.group(1).strip("#").replace("-", "_"))[:40]
            if clean_anchor not in pending_bookmarks:
                pending_bookmarks.append(clean_anchor)
            i += 1
            continue

        # Bang markdown
        if line.lstrip().startswith("|"):
            block = []
            while i < len(lines) and lines[i].lstrip().startswith("|"):
                block.append(lines[i].strip())
                i += 1
            rows = []
            for b in block:
                if re.fullmatch(r"\|[\s\-:|]+\|", b):
                    continue
                cells = [c.strip() for c in b.strip("|").split("|")]
                rows.append(cells)
            if rows:
                add_table(doc, rows)
                doc.add_paragraph()
            continue

        # Tieu de (Heading)
        m = re.match(r"^(#{1,4})\s+(.*)$", line)
        if m:
            level = len(m.group(1))
            text = m.group(2).strip()
            align = WD_ALIGN_PARAGRAPH.CENTER if level == 1 else None
            size = {1: 16, 2: 14, 3: 13, 4: 13}[level]

            # Tu dong tao bookmark neu tieu de co danh so dang 3.x
            m_sec = re.match(r"^(\d+(?:\.\d+)*)\.?\s+", text)
            if m_sec:
                auto_bm = "sec_" + m_sec.group(1).replace(".", "_")
                if auto_bm not in pending_bookmarks:
                    pending_bookmarks.append(auto_bm)
            elif "MỤC LỤC" in text.upper():
                if "muc_luc" not in pending_bookmarks:
                    pending_bookmarks.append("muc_luc")

            h_style = f"Heading {level}" if f"Heading {level}" in doc.styles else None
            add_rich_para(doc, text, size=size, align=align, space_after=8, style=h_style, bookmarks=pending_bookmarks)
            pending_bookmarks = []
            i += 1
            continue

        # Trich dan blockquote (> ...)
        if line.lstrip().startswith(">"):
            quote_text = re.sub(r"^\s*>\s*", "", line)
            add_rich_para(doc, quote_text, size=12, italic=True, left_indent=0.6, space_after=6, bookmarks=pending_bookmarks)
            pending_bookmarks = []
            i += 1
            continue

        # Danh sach danh so (1. ...)
        m_num = re.match(r"^(\d+)\.\s+(.*)$", line)
        if m_num:
            add_rich_para(doc, f"{m_num.group(1)}. {m_num.group(2)}", space_after=4, bookmarks=pending_bookmarks)
            pending_bookmarks = []
            i += 1
            continue

        # Danh sach gach dau dong (co the thụt lề cho mục lục cấp con)
        m_bullet = re.match(r"^(\s*)[-*]\s+(.*)$", line)
        if m_bullet:
            indent_spaces = len(m_bullet.group(1))
            indent_cm = 0.5 + (0.5 if indent_spaces >= 2 else 0)
            prefix = "• " if indent_spaces < 2 else "– "
            add_rich_para(doc, f"{prefix}{m_bullet.group(2)}", space_after=3, left_indent=indent_cm, bookmarks=pending_bookmarks)
            pending_bookmarks = []
            i += 1
            continue

        # Doan van thuong
        add_rich_para(doc, line.strip(), space_after=6, bookmarks=pending_bookmarks)
        pending_bookmarks = []
        i += 1

    doc.save(docx_path)
    print(f"Da tao: {docx_path}")


if __name__ == "__main__":
    convert(sys.argv[1], sys.argv[2])
