<?php
// Insert original wireframes into the user's approved DOCX, preserving its other content.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 3);
$directory = dirname(__DIR__) . '/chapter2';
$source = $directory . '/Chuong_2_Kanto_KTX_da_sua.docx';
$target = $directory . '/Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx';
$wireframes = $root . '/docs/05-ui/wireframes';
$groups = [
    ['a) Giao diện công khai và đăng ký lưu trú', 'Nhóm giao diện công khai giúp người dùng tiếp cận thông tin ký túc xá, đăng nhập và thực hiện đăng ký lưu trú theo từng bước.', [
        ['index.png', 'Trang chủ của hệ thống', 'Trang chủ bố trí thanh điều hướng, vùng giới thiệu, nút đăng ký lưu trú và điểm truy cập trợ lý AI.'],
        ['UC01-login.png', 'Giao diện đăng nhập - UC01', 'Biểu mẫu đăng nhập tập trung vào tên đăng nhập, mật khẩu và nút xác nhận để người dùng truy cập hệ thống.'],
        ['UC02-registration-step1-personal-info.png', 'Đăng ký lưu trú: nhập thông tin - UC02', 'Bước đầu thu thập thông tin cá nhân, học tập, diện ưu tiên và tệp minh chứng; các trường bắt buộc được đánh dấu trên biểu mẫu.'],
        ['UC02-registration-step2-room-selection.png', 'Đăng ký lưu trú: chọn phòng - UC02', 'Bước chọn phòng trình bày bộ lọc, thẻ phòng và thông tin phòng đã chọn để người đăng ký kiểm tra trước khi tiếp tục.'],
        ['UC02-registration-step3-review-confirmation.png', 'Đăng ký lưu trú: xem lại và xác nhận - UC02', 'Bước cuối tổng hợp hồ sơ và phòng đã chọn, cho phép quay lại chỉnh sửa hoặc gửi đăng ký.'],
        ['UC15-room-information.png', 'Giao diện xem thông tin phòng - UC15', 'Màn hình thông tin phòng tổ chức bộ lọc khu, tòa và tầng, kết hợp các thẻ phòng để trình bày tình trạng sử dụng.'],
        ['UC16-guide.png', 'Giao diện hướng dẫn đăng ký - UC16', 'Màn hình hướng dẫn trình bày các bước nhập thông tin, chọn phòng, xác nhận và chờ xét duyệt theo thứ tự thực hiện.'],
    ]],
    ['b) Trang tổng quan theo vai trò', 'Các trang tổng quan cung cấp điểm truy cập nhanh tới chức năng của từng vai trò và trình bày các chỉ số cần theo dõi.', [
        ['dashboard-student.png', 'Trang tổng quan sinh viên', 'Trang sinh viên bố trí thông tin lưu trú tóm tắt và các lối tắt tới hồ sơ, thông báo, phản ánh và trợ lý AI.'],
        ['Dashboard - Nhân viên quản lý KTX.png', 'Trang tổng quan nhân viên quản lý KTX', 'Trang nhân viên quản lý kết hợp thanh điều hướng nghiệp vụ với các thẻ thống kê đơn chờ duyệt, sinh viên, phòng và phản ánh.'],
        ['Dashboard-Nhân viên kế toán.png', 'Trang tổng quan nhân viên kế toán', 'Trang kế toán trình bày tổng số hóa đơn và các nhóm hóa đơn để hỗ trợ theo dõi công việc thu phí.'],
    ]],
    ['c) Xét duyệt, quản lý sinh viên và hợp đồng', 'Nhóm giao diện nội bộ sử dụng bộ lọc và bảng kết quả cho thao tác tra cứu; màn hình chi tiết tập trung vào dữ liệu của đối tượng đang được xử lý.', [
        ['UC03-approval-list.png', 'Danh sách hồ sơ chờ duyệt - UC03', 'Danh sách thể hiện các hồ sơ, diện ưu tiên, phòng đăng ký và thao tác mở chi tiết để nhân viên xem xét.'],
        ['UC03-approval-detail.png', 'Chi tiết hồ sơ đăng ký - UC03', 'Màn hình chi tiết chia hồ sơ thành thông tin cá nhân, ưu tiên, phòng, đơn và minh chứng, đồng thời bố trí các nút duyệt hoặc từ chối.'],
        ['UC04.1-student-search.png', 'Tra cứu sinh viên - UC04.1', 'Màn hình tra cứu kết hợp các tiêu chí tìm kiếm, trạng thái lưu trú, bảng kết quả và thao tác xem hồ sơ.'],
        ['UC04.2-student-update.png', 'Cập nhật thông tin sinh viên - UC04.2', 'Biểu mẫu cập nhật phân biệt các trường được nhập với trường định danh chỉ đọc và cung cấp thao tác lưu thay đổi.'],
        ['UC05.1-contract-search.png', 'Tra cứu hợp đồng lưu trú - UC05.1', 'Màn hình quản lý hợp đồng trình bày tiêu chí tìm kiếm, trạng thái, thời hạn và liên kết xem chi tiết.'],
        ['UC05.2-contract-termination.png', 'Chi tiết và thanh lý hợp đồng - UC05.2', 'Màn hình chi tiết tập hợp thông tin hợp đồng, sinh viên, phòng và thời hạn; thao tác thanh lý được đặt tại khu vực hành động.'],
    ]],
    ['d) Chỉ số điện nước và quản lý hóa đơn', 'Các màn hình này hỗ trợ ghi nhận dữ liệu điện nước và theo dõi chứng từ thu phí, với bảng danh sách và biểu mẫu chi tiết phù hợp từng công việc.', [
        ['UC06-utility-meter-room-list.png.png', 'Danh sách phòng ghi chỉ số điện nước - UC06', 'Danh sách thể hiện phòng, kỳ ghi, trạng thái ghi chỉ số và thao tác mở biểu mẫu tương ứng.'],
        ['UC06-utility-meter-entry.png.png', 'Biểu mẫu ghi chỉ số điện nước - UC06', 'Biểu mẫu trình bày chỉ số trước và ô nhập chỉ số mới của điện, nước, kèm thông tin phòng và kỳ ghi.'],
        ['UC07.1-invoice-search and UC07.2-payment-confirmation.png', 'Tra cứu hóa đơn và xác nhận thanh toán - UC07.1, UC07.2', 'Giao diện quản lý hóa đơn bố trí bộ lọc, danh sách, ô chọn hóa đơn và nút xác nhận thanh toán cho nhân viên kế toán.'],
    ]],
    ['e) Gửi và xử lý phản ánh sự cố', 'Nhóm giao diện phản ánh phân biệt thao tác gửi thông tin của sinh viên với thao tác xem xét, ghi chi phí và xác nhận xử lý của nhân viên.', [
        ['UC08-incident-report.png', 'Gửi phản ánh sự cố - UC08', 'Màn hình sinh viên bố trí thông tin phòng, loại sự cố, nội dung mô tả và nút gửi phản ánh.'],
        ['UC09-incident-list and UC09-incident-detail.png', 'Danh sách và chi tiết xử lý phản ánh - UC09', 'Màn hình nhân viên kết hợp bảng phản ánh và vùng chi tiết, giúp xem nội dung, nhập chi phí và xác nhận kết quả xử lý.'],
    ]],
    ['f) Thông tin lưu trú, thông báo và trợ lý AI', 'Các màn hình hỗ trợ sinh viên được tổ chức để thông tin cá nhân, lưu trú và thông báo dễ tiếp cận; trợ lý AI được trình bày dưới dạng cửa sổ hội thoại.', [
        ['UC11-accommodation-info.png', 'Thông tin lưu trú của sinh viên - UC11', 'Màn hình thông tin lưu trú chia các vùng hồ sơ sinh viên, phòng ở và hợp đồng để người dùng theo dõi thông tin của bản thân.'],
        ['UC12-notifications.png', 'Giao diện danh sách thông báo - UC12', 'Màn hình thông báo trình bày tiêu đề, phần nội dung tóm tắt và thời điểm, giúp sinh viên theo dõi các thông tin được gửi tới mình.'],
        ['UC13-ai-assistant.png.png', 'Giao diện trợ lý thông tin AI - UC13', 'Cửa sổ hội thoại bố trí lịch sử trao đổi, ô nhập câu hỏi, nút gửi và thao tác đóng cửa sổ để hỗ trợ tương tác hỏi đáp.'],
    ]],
    ['g) Thống kê dành cho quản lý', 'Giao diện quản lý trình bày các chỉ số tổng hợp và biểu đồ để hỗ trợ theo dõi hoạt động ký túc xá.', [
        ['UC14-statistics-report.png', 'Giao diện thống kê và báo cáo - UC14', 'Wireframe bố trí các thẻ chỉ số, biểu đồ tình trạng phòng và doanh thu, giúp hình dung cách tổ chức thông tin tổng hợp.'],
    ]],
];
const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
const R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
function escapeXml(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8'); }
function xpathFor(DOMDocument $dom): DOMXPath {
    $xp = new DOMXPath($dom);
    foreach (['w'=>W,'r'=>R,'a'=>'http://schemas.openxmlformats.org/drawingml/2006/main','wp'=>'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing'] as $prefix=>$ns) { $xp->registerNamespace($prefix, $ns); }
    return $xp;
}
function paragraphText(DOMNode $node, DOMXPath $xp): string {
    $text=''; foreach($xp->query('.//w:t', $node) as $t) { $text.=$t->textContent; } return trim($text);
}
$originalHash = hash_file('sha256', $source);
echo 'Reading approved DOCX...'.PHP_EOL;
$zip=new ZipArchive();
if($zip->open($source, ZipArchive::RDONLY)!==true) { throw new RuntimeException('Cannot read approved draft'); }
$parts=[];
for($i=0;$i<$zip->numFiles;$i++) { $parts[$zip->getNameIndex($i)]=$zip->getFromIndex($i); }
$zip->close();
echo 'Loaded '.count($parts).' ZIP parts; inspecting approved content...'.PHP_EOL;
$dom=new DOMDocument(); $dom->loadXML($parts['word/document.xml'], LIBXML_NONET); $xp=xpathFor($dom);
$body=$xp->query('/w:document/w:body')->item(0);
$anchor=null; $headingTemplate=null; $bodyTemplate=null; $captionTemplate=null;
$baseline=[];
foreach($body->childNodes as $node) {
    if(!$node instanceof DOMElement) { continue; }
    $baseline[]=hash('sha256', $dom->saveXML($node));
    $text=paragraphText($node,$xp);
    if(str_starts_with($text,'2.7.3.')) { throw new RuntimeException('Section 2.7.3 already exists; inspect before rerunning'); }
    if(str_starts_with($text,'2.7.1.')) { $headingTemplate=$node; }
    if(str_starts_with($text,'Giao diện được tổ chức theo vai trò.')) { $bodyTemplate=$node; }
    if(str_starts_with($text,'Hình 2.7.')) { $captionTemplate=$node; }
    if(preg_match('/^2\.8\.\s/u',$text)) { $anchor=$node; }
}
if(!$anchor || !$headingTemplate || !$bodyTemplate || !$captionTemplate) { throw new RuntimeException('Required section/templates not found'); }
$originalFigures=$xp->query('//w:drawing')->length;
echo 'Original body inspected; inserting wireframes...'.PHP_EOL;
$highestCaption=0; $highestDrawingId=0;
foreach($xp->query('//w:p') as $p) { if(preg_match('/^Hình 2\.(\d+)\./u',paragraphText($p,$xp),$m)) { $highestCaption=max($highestCaption,(int)$m[1]); } }
foreach($xp->query('//wp:docPr/@id') as $id) { $highestDrawingId=max($highestDrawingId,(int)$id->value); }
$rels=new DOMDocument(); $rels->loadXML($parts['word/_rels/document.xml.rels'], LIBXML_NONET);
$relNs=$rels->documentElement->namespaceURI;
$newNodes=[]; $manifest=[];
function addParagraph(string $text, DOMElement $template, bool $keep=false, bool $bold=false): DOMElement {
    global $dom,$xp,$body,$anchor,$newNodes;
    $p=$dom->createElementNS(W,'w:p');
    $pr=$xp->query('./w:pPr',$template)->item(0);
    $ppr=$pr ? $pr->cloneNode(true) : $dom->createElementNS(W,'w:pPr');
    foreach(iterator_to_array($xp->query('./w:keepNext | ./w:pageBreakBefore',$ppr)) as $old) { $ppr->removeChild($old); }
    if($keep) { $ppr->appendChild($dom->createElementNS(W,'w:keepNext')); }
    $p->appendChild($ppr);
    $run=$dom->createElementNS(W,'w:r');
    $rpr=$xp->query('./w:r/w:rPr',$template)->item(0);
    $runPr=$rpr ? $rpr->cloneNode(true) : $dom->createElementNS(W,'w:rPr');
    if($bold) { $runPr->appendChild($dom->createElementNS(W,'w:b')); }
    $run->appendChild($runPr);
    $t=$dom->createElementNS(W,'w:t'); $t->appendChild($dom->createTextNode($text)); $run->appendChild($t); $p->appendChild($run);
    $body->insertBefore($p,$anchor); $newNodes[]=$p;
    return $p;
}
addParagraph('2.7.3. Phác thảo giao diện',$headingTemplate,true);
addParagraph('Các wireframe dưới đây do nhóm xây dựng để mô tả bố cục, thành phần và tương tác của các màn hình. Hình được tổ chức theo nhóm chức năng nhằm thể hiện sự liên kết giữa giao diện công khai, khu vực sinh viên và khu vực nghiệp vụ nội bộ. Các giá trị và hồ sơ trên hình là dữ liệu minh họa cho thiết kế giao diện.',$bodyTemplate);
$sect=$xp->query('/w:document/w:body/w:sectPr')->item(0);
$pageWidth=(int)$xp->evaluate('string(w:pgSz/@w:w)',$sect);
$left=(int)$xp->evaluate('string(w:pgMar/@w:left)',$sect);
$right=(int)$xp->evaluate('string(w:pgMar/@w:right)',$sect);
$maxWidth=(int)(($pageWidth-$left-$right)*635)-50000;
$figureNumber=$highestCaption;
foreach($groups as [$groupTitle,$intro,$figures]) {
    echo 'Group: '.$groupTitle.PHP_EOL;
    addParagraph($groupTitle,$bodyTemplate,true,true);
    addParagraph($intro,$bodyTemplate,true);
    foreach($figures as [$filename,$title,$description]) {
        $path=$wireframes.'/'.$filename;
        if(!is_file($path)) { throw new RuntimeException('Missing supplied wireframe: '.$filename); }
        [$width,$height]=getimagesize($path);
        $cx=$maxWidth; $cy=(int)($cx*$height/$width);
        if($cy>6500000) { $cx=(int)($cx*6500000/$cy); $cy=6500000; }
        $number=++$figureNumber; $drawingId=++$highestDrawingId;
        $relationshipId='rIdKantoWireframe'.$number;
        $mediaName='kanto-wireframe-'.$number.'.png';
        if(str_contains($parts['word/_rels/document.xml.rels'],'Id="'.$relationshipId.'"') || isset($parts['word/media/'.$mediaName])) { throw new RuntimeException('Image identifier collision'); }
        $rel=$rels->createElementNS($relNs,'Relationship');
        $rel->setAttribute('Id',$relationshipId); $rel->setAttribute('Type',R.'/image'); $rel->setAttribute('Target','media/'.$mediaName);
        $rels->documentElement->appendChild($rel);
        $parts['word/media/'.$mediaName]=file_get_contents($path);
        $caption='Hình 2.'.$number.'. '.$title;
        $fragment=new DOMDocument();
        $fragment->loadXML('<w:p xmlns:w="'.W.'" xmlns:r="'.R.'" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><w:pPr><w:keepNext/><w:keepLines/><w:spacing w:before="120" w:after="80" w:line="240" w:lineRule="auto"/><w:ind w:left="0" w:right="0" w:firstLine="0"/><w:jc w:val="center"/></w:pPr><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="'.$cx.'" cy="'.$cy.'"/><wp:docPr id="'.$drawingId.'" name="'.$mediaName.'" descr="'.escapeXml($caption).'"/><wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="'.$drawingId.'" name="'.escapeXml($filename).'"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="'.$relationshipId.'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>',LIBXML_NONET);
        $imageNode=$dom->importNode($fragment->documentElement,true); $body->insertBefore($imageNode,$anchor); $newNodes[]=$imageNode;
        addParagraph($caption,$captionTemplate,true);
        addParagraph($description,$bodyTemplate);
        $manifest[]=['caption'=>$caption,'source'=>'docs/05-ui/wireframes/'.$filename,'media'=>'word/media/'.$mediaName,'relationship'=>$relationshipId,'sha256'=>hash_file('sha256',$path),'widthEmu'=>$cx,'heightEmu'=>$cy];
    }
}
addParagraph('Các phác thảo làm cơ sở đối chiếu bố cục khi xây dựng giao diện. Kiểm tra quyền, tính hợp lệ của dữ liệu và chuyển trạng thái nghiệp vụ được thực hiện tại máy chủ theo các quy tắc đã trình bày ở phần phân tích và thiết kế.',$bodyTemplate);
$parts['word/document.xml']=$dom->saveXML();
echo 'Wireframe XML ready; saving DOCX...'.PHP_EOL;
$parts['word/_rels/document.xml.rels']=$rels->saveXML();
$contentTypes=new DOMDocument(); $contentTypes->loadXML($parts['[Content_Types].xml'], LIBXML_NONET);
$hasPng=false;
foreach($contentTypes->getElementsByTagName('Default') as $default) { if(strtolower($default->getAttribute('Extension'))==='png') { $hasPng=true; } }
if(!$hasPng) { $png=$contentTypes->createElementNS($contentTypes->documentElement->namespaceURI,'Default'); $png->setAttribute('Extension','png'); $png->setAttribute('ContentType','image/png'); $contentTypes->documentElement->appendChild($png); $parts['[Content_Types].xml']=$contentTypes->saveXML(); }
$output=new ZipArchive();
if($output->open($target,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) { throw new RuntimeException('Cannot write augmented draft'); }
foreach($parts as $name=>$bytes) { $output->addFromString($name,$bytes); }
if(!$output->close()) { throw new RuntimeException('Could not save augmented draft'); }
if(hash_file('sha256',$source)!==$originalHash) { throw new RuntimeException('Approved source changed during processing'); }
// Verify body preservation independently after serializing the DOCX.
$check=new ZipArchive(); $check->open($target);
$checkedDom=new DOMDocument(); $checkedDom->loadXML($check->getFromName('word/document.xml'),LIBXML_NONET); $checkedXp=xpathFor($checkedDom);
$rest=[]; $inside=false;
foreach($checkedXp->query('/w:document/w:body/*') as $node) {
    $text=paragraphText($node,$checkedXp);
    if($text==='2.7.3. Phác thảo giao diện') { $inside=true; continue; }
    if($inside && preg_match('/^2\.8\.\s/u',$text)) { $inside=false; }
    if(!$inside) { $rest[]=hash('sha256',$checkedDom->saveXML($node)); }
}
if($rest!==$baseline) { throw new RuntimeException('Existing document content was altered'); }
foreach($manifest as $image) { if(hash('sha256',$check->getFromName($image['media']))!==$image['sha256']) { throw new RuntimeException('Wireframe bytes changed'); } }
if($checkedXp->query('//w:drawing')->length!==$originalFigures+25) { throw new RuntimeException('Unexpected drawing count'); }
foreach($parts as $name=>$bytes) {
    if(str_ends_with($name,'.xml') || str_ends_with($name,'.rels')) { $xml=new DOMDocument(); if(!$xml->loadXML($bytes,LIBXML_NONET)) { throw new RuntimeException('Invalid XML part: '.$name); } }
}
$check->close();
file_put_contents(__DIR__.'/working/approved-wireframes-manifest.json',json_encode(['approvedSourceSha256'=>$originalHash,'source'=>$source,'output'=>$target,'originalDrawings'=>$originalFigures,'addedFigures'=>$manifest,'preservedBodyElements'=>count($baseline)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo 'Added '.count($manifest).' original wireframes in section 2.7.3; figures 2.'.($highestCaption+1).'–2.'.$figureNumber.'.'.PHP_EOL;
echo 'Verified '.count($baseline).' original body elements unchanged; original media and document parts retained.'.PHP_EOL;
echo 'Saved: '.$target.PHP_EOL;
