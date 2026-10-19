(() => {
    const toggle = (element, visible) => {
        if (element) element.classList.toggle('d-none', !visible);
    };

    const filterList = (input, container) => {
        if (!input || !container) return;

        const term = input.value.trim().toLocaleLowerCase('es-PE');
        const items = [...container.querySelectorAll('[data-search-item]')];
        let visible = 0;

        items.forEach((item) => {
            const match = (item.dataset.search || item.textContent)
                .toLocaleLowerCase('es-PE')
                .includes(term);
            toggle(item, match);
            if (match) visible += 1;
        });

        const empty = container.querySelector('[data-search-empty]');
        toggle(empty, items.length > 0 && visible === 0);
    };

    const setupLiveSearch = () => {
        document.querySelectorAll('[data-live-search]').forEach((input) => {
            const container = document.querySelector(input.dataset.liveSearch);
            input.addEventListener('input', () => filterList(input, container));
        });

        const buscadorEstudiante = document.getElementById('buscar_estudiante');
        const selectorEstudiante = document.getElementById('id_estudiante');
        if (buscadorEstudiante && selectorEstudiante) {
            buscadorEstudiante.addEventListener('input', () => {
                const termino = buscadorEstudiante.value.trim().toLocaleLowerCase('es-PE');
                [...selectorEstudiante.options].forEach((option, index) => {
                    if (index === 0) return;
                    option.hidden = !option.textContent.toLocaleLowerCase('es-PE').includes(termino);
                });
            });
        }
    };

    const setupDateModes = () => {
        const select = document.getElementById('modo_periodo');
        if (!select) return;

        const update = () => {
            const isRange = select.value === 'rango';
            document.querySelectorAll('.report-date-range').forEach((element) => toggle(element, isRange));
            document.querySelectorAll('.report-date-single').forEach((element) => toggle(element, !isRange));
        };

        select.addEventListener('change', update);
        update();
    };

    const setupPerformanceFilters = () => {
        const form = document.querySelector('[data-period-filter]');
        if (!form) return;

        const filtro = form.querySelector('#filtro');
        const tipoPeriodo = form.querySelector('#tipo_periodo');
        const periodo = form.querySelector('#id_periodo');
        const tipoReporte = form.querySelector('#tipo_reporte');
        const estudiante = form.querySelector('#id_estudiante');

        const update = () => {
            const isPeriod = filtro.value === 'periodo';
            const isCustom = filtro.value === 'personalizado';
            const isStudent = tipoReporte.value === 'estudiante';

            toggle(form.querySelector('[data-period-type]'), isPeriod);
            toggle(form.querySelector('[data-period-select]'), isPeriod);
            toggle(form.querySelector('[data-date-start]'), isCustom);
            toggle(form.querySelector('[data-date-end]'), isCustom);
            toggle(form.querySelector('[data-student-filter]'), isStudent);

            tipoPeriodo.disabled = !isPeriod;
            periodo.disabled = !isPeriod;
            estudiante.disabled = !isStudent;
            estudiante.required = isStudent;

            let matchingPeriods = 0;
            [...periodo.options].forEach((option, index) => {
                if (index === 0) return;
                const matches = option.dataset.periodType === tipoPeriodo.value;
                option.hidden = !matches;
                if (matches) matchingPeriods += 1;
            });
            const seleccionActual = periodo.options[periodo.selectedIndex];
            if (periodo.value && seleccionActual && seleccionActual.hidden) periodo.value = '';
            periodo.required = isPeriod;
            const submit = form.querySelector('[type="submit"]');
            if (submit) submit.disabled = isPeriod && matchingPeriods === 0;
        };

        [filtro, tipoPeriodo, tipoReporte].forEach((element) => element.addEventListener('change', update));
        update();
    };

    const makeLineChart = (id, labels, datasets, options = {}) => {
        const canvas = document.getElementById(id);
        if (!canvas || !window.Chart) return;

        const hasValues = datasets.some((dataset) => dataset.data.some((value) => value !== null && value > 0));
        toggle(canvas, hasValues);
        toggle(document.querySelector(`[data-empty-for="${id}"]`), !hasValues);
        if (!hasValues) return;

        new window.Chart(canvas, {
            type: options.type || 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' },
                tooltips: { mode: 'index', intersect: false },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            ...(options.max ? { max: options.max } : {}),
                            ...(options.stepSize ? { stepSize: options.stepSize } : {}),
                        },
                    }],
                },
            },
        });
    };

    const setupCharts = () => {
        const container = document.querySelector('[data-report-chart]');
        if (!container || !window.Chart) return;

        let data;
        try {
            data = JSON.parse(container.dataset.reportChart);
        } catch (error) {
            console.error('No se pudieron leer los datos del reporte de rendimiento.', error);
            return;
        }

        const labels = data.labels || [];
        const color = {
            'Asistió': '#1cbb8c',
            'Tardanza': '#fcb92c',
            'Falta': '#dc3545',
            'Justificado': '#17a2b8',
        };

        makeLineChart(
            'grafico-asistencia',
            labels,
            Object.keys(color).map((estado) => ({
                label: estado,
                data: (data.asistencia || []).map((registro) => registro[estado] || 0),
                borderColor: color[estado],
                backgroundColor: color[estado],
                fill: false,
            }))
        );

        makeLineChart(
            'grafico-tareas',
            (data.tareas || []).map((registro) => registro.fecha),
            [{
                label: 'Entregas cumplidas (%)',
                data: (data.tareas || []).map((registro) => registro.porcentaje),
                borderColor: '#3b7ddd',
                backgroundColor: 'rgba(59, 125, 221, .15)',
                fill: true,
            }],
            { max: 100, stepSize: 20 }
        );

        makeLineChart(
            'grafico-notas-cuantitativas',
            labels,
            [{
                label: 'Promedio cuantitativo (0–20)',
                data: (data.notas || []).map((registro) => registro.promedio_cuantitativo),
                borderColor: '#3b7ddd',
                backgroundColor: 'rgba(59, 125, 221, .15)',
                spanGaps: false,
                fill: true,
            }],
            { max: 20, stepSize: 5 }
        );

        makeLineChart(
            'grafico-notas-cualitativas',
            labels,
            ['AD', 'A', 'B', 'C'].map((nivel, index) => ({
                label: nivel,
                data: (data.notas || []).map((registro) => registro[nivel] || 0),
                borderColor: ['#1cbb8c', '#3b7ddd', '#fcb92c', '#dc3545'][index],
                backgroundColor: ['#1cbb8c', '#3b7ddd', '#fcb92c', '#dc3545'][index],
                fill: false,
            }))
        );
    };

    const showReportsTabFromHash = () => {
        if (window.location.hash !== '#reportes') return;
        const tab = document.getElementById('reportes-tab');
        if (tab) tab.click();
    };

    document.addEventListener('DOMContentLoaded', () => {
        setupLiveSearch();
        setupDateModes();
        setupPerformanceFilters();
        setupCharts();
        showReportsTabFromHash();
    });
})();
