# Iniesta Academy Mérida

Landing adaptable a escritorio, tablet y móvil, implementada en HTML, CSS y JavaScript a partir del [nodo 74:723 de Figma](https://www.figma.com/design/tplk44ubAAJfl24TMxP9TR/Iniesta-Academy?node-id=74-723).

## Uso

Requiere Node.js 20 o superior. No requiere instalar dependencias.

```sh
npm run dev
```

Abrir `http://127.0.0.1:4173`. El servidor genera `dist/`, sirve únicamente los recursos públicos y aplica las cabeceras de la vista previa. Para revisar y empaquetar:

```sh
npm run check
npm run build
```

`dist/` contiene la landing, sus estilos, JavaScript, fuentes e imágenes. Los documentos internos, la referencia de Figma, los scripts y la configuración de seguridad no se sirven.

## Entrega visual

Se conservan las fotografías, recortes, mapa, iconos, logotipos, tipografías Raleway/Inter Tight, colores y composición del diseño. Se adaptan las columnas a móvil y se añaden menú accesible, navegación por anclas, preguntas desplegables y ventanas informativas con cierre por Escape y retorno del foco.

Se corrige la errata de marca `INESTA` a `INIESTA` en el título. La respuesta original de Figma está en `design/figma-reference.txt` como referencia de implementación; no es un recurso del sitio.

## Alcance y reglas

Esta entrega es una **vista previa de la landing**, no el sistema de inscripciones. Se aplican [SEGURIDAD.md](SEGURIDAD.md), [PLAN_DESARROLLO.md](PLAN_DESARROLLO.md) y [config/security-baseline.json](config/security-baseline.json). No se cambia el estado de especificación de la política: los controles de backend todavía no están implementados.

- No hay formularios, autenticación simulada, cobros ni confirmaciones de plaza ficticias.
- No se guardan datos personales o credenciales en el navegador, no hay analítica ni solicitudes a servicios externos desde la página.
- Las acciones de inscripción explican que aún no están abiertas. Las acciones legales, de contacto, ubicación y redes explican lo que falta confirmar.
- No se publica un lanzamiento comercial ni se habilitan inscripciones. Se permite revisar esta landing localmente o como vista previa privada sin captura de datos.

Antes de activar el sistema completo deben implementarse las fases de servidor del plan: Google OIDC, sesiones opacas, PostgreSQL privada, permisos y MFA, cupos transaccionales, órdenes y abonos, checkout alojado, webhooks firmados e idempotentes, auditoría, conciliación, comisiones y cortes. También se requieren las verificaciones operativas y de restauración establecidas en seguridad.

## Diferencias que requieren confirmación

Se preserva el contenido visual del diseño para su revisión. **No debe publicarse como información comercial vigente** hasta resolver:

| Campo | Banda de cifras | Otras secciones |
| --- | --- | --- |
| Precio | $9,991 MXN | $10,909.00 tachado y oferta de $8,200 MXN |
| Fecha | 6 a 11 Mar / Verano 2027 | 6 al 11 de marzo de 2026 |
| Edad | 6 a 16 años | 6 a 17 años |
| Horario | No indicado | 8 am – 12 am; confirmar si se pretendía mediodía |

El porcentaje de descuento también debe validarse con el precio aprobado. Los canales de contacto están marcados como ficticios en Figma y se mantienen identificados como propuesta. Hay dos direcciones distintas; por eso el botón de ubicación no envía al visitante a una sede sin confirmar. Los perfiles sociales, documentos legales y proveedor de cobro no están confirmados.

## Verificación realizada

- Chrome aislado a 1440, 1024, 768, 390 y 320 px: sin desbordamiento horizontal, texto recortado, imágenes rotas ni errores de consola.
- Navegación, menú móvil, preguntas desplegables, nueve tipos de diálogo, cierre con Escape y devolución del foco comprobados.
- Cookies y Web Storage vacíos después de las interacciones. Sin formularios ni conexiones externas de la aplicación.
- Cabeceras HTTP de la vista previa local verificadas; documentos internos devuelven 404 y POST devuelve 405.
- Resultados y geometría de recursos: `qa/report.json`. Capturas locales: `qa/screenshots/` (excluidas de Git y del sitio).

La prueba opcional `scripts/qa.cjs` requiere Playwright de desarrollo y una instalación de Chrome. Puede ejecutarse con `PLAYWRIGHT_MODULE` y `CHROME_EXECUTABLE` apuntando a esas instalaciones. No añade dependencias al sitio publicado.
