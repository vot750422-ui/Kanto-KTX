"""Chuyen cac INSERT INTO hopdong VALUES (...) sang dang co danh sach cot.

Ly do: migration UC18 them cot hopdong.MaHocKy, nen INSERT khong liet ke cot
bi sai so luong gia tri. Script nay them danh sach cot cu cho dung.
"""
import re
import sys
from pathlib import Path

COLS = "(MaHopDong,MSSV,MaPhong,NgayBatDau,NgayKetThuc,TrangThai)"

FILES = [
    "tests/accommodation.php",
    "tests/chatbot-rooms.php",
    "tests/contracts.php",
    "tests/incidents.php",
    "tests/payment.php",
    "tests/room-invoice-generation.php",
    "tests/statistics.php",
    "tests/student-management.php",
    "tests/utility-invoice-generation.php",
    "tests/utility-meters.php",
]


def patch(text: str) -> tuple[str, int]:
    count = 0
    out = []
    for line in text.splitlines(keepends=True):
        if "INSERT INTO hopdong VALUES" in line:
            line = line.replace("INSERT INTO hopdong VALUES", f"INSERT INTO hopdong {COLS} VALUES")
            count += 1
        out.append(line)
    return "".join(out), count


total = 0
for name in FILES:
    path = Path(name)
    if not path.exists():
        print(f"Bo qua (khong ton tai): {name}")
        continue
    original = path.read_text(encoding="utf-8")
    patched, n = patch(original)
    if n:
        path.write_text(patched, encoding="utf-8", newline="")
        print(f"{name}: sua {n} dong")
        total += n
    else:
        print(f"{name}: khong co dong nao can sua")

print(f"Tong cong: {total} dong da sua")
