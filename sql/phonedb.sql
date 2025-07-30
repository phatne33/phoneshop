-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 30, 2025 lúc 11:36 AM
-- Phiên bản máy phục vụ: 10.4.25-MariaDB
-- Phiên bản PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `phonedb`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_addresses`
--

CREATE TABLE `tbl_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `recipient_phone` varchar(20) NOT NULL,
  `address_detail` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `commune` varchar(255) DEFAULT NULL,
  `hamlet` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_addresses`
--

INSERT INTO `tbl_addresses` (`address_id`, `user_id`, `recipient_name`, `recipient_phone`, `address_detail`, `is_default`, `latitude`, `longitude`, `province`, `district`, `commune`, `hamlet`) VALUES
(1, 6, 'Người Nhận', '0835512896', 'Trọ Diễm Lan', 0, -82.80232, -358.377176, 'Thành phố Cần Thơ', 'Quận Cái Răng', 'Phường Lê Bình', 'Đường Trần Chiên'),
(2, 7, 'Người Nhận', '0835512896', 'Trọ Diễm Lan', 0, 35.649212, 45.090119, 'Thành phố Cần Thơ', 'Quận Cái Răng', 'Phường Lê Bình', 'Đường Trần Chiên'),
(3, 10, 'Người Nhận', '0835512896', 'Trọ Diễm Lan', 0, NULL, NULL, '92', '919', '31186', 'Đường Trần Chiên'),
(4, 10, 'Người Nhận', '0835512896', 'Trọ Diễm Lan', 0, NULL, NULL, '4', '45', '1372', 'Đường Trần Chiên'),
(5, 10, 'Người Nhận', '0835512896', 'Trọ Diễm Lan', 0, NULL, NULL, '30', '297', '11008', 'Đường Trần Chiên'),
(6, 8, 'Người Nhận', '0392139094', 'nhà tao', 0, NULL, NULL, '96', '969', '32140', 'ấp Rau Dừa B'),
(7, 6, 'Người Nhận', '0835512896', 'Nhà Bà Lệ', 0, NULL, NULL, 'Tỉnh Cà Mau', 'Huyện Cái Nước', 'Xã Hưng Mỹ', 'Chợ Rau Dừa'),
(9, 16, 'Người Nhận', '0835512896', 'Trọ Diễm Lan', 0, NULL, NULL, 'Thành phố Cần Thơ', 'Quận Cái Răng', 'Phường Lê Bình', 'Đường Trần Chiên');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `color` varchar(50) DEFAULT NULL,
  `storage` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_cart`
--

INSERT INTO `tbl_cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `added_at`, `color`, `storage`) VALUES
(68, 16, 26, 1, '2025-05-11 11:59:29', 'Đen Ngọc', '256GB'),
(76, 6, 11, 1, '2025-05-14 08:30:24', 'Titan', '256GB'),
(79, 17, 18, 2, '2025-07-24 06:35:43', 'Đen', '128GB'),
(80, 7, 14, 2, '2025-07-25 05:10:36', 'Vàng', '64GB'),
(81, 7, 12, 1, '2025-07-25 06:57:29', 'Đen', '128GB');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_categories`
--

CREATE TABLE `tbl_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_categories`
--

INSERT INTO `tbl_categories` (`category_id`, `category_name`, `description`, `category_logo`) VALUES
(1, 'Appleă', 'Apple, được thành lập vào năm 1976, đã cách mạng hóa ngành công nghiệp điện thoại thông minh với sự ra mắt của iPhone vào năm 2007. iPhone nổi bật với thiết kế tối giản, hệ điều hành iOS mượt mà và hệ sinh thái ứng dụng phong phú. Các sản phẩm iPhone thường được đánh giá cao về chất lượng camera, hiệu năng mạnh mẽ và tính bảo mật cao. Apple cũng nổi bật trong việc cải tiến công nghệ, như Face ID và chip xử lý độc quyền, tạo nên một trải nghiệm người dùng đặc biệt.', '1746688034_iPhone-Logo-2007.png'),
(3, 'Oppo', 'Oppo là một thương hiệu điện thoại thông minh nổi tiếng của Trung Quốc, được thành lập vào năm 2001. Hãng này nổi bật với các sản phẩm có thiết kế đẹp mắt và camera chất lượng cao. Oppo thường xuyên cập nhật công nghệ mới, đặc biệt trong lĩnh vực chụp ảnh, với các dòng sản phẩm như Oppo Reno và Oppo Find.', '1743846127_oppologo.png'),
(4, 'Vivo', 'Vivo cũng là một thương hiệu điện thoại đến từ Trung Quốc, ra mắt vào năm 2009. Hãng này tập trung vào việc phát triển các sản phẩm với tính năng âm thanh chất lượng và camera selfie tuyệt vời. Vivo đã giới thiệu nhiều công nghệ tiên tiến, như cảm biến vân tay dưới màn hình, và dòng sản phẩm V và X rất được ưa chuộng.', '1743846143_vivologo.png'),
(5, 'Huawei', 'Huawei là một trong những nhà sản xuất thiết bị viễn thông lớn nhất thế giới, được thành lập vào năm 1987. Hãng này đã phát triển mạnh mẽ trong lĩnh vực điện thoại thông minh, đặc biệt là với dòng sản phẩm P và Mate, nổi bật với công nghệ camera tiên tiến và hiệu năng mạnh mẽ. Tuy nhiên, Huawei cũng gặp phải nhiều thách thức về chính trị và thương mại trong những năm gần đây.\r\n\r\n', '1743846170_Huawei-logo.jpg'),
(6, 'Xiaomi', 'Xiaomi, thành lập vào năm 2010, đã trở thành một trong những thương hiệu điện thoại lớn nhất thế giới. Hãng này nổi tiếng với chiến lược cung cấp sản phẩm có giá trị cao với giá thấp. Xiaomi không chỉ sản xuất điện thoại thông minh mà còn mở rộng sang nhiều lĩnh vực khác như thiết bị gia dụng thông minh. Các dòng sản phẩm Mi và Redmi được ưa chuộng rộng rãi.,', '1743846197_logo-xiaomi-3.png');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_chatbot`
--

CREATE TABLE `tbl_chatbot` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_message` text NOT NULL,
  `bot_reply` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sender_type` enum('user','bot') NOT NULL,
  `status` enum('success','failed') DEFAULT 'success'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_chatbot`
--

INSERT INTO `tbl_chatbot` (`id`, `user_id`, `user_message`, `bot_reply`, `created_at`, `sender_type`, `status`) VALUES
(1, 16, 'chào', 'chào', '2025-07-16 20:06:49', 'user', 'success'),
(2, 16, '', 'Chào bạn!  Rất vui được hỗ trợ bạn trong việc chọn mua điện thoại.  Bạn đang tìm kiếm loại điện thoại nào vậy?  Để tôi có thể tư vấn chính xác hơn, bạn có thể cho mình biết một vài thông tin như: mức giá bạn mong muốn, các tính năng quan trọng (chụp ảnh, chơi game, pin trâu...), hệ điều hành ưa thích (Android hay iOS) và thương hiệu yêu thích không?  Càng nhiều thông tin', '2025-07-16 20:06:49', 'bot', 'success'),
(3, 16, 'điện thoại chơi game tốt', 'điện thoại chơi game tốt', '2025-07-16 20:07:01', 'user', 'success'),
(4, 16, '', 'Chào bạn!  \"Điện thoại chơi game tốt\" là một câu hỏi khá rộng.  Để tư vấn chính xác, mình cần biết thêm thông tin như ngân sách của bạn là bao nhiêu? Bạn ưu tiên cấu hình mạnh mẽ, màn hình đẹp hay thời lượng pin lâu?  \n\nHiện nay có nhiều dòng điện thoại chơi game mạnh mẽ như Asus ROG Phone, Nubia Red Magic hay Black Shark.  Tuy nhiên, các dòng flagship của Samsung, Xiaomi hay Apple cũng đáp ứng', '2025-07-16 20:07:01', 'bot', 'success'),
(5, 16, 'tầm giá 5 triệu', 'tầm giá 5 triệu', '2025-07-16 20:07:33', 'user', 'success'),
(6, 16, '', 'Với tầm giá 5 triệu, bạn có khá nhiều lựa chọn điện thoại chơi game tốt.  Tuy nhiên, để tư vấn chính xác hơn, mình cần biết thêm một vài thông tin. Bạn ưu tiên cấu hình mạnh mẽ nhất có thể, hay màn hình tốt, thời lượng pin dài?  Các hãng như Xiaomi, Realme, hay Vivo thường có các mẫu đáp ứng tốt nhu cầu chơi game trong tầm giá này.  Hãy cho mình biết thêm yêu cầu cụ thể về dung lượng RAM', '2025-07-16 20:07:33', 'bot', 'success'),
(7, 16, '??iện thoại ch??i game gi?? rẻ', 'Đề xuất điện thoại chơi game tốt tầm giá 5-8 triệu:\r\n                            \r\n                                \r\n                                \r\n                                    Realme 12 Pro\r\n                                    Giá: 5.990.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    \r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Realme GT 6\r\n                                    Giá: 6.495.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    Realme GT 6 là điện thoại tầm trung chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.78 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 8s Gen 3 cho hiệu năng ch...\r\n                                \r\n                            \r\n                          ', '2025-07-16 20:19:11', 'user', 'success'),
(8, 16, '', 'Đề xuất điện thoại chơi game tốt tầm giá 5-8 triệu:\r\n                            \r\n                                \r\n                                \r\n                                    Realme 12 Pro\r\n                                    Giá: 5.990.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    \r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Realme GT 6\r\n                                    Giá: 6.495.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    Realme GT 6 là điện thoại tầm trung chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.78 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 8s Gen 3 cho hiệu năng ch...\r\n                                \r\n                            \r\n                          ', '2025-07-16 20:19:11', 'bot', 'success'),
(9, 16, 'gi?? cao h??n ??i', 'Không thể xử lý câu hỏi, vui lòng thử lại!', '2025-07-16 20:19:24', 'user', 'success'),
(10, 16, '', 'Không thể xử lý câu hỏi, vui lòng thử lại!', '2025-07-16 20:19:24', 'bot', 'success'),
(11, 16, 'giá cao hơn', 'Tôi xin lỗi vì câu trả lời trước đó không hữu ích.  \"Giá cao hơn\" so với gì vậy ạ?  Để tôi có thể tư vấn chính xác, bạn cần cho tôi biết bạn đang so sánh với mức giá nào hoặc với mẫu điện thoại nào.  Ví dụ, bạn muốn tìm điện thoại có giá cao hơn 5 triệu đồng, hay cao hơn chiếc iPhone 13?  Càng nhiều thông tin bạn cung cấp, tôi càng có thể giúp bạn tìm được', '2025-07-16 20:25:30', 'user', 'success'),
(12, 16, '', 'Tôi xin lỗi vì câu trả lời trước đó không hữu ích.  \"Giá cao hơn\" so với gì vậy ạ?  Để tôi có thể tư vấn chính xác, bạn cần cho tôi biết bạn đang so sánh với mức giá nào hoặc với mẫu điện thoại nào.  Ví dụ, bạn muốn tìm điện thoại có giá cao hơn 5 triệu đồng, hay cao hơn chiếc iPhone 13?  Càng nhiều thông tin bạn cung cấp, tôi càng có thể giúp bạn tìm được', '2025-07-16 20:25:30', 'bot', 'success'),
(13, 16, 'điện thoại chơi game free fire tầm giá 10 triệu', 'Đề xuất điện thoại chơi game tốt tầm giá 5.000.000 - 8.000.000:\r\n                            \r\n                                \r\n                                \r\n                                    Realme 12 Pro\r\n                                    Giá: 5.990.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    Realme 12 Pro là smartphone tầm trung giá rẻ chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 hỗ trợ chơi game mượt, pin trâu 5000mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.\r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Realme GT 6\r\n                                    Giá: 6.495.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    Realme GT 6 là điện thoại tầm trung chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.78 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 8s Gen 3 cho hiệu năng chơi game mượt, pin trâu 5500mAh. Phù hợp cho game thủ, giải trí và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.\r\n                                \r\n                            \r\n                          ', '2025-07-16 20:25:54', 'user', 'success'),
(14, 16, '', 'Đề xuất điện thoại chơi game tốt tầm giá 5.000.000 - 8.000.000:\r\n                            \r\n                                \r\n                                \r\n                                    Realme 12 Pro\r\n                                    Giá: 5.990.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    Realme 12 Pro là smartphone tầm trung giá rẻ chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 hỗ trợ chơi game mượt, pin trâu 5000mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.\r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Realme GT 6\r\n                                    Giá: 6.495.000 VNĐ (Giảm: 20.00% với mã XIAOMI20)\r\n                                    Realme GT 6 là điện thoại tầm trung chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.78 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 8s Gen 3 cho hiệu năng chơi game mượt, pin trâu 5500mAh. Phù hợp cho game thủ, giải trí và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.\r\n                                \r\n                            \r\n                          ', '2025-07-16 20:25:54', 'bot', 'success'),
(15, 16, 'giá cao hơn nữa', 'Vậy nếu ngân sách cao hơn 10 triệu, bạn có thể cân nhắc các dòng flagship như  iPhone 14, Samsung Galaxy S23 hoặc Xiaomi 13.  Những chiếc điện thoại này sở hữu cấu hình mạnh mẽ hơn rất nhiều,  màn hình tốt hơn, cùng camera chất lượng cao, đảm bảo trải nghiệm chơi Free Fire cực mượt mà và hình ảnh sắc nét.  Tuy nhiên, giá thành sẽ cao hơn đáng kể so với các lựa chọn', '2025-07-16 20:26:26', 'user', 'success'),
(16, 16, '', 'Vậy nếu ngân sách cao hơn 10 triệu, bạn có thể cân nhắc các dòng flagship như  iPhone 14, Samsung Galaxy S23 hoặc Xiaomi 13.  Những chiếc điện thoại này sở hữu cấu hình mạnh mẽ hơn rất nhiều,  màn hình tốt hơn, cùng camera chất lượng cao, đảm bảo trải nghiệm chơi Free Fire cực mượt mà và hình ảnh sắc nét.  Tuy nhiên, giá thành sẽ cao hơn đáng kể so với các lựa chọn', '2025-07-16 20:26:26', 'bot', 'success'),
(17, 16, 'có mã giảm giá không', 'Chào bạn!  Hiện tại mình chưa có thông tin về mã giảm giá chính thức cho các dòng máy iPhone 14, Samsung Galaxy S23 hay Xiaomi 13.  Tuy nhiên, bạn có thể thường xuyên kiểm tra trên các trang thương mại điện tử lớn như Tiki, Shopee, Lazada hoặc website chính hãng của các nhà sản xuất.  Đôi khi họ có các chương trình khuyến mãi, giảm giá hoặc tặng kèm phụ kiện hấp dẫn.  Mình khuyên', '2025-07-16 20:27:12', 'user', 'success'),
(18, 16, '', 'Chào bạn!  Hiện tại mình chưa có thông tin về mã giảm giá chính thức cho các dòng máy iPhone 14, Samsung Galaxy S23 hay Xiaomi 13.  Tuy nhiên, bạn có thể thường xuyên kiểm tra trên các trang thương mại điện tử lớn như Tiki, Shopee, Lazada hoặc website chính hãng của các nhà sản xuất.  Đôi khi họ có các chương trình khuyến mãi, giảm giá hoặc tặng kèm phụ kiện hấp dẫn.  Mình khuyên', '2025-07-16 20:27:12', 'bot', 'success'),
(19, 16, 'đang có đợt flashsale nào không', 'Chào bạn!  Hiện tại mình chưa nắm được thông tin chính xác về đợt flash sale nào cho iPhone 14, Samsung Galaxy S23 hay Xiaomi 13.  Tuy nhiên,  bạn nên theo dõi thường xuyên các trang thương mại điện tử lớn mình đã đề cập trước đó.  Hầu hết các đợt flash sale thường được thông báo bất ngờ nên việc cập nhật liên tục là rất cần thiết.  Bạn cũng có thể đăng ký nhận thông báo khuyến', '2025-07-16 20:28:32', 'user', 'success'),
(20, 16, '', 'Chào bạn!  Hiện tại mình chưa nắm được thông tin chính xác về đợt flash sale nào cho iPhone 14, Samsung Galaxy S23 hay Xiaomi 13.  Tuy nhiên,  bạn nên theo dõi thường xuyên các trang thương mại điện tử lớn mình đã đề cập trước đó.  Hầu hết các đợt flash sale thường được thông báo bất ngờ nên việc cập nhật liên tục là rất cần thiết.  Bạn cũng có thể đăng ký nhận thông báo khuyến', '2025-07-16 20:28:32', 'bot', 'success'),
(21, 16, 'cho t??i xin m?? giảm gi??', 'Không thể xử lý câu hỏi, vui lòng thử lại!', '2025-07-16 20:39:15', 'user', 'success'),
(22, 16, '', 'Không thể xử lý câu hỏi, vui lòng thử lại!', '2025-07-16 20:39:15', 'bot', 'success'),
(23, 16, 'm?? giảm gi??', 'Không thể xử lý câu hỏi do lỗi API, vui lòng thử lại!', '2025-07-16 20:44:27', 'user', 'success'),
(24, 16, '', 'Không thể xử lý câu hỏi do lỗi API, vui lòng thử lại!', '2025-07-16 20:44:27', 'bot', 'success'),
(25, 16, 'mã giảm giá', 'Mã giảm giá hiện có:\r\n                            Mã: IPHONE15\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SAMSUNG500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: XIAOMI20\r\n                            Giảm: 20.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-10-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE8PLUS\r\n                            Giảm: 300.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-09-30\r\n                            Copy\r\n                          ', '2025-07-17 11:25:05', 'user', 'success'),
(26, 16, '', 'Mã giảm giá hiện có:\r\n                            Mã: IPHONE15\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SAMSUNG500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: XIAOMI20\r\n                            Giảm: 20.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-10-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE8PLUS\r\n                            Giảm: 300.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-09-30\r\n                            Copy\r\n                          ', '2025-07-17 11:25:05', 'bot', 'success'),
(27, 16, 'thêm mã giảm giá', 'Mã giảm giá hiện có:\r\n                            Mã: IPHONE15\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SAMSUNG500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: XIAOMI20\r\n                            Giảm: 20.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-10-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE8PLUS\r\n                            Giảm: 300.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-09-30\r\n                            Copy\r\n                          ', '2025-07-17 11:25:40', 'user', 'success'),
(28, 16, '', 'Mã giảm giá hiện có:\r\n                            Mã: IPHONE15\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SAMSUNG500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: XIAOMI20\r\n                            Giảm: 20.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-10-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE8PLUS\r\n                            Giảm: 300.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-09-30\r\n                            Copy\r\n                          ', '2025-07-17 11:25:40', 'bot', 'success'),
(29, 16, 'đang có đợt flashsale nào không', 'Hiện tại (16/07/2025) không có flash sale nào cho iPhone 14, Samsung Galaxy S23, hoặc Xiaomi 13. Vui lòng kiểm tra trên Tiki, Shopee, hoặc Lazada để biết thêm chi tiết!', '2025-07-17 11:26:20', 'user', 'success'),
(30, 16, '', 'Hiện tại (16/07/2025) không có flash sale nào cho iPhone 14, Samsung Galaxy S23, hoặc Xiaomi 13. Vui lòng kiểm tra trên Tiki, Shopee, hoặc Lazada để biết thêm chi tiết!', '2025-07-17 11:26:20', 'bot', 'success'),
(31, 16, 'chào bạn', 'Chào bạn!  Mình thấy bạn đang quan tâm đến các đợt flash sale cho iPhone 14, Samsung Galaxy S23 và Xiaomi 13 phải không?  Như mình đã trả lời trước đó, hiện tại (16/07/2025) chưa có chương trình giảm giá đột biến nào cho ba mẫu điện thoại này.  Tuy nhiên, khuyến nghị của mình vẫn là bạn nên thường xuyên kiểm tra các trang thương mại điện tử lớn như Tiki, Shopee và Lazada.  Nhiều khi các chương trình khuyến mãi sẽ được cập nhật bất ngờ.  Ngoài ra, bạn có thể tham khảo thêm các cửa hàng điện thoại di động chính hãng hoặc các nhà bán lẻ lớn khác', '2025-07-17 11:30:48', 'user', 'success'),
(32, 16, '', 'Chào bạn!  Mình thấy bạn đang quan tâm đến các đợt flash sale cho iPhone 14, Samsung Galaxy S23 và Xiaomi 13 phải không?  Như mình đã trả lời trước đó, hiện tại (16/07/2025) chưa có chương trình giảm giá đột biến nào cho ba mẫu điện thoại này.  Tuy nhiên, khuyến nghị của mình vẫn là bạn nên thường xuyên kiểm tra các trang thương mại điện tử lớn như Tiki, Shopee và Lazada.  Nhiều khi các chương trình khuyến mãi sẽ được cập nhật bất ngờ.  Ngoài ra, bạn có thể tham khảo thêm các cửa hàng điện thoại di động chính hãng hoặc các nhà bán lẻ lớn khác', '2025-07-17 11:30:48', 'bot', 'success'),
(33, 16, 'tư vấn cho tôi điện thoại chơi pubgmobile pin trâu giá rẻ đi tầm khoản dưới 10 triệu', 'Chào bạn!  Với nhu cầu chơi PUBG Mobile mượt mà, pin trâu và giá dưới 10 triệu, mình có một vài gợi ý.  Bạn nên xem xét các dòng điện thoại có chip xử lý tầm trung đến cao cấp như Snapdragon 778G trở lên hoặc MediaTek Dimensity 8000 trở lên để đảm bảo trải nghiệm chơi game tốt.  Về pin, hãy tìm những máy có dung lượng pin từ 5000mAh trở lên.\n\nMột số lựa chọn bạn có thể tham khảo (hãy kiểm tra giá cả cập nhật tại thời điểm mua):  một số dòng Realme, Redmi Note series hoặc Poco X series thường đáp ứng được các tiêu chí này.  ', '2025-07-17 11:31:32', 'user', 'success'),
(34, 16, '', 'Chào bạn!  Với nhu cầu chơi PUBG Mobile mượt mà, pin trâu và giá dưới 10 triệu, mình có một vài gợi ý.  Bạn nên xem xét các dòng điện thoại có chip xử lý tầm trung đến cao cấp như Snapdragon 778G trở lên hoặc MediaTek Dimensity 8000 trở lên để đảm bảo trải nghiệm chơi game tốt.  Về pin, hãy tìm những máy có dung lượng pin từ 5000mAh trở lên.\n\nMột số lựa chọn bạn có thể tham khảo (hãy kiểm tra giá cả cập nhật tại thời điểm mua):  một số dòng Realme, Redmi Note series hoặc Poco X series thường đáp ứng được các tiêu chí này.  ', '2025-07-17 11:31:32', 'bot', 'success'),
(35, 16, 'so sánh nhanh sss24 và ip16 promax', 'So sánh nhanh Samsung Galaxy S23 và iPhone 16 Pro Max thì quả là \"đấu trường khổng lồ\"!  Về hiệu năng, cả hai đều thuộc hàng top, nhưng S23 thiên về đa nhiệm mượt mà còn iPhone 16 Pro Max mạnh mẽ hơn trong xử lý đồ họa game nặng.  Về camera, iPhone 16 Pro Max thường được đánh giá cao hơn về khả năng chụp ảnh tính tổng thể, đặc biệt là chụp đêm.  Tuy nhiên, S23 cũng có những điểm mạnh riêng về quay phim.  Pin thì iPhone 16 Pro Max thường trâu hơn.  Cuối cùng, hệ sinh thái và trải nghiệm người dùng thì mỗi bên có ưu điểm riêng', '2025-07-17 11:38:39', 'user', 'success'),
(36, 16, '', 'So sánh nhanh Samsung Galaxy S23 và iPhone 16 Pro Max thì quả là \"đấu trường khổng lồ\"!  Về hiệu năng, cả hai đều thuộc hàng top, nhưng S23 thiên về đa nhiệm mượt mà còn iPhone 16 Pro Max mạnh mẽ hơn trong xử lý đồ họa game nặng.  Về camera, iPhone 16 Pro Max thường được đánh giá cao hơn về khả năng chụp ảnh tính tổng thể, đặc biệt là chụp đêm.  Tuy nhiên, S23 cũng có những điểm mạnh riêng về quay phim.  Pin thì iPhone 16 Pro Max thường trâu hơn.  Cuối cùng, hệ sinh thái và trải nghiệm người dùng thì mỗi bên có ưu điểm riêng', '2025-07-17 11:38:39', 'bot', 'success'),
(37, 16, 'cho tôi xin mã giảm giá', 'Mã giảm giá hiện có:\r\n                            Mã: GIAM10\r\n                            Giảm: 10.00%\r\n                            Hiệu lực: 2025-04-06 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SALE500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-06 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE15\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SAMSUNG500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: OPPOX8\r\n                            Giảm: 10.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-11-30\r\n                            Copy\r\n                          \r\n                            Mã: XIAOMI20\r\n                            Giảm: 20.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-10-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE8PLUS\r\n                            Giảm: 300.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-09-30\r\n                            Copy\r\n                          \r\n                            Mã: VIP_DONG\r\n                            Giảm: 3.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_BAC\r\n                            Giảm: 5.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_VANG\r\n                            Giảm: 7.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_KIMCUONG\r\n                            Giảm: 10.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_LUCBAO\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          ', '2025-07-17 11:43:22', 'user', 'success'),
(38, 16, '', 'Mã giảm giá hiện có:\r\n                            Mã: GIAM10\r\n                            Giảm: 10.00%\r\n                            Hiệu lực: 2025-04-06 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SALE500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-06 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE15\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: SAMSUNG500K\r\n                            Giảm: 500.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: OPPOX8\r\n                            Giảm: 10.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-11-30\r\n                            Copy\r\n                          \r\n                            Mã: XIAOMI20\r\n                            Giảm: 20.00%\r\n                            Hiệu lực: 2025-04-12 đến 2025-10-31\r\n                            Copy\r\n                          \r\n                            Mã: IPHONE8PLUS\r\n                            Giảm: 300.000 VNĐ\r\n                            Hiệu lực: 2025-04-12 đến 2025-09-30\r\n                            Copy\r\n                          \r\n                            Mã: VIP_DONG\r\n                            Giảm: 3.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_BAC\r\n                            Giảm: 5.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_VANG\r\n                            Giảm: 7.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_KIMCUONG\r\n                            Giảm: 10.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          \r\n                            Mã: VIP_LUCBAO\r\n                            Giảm: 15.00%\r\n                            Hiệu lực: 2025-05-15 đến 2025-12-31\r\n                            Copy\r\n                          ', '2025-07-17 11:43:22', 'bot', 'success'),
(39, 16, 'tư vấn cho tôi điện thoại chụp ảnh đẹp', 'Chào bạn! Bạn muốn tìm điện thoại chụp ảnh đẹp đúng không?  Tùy thuộc vào ngân sách và nhu cầu của bạn, mình có một vài gợi ý:\n\nNếu bạn muốn chất lượng ảnh xuất sắc và không quá bận tâm về giá cả, các dòng flagship như iPhone 15 series hoặc Samsung Galaxy S23 series là lựa chọn tuyệt vời.  Cả hai đều sở hữu hệ thống camera mạnh mẽ với nhiều tính năng chụp ảnh chuyên nghiệp.\n\nNếu bạn muốn tiết kiệm hơn một chút nhưng vẫn đảm bảo chất lượng ảnh tốt, bạn có thể cân nhắc các dòng điện thoại tầm trung cao cấp như Google Pixel 7a, OPPO Find', '2025-07-17 11:43:58', 'user', 'success'),
(40, 16, '', 'Chào bạn! Bạn muốn tìm điện thoại chụp ảnh đẹp đúng không?  Tùy thuộc vào ngân sách và nhu cầu của bạn, mình có một vài gợi ý:\n\nNếu bạn muốn chất lượng ảnh xuất sắc và không quá bận tâm về giá cả, các dòng flagship như iPhone 15 series hoặc Samsung Galaxy S23 series là lựa chọn tuyệt vời.  Cả hai đều sở hữu hệ thống camera mạnh mẽ với nhiều tính năng chụp ảnh chuyên nghiệp.\n\nNếu bạn muốn tiết kiệm hơn một chút nhưng vẫn đảm bảo chất lượng ảnh tốt, bạn có thể cân nhắc các dòng điện thoại tầm trung cao cấp như Google Pixel 7a, OPPO Find', '2025-07-17 11:43:58', 'bot', 'success'),
(41, 16, '??iện thoại ch??i game tốt', 'Hiện tại không có điện thoại chơi game trong tầm giá 0 - 10.000.000. Bạn muốn thử tầm giá khác không?', '2025-07-17 14:48:18', 'user', 'success'),
(42, 16, '', 'Hiện tại không có điện thoại chơi game trong tầm giá 0 - 10.000.000. Bạn muốn thử tầm giá khác không?', '2025-07-17 14:48:18', 'bot', 'success'),
(43, 16, 'tầm gi?? kh??c', 'Không thể xử lý câu hỏi do lỗi API, vui lòng thử lại!', '2025-07-17 14:48:36', 'user', 'success'),
(44, 16, '', 'Không thể xử lý câu hỏi do lỗi API, vui lòng thử lại!', '2025-07-17 14:48:36', 'bot', 'success'),
(45, 16, 'điện thoại chơi game giá rẻ', 'Hiện tại không có điện thoại chơi game trong tầm giá 0 - 10.000.000. Bạn muốn thử tầm giá khác không?', '2025-07-17 17:52:09', 'user', 'success'),
(46, 16, '', 'Hiện tại không có điện thoại chơi game trong tầm giá 0 - 10.000.000. Bạn muốn thử tầm giá khác không?', '2025-07-17 17:52:09', 'bot', 'success'),
(47, 16, 'có', '```html\nVậy bạn muốn thử tầm giá nào cao hơn ạ?  Để tôi có thể tư vấn chính xác hơn, bạn cho mình biết bạn muốn mức giá tối đa là bao nhiêu?  Ngoài ra,  bạn có yêu cầu gì khác về cấu hình không? Ví dụ như dung lượng RAM, bộ nhớ trong, màn hình (tần số quét, độ phân giải), hay thương hiệu nào bạn ưu tiên? \n\nCàng nhiều thông tin bạn cung cấp, tôi càng có thể đưa ra những gợi ý phù hợp hơn.  Chẳng hạn, nếu bạn muốn chơi game mượt mà ở mức cấu hình trung bình, thì tầm giá 10-15 triệu', '2025-07-17 17:52:18', 'user', 'success'),
(48, 16, '', '```html\nVậy bạn muốn thử tầm giá nào cao hơn ạ?  Để tôi có thể tư vấn chính xác hơn, bạn cho mình biết bạn muốn mức giá tối đa là bao nhiêu?  Ngoài ra,  bạn có yêu cầu gì khác về cấu hình không? Ví dụ như dung lượng RAM, bộ nhớ trong, màn hình (tần số quét, độ phân giải), hay thương hiệu nào bạn ưu tiên? \n\nCàng nhiều thông tin bạn cung cấp, tôi càng có thể đưa ra những gợi ý phù hợp hơn.  Chẳng hạn, nếu bạn muốn chơi game mượt mà ở mức cấu hình trung bình, thì tầm giá 10-15 triệu', '2025-07-17 17:52:18', 'bot', 'success'),
(49, 16, 'điện thoại chơi pubgmobile giá rẻ', 'Không tìm thấy điện thoại phù hợp với yêu cầu \"điện thoại chơi pubgmobile giá rẻ\". Bạn có thể cung cấp thêm thông tin (ví dụ: thương hiệu, tính năng cụ thể) hoặc kiểm tra trên Phone Shop để biết thêm chi tiết!', '2025-07-17 18:04:16', 'user', 'success'),
(50, 16, '', 'Không tìm thấy điện thoại phù hợp với yêu cầu \"điện thoại chơi pubgmobile giá rẻ\". Bạn có thể cung cấp thêm thông tin (ví dụ: thương hiệu, tính năng cụ thể) hoặc kiểm tra trên Phone Shop để biết thêm chi tiết!', '2025-07-17 18:04:16', 'bot', 'success'),
(51, 7, 'tư vấn điện thoại giá rẻ cho tôi', 'Đề xuất điện thoại phù hợp tầm giá 3.000.000 - 7.000.000:\r\n                            \r\n                                \r\n                                \r\n                                    Vivo V30\r\n                                    Giá: 5.495.000 VNĐ (Giảm: 10.00%)\r\n                                    Vivo V30 là smartphone tầm trung giá rẻ chính hãng Vivo Việt Nam, mới 100%. Màn hình AMOLED 6.44 inch 120Hz, camera 64MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 cho hiệu năng chơi game mượt, pin trâu 4600mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Vivo.\r\n                                    Mã giảm giá: GIAM10 Copy\r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Realme 12 Pro\r\n                                    Giá: 5.990.000 VNĐ (Giảm: 10.00%)\r\n                                    Realme 12 Pro là smartphone tầm trung giá rẻ chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 hỗ trợ chơi game mượt, pin trâu 5000mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.\r\n                                    Mã giảm giá: GIAM10 Copy\r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Oppo Reno 11\r\n                                    Giá: 5.995.000 VNĐ (Giảm: 10.00%)\r\n                                    Oppo Reno 11 là smartphone tầm trung chính hãng OPPO Việt Nam, mới 100%. Thiết kế thời thượng, màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip MediaTek Dimensity 7050 hỗ trợ chơi game mượt, pin trâu 4800mAh. Phù hợp cho người yêu nhiếp ảnh và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng OPPO.\r\n                                    Mã giảm giá: GIAM10 Copy\r\n                                \r\n                            \r\n                          Bạn có muốn xem thêm các mẫu trong tầm giá khác hoặc ưu tiên thương hiệu như Samsung, OPPO? Vui lòng cho mình biết thêm chi tiết!', '2025-07-18 14:12:22', 'user', 'success'),
(52, 7, '', 'Đề xuất điện thoại phù hợp tầm giá 3.000.000 - 7.000.000:\r\n                            \r\n                                \r\n                                \r\n                                    Vivo V30\r\n                                    Giá: 5.495.000 VNĐ (Giảm: 10.00%)\r\n                                    Vivo V30 là smartphone tầm trung giá rẻ chính hãng Vivo Việt Nam, mới 100%. Màn hình AMOLED 6.44 inch 120Hz, camera 64MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 cho hiệu năng chơi game mượt, pin trâu 4600mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Vivo.\r\n                                    Mã giảm giá: GIAM10 Copy\r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Realme 12 Pro\r\n                                    Giá: 5.990.000 VNĐ (Giảm: 10.00%)\r\n                                    Realme 12 Pro là smartphone tầm trung giá rẻ chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 hỗ trợ chơi game mượt, pin trâu 5000mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.\r\n                                    Mã giảm giá: GIAM10 Copy\r\n                                \r\n                            \r\n                          \r\n                            \r\n                                \r\n                                \r\n                                    Oppo Reno 11\r\n                                    Giá: 5.995.000 VNĐ (Giảm: 10.00%)\r\n                                    Oppo Reno 11 là smartphone tầm trung chính hãng OPPO Việt Nam, mới 100%. Thiết kế thời thượng, màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip MediaTek Dimensity 7050 hỗ trợ chơi game mượt, pin trâu 4800mAh. Phù hợp cho người yêu nhiếp ảnh và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng OPPO.\r\n                                    Mã giảm giá: GIAM10 Copy\r\n                                \r\n                            \r\n                          Bạn có muốn xem thêm các mẫu trong tầm giá khác hoặc ưu tiên thương hiệu như Samsung, OPPO? Vui lòng cho mình biết thêm chi tiết!', '2025-07-18 14:12:22', 'bot', 'success'),
(53, 7, 'điện thoại giá rẻ', 'Đề xuất điện thoại chụp ảnh đẹp:Redmi Note 14Giá: 7.999.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 128GBXem chi tiếtRedmi Note 14Giá: 8.999.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtOPPO Find X8Giá: 18.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtOPPO Find X8Giá: 21.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtOPPO Find X8Giá: 25.990.000đGiảm 10.00% với mã GIAM10RAM: 16GB, Bộ nhớ: 1TBXem chi tiếtSamsung Galaxy S24 UltraGiá: 31.900.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtSamsung Galaxy S24 UltraGiá: 36.000.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtSamsung Galaxy S24 UltraGiá: 34.000.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 1TBXem chi tiếtOppo Find N4 FlipGiá: 18.000.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtOppo Find N4 FlipGiá: 22.000.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 512GBXem chi tiếtOppo Find N4 FlipGiá: 25.000.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtVivo V30Giá: 10.000.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 128GBXem chi tiếtVivo V30Giá: 5.495.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtVivo V30Giá: 5.995.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtXiaomi 14Giá: 14.990.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtXiaomi 14Giá: 14.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtXiaomi 14Giá: 16.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtXiaomi 14Giá: 19.990.000đGiảm 10.00% với mã GIAM10RAM: 16GB, Bộ nhớ: 1TBXem chi tiếtHuawei Mate 60 ProGiá: 18.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtHuawei Mate 60 ProGiá: 21.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtHuawei Mate 60 ProGiá: 24.990.000đGiảm 10.00% với mã GIAM10RAM: 16GB, Bộ nhớ: 1TBXem chi tiếtXiaomi 13T ProGiá: 14.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtXiaomi 13T ProGiá: 16.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtXiaomi 13T ProGiá: 19.990.000đGiảm 10.00% với mã GIAM10RAM: 16GB, Bộ nhớ: 1TBXem chi tiếtRealme GT 6Giá: 6.495.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtRealme GT 6Giá: 6.995.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtRealme GT 6Giá: 7.995.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtRealme GT 6Giá: 8.995.000đGiảm 10.00% với mã GIAM10RAM: 16GB, Bộ nhớ: 1TBXem chi tiếtSamsung Galaxy A55Giá: 7.990.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 128GBXem chi tiếtSamsung Galaxy A55Giá: 8.990.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtSamsung Galaxy A55Giá: 9.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtRealme 12 ProGiá: 5.990.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 128GBXem chi tiếtRealme 12 ProGiá: 6.990.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtRealme 12 ProGiá: 7.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtOppo Reno 11Giá: 5.995.000đGiảm 10.00% với mã GIAM10RAM: 8GB, Bộ nhớ: 256GBXem chi tiếtOppo Reno 11Giá: 6.495.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtOppo Reno 11Giá: 7.495.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtVivo X100Giá: 12.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtVivo X100Giá: 14.990.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtVivo X100Giá: 16.990.000đGiảm 10.00% với mã GIAM10RAM: 16GB, Bộ nhớ: 1TBXem chi tiếtSamsung Galaxy S25 Ultra 5GAGiá: 23.000.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 256GBXem chi tiếtSamsung Galaxy S25 Ultra 5GAGiá: 29.000.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 512GBXem chi tiếtSamsung Galaxy S25 Ultra 5GAGiá: 33.000.000đGiảm 10.00% với mã GIAM10RAM: 12GB, Bộ nhớ: 1TXem chi tiết', '2025-07-18 14:27:43', 'bot', 'success'),
(54, 7, 'chào', 'Lỗi khi gọi API Gemini: HTTP 404<div class=\"grid grid-cols-1 md:grid-cols-2 gap-4 p-4\">\r\n            <div class=\"bg-white rounded-lg shadow-md p-4 flex items-center\">\r\n                <img src=\"1743847922_Samsung_galaxy_s25_ultra_mau_den_titan.webp\" alt=\"Samsung Galaxy S25 Ultra 5GA\" class=\"w-24 h-24 object-cover rounded-lg mr-4\">\r\n                <div>\r\n                    <h4 class=\"text-lg font-semibold\">Samsung Galaxy S25 Ultra 5GA</h4>\r\n                    <p class=\"text-red-600 font-bold\">23.000.000 VNĐ</p>\r\n                </div>\r\n            </div>\r\n            <div class=\"bg-white rounded-lg shadow-md p-4 flex items-center\">\r\n                <img src=\"1743848078_ip16titan.webp\" alt=\"iPhone 16 Pro Max\" class=\"w-24 h-24 object-cover rounded-lg mr-4\">\r\n                <div>\r\n                    <h4 class=\"text-lg font-semibold\">iPhone 16 Pro Max</h4>\r\n                    <p class=\"text-red-600 font-bold\">29.799.000 VNĐ</p>\r\n                </div>\r\n            </div>\r\n            <div class=\"bg-white rounded-lg shadow-md p-4 flex items-center\">\r\n                <img src=\"1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\" class=\"w-24 h-24 object-cover rounded-lg mr-4\">\r\n                <div>\r\n                    <h4 class=\"text-lg font-semibold\">Redmi Note 14</h4>\r\n                    <p class=\"text-red-600 font-bold\">7.999.000 VNĐ</p>\r\n                </div>\r\n            </div></div>', '2025-07-18 18:16:44', 'bot', 'success'),
(56, 7, 'tư vấn điện thoại giá rẻ cho tôi', '<p>Bạn đang tìm điện thoại giá rẻ phải không?  Mình thấy Redmi Note 14 đang có giá 7.999.000 VNĐ, kèm theo 12 mã giảm giá nữa đấy!  Bạn tham khảo xem sao nhé.</p><div class=\"grid grid-cols-1 gap-4\"><div class=\'bg-white p-4 rounded-lg shadow-md\'><img src=\'1743992147_Xiaomi_redmi_note_14_mau_den.webp\' alt=\'Redmi Note 14\' class=\'w-full h-48 object-cover rounded-md\'><h3 class=\'text-lg font-semibold mt-2\'>Redmi Note 14</h3><p class=\'text-xl text-red-600 font-bold\'>7,999,000 VNĐ</p><p class=\'text-sm text-green-600\'>Mã giảm giá: GIAM10 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SALE500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE15 (15.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SAMSUNG500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: OPPOX8 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: XIAOMI20 (20.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE8PLUS (300000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_DONG (3.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_BAC (5.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_VANG (7.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_KIMCUONG (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_LUCBAO (15.0%)</p><a href=\'product.php?id=12\' class=\'text-blue-600 hover:underline\'>Xem chi tiết</a></div><div class=\'bg-white p-4 rounded-lg shadow-md\'><img src=\'1743992147_Xiaomi_redmi_note_14_mau_trang.webp\' alt=\'Redmi Note 14\' class=\'w-full h-48 object-cover rounded-md\'><h3 class=\'text-lg font-semibold mt-2\'>Redmi Note 14</h3><p class=\'text-xl text-red-600 font-bold\'>7,999,000 VNĐ</p><p class=\'text-sm text-green-600\'>Mã giảm giá: GIAM10 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SALE500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE15 (15.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SAMSUNG500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: OPPOX8 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: XIAOMI20 (20.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE8PLUS (300000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_DONG (3.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_BAC (5.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_VANG (7.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_KIMCUONG (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_LUCBAO (15.0%)</p><a href=\'product.php?id=12\' class=\'text-blue-600 hover:underline\'>Xem chi tiết</a></div><div class=\'bg-white p-4 rounded-lg shadow-md\'><img src=\'1743992147_Xiaomi_redmi_note_14_mau_xanh.webp\' alt=\'Redmi Note 14\' class=\'w-full h-48 object-cover rounded-md\'><h3 class=\'text-lg font-semibold mt-2\'>Redmi Note 14</h3><p class=\'text-xl text-red-600 font-bold\'>7,999,000 VNĐ</p><p class=\'text-sm text-green-600\'>Mã giảm giá: GIAM10 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SALE500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE15 (15.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SAMSUNG500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: OPPOX8 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: XIAOMI20 (20.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE8PLUS (300000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_DONG (3.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_BAC (5.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_VANG (7.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_KIMCUONG (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: VIP_LUCBAO (15.0%)</p><a href=\'product.php?id=12\' class=\'text-blue-600 hover:underline\'>Xem chi tiết</a></div></div>', '2025-07-19 16:14:04', 'bot', 'success'),
(57, 7, 'tư vấn điện thoại giá rẻ cho tôi', '<p>Bạn muốn điện thoại giá rẻ?  Mình thấy Vivo V30 (5.495.000đ) và Realme 12 Pro (5.990.000đ) đang có nhiều mã giảm giá đấy, bạn tham khảo xem sao nhé!</p><div class=\"grid grid-cols-1 gap-4\"><div class=\'bg-white p-4 rounded-lg shadow-md\'><img src=\'1745158164_v30den.webp\' alt=\'Vivo V30\' class=\'w-full h-48 object-cover rounded-md\'><h3 class=\'text-lg font-semibold mt-2\'>Vivo V30</h3><p class=\'text-xl text-red-600 font-bold\'>5,495,000 VNĐ</p><p class=\'text-sm text-green-600\'>Mã giảm giá: GIAM10 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SALE500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE15 (15.0%)</p><a href=\'product.php?id=18\' class=\'text-blue-600 hover:underline\'>Xem chi tiết</a></div><div class=\'bg-white p-4 rounded-lg shadow-md\'><img src=\'1745161338_realme127mau.webp\' alt=\'Realme 12 Pro\' class=\'w-full h-48 object-cover rounded-md\'><h3 class=\'text-lg font-semibold mt-2\'>Realme 12 Pro</h3><p class=\'text-xl text-red-600 font-bold\'>5,990,000 VNĐ</p><p class=\'text-sm text-green-600\'>Mã giảm giá: GIAM10 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SALE500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE15 (15.0%)</p><a href=\'product.php?id=28\' class=\'text-blue-600 hover:underline\'>Xem chi tiết</a></div></div>', '2025-07-20 14:09:51', 'bot', 'success'),
(58, 7, 'tư vấn điện thoại giá rẻ cho tôi', '<p>Bạn muốn điện thoại giá rẻ?  Mình thấy Vivo V30 (5.495.000đ) và Realme 12 Pro (5.990.000đ) đang có nhiều mã giảm giá đấy, bạn tham khảo xem sao nhé!</p><div class=\"grid grid-cols-1 gap-4\"><div class=\'bg-white p-4 rounded-lg shadow-md\'><img src=\'1745158164_v30den.webp\' alt=\'Vivo V30\' class=\'w-full h-48 object-cover rounded-md\'><h3 class=\'text-lg font-semibold mt-2\'>Vivo V30</h3><p class=\'text-xl text-red-600 font-bold\'>5,495,000 VNĐ</p><p class=\'text-sm text-green-600\'>Mã giảm giá: GIAM10 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SALE500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE15 (15.0%)</p><a href=\'product.php?id=18\' class=\'text-blue-600 hover:underline\'>Xem chi tiết</a></div><div class=\'bg-white p-4 rounded-lg shadow-md\'><img src=\'1745161338_realme127mau.webp\' alt=\'Realme 12 Pro\' class=\'w-full h-48 object-cover rounded-md\'><h3 class=\'text-lg font-semibold mt-2\'>Realme 12 Pro</h3><p class=\'text-xl text-red-600 font-bold\'>5,990,000 VNĐ</p><p class=\'text-sm text-green-600\'>Mã giảm giá: GIAM10 (10.0%)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: SALE500K (500000.0 VNĐ)</p><p class=\'text-sm text-green-600\'>Mã giảm giá: IPHONE15 (15.0%)</p><a href=\'product.php?id=28\' class=\'text-blue-600 hover:underline\'>Xem chi tiết</a></div></div>', '2025-07-20 14:10:11', 'bot', 'success'),
(59, 7, 'tư vấn điện thoại giá rẻ cho tôi', '<div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=12\"><img src=\"1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=19\"><img src=\"1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=21\"><img src=\"1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div></div>', '2025-07-20 14:54:10', 'bot', 'success'),
(60, 7, 'tư vấn điện thoại giá rẻ cho tôi', '<div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=12\"><img src=\"/uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=21\"><img src=\"/uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div></div>', '2025-07-20 15:00:58', 'bot', 'success'),
(61, 7, 'tư vấn điện thoại giá rẻ cho tôi', '<div class=\"llm-response\">Lỗi khi gọi Gemini API: Client error: `POST https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=AIzaSyAvXWE6BtTv_4pLi3cDsiU-0FifYpgiwrs` resulted in a `404 Not Found` response:\n{\n  &quot;error&quot;: {\n    &quot;code&quot;: 404,\n    &quot;message&quot;: &quot;models/gemini-pro is not found for API version v1beta, or is not support (truncated...)\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu của bạn:</div><div class=\"product-list\"></div>', '2025-07-20 15:15:47', 'bot', 'success'),
(62, 7, 'tư vấn điện thoại giá rẻ cho tôi', 'Không tìm thấy sản phẩm nào dưới 5 triệu phù hợp với yêu cầu.', '2025-07-20 15:21:08', 'bot', 'success'),
(63, 7, 'điện thoại chơi game tốt', 'Không tìm thấy sản phẩm nào dưới 5 triệu phù hợp với yêu cầu.', '2025-07-20 15:21:30', 'bot', 'success'),
(64, 7, 'điện thoại chơi game tốt', '<div class=\"llm-response\">Chào bạn!  Mình thấy bạn muốn tìm điện thoại chơi game giá rẻ phải không ạ?  Trong tầm dưới 8 triệu, mình thấy Realme GT 6 khá ổn với chip Snapdragon 8s Gen 3 mạnh mẽ, màn hình 120Hz mượt mà và pin 5500mAh trâu.  Nếu cần thêm lựa chọn nữa thì Redmi Note 14 cũng đáng cân nhắc nhé!\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=21\"><img src=\"/uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=12\"><img src=\"/uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=19\"><img src=\"/uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-20 15:25:35', 'bot', 'success');
INSERT INTO `tbl_chatbot` (`id`, `user_id`, `user_message`, `bot_reply`, `created_at`, `sender_type`, `status`) VALUES
(65, 7, 'giá cao hơn', 'Dạ vâng, anh/chị muốn xem thêm những lựa chọn nào có giá cao hơn ạ?  Em sẵn sàng tư vấn thêm cho anh/chị!\n', '2025-07-20 15:25:51', 'bot', 'success'),
(66, 7, 'điện thoại chụp ảnh đẹp', '<div class=\"llm-response\">Chào bạn!  Bạn muốn tìm điện thoại chụp ảnh đẹp mà giá cả phải chăng đúng không ạ?  Nếu vậy thì em thấy Realme GT 6 và Redmi Note 14 rất đáng cân nhắc đấy ạ!  Cả hai đều có camera chụp ảnh khá tốt, giá dưới 8 triệu mà hiệu năng chơi game cũng ổn nữa.  Bạn muốn nghe em tư vấn kỹ hơn về hai máy này không ạ?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=21\"><img src=\"/uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=12\"><img src=\"/uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=19\"><img src=\"/uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-20 15:26:58', 'bot', 'success'),
(67, 7, 'điện thoại iphone giá rẻ', '<div class=\"llm-response\">Chào bạn!  Bạn muốn tìm iPhone giá rẻ để chơi game phải không ạ?  Vậy thì iPhone 8 Plus với giá 7.990.000 VNĐ là lựa chọn hợp lý nhất đấy.  Chip A11 Bionic vẫn chơi game mượt mà, màn hình cũng ổn, lại có bảo hành chính hãng nữa.\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=14\"><img src=\"/uploads/1743992610_iphone_8_plus_gold.webp\" alt=\"iPhone 8 Plus\"><div>iPhone 8 Plus<br>7.990.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=15\"><img src=\"/uploads/1744873191_14_PRO_MAX_DEN.webp\" alt=\"iPhone 14 Pro\"><div>iPhone 14 Pro<br>20.990.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=11\"><img src=\"/uploads/1743848078_ip16titan.webp\" alt=\"iPhone 16 Pro Max\"><div>iPhone 16 Pro Max<br>29.799.000 VNĐ</div></a></div></div>', '2025-07-20 15:27:41', 'bot', 'success'),
(68, 7, 'điện thoại samsung', '<div class=\"llm-response\">Dạ chào anh/chị! Anh/chị muốn tìm điện thoại Samsung giá rẻ phải không ạ?  Em thấy Samsung Galaxy A55 rất phù hợp, giá chỉ 7.990.000 VNĐ thôi ạ.  Chip Exynos 1480 chơi game mượt, màn hình 120Hz, pin lại trâu nữa.  Anh/chị có muốn tìm hiểu thêm không ạ?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=23\"><img src=\"uploads/1745160639_a55den.webp\" alt=\"Samsung Galaxy A55\"><div>Samsung Galaxy A55<br>7.990.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=3\"><img src=\"uploads/1743847922_Samsung_galaxy_s25_ultra_mau_den_titan.webp\" alt=\"Samsung Galaxy S25 Ultra 5GA\"><div>Samsung Galaxy S25 Ultra 5GA<br>23.000.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=16\"><img src=\"uploads/1745157942_s24vang.webp\" alt=\"Samsung Galaxy S24 Ultra\"><div>Samsung Galaxy S24 Ultra<br>31.900.000 VNĐ</div></a></div></div>', '2025-07-20 15:28:32', 'bot', 'success'),
(69, 7, 'Điện thoại nào chơi game tốt dưới 10 triệu', 'Chào bạn!  Với tầm giá dưới 10 triệu, mình thấy Realme GT 6 và Redmi Note 14 chơi game khá ổn đó ạ.  Bạn muốn mình tư vấn kỹ hơn không?\n', '2025-07-20 15:29:53', 'bot', 'success'),
(70, 7, 'có', 'Dạ được ạ!  Realme GT 6 có chip mạnh, màn hình mượt, còn Redmi Note 14 thì pin trâu hơn. Bạn thích kiểu nào mạnh mẽ hay bền bỉ hơn ạ?\n', '2025-07-20 15:31:18', 'bot', 'success'),
(71, 7, 'Chơi PUBG/Liên Quân có mượt không?', 'Chào bạn!  Cả hai máy đều chơi PUBG/Liên Quân mượt mà nha. Realme GT 6 mạnh hơn, nhưng Redmi Note 14 lại có pin trâu hơn, chơi được lâu hơn đó ạ.  Bạn thích cái nào hơn?\n', '2025-07-20 15:31:39', 'bot', 'success'),
(72, 7, 'cho tôi xin mã giảm giá', '<div class=\"promotion-list\"><div class=\"promotion-item\">Mã: GIAM10 - Giảm: 10% - Hiệu lực: 06/04/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"GIAM10\">Copy</span></div><div class=\"promotion-item\">Mã: SALE500K - Giảm: 500.000 VNĐ - Hiệu lực: 06/04/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"SALE500K\">Copy</span></div><div class=\"promotion-item\">Mã: IPHONE15 - Giảm: 15% - Hiệu lực: 12/04/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"IPHONE15\">Copy</span></div><div class=\"promotion-item\">Mã: SAMSUNG500K - Giảm: 500.000 VNĐ - Hiệu lực: 12/04/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"SAMSUNG500K\">Copy</span></div><div class=\"promotion-item\">Mã: OPPOX8 - Giảm: 10% - Hiệu lực: 12/04/2025 đến 30/11/2025 <span class=\"copy-btn\" data-code=\"OPPOX8\">Copy</span></div><div class=\"promotion-item\">Mã: XIAOMI20 - Giảm: 20% - Hiệu lực: 12/04/2025 đến 31/10/2025 <span class=\"copy-btn\" data-code=\"XIAOMI20\">Copy</span></div><div class=\"promotion-item\">Mã: IPHONE8PLUS - Giảm: 300.000 VNĐ - Hiệu lực: 12/04/2025 đến 30/09/2025 <span class=\"copy-btn\" data-code=\"IPHONE8PLUS\">Copy</span></div><div class=\"promotion-item\">Mã: VIP_DONG - Giảm: 3% - Hiệu lực: 15/05/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"VIP_DONG\">Copy</span></div><div class=\"promotion-item\">Mã: VIP_BAC - Giảm: 5% - Hiệu lực: 15/05/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"VIP_BAC\">Copy</span></div><div class=\"promotion-item\">Mã: VIP_VANG - Giảm: 7% - Hiệu lực: 15/05/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"VIP_VANG\">Copy</span></div><div class=\"promotion-item\">Mã: VIP_KIMCUONG - Giảm: 10% - Hiệu lực: 15/05/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"VIP_KIMCUONG\">Copy</span></div><div class=\"promotion-item\">Mã: VIP_LUCBAO - Giảm: 15% - Hiệu lực: 15/05/2025 đến 31/12/2025 <span class=\"copy-btn\" data-code=\"VIP_LUCBAO\">Copy</span></div></div>', '2025-07-20 15:31:54', 'bot', 'success'),
(73, 7, 'đang có đợt flashsale nào không', 'Hiện tại không có sản phẩm flashsale nào.', '2025-07-20 15:32:45', 'bot', 'success'),
(74, 7, 'mã giảm giá', '<style>\r\n                .promotion-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-family: Arial, sans-serif; }\r\n                .promotion-table th, .promotion-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }\r\n                .promotion-table th { background-color: #f2f2f2; font-weight: bold; }\r\n                .promotion-table td { background-color: #fff; }\r\n                .copy-btn { \r\n                    background-color: #4CAF50; \r\n                    color: white; \r\n                    padding: 5px 10px; \r\n                    border-radius: 4px; \r\n                    cursor: pointer; \r\n                    text-align: center; \r\n                    display: inline-block; \r\n                }\r\n                .copy-btn:hover { background-color: #45a049; }\r\n                .consultation-text { margin-bottom: 15px; font-weight: bold; color: #333; }\r\n            </style><div class=\"consultation-text\">Danh sách mã giảm giá hiện có:</div><table class=\"promotion-table\"><tr><th>Mã giảm giá</th><th>Giảm giá</th><th>Hiệu lực</th><th>Hành động</th></tr><tr><td>GIAM10</td><td>10%</td><td>06/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"GIAM10\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>SALE500K</td><td>500.000 VNĐ</td><td>06/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"SALE500K\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>IPHONE15</td><td>15%</td><td>12/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"IPHONE15\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>SAMSUNG500K</td><td>500.000 VNĐ</td><td>12/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"SAMSUNG500K\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>OPPOX8</td><td>10%</td><td>12/04/2025 - 30/11/2025</td><td><span class=\"copy-btn\" data-code=\"OPPOX8\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>XIAOMI20</td><td>20%</td><td>12/04/2025 - 31/10/2025</td><td><span class=\"copy-btn\" data-code=\"XIAOMI20\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>IPHONE8PLUS</td><td>300.000 VNĐ</td><td>12/04/2025 - 30/09/2025</td><td><span class=\"copy-btn\" data-code=\"IPHONE8PLUS\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>VIP_DONG</td><td>3%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_DONG\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>VIP_BAC</td><td>5%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_BAC\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>VIP_VANG</td><td>7%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_VANG\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>VIP_KIMCUONG</td><td>10%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_KIMCUONG\" onclick=\"copyCode(this)\">Copy</span></td></tr><tr><td>VIP_LUCBAO</td><td>15%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_LUCBAO\" onclick=\"copyCode(this)\">Copy</span></td></tr></table>', '2025-07-20 16:19:44', 'bot', 'success'),
(75, 7, 'điện thoại chơi liên quân giá rẻ', '<div class=\"llm-response\">Chào bạn!  Dạ, nếu muốn điện thoại chơi Liên Quân giá rẻ mà ngon thì em thấy Redmi Note 14 rất ổn đó ạ.  Màn hình mượt, chip mạnh, pin trâu lại chỉ có 7.999.000đ thôi.  Bạn thấy sao ạ?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=12\"><img src=\"/Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=19\"><img src=\"/Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=27\"><img src=\"/Uploads/1745160156_13txanhla.webp\" alt=\"Xiaomi 13T Pro\"><div>Xiaomi 13T Pro<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-20 16:52:57', 'bot', 'success'),
(76, 7, 'điện thoại chụp ảnh đẹp', '<div class=\"llm-response\">Chào bạn!  Nếu muốn điện thoại chụp ảnh đẹp mà giá dưới 8 triệu thì mình thấy Realme GT 6 hoặc Redmi Note 14 rất đáng cân nhắc nha. Cả hai đều có camera chất lượng, màn hình mượt mà và pin trâu nữa đó! Bạn thích mẫu nào hơn ạ?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=21\"><img src=\"Uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=12\"><img src=\"Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=19\"><img src=\"Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-20 16:54:05', 'bot', 'success'),
(77, 7, 'điện thoại chụp ảnh đẹp', '<div class=\"llm-response\">Chào bạn!  Bạn muốn điện thoại chụp ảnh đẹp và giá rẻ đúng không ạ?  Em thấy Realme GT 6 và Redmi Note 14 rất phù hợp với ngân sách dưới 8 triệu của bạn đó.  Cả hai đều có camera tốt, màn hình mượt và pin trâu, lại chơi game cũng ngon nữa!  Bạn muốn xem chi tiết hơn về máy nào không ạ?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a href=\"index.php?product_id=21\"><img src=\"Uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=12\"><img src=\"Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a href=\"index.php?product_id=19\"><img src=\"Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-20 17:04:50', 'bot', 'success'),
(78, 7, 'điện thoại chơi tốt zingspeed', '<div class=\"llm-response\">Chào bạn!  Muốn chơi ZingSpeed mượt mà mà giá lại phải chăng thì mình thấy Realme GT 6 hoặc Redmi Note 14 rất hợp lý đó ạ. Cả hai đều có chip mạnh, màn hình mượt, pin trâu lại dưới 8 triệu nữa! Bạn thích con nào hơn thì mình tư vấn thêm chi tiết nha!\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=21\"><img src=\"Uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=12\"><img src=\"Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=19\"><img src=\"Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-20 17:06:38', 'bot', 'success'),
(79, 7, 'điện thoại chụp ảnh đẹp giá sao cũng được', '<div class=\"llm-response\">Chào bạn!  Mình thấy Realme GT 6 và Redmi Note 14 rất phù hợp nếu bạn muốn điện thoại chụp ảnh đẹp mà giá dưới 8 triệu nhé. Cả hai đều có camera tốt, màn hình mượt, pin trâu và chơi game cũng rất ổn nữa!  Bạn muốn nghe mình tư vấn kỹ hơn về máy nào không ạ?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=21\"><img src=\"Uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=12\"><img src=\"Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=19\"><img src=\"Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-20 17:07:55', 'bot', 'success'),
(80, 7, 'điện thoại iphone', '<div class=\"llm-response\">Chào bạn!  Bạn muốn mua iPhone giá rẻ phải không ạ?  Hiện tại thì iPhone không có dòng nào dưới 8 triệu cả.  Nếu bạn muốn điện thoại chơi game tốt, giá cả phải chăng thì mình gợi ý Realme GT 6 hoặc Redmi Note 14 nhé, cả hai đều có cấu hình rất ổn đấy ạ!\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=22\"><img src=\"Uploads/1745160367_ip15den.webp\" alt=\"iPhone 15\"><div>iPhone 15<br>19.990.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=15\"><img src=\"Uploads/1744873191_14_PRO_MAX_DEN.webp\" alt=\"iPhone 14 Pro\"><div>iPhone 14 Pro<br>20.990.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=11\"><img src=\"Uploads/1743848078_ip16titan.webp\" alt=\"iPhone 16 Pro Max\"><div>iPhone 16 Pro Max<br>29.799.000 VNĐ</div></a></div></div>', '2025-07-20 17:08:15', 'bot', 'success'),
(81, 7, 'tư vấn cho tôi điện thoại samsung giá rẻ chơi game tốt', '<div class=\"llm-response\">Chào bạn!  Bạn muốn tìm điện thoại Samsung giá rẻ chơi game tốt đúng không ạ?  Trong tầm giá dưới 8 triệu thì hơi khó tìm máy Samsung đáp ứng đủ tiêu chí đó bạn ơi.  Tuy nhiên, mình thấy Redmi Note 14  (chỉ 7.999.000 VNĐ)  có cấu hình rất ổn, chip mạnh, màn hình mượt và pin trâu, chơi game rất tốt luôn đấy ạ!  Bạn có muốn mình tư vấn thêm về em này không?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=12\"><img src=\"Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=19\"><img src=\"Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=3\"><img src=\"Uploads/1743847922_Samsung_galaxy_s25_ultra_mau_den_titan.webp\" alt=\"Samsung Galaxy S25 Ultra 5GA\"><div>Samsung Galaxy S25 Ultra 5GA<br>23.000.000 VNĐ</div></a></div></div>', '2025-07-20 17:08:50', 'bot', 'success'),
(82, 7, 'chào', 'Chào bạn! Có gì mình giúp được không ạ?\n', '2025-07-23 11:19:55', 'bot', 'success'),
(83, 7, 'tư vấn cho tôi điện thoại giá rẻ', '<div class=\"llm-response\">Chào bạn!  Bạn muốn tìm điện thoại giá rẻ chơi game tốt đúng không ạ?  Redmi Note 14  giá chỉ 7.999.000 VNĐ là lựa chọn rất đáng cân nhắc đấy ạ!  Màn hình mượt, chip mạnh, pin lại trâu nữa, chơi game cực đã luôn!  Bạn thấy sao ạ?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=12\"><img src=\"Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=19\"><img src=\"Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=27\"><img src=\"Uploads/1745160156_13txanhla.webp\" alt=\"Xiaomi 13T Pro\"><div>Xiaomi 13T Pro<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-23 11:20:49', 'bot', 'success'),
(84, 7, 'điện thoại chụp ảnh đẹp', '<div class=\"llm-response\">Chào bạn!  Bạn muốn tìm điện thoại giá rẻ chơi game tốt đúng không ạ?  Dưới 8 triệu thì mình thấy Realme GT 6 (6.495.000 VNĐ) và Redmi Note 14 (7.999.000 VNĐ) là hai lựa chọn rất đáng cân nhắc. Cả hai đều có màn hình mượt, chip mạnh và pin trâu, chơi game khá ổn đấy ạ!  Bạn muốn biết thêm thông tin về máy nào không?\n</div><div class=\"consultation-text\">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div><div class=\"product-list\"><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=21\"><img src=\"Uploads/1745160245_gt6xam.webp\" alt=\"Realme GT 6\"><div>Realme GT 6<br>6.495.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=12\"><img src=\"Uploads/1743992147_Xiaomi_redmi_note_14_mau_den.webp\" alt=\"Redmi Note 14\"><div>Redmi Note 14<br>7.999.000 VNĐ</div></a></div><div class=\"product-item\"><a class=\"sanpham\" href=\"index.php?product_id=19\"><img src=\"Uploads/1745158360_xiao14den.webp\" alt=\"Xiaomi 14\"><div>Xiaomi 14<br>14.990.000 VNĐ</div></a></div></div>', '2025-07-23 11:21:24', 'bot', 'success'),
(85, 7, 'chụp ảnh đẹp á', 'Dạ, muốn chụp ảnh đẹp thì tầm giá bao nhiêu ạ?  Em tư vấn cho dễ hơn.\n', '2025-07-23 11:21:57', 'bot', 'success'),
(86, 7, 'khoản 10 triệu', 'Dạ 10 triệu thì lựa chọn khá đa dạng ạ!  Anh/chị thích chụp ảnh kiểu nào ạ?  (Ví dụ: ảnh chân dung, phong cảnh, chụp thiếu sáng...)  Em sẽ tư vấn cho anh/chị những máy phù hợp nhất.\n', '2025-07-23 11:22:06', 'bot', 'success'),
(87, 7, 'chụp ảnh đẹp là được', 'Dạ được ạ!  Khoảng 10 triệu thì có nhiều máy chụp ảnh đẹp lắm.  Anh/chị thích kiểu ảnh nào để em tư vấn chính xác hơn nha?\n', '2025-07-23 11:22:17', 'bot', 'success'),
(88, 17, 'Liên hệ', 'Bạn có thể liên hệ với chúng tôi qua:<br>- Zalo: 0835512896<br>- Email: support@phoneshop.com<br>- Hoặc để lại tin nhắn tại đây, chúng tôi sẽ phản hồi sớm nhất!', '2025-07-23 14:39:31', 'bot', 'success'),
(89, 17, 'Flashsale', 'Hiện tại không có sản phẩm flashsale nào.', '2025-07-23 14:39:34', 'bot', 'success'),
(90, 17, 'Mã giảm giá', '<div class=\"consultation-text\">Danh sách mã giảm giá hiện có:</div><table class=\"promotion-table\"><thead><tr><th>Mã giảm giá</th><th>Giảm giá</th><th>Hiệu lực</th><th>Hành động</th></tr></thead><tbody><tr><td>GIAM10</td><td>10%</td><td>06/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"GIAM10\">Copy</span></td></tr><tr><td>SALE500K</td><td>500.000 VNĐ</td><td>06/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"SALE500K\">Copy</span></td></tr><tr><td>IPHONE15</td><td>15%</td><td>12/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"IPHONE15\">Copy</span></td></tr><tr><td>SAMSUNG500K</td><td>500.000 VNĐ</td><td>12/04/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"SAMSUNG500K\">Copy</span></td></tr><tr><td>OPPOX8</td><td>10%</td><td>12/04/2025 - 30/11/2025</td><td><span class=\"copy-btn\" data-code=\"OPPOX8\">Copy</span></td></tr><tr><td>XIAOMI20</td><td>20%</td><td>12/04/2025 - 31/10/2025</td><td><span class=\"copy-btn\" data-code=\"XIAOMI20\">Copy</span></td></tr><tr><td>IPHONE8PLUS</td><td>300.000 VNĐ</td><td>12/04/2025 - 30/09/2025</td><td><span class=\"copy-btn\" data-code=\"IPHONE8PLUS\">Copy</span></td></tr><tr><td>VIP_DONG</td><td>3%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_DONG\">Copy</span></td></tr><tr><td>VIP_BAC</td><td>5%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_BAC\">Copy</span></td></tr><tr><td>VIP_VANG</td><td>7%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_VANG\">Copy</span></td></tr><tr><td>VIP_KIMCUONG</td><td>10%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_KIMCUONG\">Copy</span></td></tr><tr><td>VIP_LUCBAO</td><td>15%</td><td>15/05/2025 - 31/12/2025</td><td><span class=\"copy-btn\" data-code=\"VIP_LUCBAO\">Copy</span></td></tr></tbody></table>', '2025-07-23 14:39:36', 'bot', 'success');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_favorites`
--

CREATE TABLE `tbl_favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_favorites`
--

INSERT INTO `tbl_favorites` (`favorite_id`, `user_id`, `product_id`, `added_at`) VALUES
(14, 16, 17, '2025-04-12 12:33:00'),
(16, 16, 16, '2025-04-12 12:59:22'),
(18, 16, 3, '2025-04-12 17:28:55'),
(48, 16, 14, '2025-04-15 11:41:18'),
(75, 6, 11, '2025-04-21 06:47:34'),
(78, 6, 3, '2025-05-08 07:26:58'),
(79, 16, 11, '2025-05-11 11:18:18'),
(80, 16, 26, '2025-05-11 11:59:28'),
(81, 6, 12, '2025-07-15 05:18:24'),
(82, 17, 17, '2025-07-24 05:17:12'),
(83, 17, 13, '2025-07-24 06:06:19'),
(86, 7, 12, '2025-07-25 06:57:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_flashsale`
--

CREATE TABLE `tbl_flashsale` (
  `id` int(11) NOT NULL,
  `flashsale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sold_quantity` int(11) DEFAULT 0,
  `discount_value` decimal(10,2) NOT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `max_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_flashsale`
--

INSERT INTO `tbl_flashsale` (`id`, `flashsale_id`, `product_id`, `sold_quantity`, `discount_value`, `discount_type`, `max_quantity`) VALUES
(55, 6, 27, 0, '10.00', 'percentage', 13),
(56, 6, 27, 0, '10.00', 'percentage', 13),
(57, 6, 23, 0, '10.00', 'percentage', 13),
(58, 6, 11, 0, '10.00', 'percentage', 13),
(59, 6, 3, 1, '10.00', 'percentage', 13),
(125, 8, 11, 0, '50.00', 'percentage', 33),
(126, 8, 3, 0, '50.00', 'percentage', 33),
(132, 10, 3, 0, '13.00', 'percentage', 3),
(133, 11, 3, 0, '13.00', 'percentage', 13),
(134, 11, 11, 0, '13.00', 'percentage', 13),
(135, 12, 3, 0, '13.00', 'percentage', 13),
(136, 12, 11, 0, '13.00', 'percentage', 13),
(137, 13, 3, 0, '13.00', 'percentage', 13),
(138, 14, 3, 0, '13.00', 'percentage', 13),
(139, 15, 3, 0, '13.00', 'percentage', 13);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_flashsales`
--

CREATE TABLE `tbl_flashsales` (
  `flashsale_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('active','expired') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_flashsales`
--

INSERT INTO `tbl_flashsales` (`flashsale_id`, `title`, `start_time`, `end_time`, `status`) VALUES
(6, 'Sale cực mạnh', '2025-04-22 16:47:00', '2025-04-22 22:50:00', 'expired'),
(8, 'sale nè 13', '2025-04-23 16:18:00', '2025-04-23 16:25:00', 'expired'),
(10, 'a', '2025-04-28 16:00:00', '2025-04-28 19:05:00', 'expired'),
(11, 'SAD', '2025-04-30 15:40:00', '2025-04-30 15:48:00', 'expired'),
(12, 'SAD', '2025-04-30 15:40:00', '2025-04-30 15:48:00', 'expired'),
(13, 'a', '2025-04-30 15:46:00', '2025-04-30 15:55:00', 'expired'),
(14, 'a', '2025-04-30 15:46:00', '2025-04-30 15:55:00', 'expired'),
(15, 'a', '2025-04-30 15:51:00', '2025-04-30 22:56:00', 'expired');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_notifications`
--

CREATE TABLE `tbl_notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('review_reply','order_status','flashsale','promotion','favorite_flashsale','cart_flashsale','other') NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `related_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Bẫy `tbl_notifications`
--
DELIMITER $$
CREATE TRIGGER `limit_notifications` AFTER INSERT ON `tbl_notifications` FOR EACH ROW BEGIN
    DELETE FROM `tbl_notifications`
    WHERE `user_id` = NEW.`user_id`
    AND `notification_id` NOT IN (
        SELECT `notification_id`
        FROM (
            SELECT `notification_id`
            FROM `tbl_notifications`
            WHERE `user_id` = NEW.`user_id`
            ORDER BY `created_at` DESC
            LIMIT 50
        ) AS temp
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_method` varchar(50) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','shipped','shipping','delivered','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `promo_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_orders`
--

INSERT INTO `tbl_orders` (`order_id`, `user_id`, `address_id`, `total_amount`, `payment_method`, `shipping_method`, `order_date`, `status`, `notes`, `promo_code`) VALUES
(6, 6, 1, '99999999.99', 'cod', 'standard', '2025-04-06 12:41:53', 'cancelled', NULL, NULL),
(7, 6, 1, '29799000.00', 'cod', 'standard', '2025-04-06 12:51:26', 'delivered', 'đẹp', NULL),
(8, 10, 3, '45979000.00', 'cod', 'standard', '2025-04-07 13:48:18', 'delivered', '', NULL),
(9, 8, 6, '40798000.00', 'cod', 'express', '2025-04-07 13:59:24', 'delivered', 'không nhanh trừ 50%\r\n', NULL),
(10, 7, 2, '18990000.00', 'cod', 'standard', '2025-04-17 15:08:52', 'delivered', '', NULL),
(11, 6, 7, '29799000.00', 'cod', 'standard', '2025-04-17 15:34:41', 'cancelled', '', NULL),
(12, 16, 9, '37789000.00', 'cod', 'standard', '2025-04-18 09:32:10', 'pending', '', NULL),
(13, 6, 1, '14279300.00', 'cod', 'standard', '2025-04-19 07:52:06', 'delivered', '', NULL),
(14, 6, 1, '9495000.00', 'cod', 'standard', '2025-04-20 12:04:07', 'delivered', '', NULL),
(15, 6, 1, '16995000.00', 'cod', 'standard', '2025-04-20 12:12:33', 'cancelled', '', NULL),
(16, 6, 7, '99999999.99', 'cod', 'standard', '2025-04-20 12:30:29', 'cancelled', '', NULL),
(17, 6, 7, '30591000.00', 'cod', 'standard', '2025-04-22 10:15:20', 'cancelled', '', NULL),
(18, 7, 2, '33990000.00', 'momo', 'standard', '2025-04-28 11:14:08', 'delivered', '', NULL),
(19, 6, 1, '7999000.00', 'cod', 'standard', '2025-05-08 07:17:10', '', '', NULL),
(20, 6, 7, '74350260.00', 'cod', 'standard', '2025-05-14 06:38:11', 'pending', '', 'sale500k'),
(21, 6, 1, '12541300.00', 'bank_card', 'standard', '2025-05-14 06:41:54', 'pending', '', 'sale500k'),
(28, 6, 1, '25425130.00', 'COD', 'Standard', '2025-05-14 07:52:18', 'pending', '', 'sale500k'),
(29, 6, 1, '12541300.00', 'COD', 'Standard', '2025-05-14 07:59:15', 'pending', '', 'sale500k'),
(30, 6, 1, '4741300.00', 'COD', 'Standard', '2025-05-14 08:06:06', 'delivered', 'giao nhanh lên', 'sale500k'),
(31, 6, 1, '37281300.00', 'COD', 'Standard', '2025-05-14 08:11:31', 'pending', '', 'sale500k'),
(32, 6, 1, '17500000.00', 'COD', 'Standard', '2025-05-14 08:12:59', 'cancelled', '', 'sale500k'),
(33, 7, 2, '25425130.00', 'COD', 'Standard', '2025-05-15 11:20:44', 'pending', '', 'sale500k');

--
-- Bẫy `tbl_orders`
--
DELIMITER $$
CREATE TRIGGER `update_flashsale_sold_quantity` AFTER UPDATE ON `tbl_orders` FOR EACH ROW BEGIN
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' THEN
        UPDATE tbl_flashsale fs
        JOIN tbl_flashsales f ON fs.flashsale_id = f.flashsale_id
        JOIN tbl_order_details od ON fs.product_id = od.product_id
        SET fs.sold_quantity = fs.sold_quantity + od.quantity
        WHERE od.order_id = NEW.order_id
        AND f.status = 'active'
        AND NEW.order_date BETWEEN f.start_time AND f.end_time;
    END IF;
    
    IF OLD.status = 'delivered' AND NEW.status != 'delivered' THEN
        UPDATE tbl_flashsale fs
        JOIN tbl_flashsales f ON fs.flashsale_id = f.flashsale_id
        JOIN tbl_order_details od ON fs.product_id = od.product_id
        SET fs.sold_quantity = GREATEST(fs.sold_quantity - od.quantity, 0)
        WHERE od.order_id = NEW.order_id
        AND f.status = 'active'
        AND NEW.order_date BETWEEN f.start_time AND f.end_time;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_sales_count_and_quantity` AFTER UPDATE ON `tbl_orders` FOR EACH ROW BEGIN
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' THEN
        -- Cập nhật sales_count và trừ quantity
        UPDATE `tbl_products` p
        INNER JOIN `tbl_order_details` od ON p.product_id = od.product_id
        SET 
            p.sales_count = p.sales_count + od.quantity,
            p.quantity = GREATEST(p.quantity - od.quantity, 0)
        WHERE od.order_id = NEW.order_id;
    END IF;
    
    IF OLD.status = 'delivered' AND NEW.status != 'delivered' THEN
        -- Hoàn lại quantity và giảm sales_count
        UPDATE `tbl_products` p
        INNER JOIN `tbl_order_details` od ON p.product_id = od.product_id
        SET 
            p.sales_count = GREATEST(p.sales_count - od.quantity, 0),
            p.quantity = p.quantity + od.quantity
        WHERE od.order_id = NEW.order_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_order_details`
--

CREATE TABLE `tbl_order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `storage` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_order_details`
--

INSERT INTO `tbl_order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `color_id`, `storage`) VALUES
(8, 6, 3, 1, '25399000.00', NULL, '512GB'),
(9, 6, 3, 1, '32999000.00', NULL, '1T'),
(10, 6, 11, 2, '41599000.00', NULL, '1T'),
(11, 7, 11, 1, '29799000.00', NULL, '256GB'),
(12, 8, 12, 1, '7999000.00', 10, '128GB'),
(13, 8, 13, 2, '18990000.00', 13, '256GB'),
(14, 9, 3, 2, '20399000.00', 1, '256GB'),
(15, 10, 13, 1, '18990000.00', 13, '256GB'),
(16, 11, 11, 1, '29799000.00', 6, '256GB'),
(17, 12, 14, 1, '7990000.00', 16, '64GB'),
(18, 12, 11, 1, '29799000.00', 6, '256GB'),
(19, 13, 3, 1, '14279300.00', 1, '256GB'),
(20, 14, 13, 1, '9495000.00', 13, '256GB'),
(21, 15, 3, 1, '16995000.00', 1, '256GB'),
(22, 16, 13, 13, '9495000.00', 13, '256GB'),
(23, 17, 3, 1, '30591000.00', 1, '256GB'),
(24, 18, 3, 1, '33990000.00', 1, '256GB'),
(25, 19, 12, 1, '7999000.00', 10, '128GB'),
(26, 20, 3, 1, '23000000.00', 2, '256GB'),
(27, 20, 11, 2, '25925130.00', 8, '256GB'),
(28, 21, 27, 1, '13041300.00', 58, '256GB'),
(29, 28, 11, 1, '25925130.00', 6, '256GB'),
(30, 29, 27, 1, '13041300.00', 58, '256GB'),
(31, 30, 28, 1, '5211300.00', 37, '128GB'),
(32, 31, 26, 1, '14781300.00', 31, '256GB'),
(33, 31, 3, 1, '23000000.00', 1, '256GB'),
(34, 32, 17, 1, '18000000.00', 47, '256GB'),
(35, 33, 11, 1, '25925130.00', 6, '256GB');

--
-- Bẫy `tbl_order_details`
--
DELIMITER $$
CREATE TRIGGER `decrease_flashsale_sold_quantity` AFTER DELETE ON `tbl_order_details` FOR EACH ROW BEGIN
    DECLARE flashsale_exists INT;

    -- Kiểm tra sản phẩm có trong Flash Sale đang active không
    SELECT COUNT(*) INTO flashsale_exists
    FROM `tbl_flashsale` fs
    JOIN `tbl_flashsales` f ON fs.flashsale_id = f.flashsale_id
    WHERE fs.product_id = OLD.product_id
    AND f.status = 'active'
    AND f.start_time <= NOW()
    AND f.end_time >= NOW();

    IF flashsale_exists > 0 THEN
        -- Giảm sold_quantity, đảm bảo không âm
        UPDATE `tbl_flashsale` fs
        JOIN `tbl_flashsales` f ON fs.flashsale_id = f.flashsale_id
        SET fs.sold_quantity = GREATEST(fs.sold_quantity - OLD.quantity, 0)
        WHERE fs.product_id = OLD.product_id
        AND f.status = 'active'
        AND f.start_time <= NOW()
        AND f.end_time >= NOW();
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `increase_flashsale_sold_quantity` AFTER INSERT ON `tbl_order_details` FOR EACH ROW BEGIN
    DECLARE flashsale_exists INT;

    -- Kiểm tra sản phẩm có trong Flash Sale đang active không
    SELECT COUNT(*) INTO flashsale_exists
    FROM `tbl_flashsale` fs
    JOIN `tbl_flashsales` f ON fs.flashsale_id = f.flashsale_id
    WHERE fs.product_id = NEW.product_id
    AND f.status = 'active'
    AND f.start_time <= NOW()
    AND f.end_time >= NOW();

    IF flashsale_exists > 0 THEN
        -- Tăng sold_quantity
        UPDATE `tbl_flashsale` fs
        JOIN `tbl_flashsales` f ON fs.flashsale_id = f.flashsale_id
        SET fs.sold_quantity = fs.sold_quantity + NEW.quantity
        WHERE fs.product_id = NEW.product_id
        AND f.status = 'active'
        AND f.start_time <= NOW()
        AND f.end_time >= NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_products`
--

CREATE TABLE `tbl_products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `sales_count` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  `specifications` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_products`
--

INSERT INTO `tbl_products` (`product_id`, `product_name`, `category_id`, `brand`, `discount_price`, `quantity`, `sales_count`, `description`, `created_at`, `status`, `specifications`) VALUES
(3, 'Samsung Galaxy S25 Ultra 5GA', NULL, NULL, NULL, 101, 4, 'Samsung Galaxy S25 Ultra 5G là điện thoại cao cấp chính hãng Samsung Việt Nam, mới 100%. Sản phẩm nổi bật với thiết kế titan sang trọng, màn hình Dynamic AMOLED 2X 6.9 inch 120Hz, camera 200MP sắc nét, chip Snapdragon 8 Elite cho hiệu năng chơi game mượt mà, pin trâu 5000mAh hỗ trợ sạc nhanh. Phù hợp cho game thủ, người yêu công nghệ và nhiếp ảnh chuyên nghiệp. Bộ sản phẩm gồm: hộp, máy, cáp, cây lấy sim, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Samsung.', '2025-03-25 06:16:50', 'active', 'Thông tin màn hình:\r\nDynamic AMOLED 2X, 6.9 inches, 1440 x 3120 pixels, 120Hz, HDR10+, 2.600 nits\r\nCPU:\r\nQualcomm Snapdragon 8 Elite (3nm)\r\nRAM:\r\n12GB\r\nCamera sau:\r\n200MP + 50MP + 10MP + 50MP\r\nCamera trước:\r\n12MP\r\nBộ nhớ trong:\r\n256GB\r\nPin:\r\n5000 mAh\r\nHệ điều hành:\r\nAndroid 15'),
(11, 'iPhone 16 Pro Max', 1, NULL, NULL, 110, 2, 'iPhone 16 Pro Max là smartphone cao cấp chính hãng Apple Việt Nam, mới 100%. Trang bị chip A18 Pro mạnh mẽ, màn hình Super Retina XDR 6.3 inch 120Hz, camera 48MP chụp ảnh đẹp, hỗ trợ nút Capture Button và tính năng AI tiên tiến. Lý tưởng cho chơi game mượt, giải trí đỉnh cao và sáng tạo nội dung. Pin trâu 3355mAh, thiết kế titan bền bỉ. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Apple.', '2025-03-26 02:41:59', 'active', 'Màn hình:\r\n6.3 inches, Super Retina XDR OLED, HDR10, Dolby Vision, 2.000 nits, tần số quét 120Hz\r\nCamera trước:\r\n12MP\r\nCamera sau:\r\n48MP + 48MP + 12MP\r\nChipset:\r\nApple A18 Pro\r\nRAM:\r\n8GB\r\nBộ nhớ trong:\r\n128GB\r\nPin:\r\n3.355 mAh\r\nHệ điều hành:\r\niOS 18'),
(12, 'Redmi Note 14', 6, 'Xiaomi', NULL, 109, 1, 'Redmi Note 14 là điện thoại tầm trung giá rẻ chính hãng Xiaomi Việt Nam, mới 100%. Sản phẩm sở hữu màn hình AMOLED 6.67 inch 120Hz, camera 108MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 cho hiệu năng chơi game mượt, pin trâu 5000mAh sạc nhanh 67W. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Xiaomi.', '2025-04-05 17:00:00', 'active', 'Màn hình: AMOLED 6.67 inches, Full HD+, 120Hz\nCPU: Qualcomm Snapdragon 7 Gen 1\nRAM: 8GB\nCamera sau: 108MP + 8MP + 2MP\nCamera trước: 16MP\nBộ nhớ trong: 128GB\nPin: 5000 mAh, sạc nhanh 67W\nHệ điều hành: Android 15, MIUI'),
(13, 'OPPO Find X8', 3, 'Oppo', NULL, 106, 4, 'OPPO Find X8 là điện thoại cao cấp chính hãng OPPO Việt Nam, mới 100%. Thiết kế mỏng nhẹ, màn hình LTPO OLED 6.59 inch 120Hz, camera 50MP Hasselblad chụp ảnh đẹp, chip MediaTek Dimensity 9400 cho hiệu năng chơi game đỉnh cao, pin trâu 5630mAh. Phù hợp cho người yêu nhiếp ảnh, công nghệ và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng OPPO.', '2025-04-06 12:00:00', 'active', 'Màn hình: 6.59 inches, LTPO OLED, FHD+, 120Hz\nChipset: MediaTek Dimensity 9400\nCamera sau: 50MP + 50MP + 50MP\nCamera trước: 32MP\nPin: 5630 mAh\nHệ điều hành: ColorOS 15 (Android 15)'),
(14, 'iPhone 8 Plus', 1, 'Apple', NULL, 110, 0, 'iPhone 8 Plus là smartphone giá rẻ chính hãng Apple Việt Nam, mới 100%. Thiết kế nhôm kính cao cấp, màn hình Retina HD 5.5 inch, camera kép 12MP chụp ảnh đẹp, chip A11 Bionic hỗ trợ chơi game mượt. Pin 2691mAh, hỗ trợ sạc không dây. Phù hợp cho người dùng phổ thông, học tập và giải trí cơ bản. Bộ sản phẩm gồm: hộp, máy, cáp, tai nghe, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Apple.', '2025-04-06 13:00:00', 'active', 'Màn hình: 5.5 inches, Retina HD, LED-backlit IPS LCD\nChipset: Apple A11 Bionic\nCamera sau: 12MP + 12MP\nCamera trước: 7MP\nPin: 2691 mAh\nHệ điều hành: iOS 11 (có thể nâng cấp)'),
(15, 'iPhone 14 Pro', 1, 'Apple', NULL, 110, 0, 'iPhone 14 Pro là smartphone cao cấp chính hãng Apple Việt Nam, mới 100%. Trang bị chip A16 Bionic mạnh mẽ, màn hình Super Retina XDR 6.1 inch 120Hz, camera 48MP chụp ảnh đẹp, thiết kế thép không gỉ sang trọng. Hỗ trợ chơi game mượt, giải trí đỉnh cao và làm việc hiệu quả. Pin trâu 3200mAh. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Apple.', '2025-04-11 21:39:00', 'active', 'Màn hình: 6.1 inches, Super Retina XDR OLED, 120Hz\nChipset: Apple A16 Bionic\nCamera sau: 48MP + 12MP + 12MP\nCamera trước: 12MP\nPin: 3200 mAh\nHệ điều hành: iOS 16'),
(16, 'Samsung Galaxy S24 Ultra', NULL, 'Samsung', NULL, 110, 0, 'Samsung Galaxy S24 Ultra là điện thoại cao cấp chính hãng Samsung Việt Nam, mới 100%. Thiết kế titan sang trọng, màn hình Dynamic AMOLED 2X 6.8 inch 120Hz, camera 200MP chụp ảnh đẹp, chip Snapdragon 8 Gen 3 cho hiệu năng chơi game mượt mà, pin trâu 5000mAh. Phù hợp cho game thủ, nhiếp ảnh gia và người yêu công nghệ. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Samsung.', '2025-04-11 21:39:00', 'active', 'Màn hình: 6.8 inches, Dynamic AMOLED 2X, 120Hz\nChipset: Qualcomm Snapdragon 8 Gen 3\nCamera sau: 200MP + 50MP + 10MP + 12MP\nCamera trước: 12MP\nPin: 5000 mAh\nHệ điều hành: Android 14'),
(17, 'Oppo Find N4 Flip', 3, 'Oppo', NULL, 110, 0, 'Oppo Find N4 Flip là điện thoại gập cao cấp chính hãng OPPO Việt Nam, mới 100%. Thiết kế thời thượng, màn hình chính AMOLED 6.8 inch 120Hz, camera 50MP chụp ảnh đẹp, chip MediaTek Dimensity 6020 hỗ trợ chơi game mượt. Pin 4300mAh, phù hợp cho người yêu công nghệ và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng OPPO.', '2025-04-11 21:39:00', 'active', 'Màn hình chính: 6.8 inches, AMOLED, 120Hz\nMàn hình phụ: 3.2 inches, AMOLED\nChipset: MediaTek Dimensity 6020\nCamera sau: 50MP + 8MP\nCamera trước: 32MP\nPin: 4300 mAh\nHệ điều hành: ColorOS 14 (Android 14)'),
(18, 'Vivo V30', 4, 'Vivo', NULL, 110, 0, 'Vivo V30 là smartphone tầm trung giá rẻ chính hãng Vivo Việt Nam, mới 100%. Màn hình AMOLED 6.44 inch 120Hz, camera 64MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 cho hiệu năng chơi game mượt, pin trâu 4600mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Vivo.', '2025-04-11 21:39:00', 'active', 'Màn hình: 6.44 inches, AMOLED, 120Hz\nChipset: Qualcomm Snapdragon 7 Gen 1\nCamera sau: 64MP + 8MP + 2MP\nCamera trước: 50MP\nPin: 4600 mAh\nHệ điều hành: Funtouch OS 14 (Android 14)'),
(19, 'Xiaomi 14', 6, 'Xiaomi', NULL, 110, 0, 'Xiaomi 14 là điện thoại cao cấp chính hãng Xiaomi Việt Nam, mới 100%. Thiết kế sang trọng, màn hình LTPO AMOLED 6.36 inch 120Hz, camera Leica 50MP chụp ảnh đẹp, chip Snapdragon 8 Gen 3 cho hiệu năng chơi game đỉnh cao, pin trâu 4610mAh. Phù hợp cho game thủ và người yêu nhiếp ảnh. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Xiaomi.', '2025-04-11 21:39:00', 'active', 'Màn hình: 6.36 inches, LTPO AMOLED, 120Hz\nChipset: Qualcomm Snapdragon 8 Gen 3\nCamera sau: 50MP + 50MP + 50MP\nCamera trước: 32MP\nPin: 4610 mAh\nHệ điều hành: HyperOS (Android 14)'),
(20, 'Huawei Mate 60 Pro', 5, 'Huawei', NULL, 110, 0, 'Huawei Mate 60 Pro là smartphone cao cấp chính hãng Huawei Việt Nam, mới 100%. Thiết kế sang trọng, màn hình OLED 6.82 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Kirin 9010 hỗ trợ chơi game mượt, pin trâu 5000mAh, hỗ trợ kết nối vệ tinh. Phù hợp cho người yêu công nghệ và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Huawei.', '2025-04-11 21:39:00', 'active', 'Màn hình: 6.82 inches, OLED, 120Hz\nChipset: Kirin 9010\nCamera sau: 50MP + 48MP + 12MP\nCamera trước: 13MP\nPin: 5000 mAh\nHệ điều hành: HarmonyOS 4.0'),
(21, 'Realme GT 6', 6, 'Realme', NULL, 110, 0, 'Realme GT 6 là điện thoại tầm trung chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.78 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 8s Gen 3 cho hiệu năng chơi game mượt, pin trâu 5500mAh. Phù hợp cho game thủ, giải trí và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.', '2025-04-11 21:39:00', 'active', 'Màn hình: 6.78 inches, AMOLED, 120Hz\nChipset: Qualcomm Snapdragon 8s Gen 3\nCamera sau: 50MP + 8MP + 2MP\nCamera trước: 32MP\nPin: 5500 mAh\nHệ điều hành: Realme UI 5.0 (Android 14)'),
(22, 'iPhone 15', 1, 'Apple', NULL, 110, 0, 'iPhone 15 là smartphone cao cấp chính hãng Apple Việt Nam, mới 100%. Thiết kế nhôm kính cao cấp, màn hình Super Retina XDR 6.1 inch, camera 48MP chụp ảnh đẹp, chip A16 Bionic hỗ trợ chơi game mượt và làm việc hiệu quả. Pin trâu 3349mAh. Phù hợp cho người yêu công nghệ và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Apple.', '2025-04-12 07:00:00', 'active', 'Màn hình: 6.1 inches, Super Retina XDR OLED, 60Hz\nChipset: Apple A16 Bionic\nCamera sau: 48MP + 12MP\nCamera trước: 12MP\nPin: 3349 mAh\nHệ điều hành: iOS 17'),
(23, 'Samsung Galaxy A55', NULL, 'Samsung', NULL, 110, 0, 'Samsung Galaxy A55 là điện thoại tầm trung giá rẻ chính hãng Samsung Việt Nam, mới 100%. Màn hình Super AMOLED 6.6 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Exynos 1480 cho hiệu năng chơi game mượt, pin trâu 5000mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Samsung.', '2025-04-12 07:00:00', 'active', 'Màn hình: 6.6 inches, Super AMOLED, 120Hz\nChipset: Exynos 1480\nCamera sau: 50MP + 12MP + 5MP\nCamera trước: 32MP\nPin: 5000 mAh\nHệ điều hành: Android 14'),
(24, 'Oppo Reno 11', 3, 'Oppo', NULL, 110, 0, 'Oppo Reno 11 là smartphone tầm trung chính hãng OPPO Việt Nam, mới 100%. Thiết kế thời thượng, màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip MediaTek Dimensity 7050 hỗ trợ chơi game mượt, pin trâu 4800mAh. Phù hợp cho người yêu nhiếp ảnh và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng OPPO.', '2025-04-12 07:00:00', 'active', 'Màn hình: 6.7 inches, AMOLED, 120Hz\nChipset: MediaTek Dimensity 7050\nCamera sau: 50MP + 32MP + 8MP\nCamera trước: 32MP\nPin: 4800 mAh\nHệ điều hành: ColorOS 14 (Android 14)'),
(25, 'Vivo X100', 4, 'Vivo', NULL, 110, 0, 'Vivo X100 là điện thoại cao cấp chính hãng Vivo Việt Nam, mới 100%. Thiết kế sang trọng, màn hình AMOLED 6.78 inch 120Hz, camera Zeiss 50MP chụp ảnh đẹp, chip MediaTek Dimensity 9300 cho hiệu năng chơi game đỉnh cao, pin trâu 5000mAh. Phù hợp cho game thủ và người yêu nhiếp ảnh. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Vivo.', '2025-04-12 07:00:00', 'active', 'Màn hình: 6.78 inches, AMOLED, 120Hz\nChipset: MediaTek Dimensity 9300\nCamera sau: 50MP + 50MP + 64MP\nCamera trước: 32MP\nPin: 5000 mAh\nHệ điều hành: Funtouch OS 14 (Android 14)'),
(26, 'Huawei P60 Pro', 5, 'Huawei', NULL, 110, 0, 'Huawei P60 Pro là smartphone cao cấp chính hãng Huawei Việt Nam, mới 100%. Thiết kế cao cấp, màn hình OLED 6.67 inch 120Hz, camera 48MP chụp ảnh đẹp, chip Snapdragon 8+ Gen 1 cho hiệu năng chơi game mượt, pin trâu 4815mAh. Phù hợp cho người yêu công nghệ và giải trí. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Huawei.', '2025-04-12 07:00:00', 'active', 'Màn hình: 6.67 inches, OLED, 120Hz\nChipset: Qualcomm Snapdragon 8+ Gen 1\nCamera sau: 48MP + 48MP + 13MP\nCamera trước: 13MP\nPin: 4815 mAh\nHệ điều hành: HarmonyOS 3.1'),
(27, 'Xiaomi 13T Pro', 6, 'Xiaomi', NULL, 110, 0, 'Xiaomi 13T Pro là điện thoại cao cấp chính hãng Xiaomi Việt Nam, mới 100%. Thiết kế cao cấp, màn hình AMOLED 6.67 inch 144Hz, camera Leica 50MP chụp ảnh đẹp, chip MediaTek Dimensity 9200+ cho hiệu năng chơi game đỉnh cao, pin trâu 5000mAh. Phù hợp cho game thủ và người yêu nhiếp ảnh. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Xiaomi.', '2025-04-12 07:00:00', 'active', 'Màn hình: 6.67 inches, AMOLED, 144Hz\nChipset: MediaTek Dimensity 9200+\nCamera sau: 50MP + 50MP + 12MP\nCamera trước: 20MP\nPin: 5000 mAh\nHệ điều hành: HyperOS (Android 14)'),
(28, 'Realme 12 Pro', 6, 'Realme', NULL, 109, 1, 'Realme 12 Pro là smartphone tầm trung giá rẻ chính hãng Realme Việt Nam, mới 100%. Màn hình AMOLED 6.7 inch 120Hz, camera 50MP chụp ảnh đẹp, chip Snapdragon 7 Gen 1 hỗ trợ chơi game mượt, pin trâu 5000mAh. Phù hợp cho giải trí, học tập và người dùng phổ thông. Bộ sản phẩm gồm: hộp, máy, cáp, sách hướng dẫn. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng Realme.', '2025-04-12 07:00:00', 'active', 'Màn hình: 6.7 inches, AMOLED, 120Hz\nChipset: Qualcomm Snapdragon 7 Gen 1\nCamera sau: 50MP + 32MP + 8MP\nCamera trước: 16MP\nPin: 5000 mAh\nHệ điều hành: Realme UI 5.0 (Android 14)');

--
-- Bẫy `tbl_products`
--
DELIMITER $$
CREATE TRIGGER `after_product_insert` AFTER INSERT ON `tbl_products` FOR EACH ROW BEGIN
    INSERT INTO `tbl_notifications` (`user_id`, `type`, `message`, `link`, `related_id`)
    SELECT user_id, 'new_product',
           CONCAT('Sản phẩm mới ', NEW.product_name, ' vừa được thêm vào cửa hàng!'),
           CONCAT('product.php?id=', NEW.product_id),
           NEW.product_id
    FROM tbl_users;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_product_colors`
--

CREATE TABLE `tbl_product_colors` (
  `color_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `color_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_product_colors`
--

INSERT INTO `tbl_product_colors` (`color_id`, `product_id`, `color_name`) VALUES
(1, 3, 'Đen Titan'),
(2, 3, 'Bạc Titan'),
(3, 3, 'Xám Titan'),
(4, 3, 'Xanh Titan'),
(5, 3, 'Màu Vàng'),
(6, 11, 'Titan'),
(7, 11, 'Titan Đen'),
(8, 11, 'Titan Trắng'),
(9, 11, 'Titan Vàng'),
(10, 12, 'Đen'),
(11, 12, 'Xanh'),
(12, 12, 'Trắng'),
(13, 13, 'Xanh'),
(14, 13, 'Đen'),
(15, 13, 'Trắng'),
(16, 14, 'Vàng'),
(17, 14, 'Trắng'),
(18, 14, 'Đỏ'),
(19, 22, 'Xanh Dương'),
(20, 22, 'Đen'),
(25, 24, 'Xanh Ngọc'),
(26, 24, 'Đen Ánh Kim'),
(28, 25, 'Đen Huyền Bí'),
(29, 25, 'Xanh Dương Đậm'),
(31, 26, 'Đen Ngọc'),
(32, 26, 'Xanh Lá Cây'),
(33, 26, 'Trắng Băng Giá'),
(37, 28, 'Xanh Dương Nhạt'),
(38, 28, 'Đen Nhám'),
(39, 28, 'Trắng Sứ'),
(40, 15, 'Đen'),
(41, 15, 'Tím'),
(42, 15, 'Trắng'),
(43, 15, 'Vàng'),
(44, 16, 'Vàng'),
(45, 16, 'Xám'),
(46, 16, 'Đen'),
(47, 17, 'Vàng'),
(48, 17, 'Hồng'),
(49, 18, 'Đen'),
(50, 18, 'Xanh Lam'),
(51, 19, 'Đen'),
(52, 19, 'Hồng'),
(53, 19, 'Xám'),
(54, 19, 'Xanh'),
(55, 20, 'Đen'),
(56, 20, 'Tím '),
(57, 20, 'Trắng'),
(58, 27, 'Xanh Lá'),
(59, 27, 'Xanh Dương'),
(60, 27, 'Đen'),
(61, 21, 'Xám'),
(62, 21, 'Bạc'),
(63, 22, 'Hồng'),
(64, 22, 'Vàng'),
(65, 22, 'Xanh Lá'),
(66, 23, 'Đen'),
(67, 23, 'Tím'),
(68, 23, 'Trắng'),
(69, 26, 'Tím'),
(70, 24, 'Tím'),
(71, 25, 'Cam Xả');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_product_images`
--

CREATE TABLE `tbl_product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_product_images`
--

INSERT INTO `tbl_product_images` (`image_id`, `product_id`, `color_id`, `image_url`) VALUES
(1, 3, 2, '1743847922_Samsung_galaxy_s25_ultra_mau_den_titan.webp'),
(2, 3, 3, '1743847922_Samsung_galaxy_s25_ultra_mau_bac_titan.webp'),
(3, 3, 4, '1743847922_Samsung_galaxy_s25_ultra_mau_xam_titan.webp'),
(4, 3, 5, '1743847922_Samsung_galaxy_s25_ultra_mau_xanh_titan.webp'),
(5, 3, 1, '1743847941_Samsung_galaxy_s25_ultra_mau_den_titan.webp'),
(6, 11, 6, '1743848078_ip16titan.webp'),
(7, 11, 7, '1743848078_ip16titanden.webp'),
(8, 11, 8, '1743848078_ip16titantrang.webp'),
(9, 11, 9, '1743848078_ip16titanvang.webp'),
(10, 12, 10, '1743992147_Xiaomi_redmi_note_14_mau_den.webp'),
(11, 12, 11, '1743992147_Xiaomi_redmi_note_14_mau_xanh.webp'),
(12, 12, 12, '1743992147_Xiaomi_redmi_note_14_mau_trang.webp'),
(13, 13, 13, '1743992402_oppo-xanh.webp'),
(14, 13, 14, '1743992402_oppo_find_x8_mau_den.webp'),
(15, 13, 15, '1743992402_oppo_find_x8_pro_mau_trang.webp'),
(16, 14, 16, '1743992610_iphone_8_plus_gold.webp'),
(17, 14, 17, '1743992610_iphone_8_trang_plus.webp'),
(18, 14, 18, '1743992610_iphone8_red.webp'),
(19, 15, 40, '1744873191_14_PRO_MAX_DEN.webp'),
(20, 15, 41, '1744873191_iphone_14_pro_tim_2.webp'),
(21, 15, 42, '1744873191_iphone_14_pro_trang_2.webp'),
(22, 15, 43, '1744873191_iphone_14_pro_vang_2.webp'),
(23, 16, 44, '1745157942_s24vang.webp'),
(24, 16, 45, '1745157942_s24xam.webp'),
(25, 16, 46, '1745157942_s24den.webp'),
(26, 17, 47, '1745158083_n4vang.webp'),
(27, 17, 48, '1745158083_n4hong.png'),
(28, 18, 49, '1745158164_v30den.webp'),
(29, 18, 50, '1745158164_v30xanhlam.webp'),
(30, 19, 51, '1745158360_xiao14den.webp'),
(31, 19, 52, '1745158360_xiao14hong.webp'),
(32, 19, 53, '1745158360_xiao14xam.webp'),
(33, 19, 54, '1745158360_xiao14xanh.webp'),
(34, 20, 55, '1745158451_mate60den.webp'),
(35, 20, 56, '1745158451_mate60tim.webp'),
(36, 20, 57, '1745158451_mate60trang.webp'),
(37, 27, 58, '1745160156_13txanhla.webp'),
(38, 27, 59, '1745160156_13txanh.webp'),
(39, 27, 60, '1745160156_13tden.webp'),
(40, 21, 61, '1745160245_gt6xam.webp'),
(41, 21, 62, '1745160245_gt6bac.webp'),
(42, 22, 20, '1745160367_ip15den.webp'),
(43, 22, 63, '1745160367_ip15hong.webp'),
(44, 22, 64, '1745160367_ip15vang.webp'),
(45, 22, 19, '1745160367_ip15xanhduong.webp'),
(46, 22, 65, '1745160367_ip15xanhla.webp'),
(47, 23, 66, '1745160639_a55den.webp'),
(48, 23, 67, '1745160639_a55tim.webp'),
(49, 23, 68, '1745160639_a55trang.webp'),
(50, 28, 39, '1745161338_realme127mau.webp'),
(51, 28, 38, '1745161338_realme127mau2.webp'),
(52, 28, 37, '1745161338_realme12den.webp'),
(53, 26, 31, '1745161441_p90den.webp'),
(54, 26, 32, '1745161441_p90xanh.webp'),
(55, 26, 33, '1745161441_p90trang.webp'),
(56, 26, 69, '1745161441_p90hong.webp'),
(57, 24, 25, '1745161518_reno11xanh.webp'),
(58, 24, 26, '1745161518_reno11xanhden.webp'),
(59, 24, 70, '1745161518_r11tim.webp'),
(60, 25, 28, '1745161588_x100den.webp'),
(61, 25, 29, '1745161588_x100xanhduong.webp'),
(62, 25, 71, '1745161588_xiao14xanh.webp');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_product_specifications`
--

CREATE TABLE `tbl_product_specifications` (
  `spec_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `screen_size` varchar(10) DEFAULT NULL,
  `refresh_rate` varchar(10) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `chipset` varchar(100) DEFAULT NULL,
  `rear_camera` varchar(100) DEFAULT NULL,
  `front_camera` varchar(50) DEFAULT NULL,
  `battery` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_product_specifications`
--

INSERT INTO `tbl_product_specifications` (`spec_id`, `product_id`, `screen_size`, `refresh_rate`, `os`, `chipset`, `rear_camera`, `front_camera`, `battery`) VALUES
(2, 11, '6.3', '120Hz', 'iOS 18', 'Apple A18 Pro', '48MP + 48MP + 12MP', '12MP', '3355 mAh'),
(4, 12, '6.67', '120Hz', 'Android 15', 'Qualcomm Snapdragon 7 Gen 1', '108MP + 8MP + 2MP', '16MP', '5000 mAh'),
(6, 13, '6.59', '120Hz', 'ColorOS 15 (Android 15)', 'MediaTek Dimensity 9400', '50MP + 50MP + 50MP', '32MP', '5630 mAh'),
(8, 14, '5.5', '60Hz', 'iOS 11 (có thể nâng cấp)', 'Apple A11 Bionic', '12MP + 12MP', '7MP', '2691 mAh'),
(23, 15, '6.1', '120Hz', 'iOS 16', 'Apple A16 Bionic', '48MP + 12MP + 12MP', '12MP', '3200 mAh'),
(25, 16, '6.8', '120Hz', 'Android 14', 'Qualcomm Snapdragon 8 Gen 3', '200MP + 50MP + 10MP + 12MP', '12MP', '5000 mAh'),
(26, 17, '6.8', '120Hz', 'ColorOS 14 (Android 14)', 'MediaTek Dimensity 6020', '50MP + 8MP', '32MP', '4300 mAh'),
(27, 18, '6.44', '120Hz', 'Funtouch OS 14 (Android 14)', 'Qualcomm Snapdragon 7 Gen 1', '64MP + 8MP + 2MP', '50MP', '4600 mAh'),
(28, 19, '6.36', '120Hz', 'HyperOS (Android 14)', 'Qualcomm Snapdragon 8 Gen 3', '50MP + 50MP + 50MP', '32MP', '4610 mAh'),
(29, 20, '6.82', '120Hz', 'HarmonyOS 4.0', 'Kirin 9010', '50MP + 48MP + 12MP', '13MP', '5000 mAh'),
(30, 27, '6.67', '144Hz', 'HyperOS (Android 14)', 'MediaTek Dimensity 9200+', '50MP + 50MP + 12MP', '20MP', '5000 mAh'),
(31, 21, '6.78', '120Hz', 'Realme UI 5.0 (Android 14)', 'Qualcomm Snapdragon 8s Gen 3', '50MP + 8MP + 2MP', '32MP', '5500 mAh'),
(32, 22, '6.1', '60Hz', 'iOS 17', 'Apple A16 Bionic', '48MP + 12MP', '12MP', '3349 mAh'),
(33, 23, '6.6', '120Hz', 'Android 14', 'Exynos 1480', '50MP + 12MP + 5MP', '32MP', '5000 mAh'),
(34, 28, '6.7', '120Hz', 'Realme UI 5.0 (Android 14)', 'Qualcomm Snapdragon 7 Gen 1', '50MP + 32MP + 8MP', '16MP', '5000 mAh'),
(35, 26, '6.67', '120Hz', 'HarmonyOS 3.1', 'Qualcomm Snapdragon 8+ Gen 1', '48MP + 48MP + 13MP', '13MP', '4815 mAh'),
(36, 24, '6.7', '120Hz', 'ColorOS 14 (Android 14)', 'MediaTek Dimensity 7050', '50MP + 32MP + 8MP', '32MP', '4800 mAh'),
(37, 25, '6.78', '120Hz', 'Funtouch OS 14 (Android 14)', 'MediaTek Dimensity 9300', '50MP + 50MP + 64MP', '32MP', '5000 mAh'),
(42, 3, '6.9', '120Hz', 'Android 15', 'Qualcomm Snapdragon 8 Elite', '200MP + 50MP + 10MP + 50MP', '12MP', '5000 mAh');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_product_variants`
--

CREATE TABLE `tbl_product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ram` varchar(10) NOT NULL,
  `storage` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_product_variants`
--

INSERT INTO `tbl_product_variants` (`variant_id`, `product_id`, `ram`, `storage`, `price`, `original_price`) VALUES
(4, 11, '8GB', '256GB', '29799000.00', '29799000.00'),
(5, 11, '8GB', '512GB', '35699000.00', '35699000.00'),
(6, 11, '8GB', '1T', '41599000.00', '41599000.00'),
(9, 12, '8GB', '128GB', '7999000.00', '7999000.00'),
(10, 12, '8GB', '256GB', '8999000.00', '8999000.00'),
(17, 13, '12GB', '256GB', '18990000.00', '18990000.00'),
(18, 13, '12GB', '512GB', '21990000.00', '21990000.00'),
(19, 13, '16GB', '1TB', '25990000.00', '25990000.00'),
(26, 14, '3GB', '64GB', '7990000.00', '7990000.00'),
(27, 14, '3GB', '128GB', '8990000.00', '8990000.00'),
(28, 14, '3GB', '256GB', '9990000.00', '9990000.00'),
(158, 15, '6GB', '128GB', '20990000.00', '20990000.00'),
(159, 15, '6GB', '256GB', '22990000.00', '22990000.00'),
(160, 15, '6GB', '512GB', '25990000.00', '25990000.00'),
(161, 15, '6GB', '1TB', '28990000.00', '28990000.00'),
(165, 16, '12GB', '256GB', '31900000.00', '31900000.00'),
(166, 16, '12GB', '512GB', '36000000.00', '36000000.00'),
(167, 16, '12GB', '1TB', '34000000.00', '34000000.00'),
(168, 17, '8GB', '256GB', '18000000.00', '18000000.00'),
(169, 17, '8GB', '512GB', '22000000.00', '22000000.00'),
(170, 17, '12GB', '512GB', '25000000.00', '25000000.00'),
(171, 18, '8GB', '128GB', '10000000.00', '10000000.00'),
(172, 18, '8GB', '256GB', '5495000.00', '5495000.00'),
(173, 18, '12GB', '256GB', '5995000.00', '5995000.00'),
(174, 19, '8GB', '256GB', '14990000.00', NULL),
(175, 19, '12GB', '256GB', '14990000.00', NULL),
(176, 19, '12GB', '512GB', '16990000.00', NULL),
(177, 19, '16GB', '1TB', '19990000.00', NULL),
(178, 20, '12GB', '256GB', '18990000.00', '18990000.00'),
(179, 20, '12GB', '512GB', '21990000.00', '21990000.00'),
(180, 20, '16GB', '1TB', '24990000.00', '24990000.00'),
(181, 27, '12GB', '256GB', '14990000.00', '14990000.00'),
(182, 27, '12GB', '512GB', '16990000.00', '16990000.00'),
(183, 27, '16GB', '1TB', '19990000.00', '19990000.00'),
(184, 21, '8GB', '256GB', '6495000.00', '6495000.00'),
(185, 21, '12GB', '256GB', '6995000.00', '6995000.00'),
(186, 21, '12GB', '512GB', '7995000.00', '7995000.00'),
(187, 21, '16GB', '1TB', '8995000.00', '8995000.00'),
(188, 22, '6GB', '128GB', '19990000.00', NULL),
(189, 22, '6GB', '256GB', '22990000.00', NULL),
(190, 22, '6GB', '512GB', '25990000.00', NULL),
(191, 23, '8GB', '128GB', '7990000.00', '7990000.00'),
(192, 23, '8GB', '256GB', '8990000.00', '8990000.00'),
(193, 23, '12GB', '512GB', '9990000.00', '9990000.00'),
(194, 28, '8GB', '128GB', '5990000.00', '5990000.00'),
(195, 28, '8GB', '256GB', '6990000.00', '6990000.00'),
(196, 28, '12GB', '512GB', '7990000.00', '7990000.00'),
(197, 26, '12GB', '256GB', '16990000.00', '16990000.00'),
(198, 26, '12GB', '512GB', '19990000.00', '19990000.00'),
(199, 26, '16GB', '1TB', '22990000.00', '22990000.00'),
(200, 24, '8GB', '256GB', '5995000.00', NULL),
(201, 24, '12GB', '256GB', '6495000.00', NULL),
(202, 24, '12GB', '512GB', '7495000.00', NULL),
(203, 25, '12GB', '256GB', '12990000.00', '12990000.00'),
(204, 25, '12GB', '512GB', '14990000.00', '14990000.00'),
(205, 25, '16GB', '1TB', '16990000.00', '16990000.00'),
(218, 3, '12GB', '256GB', '23000000.00', NULL),
(219, 3, '12GB', '512GB', '29000000.00', NULL),
(220, 3, '12GB', '1T', '33000000.00', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_promotions`
--

CREATE TABLE `tbl_promotions` (
  `promo_id` int(11) NOT NULL,
  `promo_code` varchar(20) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_promotions`
--

INSERT INTO `tbl_promotions` (`promo_id`, `promo_code`, `discount_value`, `discount_type`, `start_date`, `end_date`, `status`) VALUES
(1, 'GIAM10', '10.00', 'percentage', '2025-04-06', '2025-12-31', 'active'),
(2, 'SALE500K', '500000.00', 'fixed', '2025-04-06', '2025-12-31', 'active'),
(3, 'IPHONE15', '15.00', 'percentage', '2025-04-12', '2025-12-31', 'active'),
(4, 'SAMSUNG500K', '500000.00', 'fixed', '2025-04-12', '2025-12-31', 'active'),
(5, 'OPPOX8', '10.00', 'percentage', '2025-04-12', '2025-11-30', 'active'),
(6, 'XIAOMI20', '20.00', 'percentage', '2025-04-12', '2025-10-31', 'active'),
(7, 'IPHONE8PLUS', '300000.00', 'fixed', '2025-04-12', '2025-09-30', 'active'),
(8, 'GIẢM CỰC ĐÃ', '333333.00', 'fixed', '2025-05-02', '2025-05-30', 'active'),
(9, 'PHATNE123', '11.00', 'percentage', '2025-05-17', '2025-05-17', 'active'),
(10, 'VIP_DONG', '3.00', 'percentage', '2025-05-15', '2025-12-31', 'active'),
(11, 'VIP_BAC', '5.00', 'percentage', '2025-05-15', '2025-12-31', 'active'),
(12, 'VIP_VANG', '7.00', 'percentage', '2025-05-15', '2025-12-31', 'active'),
(13, 'VIP_KIMCUONG', '10.00', 'percentage', '2025-05-15', '2025-12-31', 'active'),
(14, 'VIP_LUCBAO', '15.00', 'percentage', '2025-05-15', '2025-12-31', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_reviews`
--

CREATE TABLE `tbl_reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text NOT NULL,
  `parent_review_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_reviews`
--

INSERT INTO `tbl_reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`, `admin_reply`, `parent_review_id`) VALUES
(1, 11, 7, 1, 'A', '2025-04-05 11:24:54', '1', NULL),
(2, 11, 6, 2, 'được\\r\\n', '2025-04-06 01:06:13', 'd', NULL),
(3, 3, 6, 5, 'đẹp', '2025-04-06 01:08:27', '', NULL),
(4, 3, 6, 5, 'xấu', '2025-04-06 01:08:40', '', NULL),
(5, 11, 6, 2, 'đẹp quá', '2025-04-06 01:11:19', '', NULL),
(6, 11, 6, 5, 'đẹp', '2025-04-06 01:13:33', '', NULL),
(7, 14, 7, 5, 'rẻ và đẹp', '2025-04-07 12:52:49', '', NULL),
(8, 13, 10, 5, 'ĐẸP', '2025-04-07 13:26:21', '', NULL),
(9, 14, 16, 5, 'đẹp', '2025-04-15 11:41:07', '', NULL),
(10, 11, 16, 5, 'chào', '2025-04-15 12:21:45', '', NULL),
(11, 11, 16, 4, 'đẹp quá', '2025-04-15 12:28:13', '', NULL),
(12, 11, 7, 5, 'xịn', '2025-04-17 15:05:35', 'cám ơn', NULL),
(13, 19, 6, 5, 're ma xin.', '2025-04-18 06:40:22', '', NULL),
(14, 12, 6, 5, 'được', '2025-04-20 13:51:37', '', NULL),
(15, 12, 6, 5, 'được', '2025-04-20 13:51:41', '', NULL),
(16, 12, 6, 5, 'xấu', '2025-04-20 13:51:48', '', NULL),
(17, 12, 6, 4, 'đẹp', '2025-05-08 07:42:16', '', NULL),
(18, 26, 16, 4, 'đẹp', '2025-05-11 11:59:38', '', NULL),
(19, 26, 16, 5, 'a', '2025-05-11 11:59:53', '', NULL),
(20, 26, 16, 5, 'a', '2025-05-11 11:59:57', '', NULL),
(21, 26, 16, 5, 'e', '2025-05-11 12:00:03', '', NULL),
(22, 13, 7, 5, 'quao', '2025-07-20 09:47:35', '', NULL),
(23, 3, 17, 2, 'a', '2025-07-23 04:47:46', '', NULL);

--
-- Bẫy `tbl_reviews`
--
DELIMITER $$
CREATE TRIGGER `after_review_reply` AFTER UPDATE ON `tbl_reviews` FOR EACH ROW BEGIN
    IF NEW.admin_reply != '' AND OLD.admin_reply = '' THEN
        INSERT INTO `tbl_notifications` (`user_id`, `type`, `message`, `link`, `related_id`)
        SELECT NEW.user_id, 'review_reply',
               CONCAT('Bình luận của bạn về sản phẩm ', p.product_name, ' đã được admin trả lời.'),
               CONCAT('product.php?id=', NEW.product_id, '#review-', NEW.review_id),
               NEW.review_id
        FROM tbl_products p
        WHERE p.product_id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_reply` AFTER INSERT ON `tbl_reviews` FOR EACH ROW BEGIN
    IF NEW.parent_review_id IS NOT NULL THEN
        INSERT INTO `tbl_notifications` (`user_id`, `type`, `message`, `link`, `related_id`)
        SELECT 
            r.user_id,
            'user_reply',
            CONCAT('Bình luận của bạn về sản phẩm ', p.product_name, ' đã được người dùng khác trả lời.'),
            CONCAT('product.php?id=', NEW.product_id, '#review-', NEW.review_id),
            NEW.review_id
        FROM `tbl_reviews` r
        JOIN `tbl_products` p ON p.product_id = NEW.product_id
        WHERE r.review_id = NEW.parent_review_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `full_name`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(6, 'Admin 1', 'phat08355@gmail.com', '$2y$10$Bl1fzTrK4YdGaDNKshiwfOByOGa6id36ZrhC6qSh5Y.RhW65rvlai', '0123125657', 'Hưng Mỹ, Cái Nước, Cà Mau', 'admin', '2025-04-05 09:32:49'),
(7, 'anh Phát ác quỷ2', 'kanekikentp@gmail.com', '$2y$10$3/rXfVm4QpPsXSyeTMYNPOqtdzvdFH2jIY7V0extB3Ax3Lx3THbii', '0835512896', 'Cà Mau', 'user', '2025-04-05 10:22:04'),
(8, 'Mạc Minh Nhất', 'minhnhatttt1102@gmail.com', '$2y$10$UU6m8dJ0slCvYjltsdhyP.AyvC.jFQCHwSLGiu0BjQVmxJqYIMpJq', '0392139094', 'Cà Mau', 'user', '2025-04-07 12:39:00'),
(10, 'Phát ác quỷ', 'phatne@gmail.com', '$2y$10$SR6CI/fp54mOl.GggreAROcrbnE7883koD1Z8UjGBRytkt3U1FsQS', '0123456789', 'Hưng Mỹ, Cái Nước, Cà Mau', 'user', '2025-04-07 13:12:05'),
(11, 'Ty dep trai', 'ty24122004@gmail.com', '$2y$10$1Z0TWPMzNY5PTdxL3AwwHue7LcRvw72.R1N6jH.vA2QU8BuXXhvuK', '0926291826', 'Ca Mau', 'user', '2025-04-11 17:06:22'),
(16, 'Phát Trần', 'tphat431@gmail.com', '$2y$10$.X3zvJrfzHEJVgU7coTgbesdHrNtipxkZ/aXG21glWNnW4YeQuPdS', '0123456781', 'Cà Mau', 'user', '2025-04-12 09:11:41'),
(17, 'Phát gà', 'ttphat-cntt16@tdu.edu.vn', '$2y$10$k..dsYrh8R8y7xyZslv4t.9wra0co/xtRCHpKZ4hR4Pqa291km0jW', '0981231921', 'Hưng Mỹ, Cái Nước, Cà Mau', 'user', '2025-05-14 15:18:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_vip_customers`
--

CREATE TABLE `tbl_vip_customers` (
  `vip_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_spent` decimal(10,2) NOT NULL,
  `vip_level` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `tbl_vip_customers`
--

INSERT INTO `tbl_vip_customers` (`vip_id`, `user_id`, `total_spent`, `vip_level`, `updated_at`) VALUES
(1, 6, '58314600.00', 'Vàng', '2025-05-15 09:18:10'),
(2, 7, '52980000.00', 'Vàng', '2025-05-15 09:18:10'),
(3, 8, '40798000.00', 'Vàng', '2025-05-15 09:18:10'),
(4, 10, '45979000.00', 'Vàng', '2025-05-15 09:18:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_otp`
--

CREATE TABLE `user_otp` (
  `user_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `user_otp`
--

INSERT INTO `user_otp` (`user_id`, `otp`, `created_at`) VALUES
(7, '482685', '2025-07-23 04:43:53');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `tbl_addresses`
--
ALTER TABLE `tbl_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `fk_addresses_user` (`user_id`);

--
-- Chỉ mục cho bảng `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `fk_cart_user` (`user_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Chỉ mục cho bảng `tbl_categories`
--
ALTER TABLE `tbl_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `tbl_chatbot`
--
ALTER TABLE `tbl_chatbot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chatbot_user` (`user_id`);

--
-- Chỉ mục cho bảng `tbl_favorites`
--
ALTER TABLE `tbl_favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `fk_favorites_user` (`user_id`),
  ADD KEY `fk_favorites_product` (`product_id`);

--
-- Chỉ mục cho bảng `tbl_flashsale`
--
ALTER TABLE `tbl_flashsale`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_flashsale_flashsales` (`flashsale_id`),
  ADD KEY `fk_flashsale_product` (`product_id`);

--
-- Chỉ mục cho bảng `tbl_flashsales`
--
ALTER TABLE `tbl_flashsales`
  ADD PRIMARY KEY (`flashsale_id`),
  ADD KEY `idx_end_time` (`end_time`);

--
-- Chỉ mục cho bảng `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_user` (`user_id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Chỉ mục cho bảng `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `fk_orders_address` (`address_id`);

--
-- Chỉ mục cho bảng `tbl_order_details`
--
ALTER TABLE `tbl_order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `fk_order_details_order` (`order_id`),
  ADD KEY `fk_order_details_product` (`product_id`),
  ADD KEY `fk_order_details_color` (`color_id`);

--
-- Chỉ mục cho bảng `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- Chỉ mục cho bảng `tbl_product_colors`
--
ALTER TABLE `tbl_product_colors`
  ADD PRIMARY KEY (`color_id`),
  ADD KEY `fk_colors_product` (`product_id`);

--
-- Chỉ mục cho bảng `tbl_product_images`
--
ALTER TABLE `tbl_product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_images_product` (`product_id`),
  ADD KEY `fk_images_color` (`color_id`);

--
-- Chỉ mục cho bảng `tbl_product_specifications`
--
ALTER TABLE `tbl_product_specifications`
  ADD PRIMARY KEY (`spec_id`),
  ADD KEY `fk_specs_product_new` (`product_id`);

--
-- Chỉ mục cho bảng `tbl_product_variants`
--
ALTER TABLE `tbl_product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `fk_variants_product` (`product_id`);

--
-- Chỉ mục cho bảng `tbl_promotions`
--
ALTER TABLE `tbl_promotions`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `promo_code` (`promo_code`);

--
-- Chỉ mục cho bảng `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_reviews_product` (`product_id`),
  ADD KEY `fk_reviews_user` (`user_id`),
  ADD KEY `fk_reviews_parent` (`parent_review_id`);

--
-- Chỉ mục cho bảng `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Chỉ mục cho bảng `tbl_vip_customers`
--
ALTER TABLE `tbl_vip_customers`
  ADD PRIMARY KEY (`vip_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `user_otp`
--
ALTER TABLE `user_otp`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `tbl_addresses`
--
ALTER TABLE `tbl_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT cho bảng `tbl_categories`
--
ALTER TABLE `tbl_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tbl_chatbot`
--
ALTER TABLE `tbl_chatbot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT cho bảng `tbl_favorites`
--
ALTER TABLE `tbl_favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT cho bảng `tbl_flashsale`
--
ALTER TABLE `tbl_flashsale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT cho bảng `tbl_flashsales`
--
ALTER TABLE `tbl_flashsales`
  MODIFY `flashsale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT cho bảng `tbl_order_details`
--
ALTER TABLE `tbl_order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `tbl_products`
--
ALTER TABLE `tbl_products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `tbl_product_colors`
--
ALTER TABLE `tbl_product_colors`
  MODIFY `color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT cho bảng `tbl_product_images`
--
ALTER TABLE `tbl_product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT cho bảng `tbl_product_specifications`
--
ALTER TABLE `tbl_product_specifications`
  MODIFY `spec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT cho bảng `tbl_product_variants`
--
ALTER TABLE `tbl_product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT cho bảng `tbl_promotions`
--
ALTER TABLE `tbl_promotions`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `tbl_vip_customers`
--
ALTER TABLE `tbl_vip_customers`
  MODIFY `vip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `tbl_addresses`
--
ALTER TABLE `tbl_addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_chatbot`
--
ALTER TABLE `tbl_chatbot`
  ADD CONSTRAINT `fk_chatbot_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_favorites`
--
ALTER TABLE `tbl_favorites`
  ADD CONSTRAINT `fk_favorites_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_flashsale`
--
ALTER TABLE `tbl_flashsale`
  ADD CONSTRAINT `fk_flashsale_flashsales` FOREIGN KEY (`flashsale_id`) REFERENCES `tbl_flashsales` (`flashsale_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_flashsale_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) REFERENCES `tbl_addresses` (`address_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_order_details`
--
ALTER TABLE `tbl_order_details`
  ADD CONSTRAINT `fk_order_details_color` FOREIGN KEY (`color_id`) REFERENCES `tbl_product_colors` (`color_id`),
  ADD CONSTRAINT `fk_order_details_order` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_details_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `tbl_categories` (`category_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `tbl_product_colors`
--
ALTER TABLE `tbl_product_colors`
  ADD CONSTRAINT `fk_colors_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_product_images`
--
ALTER TABLE `tbl_product_images`
  ADD CONSTRAINT `fk_images_color` FOREIGN KEY (`color_id`) REFERENCES `tbl_product_colors` (`color_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_product_specifications`
--
ALTER TABLE `tbl_product_specifications`
  ADD CONSTRAINT `fk_specs_product_new` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_product_variants`
--
ALTER TABLE `tbl_product_variants`
  ADD CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  ADD CONSTRAINT `fk_reviews_parent` FOREIGN KEY (`parent_review_id`) REFERENCES `tbl_reviews` (`review_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_vip_customers`
--
ALTER TABLE `tbl_vip_customers`
  ADD CONSTRAINT `fk_vip_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
