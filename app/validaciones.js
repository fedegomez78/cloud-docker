// validaciones.js - Validaciones del formulario

// Expresiones regulares
const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const regexTelefono = /^[\d\s\-\+\(\)]{7,20}$/;
const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,100}$/;

// Validar formulario completo
function validarFormulario() {
    const nombre = document.getElementById('nombre');
    const correo = document.getElementById('correo');
    const telefono = document.getElementById('telefono');
    const ciudad = document.getElementById('ciudad');
    const pais = document.getElementById('pais');
    const mensaje = document.getElementById('mensaje');
    const foto = document.getElementById('foto');
    
    let valido = true;
    
    // Validar cada campo
    if (!validarCampo(nombre)) valido = false;
    if (!validarCampo(correo)) valido = false;
    if (!validarCampo(telefono)) valido = false;
    if (!validarCampo(ciudad)) valido = false;
    if (!validarCampo(pais)) valido = false;
    if (!validarCampo(mensaje)) valido = false;
    
    // Validar foto (opcional)
    if (foto.files.length > 0) {
        if (!validarFoto(foto)) valido = false;
    }
    
    if (!valido) {
        mostrarNotificacion('Por favor corrige los errores en el formulario', 'error');
        return false;
    }
    
    // Mostrar indicador de carga
    document.getElementById('loading').style.display = 'flex';
    document.querySelector('button[type="submit"]').disabled = true;
    
    return true;
}

// Validar campo individual
function validarCampo(campo) {
    const valor = campo.value.trim();
    const errorSpan = document.getElementById('error-' + campo.id);
    
    // Limpiar error previo
    campo.classList.remove('error');
    errorSpan.textContent = '';
    
    // Campo vacío
    if (!valor && campo.required) {
        campo.classList.add('error');
        errorSpan.textContent = 'Este campo es obligatorio';
        return false;
    }
    
    if (!valor) return true; // Campo opcional y vacío
    
    // Validaciones específicas por tipo
    switch(campo.id) {
        case 'nombre':
            if (!regexNombre.test(valor)) {
                campo.classList.add('error');
                errorSpan.textContent = 'El nombre solo debe contener letras';
                return false;
            }
            if (valor.length < 2 || valor.length > 100) {
                campo.classList.add('error');
                errorSpan.textContent = 'El nombre debe tener entre 2 y 100 caracteres';
                return false;
            }
            break;
            
        case 'correo':
            if (!regexEmail.test(valor)) {
                campo.classList.add('error');
                errorSpan.textContent = 'Por favor ingresa un correo válido';
                return false;
            }
            break;
            
        case 'telefono':
            if (!regexTelefono.test(valor)) {
                campo.classList.add('error');
                errorSpan.textContent = 'Ingresa un teléfono válido (ej: +57 300 123 4567)';
                return false;
            }
            break;
            
        case 'ciudad':
        case 'pais':
            if (valor.length < 2 || valor.length > 100) {
                campo.classList.add('error');
                errorSpan.textContent = 'Debe tener entre 2 y 100 caracteres';
                return false;
            }
            break;
            
        case 'mensaje':
            if (valor.length < 10) {
                campo.classList.add('error');
                errorSpan.textContent = 'El mensaje debe tener al menos 10 caracteres';
                return false;
            }
            if (valor.length > 500) {
                campo.classList.add('error');
                errorSpan.textContent = 'El mensaje no puede exceder 500 caracteres';
                return false;
            }
            break;
    }
    
    return true;
}

// Validar archivo de foto
function validarFoto(inputFoto) {
    const errorSpan = document.getElementById('error-foto');
    errorSpan.textContent = '';
    inputFoto.classList.remove('error');
    
    if (inputFoto.files.length === 0) return true;
    
    const archivo = inputFoto.files[0];
    const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    const tamanoMaximo = 5 * 1024 * 1024; // 5MB
    
    // Validar tipo
    if (!tiposPermitidos.includes(archivo.type)) {
        inputFoto.classList.add('error');
        errorSpan.textContent = 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP)';
        return false;
    }
    
    // Validar tamaño
    if (archivo.size > tamanoMaximo) {
        inputFoto.classList.add('error');
        errorSpan.textContent = 'La imagen no debe superar 5MB';
        return false;
    }
    
    return true;
}

// Previsualizar foto
function previsualizarFoto(input) {
    const preview = document.getElementById('preview-foto');
    const previewContainer = document.getElementById('preview-container');
    
    if (input.files && input.files[0]) {
        if (!validarFoto(input)) {
            previewContainer.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.style.display = 'none';
    }
}

// Contador de caracteres
function contarCaracteres() {
    const mensaje = document.getElementById('mensaje');
    const contador = document.getElementById('contador-caracteres');
    const longitud = mensaje.value.length;
    
    contador.textContent = `${longitud}/500 caracteres`;
    
    if (longitud > 450) {
        contador.classList.add('advertencia');
    } else {
        contador.classList.remove('advertencia');
    }
    
    if (longitud > 500) {
        mensaje.classList.add('error');
    } else {
        mensaje.classList.remove('error');
    }
}

// Buscar en mensajes
function buscarMensajes() {
    const input = document.getElementById('buscar');
    const filtro = input.value.toUpperCase();
    const tabla = document.getElementById('tabla-mensajes');
    const filas = tabla.getElementsByTagName('tr');
    let visibles = 0;
    
    for (let i = 1; i < filas.length; i++) {
        const celdas = filas[i].getElementsByTagName('td');
        let coincide = false;
        
        for (let j = 0; j < celdas.length; j++) {
            if (celdas[j]) {
                const texto = celdas[j].textContent || celdas[j].innerText;
                if (texto.toUpperCase().indexOf(filtro) > -1) {
                    coincide = true;
                    break;
                }
            }
        }
        
        if (coincide) {
            filas[i].style.display = '';
            visibles++;
        } else {
            filas[i].style.display = 'none';
        }
    }
    
    // Actualizar contador
    document.getElementById('contador-mensajes').textContent = `${visibles} mensaje(s)`;
}

// Confirmar eliminación
function confirmarEliminacion(id, nombre) {
    if (confirm(`¿Estás seguro de que deseas eliminar el mensaje de "${nombre}"?`)) {
        window.location.href = `index.php?action=delete&id=${id}`;
    }
}

// Cancelar edición
function cancelarEdicion() {
    if (confirm('¿Deseas cancelar la edición? Los cambios no guardados se perderán.')) {
        window.location.href = 'index.php';
    }
}

// Expandir/contraer mensaje
function expandirMensaje(elemento) {
    const mensajeCompleto = elemento.getAttribute('data-mensaje-completo');
    
    if (elemento.classList.contains('expandido')) {
        const mensajeCorto = mensajeCompleto.length > 50 ? 
            mensajeCompleto.substring(0, 50) + '...' : mensajeCompleto;
        elemento.innerHTML = `${mensajeCorto} <i class="fas fa-expand-alt"></i>`;
        elemento.classList.remove('expandido');
    } else {
        elemento.innerHTML = `${mensajeCompleto} <i class="fas fa-compress-alt"></i>`;
        elemento.classList.add('expandido');
    }
}

// Ver foto en modal
function verFoto(url) {
    const modal = document.getElementById('modal-foto');
    const modalImg = document.getElementById('img-modal');
    
    modal.style.display = 'flex';
    modalImg.src = url;
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') cerrarModal();
    });
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modal-foto').style.display = 'none';
}

// Mostrar notificación
function mostrarNotificacion(mensaje, tipo) {
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion ${tipo}`;
    
    const icono = tipo === 'error' ? 'fa-exclamation-circle' : 
                  tipo === 'success' ? 'fa-check-circle' : 'fa-info-circle';
    
    notificacion.innerHTML = `<i class="fas ${icono}"></i> ${mensaje}`;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.classList.add('mostrar');
    }, 100);
    
    setTimeout(() => {
        notificacion.classList.remove('mostrar');
        setTimeout(() => notificacion.remove(), 300);
    }, 3000);
}

// Formatear teléfono mientras se escribe
function formatearTelefono(input) {
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length > 0) {
        if (valor.length <= 3) {
            input.value = valor;
        } else if (valor.length <= 6) {
            input.value = valor.slice(0, 3) + ' ' + valor.slice(3);
        } else if (valor.length <= 10) {
            input.value = valor.slice(0, 3) + ' ' + valor.slice(3, 6) + ' ' + valor.slice(6);
        } else {
            input.value = valor.slice(0, 3) + ' ' + valor.slice(3, 6) + ' ' + valor.slice(6, 10);
        }
    }
}

// Inicialización cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Agregar listeners a campos del formulario
    const campos = ['nombre', 'correo', 'telefono', 'ciudad', 'pais', 'mensaje'];
    campos.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
            campo.addEventListener('blur', function() {
                validarCampo(this);
            });
        }
    });
    
    // Contador de mensajes inicial
    const tabla = document.getElementById('tabla-mensajes');
    if (tabla) {
        const filas = tabla.getElementsByTagName('tr').length - 1;
        document.getElementById('contador-mensajes').textContent = `${filas} mensaje(s)`;
    }
    
    console.log('Sistema de validaciones cargado correctamente');
});