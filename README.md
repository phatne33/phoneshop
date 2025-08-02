# 🌐 Website Bán Hàng PHP + MySQL

Dự án website bán hàng sử dụng PHP thuần và MySQL (quản lý bằng phpMyAdmin). Website cho phép người dùng xem sản phẩm, thêm vào giỏ hàng, đặt hàng và quản lý đơn hàng bên phía admin.

## 🛠️ Công nghệ sử dụng

- Ngôn ngữ: PHP (thuần)
- Cơ sở dữ liệu: MySQL (truy cập qua phpMyAdmin)
- Giao diện: HTML, CSS, JavaScript, Bootstrap
- Server: XAMPP / Laragon / MAMP (Apache + MySQL)

## 🧩 Tính năng chính

- ✅ Trang người dùng:
  - Xem danh sách sản phẩm
  - Tìm kiếm sản phẩm
  - Giỏ hàng (thêm/xoá sản phẩm)
  - Đặt hàng
  - Đăng nhập bằng tài khoản google, đổi mật khẩu bằng OTP gửi về email
- ✅ Trang admin:
  - Quản lý danh mục
  - Quản lý sản phẩm
  - Quản lý đơn hàng
  - Quản lý khách hàng
  - Quản lý mã giảm giá, flashsale (sử dụng cronjob)
Và còn nhiều các chức năng khác

## 💾 Cài đặt & chạy dự án

- Import sql/phonedb.sql để chạy website
- Truy cập localhost/phone-shop để tới website

Tóm lại : vì đây là website đầu tiền của tôi làm cảm thấy tâm huyết từ đầu mới học code, nên sẽ có những sai xót như một vài lỗi nhỏ, bị bể responsive và các modal, tổ chức các file chưa tốt loằn ngoằng, chưa hoàn toàn 
áp dụng Ajax cho các chức năng của admin, việc ẩn các File như API và Google Auth vào biến môi trường ENV, nên mong sẽ được bỏ qua vì nếu lập trình lại 1 website như vậy cũng mất không ít thời gian.


