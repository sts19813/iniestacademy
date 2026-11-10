<!doctype html>
<html lang="es-MX"><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" /><meta name="robots" content="noindex, nofollow" /><title>Inscripción · Iniesta Academy Mérida</title><link rel="stylesheet" href="{{ asset('assets/fonts.css') }}" /><link rel="stylesheet" href="{{ asset('style.css') }}?v={{ substr(md5_file(public_path('style.css')), 0, 12) }}" /></head>
<body class="registration-page"><main class="registration-panel">
@if ($cancelled)
<h1>Pago cancelado</h1><p>No se completó el pago. Puedes volver a intentar tu inscripción.</p><a class="button button-black" href="{{ route('registration.create') }}">Intentar de nuevo</a>
@else
<h1>Gracias por tu registro</h1><p>Si completaste el pago, Stripe procesará su confirmación. Conserva el comprobante de pago de Stripe.</p><a class="button button-black" href="{{ route('landing') }}">Volver al inicio</a>
@endif
</main></body></html>
