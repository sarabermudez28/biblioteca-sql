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