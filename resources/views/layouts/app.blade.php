<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ Thống Quản Lý Hiệu Thuốc')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.10.7/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.10.7/locale/vi.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.10.7/plugin/relativeTime.js"></script>
    <script>
        dayjs.locale('vi');
        dayjs.extend(window.dayjs_plugin_relativeTime);

        document.addEventListener('DOMContentLoaded', function() {
            // Tìm tất cả các menu item đang active
            const activeItems = document.querySelectorAll('.menu-item.active');
            
            activeItems.forEach(item => {
                // Tìm accordion collapse chứa menu item này
                const collapse = item.closest('.accordion-collapse');
                if (collapse) {
                    // Thêm class show để mở accordion
                    collapse.classList.add('show');
                    // Tìm nút toggle tương ứng và loại bỏ class collapsed
                    const toggleButton = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
                    if (toggleButton) {
                        toggleButton.classList.remove('collapsed');
                        toggleButton.setAttribute('aria-expanded', 'true');
                    }
                }
            });
        });
    </script>
    <style>
        .accordion-body {
            padding: 0 0 0 20px !important;
        }

        .accordion-button {
            padding: 8px 15px;
            font-size: 1rem;
        }

        .accordion-item {
            background: transparent;
            border: none;
        }

        .accordion-collapse {
            background: transparent;
        }

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

        .menu-item:hover,
        .menu-item.active {
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
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
                        <a href="{{ route('lich-su-ton-kho.index') }}" class="menu-item {{ request()->routeIs('lich-su-ton-kho.*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> Lịch Sử Tồn Kho
                        </a>
                        @if(Auth::user()->vai_tro == 'admin')
                            <div class="accordion" id="sidebarAccordion">
                                <div class="accordion" id="sidebarAccordion">
                                    <!-- Quản lý thuốc -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingDanhMuc">
                                            <button class="accordion-button collapsed bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDanhMuc" aria-expanded="false" aria-controls="collapseDanhMuc">
                                                <i class="bi bi-list-ul me-2"></i> Quản lý thuốc
                                            </button>
                                        </h2>
                                        <div id="collapseDanhMuc" class="accordion-collapse collapse" aria-labelledby="headingDanhMuc">
                                            <div class="accordion-body p-0">
                                                <a href="{{ route('thuoc.index') }}" class="menu-item {{ request()->routeIs('thuoc.*') || request()->routeIs('nhom-thuoc.*') ? 'active' : '' }}">
                                                    <i class="bi bi-capsule"></i> Quản Lý Thuốc & Nhóm Thuốc
                                                </a>
                                                <a href="{{ route('gia-thuoc.index') }}" class="menu-item {{ request()->routeIs('gia-thuoc.*') ? 'active' : '' }}">
                                                    <i class="bi bi-tag"></i> Giá Thuốc
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Quản lý danh mục -->
                                    <div class="accordion-item bg-transparent border-0">
                                        <h2 class="accordion-header" id="headingDoiTac">
                                            <button class="accordion-button collapsed bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDoiTac" aria-expanded="false" aria-controls="collapseDoiTac">
                                                <i class="bi bi-people me-2"></i> Quản lý danh mục
                                            </button>
                                        </h2>
                                        <div id="collapseDoiTac" class="accordion-collapse collapse" aria-labelledby="headingDoiTac">
                                            <div class="accordion-body p-0">
                                                <a href="{{ route('nha-cung-cap.index') }}" class="menu-item {{ request()->routeIs('nha-cung-cap.*') ? 'active' : '' }}">
                                                    <i class="bi bi-building"></i> Quản Lý Nhà Cung Cấp
                                                </a>
                                                <a href="{{ route('khach-hang.index') }}" class="menu-item {{ request()->routeIs('khach-hang.*') ? 'active' : '' }}">
                                                    <i class="bi bi-person"></i> Quản Lý Khách Hàng
                                                </a>
                                                <a href="{{ route('nguoi-dung.index') }}" class="menu-item {{ request()->routeIs('nguoi-dung.*') ? 'active' : '' }}">
                                                    <i class="bi bi-person-badge"></i> Quản Lý Nhân Sự
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Quản lý nhập bán -->
                                    <div class="accordion-item bg-transparent border-0">
                                        <h2 class="accordion-header" id="headingKhoHang">
                                            <button class="accordion-button collapsed bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKhoHang" aria-expanded="false" aria-controls="collapseKhoHang">
                                                <i class="bi bi-box-seam me-2"></i> Quản lý nhập bán
                                            </button>
                                        </h2>
                                        <div id="collapseKhoHang" class="accordion-collapse collapse" aria-labelledby="headingKhoHang">
                                            <div class="accordion-body p-0">
                                                <a href="{{ route('kho.index') }}" class="menu-item {{ request()->routeIs('kho.*') ? 'active' : '' }}">
                                                    <i class="bi bi-building-gear"></i> Quản Lý Kho
                                                </a>
                                                <a href="{{ route('phieu-nhap.index') }}" class="menu-item {{ request()->routeIs('phieu-nhap.*') ? 'active' : '' }}">
                                                    <i class="bi bi-file-earmark-plus"></i> Quản Lý Phiếu Nhập
                                                </a>
                                                <a href="{{ route('don-ban-le.index') }}" class="menu-item {{ request()->routeIs('don-ban-le.*') ? 'active' : '' }}">
                                                    <i class="bi bi-receipt"></i> Quản Lý Đơn Bán
                                                </a>
                                                <a href="{{ route('lo-thuoc.index') }}" class="menu-item {{ request()->routeIs('lo-thuoc.*') ? 'active' : '' }}">
                                                    <i class="bi bi-box2"></i> Quản Lý Lô
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Quản lý báo cáo -->
                                    <div class="accordion-item bg-transparent border-0">
                                        <h2 class="accordion-header" id="headingBaoCao">
                                            <button class="accordion-button collapsed bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBaoCao" aria-expanded="false" aria-controls="collapseBaoCao">
                                                <i class="bi bi-bar-chart-line me-2"></i> Quản lý Báo Cáo
                                            </button>
                                        </h2>
                                        <div id="collapseBaoCao" class="accordion-collapse collapse" aria-labelledby="headingBaoCao">
                                            <div class="accordion-body p-0">
                                                <a href="{{ route('bao-cao.lo-thuoc.index') }}" class="menu-item {{ request()->routeIs('bao-cao.lo-thuoc.*') ? 'active' : '' }}">
                                                    <i class="bi bi-box2"></i> Báo Cáo Lô Thuốc
                                                </a>
                                                <a href="{{ route('bao-cao.thuoc.index') }}" class="menu-item {{ request()->routeIs('bao-cao.thuoc.*') ? 'active' : '' }}">
                                                    <i class="bi bi-capsule"></i> Báo Cáo Thuốc
                                                </a>
                                                <a href="{{ route('bao-cao.kho.index') }}" class="menu-item {{ request()->routeIs('bao-cao.kho.*') ? 'active' : '' }}">
                                                    <i class="bi bi-building"></i> Báo Cáo Kho
                                                </a>
                                                <a href="{{ route('bao-cao.khach-hang.index') }}" class="menu-item {{ request()->routeIs('bao-cao.khach-hang.*') ? 'active' : '' }}">
                                                    <i class="bi bi-people"></i> Báo Cáo Khách Hàng
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('khach-hang.index') }}" class="menu-item {{ request()->routeIs('khach-hang.*') ? 'active' : '' }}">
                                <i class="bi bi-person"></i> Khách Hàng
                            </a>
                            <a href="{{ route('don-ban-le.index') }}" class="menu-item {{ request()->routeIs('don-ban-le.*') ? 'active' : '' }}">
                                <i class="bi bi-receipt"></i> Quản Lý Đơn Bán
                            </a>
                        @endif
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
                            </div>
                            <div class="notification-list">
                                <!-- Notifications will be dynamically inserted here -->
                            </div>
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
                            <li>
                                <hr class="dropdown-divider">
                            </li>
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
    <script src="{{ asset('js/thong-bao.js') }}"></script>
</body>

</html>