@extends('layouts.app')

@section('title', 'Riwayat Data - GESTI')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Riwayat Data Sensor
                    </h4>
                    <p class="text-secondary mb-0">Data pembacaan sensor dari ESP32</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>P4</th>
                                    <th>Air Tengah</th>
                                    <th>Air Utama</th>
                                    <th>Tandon</th>
                                    <th>Pompa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($readings as $reading)
                                    <tr>
                                        <td>{{ $reading->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ App\Models\SensorReading::getMoistureColor($reading->petak1_moisture) }}">
                                                {{ $reading->petak1_moisture }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ App\Models\SensorReading::getMoistureColor($reading->petak2_moisture) }}">
                                                {{ $reading->petak2_moisture }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ App\Models\SensorReading::getMoistureColor($reading->petak3_moisture) }}">
                                                {{ $reading->petak3_moisture }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ App\Models\SensorReading::getMoistureColor($reading->petak4_moisture) }}">
                                                {{ $reading->petak4_moisture }}%
                                            </span>
                                        </td>
                                        <td>{{ isset($reading->water_mid) ? round($reading->water_mid) : '-' }} cm</td>
                                        <td>{{ isset($reading->water_main) ? round($reading->water_main) : '-' }} cm</td>
                                        <td>{{ isset($reading->water_tank) ? round($reading->water_tank) : '-' }} cm</td>
                                        <td>
                                            <span class="badge {{ $reading->pump_status ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $reading->pump_status ? 'ON' : 'OFF' }}
                                            </span>
                                        </td>
                                        <td>{{ $reading->system_status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-secondary py-4">
                                            <i class="fas fa-database fa-2x mb-2 d-block"></i>
                                            Belum ada data sensor
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $readings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection