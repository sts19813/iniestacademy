@if ($preview)
CORREO DE PRUEBA: los datos son ficticios y no representan una inscripción ni un pago real.

@endif
INSCRIPCIÓN CONFIRMADA · INIESTA ACADEMY MÉRIDA

Hola, {{ $registration->buyer_name }}:

Hemos recibido el pago y confirmado la inscripción de {{ $registration->participant_name }} a Iniesta Academy Mérida.

Fechas: 20 al 24 de marzo de 2027
Sede: Centro Universitario Montejo, Mérida, Yucatán
Ubicación: https://maps.app.goo.gl/fKa6pLhiDpGuDqJ48
Pago: ${{ number_format($registration->amount / 100, 2) }} {{ strtoupper($registration->currency) }}
Referencia: {{ $registration->id }}

El horario se confirmará más adelante. Conserva este correo como referencia de tu inscripción.

¡Gracias por formar parte de la familia!
{{ config('app.url') }}
