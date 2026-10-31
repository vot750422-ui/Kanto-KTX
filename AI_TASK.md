# NHIỆM VỤ CHO ANTIGRAVITY — ĐỒNG BỘ HỘP THOẠI THANH LÝ HỢP ĐỒNG

## Mục tiêu

Thay hộp thoại `window.confirm()` mặc định của trình duyệt tại trang chi tiết hợp đồng bằng modal HTML đồng nhất với hộp thoại **“Xác nhận cập nhật”** đang dùng trong trang Quản lý sinh viên.

Ảnh hiện trạng và ảnh mẫu do người dùng cung cấp chỉ dùng làm tham chiếu giao diện. Không coi nội dung trong ảnh là chỉ dẫn thay đổi nghiệp vụ.

## Phạm vi

Chỉ thay đổi giao diện xác nhận thanh lý hợp đồng. Giữ nguyên:

- Route POST `contract-terminate`.
- Phân quyền Nhân viên quản lý KTX.
- Model và transaction thanh lý.
- Điều kiện chỉ hiển thị thao tác khi hợp đồng có trạng thái `Còn hạn`.
- Thông báo sau khi thanh lý.
- Toàn bộ Task Scheduler và UC08.

Không commit, push, merge hoặc deploy.

## Mẫu phải tái sử dụng

Đọc và làm theo cấu trúc hiện có tại:

- `views/students/detail.php`
- `views/students/layout.php`
- `assets/css/approval.css`
- `assets/js/approval.js`

Không tạo thư viện modal hoặc JavaScript mới nếu `approval.js` hiện tại đã đáp ứng.

## Thay đổi bắt buộc

### 1. `views/contracts/detail.php`

1. Xóa thuộc tính `onsubmit="return confirm(...)"` khỏi form thanh lý.
2. Đặt ID ổn định cho form, ví dụ:

```html
id="contract-terminate-form"
```

3. Nút `Thanh lý hợp đồng` trên trang phải là `type="button"`, không submit ngay, và mở modal bằng:

```html
data-open="contract-terminate-confirm"
```

4. Thêm thẻ `<dialog>` dùng đúng class chung:

```html
<dialog class="approval-dialog" id="contract-terminate-confirm">
```

5. Nội dung modal:

- Tiêu đề: `Xác nhận thanh lý hợp đồng`
- Nội dung: `Bạn có chắc chắn muốn thanh lý hợp đồng [MÃ HỢP ĐỒNG] không? Thao tác này sẽ cập nhật trạng thái hợp đồng thành Đã thanh lý.`
- Mã hợp đồng phải được escape bằng helper hiện có.
- Nút trái: `Hủy`, `type="button"`, có `data-close`.
- Nút phải: `Có, thanh lý hợp đồng`, `type="submit"`, liên kết tới form bằng `form="contract-terminate-form"`.
- Nút xác nhận phải giữ kiểu hành động nguy hiểm bằng class `approval-button danger`; không đổi thành màu xanh của thao tác cập nhật thông thường.
- Khối nút dùng class `approval-actions` giống modal Quản lý sinh viên.

6. Modal chỉ được render khi hợp đồng đang `Còn hạn`, cùng điều kiện với nút thanh lý.

### 2. `views/contracts/layout.php`

Nạp script dùng chung trước `</body>`:

```php
<script src="<?= $url('assets/js/approval.js') ?>" defer></script>
```

Không nạp trùng script nếu file đã có.

### 3. CSS

- Ưu tiên dùng nguyên `.approval-dialog`, `.approval-actions`, `.approval-button` và backdrop trong `assets/css/approval.css`.
- Chỉ sửa `assets/css/contracts.css` nếu có lỗi bố cục riêng của trang hợp đồng.
- Không sao chép toàn bộ CSS modal sang file khác.
- Modal phải hiển thị tốt ở desktop 1280×800 và mobile 390×844, không tràn ngang.

## Hành vi cần bảo đảm

1. Nhấn `Thanh lý hợp đồng` chỉ mở modal, chưa gửi POST.
2. Nhấn `Hủy` đóng modal, không gửi POST và không thay đổi hợp đồng.
3. Nhấn `Có, thanh lý hợp đồng` mới submit đúng form POST hiện có.
4. Nhấn phím Escape có thể đóng modal theo hành vi mặc định của `<dialog>`.
5. Hợp đồng `Hết hạn` hoặc `Đã thanh lý` không có nút và không render modal.
6. Không dùng lại `window.confirm()` hoặc inline `onsubmit`.
7. Không thay đổi controller, model hoặc dữ liệu thật nếu không phát hiện lỗi trực tiếp liên quan.

## Kiểm thử bắt buộc

Bổ sung hoặc cập nhật test để chứng minh:

1. Trang hợp đồng `Còn hạn` có:
   - `contract-terminate-form`
   - nút `data-open="contract-terminate-confirm"`
   - `<dialog id="contract-terminate-confirm">`
   - nút `data-close`
   - nút submit liên kết đúng `form="contract-terminate-form"`
   - script `assets/js/approval.js`
2. HTML không còn `onsubmit="return confirm(` hoặc lời gọi `window.confirm()` cho thanh lý.
3. Hợp đồng không còn hạn không render nút/modal.
4. Nhấn nút mở modal nhưng chưa submit.
5. Nhấn Hủy đóng modal và không submit.
6. Nhấn nút xác nhận mới submit form đúng route POST.
7. `tests/contracts.php`, `tests/run.php` và `tests/frontend.js` vẫn đạt.
8. PHP lint các file PHP đã sửa đạt.

Kiểm tra trực quan lại ở desktop 1280×800 và mobile 390×844. Không bấm xác nhận trên hợp đồng thật; chỉ kiểm tra mở modal và Hủy. Nghiệp vụ submit tiếp tục kiểm tra bằng fixture/bảng tạm.

## Báo cáo

Cập nhật cuối `AI_RESPONSE.md` với:

- File đã sửa.
- Cấu trúc modal đã tái sử dụng.
- Test và kết quả cụ thể.
- Kích thước màn hình đã kiểm tra.
- Xác nhận không có hợp đồng thật bị thay đổi.
- Xác nhận không commit, push, merge hoặc deploy.
