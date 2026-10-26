<?php
// views/registro.php
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-secondary-custom">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-dark">JUAN’<span class="text-gold">STYLE</span></h2>
                    <p class="text-muted small">Crea tu cuenta de cliente para reservar citas</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?php 
                            if ($_GET['error'] === 'campos_vacios') echo 'Por favor completa todos los campos obligatorios.';
                            elseif ($_GET['error'] === 'correo_existente') echo 'El correo electrónico ya está registrado.';
                            else echo 'Ocurrió un error al procesar el registro.';
                        ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=procesar_registro" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control rounded-pill px-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control rounded-pill px-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Teléfono / WhatsApp</label>
                        <input type="text" name="telefono" class="form-control rounded-pill px-3">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control rounded-pill px-3" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold shadow-sm">Registrarse</button>
                </form>

                <div class="text-center mt-3">
                    <p class="small text-muted">¿Ya tienes cuenta? <a href="index.php?page=login" class="text-gold text-decoration-none fw-bold">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>