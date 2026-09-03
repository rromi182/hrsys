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
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            paginate: {
                previous: '<i class="ri-arrow-left-s-line"></i>',
                next: '<i class="ri-arrow-right-s-line"></i>'
            },
            info: 'Mostrando _START_ a _END_ de _TOTAL_ resultados',
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
            $inputObservacion.closest('.mb-3').find('label').addClass('text-danger');
        } else {
            $inputObservacion.prop('required', false)
                .attr('placeholder', 'Requerido solo para OTROS');
            $inputObservacion.closest('.mb-3').find('label').removeClass('text-danger');
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
    // 5. GUARDAR MOVIMIENTO
    // --------------------------------------------------------
    $('#formNuevoMovimiento').on('submit', function (e) {
        e.preventDefault();

        // Asegurar que el monto tenga un valor
        const tipo = $selectTipo.val();
        const observacion = $inputObservacion.val().trim();
        let montoVal = $inputMonto.val();

        // Si el monto está vacío o es 0 y es sueldo, intentar obtener el salario
        if ((!montoVal || montoVal === '0') && tipo === 'sueldo') {
            const empleadoOption = $selectEmpleado.find('option:selected');
            const salario = empleadoOption.data('salario');
            if (salario && salario > 0) {
                $inputMonto.val(salario);
                montoVal = salario;
            }
        }

        // Validación de observación para "otros"
        if (tipo === 'otros' && observacion === '') {
            $inputObservacion.addClass('is-invalid').focus();
            $feedbackObs.show();
            return;
        }

        // Validación de empleado
        if (!$selectEmpleado.val()) {
            $('#errorEmpleado').removeClass('hidden');
            $selectEmpleado.addClass('border-red-500');
            return;
        } else {
            $('#errorEmpleado').addClass('hidden');
            $selectEmpleado.removeClass('border-red-500');
        }

        const $btn = $('#btnGuardarMovimiento');
        const $spinner = $('#spinnerGuardar');

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');

        // Crear FormData
        const formData = new FormData(this);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Asegurar que el monto se envía correctamente
        formData.set('monto', montoVal || 0);

        // Agregar campos adicionales que el backend espera
        formData.append('empresa_id', 1); // O el ID de la empresa del usuario

        $.ajax({
            url: window.routes.nominaStore,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    $('#modalNuevoMovimiento').modal('hide');
                    $('#formNuevoMovimiento')[0].reset();
                    $inputMonto.val(0).prop('readonly', false);
                    $inputObservacion.removeClass('is-invalid');
                    $feedbackObs.hide();
                    tabla.ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al guardar',
                    });
                }
            },
            error: function (xhr) {
                let message = 'Ocurrió un error al guardar.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join('<br>');
                    } else if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: message,
                });
            },
            complete: function () {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
            }
        });
    });

    // Resetear formulario al cerrar modal
    $('#modalNuevoMovimiento').on('hidden.bs.modal', function () {
        $('#formNuevoMovimiento')[0].reset();
        $inputMonto.val(0).prop('readonly', false);
        $inputObservacion.removeClass('is-invalid');
        $feedbackObs.hide();
        $('#errorEmpleado').addClass('hidden');
        $selectEmpleado.removeClass('border-red-500');
    });

    // --------------------------------------------------------
    // 6. ANULAR MOVIMIENTO
    // --------------------------------------------------------
    $('#tablaMovimientos tbody').on('click', '.btn-anular', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Anular movimiento?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: window.routes.nominaAnular.replace(':id', id),
                    method: 'POST',
                    data: { 
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'POST'
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Anulado',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false,
                            });
                            tabla.ajax.reload();
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'No se pudo anular el movimiento.',
                        });
                    }
                });
            }
        });
    });
});