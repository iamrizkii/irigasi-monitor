<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GESTI - Sistem Irigasi IoT')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #2563eb;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --light: #f8fafc;
            --lighter: #ffffff;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);
            min-height: 100vh;
            color: var(--text-primary);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--text-primary) !important;
        }

        .navbar-brand i {
            color: var(--success);
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary) !important;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: var(--shadow-sm);
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        /* Touch device hover effect */
        @media (hover: none) {
            .card:active {
                transform: translateY(-4px) scale(1.01);
                box-shadow: var(--shadow-md);
                border-color: var(--primary);
            }
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            border-radius: 20px 20px 0 0 !important;
        }

        .moisture-card {
            position: relative;
            overflow: hidden;
        }

        .moisture-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.5s;
        }

        .moisture-card:hover::before {
            left: 100%;
        }

        .moisture-value {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1;
        }

        .moisture-bar {
            height: 10px;
            border-radius: 5px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .moisture-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease;
            background: linear-gradient(90deg, var(--success), #4ade80);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .status-on {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #15803d;
            border: 1px solid #86efac;
        }

        .status-off {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .status-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
            border: 1px solid #fcd34d;
        }

        .water-level {
            background: linear-gradient(180deg, #e0f2fe 0%, #bae6fd 100%);
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            border: 1px solid #7dd3fc;
        }

        .water-value {
            font-size: 2.25rem;
            font-weight: 700;
            color: #0284c7;
        }

        .btn-control {
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: var(--shadow-sm);
        }

        .btn-control:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        .alert-item {
            background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
            border-left: 4px solid var(--danger);
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .alert-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-sm);
        }

        .alert-item.alert-warning-item {
            background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
            border-left-color: var(--warning);
        }

        .mode-switch {
            display: flex;
            background: #f1f5f9;
            border-radius: 14px;
            padding: 5px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .mode-btn {
            flex: 1;
            padding: 10px 20px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .mode-btn:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .mode-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--card-bg);
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 0.85rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            color: var(--text-primary);
        }

        .table {
            color: var(--text-primary);
        }

        .table thead th {
            border-bottom: 2px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: 600;
            background: #f8fafc;
        }

        .table tbody td {
            border-bottom-color: var(--border-color);
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.2s;
        }

        .table tbody tr:hover {
            background: #f0f9ff;
        }

        .pagination .page-link {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            margin: 0 3px;
            transition: all 0.3s;
        }

        .pagination .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .moisture-value {
                font-size: 2.5rem;
            }

            .card {
                border-radius: 16px;
            }

            .card:active {
                transform: scale(0.98);
            }
        }

        /* Title and heading styles */
        h4,
        h5,
        h6 {
            color: var(--text-primary);
        }

        .text-secondary {
            color: var(--text-secondary) !important;
        }

        /* Navbar toggler for mobile */
        .navbar-toggler {
            border-color: var(--border-color);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(30, 41, 59, 0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Badge adjustments */
        .badge {
            font-weight: 500;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-seedling me-2"></i>GESTI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="fas fa-chart-line me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('history') ? 'active' : '' }}"
                            href="{{ route('history') }}">
                            <i class="fas fa-history me-1"></i> Riwayat
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2" id="connection-status">
                        <i class="fas fa-circle me-1 pulse-animation"></i> Online
                    </span>
                    <span class="text-secondary small" id="last-update">-</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-4">
        @yield('content')
    </main>

    <!-- Refresh Indicator -->
    <div class="refresh-indicator" id="refresh-indicator" style="display: none;">
        <i class="fas fa-sync-alt fa-spin me-2"></i> Memperbarui data...
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // CSRF Token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Fetch utility
        async function fetchAPI(url, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            };
            if (data) options.body = JSON.stringify(data);

            const response = await fetch(url, options);
            return response.json();
        }

        // Update last update time
        function updateLastUpdateTime() {
            const now = new Date();
            document.getElementById('last-update').textContent =
                'Update: ' + now.toLocaleTimeString('id-ID');
        }
    </script>
    @stack('scripts')
</body>

</html>