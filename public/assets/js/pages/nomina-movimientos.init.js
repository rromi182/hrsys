/**
 * ============================================================
 * NOMINA MOVIMIENTOS - DataTable + Modal + Validaciones
 * ============================================================
 */

$(document).ready(function () {
    // --------------------------------------------------------
    // 1. INICIALIZAR DATATABLE
    // --------------------------------------------------------
    const tabla = $('#tablaMovimientos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.routes.nominaData,
            type: 'GET',
            data: function (d) {
                d.anio = $('#filtroAnio').val();
                d.mes = $('#filtroMes').val();
                d.empleado_id = $('#filtroEmpleado').val();
                d.tipo = $('#filtroTipo').val();
            },
            dataSrc: 'data',
        },
        columns: [
            { data: 'fecha', className: 'text-center' },
            { data: 'ci', className: 'text-center' },
            { data: 'empleado', className: 'fw-semibold' },
            {
                data: 'tipo_label',
                className: 'text-center',
                render: function (data) {
                    const badges = {
                        'Sueldo': '<span class="badge bg-primary-subtle text-primary">Sueldo</span>',
                        'Extra': '<span class="badge bg-info-subtle text-info">Extra</span>',
                        'Vale': '<span class="badge bg-warning-subtle text-warning">Vale</span>',
                        'Ausencia': '<span class="badge bg-danger-subtle text-danger">Ausencia</span>',
                        'Llegada Tardía': '<span class="badge bg-secondary-subtle text-secondary">Llegada Tardía</span>',
                        'Otros': '<span class="badge bg-dark-subtle text-dark">Otros</span>',
                    };
                    return badges[data] || data;
                }
            },
            {
                data: 'monto',
                className: 'text-end fw-semibold',
                render: function (data, type, row) {
                    const color = row.es_ingreso ? 'text-success' : 'text-danger';
                    return `<span class="${color}">${data}</span>`;
                }
            },
            { data: 'naturaleza', className: 'text-center' },
            { data: 'estado', className: 'text-center' },
            { data: 'observacion', className: 'text-muted small' },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function (data, type, row) {
                    if (row.estado_raw === 'anulado') {
                        return '<span class="text-muted small">Anulado</span>';
                    }
                    return `
                        <button class="btn btn-soft-danger btn-sm btn-anular" data-id="${row.id}" title="Anular">
                            <i class="ri-close-circle-line"></i>
                        </button>
                    `;
                }
            },
        ],
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron registros",
            emptyTable: "No hay datos disponibles",
            paginate: {
                first: "Primero",
                previous: '<i class="ri-arrow-left-s-line"></i>',
                next: '<i class="ri-arrow-right-s-line"></i>',
                last: "Último"
            }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    });

    // --------------------------------------------------------
    // 2. FILTROS
    // --------------------------------------------------------
    $('#btnFiltrar').on('click', function () {
        tabla.ajax.reload();
    });

    $('#filtroAnio, #filtroMes, #filtroEmpleado, #filtroTipo').on('change', function () {
        tabla.ajax.reload();
    });

    // --------------------------------------------------------
    // 3. COMPORTAMIENTO DEL MODAL - MONTO AUTOMÁTICO
    // --------------------------------------------------------
    const $selectEmpleado = $('#selectEmpleado');
    const $selectTipo = $('#selectTipoMovimiento');
    const $inputMonto = $('#inputMonto');
    const $inputObservacion = $('#inputObservacion');
    const $feedbackObs = $('#feedbackObservacion');
    const $errorEmpleado = $('#errorEmpleado');

    const MONTOS_FIJOS = {
        'sueldo': null,
        'extra': 500000,
        'vale': 0,
        'ausencia': 0,
        'llegada_tardia': 0,
        'otros': 0,
    };

    function actualizarMonto() {
        const tipo = $selectTipo.val();
        const empleadoOption = $selectEmpleado.find('option:selected');

        if (!tipo) {
            $inputMonto.val(0).prop('readonly', false);
            return;
        }

        if (tipo === 'sueldo') {
            const salario = empleadoOption.data('salario');
            if (salario && salario > 0) {
                $inputMonto.val(salario).prop('readonly', true);
            } else {
                $inputMonto.val(0).prop('readonly', false);
            }
        } else if (MONTOS_FIJOS[tipo] !== undefined) {
            if (MONTOS_FIJOS[tipo] === 0) {
                $inputMonto.val(0).prop('readonly', false);
            } else {
                $inputMonto.val(MONTOS_FIJOS[tipo]).prop('readonly', true);
            }
        }
    }

    $selectEmpleado.on('change', actualizarMonto);
    $selectTipo.on('change', function () {
        actualizarMonto();
        validarObservacion();
    });

    // --------------------------------------------------------
    // 4. VALIDACIÓN DE OBSERVACIÓN
    // --------------------------------------------------------
    function validarObservacion() {
        const tipo = $selectTipo.val();
        if (tipo === 'otros') {
            $inputObservacion.prop('required', true)
                .attr('placeholder', 'Ingrese la observación (obligatorio)');
            $('#labelObservacion').addClass('text-danger');
        } else {
            $inputObservacion.prop('required', false)
                .attr('placeholder', 'Requerido solo para OTROS');
            $('#labelObservacion').removeClass('text-danger');
            $inputObservacion.removeClass('is-invalid');
            $feedbackObs.hide();
        }
    }

    $inputObservacion.on('input', function () {
        const tipo = $selectTipo.val();
        if (tipo === 'otros' && $(this).val().trim() === '') {
            $(this).addClass('is-invalid');
            $feedbackObs.show();
        } else {
            $(this).removeClass('is-invalid');
            $feedbackObs.hide();
        }
    });

    // --------------------------------------------------------
    // 5. FUNCIÓN PARA CERRAR MODAL
    // --------------------------------------------------------
    function cerrarModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('show', 'flex', 'block');
            modal.style.display = 'none';
            
            const backdrops = document.querySelectorAll('.modal-backdrop, .fixed.inset-0.bg-black.bg-opacity-50');
            backdrops.forEach(function(el) {
                if (el.id !== modalId) {
                    el.remove();
                }
            });
            
            document.body.classList.remove('overflow-hidden', 'modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            const bodyBackdrop = document.querySelector('body > .fixed.inset-0');
            if (bodyBackdrop) {
                bodyBackdrop.remove();
            }
        }
    }

    // --------------------------------------------------------
    // 6. FUNCIÓN PARA MOSTRAR NOTIFICACIONES FLASHER
    // --------------------------------------------------------
    function showFlasherNotification(type, message) {
        // Usar el sistema de notificaciones de Flasher
        if (typeof window.flasher !== 'undefined') {
            // Si Flasher está disponible en el frontend
            window.flasher[type](message);
        } else {
            // Fallback: usar el sistema de notificaciones del proyecto
            // Esto intenta usar el sistema de notificaciones que ya tienes
            if (typeof toastr !== 'undefined') {
                // Si usas toastr
                if (type === 'success') {
                    toastr.success(message);
                } else {
                    toastr.error(message);
                }
            } else if (typeof notify !== 'undefined') {
                // Si usas notify
                notify(message, type);
            } else {
                // Último recurso: alert simple
                alert(message);
            }
        }
    }

// --------------------------------------------------------
// 7. GUARDAR MOVIMIENTO
// --------------------------------------------------------
$('#formNuevoMovimiento').on('submit', function (e) {
    e.preventDefault();

    // Validar empleado
    if (!$selectEmpleado.val()) {
        $errorEmpleado.removeClass('hidden');
        $selectEmpleado.addClass('border-red-500');
        return;
    } else {
        $errorEmpleado.addClass('hidden');
        $selectEmpleado.removeClass('border-red-500');
    }

    const tipo = $selectTipo.val();
    const observacion = $inputObservacion.val().trim();
    let montoVal = $inputMonto.val();

    if ((!montoVal || montoVal === '0') && tipo === 'sueldo') {
        const empleadoOption = $selectEmpleado.find('option:selected');
        const salario = empleadoOption.data('salario');
        if (salario && salario > 0) {
            $inputMonto.val(salario);
            montoVal = salario;
        }
    }

    if (tipo === 'otros' && observacion === '') {
        $inputObservacion.addClass('is-invalid').focus();
        $feedbackObs.show();
        return;
    }

    const $btn = $('#btnGuardarMovimiento');
    const $spinner = $('#spinnerGuardar');

    $btn.prop('disabled', true);
    $spinner.removeClass('d-none');

    const formData = new FormData(this);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    formData.append('empresa_id', 1);

    $.ajax({
        url: window.routes.nominaStore,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                // 1. Cerrar modal
                cerrarModal('modalNuevoMovimiento');
                
                // 2. Resetear formulario
                $('#formNuevoMovimiento')[0].reset();
                $inputMonto.val(0).prop('readonly', false);
                $inputObservacion.removeClass('is-invalid');
                $feedbackObs.hide();
                $errorEmpleado.addClass('hidden');
                $selectEmpleado.removeClass('border-red-500');
                
                // 3. Recargar la página para mostrar el flash
                // Esto mantiene la consistencia con el login
                window.location.reload();
                
            } else {
                // Mostrar error sin recargar
                showError(response.message || '❌ Error al guardar el movimiento.');
            }
        },
        error: function (xhr) {
            let message = '❌ Error al guardar el movimiento.';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                } else if (xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
            }
            showError(message);
        },
        complete: function () {
            $btn.prop('disabled', false);
            $spinner.addClass('d-none');
        }
    });
});

    // --------------------------------------------------------
    // 8. MANEJAR CIERRE DEL MODAL CON BOTÓN X
    // --------------------------------------------------------
    $('#modalNuevoMovimiento [data-modal-close]').on('click', function() {
        cerrarModal('modalNuevoMovimiento');
    });

    // Cerrar modal al hacer clic en el backdrop
    $('#modalNuevoMovimiento').on('click', function(e) {
        if (e.target === this) {
            cerrarModal('modalNuevoMovimiento');
        }
    });

    // --------------------------------------------------------
    // 9. ANULAR MOVIMIENTO
    // --------------------------------------------------------
    $('#tablaMovimientos tbody').on('click', '.btn-anular', function () {
        const id = $(this).data('id');

        if (confirm('¿Estás seguro de que deseas anular este movimiento?\nEsta acción no se puede deshacer.')) {
            $.ajax({
                url: window.routes.nominaAnular.replace(':id', id),
                method: 'POST',
                data: { 
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        showFlasherNotification('success', response.message);
                        tabla.ajax.reload(null, false);
                    } else {
                        showFlasherNotification('error', response.message);
                    }
                },
                error: function (xhr) {
                    showFlasherNotification('error', xhr.responseJSON?.message || 'No se pudo anular el movimiento.');
                }
            });
        }
    });
});