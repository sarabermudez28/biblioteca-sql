//prestamos.js

// ================================
// ABRIR MODAL
// ================================
btnAgregar.addEventListener("click", function () {
    // Usamos 'flex' en lugar de 'block' para que el centrado del CSS funcione de inmediato
    modalPrestamo.style.display = "flex";

    // Enfocar DNI al abrir
    document.getElementById("dni_alumno").focus();
});

// Y al cerrar, lo regresas a "none":
btnCerrarModal.addEventListener("click", function () {
    modalPrestamo.style.display = "none";
});

modalPrestamo.addEventListener("click", function (e) {
    if (e.target === modalPrestamo) {
        modalPrestamo.style.display = "none";
    }
});

// ================================
// MODAL VER DETALLES DE PRÉSTAMO
// ================================
const modalDetalles = document.getElementById('modalVerDetallesPrestamo');
const btnCerrarDetalles = document.getElementById('btnCerrarModalDetalles');
const btnCerrarDetallesBtn = document.getElementById('btnCerrarModalDetallesBtn');

document.querySelectorAll('.btn-ver-detalle').forEach(button => {
    button.addEventListener("click", function () {
        // Rellenar información en el modal
        document.getElementById('ver_id_prestamo').textContent = this.getAttribute('data-id');
        document.getElementById('ver_id_material').textContent = this.getAttribute('data-materialid');
        document.getElementById('ver_nombre_material').textContent = this.getAttribute('data-materialnombre');
        document.getElementById('ver_categoria').textContent = this.getAttribute('data-categoria');
        document.getElementById('ver_atributo_detalle').textContent = this.getAttribute('data-detalle');
        document.getElementById('ver_estado_material').textContent = this.getAttribute('data-estadomaterial');
        document.getElementById('ver_usuario').textContent = this.getAttribute('data-usuario');
        document.getElementById('ver_dni_usuario').textContent = this.getAttribute('data-dniusuario');
        document.getElementById('ver_dni_bibliotecario').textContent = this.getAttribute('data-dnibibliotecario');
        document.getElementById('ver_fecha_registro').textContent = this.getAttribute('data-fecharegistro');
        document.getElementById('ver_fecha_limite').textContent = this.getAttribute('data-fechalimite');
        document.getElementById('ver_fecha_devolucion').textContent = this.getAttribute('data-fechadevolucion');
        
        const estadoPrestamo = this.getAttribute('data-estado');
        const badgeEstado = document.getElementById('ver_estado_prestamo');
        badgeEstado.textContent = estadoPrestamo;
        badgeEstado.className = 'badge ' + (estadoPrestamo === 'Devuelto' ? 'bg-success' : 'bg-danger');

        // Mostrar usando flex (igual que tu modal de nuevo préstamo)
        if (modalDetalles) {
            modalDetalles.style.display = "flex";
        }
    });
});

const cerrarModalDetalles = function () {
    if (modalDetalles) modalDetalles.style.display = "none";
};

if (btnCerrarDetalles) btnCerrarDetalles.addEventListener("click", cerrarModalDetalles);
if (btnCerrarDetallesBtn) btnCerrarDetallesBtn.addEventListener("click", cerrarModalDetalles);

if (modalDetalles) {
    modalDetalles.addEventListener("click", function (e) {
        if (e.target === modalDetalles) {
            modalDetalles.style.display = "none";
        }
    });
}

// ================================
// MODAL SEMÁFORO DE CONDUCTA
// ================================
const modalSemaforo = document.getElementById('modalSemaforoGeneral');
const btnCerrarSemaforo = document.getElementById('btnCerrarSemaforo');
const btnCancelarSemaforo = document.getElementById('btnCancelarSemaforo');

// Abrir modal al hacer clic en cualquier botón de devolución con semáforo
document.querySelectorAll('.btn-abrir-semaforo').forEach(button => {
    button.addEventListener("click", function () {
        const idPrestamo = this.getAttribute('data-idprestamo');
        
        // Asignar el ID al input oculto
        const inputId = document.getElementById('input_id_prestamo');
        if (inputId) {
            inputId.value = idPrestamo;
        }

        // Mostrar con flex (mismo método que tus otros modales)
        if (modalSemaforo) {
            modalSemaforo.style.display = "flex";
        }
    });
});

// Función para cerrar el modal del semáforo
const cerrarModalSemaforo = function () {
    if (modalSemaforo) {
        modalSemaforo.style.display = "none";
    }
};

if (btnCerrarSemaforo) btnCerrarSemaforo.addEventListener("click", cerrarModalSemaforo);
if (btnCancelarSemaforo) btnCancelarSemaforo.addEventListener("click", cerrarModalSemaforo);

// Cerrar haciendo clic en el fondo oscuro exterior
if (modalSemaforo) {
    modalSemaforo.addEventListener("click", function (e) {
        if (e.target === modalSemaforo) {
            modalSemaforo.style.display = "none";
        }
    });
}

// ================================
// CONSULTAR ESTADÍSTICAS DE USUARIO POR DNI
// ================================
const inputDniAlumno = document.getElementById('dni_alumno');
const lectorQR = document.getElementById('lectorQR');
const btnSubmit = document.querySelector('#modalPrestamo button[type="submit"]');
const boxInfoUsuario = document.getElementById('info-usuario-prestamo');

if (inputDniAlumno) {
    ['input', 'change'].forEach(evento => {
        inputDniAlumno.addEventListener(evento, function () {
            const dni = this.value.trim();

            if (dni.length >= 4) {
                fetch(`obtener_info_usuario.php?dni=${encodeURIComponent(dni)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            // MOSTRAR NOMBRE Y APELLIDO
                            document.getElementById('lbl-nombre-usuario').textContent = `${data.apellido}, ${data.nombre}`;
                            
                            document.getElementById('lbl-puntos').textContent = data.puntos;
                            document.getElementById('cnt-verde').textContent = data.verdes;
                            document.getElementById('cnt-amarillo').textContent = data.amarillos;
                            document.getElementById('cnt-rojo').textContent = data.rojos;

                            const badge = document.getElementById('lbl-estado-badge');
                            const seccionSemaforos = document.getElementById('seccion-semaforos');
                            const msgSinPrestamos = document.getElementById('msg-sin-prestamos');
                            const msgBloqueado = document.getElementById('msg-bloqueado');

                            // Validar si el usuario está bloqueado (Menos de 50 puntos)
                            if (data.puntos < 50) {
                                badge.className = 'badge bg-danger';
                                badge.textContent = 'Bloqueado';

                                lectorQR.disabled = true;
                                lectorQR.value = '';
                                btnSubmit.disabled = true;
                                btnSubmit.classList.add('opacity-50');

                                msgBloqueado.style.display = 'block';
                                seccionSemaforos.style.display = 'flex';
                                msgSinPrestamos.style.display = 'none';
                            } else {
                                if (data.puntos >= 80) {
                                    badge.className = 'badge bg-success';
                                    badge.textContent = 'Excelente';
                                } else {
                                    badge.className = 'badge bg-warning text-dark';
                                    badge.textContent = 'Precaución';
                                }

                                lectorQR.disabled = false;
                                btnSubmit.disabled = false;
                                btnSubmit.classList.remove('opacity-50');
                                msgBloqueado.style.display = 'none';

                                if (data.verdes === 0 && data.amarillos === 0 && data.rojos === 0) {
                                    seccionSemaforos.style.display = 'none';
                                    msgSinPrestamos.style.display = 'block';
                                } else {
                                    seccionSemaforos.style.display = 'flex';
                                    msgSinPrestamos.style.display = 'none';
                                }
                            }

                            boxInfoUsuario.style.display = 'block';
                        } else {
                            resetearFormularioPrestamo();
                        }
                    })
                    .catch(error => {
                        console.error('Error en fetch:', error);
                        resetearFormularioPrestamo();
                    });
            } else {
                resetearFormularioPrestamo();
            }
        });
    });
}

function resetearFormularioPrestamo() {
    if (boxInfoUsuario) boxInfoUsuario.style.display = 'none';
    if (lectorQR) lectorQR.disabled = false;
    if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('opacity-50');
    }
}