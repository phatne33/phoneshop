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
  - Đặt hàng (có thông báo xác nhận gửi về mail)
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
- mật khẩu admin: phat08355@gmail.com , Phat01234@


Đây là **website đầu tiên** mình tự tay phát triển khi mới bắt đầu học lập trình. Dù còn một số hạn chế như:
- Giao diện chưa responsive hoàn toàn
- Một số modal chưa hoạt động mượt
- File tổ chức chưa gọn (còn loằn ngoằn)
- Ajax chưa được áp dụng cho tất cả chức năng admin
- Biến môi trường `.env` chưa sử dụng cho các API key

Nhưng mình rất tâm huyết và cố gắng hoàn thiện từng chút.  
Nếu có cơ hội viết lại từ đầu, mình tin rằng sẽ cải thiện tốt hơn nhiều.

👉 Mời bạn tham khảo dự án tiếp theo của mình tại:  
🔗 [https://github.com/phatne33/websitequanlybenhvien](https://github.com/phatne33/websitequanlybenhvien)  
(Dự án này sử dụng Ajax gần như toàn bộ, tổ chức file rõ ràng và có hệ thống toast thông báo đầy đủ.)

---

## 🤝 Cảm ơn

Rất mong nhận được góp ý và phản hồi để mình hoàn thiện hơn 💙

