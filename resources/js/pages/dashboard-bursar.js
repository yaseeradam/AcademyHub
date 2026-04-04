import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function() {
    if (!window.bursarDashboardData) {
        console.warn('Bursar dashboard data not found');
        return;
    }

    const { monthlyRevenue, paymentMethods } = window.bursarDashboardData;

    // Revenue Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart');
    if (revenueTrendCtx) {
        new Chart(revenueTrendCtx, {
            type: 'line',
            data: {
                labels: monthlyRevenue.map(item => item.month),
                datasets: [{
                    label: 'Revenue',
                    data: monthlyRevenue.map(item => item.revenue),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#10B981',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const currency = document.querySelector('[data-currency]')?.dataset.currency || '₦';
                                return `Revenue: ${currency}${context.parsed.y.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#64748B',
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // Payment Methods Chart
    const paymentMethodsCtx = document.getElementById('paymentMethodsChart');
    if (paymentMethodsCtx && paymentMethods.length > 0) {
        const colors = [
            '#3B82F6', // Blue
            '#10B981', // Emerald
            '#F59E0B', // Amber
            '#EF4444', // Red
            '#8B5CF6', // Purple
            '#06B6D4', // Cyan
            '#84CC16', // Lime
            '#F97316'  // Orange
        ];

        new Chart(paymentMethodsCtx, {
            type: 'doughnut',
            data: {
                labels: paymentMethods.map(method => method.method),
                datasets: [{
                    data: paymentMethods.map(method => method.total),
                    backgroundColor: colors.slice(0, paymentMethods.length),
                    borderWidth: 0,
                    hoverBorderWidth: 2,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3B82F6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const currency = document.querySelector('[data-currency]')?.dataset.currency || '₦';
                                const method = paymentMethods[context.dataIndex];
                                return [
                                    `${context.label}: ${currency}${context.parsed.toLocaleString()}`,
                                    `Transactions: ${method.count}`
                                ];
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 1000
                }
            }
        });
    } else if (paymentMethodsCtx) {
        // Show empty state
        paymentMethodsCtx.parentElement.innerHTML = `
            <div class="flex items-center justify-center h-full text-slate-500">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="mt-2 text-sm">No payment data available</p>
                </div>
            </div>
        `;
    }
});