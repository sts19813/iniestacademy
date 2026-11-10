# Reglas Iniesta Academy web — Seguridad

Fecha: 9 de noviembre de 2026. Alcance: requisitos para la futura implementación. **Este documento y el JSON asociado no activan protecciones en los archivos actuales.**

## 1. Límite de confianza y datos del navegador

El navegador es un cliente no confiable: cualquiera puede modificar JavaScript, peticiones, IDs y precios. El servidor es la autoridad sobre identidad, permisos, cupos, importes, pagos y saldos.

No es posible mostrar un formulario o expediente sin que los datos visibles pasen temporalmente por la memoria/DOM del navegador autorizado. Tampoco puede impedirse por completo que ese usuario copie o capture lo que ve. El compromiso técnico es **minimización, acceso autorizado y ausencia de persistencia de datos privados en el navegador por parte de la aplicación**.

- Prohibido guardar perfiles, respuestas, saldos, transacciones, tokens, JWT o credenciales en `localStorage`, `sessionStorage`, IndexedDB, Cache API o service workers.
- No implementar borradores locales, modo sin conexión privado ni persistencia de estado del cliente. Los borradores se guardan en servidor.
- Única credencial de sesión en navegador: cookie con identificador aleatorio opaco; no contiene perfil, rol, correo ni tokens de Google. La cookie es una excepción necesaria para autenticar peticiones.
- Cookie `HttpOnly` impide leerla desde JavaScript; no elimina los demás riesgos de XSS. Es necesario controlar scripts y validar operaciones.
- Formularios: evitar persistencia propia y solicitar `autocomplete="off"` cuando corresponda; el navegador puede ignorarlo. No prometer controlar autocompletado, historial o extensiones del usuario.
- Entregar únicamente los campos necesarios a cada rol. No enviar registros completos y ocultarlos con CSS, ni incluir datos privados en comentarios HTML, propiedades hidratadas o paquetes públicos.
- Nunca poner datos personales o secretos en URLs, parámetros de analítica, consola, mensajes de error o reportes de fallos. Redactar callbacks OAuth y datos de checkout en logs.
- Respuestas privadas, incluidas HTML, payloads del framework y API: `Cache-Control: private, no-store`. Desactivar cachés del framework/CDN para rutas con sesión; comprobar aislamiento entre dos usuarios.
- Excluir respuestas privadas del service worker si alguna vez se agrega uno. Las cabeceras HTTP no bastan para controlar su Cache API.
- Al cerrar sesión, revocar en servidor y limpiar estado de pantalla; al volver por historial/reabrir, exigir sesión válida antes de mostrar información privada.

La elección de no persistir secretos en Web Storage y no cachear datos privados se apoya en [OWASP HTML5 Security](https://cheatsheetseries.owasp.org/cheatsheets/HTML5_Security_Cheat_Sheet.html). Es una política del proyecto, no una garantía de invisibilidad ante el titular del navegador.

## 2. Identidad con Google

- Flujo Authorization Code en servidor, usando biblioteca mantenida y PKCE S256; sin tokens de Google persistidos o gestionados por el JavaScript del sitio.
- Validar firma con claves oficiales, emisor, audiencia, expiración, `nonce`, `state` y URI de retorno exacta; comprobar correo verificado si se usa para contacto.
- `state`, `nonce` y verificador PKCE: aleatorios, de un solo uso, vinculados a la operación y con vencimiento de 10 minutos. No registrarlos en logs.
- Identidad primaria: combinación validada `issuer` + `sub`. No usar correo como identificador inmutable ni vincular cuentas automáticamente solo por coincidencia de correo.
- Scopes mínimos: `openid email profile`. No pedir acceso offline ni conservar refresh tokens para un simple inicio de sesión.
- Intercambiar y validar tokens en servidor; descartarlos al terminar si no hay una finalidad adicional aprobada. Nunca guardarlos dentro de la cookie de sesión.
- Redirecciones posteriores únicamente a rutas locales permitidas. Prohibidas URLs de retorno arbitrarias proporcionadas por el cliente.
- Separar clientes OAuth, secretos y callbacks de pruebas y producción; secretos en gestor de secretos, nunca en código público.
- Cliente sin sesión o sin perfil requerido no puede crear orden ni checkout aunque llame directamente a la API.

Ver [Google OpenID Connect](https://developers.google.com/identity/openid-connect/openid-connect) para la validación de identidad y el flujo de servidor. PKCE y los vencimientos aquí descritos son requisitos del proyecto que deben comprobarse en la biblioteca elegida.

## 3. Sesiones y administración

| Parámetro | Valor inicial obligatorio |
| --- | --- |
| Cookie | `__Host-ia_session`; `Secure`; `HttpOnly`; `SameSite=Lax`; `Path=/`; sin `Domain` |
| Persistencia de cookie | Sin `Expires`/`Max-Age`; el vencimiento efectivo siempre se comprueba en servidor |
| Identificador | 32 bytes aleatorios criptográficos; guardar su hash, no su valor original, en BD |
| Cliente | Inactividad máxima 60 minutos; duración absoluta máxima 24 horas |
| Personal interno | Inactividad máxima 15 minutos; duración absoluta máxima 8 horas |
| Reautenticación sensible | Identidad reciente y segundo factor completado en los últimos 5 minutos |
| Segundo factor interno | Obligatorio para propietario, finanzas, admisiones y seguimiento |
| Rotación | Tras login, completar MFA y cualquier cambio de privilegios |
| Revocación | Logout, baja/suspensión, cambio de rol y recuperación de MFA |

`SameSite=Lax` permite el retorno de Google mediante navegación GET; no reemplaza `state` ni la defensa CSRF. Usar cookies temporales opacas separadas si la biblioteca las requiere, sin datos de negocio ni tokens del proveedor. Atributos seguros también para ellas.

Exigir segundo factor verificable por la aplicación —preferentemente WebAuthn/passkey, o TOTP—, con alta autenticada y recuperación controlada. No asumir que usar Google acredita MFA: el servidor debe verificar su propio requisito. Cifrar secretos TOTP en servidor; códigos de recuperación con hash, de un solo uso y nunca en logs. Guardar solo los datos públicos requeridos para verificar WebAuthn.

La primera cuenta propietaria se provisiona por un procedimiento de servidor con identidad exacta verificada. No habrá registro público de administradores, credenciales compartidas ni elevación basada en un campo del formulario. Registrar actor, motivo y revocaciones en cambios de permisos.

Aplicar expiración y revocación del lado servidor, siguiendo la [guía de sesiones OWASP](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html). El cierre del navegador no basta para invalidar una sesión.

## 4. Autorización y validación

- Denegar por defecto. Cada endpoint, acción de servidor, tarea y descarga debe validar identidad, facultad y alcance del recurso.
- Obtener `user_id`, roles, total, moneda y estado desde la sesión/BD; no confiar en valores enviados por el navegador.
- Cliente: filtro obligatorio por titularidad y vínculos autorizados. Personal: rol más sede/programa/cartera asignados, cuando aplique.
- Ocultar un botón no protege una operación. Responder con `401` sin sesión y `403` o `404` según la política de divulgación del recurso.
- Validación de esquema en servidor, tipos y longitudes máximas, normalización de teléfonos/correos, rechazo de campos desconocidos y asignación explícita de campos modificables.
- Consultas parametrizadas; sin concatenar SQL. Escapar salida y evitar insertar HTML no confiable. El contenido enriquecido, si se permite, necesita sanitización.
- CSRF para mutaciones autenticadas con cookies: token y validación de `Origin`; no modificar estado con GET. Flujo OAuth usa `state` propio.
- API del mismo origen, sin CORS externo habilitado por defecto. Si cambia la arquitectura, lista exacta de orígenes y nunca `*` con credenciales.
- Operaciones sensibles: permiso específico, reautenticación reciente y auditoría. Incluye roles, recuperación MFA, reembolsos, comisiones, reprogramaciones y cierre de corte.

Referencia: [OWASP Authorization](https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html).

## 5. Pagos, saldo y consistencia

- Checkout alojado: el sitio no recibe ni almacena PAN, CVV, banda magnética o credenciales bancarias. La pasarela procesa esos datos. El alcance de obligaciones del comercio se verifica con el proveedor antes de activar producción.
- La tarjeta no se pide dentro del formulario de inscripción. Los abonos son pagos iniciados por el cliente, sin cobro automático.
- Precio, descuento autorizado, MXN, cuotas y saldo calculados en servidor. Guardar condiciones aceptadas y versión histórica.
- Crear claves idempotentes por operación de cobro/reembolso y persistirlas antes de llamar a la pasarela. Un reintento de red no debe crear una operación distinta.
- Evitar intentos de pago simultáneos para la misma obligación; reutilizar o expirar la sesión activa según soporte del proveedor. Detectar y gestionar sobrepagos si dos cobros externos aun así se confirman.
- Webhook: validar firma sobre cuerpo original, tolerancia temporal conforme al SDK/proveedor y entorno/cuenta esperados. Restringir tipos de evento procesables.
- El endpoint webhook no usa CSRF de navegador; su autenticación es la firma del proveedor. No eximir otros endpoints.
- No acreditar un cobro por la URL de éxito, un comprobante subido, una captura de pantalla o un estado enviado por cliente.
- Verificar referencia interna, cuenta receptora, moneda, importe y estado final del proveedor antes de aplicar un pago. Un pago asíncrono pendiente no equivale a pagado.
- Deduplicar por proveedor/cuenta/ID de evento y por ID de cobro; el mismo pago puede aparecer en eventos distintos. No depender del orden de entrega.
- Persistir evento/trabajo antes de responder éxito; aplicar movimiento y marca de procesamiento en una transacción. Usar outbox y reintentos para efectos externos.
- Registrar únicamente campos mínimos del evento. Si se requiere conservar el cuerpo original para diagnóstico, usar almacenamiento cifrado restringido y caducidad corta definida; no volcarlo en logs.
- Proteger cupos y aplicaciones de pago mediante transacciones, restricciones y control de concurrencia. Dinero en centavos enteros; política de redondeo explícita.
- Movimientos financieros y cortes cerrados no se sobrescriben ni se borran. Correcciones con movimientos compensatorios y motivo.
- Consultar la API del proveedor para conciliar fallos y eventos fuera de orden; si el estado no es verificable, dejar pendiente y alertar.

La validación de firmas, duplicados y confirmación de cobros debe ajustarse a la pasarela elegida. Referencias de implementación: [webhooks de Stripe](https://docs.stripe.com/webhooks) y [confirmación de órdenes](https://docs.stripe.com/checkout/fulfillment.md?ui=full-page).

## 6. Base de datos, secretos y privacidad

- PostgreSQL sin acceso directo desde el navegador ni puertos abiertos a Internet cuando el alojamiento permita red privada; TLS y restricciones de red entre servicios.
- Cuenta de aplicación con mínimo privilegio, sin superusuario, propiedad de tablas ni `BYPASSRLS`; credenciales de migración separadas.
- Aislamiento por propietario en la capa de datos; añadir RLS como defensa adicional para tablas privadas. Configurar contexto de usuario desde sesión validada y dentro de transacción, evitando fugas al reutilizar conexiones.
- Verificar RLS con la cuenta real de ejecución: propietarios y roles privilegiados pueden omitirla, según [PostgreSQL](https://www.postgresql.org/docs/current/ddl-rowsecurity.html).
- Cifrado en tránsito y en reposo para BD, respaldos y cualquier archivo privado. Campos especialmente sensibles cifrados con claves separadas administradas por el proveedor de secretos.
- No solicitar documentos o datos de salud sin necesidad definida. Cualquier carga futura irá a almacenamiento privado, con validación de tipo/tamaño y acceso autenticado; no a la carpeta pública.
- Guardar secretos en servidor/gestor del alojamiento. Sin secretos en repositorio, HTML, CSS, JavaScript, variables públicas o mapas de código fuente públicos del área privada.
- Si se agregan variables públicas, permitir únicamente valores expresamente públicos: un client ID OAuth o una clave publicable no son secretos; nunca sustituyen controles de servidor.
- Separar desarrollo, pruebas y producción: bases, cuentas de pasarela, Google OAuth y llaves diferentes. Usar datos sintéticos en pruebas.
- Definir matriz de conservación por categoría —prospectos, expedientes, pagos, auditoría y backups— antes de producción; minimizar o anonimizar al vencerla, respetando las obligaciones que se determinen para el negocio.
- Registrar versión de aviso/condiciones y finalidad; marketing separado de la inscripción. Si hay menores, diseñar flujo de tutor y controles específicos antes de recopilar datos.
- No almacenar información personal innecesaria en auditoría. Para datos financieros registrar referencia/cambio, no una copia completa de cada expediente.
- Exportaciones y descargas masivas deshabilitadas en el MVP. Reportes privados disponibles solo en pantalla a roles autorizados.

## 7. Cabeceras, límites y prevención de abuso

Configurar cabeceras HTTP desde el servidor/CDN; no suponer que una etiqueta meta equivale a aplicarlas.

| Control | Parámetro inicial |
| --- | --- |
| Transporte | HTTPS obligatorio en producción, TLS 1.2 mínimo; preferir 1.3 |
| HSTS | `max-age=31536000` tras validar HTTPS; subdominios/preload solo con cobertura verificada |
| Tipo de contenido | `X-Content-Type-Options: nosniff` |
| Referencias | `Referrer-Policy: no-referrer` |
| Marcos | CSP `frame-ancestors 'none'`; `X-Frame-Options: DENY` |
| Permisos | `Permissions-Policy: camera=(), microphone=(), geolocation=()` salvo función aprobada |
| CSP | Recursos propios y orígenes exactos necesarios; `object-src 'none'`, `base-uri 'none'`, `form-action 'self'` |
| Scripts CSP | Nonces aleatorios por respuesta o hashes compatibles; sin `unsafe-eval` ni excepciones amplias en producción |
| Formularios/JSON | Límite inicial de cuerpo: 64 KiB; archivos deshabilitados |
| Webhooks | Límite inicial 256 KiB, ajustar al proveedor; tasa separada y tolerante a ráfagas/reintentos |
| Inicio OAuth | 10 inicios / 10 minutos / IP; callback separado para no romper retornos legítimos |
| MFA | 5 intentos fallidos / 15 minutos / cuenta e IP; bloqueo temporal y auditoría |
| Formulario de interés | 5 envíos / hora / IP; medidas adicionales ante abuso |
| Inicio de checkout | 10 intentos / 10 minutos / cuenta, además del bloqueo de intento simultáneo |
| API privada | 120 solicitudes / minuto / cuenta como punto inicial, ajustar con carga real |
| Paginación | 50 registros por defecto, máximo 100; búsquedas y consultas acotadas |

Límites compartidos entre instancias; responder `429` con `Retry-After`. Confiar cabeceras de IP solo del proxy controlado. Ajustar por uso real sin permitir eludir el límite cambiando un campo del cliente.

La CSP final depende del framework y del checkout. Probar primero en staging, revisar violaciones y activar en modo obligatorio antes de producción. Los reportes CSP también deben redactar URLs. No copiar una política con nonce fijo ni permitir todo un dominio de terceros por comodidad. Referencia: [OWASP CSP](https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html).

## 8. Operación y criterios de aceptación

- Dependencias mantenidas y versiones fijadas con lockfile; revisión de vulnerabilidades y secretos en integración continua.
- Migraciones revisables; cambios destructivos de esquema requieren estrategia de respaldo, compatibilidad y recuperación.
- Logs estructurados con referencia de petición; sin cookies, cabecera Authorization, tokens, cuerpos de formularios o datos de tarjeta.
- Auditoría de consultas administrativas sensibles y modificaciones; acceso restringido y protección contra alteraciones desde el rol de aplicación.
- Alertas por fallos de login/MFA, cambios de permisos, webhooks rechazados, cola atascada, diferencias de conciliación y respaldos fallidos.
- Objetivo inicial de recuperación: RPO máximo 1 hora y RTO máximo 4 horas. Seleccionar un servicio con recuperación puntual/respaldos que lo soporte y demostrarlo mediante una restauración antes de producción.
- Procedimiento de incidentes: revocar sesiones, rotar secretos comprometidos, detener operaciones afectadas y recuperar desde datos verificados. Mantener responsables y contactos operativos definidos.

Bloquean el lanzamiento los siguientes incumplimientos:

1. Falta de backend o ausencia de autenticación/autorización por operación.
2. Acceso de un cliente a datos de otro o de personal fuera de su alcance.
3. Datos privados/tokens persistidos por la aplicación en el navegador o expuestos en recursos públicos/logs.
4. Posibilidad de alterar precios, marcar pagos desde cliente o duplicar aplicaciones por concurrencia/reintentos.
5. Webhooks sin firma válida, sin persistencia durable o sin deduplicación.
6. Personal con acceso operativo sin segundo factor.
7. CSP/cabeceras/caché sin verificar contra la aplicación desplegada.
8. Uso de secretos de producción en pruebas, o credenciales/puertos de BD expuestos.
9. Corte modificable después de cerrar o diferencias financieras sin tratamiento definido.
10. Falta de restauración demostrada, alertas operativas o políticas de privacidad/conservación resueltas.

Los valores de [config/security-baseline.json](config/security-baseline.json) son criterios para implementar y verificar. Antes de desplegar deberá existir validación automática de configuración que falle si faltan orígenes, credenciales o controles obligatorios; esa validación todavía no está programada.

