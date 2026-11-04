#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Sua toan bo cac loi da phat hien trong quyen bao cao
BAO_CAO_DU_THAO_DO_AN_1_KANTO_KTX_FULL.docx
  -> QUYEN_BAO_CAO_DO_AN_1_KANTO_KTX_HOAN_THIEN.docx


Cach chay (tren may Windows co file goc):
    python fix_kanto_report.py
    python fix_kanto_report.py --src "D:\\ban-sao.docx" --dst "D:\\out.docx"
    python fix_kanto_report.py --strict          # thoat ma 1 neu co muc nao 0 luot sua / co canh bao


Nguyen tac quan trong:
  * KHONG ghi de file goc. Luon luu ra file moi.
  * KHONG dung paragraph.text = ... (cach do xoa het format cua cac run).
    Moi phep thay the deu giu nguyen run: neu cum can thay nam trong
    1 run thi sua run do; neu cat ngang nhieu run thi dat text moi vao
    run dau tien, xoa cac run giua, giu phan duoi cua run cuoi.
  * python-docx khong nen / khong giai nen lai anh nhung: anh trong
    word/media/ duoc giu nguyen khi load/save. Script dem so media
    truoc/sau de kiem chung (file goc: 88 hinh).
  * Moi muc deu ghi log so luot thay doi. Neu mot muc = 0, script KHONG
    tu bien ra noi dung moi (tru 2 truong hop da ghi ro: 3.4.4 va 2.3.24,
    chi chen cau khi cum dich chua ton tai) ma in WARNING de ban kiem
    tra thu cong -- tot hon la sua sai cho.
  * w:updateFields (muc 13) duoc chen bang cach sua chuoi trong
    word/settings.xml sau khi save, khong dung XML parser viet lai
    toan bo file settings (tranh lam hong namespace/prefix khac).
    Word khi mo file se cap nhat field; neu Word hoi, chon
    "Update entire table" cho Muc luc.
"""


from __future__ import annotations


import argparse
import os
import re
import shutil
import sys
import tempfile
import zipfile
from collections import Counter


from docx import Document
from docx.oxml.ns import qn
from docx.shared import Pt
from docx.table import Table
from docx.text.paragraph import Paragraph


# ------------------------------------------------------------------ cau hinh


SRC_DEFAULT = r"F:\Kanto-KTX\docs\reports\complete\BAO_CAO_DU_THAO_DO_AN_1_KANTO_KTX_FULL.docx"
DST_DEFAULT = r"F:\Kanto-KTX\docs\reports\complete\QUYEN_BAO_CAO_DO_AN_1_KANTO_KTX_HOAN_THIEN.docx"


# Sap xep lai cac dong trong bang tu viet tat theo A-Z sau khi them.
# Dat False neu bang goc co nhom rieng (vi du: nhom theo chuong) va
# ban khong muon dao thu tu cac nhom do.
SORT_ABBREV_TABLE = True


NOTE_273 = (
    "(Tổng cộng 273 lượt kiểm chứng chức năng theo 22 ca sử dụng chi tiết, "
    "do các ca kiểm thử tích hợp bao phủ liên thông nhiều Use Case)"
)
NOTE_UC_COUNT = (
    "Ghi chú: Hệ thống gồm 18 Use Case cốt lõi, được phân rã thành 22 kịch bản "
    "đặc tả chi tiết (UC04, UC05, UC07, UC10 được tách thành các phần .1 và .2)."
)
NOTE_UC17 = (
    "(Quy trình thanh toán QR mô phỏng kế thừa và liên thông trực tiếp với "
    "sơ đồ tuần tự xét duyệt và thanh toán tại Hình 2.25)."
)
TARGET_106 = "106/106 ca kiểm thử phân quyền và định tuyến (route)"


THANKS_OLD = (
    "Trong suốt quá trình triển khai đồ án, từ giai đoạn khảo sát nghiệp vụ, "
    "phân tích thiết kế hệ thống cho đến cài đặt phần mềm và hoàn thiện báo cáo."
)
THANKS_NEW = (
    "Trong suốt quá trình đó, Thầy đã tận tình hướng dẫn nhóm từ giai đoạn "
    "khảo sát nghiệp vụ, phân tích thiết kế hệ thống cho đến cài đặt phần mềm "
    "và hoàn thiện báo cáo."
)


NEW_ABBREVS = {
    # Viet tat: dinh nghia (ban soat lai mot luot cho hop van phong bao cao).
    "ACID": "Atomicity, Consistency, Isolation, Durability - Tính nguyên tử, nhất quán, cô lập, bền vững của giao dịch cơ sở dữ liệu",
    "CLI": "Command Line Interface - Giao diện dòng lệnh",
    "CSRF": "Cross-Site Request Forgery - Giả mạo yêu cầu liên trang",
    "SMTP": "Simple Mail Transfer Protocol - Giao thức truyền thư đơn giản, dùng để gửi email",
    "VietQR": "Chuẩn mã QR thanh toán thống nhất tại Việt Nam (NAPAS)",
    "WAMP": "Windows, Apache, MySQL, PHP - Môi trường máy chủ web trên Windows gồm Apache, MySQL và PHP",
}


# Tham chieu ma (cu) -> anh xa theo chu de, lay tu phan tich truoc do:
#   AI -> 3.2.5, QR -> 3.2.4, gia han luu tru -> 3.2.6, gioi han -> 3.8.2
OLD_REFS = [
    "3.2.7", "3.2.8", "3.2.9", "3.2.10", "3.2.11", "3.2.12", "3.2.13", "3.2.14",
    "3.4.5", "3.5.2", "3.3.1", "3.3.2",
]




# ------------------------------------------------------------------ log


class Log:
    def __init__(self):
        self.counts = Counter()
        self.warnings = []


    def add(self, key, n=1):
        if n:
            self.counts[key] += n


    def warn(self, msg):
        self.warnings.append(msg)
        print(f"  [WARNING] {msg}")


    def report(self):
        print("\n===== TONG KET THAY DOI =====")
        for key in sorted(self.counts):
            print(f"  {key}: {self.counts[key]}")
        if self.warnings:
            print(f"\n===== CANH BAO CAN KIEM TRA THU CONG ({len(self.warnings)}) =====")
            for w in self.warnings:
                print(f"  - {w}")
        else:
            print("\nKhong co canh bao.")




LOG = Log()




# ------------------------------------------------------------------ thay the giu format run


def para_text(par: Paragraph) -> str:
    return "".join(r.text for r in par.runs)




def _run_bounds(par: Paragraph):
    """Tra ve list (run, start, end) theo chi so ky tu trong para_text."""
    bounds, pos = [], 0
    for r in par.runs:
        bounds.append((r, pos, pos + len(r.text)))
        pos += len(r.text)
    return bounds




def replace_span(par: Paragraph, start: int, end: int, new: str) -> None:
    """Thay doan [start:end) trong para_text bang new, giu format run.


    Run dau giu phan truoc start + text moi, cac run giua bi xoa,
    run cuoi giu phan sau end, cac run sau run cuoi giu nguyen.
    """
    if not par.runs:
        par.add_run(new)
        return
    bounds = _run_bounds(par)
    first_idx = next(
        (i for i, (_, s, e) in enumerate(bounds) if s <= start < e),
        len(bounds) - 1,
    )
    last_idx = next(
        (i for i, (_, s, e) in enumerate(bounds) if s < end <= e),
        len(bounds) - 1,
    ) if end > start else first_idx
    first, first_start = bounds[first_idx][0], bounds[first_idx][1]
    last, last_start = bounds[last_idx][0], bounds[last_idx][1]
    first_prefix = first.text[: start - first_start]
    last_suffix = last.text[end - last_start:] if end > start else last.text[start - last_start:]
    for i, (r, _, _) in enumerate(bounds):
        if i == first_idx == last_idx:
            r.text = first_prefix + new + last_suffix
        elif i == first_idx:
            r.text = first_prefix + new
        elif i == last_idx:
            r.text = last_suffix
        elif first_idx < i < last_idx:
            r.text = ""
        # Cac run truoc first_idx va sau last_idx: giu nguyen.




def replace_literal_in_para(par: Paragraph, old: str, new: str) -> int:
    """Thay literal old -> new trong 1 paragraph. Tra ve so luot thay."""
    if not old or old == new:
        return 0
    count, offset = 0, 0
    while True:
        text = para_text(par)
        idx = text.find(old, offset)
        if idx < 0:
            break
        replace_span(par, idx, idx + len(old), new)
        count += 1
        # Tien offset qua text moi: tranh lap vo han khi new chua old
        # (vi du: NVQL -> NVQLKTX).
        offset = idx + len(new)
    return count




def replace_regex_in_para(par: Paragraph, pattern, repl: str) -> int:
    count, offset = 0, 0
    while True:
        text = para_text(par)
        m = pattern.search(text, offset)
        if not m:
            break
        replace_span(par, m.start(), m.end(), repl)
        count += 1
        offset = m.start() + len(repl)
    return count




def set_cell_text(cell, text: str) -> None:
    par = cell.paragraphs[0]
    if par.runs:
        par.runs[0].text = text
        for r in par.runs[1:]:
            r.text = ""
    else:
        par.add_run(text)




def cell_text(cell) -> str:
    return "\n".join(para_text(p) for p in cell.paragraphs)




# ------------------------------------------------------------------ duyet tai lieu


def iter_table_paras(table: Table):
    for row in table.rows:
        for cell in row.cells:
            yield from cell.paragraphs
            for nested in cell.tables:
                yield from iter_table_paras(nested)




def iter_all_paragraphs(doc: Document):
    """Moi paragraph trong body + bang + header/footer (cho thay the toan cuc)."""
    yield from doc.paragraphs
    for table in doc.tables:
        yield from iter_table_paras(table)
    for section in doc.sections:
        for part in (section.header, section.footer):
            if part is None:
                continue
            yield from part.paragraphs
            for table in part.tables:
                yield from iter_table_paras(table)




def replace_global(doc: Document, old: str, new: str, key: str) -> int:
    total = 0
    for par in iter_all_paragraphs(doc):
        total += replace_literal_in_para(par, old, new)
    LOG.add(key, total)
    return total




def iter_body_blocks(doc: Document):
    """Cac block (paragraph/table) trong body theo dung thu tu, kem section.


    Tra ve list (kind, obj, numbered, uc) trong do numbered la so muc
    cua heading gan nhat (vi du '2.3.25'), uc la UC trong heading do
    (vi du 'UC18') hoac None.
    """
    blocks, numbered, uc = [], None, None
    for child in doc.element.body.iterchildren():
        if child.tag == qn("w:p"):
            par = Paragraph(child, doc)
            style = ""
            try:
                style = par.style.name or ""
            except Exception:
                pass
            if "Heading" in style:
                txt = para_text(par).strip()
                m = re.match(r"\s*(\d+(?:\.\d+)+)", txt)
                numbered = m.group(1) if m else None
                m2 = re.search(r"\b(UC\d+(?:\.\d+)?)\b", txt, re.IGNORECASE)
                uc = m2.group(1).upper() if m2 else None
            blocks.append(("p", par, numbered, uc))
        elif child.tag == qn("w:tbl"):
            blocks.append(("tbl", Table(child, doc), numbered, uc))
    return blocks




def replace_in_blocks(blocks, predicate, pairs, key_prefix: str) -> int:
    """Ap dung cac cap (old, new) cho block thoa predicate(numbered, uc)."""
    total = 0
    for kind, obj, numbered, uc in blocks:
        if not predicate(numbered, uc):
            continue
        paras = [obj] if kind == "p" else list(iter_table_paras(obj))
        for par in paras:
            for old, new in pairs:
                n = replace_literal_in_para(par, old, new)
                if n:
                    total += n
                    LOG.add(f"{key_prefix}:{old!r}->{new!r}", n)
    return total




def find_heading_para(blocks, numbered: str):
    for kind, obj, num, _ in blocks:
        if kind == "p" and num == numbered and para_text(obj).strip().startswith(numbered):
            return obj
    return None




def insert_para_after(anchor_par: Paragraph, doc: Document, text: str, italic=True):
    """Chen paragraph moi ngay sau anchor_par (dung cho ghi chu muc)."""
    p = doc.add_paragraph()
    run = p.add_run(text)
    run.italic = italic
    try:
        run.font.size = Pt(10)
    except Exception:
        pass
    anchor_par._p.addnext(p._p)
    return p




def find_tables_by_caption(doc: Document, blocks, caption_re: str):
    """Tim bang co caption 'Bang ...' ke ngay truoc hoac ngay sau bang.


    CHI xet doan van ban khong rong GAN NHAT truoc/sau bang, khong quet
    xa: quet xa se nhan nham bang ke tiep (vi du: ghi chu Bang 2.4 bi
    chen sau ca Bang A.2 va bang viet tat neu chung nam gan nhau).
    """
    found, seen = [], set()
    for i, (kind, obj, _, _) in enumerate(blocks):
        if kind != "tbl":
            continue
        caption = None
        # Doan khong rong gan nhat truoc bang.
        for j in range(i - 1, -1, -1):
            if blocks[j][0] == "p":
                txt = para_text(blocks[j][1]).strip()
                if txt:
                    if re.search(caption_re, txt):
                        caption = txt
                    break
            # Block bang o giua thi dung lai: caption khong the vuot qua bang khac.
            if blocks[j][0] == "tbl":
                break
        if caption is None:
            for j in range(i + 1, len(blocks)):
                if blocks[j][0] == "p":
                    txt = para_text(blocks[j][1]).strip()
                    if txt:
                        if re.search(caption_re, txt):
                            caption = txt
                        break
                if blocks[j][0] == "tbl":
                    break
        if caption is not None and id(obj) not in seen:
            seen.add(id(obj))
            found.append(obj)
    if not found:
        # Fallback: bang co noi dung chua dung ten bang (mot so file dat
        # caption trong chinh o dau bang).
        for table in doc.tables:
            txt = " ".join(cell_text(c) for row in table.rows for c in row.cells)
            if re.search(caption_re, txt):
                found.append(table)
    return found




def add_note_after_table(doc: Document, blocks, table: Table, note: str, key: str) -> int:
    """Them doan ghi chu ngay sau bang, neu chua co."""
    for i, (kind, obj, _, _) in enumerate(blocks):
        if kind == "tbl" and obj is table:
            # Doan ngay sau bang da co ghi chu thi bo qua (chay lai an toan).
            if i + 1 < len(blocks) and blocks[i + 1][0] == "p":
                nxt = para_text(blocks[i + 1][1])
                if note[:40] in nxt:
                    return 0
            p = doc.add_paragraph()
            run = p.add_run(note)
            run.italic = True
            try:
                run.font.size = Pt(10)
            except Exception:
                pass
            table._tbl.addnext(p._p)
            LOG.add(key, 1)
            return 1
    LOG.warn(f"Khong chen duoc ghi chu sau bang ({key}): khong tim thay bang trong body.")
    return 0




# ------------------------------------------------------------------ cac muc sua


def edit_01_php_count(doc: Document):
    print("1. So file PHP 125 -> 131/131")
    for old, new in [
        ("125/125 file PHP", "131/131 file PHP"),
        ("125/125 file", "131/131 file"),
        ("125 file PHP", "131/131 file PHP"),
        ("125/125", "131/131"),
    ]:
        n = replace_global(doc, old, new, "01_php")
        print(f"   {old!r} -> {new!r}: {n}")
    if LOG.counts["01_php"] == 0:
        LOG.warn("Muc 1: khong tim thay '125/125' hay '125 file PHP' nao.")




def edit_02_test_counts(doc: Document, blocks):
    print("2. So ca test: giu 235, ghi chu 273 o Bang 3.1 / A.2, ro 106/106 o 3.4.4")
    n235 = sum(para_text(p).count("235") for p in iter_all_paragraphs(doc))
    print(f"   Kiem chung (khong sua): '235' xuat hien {n235} lan.")
    LOG.add("02_235_giu_nguyen_kiem_chung", n235)


    for name, cre in [("Bang 3.1", r"Bảng\s*3\.1(?!\d)"), ("Bang A.2", r"Bảng\s*A\.2(?!\d)")]:
        tables = find_tables_by_caption(doc, blocks, cre)
        if not tables:
            LOG.warn(f"Muc 2: khong tim thay {name} de them ghi chu 273.")
            continue
        for t in tables:
            add_note_after_table(doc, blocks, t, NOTE_273, "02_note273")
        # Cap nhat lai blocks vi da chen doan moi (de cac buoc sau dung thu tu).
        blocks[:] = iter_body_blocks(doc)


    # 3.4.4: sua cac bien the ve dang chuan, neu chua co thi chen cau.
    pairs_106 = [
        ("106 ca kiểm thử phân quyền", TARGET_106),
        ("106/106 ca phân quyền", TARGET_106),
        ("106 ca phân quyền", TARGET_106),
    ]
    n = replace_in_blocks(blocks, lambda num, uc: num == "3.4.4", pairs_106, "02_106")
    if n == 0:
        n = sum(replace_literal_in_para(p, o, nw) for p in iter_all_paragraphs(doc) for o, nw in pairs_106)
        LOG.add("02_106_toan_cuc_fallback", n)
    already = any(TARGET_106 in para_text(p) for p in iter_all_paragraphs(doc))
    if not already:
        head = find_heading_para(blocks, "3.4.4")
        if head is not None:
            insert_para_after(head, doc, f"Trong đó, {TARGET_106} đạt yêu cầu.")
            LOG.add("02_106_chen_cau", 1)
            blocks[:] = iter_body_blocks(doc)
        else:
            LOG.warn("Muc 2: khong tim thay heading 3.4.4 va cum 106/106 de chen cau.")




def edit_03_ghost_refs(doc: Document, blocks):
    print("3. Tham chieu ma 3.2.7-3.2.14, 3.4.5, 3.5.2 (+ 3.3.1/3.3.2 o A.2)")
    # Khoang cu -> khoang hien tai 3.2.1-3.2.6 (khi khong co chu de cu the).
    for old, new in [
        ("3.2.7–3.2.14", "3.2.1–3.2.6"),
        ("3.2.7 – 3.2.14", "3.2.1 – 3.2.6"),
        ("3.2.7-3.2.14", "3.2.1-3.2.6"),
        ("3.2.7 đến 3.2.14", "3.2.1 đến 3.2.6"),
    ]:
        replace_global(doc, old, new, "03_khoang_cu")


    def mapped_ref(text: str):
        low = text.lower()
        if re.search(r"\bai\b", low):
            return "3.2.5"
        if re.search(r"\bqr\b", low):
            return "3.2.4"
        if "gia hạn" in low:
            return "3.2.6"
        if any(k in low for k in ("giới hạn", "hạn chế", "tải đồng thời", "thiết bị")):
            return "3.8.2"
        return None


    ambiguous = []
    for par in iter_all_paragraphs(doc):
        text = para_text(par)
        present = [r for r in OLD_REFS if r in text]
        if not present:
            continue
        new_ref = mapped_ref(text)
        if new_ref is None:
            continue
        if len(present) > 1:
            ambiguous.append(para_text(par)[:160])
        for r in present:
            # Chi thay khi r khong phai la phan cua so dai hon
            # (vi du: tranh thay '3.2.7' trong '3.2.71' -- khong co trong bao cao,
            #  nhung kiem tra bang regex van an toan hon).
            n = replace_regex_in_para(par, re.compile(re.escape(r) + r"(?!\d)"), new_ref)
            LOG.add("03_theo_chu_de", n)


    # Bang A.2: cot vi tri + UC18 + UC10.2 (chi trong bang nay, KHONG thay
    # toan cuc "Hinh 3.22" vi Hinh 3.22 o noi khac la anh mobile).
    tables_a2 = find_tables_by_caption(doc, blocks, r"Bảng\s*A\.2(?!\d)")
    if not tables_a2:
        LOG.warn("Muc 3: khong tim thay Bang A.2 de sua cot vi tri / UC18 / UC10.2.")
    for table in tables_a2:
        rows = list(table.rows)
        if not rows:
            continue
        header = [cell_text(c).lower() for c in rows[0].cells]
        loc_col = next((i for i, h in enumerate(header) if "vị trí" in h or "báo cáo" in h or "chương" in h or "mục" in h), None)
        ev_col = next((i for i, h in enumerate(header) if "minh chứng" in h or "bằng chứng" in h or "hình" in h), None)
        for row in rows[1:]:
            row_txt = " ".join(cell_text(c) for c in row.cells)
            is_uc18 = bool(re.search(r"\bUC18\b", row_txt))
            is_uc102 = bool(re.search(r"\bUC10\.2\b", row_txt))
            if loc_col is not None and loc_col < len(row.cells):
                cell = row.cells[loc_col]
                for par in cell.paragraphs:
                    new_ref = mapped_ref(row_txt)
                    for r in OLD_REFS:
                        if r in para_text(par):
                            replace_regex_in_para(par, re.compile(re.escape(r) + r"(?!\d)"), new_ref or "3.2.1–3.2.6")
                            LOG.add("03_A2_vi_tri", 1)
                    # Con sot tham chieu cu trong o vi tri thi dua ve khoang hien tai.
                    for par2 in cell.paragraphs:
                        for r in OLD_REFS:
                            if r in para_text(par2):
                                replace_regex_in_para(par2, re.compile(re.escape(r) + r"(?!\d)"), "3.2.1–3.2.6")
                                LOG.add("03_A2_vi_tri_khoang", 1)
            if is_uc18:
                if ev_col is not None and ev_col < len(row.cells):
                    for par in row.cells[ev_col].paragraphs:
                        LOG.add("03_A2_UC18_hinh", replace_literal_in_para(par, "Hình 3.22", "Hình 3.24"))
                        LOG.add("03_A2_UC18_hinh", replace_literal_in_para(par, "3.22", "3.24"))
                else:
                    for par in iter_table_paras_row(row):
                        LOG.add("03_A2_UC18_hinh", replace_literal_in_para(par, "Hình 3.22", "Hình 3.24"))
            if is_uc102:
                for par in iter_table_paras_row(row):
                    # "theo luy tien" truoc, de khong ra "theo chia deu theo...".
                    for old in ("theo lũy tiến", "theo luỹ tiến", "lũy tiến", "luỹ tiến"):
                        LOG.add("03_A2_UC102", replace_literal_in_para(par, old, "chia đều theo đơn giá quy định"))


    # Bao cao cac tham chieu cu con sot (de kiem tra thu cong, khong xoa mu).
    leftovers = []
    for par in iter_all_paragraphs(doc):
        text = para_text(par)
        for r in OLD_REFS:
            if re.search(re.escape(r) + r"(?!\d)", text):
                leftovers.append(f"{r} :: {text[:160]}")
                break
    if leftovers:
        LOG.warn(f"Muc 3: con {len(leftovers)} doan van tham chieu cu (can xem thu cong, co the la trich dan lich su hoac thieu chu de de anh xa):")
        for s in leftovers[:20]:
            print(f"    - {s}")
    if ambiguous:
        LOG.warn(f"Muc 3: {len(ambiguous)} doan co nhieu tham chieu cu + nhieu chu de (da anh xa theo tu khoa dau tien tim thay, nen soat lai):")
        for s in ambiguous[:10]:
            print(f"    - {s}")




def iter_table_paras_row(row):
    for cell in row.cells:
        yield from cell.paragraphs
        for nested in cell.tables:
            yield from iter_table_paras(nested)




def edit_04_log_table(doc: Document, blocks):
    print("4. UC18 (2.3.25): bang nhatkyhethong -> tep log file")
    pairs = [
        ("ghi nhật ký thực thi vào bảng nhatkyhethong",
         "ghi nhật ký thực thi vào tệp nhật ký hệ thống (log file)"),
        ("bảng nhatkyhethong", "tệp nhật ký hệ thống (log file)"),
    ]
    n = replace_in_blocks(blocks, lambda num, uc: num == "2.3.25" or uc == "UC18", pairs, "04_uc18")
    if n == 0:
        # Fallback: cum cau dich xac chi xuat hien o UC18, thay toan cuc
        # van an toan neu heading khong nhan dang duoc (khac style).
        n = sum(replace_literal_in_para(p, o, nw) for p in iter_all_paragraphs(doc) for o, nw in pairs)
        LOG.add("04_fallback_toan_cuc", n)
        if n:
            print("   (fallback toan cuc vi khong dinh vi duoc section 2.3.25/UC18)")
    if n == 0:
        LOG.warn("Muc 4: khong tim thay 'bang nhatkyhethong' trong UC18 (co the da sua roi).")




def edit_05_bed_roommate(doc: Document, blocks):
    print("5. UC11 / UC18: bo giuong, ban cung phong")
    uc11_pairs = [
        (", giường, danh sách bạn cùng phòng", ""),
        ("giường, danh sách bạn cùng phòng, ", ""),
        ("giường, danh sách bạn cùng phòng", ""),
        ("giường và danh sách bạn cùng phòng", ""),
        ("danh sách bạn cùng phòng", ""),
    ]
    uc18_pairs = [
        ("kế thừa thông tin phòng và giường", ""),
        ("kế thừa thông tin phòng, giường", ""),
        ("thông tin phòng và giường", "thông tin phòng"),
    ]
    n11 = replace_in_blocks(blocks, lambda num, uc: uc == "UC11", uc11_pairs, "05_uc11")
    n18 = replace_in_blocks(blocks, lambda num, uc: uc == "UC18", uc18_pairs, "05_uc18")
    # Bang 2.4: dong UC11 / UC18 (bang tom tat, khong nam trong section UC).
    tables_24 = find_tables_by_caption(doc, blocks, r"Bảng\s*2\.4(?!\d)")
    if not tables_24:
        LOG.warn("Muc 5: khong tim thay Bang 2.4 de sua dong UC11/UC18 (chi sua trong section).")
    for table in tables_24:
        for row in table.rows:
            txt = " ".join(cell_text(c) for c in row.cells)
            pairs = uc11_pairs if re.search(r"\bUC11\b", txt) else (uc18_pairs if re.search(r"\bUC18\b", txt) else None)
            if pairs:
                for par in iter_table_paras_row(row):
                    for old, new in pairs:
                        LOG.add("05_bang24", replace_literal_in_para(par, old, new))
    if n11 + LOG.counts["05_bang24"] == 0:
        # Fallback toan cuc cho cum dich xac (chi co o UC11).
        n = sum(replace_literal_in_para(p, o, nw) for p in iter_all_paragraphs(doc) for o, nw in uc11_pairs)
        LOG.add("05_uc11_fallback", n)
    # Don dep khoang trang doi sau khi xoa cum (chi o cac doan vua sua:
    # o day lam sach toan cuc "  " -> " " la an toan vi khoang trang doi
    # trong bao cao chi la loi danh may).
    replace_global(doc, "  ", " ", "05_don_khoang_trang")




def edit_06_unify_uc18(doc: Document, blocks):
    print("6. Thong nhat UC18: 'Con han', no phi -> khoa tai khoan")
    # Chu y: theo phan tich truoc do, gia tri sai nam o muc 2.4.8
    # (doi chieu UC18), con dac ta UC18 (2.3.25) da dung. Vi vay sua ca
    # hai noi; noi nao da dung thi so luot = 0, khong anh huong gi.
    pairs = [
        ("giữ nguyên trạng thái hoặc chuyển vào danh sách cảnh báo", "khóa tài khoản"),
        ("giữ nguyên trạng thái hoặc cảnh báo", "khóa tài khoản"),
        ("giữ nguyên trạng thái, cảnh báo", "khóa tài khoản"),
        ("Đang hiệu lực", "Còn hạn"),
        ("đang hiệu lực", "còn hạn"),
    ]
    n = replace_in_blocks(
        blocks, lambda num, uc: num == "2.4.8" or num == "2.3.25" or uc == "UC18", pairs, "06"
    )
    if n == 0:
        LOG.warn("Muc 6: khong tim thay 'Dang hieu luc' / 'giu nguyen trang thai...' o 2.4.8 hay UC18 (co the da thong nhat roi).")




def edit_07_note_table_24(doc: Document, blocks):
    print("7. Ghi chu tai Bang 2.4: 18 UC loi = 22 dac ta chi tiet")
    tables = find_tables_by_caption(doc, blocks, r"Bảng\s*2\.4(?!\d)")
    if not tables:
        LOG.warn("Muc 7: khong tim thay Bang 2.4.")
        return
    for t in tables:
        add_note_after_table(doc, blocks, t, NOTE_UC_COUNT, "07_note24")
    blocks[:] = iter_body_blocks(doc)




def edit_08_note_uc17(doc: Document, blocks):
    print("8. Ghi chu tai 2.3.24 (UC17): lien thong Hinh 2.25")
    if any("sơ đồ tuần tự xét duyệt và thanh toán tại Hình 2.25" in para_text(p)
           for p in iter_all_paragraphs(doc)):
        print("   Ghi chu da co, khong chen them.")
        return
    head = find_heading_para(blocks, "2.3.24")
    if head is None:
        # Fallback: heading chua 'UC17' (neu danh so khac).
        for kind, obj, _, uc in blocks:
            if kind == "p" and uc == "UC17":
                head = obj
                break
    if head is not None:
        insert_para_after(head, doc, NOTE_UC17, italic=True)
        LOG.add("08_note_uc17", 1)
        blocks[:] = iter_body_blocks(doc)
    else:
        LOG.warn("Muc 8: khong tim thay heading 2.3.24 / UC17 de chen ghi chu.")




def edit_09_thanks(doc: Document, blocks):
    print("9. Loi cam on: sua cau cut doan 2")
    n = replace_global(doc, THANKS_OLD, THANKS_NEW, "09_thanks_cau_chuan")
    if n:
        print(f"   Da thay cau cut goc: {n} lan.")
        return
    # Fallback: tim doan 2 trong section Loi cam on, thay tu "Trong suot
    # qua trinh" den het cau (dau '.' dau tien) bang cau moi.
    in_thanks, paras = False, []
    for kind, obj, _, _ in blocks:
        if kind != "p":
            continue
        txt = para_text(obj)
        try:
            is_head = "Heading" in (obj.style.name or "")
        except Exception:
            is_head = False
        if "Lời cảm ơn" in txt and (is_head or txt.strip() == "Lời cảm ơn"):
            in_thanks = True
            continue
        if is_head and in_thanks:
            break
        if in_thanks and txt.strip():
            paras.append(obj)
    if len(paras) >= 2:
        target = paras[1]
        txt = para_text(target)
        start = txt.find("Trong suốt quá trình")
        if start >= 0:
            end = txt.find(".", start)
            end = len(txt) if end < 0 else end + 1
            replace_span(target, start, end, THANKS_NEW)
            LOG.add("09_thanks_fallback_doan2", 1)
            print(f"   (fallback) Doan 2 goc: {txt[:200]}")
            return
    LOG.warn("Muc 9: khong xac dinh duoc cau cut o Loi cam on; can sua thu cong theo cau moi trong script (THANKS_NEW).")




def edit_10_draft_sentence(doc: Document):
    print("10. Muc 3.2: xoa cum nhap 'anh can them sau nay'")
    for old in [
        ", và các ảnh cần thêm vào báo cáo sau này",
        " và các ảnh cần thêm vào báo cáo sau này",
        "và các ảnh cần thêm vào báo cáo sau này",
    ]:
        n = replace_global(doc, old, "", "10_draft")
        if n:
            print(f"   {old!r}: {n}")
    replace_global(doc, "  ", " ", "10_don_khoang_trang")




def edit_11_abbrevs(doc: Document):
    print("11. Bang tu viet tat: them CSRF, ACID, SMTP, CLI, WAMP, VietQR; thong nhat NVQLKTX")
    abbrev_table = None
    for table in doc.tables:
        if not table.rows:
            continue
        header = " ".join(cell_text(c).lower() for c in table.rows[0].cells)
        if "viết tắt" in header or "từ viết tắt" in header or ("ký hiệu" in header and "nghĩa" in header):
            abbrev_table = table
            break
    if abbrev_table is None:
        LOG.warn("Muc 11: khong tim thay bang tu viet tat (header chua 'viet tat').")
    else:
        existing = {}
        for row in abbrev_table.rows[1:]:
            if row.cells:
                existing[cell_text(row.cells[0]).strip().upper()] = row
        for abbr in sorted(NEW_ABBREVS):
            if abbr.upper() in existing:
                print(f"   {abbr}: da co, khong them.")
                continue
            row = abbrev_table.add_row()
            if len(row.cells) >= 2:
                set_cell_text(row.cells[0], abbr)
                set_cell_text(row.cells[1], NEW_ABBREVS[abbr])
                LOG.add("11_them_viet_tat", 1)
            else:
                LOG.warn(f"Muc 11: bang viet tat chi co {len(row.cells)} cot, khong them duoc {abbr}.")
        # Doi NVQL -> NVQLKTX trong bang (neu co).
        for row in abbrev_table.rows[1:]:
            if row.cells and cell_text(row.cells[0]).strip() == "NVQL":
                set_cell_text(row.cells[0], "NVQLKTX")
                LOG.add("11_nvql_bang", 1)
        if SORT_ABBREV_TABLE:
            sort_table_by_first_col(abbrev_table)
            LOG.add("11_sap_xep_bang", 1)
    # Thong nhat trong toan van: "NVQL KTX" / "NVQL-KTX" / NVQL dung mot minh.
    replace_global(doc, "NVQL KTX", "NVQLKTX", "11_nvql_toan_van")
    replace_global(doc, "NVQL-KTX", "NVQLKTX", "11_nvql_toan_van")
    pat = re.compile(r"\bNVQL\b(?!KTX)")
    total = 0
    for par in iter_all_paragraphs(doc):
        total += replace_regex_in_para(par, pat, "NVQLKTX")
    LOG.add("11_nvql_toan_van", total)




def sort_table_by_first_col(table: Table):
    tbl = table._tbl
    trs = tbl.findall(qn("w:tr"))
    if len(trs) <= 2:
        return
    header, data = trs[0], trs[1:]


    def first_cell_text(tr):
        tc = tr.find(qn("w:tc"))
        if tc is None:
            return ""
        return "".join(t.text or "" for t in tc.iter(qn("w:t"))).strip().lower()


    for tr in data:
        tbl.remove(tr)
    for tr in sorted(data, key=first_cell_text):
        tbl.append(tr)




def edit_12_terms(doc: Document):
    print("12. Thuat ngu: escape, disable button, MVC")
    for old, new in [
        ("được mã hóa tránh lỗi hiển thị", "được escape (thoát ký tự HTML) tránh lỗi hiển thị"),
        ("mô hình ba lớp", "mô hình MVC"),
    ]:
        n = replace_global(doc, old, new, "12_thuat_ngu")
        print(f"   {old!r}: {n}")
        if n == 0:
            LOG.warn(f"Muc 12: khong tim thay {old!r} (co the da sua hoac khac dau cach).")
    # debouncing / debounce la 2 bien the cua cung 1 cho: chi canh bao
    # khi ca hai deu khong tim thay, tranh canh bao thua khi 1 bien the da sua.
    n_deb = 0
    for old in [
        "vô hiệu hóa nút thanh toán (debouncing)",
        "vô hiệu hóa nút thanh toán (debounce)",
    ]:
        n = replace_global(doc, old, "vô hiệu hóa nút thanh toán (disable button)", "12_thuat_ngu")
        print(f"   {old!r}: {n}")
        n_deb += n
    if n_deb == 0:
        LOG.warn("Muc 12: khong tim thay 'vo hieu hoa nut thanh toan (debouncing/debounce)' (co the da sua hoac khac dau cach).")




# ------------------------------------------------------------------ muc 13: updateFields


def patch_update_fields(path: str) -> bool:
    """Chen <w:updateFields w:val='true'/> vao word/settings.xml bang sua chuoi.


    Ly do dung sua chuoi thay vi XML parser: parser viet lai file co the
    doi prefix namespace (mc, w14, ...) va lam hong settings.xml.
    """
    with zipfile.ZipFile(path, "r") as zin:
        names = zin.namelist()
        if "word/settings.xml" not in names:
            print("  [WARNING] Muc 13: khong co word/settings.xml trong file, bo qua.")
            return False
        data = {name: zin.read(name) for name in names}
    settings = data["word/settings.xml"].decode("utf-8")
    if "<w:updateFields" in settings:
        new_settings = re.sub(r"<w:updateFields[^>]*/>", '<w:updateFields w:val="true"/>', settings, count=1)
        if new_settings == settings:
            # Dang <w:updateFields ...></w:updateFields>: chi sua the mo,
            # giu nguyen the dong de khong tao XML thua the dong.
            new_settings = re.sub(
                r"<w:updateFields[^>]*>",
                '<w:updateFields w:val="true">',
                settings, count=1,
            )
        changed = new_settings != settings
    else:
        if "</w:settings>" in settings:
            new_settings = settings.replace("</w:settings>", '<w:updateFields w:val="true"/></w:settings>', 1)
            changed = True
        else:
            print("  [WARNING] Muc 13: khong tim thay the dong </w:settings>.")
            return False
    if not changed:
        print("  Muc 13: updateFields da la true, khong can sua.")
        return True
    data["word/settings.xml"] = new_settings.encode("utf-8")
    fd, tmp_path = tempfile.mkstemp(suffix=".docx", dir=os.path.dirname(os.path.abspath(path)))
    try:
        with os.fdopen(fd, "wb") as fout, zipfile.ZipFile(fout, "w", zipfile.ZIP_DEFLATED) as zout:
            for name in names:
                zout.writestr(name, data[name])
        shutil.move(tmp_path, path)
    except Exception:
        try:
            os.unlink(tmp_path)
        except Exception:
            pass
        raise
    # Kiem chung lai.
    with zipfile.ZipFile(path, "r") as z:
        ok = 'w:updateFields w:val="true"' in z.read("word/settings.xml").decode("utf-8")
    print(f"  Muc 13: updateFields = true (kiem chung: {ok}).")
    return ok




def count_media(path: str) -> int:
    with zipfile.ZipFile(path, "r") as z:
        return sum(1 for n in z.namelist() if n.startswith("word/media/"))




# ------------------------------------------------------------------ main


def main(argv=None) -> int:
    ap = argparse.ArgumentParser(description="Sua bao cao Kanto KTX (khong ghi de file goc).")
    ap.add_argument("--src", default=SRC_DEFAULT)
    ap.add_argument("--dst", default=DST_DEFAULT)
    ap.add_argument("--strict", action="store_true",
                    help="Thoat ma 1 neu co canh bao (de chay trong CI / kiem tra).")
    ap.add_argument("--no-sort-abbrev", action="store_true",
                    help="Khong sap xep lai bang tu viet tat.")
    args = ap.parse_args(argv)


    global SORT_ABBREV_TABLE
    if args.no_sort_abbrev:
        SORT_ABBREV_TABLE = False


    if not os.path.isfile(args.src):
        print(f"LOI: khong tim thay file goc:\n  {args.src}", file=sys.stderr)
        print("Kiem tra lai duong dan F:\\Kanto-KTX\\... hoac chay voi --src <duong-dan-that>.", file=sys.stderr)
        return 2


    media_before = count_media(args.src)
    print(f"File goc: {args.src}")
    print(f"So media (anh) trong file goc: {media_before}")
    print(f"File dich: {args.dst}")


    doc = Document(args.src)
    blocks = iter_body_blocks(doc)


    edit_01_php_count(doc)
    edit_02_test_counts(doc, blocks)
    blocks = iter_body_blocks(doc)
    edit_03_ghost_refs(doc, blocks)
    blocks = iter_body_blocks(doc)
    edit_04_log_table(doc, blocks)
    edit_05_bed_roommate(doc, blocks)
    edit_06_unify_uc18(doc, blocks)
    blocks = iter_body_blocks(doc)
    edit_07_note_table_24(doc, blocks)
    blocks = iter_body_blocks(doc)
    edit_08_note_uc17(doc, blocks)
    edit_09_thanks(doc, blocks)
    edit_10_draft_sentence(doc)
    edit_11_abbrevs(doc)
    edit_12_terms(doc)


    os.makedirs(os.path.dirname(os.path.abspath(args.dst)) or ".", exist_ok=True)
    doc.save(args.dst)
    print(f"\nDa luu file moi: {args.dst}")


    print("13. Bat co updateFields trong settings.xml")
    ok13 = patch_update_fields(args.dst)
    LOG.add("13_updateFields", 1 if ok13 else 0)
    if not ok13:
        LOG.warn("Muc 13: chua bat duoc updateFields, can mo file bang Word > Update Table of Contents thu cong.")


    # Kiem chung sau khi luu.
    media_after = count_media(args.dst)
    print(f"So media (anh) trong file moi: {media_after} (goc: {media_before})")
    if media_after != media_before:
        LOG.warn(f"So anh thay doi: goc {media_before}, moi {media_after} (can kiem tra).")
    check = Document(args.dst)
    full = "\n".join(para_text(p) for p in iter_all_paragraphs(check))
    print("\nKiem chung nhanh tren file moi:")
    for label, needle in [
        ("'125/125' con sot", "125/125"),
        ("'125 file PHP' con sot", "125 file PHP"),
        ("'nhatkyhethong' con sot", "nhatkyhethong"),
        ("'lũy tiến' con sot", "lũy tiến"),
        ("'131/131' da co", "131/131"),
        ("'273 lượt' da co", "273 lượt"),
        ("'106/106' da co", "106/106"),
    ]:
        print(f"  {label}: {full.count(needle)} lan")


    LOG.report()
    if args.strict and LOG.warnings:
        return 1
    return 0




if __name__ == "__main__":
    raise SystemExit(main())

