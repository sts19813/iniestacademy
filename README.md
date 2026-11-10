# Iniesta Academy Mérida

Landing adaptable de Iniesta Academy Mérida, migrada a Laravel 13 a partir de la versión HTML, CSS y JavaScript. Mantiene el diseño, las imágenes, las fuentes y las interacciones originales.

## Requisitos y ejecución

Se requieren PHP 8.3 o superior, las extensiones habituales de Laravel y Composer. No se necesita Node.js. La landing puede mostrarse sin base de datos; las inscripciones requieren SQLite.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Abrir `http://127.0.0.1:8000`. En un servidor web, configurar `public/` como directorio público; no exponer la raíz del proyecto. Para producción, configurar `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` y una clave propia en `.env`.

La página está en `resources/views/landing.blade.php`, sus recursos en `public/` y la ruta en `bootstrap/app.php`. La landing es pública y no inicia sesiones; el formulario de inscripción utiliza una sesión para proteger el envío. Las cabeceras de la respuesta se configuran en `app/Http/Middleware/SecurityHeaders.php`; el servidor web debe aplicar las mismas cabeceras a los recursos estáticos si se requiere esa cobertura en producción.

Para ejecutar las comprobaciones:

```sh
php artisan test
node --check public/script.js # opcional
```

## Alcance

La integración de inscripción y Stripe se activa por configuración. Mientras está desactivada, la landing no recoge registros ni cobra. Se mantienen `SEGURIDAD.md`, `PLAN_DESARROLLO.md` y `config/security-baseline.json` como documentación de las fases futuras.

El contenido comercial del diseño requiere confirmación antes de publicarse: hay diferencias entre secciones en precio, fechas, edades y dirección, y debe confirmarse el horario. Los datos de contacto son ficticios y los perfiles sociales, documentos legales y proveedor de pago siguen pendientes. Las capturas en `qa/` corresponden a la versión estática anterior a la migración.

## Inscripciones con Stripe (preparación)

El flujo de inscripciones se configura desde `.env` y permanece desactivado mientras `REGISTRATION_ENABLED=false`. Requiere una base SQLite privada y persistente, ejecutando la migración de inscripciones; no debe usarse un archivo temporal ni exponerse la base desde `public/`. Configurar una clave de Stripe en `STRIPE_SECRET` y un secreto **propio del endpoint** en `STRIPE_WEBHOOK_SECRET`. El webhook `/stripe/webhook` debe estar disponible por HTTPS en el dominio público y recibir `checkout.session.completed`, `checkout.session.async_payment_succeeded` y `checkout.session.async_payment_failed` antes de activar inscripciones.

Los importes se expresan en centavos mexicanos: `REGISTRATION_PRICE_CENTS=1099900` es $10,999.00; `REGISTRATION_PROMO_PRICE_CENTS=820000` establece el precio promocional de $8,200.00. Para retirar la promoción, dejar vacío `REGISTRATION_PROMO_PRICE_CENTS`. `REGISTRATION_CAPACITY=50` fija el máximo de inscripciones pagadas o con una sesión de pago pendiente; `REGISTRATION_CONTACT` permite añadir un medio de contacto cuando exista. Tras modificar `.env`, ejecutar `php artisan config:cache`. El precio y cupo quedan fijados en el servidor; no se aceptan del navegador. La disponibilidad se reserva mientras Stripe mantiene abierto el checkout y se libera tras confirmar con Stripe que la sesión expiró.

Los datos personales se guardan cifrados con `APP_KEY`: hay que respaldar la base y esa clave de forma segura para no perder acceso a los registros. Consultar estados con `php artisan registrations:list` y, desde una sesión administrativa protegida, los datos con `php artisan registrations:list --details`. Las reservas pendientes sin identificador de sesión requieren revisión manual en Stripe antes de liberarlas, para evitar vender dos veces una plaza. El retorno del navegador no marca un pago como completado: se usa el webhook firmado y se verifica el importe.

Antes de activar cobros faltan la configuración DNS y SSL del dominio, el endpoint de Stripe, pruebas con claves de prueba y una forma aprobada de informar la política de privacidad y las condiciones a los compradores. El contacto, la fecha exacta, el horario y la sede siguen pendientes de confirmación.
