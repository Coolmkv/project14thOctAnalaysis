import './bootstrap';

document.addEventListener('DOMContentLoaded', async () => {
    const tables = document.querySelectorAll('[data-datatable]');
    if (tables.length > 0) {
        const { default: DataTable } = await import('datatables.net-dt');
        await import('datatables.net-dt/css/dataTables.dataTables.css');

        tables.forEach((table) => {
            const searchUrl = table.dataset.source;
            if (!searchUrl) {
                return;
            }

            new DataTable(table, {
                processing: true,
                serverSide: true,
                ajax: searchUrl,
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                order: [[0, 'asc']],
                columns: [...table.querySelectorAll('thead th')].map((header) => ({
                    data: header.dataset.column,
                    render: DataTable.render.text(),
                    orderable: header.dataset.sortable !== 'false',
                })),
                language: {
                    emptyTable: 'No records to display.',
                    processing: 'Loading records…',
                    search: 'Search:',
                },
            });
        });
    }

    const charts = document.querySelectorAll('[data-admin-chart]');
    if (charts.length > 0) {
        const { default: Chart } = await import('chart.js/auto');

        charts.forEach((canvas) => {
            const chartData = JSON.parse(canvas.dataset.chart || '{}');
            const isInstituteChart = canvas.dataset.adminChart === 'institutes';

            new Chart(canvas, {
                type: isInstituteChart ? 'bar' : 'line',
                data: {
                    labels: chartData.labels || [],
                    datasets: [{
                        data: chartData.values || [],
                        borderColor: '#4f46e5',
                        backgroundColor: isInstituteChart
                            ? ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#f43f5e']
                            : 'rgba(79, 70, 229, 0.12)',
                        borderWidth: 2,
                        fill: !isInstituteChart,
                        tension: 0.38,
                        pointRadius: isInstituteChart ? 0 : 4,
                        pointHoverRadius: 6,
                        borderRadius: isInstituteChart ? 7 : 0,
                        maxBarThickness: 32,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { displayColors: false, padding: 10 },
                    },
                    scales: isInstituteChart
                        ? {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { color: '#64748b', precision: 0 },
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: '#64748b', autoSkip: false },
                            },
                        }
                        : {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', maxRotation: 0 },
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { color: '#64748b', precision: 0 },
                            },
                        },
                    ...(isInstituteChart ? { indexAxis: 'y' } : {}),
                },
            });
        });
    }

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
});
