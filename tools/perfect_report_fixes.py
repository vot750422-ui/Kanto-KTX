#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import docx
import zipfile
import shutil
import tempfile
import os

target_path = r"F:\Kanto-KTX\docs\reports\complete\QUYEN_BAO_CAO_DO_AN_1_KANTO_KTX_HOAN_THIEN.docx"

doc = docx.Document(target_path)

def replace_in_para(p, old_text, new_text):
    if old_text in p.text:
        # replace in runs or if broken across runs
        full_text = p.text
        if old_text in full_text:
            new_full = full_text.replace(old_text, new_text)
            p.text = new_full

# 1. Paragraph 135
p135 = doc.paragraphs[135]
if "3.2.11" in p135.text:
    p135.text = p135.text.replace("mục 3.2.11", "mục 3.2.5")
    print("Fixed P135")

# 2. Paragraph 154
p154 = doc.paragraphs[154]
if "3.2.7" in p154.text:
    p154.text = p154.text.replace("mục 3.2.5, 3.2.7 và 3.2.14", "mục 3.2.3 và 3.2.4")
    print("Fixed P154")

# 3. Paragraph 512 (E1 nhatkyhethong)
p512 = doc.paragraphs[512]
if "nhatkyhethong" in p512.text:
    p512.text = p512.text.replace("nhatkyhethong", "tệp nhật ký hệ thống (log file)")
    print("Fixed P512")

# 4. Table 56 Row 7 Col 2 (nhatkyhethong)
t56 = doc.tables[56]
for r in t56.rows:
    for c in r.cells:
        if "nhatkyhethong" in c.text:
            c.text = c.text.replace("nhatkyhethong", "tệp nhật ký hệ thống (log file)")
            print("Fixed Table 56 nhatkyhethong")

# 5. Table 70 (Bảng A.1)
t70 = doc.tables[70]
for r in t70.rows:
    for c in r.cells:
        if "3.2.11, 3.2.14" in c.text:
            c.text = c.text.replace("3.2.11, 3.2.14", "3.2.5 (AI), 3.2.4 (QR)")
            print("Fixed T70 Row 4")
        if "3.2.1, 3.2.11" in c.text:
            c.text = c.text.replace("3.2.1, 3.2.11", "3.2.1, 3.2.5")
            print("Fixed T70 Row 7")
        if "3.8.2 và 3.8.2" in c.text:
            c.text = c.text.replace("3.8.2 và 3.8.2", "3.2.5 và 3.8.2")
            print("Fixed T70 Row 9")

# 6. Table 71 (Bảng A.2)
t71 = doc.tables[71]
# Row mapping for UC -> Section
uc_mapping = {
    "UC04.1": ("3.2.3", None),
    "UC04.2": ("3.2.3", None),
    "UC05.1": ("3.2.3", None),
    "UC05.2": ("3.2.3", None),
    "UC06": ("3.2.4", None),
    "UC07.1": ("3.2.4", None),
    "UC07.2": ("3.2.4", None),
    "UC08": ("3.2.5", None),
    "UC09": ("3.2.5", None),
    "UC10.1": ("3.2.4", None),
    "UC10.2": ("3.2.4", "chia đều theo đơn giá quy định"),
    "UC11": ("3.2.3", None),
    "UC12": ("3.2.5", None),
    "UC13": ("3.2.5", None),
    "UC14": ("3.2.5", None),
    "UC15": ("3.2.5", None),
    "UC16": ("3.2.5", None),
    "UC17": ("3.2.4", None),
    "UC18": ("3.2.6", "Hình 3.24"),
}

for i in range(1, len(t71.rows)):
    r = t71.rows[i]
    uc_col = r.cells[0].text.strip()
    sec_cell = r.cells[1]
    desc_cell = r.cells[2]
    
    for uc_prefix, (correct_sec, extra_fix) in uc_mapping.items():
        if uc_col.startswith(uc_prefix):
            sec_cell.text = correct_sec
            if extra_fix:
                if uc_prefix == "UC10.2" and "lũy tiến" in desc_cell.text:
                    desc_cell.text = desc_cell.text.replace("theo lũy tiến", extra_fix)
                elif uc_prefix == "UC18" and "Hình 3.22" in desc_cell.text:
                    desc_cell.text = desc_cell.text.replace("Hình 3.22", extra_fix)
            print(f"Fixed Table 71 row for {uc_prefix} -> {correct_sec}")
            break

doc.save(target_path)
print("Saved updated docx successfully!")

# Ensure updateFields in settings.xml
with zipfile.ZipFile(target_path, "r") as z:
    settings_data = z.read("word/settings.xml").decode("utf-8")
    names = z.namelist()
    data = {n: z.read(n) for n in names}

if 'w:updateFields' not in settings_data:
    settings_data = settings_data.replace('<w:settings ', '<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" ')
    idx = settings_data.find('</w:settings>')
    if idx != -1:
        settings_data = settings_data[:idx] + '<w:updateFields w:val="true"/>' + settings_data[idx:]
        data["word/settings.xml"] = settings_data.encode("utf-8")
        fd, tmp = tempfile.mkstemp(suffix=".docx", dir=os.path.dirname(os.path.abspath(target_path)))
        with os.fdopen(fd, "wb") as fout, zipfile.ZipFile(fout, "w", zipfile.ZIP_DEFLATED) as zout:
            for n in names:
                zout.writestr(n, data[n])
        shutil.move(tmp, target_path)
        print("Re-verified updateFields = True in settings.xml")
