# Iniesta Academy Mérida

Landing adaptable de Iniesta Academy Mérida, migrada a Laravel 13 a partir de la versión HTML, CSS y JavaScript. Mantiene el diseño, las imágenes, las fuentes y las interacciones originales.

## Requisitos y ejecución

Se requieren PHP 8.3 o superior, las extensiones habituales de Laravel y Composer. No se necesita Node.js ni una base de datos para esta landing.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Abrir `http://127.0.0.1:8000`. En un servidor web, configurar `public/` como directorio público; no exponer la raíz del proyecto. Para producción, configurar `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` y una clave propia en `.env`.

La página está en `resources/views/landing.blade.php`, sus recursos en `public/` y la ruta en `bootstrap/app.php`. La ruta es pública y no inicia sesiones ni utiliza cookies. Las cabeceras de la respuesta se configuran en `app/Http/Middleware/SecurityHeaders.php`; el servidor web debe aplicar las mismas cabeceras a los recursos estáticos si se requiere esa cobertura en producción.

Para ejecutar las comprobaciones:

```sh
php artisan test
node --check public/script.js # opcional
```

## Alcance

Esta entrega es una **vista previa**, no el sistema de inscripciones. No hay formularios, autenticación, cobros ni reservas. Los botones muestran información provisional; no se recopilan datos personales ni se conectan servicios externos. Se mantienen `SEGURIDAD.md`, `PLAN_DESARROLLO.md` y `config/security-baseline.json` como documentación de las fases futuras.

El contenido comercial del diseño requiere confirmación antes de publicarse: hay diferencias entre secciones en precio, fechas, edades y dirección, y debe confirmarse el horario. Los datos de contacto son ficticios y los perfiles sociales, documentos legales y proveedor de pago siguen pendientes. Las capturas en `qa/` corresponden a la versión estática anterior a la migración.
