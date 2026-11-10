<!doctype html>
<html lang="es-MX"><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" /><title>Confirmación de inscripción</title></head>
<body style="margin:0;padding:24px 12px;background:#f3f6f6;font-family:Arial,sans-serif;color:#101e24;">
  <div style="max-width:600px;margin:auto;background:#fff;border-radius:12px;overflow:hidden;">
    <div style="background:#101e24;padding:26px 32px;color:#fff;font-size:15px;font-weight:bold;letter-spacing:.5px;">INIESTA ACADEMY <span style="font-weight:normal;">MÉRIDA</span></div>
    <div style="padding:32px;line-height:1.6;">
      @if ($preview)<p style="padding:12px;background:#fff4d9;color:#604000;"><strong>Correo de prueba.</strong> Los datos son ficticios y no representan una inscripción ni un pago real.</p>@endif
      <p style="margin:0 0 8px;color:#0094b2;font-size:12px;font-weight:bold;letter-spacing:1.4px;">INSCRIPCIÓN CONFIRMADA</p>
      <h1 style="font-size:28px;line-height:1.2;margin:0 0 20px;">¡Nos vemos en la cancha!</h1>
      <p>Hola, {{ $registration->buyer_name }}:</p>
      <p>Hemos recibido el pago y confirmado la inscripción de <strong>{{ $registration->participant_name }}</strong> a Iniesta Academy Mérida.</p>
      <div style="background:#f3f8f9;border-radius:8px;padding:16px 20px;margin:24px 0;">
        <p style="margin:0 0 8px;"><strong>Fechas:</strong> 20 al 24 de marzo de 2027</p>
        <p style="margin:0 0 8px;"><strong>Sede:</strong> Centro Universitario Montejo, Mérida, Yucatán</p>
        <p style="margin:0 0 8px;"><strong>Pago:</strong> ${{ number_format($registration->amount / 100, 2) }} {{ strtoupper($registration->currency) }}</p>
        <p style="margin:0;overflow-wrap:anywhere;"><strong>Referencia:</strong> {{ $registration->id }}</p>
      </div>
      <p>El horario y las indicaciones de acceso se confirmarán más adelante. Conserva este correo como referencia de tu inscripción.</p>
      <p>¡Gracias por formar parte de la familia!</p>
      <p style="margin-top:28px;"><a href="{{ config('app.url') }}" style="display:inline-block;padding:12px 22px;border-radius:40px;background:#009ec0;color:#fff;text-decoration:none;font-weight:bold;">Visitar Iniesta Academy</a></p>
    </div>
    <div style="padding:20px 32px;background:#f7f9f9;color:#718086;font-size:12px;">Iniesta Academy Mérida · Este buzón es de envío automático.</div>
  </div>
</body></html>
