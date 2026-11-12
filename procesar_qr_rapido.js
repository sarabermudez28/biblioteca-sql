let barcodeBuffer = '';
let lastKeyTime = Date.now();

document.addEventListener('keydown', function(event) {
    const currentTime = Date.now();
    
    // Los escáneres escriben mucho más rápido que un humano (menos de 50ms por tecla)
    if (currentTime - lastKeyTime > 100) {
        barcodeBuffer = ''; // Reiniciar si pasa mucho tiempo (es tipeo humano)
    }
    
    lastKeyTime = currentTime;

    if (event.key === 'Enter') {
        if (barcodeBuffer.length > 3) { // Longitud mínima de tu QR o carné
            procesarCodigoQR(barcodeBuffer);
        }
        barcodeBuffer = '';
        event.preventDefault(); // Evitar comportamientos por defecto
    } else if (event.key.length === 1) {
        barcodeBuffer += event.key;
    }
});

function procesarCodigoQR(codigo) {
    console.log("QR detectado:", codigo);
    
    // Enviar por AJAX/Fetch a tu backend PHP sin recargar la página
    fetch('procesar_qr_rapido.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'codigo_qr=' + encodeURIComponent(codigo)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            mostrarNotificacionFlotante(data.mensaje, 'success');
            reproducirSonidoExito();
        } else {
            mostrarNotificacionFlotante(data.mensaje, 'error');
            reproducirSonidoAlerta();
        }
    })
    .catch(error => console.error('Error:', error));
}