<?php
include 'db.php';

$message = "";
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Procesar eliminación
if ($action == 'delete' && $id > 0) {
    try {
        $sql = "DELETE FROM mensajes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $message = "<div class='alert success'>✅ Mensaje eliminado correctamente.</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert error'>❌ Error al eliminar el mensaje: " . $e->getMessage() . "</div>";
    }
}

// Procesar edición o creación
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = htmlspecialchars($_POST['nombre']);
    $correo = htmlspecialchars($_POST['correo']);
    $mensaje = htmlspecialchars($_POST['mensaje']);
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;

    if (!empty($nombre) && !empty($correo) && !empty($mensaje)) {
        try {
            if ($edit_id > 0) {
                // Actualizar mensaje existente
                $sql = "UPDATE mensajes SET nombre = ?, correo = ?, mensaje = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre, $correo, $mensaje, $edit_id]);
                $message = "<div class='alert success'>✅ Mensaje actualizado correctamente.</div>";
            } else {
                // Insertar nuevo mensaje
                $sql = "INSERT INTO mensajes (nombre, correo, mensaje) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre, $correo, $mensaje]);
                $message = "<div class='alert success'>✅ Mensaje guardado correctamente.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert error'>❌ Error al guardar el mensaje: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert warning'>⚠️ Por favor, completa todos los campos.</div>";
    }
}

// Obtener mensaje para editar
$mensaje_editar = null;
if ($action == 'edit' && $id > 0) {
    try {
        $sql = "SELECT * FROM mensajes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $mensaje_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "<div class='alert error'>❌ Error al cargar el mensaje: " . $e->getMessage() . "</div>";
    }
}

// Obtener todos los mensajes
$stmt = $conn->query("SELECT * FROM mensajes ORDER BY fecha DESC");
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Arquitectura Cloud - Contenedores Docker</title>
<link rel='stylesheet' href='style.css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
function validarFormulario() {
    const nombre = document.getElementById('nombre').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const mensaje = document.getElementById('mensaje').value.trim();
    
    if (!nombre || !correo || !mensaje) {
        alert("Por favor completa todos los campos.");
        return false;
    }
    
    // Mostrar indicador de carga
    document.getElementById('loading').style.display = 'block';
    document.querySelector('button[type="submit"]').disabled = true;
    
    return true;
}

function validarCampo(campo) {
    const valor = campo.value.trim();
    const errorSpan = document.getElementById('error-' + campo.id);
    
    if (!valor) {
        campo.classList.add('error');
        errorSpan.textContent = 'Este campo es obligatorio';
        return false;
    }
    
    if (campo.type === 'email' && valor) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(valor)) {
            campo.classList.add('error');
            errorSpan.textContent = 'Por favor ingresa un correo válido';
            return false;
        }
    }
    
    campo.classList.remove('error');
    errorSpan.textContent = '';
    return true;
}

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

function buscarMensajes() {
    const input = document.getElementById('buscar');
    const filtro = input.value.toUpperCase();
    const tabla = document.getElementById('tabla-mensajes');
    const filas = tabla.getElementsByTagName('tr');
    
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
        
        filas[i].style.display = coincide ? '' : 'none';
    }
}

function confirmarEliminacion(id, nombre) {
    if (confirm(`¿Estás seguro de que deseas eliminar el mensaje de "${nombre}"?`)) {
        window.location.href = `index.php?action=delete&id=${id}`;
    }
}

function cancelarEdicion() {
    window.location.href = 'index.php';
}

function expandirMensaje(elemento) {
    const mensajeCompleto = elemento.getAttribute('data-mensaje-completo');
    const mensajeCorto = elemento.textContent;
    
    if (elemento.classList.contains('expandido')) {
        elemento.textContent = mensajeCorto.length > 100 ? mensajeCorto.substring(0, 100) + '...' : mensajeCorto;
        elemento.classList.remove('expandido');
        elemento.innerHTML += ' <i class="fas fa-expand-alt"></i>';
    } else {
        elemento.textContent = mensajeCompleto;
        elemento.classList.add('expandido');
        elemento.innerHTML += ' <i class="fas fa-compress-alt"></i>';
    }
}
</script>
</head>
<body>
<header>
    <div class="header-content">
        <h1><i class="fas fa-cloud"></i> Taller de Arquitectura Cloud</h1>
        <h2>Contenedores con Docker y PHP + MySQL</h2>
    </div>
</header>

<main>
    <section class='form-section'>
        <div class="section-header">
            <i class="fas fa-edit"></i>
            <h3><?= $mensaje_editar ? 'Editar Mensaje' : 'Enviar Mensaje' ?></h3>
        </div>
        
        <?= $message ?>
        
        <form method='POST' onsubmit='return validarFormulario();' novalidate>
            <?php if ($mensaje_editar): ?>
                <input type="hidden" name="edit_id" value="<?= $mensaje_editar['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for='nombre'>Nombre:</label>
                <input type='text' name='nombre' id='nombre' placeholder='Tu nombre completo' 
                       value="<?= $mensaje_editar ? htmlspecialchars($mensaje_editar['nombre']) : '' ?>"
                       onblur="validarCampo(this)">
                <span class="error-message" id="error-nombre"></span>
            </div>
            
            <div class="form-group">
                <label for='correo'>Correo electrónico:</label>
                <input type='email' name='correo' id='correo' placeholder='ejemplo@correo.com' 
                       value="<?= $mensaje_editar ? htmlspecialchars($mensaje_editar['correo']) : '' ?>"
                       onblur="validarCampo(this)">
                <span class="error-message" id="error-correo"></span>
            </div>
            
            <div class="form-group">
                <label for='mensaje'>Mensaje:</label>
                <textarea name='mensaje' id='mensaje' rows='4' placeholder='Escribe tu mensaje... (máximo 500 caracteres)'
                          onkeyup="contarCaracteres()" onblur="validarCampo(this)"><?= $mensaje_editar ? htmlspecialchars($mensaje_editar['mensaje']) : '' ?></textarea>
                <div class="contador-container">
                    <span class="contador-caracteres" id="contador-caracteres">
                        <?= $mensaje_editar ? strlen($mensaje_editar['mensaje']) : '0' ?>/500 caracteres
                    </span>
                </div>
                <span class="error-message" id="error-mensaje"></span>
            </div>
            
            <div class="form-actions">
                <button type='submit' class="btn-primary">
                    <i class="fas fa-<?= $mensaje_editar ? 'save' : 'paper-plane' ?>"></i> 
                    <?= $mensaje_editar ? 'Actualizar Mensaje' : 'Enviar Mensaje' ?>
                </button>
                
                <?php if ($mensaje_editar): ?>
                    <button type="button" class="btn-secondary" onclick="cancelarEdicion()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                <?php endif; ?>
                
                <div class="loading" id="loading">
                    <i class="fas fa-spinner fa-spin"></i> Procesando...
                </div>
            </div>
        </form>
    </section>

    <section class='table-section'>
        <div class="section-header">
            <i class="fas fa-list"></i>
            <h3>Mensajes registrados</h3>
        </div>
        
        <div class="table-controls">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="buscar" placeholder="Buscar en mensajes..." onkeyup="buscarMensajes()">
            </div>
            <div class="counter">
                <span id="contador-mensajes"><?= count($mensajes) ?> mensaje(s)</span>
            </div>
        </div>
        
        <div class="table-container">
            <table id="tabla-mensajes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mensajes) > 0): ?>
                        <?php foreach ($mensajes as $fila): ?>
                            <tr>
                                <td><?= $fila['id'] ?></td>
                                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                                <td><?= htmlspecialchars($fila['correo']) ?></td>
                                <td class="mensaje-celda">
                                    <span class="mensaje-texto" 
                                          onclick="expandirMensaje(this)"
                                          data-mensaje-completo="<?= htmlspecialchars($fila['mensaje']) ?>">
                                        <?= strlen($fila['mensaje']) > 100 ? htmlspecialchars(substr($fila['mensaje'], 0, 100)) . '...' : htmlspecialchars($fila['mensaje']) ?>
                                        <i class="fas fa-expand-alt"></i>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($fila['fecha'])) ?></td>
                                <td class="acciones">
                                    <a href="index.php?action=edit&id=<?= $fila['id'] ?>" class="btn-editar" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmarEliminacion(<?= $fila['id'] ?>, '<?= htmlspecialchars($fila['nombre']) ?>')" 
                                            class="btn-eliminar" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan='6' class='no-data'>
                                <i class="fas fa-inbox"></i>
                                <p>No hay mensajes registrados aún.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer>
    <div class="footer-content">
        <p><i class="fas fa-copyright"></i> 2025 Taller Docker — Docente: Juan Carlos López Henao</p>
        <div class="footer-links">
            <a href="#"><i class="fas fa-question-circle"></i> Ayuda</a>
            <a href="#"><i class="fas fa-envelope"></i> Contacto</a>
        </div>
    </div>
</footer>
</body>
</html>