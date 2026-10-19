document.addEventListener("DOMContentLoaded", function () {
    
    // ==========================================
    // 1. NAVEGACIÓN ENTRE VISTAS (Lista <-> Calificación)
    // ==========================================
    const listView = document.getElementById('tareas-list-view');
    const gradingView = document.getElementById('tareas-grading-view');
    const btnVolver = document.getElementById('btn-volver-tareas');
    const gradingTitle = document.getElementById('grading-task-title');
    const gradingType = document.getElementById('grading-task-type');

    // Al hacer clic en el título de una tarjeta
    document.querySelectorAll('.tarea-card-title').forEach(title => {
        title.addEventListener('click', function () {
            const taskName = this.textContent;
            const taskType = this.getAttribute('data-tipo');
            
            gradingTitle.textContent = `Calificando: ${taskName}`;
            gradingType.textContent = taskType;
            
            // Configurar inputs de nota según el tipo de evaluación
            configurarInputsNota(taskType);

            listView.classList.add('d-none');
            gradingView.classList.remove('d-none');
            
            // Re-inicializar iconos de Feather si es necesario
            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    // Botón volver
    btnVolver.addEventListener('click', function () {
        gradingView.classList.add('d-none');
        listView.classList.remove('d-none');
    });

    // ==========================================
    // 2. MODAL: ASIGNACIÓN MÚLTIPLE CON FECHAS DINÁMICAS
    // ==========================================
    const aulaCheckboxes = document.querySelectorAll('.aula-checkbox');

    aulaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            // Encontrar el contenedor de fechas hermano más cercano
            const fechasContainer = this.closest('.form-check').querySelector('.aula-fechas-container');
            const fechaAsignacion = fechasContainer.querySelector('.fecha-asignacion');
            const fechaEntrega = fechasContainer.querySelector('.fecha-entrega');

            if (this.checked) {
                fechasContainer.classList.remove('d-none');
                // Establecer fecha de hoy por defecto para asignación
                if (!fechaAsignacion.value) {
                    fechaAsignacion.valueAsDate = new Date();
                }
            } else {
                fechasContainer.classList.add('d-none');
                fechaAsignacion.value = '';
                fechaEntrega.value = '';
            }
        });
    });

    // ==========================================
    // 3. LÓGICA DE LA TABLA DE CALIFICACIÓN
    // ==========================================
    
    // Función para habilitar/deshabilitar nota según el estado
    function toggleNotaInput(row) {
        const cumplio = row.querySelector('.estado-cumplio').checked;
        const inputNum = row.querySelector('.input-nota');
        const inputSelect = row.querySelector('.input-nota-select');
        
        // Determinar cuál input está visible
        const inputActivo = inputNum.classList.contains('d-none') ? inputSelect : inputNum;

        if (cumplio) {
            inputActivo.disabled = false;
            inputActivo.required = true;
        } else {
            inputActivo.disabled = true;
            inputActivo.required = false;
            inputActivo.value = ''; // Limpiar nota si cambia a "No cumplió"
        }
    }

    // Escuchar cambios en los radio buttons de estado de cada estudiante
    document.querySelectorAll('.student-row').forEach(row => {
        const radios = row.querySelectorAll('input[type="radio"]');
        radios.forEach(radio => {
            radio.addEventListener('change', () => toggleNotaInput(row));
        });
    });

    // Botón "Marcar todos como Cumplió"
    document.getElementById('btn-marcar-todos-cumplio').addEventListener('click', function () {
        document.querySelectorAll('.student-row').forEach(row => {
            const radioCumplio = row.querySelector('.estado-cumplio');
            radioCumplio.checked = true;
            toggleNotaInput(row);
        });
    });

    // Botón Guardar (Simulación)
    document.getElementById('btn-guardar-tareas').addEventListener('click', function () {
        // Aquí iría la lógica de envío AJAX o submit del formulario
        alert('Registro de tareas guardado exitosamente (Simulación).');
    });

    // ==========================================
    // 4. FUNCIONES AUXILIARES
    // ==========================================
    function configurarInputsNota(tipo) {
        document.querySelectorAll('.student-row').forEach(row => {
            const inputNum = row.querySelector('.input-nota');
            const inputSelect = row.querySelector('.input-nota-select');
            
            if (tipo === 'Cualitativa') {
                inputNum.classList.add('d-none');
                inputSelect.classList.remove('d-none');
            } else {
                inputNum.classList.remove('d-none');
                inputSelect.classList.add('d-none');
            }
            
            // Re-evaluar estado actual para habilitar/deshabilitar el input correcto
            toggleNotaInput(row);
        });
    }

    // Inicializar iconos Feather al cargar
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});