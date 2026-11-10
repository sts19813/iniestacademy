<!doctype html>
<html lang="es-MX">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Descubre Iniesta Academy Mérida: la metodología de Andrés Iniesta, formación, valores y una experiencia de fútbol para niños y niñas." />
    <meta name="theme-color" content="#101e24" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <meta name="robots" content="noindex, nofollow" />
    <link rel="preload" href="{{ asset('assets/raleway-800.ttf') }}" as="font" type="font/ttf" crossorigin />
    <link rel="preload" href="{{ asset('assets/img02HeroElJuegoEmpiezaContigo.png') }}" as="image" />
    <link rel="stylesheet" href="{{ asset('assets/fonts.css') }}" />
    <link rel="stylesheet" href="{{ asset('style.css') }}?v={{ substr(md5_file(public_path('style.css')), 0, 12) }}" />
    <title>Iniesta Academy Mérida · El juego empieza contigo</title>
  </head>
  <body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <header class="site-header">
      <div class="container header-inner">
        <a class="brand" href="#inicio" aria-label="Iniesta Academy Mérida, inicio"><img src="{{ asset('assets/imgLogoOficialSobreFondoOscuro1.svg') }}" alt="Iniesta Academy" width="160" height="46" /></a>
        <button class="menu-toggle" type="button" aria-controls="navegacion" aria-expanded="false"><span class="menu-lines" aria-hidden="true"></span><span class="menu-label">Menú</span></button>
        <nav id="navegacion" class="main-nav" aria-label="Navegación principal">
          <a href="#experiencia">La experiencia</a><a href="#programa">Programa</a><a href="#sede">Sede y precio</a>
        </nav>
        <button class="button button-paper header-cta" data-dialog="inscripcion" type="button">Solicitar plaza<img src="{{ asset('assets/imgIaIconArrowUpRight.svg') }}" alt="" width="24.3333" height="24.3333" /></button>
      </div>
    </header>
    <main id="contenido">
      <section id="inicio" class="hero" aria-labelledby="hero-title" data-node-id="74:725">
        <img class="hero-image" src="{{ asset('assets/img02HeroElJuegoEmpiezaContigo.png') }}" alt="Entrenadores de Iniesta Academy compartiendo una sesión con jóvenes futbolistas" width="1440" height="879" fetchpriority="high" />
        <div class="hero-overlay"></div>
        <div class="container hero-content">
          <p class="hero-eyebrow">BIENVENIDOS A</p>
          <h1 id="hero-title">INIESTA ACADEMY<br />MÉRIDA</h1>
          <p class="hero-description">Una clínica de fútbol para niñas, niños y sus familias, basada en la metodología y los conceptos futbolísticos de Andrés Iniesta.</p>
          <div class="hero-actions">
            <a class="button button-grey" href="#academia">Saber más<img src="{{ asset('assets/imgIaIconArrowUpRight.svg') }}" alt="" width="24.3333" height="24.3333" /></a>
            <a class="button button-turquoise hero-cta" href="#experiencia">Quiero vivir la experiencia<img src="{{ asset('assets/imgIaIconArrowUpRight1.svg') }}" alt="" width="24.3333" height="24.3333" /></a>
          </div>
        </div>
      </section>
      <div class="ticker ticker-black" aria-label="Únete a la familia · Join the family"><div class="ticker-track" aria-hidden="true"><div class="ticker-group"><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span></div><div class="ticker-group"><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span></div></div></div>
      <section class="facts" aria-label="La clínica en cifras" data-node-id="74:746">
        <dl class="container facts-grid">
          <div><dd>05</dd><dt>DÍAS DE FÚTBOL</dt></div><div><dd>5 a 18</dd><dt>AÑOS · TODOS LOS NIVELES</dt></div><div><dd>20 al 24</dd><dt>MARZO 2027</dt></div><div><dd>${{ number_format((config('registration.promotion_amount') ?: config('registration.regular_amount')) / 100, (config('registration.promotion_amount') ?: config('registration.regular_amount')) % 100 === 0 ? 0 : 2) }} <small>MXN</small></dd><dt>TOTAL CON UNIFORME</dt></div>
        </dl>
      </section>
      <section id="academia" class="section academy" aria-labelledby="academy-title" data-node-id="74:773">
        <div class="container split">
          <div class="section-copy"><h2 id="academy-title"><span class="accent">¿QUÉ ES</span><br />INIESTA<br />ACADEMY?</h2><div class="body-copy"><p>Es una metodología de entrenamiento basada en la forma de ver y entender el juego de Andrés Iniesta, surgiendo de su propia motivación para acercarse a la formación de niños y niñas por medio del fútbol.</p><p>Iniesta Academy busca desarrollar a niños y niñas a nivel técnico-táctico para que entiendan la importancia que tiene la toma de decisiones inteligentes en el campo.</p></div></div>
          <div class="editorial-photo academy-photo"><img src="{{ asset('assets/imgEntrenamientoOficialReferenciaRojoVino.png') }}" alt="Niños entrenando con el balón en una sesión de Iniesta Academy" loading="lazy" /></div>
        </div>
      </section>
      <section id="metodologia" class="section benefits" aria-labelledby="benefits-title" data-node-id="74:760">
        <div class="container">
          <div class="section-heading centered"><p class="eyebrow">POR QUÉ INIESTA ACADEMY MÉRIDA</p><h2 id="benefits-title">Formación con <span class="accent">visión europea.</span><br />Pasión por jugar en Mérida.</h2></div>
          <div class="benefit-grid">
            <article class="benefit-card"><img src="{{ asset('assets/imgFrame5.svg') }}" alt="" width="28" height="28" loading="lazy" /><div><h3>Entrenadores que acompañan</h3><p>Atención cercana para aprender, ganar confianza y disfrutar del fútbol.</p></div></article>
            <article class="benefit-card"><img src="{{ asset('assets/imgFrame4.svg') }}" alt="" width="28" height="28" loading="lazy" /><div><h3>Metodología Iniesta</h3><p>Técnica, inteligencia y valores para comprender cada momento del juego.</p></div></article>
            <article class="benefit-card"><img src="{{ asset('assets/imgFrame3.svg') }}" alt="" width="28" height="28" loading="lazy" /><div><h3>Una visión internacional</h3><p>Distintas culturas y formas de jugar enriquecen la experiencia de aprendizaje.</p></div></article>
            <article class="benefit-card"><img src="{{ asset('assets/imgFrame2.svg') }}" alt="" width="28" height="28" loading="lazy" /><div><h3>Desarrollo integral</h3><p>Hábitos, autonomía y respeto para crecer dentro y fuera de la cancha.</p></div></article>
            <article class="benefit-card"><img src="{{ asset('assets/imgFrame1.svg') }}" alt="" width="28" height="28" loading="lazy" /><div><h3>Grupos por edad y nivel</h3><p>Sesiones adaptadas para que cada jugador encuentre nuevos retos.</p></div></article>
            <article class="benefit-card"><img src="{{ asset('assets/imgFrame.svg') }}" alt="" width="28" height="28" loading="lazy" /><div><h3>Fútbol en Mérida</h3><p>Un entorno cercano para entrenar, compartir y disfrutar en equipo.</p></div></article>
          </div>
        </div>
      </section>
      <section id="experiencia" class="section experience" aria-labelledby="experience-title" data-node-id="81:596">
        <div class="container">
          <div class="split">
            <div class="section-copy"><p class="eyebrow">LA EXPERIENCIA</p><h2 id="experience-title">Una experiencia<br />irrepetible</h2><p class="body-copy">Vive una semana intensiva llena de fútbol, diseñada para que las jugadoras y jugadores desarrollen sus habilidades técnicas y prácticas bajo un programa de entrenamiento desarrollado por Andrés Iniesta y equipo de Iniesta Academy.</p><ul class="experience-details"><li>Fecha: 20 al 24 de marzo de 2027</li><li>Horario: por confirmar</li><li>Sede: Centro Universitario Montejo, Mérida, Yucatán</li><li>Edades: 5 a 18 años</li></ul></div>
            <div class="editorial-photo experience-photo"><img class="photo-underlay" src="{{ asset('assets/imgEntrenamientoOficialReferenciaRojoVino.png') }}" alt="" loading="lazy" /><img class="photo-cover" src="{{ asset('assets/imgEntrenamientoOficialReferenciaRojoVino1.png') }}" alt="Un joven futbolista en la cancha al atardecer" loading="lazy" /></div>
          </div>
          <div class="experience-grid"><article><p class="eyebrow">01</p><h3>TU FORMA DE JUGAR</h3><p>Técnica, visión y decisiones para entender mejor cada jugada.</p></article><article><p class="eyebrow">02</p><h3>CRECER EN EQUIPO</h3><p>Respeto, compañerismo y confianza. Dentro y fuera del campo.</p></article><article><p class="eyebrow">03</p><h3>TODO PARA DISFRUTAR</h3><p>Uniforme, hidratación, acompañamiento y diploma incluidos.</p></article></div>
        </div>
      </section>
      <section id="programa" class="section included" aria-labelledby="included-title" data-node-id="81:674">
        <div class="container"><h2 id="included-title" class="centered">¿Qué incluye?</h2>
          <div class="included-grid">
            <article class="included-card"><img src="{{ asset('assets/imgFrame6.svg') }}" alt="" width="44.8" height="44.8" loading="lazy" /><h3>KIT DE ENTRENAMIENTO</h3><p>1 botella + 1 uniforme de fútbol<br />(camiseta + pantalones + medias)</p><button class="text-button accent" data-dialog="contacto" type="button">¿Tienes dudas? Contáctanos</button><a class="button button-black" href="#preguntas">Más información<img src="{{ asset('assets/imgIaIconArrowUpRight1.svg') }}" alt="" width="24.3333" height="24.3333" /></a></article>
            <article class="included-card"><img src="{{ asset('assets/imgFrame7.svg') }}" alt="" width="44.8" height="44.8" loading="lazy" /><h3>CLINICS</h3><p>5 días de entrenamiento deportivo con el staff y entrenadores de Iniesta Academy Global. Curso intensivo donde niños y niñas aprenden y disfrutan los conceptos futbolísticos de Andrés Iniesta.</p><button class="text-button accent" data-dialog="contacto" type="button">¿Tienes dudas? Contáctanos</button><a class="button button-black" href="#preguntas">Más información<img src="{{ asset('assets/imgIaIconArrowUpRight1.svg') }}" alt="" width="24.3333" height="24.3333" /></a></article>
          </div>
        </div>
      </section>
      <div class="ticker ticker-turquoise" aria-label="Únete a Iniesta Academy"><div class="ticker-track" aria-hidden="true"><div class="ticker-group"><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong></div><div class="ticker-group"><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong><strong>ÚNETE A INIESTA ACADEMY</strong></div></div></div>
      <section id="sede" class="section venue" aria-labelledby="venue-title" data-node-id="74:799">
        <div class="container"><p class="eyebrow venue-eyebrow">03 / NOS VEMOS EN LA CANCHA</p>
          <div class="split">
            <div class="venue-copy"><h2 id="venue-title">TU PRÓXIMA<br />CANCHA: MÉRIDA.</h2><p class="venue-name">Centro Universitario Montejo<br />Mérida, Yucatán</p><a class="map-photo" href="https://maps.app.goo.gl/fKa6pLhiDpGuDqJ48" target="_blank" rel="noopener noreferrer" aria-label="Ver Centro Universitario Montejo en Google Maps"><img src="{{ asset('assets/imgRectangle1.png') }}" alt="Mapa de Centro Universitario Montejo en Mérida" loading="lazy" /></a><a class="button button-turquoise map-button" href="https://maps.app.goo.gl/fKa6pLhiDpGuDqJ48" target="_blank" rel="noopener noreferrer">Ver ubicación<img src="{{ asset('assets/imgIaIconArrowUpRight1.svg') }}" alt="" width="24.3333" height="24.3333" /></a></div>
            <article class="price-card" aria-labelledby="price-title">@if (config('registration.promotion_amount') && config('registration.regular_amount') > config('registration.promotion_amount'))<span class="discount">{{ (int) round((1 - config('registration.promotion_amount') / config('registration.regular_amount')) * 100) }}% de descuento por tiempo limitado</span>@endif<h3 id="price-title" class="price">@if (config('registration.promotion_amount'))<del>${{ number_format(config('registration.regular_amount') / 100, config('registration.regular_amount') % 100 === 0 ? 0 : 2) }}</del>@endif<span>${{ number_format((config('registration.promotion_amount') ?: config('registration.regular_amount')) / 100, (config('registration.promotion_amount') ?: config('registration.regular_amount')) % 100 === 0 ? 0 : 2) }}</span><small>MXN</small></h3><p>Precio total por jugador. Uniforme incluido.<br />Cupo de referencia: {{ config('registration.capacity') }} participantes.</p><ul class="price-includes"><li>Sesiones de entrenamiento y partidos</li><li>Kit de uniforme y diploma</li><li>Hidratación y acompañamiento en cancha</li></ul><div class="payment-info"><h4>Pagos aceptados</h4><ul><li><img src="{{ asset('assets/imgCreditCard.svg') }}" alt="" width="24" height="24" />Métodos disponibles en Stripe</li></ul></div>@if (config('registration.enabled'))<a class="button button-black full-width registration-button" href="{{ route('registration.create') }}"><span>Inscríbete ahora<br />(Cupo limitado)</span></a>@else<button class="button button-black full-width registration-button" data-dialog="inscripcion" type="button"><span>Inscripciones próximamente</span></button>@endif<p class="powered-by">Powered by <strong>StomTickets</strong></p></article>
          </div>
        </div>
      </section>
      <section id="preguntas" class="section faq" aria-labelledby="faq-title" data-node-id="74:815">
        <div class="container"><h2 id="faq-title">Preguntas frecuentes</h2><div class="faq-list">
          <details open><summary>¿Necesita experiencia previa?</summary><p>No. Los grupos se organizan por edad y nivel para que cada jugador pueda disfrutar y aprender.</p></details>
          <details open><summary>¿Qué debe llevar cada día?</summary><p>Botas o tenis para cancha, botella de agua, protector solar y muchas ganas de jugar.</p></details>
          <details open><summary>¿Enviar el formulario reserva una plaza?</summary><p>El pago se procesa de forma segura en Stripe. Conserva tu comprobante de pago.</p></details>
        </div></div>
      </section>
      <div class="ticker ticker-wine" aria-label="Únete a la familia · Join the family"><div class="ticker-track" aria-hidden="true"><div class="ticker-group"><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span></div><div class="ticker-group"><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span><strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span></div></div></div>
    </main>
    <footer class="site-footer" data-node-id="74:827"><div class="container"><div class="footer-grid">
      <div class="footer-brand"><a href="#inicio" aria-label="Volver al inicio"><img src="{{ asset('assets/imgLogoOficialSobreFondoOscuro.svg') }}" alt="Iniesta Academy" width="208" height="60" loading="lazy" /></a><h3>MÉRIDA, YUCATÁN</h3><p>{{ config('registration.contact') ?: 'Datos de contacto por confirmar.' }}<br />Centro Universitario Montejo, Mérida, Yucatán.</p></div>
      <nav aria-label="Navegación del pie"><h3>NAVEGACIÓN</h3><a href="#inicio">Inicio</a><a href="#academia">Academia</a><a href="#metodologia">Metodología</a><a href="#programa">Programas</a><button data-dialog="inscripcion" type="button">Inscripciones</button></nav>
      <nav aria-label="Información legal"><h3>LEGAL</h3><a href="{{ route('privacy') }}">Aviso de privacidad</a><button data-dialog="terminos" type="button">Términos y condiciones</button><button data-dialog="cookies" type="button">Política de cookies</button></nav>
      <nav aria-label="Redes sociales"><h3>REDES SOCIALES</h3><button data-dialog="redes" type="button">Instagram</button><button data-dialog="redes" type="button">Facebook</button><button data-dialog="redes" type="button">YouTube</button></nav>
    </div><p class="copyright">© 2026 Iniesta Academy Mérida. Todos los derechos reservados.</p></div></footer>
    <dialog id="information-dialog" aria-labelledby="dialog-title" aria-describedby="dialog-body"><div class="dialog-content"><button class="dialog-close" type="button" aria-label="Cerrar ventana">×</button><p class="eyebrow">INIESTA ACADEMY MÉRIDA</p><h2 id="dialog-title"></h2><div id="dialog-body"></div><button class="button button-black dialog-done" type="button">Entendido</button></div></dialog>
    <noscript><p class="noscript-notice">Activa JavaScript para abrir el menú móvil y consultar los detalles. Puedes inscribirte desde el botón de inscripción.</p></noscript>
    <script src="{{ asset('script.js') }}?v={{ substr(md5_file(public_path('script.js')), 0, 12) }}" defer></script>
  </body>
</html>
