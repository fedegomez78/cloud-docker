<?php
include 'db.php';

$message = "";
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Procesar eliminación
if ($action == 'delete' && $id > 0) {
    try {
        // Obtener foto antes de eliminar
        $sql = "SELECT foto FROM mensajes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $mensaje = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Eliminar foto si existe
        if ($mensaje && $mensaje['foto'] && file_exists('uploads/' . $mensaje['foto'])) {
            unlink('uploads/' . $mensaje['foto']);
        }
        
        // Eliminar registro
        $sql = "DELETE FROM mensajes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Mensaje eliminado correctamente.</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert error'><i class='fas fa-times-circle'></i> Error al eliminar: " . $e->getMessage() . "</div>";
    }
}

// Procesar edición o creación
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $correo = htmlspecialchars(trim($_POST['correo']));
    $telefono = htmlspecialchars(trim($_POST['telefono']));
    $ciudad = htmlspecialchars(trim($_POST['ciudad']));
    $pais = htmlspecialchars(trim($_POST['pais']));
    $mensaje = htmlspecialchars(trim($_POST['mensaje']));
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    // Procesar foto
    $nombreFoto = '';
    $fotoAnterior = isset($_POST['foto_anterior']) ? $_POST['foto_anterior'] : '';
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $archivoTmp = $_FILES['foto']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $tamanoMaximo = 5 * 1024 * 1024; // 5MB
        
        if (in_array($extension, $permitidos) && $_FILES['foto']['size'] <= $tamanoMaximo) {
            // Crear directorio si no existe
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            // Generar nombre único
            $nombreFoto = uniqid() . '_' . time() . '.' . $extension;
            $rutaDestino = 'uploads/' . $nombreFoto;
            
            if (move_uploaded_file($archivoTmp, $rutaDestino)) {
                // Eliminar foto anterior si existe
                if ($edit_id > 0 && $fotoAnterior && file_exists('uploads/' . $fotoAnterior)) {
                    unlink('uploads/' . $fotoAnterior);
                }
            } else {
                $nombreFoto = $fotoAnterior;
            }
        } else {
            $nombreFoto = $fotoAnterior;
        }
    } else {
        $nombreFoto = $fotoAnterior;
    }

    if (!empty($nombre) && !empty($correo) && !empty($mensaje)) {
        try {
            if ($edit_id > 0) {
                // Actualizar
                $sql = "UPDATE mensajes SET nombre = ?, correo = ?, telefono = ?, ciudad = ?, pais = ?, foto = ?, mensaje = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre, $correo, $telefono, $ciudad, $pais, $nombreFoto, $mensaje, $edit_id]);
                $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Mensaje actualizado correctamente.</div>";
            } else {
                // Insertar
                $sql = "INSERT INTO mensajes (nombre, correo, telefono, ciudad, pais, foto, mensaje) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre, $correo, $telefono, $ciudad, $pais, $nombreFoto, $mensaje]);
                $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Mensaje guardado correctamente.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert error'><i class='fas fa-times-circle'></i> Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert warning'><i class='fas fa-exclamation-triangle'></i> Por favor, completa todos los campos obligatorios.</div>";
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
        $message = "<div class='alert error'><i class='fas fa-times-circle'></i> Error al cargar: " . $e->getMessage() . "</div>";
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
</head>
<body>
<header>
    <div class="header-content">
        <h1><i class="fas fa-cloud"></i> Taller de Arquitectura Cloud</h1>
        <h2>Contenedores con Docker, PHP 8.2 + MySQL 8.0</h2>
        <div class="header-badges">
            <span class="badge"><i class="fas fa-server"></i> Docker Compose</span>
            <span class="badge"><i class="fas fa-database"></i> MySQL</span>
            <span class="badge"><i class="fas fa-code"></i> PHP 8.2</span>
        </div>
    </div>
</header>

<main>
    <section class='form-section'>
        <div class="section-header">
            <i class="fas fa-<?= $mensaje_editar ? 'edit' : 'paper-plane' ?>"></i>
            <h3><?= $mensaje_editar ? 'Editar Mensaje' : 'Enviar Nuevo Mensaje' ?></h3>
        </div>
        
        <?= $message ?>
        
        <form method='POST' enctype='multipart/form-data' onsubmit='return validarFormulario();' novalidate>
            <?php if ($mensaje_editar): ?>
                <input type="hidden" name="edit_id" value="<?= $mensaje_editar['id'] ?>">
                <input type="hidden" name="foto_anterior" value="<?= $mensaje_editar['foto'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for='nombre'><i class="fas fa-user"></i> Nombre completo: <span class="required">*</span></label>
                    <input type='text' name='nombre' id='nombre' placeholder='Tu nombre completo' 
                           value="<?= $mensaje_editar ? htmlspecialchars($mensaje_editar['nombre']) : '' ?>"
                           onblur="validarCampo(this)" required>
                    <span class="error-message" id="error-nombre"></span>
                </div>
                
                <div class="form-group">
                    <label for='correo'><i class="fas fa-envelope"></i> Correo electrónico: <span class="required">*</span></label>
                    <input type='email' name='correo' id='correo' placeholder='ejemplo@correo.com' 
                           value="<?= $mensaje_editar ? htmlspecialchars($mensaje_editar['correo']) : '' ?>"
                           onblur="validarCampo(this)" required>
                    <span class="error-message" id="error-correo"></span>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for='telefono'><i class="fas fa-phone"></i> Teléfono: <span class="required">*</span></label>
                    <input type='text' name='telefono' id='telefono' placeholder='+57 300 123 4567' 
                           value="<?= $mensaje_editar ? htmlspecialchars($mensaje_editar['telefono']) : '' ?>"
                           onblur="validarCampo(this)" oninput="formatearTelefono(this)" required>
                    <span class="error-message" id="error-telefono"></span>
                </div>
                
                <div class="form-group">
                    <label for='ciudad'><i class="fas fa-city"></i> Ciudad: <span class="required">*</span></label>
                    <input type='text' name='ciudad' id='ciudad' placeholder='Medellín' 
                           value="<?= $mensaje_editar ? htmlspecialchars($mensaje_editar['ciudad']) : '' ?>"
                           onblur="validarCampo(this)" required>
                    <span class="error-message" id="error-ciudad"></span>
                </div>
                
                <div class="form-group">
                    <label for='pais'><i class="fas fa-globe-americas"></i> País: <span class="required">*</span></label>
                    <input type='text' name='pais' id='pais' placeholder='Colombia' 
                           value="<?= $mensaje_editar ? htmlspecialchars($mensaje_editar['pais']) : '' ?>"
                           onblur="validarCampo(this)" required>
                    <span class="error-message" id="error-pais"></span>
                </div>
            </div>
            
            <div class="form-group">
                <label for='foto'><i class="fas fa-image"></i> Foto de perfil: <span class="optional">(opcional)</span></label>
                <div class="file-input-wrapper">
                    <input type='file' name='foto' id='foto' accept='image/*' onchange='previsualizarFoto(this)'>
                    <span class="file-input-label"><i class="fas fa-cloud-upload-alt"></i> Seleccionar imagen</span>
                </div>
                <small class="help-text">Formatos: JPG, PNG, GIF, WEBP (máx. 5MB)</small>
                <span class="error-message" id="error-foto"></span>
                
                <div id="preview-container" style="display: <?= $mensaje_editar && $mensaje_editar['foto'] ? 'block' : 'none' ?>;">
                    <img id="preview-foto" src="<?= $mensaje_editar && $mensaje_editar['foto'] ? 'uploads/' . htmlspecialchars($mensaje_editar['foto']) : '' ?>" alt="Vista previa">
                </div>
            </div>
            
            <div class="form-group">
                <label for='mensaje'><i class="fas fa-comment"></i> Mensaje: <span class="required">*</span></label>
                <textarea name='mensaje' id='mensaje' rows='4' placeholder='Escribe tu mensaje... (mínimo 10, máximo 500 caracteres)'
                          onkeyup="contarCaracteres()" onblur="validarCampo(this)" required><?= $mensaje_editar ? htmlspecialchars($mensaje_editar['mensaje']) : '' ?></textarea>
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
            <i class="fas fa-list-alt"></i>
            <h3>Mensajes Registrados</h3>
        </div>
        
        <div class="table-controls">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="buscar" placeholder="Buscar en mensajes..." onkeyup="buscarMensajes()">
            </div>
            <div class="counter">
                <i class="fas fa-database"></i>
                <span id="contador-mensajes"><?= count($mensajes) ?> mensaje(s)</span>
            </div>
        </div>
        
        <div class="table-container">
            <table id="tabla-mensajes">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Nombre</th>
                        <th><i class="fas fa-envelope"></i> Correo</th>
                        <th><i class="fas fa-phone"></i> Teléfono</th>
                        <th><i class="fas fa-map-marker-alt"></i> Ubicación</th>
                        <th><i class="fas fa-image"></i> Foto</th>
                        <th><i class="fas fa-comment"></i> Mensaje</th>
                        <th><i class="fas fa-calendar"></i> Fecha</th>
                        <th><i class="fas fa-cog"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mensajes) > 0): ?>
                        <?php foreach ($mensajes as $fila): ?>
                            <tr>
                                <td><?= $fila['id'] ?></td>
                                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                                <td><?= htmlspecialchars($fila['correo']) ?></td>
                                <td><?= htmlspecialchars($fila['telefono']) ?></td>
                                <td>
                                    <div class="ubicacion">
                                        <span><?= htmlspecialchars($fila['ciudad']) ?></span>
                                        <small><?= htmlspecialchars($fila['pais']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($fila['foto']): ?>
                                        <img src="uploads/<?= htmlspecialchars($fila['foto']) ?>" 
                                             alt="Foto" class="miniatura" 
                                             onclick="verFoto('uploads/<?= htmlspecialchars($fila['foto']) ?>')">
                                    <?php else: ?>
                                        <span class="sin-foto"><i class="fas fa-user-circle"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="mensaje-celda">
                                    <span class="mensaje-texto" 
                                          onclick="expandirMensaje(this)"
                                          data-mensaje-completo="<?= htmlspecialchars($fila['mensaje']) ?>">
                                        <?= strlen($fila['mensaje']) > 50 ? htmlspecialchars(substr($fila['mensaje'], 0, 50)) . '...' : htmlspecialchars($fila['mensaje']) ?>
                                        <?php if (strlen($fila['mensaje']) > 50): ?>
                                            <i class="fas fa-expand-alt"></i>
                                        <?php endif; ?>
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
                            <td colspan='9' class='no-data'>
                                <i class="fas fa-inbox"></i>
                                <p>No hay mensajes registrados aún.</p>
                                <small>¡Sé el primero en enviar un mensaje!</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<!-- Modal para ver foto -->
<div id="modal-foto" class="modal" onclick="cerrarModal()">
    <span class="modal-close"><i class="fas fa-times"></i></span>
    <img class="modal-content" id="img-modal">
</div>

<footer>
    <div class="footer-content">
        <div class="footer-info">
            <p><i class="fas fa-copyright"></i> 2025 Taller Docker — Docente: Juan Carlos López Henao</p>
            <p class="footer-tech">
                <span><i class="fab fa-php"></i> PHP 8.2</span>
                <span><i class="fas fa-database"></i> MySQL 8.0</span>
                <span><i class="fab fa-docker"></i> Docker</span>
            </p>
        </div>
        <div class="footer-links">
            <a href="#"><i class="fas fa-question-circle"></i> Ayuda</a>
            <a href="#"><i class="fas fa-book"></i> Documentación</a>
            <a href="#"><i class="fas fa-envelope"></i> Contacto</a>
        </div>
    </div>
</footer>

<script src="validaciones.js"></script>
</body>
</html>