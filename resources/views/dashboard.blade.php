@extends('layouts.app')

@section('title', 'Dashboard - GESTI')

@section('content')
    <div class="row g-4">
        <!-- Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        Dashboard Monitoring
                    </h4>
                    <p class="text-secondary mb-0">Sistem Irigasi IoT - Desa Lape</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Mode Switch -->
                    <div class="mode-switch">
                        <button class="mode-btn {{ ($settings->mode ?? 'auto') === 'auto' ? 'active' : '' }}"
                            data-mode="auto" onclick="setMode('auto')">
                            <i class="fas fa-robot me-1"></i> Auto
                        </button>
                        <button class="mode-btn {{ ($settings->mode ?? 'auto') === 'manual' ? 'active' : '' }}"
                            data-mode="manual" onclick="setMode('manual')">
                            <i class="fas fa-hand-paper me-1"></i> Manual
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelembaban Tanah Cards -->
        <div class="col-12">
            <h6 class="text-secondary mb-3">
                <i class="fas fa-seedling me-2"></i>Kelembaban Tanah (4 Petak Sawah)
            </h6>
        </div>

        @php
            $petaks = [
                ['id' => 1, 'name' => 'Petak 1', 'value' => $latest->petak1_moisture ?? 0, 'gate' => $latest->gate1 ?? 0],
                ['id' => 2, 'name' => 'Petak 2', 'value' => $latest->petak2_moisture ?? 0, 'gate' => $latest->gate2 ?? 0],
                ['id' => 3, 'name' => 'Petak 3', 'value' => $latest->petak3_moisture ?? 0, 'gate' => $latest->gate3 ?? 0],
                ['id' => 4, 'name' => 'Petak 4', 'value' => $latest->petak4_moisture ?? 0, 'gate' => $latest->gate4 ?? 0],
            ];
        @endphp

        @foreach($petaks as $petak)
            <div class="col-md-6 col-lg-3">
                <div class="card moisture-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="text-secondary mb-1">{{ $petak['name'] }}</h6>
                                <div class="moisture-value" id="petak{{ $petak['id'] }}-value"
                                    style="color: var(--{{ App\Models\SensorReading::getMoistureColor($petak['value']) }})">
                                    {{ $petak['value'] }}<span class="fs-4">%</span>
                                </div>
                            </div>
                            <span class="status-badge {{ $petak['value'] <= 30 ? 'status-off' : 'status-on' }}"
                                id="petak{{ $petak['id'] }}-status">
                                {{ App\Models\SensorReading::getMoistureStatus($petak['value']) }}
                            </span>
                        </div>
                        <div class="moisture-bar mb-3">
                            <div class="moisture-bar-fill" id="petak{{ $petak['id'] }}-bar"
                                style="width: {{ $petak['value'] }}%; 
                                                        background: var(--{{ App\Models\SensorReading::getMoistureColor($petak['value']) }})">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-secondary">
                                <i class="fas fa-door-open me-1"></i>Gerbang
                            </small>
                            <span class="status-badge {{ $petak['gate'] > 0 ? 'status-on' : 'status-off' }}"
                                id="gate{{ $petak['id'] }}-status">
                                {{ $petak['gate'] > 0 ? 'BUKA' : 'TUTUP' }}
                            </span>
                        </div>
                        <!-- Manual Control Buttons -->
                        <div class="mt-3 manual-controls"
                            style="display: {{ ($settings->mode ?? 'auto') === 'manual' ? 'block' : 'none' }}">
                            <div class="btn-group w-100" role="group">
                                <button class="btn btn-sm btn-outline-success"
                                    onclick="controlGate({{ $petak['id'] }}, 90)">Buka</button>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="controlGate({{ $petak['id'] }}, 45)">Setengah</button>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="controlGate({{ $petak['id'] }}, 0)">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Level Air Section -->
        <div class="col-12 mt-4">
            <h6 class="text-secondary mb-3">
                <i class="fas fa-water me-2"></i>Level Air (Sensor Ultrasonik)
            </h6>
        </div>

        <!-- Saluran Tengah -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-secondary mb-3">
                        <i class="fas fa-arrows-alt-h me-2"></i>Saluran Tengah
                    </h6>
                    <div class="water-level">
                        <div class="water-value" id="water-mid">
                            {{ isset($latest->water_mid) ? round($latest->water_mid) : '-' }}
                        </div>
                        <small class="text-secondary">cm dari sensor</small>
                    </div>
                    <div class="text-center mt-2">
                        @php
                            $midLevel = $latest->water_mid ?? 999;
                            if ($midLevel >= 1 && $midLevel <= 3) {
                                $midStatus = 'Air Tinggi';
                                $midClass = 'status-on';
                            } elseif ($midLevel >= 4 && $midLevel <= 6) {
                                $midStatus = 'Air Sedang';
                                $midClass = 'status-warning';
                            } else {
                                $midStatus = 'Air Rendah';
                                $midClass = 'status-off';
                            }
                        @endphp
                        <span class="status-badge {{ $midClass }}" id="water-mid-status">
                            {{ $midStatus }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saluran Utama -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-secondary mb-3">
                        <i class="fas fa-arrows-alt-v me-2"></i>Saluran Utama
                    </h6>
                    <div class="water-level">
                        <div class="water-value" id="water-main">
                            {{ isset($latest->water_main) ? round($latest->water_main) : '-' }}
                        </div>
                        <small class="text-secondary">cm dari sensor</small>
                    </div>
                    <div class="text-center mt-2">
                        @php
                            $mainLevel = $latest->water_main ?? 999;
                            if ($mainLevel >= 1 && $mainLevel <= 3) {
                                $mainStatus = 'Air Tinggi';
                                $mainClass = 'status-on';
                            } elseif ($mainLevel >= 4 && $mainLevel <= 6) {
                                $mainStatus = 'Air Sedang';
                                $mainClass = 'status-warning';
                            } else {
                                $mainStatus = 'Air Rendah';
                                $mainClass = 'status-off';
                            }
                        @endphp
                        <span class="status-badge {{ $mainClass }}" id="water-main-status">
                            {{ $mainStatus }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tandon -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-secondary mb-3">
                        <i class="fas fa-database me-2"></i>Tandon Air
                    </h6>
                    <div class="water-level">
                        <div class="water-value" id="water-tank">
                            {{ isset($latest->water_tank) ? round($latest->water_tank) : '-' }}
                        </div>
                        <small class="text-secondary">cm dari sensor</small>
                    </div>
                    <div class="text-center mt-2">
                        @php
                            $tankLevel = $latest->water_tank ?? 999;
                            if ($tankLevel >= 1 && $tankLevel <= 3) {
                                $tankStatus = 'Air Tinggi';
                                $tankClass = 'status-on';
                            } elseif ($tankLevel >= 4 && $tankLevel <= 6) {
                                $tankStatus = 'Air Sedang';
                                $tankClass = 'status-warning';
                            } elseif ($tankLevel >= 7 && $tankLevel <= 10) {
                                $tankStatus = 'Air Rendah';
                                $tankClass = 'status-off';
                            } else {
                                $tankStatus = 'Perlu Isi Ulang';
                                $tankClass = 'status-off';
                            }
                        @endphp
                        <span class="status-badge {{ $tankClass }}" id="water-tank-status">
                            {{ $tankStatus }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Sistem & Kontrol -->
        <div class="col-md-6 mt-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-cogs me-2"></i>Status Sistem
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Status Sistem -->
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: var(--border-color)">
                                <small class="text-secondary d-block">Status</small>
                                <h5 class="mb-0" id="system-status">{{ $latest->system_status ?? 'Stabil' }}</h5>
                            </div>
                        </div>

                        <!-- Pompa -->
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: var(--border-color)">
                                <small class="text-secondary d-block">Pompa Air</small>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0" id="pump-status">
                                        {{ ($latest->pump_status ?? false) ? 'ON' : 'OFF' }}
                                    </h5>
                                    <span
                                        class="status-badge {{ ($latest->pump_status ?? false) ? 'status-on' : 'status-off' }}"
                                        id="pump-badge">
                                        <i class="fas fa-{{ ($latest->pump_status ?? false) ? 'check' : 'times' }}"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Gerbang Utama -->
                        <div class="col-12">
                            <div class="p-3 rounded" style="background: var(--border-color)">
                                <small class="text-secondary d-block">Gerbang Utama</small>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0" id="gate-main-status">
                                        {{ ($latest->gate_main ?? 0) > 0 ? 'BUKA' : 'TUTUP' }}
                                    </h5>
                                    <span
                                        class="status-badge {{ ($latest->gate_main ?? 0) > 0 ? 'status-on' : 'status-off' }}">
                                        {{ $latest->gate_main ?? 0 }}°
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Control for Pump & Main Gate -->
                    <div class="mt-3 manual-controls"
                        style="display: {{ ($settings->mode ?? 'auto') === 'manual' ? 'block' : 'none' }}">
                        <div class="row g-2">
                            <div class="col-6">
                                <button
                                    class="btn btn-control w-100 {{ ($settings->pump_command ?? false) ? 'btn-danger' : 'btn-success' }}"
                                    onclick="controlPump()">
                                    <i class="fas fa-power-off me-2"></i>
                                    Pompa {{ ($settings->pump_command ?? false) ? 'OFF' : 'ON' }}
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-control w-100 btn-primary" onclick="controlMainGate()">
                                    <i class="fas fa-door-open me-2"></i>
                                    Gerbang Utama
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div class="col-md-6 mt-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell me-2"></i>Notifikasi</span>
                    <span class="badge bg-danger" id="alerts-count">{{ $alertsCount ?? 0 }}</span>
                </div>
                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                    <div id="alerts-container">
                        @forelse($alerts as $alert)
                            <div class="alert-item {{ $alert->type === 'warning' ? 'alert-warning-item' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <i class="fas {{ $alert->icon }} me-2 text-{{ $alert->color }}"></i>
                                        <strong>{{ $alert->petak ?? 'Sistem' }}</strong>
                                    </div>
                                    <small class="text-secondary">{{ $alert->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 mt-1 small">{{ $alert->message }}</p>
                            </div>
                        @empty
                            <div class="text-center text-secondary py-4">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p class="mb-0">Tidak ada notifikasi baru</p>
                            </div>
                        @endforelse
                    </div>
                    @if($alertsCount > 0)
                        <button class="btn btn-outline-secondary btn-sm w-100 mt-2" onclick="markAllRead()">
                            <i class="fas fa-check me-1"></i> Tandai Semua Dibaca
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i>Grafik Kelembaban 24 Jam Terakhir
                </div>
                <div class="card-body">
                    <canvas id="moistureChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Chart initialization
        const ctx = document.getElementById('moistureChart').getContext('2d');
        const chartData = @json($chartData ?? []);

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => new Date(d.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })),
                datasets: [
                    {
                        label: 'Petak 1',
                        data: chartData.map(d => d.petak1_moisture),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Petak 2',
                        data: chartData.map(d => d.petak2_moisture),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Petak 3',
                        data: chartData.map(d => d.petak3_moisture),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Petak 4',
                        data: chartData.map(d => d.petak4_moisture),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#8b949e' }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: '#30363d' },
                        ticks: { color: '#8b949e' }
                    },
                    x: {
                        grid: { color: '#30363d' },
                        ticks: { color: '#8b949e' }
                    }
                }
            }
        });

        // Auto-refresh data every 5 seconds
        let currentSettings = @json($settings ?? ['mode' => 'auto', 'pump_command' => false]);

        async function refreshData() {
            try {
                document.getElementById('refresh-indicator').style.display = 'block';
                const data = await fetchAPI('/api/latest');

                if (data.sensor) {
                    // Update moisture values
                    for (let i = 1; i <= 4; i++) {
                        const value = data.sensor[`petak${i}_moisture`];
                        const color = value <= 30 ? 'danger' : (value <= 60 ? 'warning' : 'success');
                        const status = value <= 30 ? 'Kering' : (value <= 60 ? 'Lembab' : 'Basah');

                        document.getElementById(`petak${i}-value`).innerHTML = `${value}<span class="fs-4">%</span>`;
                        document.getElementById(`petak${i}-value`).style.color = `var(--${color})`;
                        document.getElementById(`petak${i}-bar`).style.width = `${value}%`;
                        document.getElementById(`petak${i}-bar`).style.background = `var(--${color})`;
                        document.getElementById(`petak${i}-status`).textContent = status;
                        document.getElementById(`petak${i}-status`).className = `status-badge ${value <= 30 ? 'status-off' : 'status-on'}`;

                        // Gate status
                        const gate = data.sensor[`gate${i}`];
                        document.getElementById(`gate${i}-status`).textContent = gate > 0 ? 'BUKA' : 'TUTUP';
                        document.getElementById(`gate${i}-status`).className = `status-badge ${gate > 0 ? 'status-on' : 'status-off'}`;
                    }

                    // Update water levels (rounded to whole numbers like LCD)
                    const waterMid = data.sensor.water_mid != null ? Math.round(data.sensor.water_mid) : null;
                    const waterMain = data.sensor.water_main != null ? Math.round(data.sensor.water_main) : null;
                    const waterTank = data.sensor.water_tank != null ? Math.round(data.sensor.water_tank) : null;

                    document.getElementById('water-mid').textContent = waterMid ?? '-';
                    document.getElementById('water-main').textContent = waterMain ?? '-';
                    document.getElementById('water-tank').textContent = waterTank ?? '-';

                    // Update water level status badges
                    function getWaterStatus(level) {
                        if (level >= 1 && level <= 3) return { text: 'Air Tinggi', class: 'status-on' };
                        if (level >= 4 && level <= 6) return { text: 'Air Sedang', class: 'status-warning' };
                        return { text: 'Air Rendah', class: 'status-off' };
                    }

                    function getTankStatus(level) {
                        if (level >= 1 && level <= 3) return { text: 'Air Tinggi', class: 'status-on' };
                        if (level >= 4 && level <= 6) return { text: 'Air Sedang', class: 'status-warning' };
                        if (level >= 7 && level <= 10) return { text: 'Air Rendah', class: 'status-off' };
                        return { text: 'Perlu Isi Ulang', class: 'status-off' };
                    }

                    if (waterMid != null) {
                        const midStatus = getWaterStatus(waterMid);
                        document.getElementById('water-mid-status').textContent = midStatus.text;
                        document.getElementById('water-mid-status').className = 'status-badge ' + midStatus.class;
                    }

                    if (waterMain != null) {
                        const mainStatus = getWaterStatus(waterMain);
                        document.getElementById('water-main-status').textContent = mainStatus.text;
                        document.getElementById('water-main-status').className = 'status-badge ' + mainStatus.class;
                    }

                    if (waterTank != null) {
                        const tankStatus = getTankStatus(waterTank);
                        document.getElementById('water-tank-status').textContent = tankStatus.text;
                        document.getElementById('water-tank-status').className = 'status-badge ' + tankStatus.class;
                    }

                    // Update system status
                    document.getElementById('system-status').textContent = data.sensor.system_status;
                    document.getElementById('pump-status').textContent = data.sensor.pump_status ? 'ON' : 'OFF';
                    document.getElementById('pump-badge').innerHTML = `<i class="fas fa-${data.sensor.pump_status ? 'check' : 'times'}"></i>`;
                    document.getElementById('pump-badge').className = `status-badge ${data.sensor.pump_status ? 'status-on' : 'status-off'}`;
                    document.getElementById('gate-main-status').textContent = data.sensor.gate_main > 0 ? 'BUKA' : 'TUTUP';
                }

                if (data.settings) {
                    currentSettings = data.settings;
                }

                document.getElementById('alerts-count').textContent = data.alerts_count || 0;
                updateLastUpdateTime();
            } catch (error) {
                console.error('Error refreshing data:', error);
            } finally {
                document.getElementById('refresh-indicator').style.display = 'none';
            }
        }

        // Refresh every 5 seconds
        setInterval(refreshData, 5000);

        // Control functions
        async function setMode(mode) {
            await fetchAPI('/api/control', 'POST', { mode });

            document.querySelectorAll('.mode-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.mode === mode);
            });

            document.querySelectorAll('.manual-controls').forEach(el => {
                el.style.display = mode === 'manual' ? 'block' : 'none';
            });

            currentSettings.mode = mode;
        }

        async function controlPump() {
            const newState = !currentSettings.pump_command;
            await fetchAPI('/api/control', 'POST', { pump_command: newState });
            currentSettings.pump_command = newState;
            refreshData();
        }

        async function controlGate(gateNum, angle) {
            const data = {};
            data[`gate${gateNum}_command`] = angle;
            await fetchAPI('/api/control', 'POST', data);
            refreshData();
        }

        async function controlMainGate() {
            const currentAngle = currentSettings.gate_main_command || 0;
            const newAngle = currentAngle > 0 ? 0 : 90;
            await fetchAPI('/api/control', 'POST', { gate_main_command: newAngle });
            currentSettings.gate_main_command = newAngle;
            refreshData();
        }

        async function markAllRead() {
            await fetchAPI('/api/alerts/read', 'POST', {});
            document.getElementById('alerts-count').textContent = '0';
            document.getElementById('alerts-container').innerHTML = `
                        <div class="text-center text-secondary py-4">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">Tidak ada notifikasi baru</p>
                        </div>
                    `;
        }

        // Initial update time
        updateLastUpdateTime();
    </script>
@endpush