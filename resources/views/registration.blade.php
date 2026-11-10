<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Inscripción · Iniesta Academy Mérida</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
  <link rel="stylesheet" href="{{ asset('assets/fonts.css') }}" />
  <link rel="stylesheet" href="{{ asset('style.css') }}?v={{ substr(md5_file(public_path('style.css')), 0, 12) }}" />
</head>
<body class="registration-layout">
  <a class="skip-link" href="#datos">Saltar al formulario</a>
  <header class="registration-header">
    <div class="container registration-header-inner">
      <a class="brand" href="{{ route('landing') }}" aria-label="Iniesta Academy Mérida, inicio"><img src="{{ asset('assets/imgLogoOficialSobreFondoOscuro1.svg') }}" alt="Iniesta Academy" width="160" height="46" /></a>
      <nav class="registration-nav" aria-label="Navegación principal"><a href="{{ route('landing') }}#experiencia">La experiencia</a><a href="{{ route('landing') }}#programa">Programa</a><a href="{{ route('landing') }}#sede">Sede y precio</a></nav>
      <a class="button button-paper registration-header-cta" href="#datos">Solicitar plaza <img src="{{ asset('assets/imgIaIconArrowUpRight.svg') }}" alt="" width="24" height="24" /></a>
    </div>
  </header>
  <main>
    <section class="registration-hero" aria-labelledby="registration-hero-title">
      <div class="registration-hero-copy"><div><p>INIESTA ACADEMY / MÉRIDA</p><h1 id="registration-hero-title">TU PRÓXIMO<br />PASE EMPIEZA AQUÍ.</h1></div></div>
      <div class="registration-hero-image"><img src="{{ asset('assets/registro-banner.png') }}" alt="Entrenador saludando a jóvenes jugadores en la cancha" width="880" height="328" /></div>
    </section>
    <div class="ticker ticker-black" aria-label="Únete a la familia · Join the family"><div class="ticker-track" aria-hidden="true">@for ($i = 0; $i < 2; $i++)<div class="ticker-group">@for ($j = 0; $j < 4; $j++)<strong>ÚNETE A LA FAMILIA</strong><span>JOIN THE FAMILY</span>@endfor</div>@endfor</div></div>
    <section class="registration-content" id="datos">
      <div class="container registration-grid">
        <aside class="registration-intro">
          <p class="eyebrow">INSCRIPCIÓN / 01 DATOS → 02 PAGO</p>
          <h2>DA EL<br />PRIMER<br />PASE.</h2>
          <p class="registration-intro-description">Completa los datos del tutor y del jugador. Después continuarás al pago con Stripe.</p>
          <ol class="registration-steps"><li><span>01</span> Completa tus datos</li><li><span>02</span> Continúa al pago con Stripe</li><li><span>03</span> Conserva tu comprobante de pago</li></ol>
          @if (config('registration.contact'))<p class="registration-contact">¿Prefieres escribirnos?<br />{{ config('registration.contact') }}</p>@endif
        </aside>
        <div class="registration-main">
          <h2>Inscripción a la clínica</h2>
          <p class="registration-form-note">Paso 1 de 2 · Datos de inscripción<br />Los campos con * son obligatorios.</p>
          @if ($errors->any())<div class="registration-errors" role="alert"><strong>Revisa los siguientes datos:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
          <form method="post" action="{{ route('registration.store') }}" class="registration-details-form">
            @csrf
            <input type="hidden" name="price_quote" value="{{ config('registration.promotion_amount') ?: config('registration.regular_amount') }}" />
            <fieldset><legend>DATOS DEL PADRE, MADRE O TUTOR</legend><div class="registration-fields">
              <label>Nombre y apellidos del tutor *<input name="buyer_name" value="{{ old('buyer_name') }}" placeholder="Ej. Ana García López" maxlength="120" autocomplete="name" required /></label>
              <label>Teléfono de contacto *<input type="tel" name="buyer_phone" value="{{ old('buyer_phone') }}" placeholder="Ej. +52 999 000 0000" maxlength="30" autocomplete="tel" required /></label>
              <label>Correo electrónico *<input type="email" name="buyer_email" value="{{ old('buyer_email') }}" placeholder="Ej. hola@ejemplo.com" maxlength="254" autocomplete="email" required /></label>
              <label>Ciudad de residencia *<input name="buyer_city" value="{{ old('buyer_city') }}" placeholder="Ej. Mérida" maxlength="120" autocomplete="address-level2" required /></label>
            </div></fieldset>
            <fieldset><legend>DATOS DEL JUGADOR O JUGADORA</legend><div class="registration-fields">
              <label>Nombre y apellidos del jugador *<input name="participant_name" value="{{ old('participant_name') }}" placeholder="Ej. Alex García López" maxlength="120" required /></label>
              <label>Posición en el campo<select name="participant_position"><option value="">Selecciona una opción</option>@foreach (['Portero','Defensa','Centrocampista','Delantero','Otra'] as $position)<option value="{{ $position }}" @selected(old('participant_position') === $position)>{{ $position }}</option>@endforeach</select></label>
              <label>Años practicando fútbol *<input name="football_years" type="number" min="0" max="100" value="{{ old('football_years') }}" placeholder="Ej. 5" required /></label>
              <label>Fecha de nacimiento *<input type="date" name="participant_birth_date" value="{{ old('participant_birth_date') }}" required /></label>
              <label>Lugar de nacimiento *<input name="birthplace" value="{{ old('birthplace') }}" placeholder="Ej. Mérida" maxlength="120" required /></label>
              <label>Talla camiseta y short *<select name="uniform_size" required><option value="">Selecciona una talla</option>@foreach (['CH','M','G','EG'] as $size)<option value="{{ $size }}" @selected(old('uniform_size') === $size)>{{ $size }}</option>@endforeach</select></label>
              <label>Talla calcetines *<select name="socks_size" required><option value="">Selecciona una talla</option>@foreach (['CH','M','G','EG'] as $size)<option value="{{ $size }}" @selected(old('socks_size') === $size)>{{ $size }}</option>@endforeach</select></label>
              <label><span>Nombre de camiseta <span class="field-optional">(opcional)</span></span><input name="shirt_name" value="{{ old('shirt_name') }}" maxlength="40" placeholder="Nombre para la camiseta" /></label>
            </div></fieldset>
            <label class="registration-consent"><input type="checkbox" name="privacy_consent" value="1" @checked(old('privacy_consent')) required /><span>He leído el aviso de privacidad y autorizo que me contacten sobre esta solicitud. *</span></label>
            <a class="registration-privacy-link" href="{{ route('privacy') }}">Consultar aviso de privacidad</a>
<p class="registration-price">20 al 24 de marzo de 2027<br />@if (config('registration.promotion_amount'))<del>${{ number_format(config('registration.regular_amount') / 100, 2) }}</del> MXN · @endif Total por participante: <strong>${{ number_format((config('registration.promotion_amount') ?: config('registration.regular_amount')) / 100, 2) }} MXN</strong>.</p>
            <button class="button button-turquoise registration-submit" type="submit"><span>Continuar y pagar</span><img src="{{ asset('assets/imgIaIconArrowUpRight1.svg') }}" alt="" width="24" height="24" /></button>
          </form>
        </div>
      </div>
    </section>
  </main>
  <footer class="site-footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><a href="{{ route('landing') }}" aria-label="Iniesta Academy, inicio"><img src="{{ asset('assets/imgLogoOficialSobreFondoOscuro.svg') }}" alt="Iniesta Academy" width="208" height="60" loading="lazy" /></a><h3>MÉRIDA, YUCATÁN</h3><p>{{ config('registration.contact') ?: 'Datos de contacto por confirmar.' }}<br />Ubicación por confirmar.</p></div>
    <nav aria-label="Navegación del pie"><h3>NAVEGACIÓN</h3><a href="{{ route('landing') }}#inicio">Inicio</a><a href="{{ route('landing') }}#academia">Academia</a><a href="{{ route('landing') }}#metodologia">Metodología</a><a href="{{ route('landing') }}#programa">Programas</a><a href="#datos">Inscripciones</a></nav>
    <nav aria-label="Información legal"><h3>LEGAL</h3><a href="{{ route('privacy') }}">Aviso de privacidad</a></nav>
    <div><h3>REDES SOCIALES</h3><p>Perfiles oficiales<br />por confirmar.</p></div>
  </div><p class="copyright">© {{ date('Y') }} Iniesta Academy Mérida. Todos los derechos reservados.</p></div></footer>
</body>
</html>
