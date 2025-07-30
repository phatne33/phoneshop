<?php
// Bắt đầu hoặc tiếp tục session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Load Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">

<nav class="bg-gray-100 shadow-lg">
    <div class="">
        <div class="flex justify-between h-16 items-center">
            <div class="flex-shrink-0">
                <a href="index.php" class="text-3xl font-extrabold text-gray-800 font-poppins">PhoneStore</a>
            </div>
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Cart Button with Badge -->
                    <button type="button" class="relative inline-flex items-center text-sm font-semibold text-gray-700 hover:text-indigo-600 transition duration-300 group" data-bs-toggle="modal" data-bs-target="#cartModal" title="Giỏ hàng">
                        <span class="relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?php
                                $conn = new mysqli("localhost", "root", "", "phonedb");
                                if (!$conn->connect_error) {
                                    $user_id = $_SESSION['user_id'];
                                    $cart_count_sql = "SELECT COUNT(*) as count FROM tbl_cart WHERE user_id = ?";
                                    $cart_count_stmt = $conn->prepare($cart_count_sql);
                                    $cart_count_stmt->bind_param("i", $user_id);
                                    $cart_count_stmt->execute();
                                    $cart_count_result = $cart_count_stmt->get_result();
                                    echo $cart_count_result->fetch_assoc()['count'];
                                    $cart_count_stmt->close();
                                    $conn->close();
                                } else {
                                    echo '0';
                                }
                            ?></span>
                        </span>
                    </button>

                    <!-- Favorites Button with Badge -->
                    <button type="button" class="relative inline-flex items-center text-sm font-semibold text-gray-700 hover:text-pink-600 transition duration-300 group" data-bs-toggle="modal" data-bs-target="#favoritesModal" title="Yêu thích">
                        <span class="relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span id="favorite-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?php
                                $conn = new mysqli("localhost", "root", "", "phonedb");
                                if (!$conn->connect_error) {
                                    $user_id = $_SESSION['user_id'];
                                    $favorite_count_sql = "SELECT COUNT(*) as count FROM tbl_favorites WHERE user_id = ?";
                                    $favorite_count_stmt = $conn->prepare($favorite_count_sql);
                                    $favorite_count_stmt->bind_param("i", $user_id);
                                    $favorite_count_stmt->execute();
                                    $favorite_count_result = $favorite_count_stmt->get_result();
                                    echo $favorite_count_result->fetch_assoc()['count'];
                                    $favorite_count_stmt->close();
                                    $conn->close();
                                } else {
                                    echo '0';
                                }
                            ?></span>
                        </span>
                    </button>

                    <a href="index.php?page=addresses" class="inline-flex items-center text-sm font-semibold text-gray-700 hover:text-teal-600 transition duration-300 group" title="Địa chỉ">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </a>
                    <a href="index.php?page=myorders" class="inline-flex items-center text-sm font-semibold text-gray-700 hover:text-purple-600 transition duration-300 group" title="Đơn hàng">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </a>
                    <a href="index.php?page=account" class="inline-flex items-center text-sm font-semibold text-gray-700 hover:text-blue-600 transition duration-300 group" title="Tài khoản">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </a>
                    <a href="logout.php" class="inline-flex items-center text-sm font-semibold text-gray-700 hover:text-red-600 transition duration-300 group" title="Đăng xuất">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="login_customer.php" class="inline-flex items-center text-sm font-semibold text-gray-700 hover:text-indigo-600 transition duration-300 group" title="Đăng nhập">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
            <div class="-mr-2 flex items-center sm:hidden">
                <button type="button" class="inline-flex items-center justify-center rounded-full text-gray-400 hover:text-indigo-600 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition duration-300" aria-controls="mobile-menu" aria-expanded="false" id="mobile-menu-toggle">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div class="sm:hidden hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1 bg-gray-100">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" class="border-l-4 border-transparent text-gray-700 hover:bg-indigo-50 hover:border-indigo-500 hover:text-indigo-600 block pl-3 pr-4 py-2 text-base font-semibold transition duration-300" data-bs-toggle="modal" data-bs-target="#cartModal">
                    <span class="relative inline-flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span id="mobile-cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?php
                            $conn = new mysqli("localhost", "root", "", "phonedb");
                            if (!$conn->connect_error) {
                                $user_id = $_SESSION['user_id'];
                                $cart_count_sql = "SELECT COUNT(*) as count FROM tbl_cart WHERE user_id = ?";
                                $cart_count_stmt = $conn->prepare($cart_count_sql);
                                $cart_count_stmt->bind_param("i", $user_id);
                                $cart_count_stmt->execute();
                                $cart_count_result = $cart_count_stmt->get_result();
                                echo $cart_count_result->fetch_assoc()['count'];
                                $cart_count_stmt->close();
                                $conn->close();
                            } else {
                                echo '0';
                            }
                        ?></span>
                    </span>
                    Giỏ hàng
                </a>
                <a href="#" class="border-l-4 border-transparent text-gray-700 hover:bg-pink-50 hover:border-pink-500 hover:text-pink-600 block pl-3 pr-4 py-2 text-base font-semibold transition duration-300" data-bs-toggle="modal" data-bs-target="#favoritesModal">
                    <span class="relative inline-flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span id="mobile-favorite-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?php
                            $conn = new mysqli("localhost", "root", "", "phonedb");
                            if (!$conn->connect_error) {
                                $user_id = $_SESSION['user_id'];
                                $favorite_count_sql = "SELECT COUNT(*) as count FROM tbl_favorites WHERE user_id = ?";
                                $favorite_count_stmt = $conn->prepare($favorite_count_sql);
                                $favorite_count_stmt->bind_param("i", $user_id);
                                $favorite_count_stmt->execute();
                                $favorite_count_result = $favorite_count_stmt->get_result();
                                echo $favorite_count_result->fetch_assoc()['count'];
                                $favorite_count_stmt->close();
                                $conn->close();
                            } else {
                                echo '0';
                            }
                        ?></span>
                    </span>
                    Yêu thích
                </a>
                <a href="index.php?page=addresses" class="border-l-4 border-transparent text-gray-700 hover:bg-teal-50 hover:border-teal-500 hover:text-teal-600 block pl-3 pr-4 py-2 text-base font-semibold transition duration-300">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Địa chỉ
                </a>
                <a href="index.php?page=myorders" class="border-l-4 border-transparent text-gray-700 hover:bg-purple-50 hover:border-purple-500 hover:text-purple-600 block pl-3 pr-4 py-2 text-base font-semibold transition duration-300">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Đơn hàng
                </a>
                <a href="index.php?page=account" class="border-l-4 border-transparent text-gray-700 hover:bg-blue-50 hover:border-blue-500 hover:text-blue-600 block pl-3 pr-4 py-2 text-base font-semibold transition duration-300">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Tài khoản
                </a>
                <a href="logout.php" class="border-l-4 border-transparent text-gray-700 hover:bg-red-50 hover:border-red-500 hover:text-red-600 block pl-3 pr-4 py-2 text-base font-semibold transition duration-300">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Đăng xuất
                </a>
            <?php else: ?>
                <a href="login_customer.php" class="border-l-4 border-transparent text-gray-700 hover:bg-indigo-50 hover:border-indigo-500 hover:text-indigo-600 block pl-3 pr-4 py-2 text-base font-semibold transition duration-300">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Đăng nhập
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Modal Giỏ Hàng -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Giỏ Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cart-modal-content">
                <div class="text-center">
                    <p>Đang tải giỏ hàng...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded transition duration-300" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Yêu Thích -->
<div class="modal fade" id="favoritesModal" tabindex="-1" aria-labelledby="favoritesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="favoritesModalLabel">Sản Phẩm Yêu Thích</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="favorites-modal-content">
                <div class="text-center">
                    <p>Đang tải danh sách yêu thích...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded transition duration-300" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    // Đảm bảo modal đóng khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        const cartModal = document.getElementById('cartModal');
        const favoritesModal = document.getElementById('favoritesModal');
        const cartModalInstance = bootstrap.Modal.getInstance(cartModal) || new bootstrap.Modal(cartModal);
        const favoritesModalInstance = bootstrap.Modal.getInstance(favoritesModal) || new bootstrap.Modal(favoritesModal);
        if (cartModalInstance) cartModalInstance.hide();
        if (favoritesModalInstance) favoritesModalInstance.hide();
    });

    // Toggle menu mobile
    document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });

    // Hàm lấy nội dung giỏ hàng
    async function fetchCart() {
        try {
            const response = await fetch('views/ajax.php?action=fetch_cart', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('cart-modal-content').innerHTML = data.html;
                document.getElementById('cart-count').textContent = data.cart_count;
                document.getElementById('mobile-cart-count').textContent = data.cart_count;
                attachCartEventListeners();
            } else {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message,
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Đã xảy ra lỗi khi tải giỏ hàng.',
                timer: 1000,
                showConfirmButton: false
            });
        }
    }

    // Hàm lấy nội dung yêu thích
    async function fetchFavorites() {
        try {
            const response = await fetch('views/ajax.php?action=fetch_favorites', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('favorites-modal-content').innerHTML = data.html;
                document.getElementById('favorite-count').textContent = data.favorite_count;
                document.getElementById('mobile-favorite-count').textContent = data.favorite_count;
                attachFavoritesEventListeners();
            } else {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message,
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Đã xảy ra lỗi khi tải danh sách yêu thích.',
                timer: 1000,
                showConfirmButton: false
            });
        }
    }

    // Gắn sự kiện cho các hành động trong giỏ hàng
    function attachCartEventListeners() {
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const cartId = this.closest('tr').dataset.cartId;
                const action = this.querySelector('svg').classList.contains('fa-plus') ? 1 : -1;
                await updateQuantity(cartId, action);
            });
        });

        document.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const cartId = this.closest('tr').dataset.cartId;
                await removeCartItem(cartId);
            });
        });

        document.querySelectorAll('.cart-color, .cart-storage').forEach(select => {
            select.addEventListener('change', async function() {
                const cartId = this.dataset.cartId;
                const color = this.closest('tr').querySelector('.cart-color').value;
                const storage = this.closest('tr').querySelector('.cart-storage').value;
                const quantity = this.closest('tr').querySelector('.quantity-input').value;
                await updateCartItem(cartId, quantity, color, storage);
            });
        });

        const promoForm = document.getElementById('promo-form');
        if (promoForm) {
            promoForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'apply_promo');
                try {
                    const response = await fetch('views/ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: data.message,
                            timer: 1000,
                            showConfirmButton: false
                        });
                        fetchCart();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: data.message,
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Đã xảy ra lỗi khi áp dụng mã giảm giá.',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            });
        }
    }

    // Gắn sự kiện cho các hành động trong danh sách yêu thích
    function attachFavoritesEventListeners() {
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                const form = this.closest('.add-to-cart-form');
                const formData = new FormData(form);
                formData.append('action', 'add_to_cart');
                try {
                    const response = await fetch('views/ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: data.message,
                            timer: 1000,
                            showConfirmButton: false
                        });
                        document.getElementById('cart-count').textContent = data.cart_count;
                        document.getElementById('mobile-cart-count').textContent = data.cart_count;
                        fetchCart();
                    } else {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi!',
                                text: data.message,
                                timer: 1000,
                                showConfirmButton: false
                            });
                        }
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Đã xảy ra lỗi khi thêm vào giỏ hàng.',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            });
        });

        document.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                const form = this.closest('.remove-favorite-form');
                const formData = new FormData(form);
                formData.append('action', 'toggle_favorite');
                try {
                    const response = await fetch('views/ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: data.message,
                            timer: 1000,
                            showConfirmButton: false
                        });
                        document.getElementById('favorite-count').textContent = data.favorite_count;
                        document.getElementById('mobile-favorite-count').textContent = data.favorite_count;
                        fetchFavorites();
                    } else {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi!',
                                text: data.message,
                                timer: 1000,
                                showConfirmButton: false
                            });
                        }
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Đã xảy ra lỗi khi xóa sản phẩm yêu thích.',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            });
        });
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    async function updateQuantity(cartId, change) {
        const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
        const quantityInput = row.querySelector('.quantity-input');
        let quantity = parseInt(quantityInput.value) + change;
        if (quantity < 1) quantity = 1;
        const maxQuantity = parseInt(quantityInput.dataset.max) || Number.MAX_SAFE_INTEGER;
        if (quantity > maxQuantity) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: `Chỉ còn ${maxQuantity} sản phẩm trong Flash Sale!`,
                timer: 1000,
                showConfirmButton: false
            });
            return;
        }

        const color = row.querySelector('.cart-color').value;
        const storage = row.querySelector('.cart-storage').value;

        const formData = new FormData();
        formData.append('action', 'update_cart');
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        formData.append('color', color);
        formData.append('storage', storage);

        try {
            const response = await fetch('views/ajax.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                quantityInput.value = data.quantity;
                row.querySelector('.subtotal').textContent = `${parseInt(data.subtotal).toLocaleString('vi-VN')} VNĐ`;
                document.getElementById('total-amount').innerHTML = data.total_amount_html;
                document.getElementById('cart-count').textContent = data.cart_count;
                document.getElementById('mobile-cart-count').textContent = data.cart_count;
                row.querySelectorAll('.quantity-btn')[0].disabled = data.quantity <= 1;
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Đã xảy ra lỗi khi cập nhật số lượng.',
                timer: 1000,
                showConfirmButton: false
            });
        }
    }

    // Xóa sản phẩm khỏi giỏ hàng
    async function removeCartItem(cartId) {
        const formData = new FormData();
        formData.append('action', 'remove_cart');
        formData.append('cart_id', cartId);

        try {
            const response = await fetch('views/ajax.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('cart-count').textContent = data.cart_count;
                document.getElementById('mobile-cart-count').textContent = data.cart_count;
                document.getElementById('total-amount').innerHTML = data.total_amount_html;
                fetchCart();
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Đã xảy ra lỗi khi xóa sản phẩm.',
                timer: 1000,
                showConfirmButton: false
            });
        }
    }

    // Lắng nghe sự kiện hiển thị modal để lấy nội dung
    document.getElementById('cartModal').addEventListener('show.bs.modal', fetchCart);
    document.getElementById('favoritesModal').addEventListener('show.bs.modal', fetchFavorites);

    // Lắng nghe sự kiện từ các trang khác (ví dụ: sanpham.php)
    window.addEventListener('message', function(event) {
        if (event.data.type === 'updateCartCount') {
            document.getElementById('cart-count').textContent = event.data.count;
            document.getElementById('mobile-cart-count').textContent = event.data.count;
            if (document.getElementById('cartModal').classList.contains('show')) {
                fetchCart();
            }
        } else if (event.data.type === 'updateFavoriteCount') {
            document.getElementById('favorite-count').textContent = event.data.count;
            document.getElementById('mobile-favorite-count').textContent = event.data.count;
            if (document.getElementById('favoritesModal').classList.contains('show')) {
                fetchFavorites();
            }
        }
    });
</script>

<style>
    .cart-table, .favorites-table {
        width: 100%;
        border-collapse: collapse;
    }
    .cart-table td, .favorites-table td {
        padding: 10px;
        vertical-align: middle;
        border-bottom: 1px solid #e5e7eb;
    }
    .product-image {
        width: 80px;
        height: 80px;
        object-fit: contain;
    }
    .flashsale-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        width: 50px;
    }
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .quantity-btn {
        background: #4f46e5;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }
    .quantity-btn:hover {
        background: #4338ca;
        transform: scale(1.05);
    }
    .quantity-btn:disabled {
        background: #d1d5db;
        cursor: not-allowed;
    }
    .quantity-input {
        width: 50px;
        text-align: center;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 5px;
        transition: border-color 0.3s;
    }
    .quantity-input:focus {
        border-color: #4f46e5;
        outline: none;
    }
    .remove-btn, .action-btn {
        background: #ef4444;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }
    .remove-btn:hover, .action-btn:hover {
        background: #dc2626;
        transform: scale(1.05);
    }
    .add-to-cart-btn {
        background: #22c55e;
    }
    .add-to-cart-btn:hover {
        background: #16a34a;
        transform: scale(1.05);
    }
    .productname {
        font-weight: 600;
        color: #1f2937;
        font-family: 'Poppins', sans-serif;
    }
    .price {
        color: #dc2626;
        font-weight: 600;
        font-family: 'Roboto', sans-serif;
    }
    .original-price {
        color: #6b7280;
        text-decoration: line-through;
        margin-right: 5px;
        font-family: 'Roboto', sans-serif;
    }
    .remaining-quantity {
        color: #dc2626;
        font-size: 12px;
        font-family: 'Roboto', sans-serif;
    }
    .total-section {
        text-align: right;
        margin-top: 20px;
    }
    .promo-section {
        margin-bottom: 10px;
    }
    .formpromo {
        display: flex;
        gap: 10px;
    }
    .formpromo input {
        padding: 5px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        transition: border-color 0.3s;
    }
    .formpromo input:focus {
        border-color: #4f46e5;
        outline: none;
    }
    .formpromo button {
        background: #4f46e5;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }
    .formpromo button:hover {
        background: #4338ca;
        transform: scale(1.05);
    }
    .empty-cart, .empty-message {
        text-align: center;
        padding: 20px;
        color: #6b7280;
        font-family: 'Poppins', sans-serif;
    }
    .font-poppins {
        font-family: 'Poppins', sans-serif;
    }
    .font-roboto {
        font-family: 'Roboto', sans-serif;
    }
    /* Ẩn tên, chỉ hiện khi hover */
    .group:hover::after {
        content: attr(title);
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #4a5568;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 10;
    }
    .group {
        position: relative;
    }
    /* Ẩn text trong desktop navbar */
    .sm:flex .inline-flex > span:not(.relative) {
        display: none;
    }
</style>
