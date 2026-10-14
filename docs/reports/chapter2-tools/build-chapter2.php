<?php
// CLI-only, reproducible diagrams and DOCX. No application or database mutation.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$out = dirname(__DIR__) . '/chapter2';
if (!is_dir($out . '/assets')) { mkdir($out . '/assets', 0777, true); }
$font = 'C:/Windows/Fonts/arial.ttf';
$bold = 'C:/Windows/Fonts/arialbd.ttf';

class Figure {
    private GdImage $im;
    private array $colors = [];
    public function __construct(public int $width, public int $height) {
        $this->im = imagecreatetruecolor($width, $height);
        imagefill($this->im, 0, 0, $this->color('#ffffff'));
        imageantialias($this->im, true);
    }
    private function color(string $hex): int {
        return $this->colors[$hex] ??= imagecolorallocate($this->im, hexdec(substr($hex,1,2)), hexdec(substr($hex,3,2)), hexdec(substr($hex,5,2)));
    }
    public function text(int $x, int $y, string $text, int $size=30, bool $isBold=false, string $color='#14243d', bool $center=false): void {
        global $font, $bold;
        $f = $isBold ? $bold : $font;
        $box = imagettfbbox($size, 0, $f, $text);
        if ($center) { $x -= (int)(($box[2]-$box[0])/2); }
        imagettftext($this->im, $size, 0, $x, $y, $this->color($color), $f, $text);
    }
    public function line(int $x1,int $y1,int $x2,int $y2,string $color='#62748e',bool $arrow=false,bool $dash=false): void {
        imagesetthickness($this->im, 3);
        if ($dash) {
            $len=max(1,hypot($x2-$x1,$y2-$y1));
            for($d=0;$d<$len;$d+=20) {
                $e=min($d+10,$len);
                imageline($this->im,(int)($x1+($x2-$x1)*$d/$len),(int)($y1+($y2-$y1)*$d/$len),(int)($x1+($x2-$x1)*$e/$len),(int)($y1+($y2-$y1)*$e/$len),$this->color($color));
            }
        } else { imageline($this->im,$x1,$y1,$x2,$y2,$this->color($color)); }
        if ($arrow) {
            $a=atan2($y2-$y1,$x2-$x1);
            foreach([-0.45,0.45] as $offset) { imageline($this->im,$x2,$y2,(int)($x2-20*cos($a+$offset)),(int)($y2-20*sin($a+$offset)),$this->color($color)); }
        }
    }
    public function box(int $x,int $y,int $w,int $h,array $lines,string $fill='#eef4ff',int $size=30): void {
        imagefilledrectangle($this->im,$x,$y,$x+$w,$y+$h,$this->color($fill));
        imagesetthickness($this->im,2);
        imagerectangle($this->im,$x,$y,$x+$w,$y+$h,$this->color('#788da9'));
        $lineHeight=$size+13;
        $start=$y+(int)(($h-count($lines)*$lineHeight)/2)+$size;
        global $font, $bold;
        foreach($lines as $i=>$s) {
            $fit=$size;
            do { $bounds=imagettfbbox($fit,0,$i===0?$bold:$font,$s); if($bounds[2]-$bounds[0]<=$w-24) { break; } $fit--; } while($fit>16);
            $this->text($x+(int)($w/2),$start+$i*$lineHeight,$s,$fit,$i===0,'#14243d',true);
        }
    }
    public function ellipse(int $x,int $y,int $w,int $h,string $text,int $size=28): void {
        imagefilledellipse($this->im,$x,$y,$w,$h,$this->color('#eef4ff'));
        imageellipse($this->im,$x,$y,$w,$h,$this->color('#788da9'));
        $this->text($x,$y+10,$text,$size,false,'#14243d',true);
    }
    public function actor(int $x,int $y,string $label): void {
        imageellipse($this->im,$x,$y,44,44,$this->color('#14243d'));
        $this->line($x,$y+22,$x,$y+86);
        $this->line($x-38,$y+45,$x+38,$y+45);
        $this->line($x,$y+86,$x-34,$y+132);
        $this->line($x,$y+86,$x+34,$y+132);
        $this->text($x,$y+174,$label,28,true,'#14243d',true);
    }
    public function save(string $name): void {
        global $out;
        imagepng($this->im,$out.'/assets/'.$name.'.png');
        imagedestroy($this->im);
    }
}

function usecase(string $name, array $ucs, array $actors): void {
    $height=count($ucs)*115+160;
    $f=new Figure(1400,$height);
    $f->box(440,20,920,$height-50,[], '#ffffff');
    $f->text(900,65,'HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ',30,true,'#14243d',true);
    foreach($actors as $label=>$a) {
        [$y,$indices]=$a;
        foreach($indices as $i) { $f->line(270,$y+60,555,130+$i*115); }
        $f->actor(230,$y,$label);
    }
    foreach($ucs as $i=>$label) { $f->ellipse(930,130+$i*115,750,80,$label,28); }
    $f->save($name);
}
usecase('usecase-public',[
    'UC02 - Đăng ký lưu trú','UC15 - Xem thông tin phòng','UC16 - Xem hướng dẫn',
    'UC13 - Trợ lý thông tin AI','UC01 - Đăng nhập','UC08 - Gửi phản ánh sự cố',
    'UC11 - Xem thông tin lưu trú','UC12 - Xem thông báo'
],['Khách'=>[180,[0,1,2,3]],'Sinh viên'=>[680,[3,4,5,6,7]]]);
usecase('usecase-staff',[
    'UC01 - Đăng nhập','UC03 - Xét duyệt đăng ký','UC04 - Quản lý sinh viên',
    'UC05 - Quản lý hợp đồng','UC09 - Xử lý phản ánh','UC06 - Ghi chỉ số điện nước',
    'UC07 - Quản lý hóa đơn','UC14 - Xem thống kê','UC10.1 - Lập hóa đơn tiền phòng',
    'UC10.2 - Lập hóa đơn điện nước'
],['NV quản lý'=>[140,[0,1,2,3,4,5]],'NV kế toán'=>[520,[0,6]],'Quản lý'=>[810,[0,7]],'Timer'=>[1060,[8,9]]]);

$f=new Figure(1400,1450);
$f->text(700,60,'VÒNG ĐỜI ĐĂNG KÝ VÀ LƯU TRÚ',36,true,'#14243d',true);
$f->box(360,110,680,110,['Gửi hồ sơ và chọn phòng']);
$f->line(700,220,700,280,arrow:true);
$f->box(360,280,680,130,['Đơn: Chờ duyệt','Giữ một chỗ; chưa có tài khoản']);
$f->line(700,410,700,485,arrow:true);
$f->box(460,485,480,110,['Quyết định xét duyệt'], '#fff5d9');
$f->line(460,540,195,540,arrow:true);
$f->text(260,520,'Từ chối',26);
$f->box(30,615,320,200,['Ghi lý do','Đơn: Từ chối','Bỏ chỗ đang giữ'], '#fff0ef',26);
$f->line(195,540,195,615,arrow:true);
$f->line(700,595,700,675,arrow:true);
$f->text(730,645,'Duyệt',28);
$f->box(420,675,900,210,['Tạo tài khoản và sinh viên: Chờ thanh toán','Hóa đơn ban đầu: Chưa thu','Đơn: Đã duyệt; tiếp tục giữ chỗ'],size:28);
$f->line(870,885,870,980,arrow:true,dash:true);
$f->box(420,980,900,130,['Xác nhận thanh toán hóa đơn đầu tiên','Bước tiếp nối cần hoàn thiện đặc tả'], '#fff5d9',28);
$f->line(870,1110,870,1190,arrow:true,dash:true);
$f->box(420,1190,900,150,['Lập và gắn hợp đồng; ghi nhận lưu trú','Không đếm đơn giữ chỗ lần hai'], '#eaf8f0',28);
$f->text(700,1410,'Nét đứt: thiết kế tiếp nối; không khẳng định đã triển khai đầy đủ.',24,false,'#52617a',true);
$f->save('workflow-registration');

$f=new Figure(1400,1290);
$f->text(700,50,'TRÌNH TỰ DUYỆT HỒ SƠ - UC03',34,true,'#14243d',true);
$xs=[140,480,830,1220];
foreach(['NV quản lý','ApprovalController','Approval','MySQL'] as $i=>$label) {
    $f->box($xs[$i]-130,90,260,90,[$label],size:23);
    $f->line($xs[$i],180,$xs[$i],1210,dash:true);
}
$messages=[
    [0,1,'Gửi quyết định duyệt',250],
    [1,2,'approve(mã đơn)',350],
    [2,3,'Khóa; bắt đầu transaction',450],
    [2,3,'Đọc đơn, phòng, kỳ và đơn giá',550],
    [3,2,'Dữ liệu để kiểm tra lại',650],
    [2,3,'Tạo tài khoản, sinh viên',760],
    [2,3,'Tạo hóa đơn; gắn đơn Đã duyệt',860],
    [2,3,'COMMIT; giải phóng khóa',960],
    [2,1,'Kết quả duyệt',1060],
    [1,0,'Hiển thị thành công',1160]
];
foreach($messages as [$a,$b,$label,$y]) {
    $f->text((int)(($xs[$a]+$xs[$b])/2),$y-15,$label,22,false,'#14243d',true);
    $f->line($xs[$a],$y,$xs[$b],$y,arrow:true,dash:$a>$b);
}
$f->text(700,1260,'Nếu kiểm tra hoặc ghi lỗi: dừng thao tác; ROLLBACK; trả thông báo.',25,false,'#52617a',true);
$f->save('sequence-approval');

$f=new Figure(1400,770);
$f->text(700,60,'KIẾN TRÚC CLIENT - SERVER / MVC',36,true,'#14243d',true);
$f->box(50,155,360,180,['Trình duyệt','HTML / CSS','JavaScript'],size:30);
$f->box(525,120,350,120,['index.php','Định tuyến'],size:28);
$f->box(525,310,350,110,['Controller'],size:30);
$f->box(985,310,350,110,['Model / PDO'],size:30);
$f->box(525,530,350,110,['View'],size:30);
$f->box(985,530,350,110,['MySQL'],size:30);
$f->line(410,210,525,185,arrow:true);
$f->text(415,155,'HTTP',24);
$f->line(700,240,700,310,arrow:true);
$f->line(875,365,985,365,arrow:true);
$f->line(1160,420,1160,530,arrow:true);
$f->line(700,420,700,530,arrow:true);
$f->line(525,585,230,585,arrow:true);
$f->line(230,585,230,335,arrow:true);
$f->text(335,555,'HTML / phản hồi',24,false,'#14243d',true);
$f->text(700,735,'Cấu hình, bố cục và tài nguyên giao diện được dùng chung giữa các module.',26,false,'#52617a',true);
$f->save('architecture');

$f=new Figure(1400,1020);
$f->text(700,60,'LỚP XỬ LÝ PHÒNG - ĐĂNG KÝ - XÉT DUYỆT',32,true,'#14243d',true);
$f->box(30,140,410,220,['RoomController','+ show(): void','- db: PDO'],size:28);
$f->box(490,140,410,220,['RegistrationController','+ show(): void','- db: PDO'],size:26);
$f->box(950,140,410,220,['ApprovalController','+ handle(action): void','- db: PDO'],size:26);
$f->box(30,525,410,310,['Room','+ buildings(gender)','+ floors(gender, building)','+ rooms(..., availableOnly)','- db: PDO'],size:26);
$f->box(490,525,410,310,['Registration','+ rooms(gender)','+ submit(data, roomId)','+ studentConflict(...)','- db: PDO'],size:26);
$f->box(950,525,410,310,['Approval','+ pending() / find(id)','+ approve(id)','+ reject(id, reason)','- db: PDO'],size:26);
foreach([235,695,1155] as $x) { $f->line($x,360,$x,525,arrow:true); }
$f->line(490,700,440,700,arrow:true);
$f->text(700,920,'Controller gọi Model; Registration sử dụng Room cho dữ liệu phòng.',27,false,'#52617a',true);
$f->text(700,975,'Quan hệ phụ thuộc xử lý; không phải quan hệ kế thừa.',27,false,'#52617a',true);
$f->save('classes');

$schema=json_decode(file_get_contents(__DIR__.'/schema-reference.json'),true,512,JSON_THROW_ON_ERROR);
function erd(string $name,string $title,array $positions): void {
    global $schema;
    $f=new Figure(1400,1290);
    $f->text(700,55,$title,32,true,'#14243d',true);
    $boxes=[];
    foreach($positions as $table=>[$col,$row,$fields]) {
        $boxes[$table]=[30+$col*465,115+$row*345,410,260];
    }
    // Draw actual FK edges only; PK/unique implies max 1, otherwise many.
    foreach($positions as $table=>$_) {
        preg_match_all('/FOREIGN KEY \(`([^`]+)`\) REFERENCES `([^`]+)` \(`([^`]+)`\)/',$schema[$table]['create'],$matches,PREG_SET_ORDER);
        $seen=[];
        foreach($matches as $m) {
            $target=$m[2];
            if(!isset($boxes[$target])||isset($seen[$target])) { continue; }
            $seen[$target]=true;
            [$x,$y,$w,$h]=$boxes[$table]; [$tx,$ty,$tw,$th]=$boxes[$target];
            $sx=$x+$w/2; $sy=$y+$h/2; $ex=$tx+$tw/2; $ey=$ty+$th/2;
            $dx=$ex-$sx; $dy=$ey-$sy;
            $factor=min($dx==0?INF:($w/2)/abs($dx),$dy==0?INF:($h/2)/abs($dy));
            $tf=min($dx==0?INF:($tw/2)/abs($dx),$dy==0?INF:($th/2)/abs($dy));
            $f->line((int)($sx+$dx*$factor),(int)($sy+$dy*$factor),(int)($ex-$dx*$tf),(int)($ey-$dy*$tf),arrow:true);
        }
    }
    foreach($positions as $table=>[$c,$r,$fields]) {
        [$x,$y,$w,$h]=$boxes[$table];
        $lines=array_merge([$table],$fields);
        $f->box($x,$y,$w,$h,$lines,size:25);
    }
    $f->text(700,1225,'PK: khóa chính; FK: khóa ngoại; ?: có thể NULL.',27,false,'#52617a',true);
    $f->text(700,1270,'Mũi tên: bảng chứa FK → bảng được tham chiếu; chỉ hiện liên kết trong nhóm.',25,false,'#52617a',true);
    $f->save($name);
}
erd('erd-residence','ERD - NHÓM HỒ SƠ VÀ LƯU TRÚ',[
    'taikhoan'=>[0,0,['PK MaTK','TenDangNhap (unique)','MatKhau, VaiTro']],
    'dienuutien'=>[1,0,['PK MaUuTien','TenDienUuTien','MucUuTien']],
    'toanha'=>[2,0,['PK MaToa','TenToa']],
    'sinhvien'=>[0,1,['PK MSSV','FK MaTK, MaUuTien','TrangThaiLuuTru']],
    'dondangky'=>[1,1,['PK MaDonDangKy','FK MaPhong, MaUuTien','FK? MaHoaDon, MaHopDong','MSSV, TrangThai']],
    'phong'=>[2,1,['PK MaPhong','FK MaToa','Tang, GioiTinhPhong','SucChua']],
    'hopdong'=>[1,2,['PK MaHopDong','FK MSSV, MaPhong','NgayBatDau, NgayKetThuc','TrangThai']]
]);
erd('erd-billing','ERD - NHÓM HÓA ĐƠN VÀ ĐƠN GIÁ',[
    'hocky'=>[0,0,['PK MaHocKy','NgayBatDau','NgayKetThuc']],
    'hoadon'=>[1,0,['PK MaHoaDon','LoaiHoaDon, TongTien','TrangThai']],
    'dongia'=>[2,0,['PK MaDonGia','LoaiDonGia, GiaTri','DonViTinh, NgayApDung']],
    'hoadontienphong'=>[0,1,['PK/FK MaHoaDon','FK MSSV, MaPhong','FK MaHocKy, MaDonGia']],
    'hoadondiennuoc'=>[1,1,['PK/FK MaHoaDon','FK MaPhong','FK MaDonGiaDien/Nuoc','Thang, Nam, tiêu thụ']],
    'chisodiennuoc'=>[2,1,['PK MaChiSo','FK MaPhong, MaNV','Thang, Nam','ChiSoDien, ChiSoNuoc']],
    'sinhvien'=>[0,2,['PK MSSV','Bảng tham chiếu']],
    'hoadondiennuocsinhvien'=>[1,2,['PK/FK MaHoaDon','PK/FK MSSV','Bảng liên kết']],
    'phong'=>[2,2,['PK MaPhong','Bảng tham chiếu']]
]);
erd('erd-support','ERD - NHÓM PHẢN ÁNH VÀ THÔNG BÁO',[
    'taikhoan'=>[0,0,['PK MaTK','VaiTro, TrangThai']],
    'sinhvien'=>[1,0,['PK MSSV','FK MaTK']],
    'hoadon'=>[2,0,['PK MaHoaDon','TrangThai']],
    'nhanvien'=>[0,1,['PK MaNV','FK MaTK','TrangThaiLamViec']],
    'phananhsuco'=>[1,1,['PK MaPhanAnh','FK MSSV, MaPhong','FK? MaNhanVienXuLy','TrangThai, ChiPhiSuaChua']],
    'thongbao'=>[2,1,['PK MaThongBao','FK MSSV','FK? MaHoaDon','TieuDe, ThoiGianTao']],
    'phong'=>[1,2,['PK MaPhong','Bảng tham chiếu']]
]);

$f=new Figure(1400,1020);
$f->box(30,30,1340,90,['KANTO     Trang chủ | Đăng ký | Thông tin phòng | Hướng dẫn | Đăng nhập'],size:26);
$f->text(75,195,'Thông tin phòng',40,true);
$f->text(75,250,'Xem tình trạng phòng theo khu, tòa và tầng.',28);
foreach([[310,['Khu','Nam','Nữ']],[410,['Tòa','Tòa A','Tòa B']],[510,['Tầng','1','2','3','4']]] as [$y,$labels]) {
    $f->text(75,$y+38,$labels[0],30,true);
    foreach(array_slice($labels,1) as $i=>$label) { $f->box(300+$i*230,$y,190,70,[$label],$i===0?'#e1ecff':'#ffffff',28); }
}
foreach([['A101','Đang ở: 5 | Đang giữ: 2','Khả dụng: 1 / 8'],['A102','Đang ở: 6 | Đang giữ: 2','Đầy: 0 / 8'],['A103','Đang ở: 4 | Đang giữ: 0','Khả dụng: 4 / 8']] as $i=>$lines) {
    $f->box(75+$i*425,640,390,200,$lines,$i===1?'#eff1f5':'#eaf8f0',25);
}
$f->text(75,925,'Chỗ khả dụng đã trừ số đang ở và đang giữ. Đăng ký để chọn phòng.',26);
$f->text(75,980,'PHÁC THẢO - Số liệu minh họa, không phải dữ liệu sinh viên thực tế.',25,false,'#52617a');
$f->save('ui-rooms');

$f=new Figure(1400,1000);
$f->box(30,30,1340,90,['KANTO  |  Nhân viên quản lý  |  Xét duyệt đăng ký'],size:30);
$f->text(75,195,'Hồ sơ chờ duyệt',40,true);
$f->text(75,255,'Thứ tự: ưu tiên giảm dần → thời gian gửi → mã đơn.',28);
$f->box(75,310,1250,90,['Mã đơn    |    Mã sinh viên    |    Ưu tiên    |    Phòng    |    Chi tiết'],size:27);
$f->box(75,400,1250,100,['Hồ sơ minh họa    |    Thông tin được xem theo quyền của nhân viên'], '#ffffff',27);
$f->box(75,550,1250,200,['Chi tiết hồ sơ và phòng đã chọn','Lý do từ chối (bắt buộc nếu từ chối):','.........................................................................'], '#ffffff',28);
$f->box(670,795,260,80,['Từ chối'], '#fff0ef',30);
$f->box(1000,795,325,80,['Duyệt hồ sơ'], '#e1ecff',30);
$f->text(75,935,'Duyệt → tạo tài khoản, sinh viên chờ thanh toán và hóa đơn; chưa tạo hợp đồng.',25);
$f->text(75,980,'PHÁC THẢO - Không sử dụng bản ghi cá nhân.',25,false,'#52617a');
$f->save('ui-approval');

// Minimal, standards-based OOXML with editable text, tables, headings and images.
function x(string $s): string { return htmlspecialchars($s,ENT_XML1|ENT_QUOTES,'UTF-8'); }
function runs(string $s,bool $bold=false): string {
    return '<w:r>'.($bold?'<w:rPr><w:b/></w:rPr>':'').'<w:t xml:space="preserve">'.x($s).'</w:t></w:r>';
}
function p(string $text,string $style='Normal',bool $keep=false): string {
    return '<w:p><w:pPr><w:pStyle w:val="'.$style.'"/>'.($keep?'<w:keepNext/>':'').'</w:pPr>'.runs($text).'</w:p>';
}
function table(array $rows): string {
    $count=count($rows[0]);
    $widths=$count===4?[1800,1300,3100,2870]:($count===3?[2000,3500,3570]:[2600,6470]);
    $s='<w:tbl><w:tblPr><w:tblW w:w="9070" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblBorders>';
    foreach(['top','left','bottom','right','insideH','insideV'] as $b) { $s.='<w:'.$b.' w:val="single" w:sz="4" w:color="B0B8C4"/>'; }
    $s.='</w:tblBorders><w:tblCellMar><w:top w:w="85" w:type="dxa"/><w:left w:w="95" w:type="dxa"/><w:bottom w:w="85" w:type="dxa"/><w:right w:w="95" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid>';
    foreach($widths as $w) { $s.='<w:gridCol w:w="'.$w.'"/>'; }
    $s.='</w:tblGrid>';
    foreach($rows as $i=>$row) {
        $s.='<w:tr><w:trPr><w:cantSplit/>'.($i===0?'<w:tblHeader/>':'').'</w:trPr>';
        foreach($row as $j=>$cell) {
            $s.='<w:tc><w:tcPr><w:tcW w:w="'.$widths[$j].'" w:type="dxa"/>'.($i===0?'<w:shd w:fill="EAF0F8"/>':'').'<w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:pStyle w:val="TableText"/></w:pPr>'.runs($cell,$i===0).'</w:p></w:tc>';
        }
        $s.='</w:tr>';
    }
    return $s.'</w:tbl>'.p('','Spacer');
}
$lines=preg_split('/\R/u',file_get_contents($out.'/BAN_THAO_CHUONG_2_KANTO_KTX.md'));
$body=''; $media=[]; $rels=[]; $imageId=0;
for($i=0;$i<count($lines);$i++) {
    $line=trim($lines[$i]);
    if($line==='') { continue; }
    if(preg_match('/^(#{1,3}) (.+)$/u',$line,$m)) {
        $style=['1'=>'Title','2'=>'Heading1','3'=>'Heading2'][(string)strlen($m[1])];
        $body.=p($m[2],$style,true); continue;
    }
    if(preg_match('/^!\[(.+)\]\((.+)\)$/u',$line,$m)) {
        $path=$out.'/'.$m[2];
        if(!is_file($path)) { throw new RuntimeException('Missing image '.$path); }
        [$w,$h]=getimagesize($path);
        $cx=5750000; $cy=(int)($cx*$h/$w);
        if($cy>6150000) { $cx=(int)($cx*6150000/$cy); $cy=6150000; }
        $id=++$imageId; $rid='rIdImage'.$id; $name='figure'.$id.'.png';
        $rels[]='<Relationship Id="'.$rid.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/'.$name.'"/>';
        $media[$name]=$path;
        $body.='<w:p><w:pPr><w:jc w:val="center"/><w:keepNext/><w:spacing w:before="160" w:after="100"/></w:pPr><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="'.$cx.'" cy="'.$cy.'"/><wp:docPr id="'.$id.'" name="'.$name.'" descr="'.x($m[1]).'"/><wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="'.$id.'" name="'.$name.'"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="'.$rid.'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>'.p($m[1],'Caption');
        continue;
    }
    if(str_starts_with($line,'|')) {
        $rows=[];
        while($i<count($lines) && str_starts_with(trim($lines[$i]),'|')) {
            $cells=array_map('trim',explode('|',trim(trim($lines[$i]),'|')));
            if(!preg_match('/^:?-+:?$/',str_replace(' ','',$cells[0]))) { $rows[]=$cells; }
            $i++;
        }
        $i--; $body.=table($rows); continue;
    }
    $body.=p($line);
}
$styles='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="Times New Roman"/><w:sz w:val="26"/><w:lang w:val="vi-VN"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="360" w:lineRule="auto"/><w:jc w:val="both"/><w:widowControl/></w:pPr></w:pPrDefault></w:docDefaults>';
$defs=[
    ['Normal','Normal',26,false,'both',null,120,0],
    ['Title','Chapter title',30,true,'center',null,300,0],
    ['Heading1','Heading 1',28,true,'left',0,180,240],
    ['Heading2','Heading 2',26,true,'left',1,120,180],
    ['Caption','Caption',24,false,'center',null,180,0],
    ['TableText','Table text',22,false,'left',null,0,0],
    ['Spacer','Spacer',10,false,'left',null,0,0]
];
foreach($defs as [$id,$name,$size,$b,$align,$outline,$after,$before]) {
    $styles.='<w:style w:type="paragraph" w:styleId="'.$id.'"'.($id==='Normal'?' w:default="1"':'').'><w:name w:val="'.$name.'"/><w:pPr><w:jc w:val="'.$align.'"/><w:spacing w:before="'.$before.'" w:after="'.$after.'" w:line="'.($id==='TableText'?'260':'360').'" w:lineRule="auto"/>'.($outline!==null?'<w:keepNext/><w:keepLines/><w:outlineLvl w:val="'.$outline.'"/>':'').($id==='Normal'?'<w:ind w:firstLine="425"/>':'<w:ind w:firstLine="0"/>').'</w:pPr><w:rPr><w:sz w:val="'.$size.'"/>'.($b?'<w:b/>':'').($id==='Caption'?'<w:i/>':'').'</w:rPr></w:style>';
}
$styles.='</w:styles>';
$document='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><w:body>'.$body.'<w:sectPr><w:footerReference w:type="default" r:id="rIdFooter"/><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1417" w:right="1134" w:bottom="1417" w:left="1701" w:header="650" w:footer="650" w:gutter="0"/><w:pgNumType w:start="1"/></w:sectPr></w:body></w:document>';
$outputName=$argv[1] ?? 'BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx';
if(!preg_match('/^[A-Z0-9_]+\.docx$/D',$outputName)) { throw new RuntimeException('Invalid output filename'); }
$zip=new ZipArchive(); $docx=$out.'/'.$outputName;
if($zip->open($docx,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) { throw new RuntimeException('Cannot create DOCX'); }
$zip->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/><Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/><Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/></Types>');
$zip->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/></Relationships>');
$zip->addFromString('word/document.xml',$document);
$zip->addFromString('word/styles.xml',$styles);
$zip->addFromString('word/settings.xml','<?xml version="1.0" encoding="UTF-8"?><w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:defaultTabStop w:val="708"/><w:compat><w:compatSetting w:name="compatibilityMode" w:uri="http://schemas.microsoft.com/office/word" w:val="15"/></w:compat><w:autoHyphenation w:val="0"/></w:settings>');
$zip->addFromString('word/footer1.xml','<?xml version="1.0" encoding="UTF-8"?><w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:fldSimple w:instr="PAGE"><w:r><w:rPr><w:sz w:val="22"/></w:rPr><w:t>1</w:t></w:r></w:fldSimple></w:p></w:ftr>');
$zip->addFromString('word/_rels/document.xml.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rIdSettings" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/><Relationship Id="rIdFooter" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>'.implode('',$rels).'</Relationships>');
$zip->addFromString('docProps/core.xml','<?xml version="1.0" encoding="UTF-8"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>Chương 2 - Hệ thống quản lý ký túc xá trường Đại học Kanto</dc:title><dc:subject>Bản thảo phân tích và thiết kế</dc:subject><dc:language>vi-VN</dc:language><dcterms:created xsi:type="dcterms:W3CDTF">2026-10-11T09:30:00Z</dcterms:created></cp:coreProperties>');
foreach($media as $name=>$path) { $zip->addFile($path,'word/media/'.$name); }
if(!$zip->close()) { throw new RuntimeException('Cannot save DOCX; file may be open in Word. Choose another output filename.'); }
echo 'Built DOCX with '.$imageId.' figures: '.$docx.PHP_EOL;
