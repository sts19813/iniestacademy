# Iniesta Academy Mérida

Landing adaptable de Iniesta Academy Mérida, migrada a Laravel 13 a partir de la versión HTML, CSS y JavaScript. Mantiene el diseño, las imágenes, las fuentes y las interacciones originales.

## Requisitos y ejecución

Se requieren PHP 8.3 o superior, las extensiones habituales de Laravel y Composer. No se necesita Node.js. El sitio utiliza una base SQLite privada para inscripciones y configuración.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
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

La clínica está programada del 20 al 24 de marzo de 2027. El horario, las edades y la sede están pendientes de confirmación. Los datos de contacto son ficticios y los perfiles sociales, documentos legales y proveedor de pago siguen pendientes. Las capturas en `qa/` corresponden a la versión estática anterior a la migración.

## Inscripciones con Stripe (preparación)

El flujo de inscripciones utiliza la configuración inicial de `.env` y permanece desactivado mientras `REGISTRATION_ENABLED=false`. Requiere una base SQLite privada y persistente, ejecutando la migración de inscripciones; no debe usarse un archivo temporal ni exponerse la base desde `public/`. Configurar una clave de Stripe en `STRIPE_SECRET` y un secreto **propio del endpoint** en `STRIPE_WEBHOOK_SECRET`. El webhook `/stripe/webhook` debe estar disponible por HTTPS en el dominio público y recibir `checkout.session.completed`, `checkout.session.async_payment_succeeded` y `checkout.session.async_payment_failed` antes de activar inscripciones.

Los importes se expresan en centavos mexicanos: `REGISTRATION_PRICE_CENTS=1099900` es $10,999.00; `REGISTRATION_PROMO_PRICE_CENTS=820000` establece el precio promocional de $8,200.00. Para retirar la promoción, dejar vacío `REGISTRATION_PROMO_PRICE_CENTS`. `REGISTRATION_CAPACITY=50` fija el máximo de inscripciones pagadas o con una sesión de pago pendiente; `REGISTRATION_CONTACT` permite añadir un medio de contacto cuando exista. Una vez guardados desde el panel, los ajustes de inscripción prevalecen sobre `.env`. Para cambios de claves en `.env`, ejecutar `php artisan config:cache`. El precio y cupo quedan fijados en el servidor; no se aceptan del navegador. La disponibilidad se reserva mientras Stripe mantiene abierto el checkout y se libera tras confirmar con Stripe que la sesión expiró.

Los datos personales se guardan cifrados con `APP_KEY`: hay que respaldar la base y esa clave de forma segura para no perder acceso a los registros. Consultar estados con `php artisan registrations:list` y, desde una sesión administrativa protegida, los datos con `php artisan registrations:list --details`. Las reservas pendientes sin identificador de sesión requieren revisión manual en Stripe antes de liberarlas, para evitar vender dos veces una plaza. El retorno del navegador no marca un pago como completado: se usa el webhook firmado y se verifica el importe.

Antes de activar cobros faltan la configuración DNS y SSL del dominio, el endpoint de Stripe, pruebas con claves de prueba y una forma aprobada de informar la política de privacidad y las condiciones a los compradores. El contacto, el horario y la sede siguen pendientes de confirmación.


## Panel administrativo

El panel está en `/admin/acceso`. La primera cuenta se crea desde una sesión SSH de confianza con `php artisan admin:setup admin`: el comando solicita una contraseña de al menos 14 caracteres sin mostrarla, muestra una clave para una aplicación de autenticación TOTP y pide un código para verificarla antes de guardar. La clave TOTP se muestra una sola vez; no debe compartirse por chat. Como alternativa, `php artisan admin:activation-link admin` genera un enlace temporal de un solo uso: la persona administradora define su contraseña y vincula su autenticador en el navegador. El enlace vence en 30 minutos y deja de funcionar al usarse. Una vez iniciada la activación, debe completarse en 15 minutos. No hay registro público de administradores.

Si se pierde el acceso, `php artisan admin:reset admin` sustituye contraseña y autenticador desde SSH y revoca las sesiones existentes. El acceso requiere contraseña y código, limita intentos y cierra la sesión tras 15 minutos de inactividad o 8 horas como máximo. Los cambios de configuración requieren confirmar nuevamente ambos factores.

El panel muestra los registros y sus detalles, y permite configurar precio regular, promoción, cupo, contacto público y apertura de inscripciones. Las claves de Stripe y del webhook permanecen en el entorno del servidor y no se muestran ni editan desde el panel. Los cambios de configuración y las consultas de registros se anotan en `admin_audit_events` sin almacenar contraseñas ni datos personales en el registro de auditoría.

Los ajustes guardados en la base prevalecen sobre `.env`; respaldar la base SQLite y `APP_KEY` de manera segura. Un cambio de precio se verifica al enviar el formulario para que el comprador revise el importe actualizado antes de continuar. Las inscripciones existentes conservan su importe original.
