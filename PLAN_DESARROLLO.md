# Iniesta Academy web — Plan de programación

Fecha: 9 de noviembre de 2026. Estado: planificación; el sistema todavía no está implementado.

## 1. Objetivo y decisiones confirmadas

Crear una landing page con inscripción y venta de programas de Iniesta Academy, perfiles de clientes y panel privado para administrar inscripciones, transacciones, cortes, comisiones y seguimiento de saldos.

- País: México. Moneda: peso mexicano, código `MXN`.
- Compra y perfil: registro/inicio de sesión obligatorio con Google antes de crear una orden de compra.
- Pagos diferidos: **anticipo y abonos con fechas acordadas**. El cliente realiza cada pago; no se harán cargos automáticos.
- Cada cliente verá solamente su perfil, participantes vinculados, inscripciones y pagos.
- La landing será pública; los módulos de clientes y administración requerirán sesión y permisos.
- Los datos de negocio se guardarán en el servidor. La política detallada está en [SEGURIDAD.md](SEGURIDAD.md).

## 2. Estado del proyecto

Archivos revisados: `index.html`, `style.css` y `script.js`. El HTML contiene una estructura inicial; CSS y JavaScript están vacíos. No hay backend, base de datos, autenticación ni integración de pagos.

Estos archivos sirven como base visual. Las funciones privadas necesitan infraestructura de servidor; no se implementarán con almacenamiento local del navegador ni con una clave de base de datos en `script.js`. En esta etapa se conservan los tres archivos originales.

## 3. Arquitectura propuesta

Usar una aplicación modular con un backend central, evitando introducir microservicios antes de necesitarlos.

```text
Visitante / cliente / personal autorizado
                 |
             HTTPS
                 |
       Aplicación web del mismo origen
       ├── Landing pública y recursos estáticos
       ├── Área del cliente
       ├── Panel de administración
       └── Backend / API privada
             ├── Google OIDC: identidad
             ├── PostgreSQL privada: datos y sesiones
             ├── Pasarela: checkout alojado y consulta de cobros
             ├── Receptor de webhooks firmados
             └── Trabajador de tareas y notificaciones
                   ├── Reintentos y conciliación
                   └── Recordatorios y alertas internas
```

| Capa | Propuesta | Responsabilidad |
| --- | --- | --- |
| Aplicación | Next.js + TypeScript, con runtime Node.js mantenido | Landing, rutas privadas y backend en un mismo origen |
| Identidad | Google OpenID Connect mediante biblioteca mantenida | Validación en servidor y sesiones opacas almacenadas en servidor |
| Persistencia | PostgreSQL administrada + migraciones versionadas | Relaciones, restricciones, transacciones y auditoría |
| Acceso a datos | Módulos exclusivos de servidor, consultas parametrizadas | Autorización por operación y propietario; respuestas mínimas |
| Cobros | Checkout alojado de una pasarela por seleccionar | Tarjetas fuera del sitio; cobros vinculados a órdenes internas |
| Trabajo asíncrono | Cola persistente; inicialmente puede usar PostgreSQL | Webhooks, conciliación, vencimientos y recordatorios |
| Operación | Entornos separados, gestor de secretos, monitoreo y respaldos | Despliegue, alertas, restauración y seguimiento de incidentes |

La elección de Next.js es una propuesta de implementación. La biblioteca de autenticación, ORM, proveedor de alojamiento y pasarela se fijarán en la fase 0, verificando soporte para sesiones de servidor y MFA administrativo. No depender de proteger únicamente la interfaz: comprobar permisos dentro de cada operación de servidor, siguiendo la [guía de autenticación de Next.js](https://nextjs.org/docs/app/guides/authentication).

Separación de código propuesta:

```text
src/app/                 páginas y endpoints
src/modules/auth/        identidad, sesiones y segundo factor
src/modules/customers/   perfiles y participantes
src/modules/enrollments/ formularios, programas, cupos e inscripciones
src/modules/payments/    órdenes, abonos, pasarela y webhooks
src/modules/finance/     movimientos, comisiones, conciliación y cortes
src/modules/followup/    prospectos, tareas y recordatorios
src/server/              acceso a datos, permisos y secretos
workers/                 tareas persistentes
db/migrations/           esquema y cambios versionados
tests/                   pruebas de permisos, cobros y flujos críticos
```

## 4. Módulos y comportamiento

### Landing pública

Presentación de la academia, programas, sedes/fechas, beneficios, preguntas frecuentes, precios vigentes y llamada a inscribirse. Diseño adaptable a móvil, navegación accesible, imágenes optimizadas y metadatos para buscadores. Aviso de privacidad, términos y condiciones de compra/cancelación disponibles antes de enviar datos o pagar.

### Registro con Google y perfil

1. El visitante elige un programa y pulsa Inscribirme.
2. El servidor inicia el acceso con Google y valida su respuesta.
3. Se crea o recupera la cuenta usando la identidad estable de Google (`issuer` + `sub`), con restricción única.
4. El cliente completa los campos de contacto requeridos y acepta los documentos aplicables.
5. Si inscribe a otra persona, se registra un participante separado del titular que paga.
6. Al finalizar, el cliente consulta inscripciones, recibos, saldo y próximos vencimientos desde su cuenta.

Pedir solo `openid`, `email` y `profile`; no solicitar acceso a Drive, contactos o Gmail. Usar el flujo de servidor documentado por [Google OpenID Connect](https://developers.google.com/identity/openid-connect/openid-connect). No asignar permisos administrativos por haberse registrado con Google.

### Formulario e inscripciones

Campos configurados por programa y validados en el servidor: participante, programa, sede/fecha, contacto y consentimientos. Datos adicionales solo cuando tengan una finalidad definida. Borradores guardados en servidor, sin autoguardado local.

Si participan menores, separar titular/tutor y participante, registrar la relación y consentimiento aplicable. Los campos de salud, documentos e imágenes requieren una decisión específica de necesidad y acceso antes de añadirse.

Estados de inscripción separados del estado de pago: `draft`, `reserved`, `confirmed`, `cancelled`, `completed`. La regla que confirma un lugar al pagar el anticipo debe fijarse por programa. Reservar cupos de forma transaccional y con vencimiento; un pago recibido después de vencer una reserva pasa a revisión si ya no hay lugar.

### Compra y abonos

1. El servidor verifica sesión, perfil completo, titularidad, cupo y precio vigente.
2. Genera una orden con precio, moneda, descuentos y condiciones congelados.
3. Para pagos diferidos crea anticipo, calendario, montos y aceptación de condiciones. La suma de cuotas debe coincidir con el total de la orden.
4. Cada intento de pago se asocia a una cuota o al saldo autorizado de esa orden. El navegador nunca define el importe definitivo.
5. El servidor crea una sesión de checkout alojado y redirige al cliente.
6. Un webhook firmado, validado y procesado de forma idempotente actualiza el pago, su aplicación y la inscripción.
7. El cliente ve confirmación y saldo consultando al servidor; la URL de retorno no acredita un pago.

Los eventos de la pasarela pueden llegar repetidos o fuera de orden. No producir un segundo cobro ni una segunda inscripción al reintentarlos; confirmar recepción únicamente tras persistir de forma durable el evento o el trabajo. Estas condiciones siguen la [documentación de webhooks de Stripe](https://docs.stripe.com/webhooks), usada aquí como referencia técnica, sin seleccionar aún esa pasarela.

### Panel administrativo

- Resumen de inscripciones, cobros confirmados, saldos, cuotas vencidas y pendientes de conciliación.
- Inscripciones: búsqueda, filtros por programa/sede/estado y expediente del participante con acceso restringido.
- Transacciones: estado, importe, fecha, método, referencia interna, reembolsos y contracargos.
- Finanzas: comisión bancaria real, comisión comercial, netos, conciliación y cortes.
- Seguimiento: prospectos, responsable, historial de contacto, próxima acción y calendario de abonos.
- Auditoría: cambios de permisos, planes, estados, comisiones y cierres, con actor y motivo.

El MVP tendrá reportes en pantalla y paginación. Descargas masivas y exportaciones estarán deshabilitadas inicialmente para evitar copias locales de datos personales; añadirlas requerirá definir expresamente permisos, necesidad y retención.

### Seguimiento de pagos diferidos

La persona puede expresar interés sin comprar; un prospecto no equivale a una cuenta ni a una inscripción pagada. Solicitar el mínimo contacto necesario y registrar la finalidad/consentimiento aplicable.

Cada plan contendrá anticipo, cuotas, fechas, saldo, responsable y condiciones aceptadas. Estados de cuota: pendiente, parcialmente pagada, pagada, vencida o cancelada. Permitir reprogramación con motivo e historial; no sobrescribir el acuerdo anterior. Definir cómo se aplica un pago parcial, un abono anticipado y un reembolso.

Crear tareas internas y preparar recordatorios configurables, por ejemplo tres días antes y el día del vencimiento. El envío de correos se implementará posteriormente con el proveedor y las preferencias de contacto definidos; este plan no envía mensajes. Deduplicar cada notificación por cuota, canal y tipo. No incluir información sensible en enlaces o correos. El cliente vuelve a autenticarse para consultar o pagar.

## 5. Modelo de datos inicial

| Entidades | Datos y restricciones principales |
| --- | --- |
| `users`, `identities`, `sessions` | Perfil mínimo; identidad Google única; sesiones revocables y hash del identificador |
| `roles`, `role_assignments`, `mfa_credentials` | Facultades explícitas, segundo factor y trazabilidad de asignaciones |
| `participants`, `guardian_links` | Participante independiente del pagador; vínculos autorizados con el titular |
| `programs`, `editions`, `price_versions`, `capacity_reservations` | Sede/fecha, cupo, precios históricos y reservas con vencimiento |
| `enrollments`, `form_answers` | Usuario titular, participante, edición, estado y respuestas permitidas |
| `consent_records` | Versión del documento, finalidad, aceptación y fecha; sin aceptación de marketing preseleccionada |
| `orders`, `order_items` | Total y moneda congelados; relación con inscripción y versión de precio |
| `payment_plans`, `plan_versions`, `installments` | Condiciones, cuotas, vencimientos y reprogramaciones sin perder historia |
| `payment_attempts`, `payments`, `payment_allocations` | Intentos separados de cobros; referencias únicas y aplicación del dinero a cuotas |
| `webhook_inbox`, `outbox_jobs` | Deduplicación, procesamiento durable, reintentos y alertas |
| `financial_movements`, `refunds`, `disputes` | Movimientos trazables de cobros, devoluciones y contracargos |
| `provider_fees`, `settlements`, `payouts` | Costos reales y depósitos bancarios; pendientes de conciliación |
| `commission_rules`, `commissions` | Reglas versionadas, beneficiario, base, devengo, liquidación y reversos |
| `cutoffs`, `cutoff_items` | Periodo, moneda, movimientos incluidos, responsable, estado y cierre |
| `leads`, `followup_tasks`, `contact_history` | Prospecto, responsable, próxima acción e historial restringido |
| `audit_events` | Actor, acción, entidad, fecha y cambio mínimo necesario, sin secretos |

Claves foráneas, índices de búsqueda, restricciones únicas y validaciones de importes en base de datos. Guardar dinero en unidades mínimas enteras —centavos para MXN—, sin números de coma flotante. Registrar fechas de eventos en UTC y presentar vencimientos/cortes en `America/Merida` como zona propuesta. Fechas de calendario acordadas: conservar fecha local y zona, no convertirlas arbitrariamente a medianoche UTC.

## 6. Reglas de finanzas y cortes

- Distinguir intento, cobro confirmado, fondos disponibles en pasarela y depósito en banco. Un cobro no implica que ya exista un depósito.
- Saldo de una orden = total vigente menos pagos confirmados aplicados, ajustado por devoluciones y cancelaciones según su efecto documentado sobre la deuda.
- Comisiones comerciales: definir beneficiario, porcentaje o monto fijo, base antes/después de descuentos, tratamiento de impuestos, redondeo y momento de devengo. Propuesta: devengar sobre importes efectivamente cobrados; no sobre promesas de pago.
- Comisión bancaria: mostrar estimada mientras se conoce el costo real; conciliar contra el desglose del proveedor, incluidos impuestos o cargos adicionales que correspondan. No fijar una tarifa inventada en el código.
- Mantener separadas las comisiones comerciales y bancarias. Registrar si la comercial fue pagada desde el saldo de la pasarela o fuera de él para no descontarla dos veces.
- Neto de cobros del periodo: cobros menos devoluciones, débitos por contracargos y costos reales aplicables. Neto comercial: mostrar por separado la comisión devengada/pagada. El reporte debe indicar la base temporal y el estado de conciliación.
- Conciliar depósitos contra liquidaciones del proveedor, reservas, ajustes y diferencias de fechas; no deducirlos solamente de la fórmula del neto.
- Cortes por intervalo semiabierto `[inicio, fin)`, zona y moneda explícitas; incluir IDs exactos de movimientos, totales, pendientes y responsable. Propuesta inicial: vista diaria y cierre por finanzas.
- Estados de corte: abierto, en revisión, cerrado. Bloquear el cierre si existen diferencias sin resolver o sin explicación autorizada.
- Un corte cerrado es inmutable. Correcciones, comisiones tardías y reembolsos posteriores generan ajustes trazables en otro periodo, relacionados con el corte original.
- No borrar pagos o movimientos para corregir cifras. Los reversos deben conservar el vínculo al original.
- Reembolso manual: solicitud autorizada y auditada, llamada idempotente a la pasarela y confirmación de su resultado. No marcarlo completado por pulsar un botón.

## 7. Roles iniciales

| Rol | Alcance |
| --- | --- |
| Visitante | Landing y envío limitado de interés; no crea compras |
| Cliente | Sus datos, participantes vinculados, inscripciones, órdenes, abonos y recibos |
| Admisiones | Inscripciones autorizadas y estado de pago necesario; sin comisiones ni reembolsos |
| Seguimiento | Prospectos/cartera asignados, saldos y acciones de contacto; sin finanzas globales |
| Finanzas | Transacciones, conciliación, comisiones y cortes; reembolsos con facultad adicional |
| Propietario | Configuración y gestión de permisos; facultades financieras explícitas, sin posibilidad de borrar auditoría |

Los roles internos requieren segundo factor. Comprobar además el alcance por sede, programa o cartera cuando corresponda. Los IDs difíciles de adivinar no reemplazan la autorización. [OWASP recomienda denegar por defecto y validar permisos en cada petición](https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html).

## 8. Fases de programación y criterios de entrega

Estimación orientativa para una persona desarrolladora con dedicación completa: **6–8 semanas**, sujeta a diseño, campos finales, disponibilidad de cuentas de prueba y revisión de condiciones. No constituye una fecha comprometida.

| Fase | Trabajo | Estimación | Criterio de salida |
| --- | --- | --- | --- |
| 0. Definición | Campos, programas, precios, cupos, política de abonos/comisiones, pasarela, hosting y diseño | 2–3 días | Reglas documentadas y sandbox disponible |
| 1. Fundaciones | Proyecto TypeScript, BD, migraciones, entornos, secretos, permisos, Google, sesiones y MFA interno | 4–5 días | Sesiones revocables; cliente A no accede a B; personal sin MFA bloqueado |
| 2. Experiencia | Landing móvil, perfil, participantes, formulario, consentimientos y reservas de cupo | 4–5 días | Inscripción validada en servidor y datos privados sin almacenamiento local |
| 3. Cobros | Órdenes, anticipo/cuotas, checkout alojado, webhooks, idempotencia y recibos | 5–7 días | Un pago de prueba produce una sola aplicación aun con reintentos y concurrencia |
| 4. Administración | Listados, filtros, detalle, prospectos, tareas, cartera y reprogramaciones | 4–5 días | Permisos por rol/cartera y seguimiento de cada cuota con historial |
| 5. Finanzas | Costos reales, reglas de comisión, reembolsos, conciliación y cortes | 4–5 días | Caso completo de cobro parcial, liquidación, comisión, cierre y ajuste posterior |
| 6. Validación y salida | Pruebas críticas, accesibilidad, seguridad, restauración, monitoreo y despliegue | 4–5 días | Criterios de seguridad cumplidos y operación recuperable |

Cada fase depende de la anterior para sus entregas privadas; el diseño de la landing puede avanzar mientras se prepara la infraestructura. Publicar producción solo al completar las verificaciones de [SEGURIDAD.md](SEGURIDAD.md).

## 9. Pruebas que deben existir al implementar

- Flujo completo: Google → perfil → formulario → anticipo → confirmación → abono → saldo cero.
- Cliente no autenticado, sesión vencida/revocada, personal sin MFA y cliente intentando cambiar su rol.
- Cliente A intentando leer/modificar una inscripción, cuota o recibo de B por cualquier endpoint.
- Manipulación de precio, descuento, moneda, cuota y referencia de pago en el navegador.
- Callback Google falsificado/reutilizado; inicio de sesión no permite enlazar cuentas por correo sin verificación.
- Firma de webhook inválida, duplicados, eventos fuera de orden, caídas de BD y reintentos concurrentes.
- Doble clic de pago, dos sesiones de checkout y dos clientes intentando ocupar el último cupo.
- Pago tardío, pago parcial, reembolso parcial, contracargo y comisión bancaria conocida después del cierre.
- Conciliación contra datos de sandbox del proveedor y cierre con ajustes verificables.
- Confirmación de ausencia de datos privados en almacenamiento del navegador, cachés compartidas, HTML público, URLs y logs.
- Validación móvil/accesibilidad, recuperación de respaldo y alertas de fallos de tareas/webhooks.

## 10. Pendientes de negocio antes de programar cobros

1. Pasarela y cuenta comercial que se utilizarán; métodos admitidos y datos reales de comisiones.
2. Programas, sedes, fechas, precios, descuentos y cupos.
3. Campos exactos del formulario y si habrá menores de edad.
4. Anticipo mínimo, número de cuotas, fechas, tolerancia al atraso y condición para conservar el lugar.
5. Tratamiento de cancelaciones, devoluciones, contracargos y saldos después de una devolución.
6. Beneficiarios y fórmula de las comisiones comerciales; quién puede autorizarlas y pagarlas.
7. Responsables del panel, zona horaria definitiva, periodicidad y autorización de cortes.
8. Dominio, alojamiento, textos de privacidad/compra, periodos de conservación y necesidades de facturación.

No se presume facturación fiscal integrada, financiamiento bancario, cargos recurrentes ni envío de WhatsApp dentro del MVP. Si se requieren, incorporar su alcance y validación antes de implementarlos.

## 11. Documentos de control

- [SEGURIDAD.md](SEGURIDAD.md): requisitos obligatorios y criterios de aceptación.
- [config/security-baseline.json](config/security-baseline.json): parámetros iniciales legibles por máquina. Son una especificación; todavía no los aplica ninguna aplicación.

