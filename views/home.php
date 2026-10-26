<?php
// views/home.php
require_once __DIR__ . '/../config/conexion.php';

// Cargar servicios activos de la base de datos
$stmtServicios = $pdo->query("SELECT * FROM servicios WHERE estado = 1 ORDER BY id ASC");
$servicios = $stmtServicios->fetchAll();

// Cargar datos de Juan González
$stmtBarbero = $pdo->query("SELECT * FROM barbero WHERE id = 1 LIMIT 1");
$barbero = $stmtBarbero->fetch();
?>

<!-- 1. HERO / PORTADA PRINCIPAL -->
<section class="py-5 bg-secondary-custom border-bottom">
    <div class="container py-4">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6">
                <span class="badge bg-gold-subtle text-gold fw-bold px-3 py-2 rounded-pill mb-3 border border-gold">
                    BARBERÍA PREMIUM EN NEIVA
                </span>
                <h1 class="display-4 fw-extrabold text-dark mb-3">
                    Elegancia, Estilo y <span class="text-gold">Perfección</span> en cada corte
                </h1>
                <p class="lead text-muted mb-4">
                    Reserva tu cita con <strong>Juan González</strong> y experimenta un servicio exclusivo adaptado a tu estilo personal.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#reservar" class="btn btn-gold btn-lg rounded-pill px-4 shadow-sm fw-bold">
                        <i class="bi bi-calendar-event me-2"></i> Reservar Cita
                    </a>
                    <a href="https://wa.me/573177128368" target="_blank" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                        <i class="bi bi-whatsapp text-success me-2"></i> WhatsApp Directo
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="position-relative d-inline-block">
                    <div class="rounded-4 overflow-hidden border border-gold shadow-lg" style="max-width: 450px; margin: auto;">
                        <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=600&q=80" alt="Juan’Style Barbería" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. CATÁLOGO DE SERVICIOS -->
<section id="servicios" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-gold fw-bold text-uppercase tracking-wider">Nuestros Servicios</h6>
            <h2 class="fw-bold text-dark display-6">Cortes & Estilismo de Alta Calidad</h2>
            <div class="mx-auto border-top border-gold border-3" style="width: 60px;"></div>
        </div>

        <div class="row g-4">
            <?php if (!empty($servicios)): ?>
                <?php foreach ($servicios as $servicio): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border rounded-4 shadow-sm hover-top transition">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($servicio['nombre']) ?></h5>
                                        <span class="fs-5 fw-bold text-gold">$<?= number_format($servicio['precio'], 0, ',', '.') ?> COP</span>
                                    </div>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($servicio['descripcion']) ?></p>
                                </div>
                                <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i> <?= $servicio['duracion_minutos'] ?> min</span>
                                    <button onclick="seleccionarServicio(<?= $servicio['id'] ?>)" class="btn btn-sm btn-gold rounded-pill px-3 fw-bold">
                                        Elegir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">
                    <p>No hay servicios registrados en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. MÓDULO DE RESERVAS INTERACTIVO -->
<section id="reservar" class="py-5 bg-secondary-custom border-top border-bottom">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <h6 class="text-gold fw-bold text-uppercase">Agenda Tu Turno</h6>
                        <h3 class="fw-bold text-dark">Reserva tu cita con Juan González</h3>
                        <p class="text-muted small">Selecciona el servicio, la fecha y la hora deseada.</p>
                    </div>

                    <?php if (isset($_GET['reserva_exito'])): ?>
                        <div class="alert alert-success rounded-pill text-center py-2 mb-4">
                            ¡Tu cita ha sido agendada con éxito! Te esperamos en Juan’Style.
                        </div>
                    <?php endif; ?>

                    <form action="index.php?page=procesar_reserva" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Servicio Seleccionado</label>
                            <select name="servicio_id" id="selectServicio" class="form-select rounded-pill px-3 py-2" required>
                                <option value="">-- Selecciona un servicio --</option>
                                <?php foreach ($servicios as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?> - $<?= number_format($s['precio'], 0, ',', '.') ?> COP (<?= $s['duracion_minutos'] ?> min)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Fecha</label>
                                <input type="date" name="fecha" class="form-control rounded-pill px-3 py-2" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hora de Atención</label>
                                <input type="time" name="hora" class="form-control rounded-pill px-3 py-2" min="08:00" max="20:00" required>
                            </div>
                        </div>

                        <?php if (Auth::check()): ?>
                            <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold shadow-sm">Confirmar Reserva</button>
                        <?php else: ?>
                            <div class="text-center p-3 bg-light rounded-4 border">
                                <p class="small text-muted mb-2">Debes iniciar sesión para confirmar tu reserva.</p>
                                <a href="index.php?page=login" class="btn btn-gold rounded-pill px-4 btn-sm fw-bold">Iniciar Sesión para Reservar</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. PRESENTACIÓN DEL BARBERO ÚNICO: JUAN GONZÁLEZ -->
<section id="nosotros" class="py-5 bg-white">
    <div class="container py-4">
        <div class="row align-items-center gy-4">
            <div class="col-md-5 text-center">
                <img src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=500&q=80" class="img-fluid rounded-circle border border-3 border-gold shadow p-2" style="width: 280px; height: 280px; object-fit: cover;" alt="Juan González">
            </div>
            <div class="col-md-7">
                <span class="badge bg-gold text-white px-3 py-1 rounded-pill mb-2">Barbero Profesional</span>
                <h2 class="fw-bold text-dark mb-3"><?= htmlspecialchars($barbero['nombre'] ?? 'Juan González') ?></h2>
                <h5 class="text-gold mb-3"><?= htmlspecialchars($barbero['especialidad'] ?? 'Especialista en Barbería Clásica y Urbana') ?></h5>
                <p class="text-muted mb-4">
                    <?= htmlspecialchars($barbero['descripcion'] ?? 'Apasionado por la precisión y las técnicas modernas de degradado y perfilado de barba. Cada atención es personalizada para garantizar los máximos estándares de higiene y acabado.') ?>
                </p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light border rounded-3 text-center">
                            <i class="bi bi-clock-history fs-3 text-gold mb-1"></i>
                            <h6 class="fw-bold mb-0">Horarios de Atención</h6>
                            <small class="text-muted"><?= htmlspecialchars($barbero['horario_laboral'] ?? 'Lunes a Sábado: 8:00 AM - 8:00 PM') ?></small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light border rounded-3 text-center">
                            <i class="bi bi-shield-check fs-3 text-gold mb-1"></i>
                            <h6 class="fw-bold mb-0">Atención Exclusiva</h6>
                            <small class="text-muted">Garantía 100% Personalizada</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. MÓDULO GALERÍA DE CORTES -->
<section id="galeria" class="py-5 bg-secondary-custom border-top border-bottom">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-gold fw-bold text-uppercase tracking-wider">Trabajos Recientes</h6>
            <h2 class="fw-bold text-dark display-6">Galería de Estilos & Cortes</h2>
            <div class="mx-auto border-top border-gold border-3" style="width: 60px;"></div>
            <p class="text-muted mt-2">Muestra de los acabados profesionales realizados por <strong>Juan González</strong></p>
        </div>

        <div class="row g-4">
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=500&q=80" class="img-fluid rounded-top-4 w-100" style="height: 250px; object-fit: cover;" alt="Corte Degradado">
                    <div class="p-2 text-center bg-white border-top">
                        <small class="fw-bold text-dark">Degradado / Skin Fade</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=500&q=80" class="img-fluid rounded-top-4 w-100" style="height: 250px; object-fit: cover;" alt="Perfilado de Barba">
                    <div class="p-2 text-center bg-white border-top">
                        <small class="fw-bold text-dark">Perfilado de Barba VIP</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=500&q=80" class="img-fluid rounded-top-4 w-100" style="height: 250px; object-fit: cover;" alt="Corte Clásico">
                    <div class="p-2 text-center bg-white border-top">
                        <small class="fw-bold text-dark">Corte Tradicional & Peinado</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=500&q=80" class="img-fluid rounded-top-4 w-100" style="height: 250px; object-fit: cover;" alt="Diseño de Barba">
                    <div class="p-2 text-center bg-white border-top">
                        <small class="fw-bold text-dark">Diseño & Delineado</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. UBICACIÓN Y MAPA INTERACTIVO -->
<section id="contacto" class="py-5 bg-white">
    <div class="container py-4">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-5">
                <h3 class="fw-bold text-dark mb-3">Encuéntranos en Neiva</h3>
                <p class="text-muted mb-4">Visita nuestra barbería en Neiva, Huila. Puedes agendar tu cita directamente en la plataforma o comunicarte por WhatsApp.</p>
                
                <div class="mb-3 d-flex align-items-center">
                    <div class="p-3 bg-secondary-custom text-gold rounded-circle me-3 shadow-sm">
                        <i class="bi bi-geo-alt fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Ubicación</h6>
                        <small class="text-muted">Barrio Villapatricia, Neiva, Huila</small>
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-center">
                    <div class="p-3 bg-secondary-custom text-gold rounded-circle me-3 shadow-sm">
                        <i class="bi bi-whatsapp fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">WhatsApp Directo</h6>
                        <small class="text-muted">3177128368</small>
                    </div>
                </div>

                <a href="https://wa.me/573177128368" target="_blank" class="btn btn-gold rounded-pill px-4 py-2 shadow-sm fw-bold">
                    <i class="bi bi-whatsapp me-2"></i> Escribir a Juan González
                </a>
            </div>

            <div class="col-lg-7">
                <div id="mapa" class="rounded-4 border shadow-sm" style="height: 380px; width: 100%;"></div>
            </div>
        </div>
    </div>
</section>

<!-- INTEGRACIÓN DE MAPAS CON LEAFLET & SCRIPT DE ELECCIÓN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = 2.9404114;
    var lng = -75.2927348;

    var map = L.map('mapa').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup('<b>Juan’Style Barbería</b><br>Barrio Villapatricia, Neiva.')
        .openPopup();
});

function seleccionarServicio(id) {
    var select = document.getElementById('selectServicio');
    if (select) {
        select.value = id;
    }
    var elem = document.getElementById('reservar');
    if (elem) {
        elem.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>