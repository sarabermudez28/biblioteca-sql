// ========================================
// MODAL AGREGAR MATERIAL
// ========================================

const modal = document.getElementById("modalMaterial");
const btnAgregar = document.getElementById("btnAgregar");
const cerrar = document.getElementById("cerrarModal");
const cancelar = document.getElementById("cancelar");


// ========================================
// ABRIR MODAL
// ========================================

if (btnAgregar && modal) {

    btnAgregar.addEventListener("click", function () {

        modal.classList.add("activo");

    });

}


// ========================================
// CERRAR CON X
// ========================================

if (cerrar && modal) {

    cerrar.addEventListener("click", function () {

        modal.classList.remove("activo");

    });

}


// ========================================
// CANCELAR
// ========================================

if (cancelar && modal) {

    cancelar.addEventListener("click", function () {

        modal.classList.remove("activo");

    });

}


// ========================================
// CERRAR HACIENDO CLICK AFUERA
// ========================================

if (modal) {

    modal.addEventListener("click", function (e) {

        if (e.target === modal) {

            modal.classList.remove("activo");

        }

    });

}