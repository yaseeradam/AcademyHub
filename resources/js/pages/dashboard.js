import Chart from 'chart.js/auto';

const data = window.dashboardData || {};

// ── Management Value Line Chart ──────────────────────────────────────────────
(function () {
    const canvas = document.getElementById('managementValueChart');
    if (!canvas) return;

    const attendance = data.attendance || [];
    const labels     = attendance.map(d => d.label);
    const present    = attendance.map(d => d.present);
    const absent     = attendance.map(d => d.absent);

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Present',
                    data: present,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.12)',
                    tension: 0.45,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#f97316',
                    borderWidth: 2.5,
                },
                {
                    label: 'Absent',
                    data: absent,
                    borderColor: '#a78bfa',
                    backgroundColor: 'rgba(167,139,250,0.10)',
                    tension: 0.45,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#a78bfa',
                    borderWidth: 2.5,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: {
                    grid: { color: 'rgba(226,232,240,0.8)' },
                    ticks: { font: { size: 11 } },
                    beginAtZero: true,
                },
            },
        },
    });
})();

// ── Gender Donut Chart ───────────────────────────────────────────────────────
(function () {
    const canvas = document.getElementById('genderDonutChart');
    if (!canvas) return;

    const boys  = data.boys  || 0;
    const girls = data.girls || 0;
    const total = boys + girls;

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: total > 0 ? [boys, girls] : [1, 1],
                backgroundColor: ['#fb923c', '#8b5cf6'],
                borderWidth: 0,
                hoverOffset: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const val = ctx.raw;
                            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` ${ctx.label}: ${val} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });
})();

// ── Subject Task Horizontal Bar Chart ───────────────────────────────────────
(function () {
    const canvas = document.getElementById('subjectTaskChart');
    if (!canvas) return;

    const subjects = data.subjects || [];
    const labels   = subjects.map(s => s.name);
    const values   = subjects.map(s => s.avg);

    const palette = ['#fb923c', '#a78bfa', '#34d399', '#60a5fa', '#f472b6'];

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Avg Score',
                data: values,
                backgroundColor: labels.map((_, i) => palette[i % palette.length]),
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 18,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(226,232,240,0.8)' },
                    ticks: { font: { size: 11 } },
                    beginAtZero: true,
                    max: 100,
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } },
                },
            },
        },
    });
})();
