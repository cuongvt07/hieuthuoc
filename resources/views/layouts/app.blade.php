<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ Thống Quản Lý Hiệu Thuốc')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            min-height: 100vh;
            background-color: #343a40;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
        }
        .content-wrapper {
            margin-left: 280px;
        }
        .menu-item {
            color: #ced4da;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .menu-item:hover, .menu-item.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .menu-header {
            margin-top: 10px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #adb5bd !important;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1090;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="d-flex flex-column h-100">
            <div class="p-3">
                <h3 class="text-white">Hiệu Thuốc</h3>
            </div>
            <div class="flex-grow-1">
                <div class="list-group rounded-0">
                    <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    
                    <!-- Danh mục -->
                    <div class="menu-header text-muted px-3 py-2 small">
                        <i class="bi bi-list-ul me-2"></i> DANH MỤC
                    </div>
                    <a href="{{ route('thuoc.index') }}" class="menu-item {{ request()->routeIs('thuoc.*') || request()->routeIs('nhom-thuoc.*') ? 'active' : '' }}">
                        <i class="bi bi-capsule"></i> Quản Lý Thuốc & Nhóm Thuốc
                    </a>
                    <a href="{{ route('gia-thuoc.index') }}" class="menu-item {{ request()->routeIs('gia-thuoc.*') ? 'active' : '' }}">
                        <i class="bi bi-tag"></i> Giá Thuốc
                    </a>
                    
                    <!-- Đối tác -->
                    <div class="menu-header text-muted px-3 py-2 small">
                        <i class="bi bi-people me-2"></i> ĐỐI TÁC
                    </div>
                    <a href="{{ route('khach-hang.index') }}" class="menu-item {{ request()->routeIs('khach-hang.*') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Khách Hàng
                    </a>
                    <a href="{{ route('nha-cung-cap.index') }}" class="menu-item {{ request()->routeIs('nha-cung-cap.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> Nhà Cung Cấp
                    </a>
                    
                    <!-- Kho & Hàng -->
                    <div class="menu-header text-muted px-3 py-2 small">
                        <i class="bi bi-box-seam me-2"></i> KHO & HÀNG
                    </div>
                    <a href="{{ route('kho.index') }}" class="menu-item {{ request()->routeIs('kho.*') ? 'active' : '' }}">
                        <i class="bi bi-building-gear"></i> Quản Lý Kho
                    </a>
                    <a href="{{ route('phieu-nhap.index') }}" class="menu-item {{ request()->routeIs('phieu-nhap.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-plus"></i> Phiếu Nhập Kho
                    </a>
                    <a href="{{ route('lo-thuoc.index') }}" class="menu-item {{ request()->routeIs('lo-thuoc.*') ? 'active' : '' }}">
                        <i class="bi bi-box2"></i> Quản Lý Lô Thuốc
                    </a>
                    
                    <!-- Hệ thống -->
                    <div class="menu-header text-muted px-3 py-2 small">
                        <i class="bi bi-gear me-2"></i> HỆ THỐNG
                    </div>
                    <a href="{{ route('nguoi-dung.index') }}" class="menu-item {{ request()->routeIs('nguoi-dung.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i> Quản Lý Nhân Sự
                    </a>
                </div>
            </div>
            <div class="mt-auto border-top">
                <div class="p-3">
                    <div class="menu-item">
                        <i class="bi bi-info-circle"></i> 
                        <div>
                            <div>Phiên bản: 1.0.0</div>
                            <small class="text-muted">© 2025 Hiệu Thuốc</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <button class="btn btn-sm btn-light position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                2
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" style="min-width: 300px;" aria-labelledby="notificationsDropdown">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Thông Báo</span>
                                <a href="#" class="text-decoration-none small">Đánh dấu đã đọc tất cả</a>
                            </div>
                            <div class="dropdown-item">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Hôm nay</div>
                                        <div>Thuốc Paracetamol sắp hết hạn</div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-item">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Hôm qua</div>
                                        <div>Thuốc Amoxicillin đã hết hàng</div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center small" href="#">Xem tất cả thông báo</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="me-2" style="width: 32px; height: 32px; background-color: #4e73df; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                {{ substr(Auth::user()->ho_ten ?? 'U', 0, 1) }}
                            </div>
                            <div class="d-none d-md-block text-start">
                                <div style="line-height: 1;">{{ Auth::user()->ho_ten ?? '' }}</div>
                                <small class="text-muted">{{ Auth::user()->vai_tro == 'admin' ? 'Quản trị viên' : 'Dược sĩ' }}</small>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Hồ sơ cá nhân</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-key me-2"></i>Đổi mật khẩu</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">
            @yield('content')
        </div>
    </main>

    <div class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to show toast messages
        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.classList.add('toast', 'align-items-center', 'text-white', 'bg-' + type, 'border-0', 'mb-2');
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            const toastContent = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toast.innerHTML = toastContent;
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Display session messages
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            showToast("{{ session('error') }}", 'danger');
        @endif
        
        // Setup CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
