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
<body class="registration-page">
  <main class="registration-panel">
    <a href="{{ route('landing') }}">← Volver a la landing</a>
    <h1>Inscripción</h1>
    <p>Iniesta Academy Mérida · Verano 2027</p>
    <p>Fechas exactas por confirmar. @if (config('registration.promotion_amount')) Precio habitual: <del>${{ number_format(config('registration.regular_amount') / 100, 2) }}</del> MXN. @endif Pago total: <strong>${{ number_format((config('registration.promotion_amount') ?: config('registration.regular_amount')) / 100, 2) }} MXN por participante.</strong></p>
    <p>Completa los datos del participante y de su tutor o comprador. Después continuarás al pago seguro en Stripe.</p>
    <p>No hay devoluciones. @if (config('registration.contact')) Contacto: {{ config('registration.contact') }}. @else Los datos de contacto están por confirmar. @endif</p>
    @if ($errors->any())
      <div class="registration-errors" role="alert"><strong>Revisa los siguientes datos:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="post" action="{{ route('registration.store') }}" class="registration-form">
      @csrf
      <h2>Datos del tutor o comprador</h2>
      <label>Nombre completo<input name="buyer_name" value="{{ old('buyer_name') }}" maxlength="120" autocomplete="name" required /></label>
      <label>Correo electrónico<input type="email" name="buyer_email" value="{{ old('buyer_email') }}" maxlength="254" autocomplete="email" required /></label>
      <label>Teléfono<input type="tel" name="buyer_phone" value="{{ old('buyer_phone') }}" maxlength="30" autocomplete="tel" required /></label>
      <h2>Datos del participante</h2>
      <label>Nombre completo<input name="participant_name" value="{{ old('participant_name') }}" maxlength="120" required /></label>
      <label>Fecha de nacimiento<input type="date" name="participant_birth_date" value="{{ old('participant_birth_date') }}" required /></label>
      <button class="button button-black" type="submit">Continuar al pago · ${{ number_format((config('registration.promotion_amount') ?: config('registration.regular_amount')) / 100, 2) }} MXN <img class="stomtickets-logo" src="{{ asset('assets/stomtickets-logo.svg') }}" alt="StomTickets" width="78" height="30" /></button>
    </form>
  </main>
</body>
</html>
