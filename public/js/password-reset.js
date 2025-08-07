// Funciones para el manejo del formulario de restablecimiento de contraseña
function hideMessage(messageId) {
    document.getElementById(messageId).style.display = 'none';
}

function showMessage(messageId, textId, message) {
    document.getElementById(textId).textContent = message;
    document.getElementById(messageId).style.display = 'block';
    
    // Ocultar otros mensajes
    if (messageId === 'successMessage') {
        document.getElementById('errorMessage').style.display = 'none';
    } else {
        document.getElementById('successMessage').style.display = 'none';
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('restorePasswordForm');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value;
            
            // Validar email
            if (!email || !email.includes('@')) {
                showMessage('errorMessage', 'errorText', 'Por favor ingresa un correo electrónico válido');
                return;
            }
            
            // Deshabilitar botón
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Enviando...';
            
            try {
                // Obtener la URL y token CSRF desde los meta tags
                const url = document.querySelector('meta[name="password-reset-url"]').getAttribute('content');
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        email: email
                    })
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    showMessage('successMessage', 'successText', data.message);
                    // Limpiar formulario
                    document.getElementById('email').value = '';
                } else {
                    showMessage('errorMessage', 'errorText', data.message || 'Error al enviar el enlace de restablecimiento');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('errorMessage', 'errorText', 'Error al procesar la solicitud. Verifica tu conexión a internet.');
            } finally {
                // Habilitar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📧 Enviar Enlace de Restablecimiento';
            }
        });
    }
});
