@props(['type' => 'line', 'data' => [], 'height' => 300])

<div class="card p-6">
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-gray-800">{{ $title ?? 'Chart' }}</h3>
        @if ($subtitle ?? false)
            <p class="text-xs text-gray-600 mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="relative" style="height: {{ $height }}px;">
        <canvas id="chart-{{ $type }}-{{ uniqid() }}" class="w-full h-full"></canvas>
    </div>

    <!-- Chart Legend -->
    @if ($legend ?? false)
        <div class="mt-4 flex flex-wrap gap-4 justify-center">
            @foreach ($legend as $item)
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $item['color'] }}"></div>
                    <span class="text-xs text-gray-600">{{ $item['label'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Enhanced Chart.js configuration
        Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
        Chart.defaults.color = '#6b7280';
        Chart.defaults.borderColor = '#e5e7eb';



        // Initialize charts
        const charts = document.querySelectorAll('canvas[id^="chart-"]');
        charts.forEach(canvas => {
            const ctx = canvas.getContext('2d');
            const chartType = canvas.id.includes('line') ? 'line' :
                canvas.id.includes('bar') ? 'bar' :
                    canvas.id.includes('pie') ? 'pie' : 'doughnut';

            // Sample data - replace with actual data
            const data = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sample Data',
                    data: [65, 59, 80, 81, 56, 55],
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.12)',
                        'rgba(37, 99, 235, 0.12)',
                        'rgba(37, 99, 235, 0.12)',
                        'rgba(37, 99, 235, 0.12)',
                        'rgba(37, 99, 235, 0.12)',
                        'rgba(37, 99, 235, 0.12)'
                    ],
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            };

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12
                    }
                },
                scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            };

            new Chart(ctx, {
                type: chartType,
                data: data,
                options: options
            });
        });
    });

    // Real-time chart updates
    window.updateChart = function (chartId, newData) {
        const chart = Chart.getChart(chartId);
        if (chart) {
            chart.data.datasets[0].data = newData;
            chart.update('active');
        }
    };

    // Add chart to dashboard
    window.addDashboardChart = function (containerId, type, data, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const canvas = document.createElement('canvas');
        canvas.id = 'chart-' + type + '-' + Date.now();
        container.innerHTML = '';
        container.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: type,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                ...options
            }
        });
    };
</script>