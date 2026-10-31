"""Chuyen bao cao tien do (Markdown) sang .docx theo mau BM 1.8.a."""
import re
import sys
from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.shared import Pt, Cm


def set_font(run, size=13, bold=False):
    run.font.name = "Times New Roman"
    run.font.size = Pt(size)
    run.bold = bold
    rpr = run._element.get_or_add_rPr()
    rfonts = rpr.find("{http://schemas.openxmlformats.org/wordprocessingml/2006/main}rFonts")
    if rfonts is None:
        from docx.oxml.ns import qn
        rfonts = rpr.makeelement(qn("w:rFonts"), {})
        rpr.append(rfonts)
    from docx.oxml.ns import qn
    rfonts.set(qn("w:ascii"), "Times New Roman")
    rfonts.set(qn("w:hAnsi"), "Times New Roman")
    rfonts.set(qn("w:cs"), "Times New Roman")


def add_para(doc, text, size=13, bold=False, align=None, space_after=6):
    p = doc.add_paragraph()
    if align is not None:
        p.alignment = align
    p.paragraph_format.space_after = Pt(space_after)
    run = p.add_run(text)
    set_font(run, size, bold)
    return p


def add_rich_para(doc, text, size=13, align=None, space_after=6):
    """Ho tro **bold** va `code` trong dong."""
    p = doc.add_paragraph()
    if align is not None:
        p.alignment = align
    p.paragraph_format.space_after = Pt(space_after)
    for part in re.split(r"(\*\*[^*]+\*\*|`[^`]+`)", text):
        if not part:
            continue
        if part.startswith("**") and part.endswith("**"):
            run = p.add_run(part[2:-2])
            set_font(run, size, True)
        elif part.startswith("`") and part.endswith("`"):
            run = p.add_run(part[1:-1])
            set_font(run, size)
        else:
            run = p.add_run(part)
            set_font(run, size)
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
                    set_font(run, 11, True)
                elif part.startswith("`") and part.endswith("`"):
                    run = p.add_run(part[1:-1])
                    set_font(run, 11)
                else:
                    run = p.add_run(part)
                    set_font(run, 11, r == 0)
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

    i = 0
    while i < len(lines):
        line = lines[i].rstrip()

        if line.strip() == "---":
            i += 1
            continue

        if not line.strip():
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

        # Tieu de
        m = re.match(r"^(#{1,4})\s+(.*)$", line)
        if m:
            level = len(m.group(1))
            text = m.group(2).strip()
            align = WD_ALIGN_PARAGRAPH.CENTER if level <= 2 else None
            size = {1: 16, 2: 14, 3: 13, 4: 13}[level]
            add_rich_para(doc, text, size=size, align=align, space_after=8)
            i += 1
            continue

        # Danh sach danh so
        m = re.match(r"^(\d+)\.\s+(.*)$", line)
        if m:
            add_rich_para(doc, f"{m.group(1)}. {m.group(2)}", space_after=4)
            i += 1
            continue

        # Danh sach gach dau dong
        m = re.match(r"^[-*]\s+(.*)$", line)
        if m:
            add_rich_para(doc, f"• {m.group(1)}", space_after=4)
            i += 1
            continue

        # Doan van thuong
        add_rich_para(doc, line.strip())
        i += 1

    doc.save(docx_path)
    print(f"Da tao: {docx_path}")


if __name__ == "__main__":
    convert(sys.argv[1], sys.argv[2])
