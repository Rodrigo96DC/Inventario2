document.addEventListener('DOMContentLoaded', function () {

    const isDark    = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,.1)' : 'rgba(0,0,0,.08)';
    const textColor = isDark ? '#adb5bd' : '#495057';

    Chart.defaults.color       = textColor;
    Chart.defaults.font.family = 'Arial, sans-serif';
    Chart.defaults.font.size   = 12;

    // Destruir charts existentes antes de crear nuevos
    ['chart-tipo', 'chart-estado', 'chart-funcionarios', 'chart-asignados'].forEach(id => {
        const chart = Chart.getChart(id);
        if (chart) chart.destroy();
    });


   
  // ── Equipos por región (barras horizontales) ──────────────────────────────
new Chart(document.getElementById('chart-tipo'), {
    type: 'bar',
    data: {
        labels: porRegionLabels,
        datasets: [{
            label: 'Equipos asignados',
            data: porRegionDatos,
            backgroundColor: 'rgba(37,99,235,.7)',
            borderColor: '#2563eb',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } },
            y: { grid: { display: false }, ticks: { color: textColor } },
        }
    }
});

    // ── Equipos por estado (dona) ─────────────────────────────────────────────
    new Chart(document.getElementById('chart-estado'), {
        type: 'doughnut',
        data: {
            labels: porEstadoLabels,
            datasets: [{
                data: porEstadoDatos,
                backgroundColor: porEstadoColores,
                borderWidth: 2,
                borderColor: isDark ? '#212529' : '#ffffff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, padding: 16 } }
            }
        }
    });

    // ── Funcionarios (dona) ───────────────────────────────────────────────────
    new Chart(document.getElementById('chart-funcionarios'), {
        type: 'doughnut',
        data: {
            labels: ['Activos', 'Inactivos'],
            datasets: [{
                data: [funcActivos, funcInactivos],
                backgroundColor: ['#198754', '#6c757d'],
                borderWidth: 2,
                borderColor: isDark ? '#212529' : '#ffffff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, padding: 16 } }
            }
        }
    });

    // ── Asignados vs sin asignar (dona) ───────────────────────────────────────
    new Chart(document.getElementById('chart-asignados'), {
        type: 'doughnut',
        data: {
            labels: ['Asignados', 'Sin asignar'],
            datasets: [{
                data: [equipAsignados, equipSinAsignar],
                backgroundColor: ['#2563eb', '#adb5bd'],
                borderWidth: 2,
                borderColor: isDark ? '#212529' : '#ffffff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, padding: 16 } }
            }
        }
    });



});