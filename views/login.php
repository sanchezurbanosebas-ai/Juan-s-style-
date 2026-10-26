<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-secondary-custom">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-dark">JUAN’<span class="text-gold">STYLE</span></h2>
                    <p class="text-muted small">Inicia sesión para gestionar tus citas</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?php 
                            if ($_GET['error'] === 'credenciales_invalidas') echo 'Correo o contraseña incorrectos.';
                            elseif ($_GET['error'] === 'acceso_denegado') echo 'No tienes permisos de administrador.';
                            else echo 'Ocurrió un error al iniciar sesión.';
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] === 'registrado'): ?>
                    <div class="alert alert-success py-2 small" role="alert">
                        ¡Registro exitoso! Ya puedes iniciar sesión.
                    </div>
                <?php endif; ?>

                <form action="index.php?page=procesar_login" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control rounded-pill px-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control rounded-pill px-3" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold shadow-sm">Iniciar Sesión</button>
                </form>

                <div class="text-center mt-3">
                    <p class="small text-muted">¿No tienes cuenta? <a href="index.php?page=registro" class="text-gold text-decoration-none fw-bold">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>