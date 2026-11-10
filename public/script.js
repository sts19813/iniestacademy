'use strict';

// Public presentation only. No forms, credentials, payment state or browser persistence.
const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.main-nav');
const dialog = document.querySelector('#information-dialog');
const dialogTitle = document.querySelector('#dialog-title');
const dialogBody = document.querySelector('#dialog-body');
let dialogTrigger;

function closeMenu() {
  menuButton.setAttribute('aria-expanded', 'false');
  navigation.classList.remove('is-open');
}

menuButton.addEventListener('click', () => {
  const expanded = menuButton.getAttribute('aria-expanded') !== 'true';
  menuButton.setAttribute('aria-expanded', String(expanded));
  navigation.classList.toggle('is-open', expanded);
});
navigation.addEventListener('click', (event) => {
  if (event.target.closest('a')) closeMenu();
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
    closeMenu();
    menuButton.focus();
  }
});
document.addEventListener('click', (event) => {
  if (!event.target.closest('.header-inner')) closeMenu();
});
window.matchMedia('(min-width: 768px)').addEventListener('change', closeMenu);

const information = {
  inscripcion: {
    title: 'Tu próxima experiencia empieza aquí',
    paragraphs: [
      'Las inscripciones aún no están abiertas en esta vista previa.',
      'Próximamente podrás acceder con Google, registrar al participante y consultar las condiciones vigentes antes de pagar. Los pagos diferidos se harán mediante anticipo y abonos iniciados por ti.',
      'Fechas, edades, horarios y precios del diseño están pendientes de confirmación. Esta página no reserva plazas ni realiza cobros.',
    ],
  },
  kit: {
    title: 'Todo listo para salir a la cancha',
    paragraphs: ['El kit de entrenamiento del diseño incluye 1 botella y 1 uniforme de fútbol: camiseta, pantalones y medias.', 'La disponibilidad, las tallas y las condiciones de entrega se confirmarán al abrir las inscripciones.'],
  },
  clinics: {
    title: 'Aprender el juego. Disfrutar el fútbol.',
    paragraphs: ['5 días de entrenamiento deportivo con el staff y entrenadores de Iniesta Academy Global. Una experiencia basada en los conceptos futbolísticos de Andrés Iniesta.', 'Los grupos se organizan por edad y nivel. Las fechas y los horarios definitivos se publicarán antes de la inscripción.'],
  },
  contacto: {
    title: 'Hablemos de fútbol',
    paragraphs: ['Los canales de atención de Mérida están pendientes de confirmación.', 'El teléfono, correo y dirección mostrados en el diseño son ficticios. Publicaremos los datos oficiales antes de abrir las inscripciones.'],
  },
  ubicacion: {
    title: 'Nos vemos en Mérida',
    paragraphs: ['El mapa muestra la referencia visual del diseño. La dirección definitiva de la sede está pendiente de confirmación.', 'Publicaremos la ubicación oficial y las indicaciones para llegar antes de abrir las inscripciones.'],
  },
  privacidad: {
    title: 'Aviso de privacidad',
    paragraphs: ['El aviso de privacidad oficial está pendiente de aprobación y se publicará antes de recopilar datos personales.', 'Esta vista previa no dispone de formularios de registro ni recopila datos de participantes o datos de pago.'],
  },
  terminos: {
    title: 'Términos y condiciones',
    paragraphs: ['Esta es una vista previa de la propuesta. Los precios, fechas, edades y horarios mostrados en Figma presentan diferencias entre secciones y requieren confirmación.', 'Las condiciones de compra, reserva de cupo, abonos, cancelaciones y devoluciones se publicarán antes de habilitar las inscripciones. No se realizan compras desde esta página.'],
  },
  cookies: {
    title: 'Política de cookies',
    paragraphs: ['Esta vista previa no instala cookies desde la aplicación, no utiliza herramientas de analítica ni guarda información en el almacenamiento local del navegador.', 'Al incorporar el acceso con Google, se informará sobre las cookies de sesión necesarias antes de habilitar el servicio.'],
  },
  redes: {
    title: 'Sigamos en contacto',
    paragraphs: ['Los enlaces a los perfiles oficiales de Iniesta Academy Mérida se incorporarán cuando estén confirmados.'],
  },
};

document.querySelectorAll('[data-dialog]').forEach((button) => {
  button.addEventListener('click', () => {
    const content = information[button.dataset.dialog];
    if (!content) return;
    closeMenu();
    dialogTrigger = button;
    dialogTitle.textContent = content.title;
    dialogBody.replaceChildren(...content.paragraphs.map((text) => {
      const paragraph = document.createElement('p');
      paragraph.textContent = text;
      return paragraph;
    }));
    dialog.showModal();
    document.body.classList.add('dialog-open');
    dialog.querySelector('.dialog-close').focus();
  });
});
document.querySelectorAll('.dialog-close, .dialog-done').forEach((button) => {
  button.addEventListener('click', () => dialog.close());
});
dialog.addEventListener('click', (event) => {
  const bounds = dialog.getBoundingClientRect();
  if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
});
dialog.addEventListener('close', () => {
  document.body.classList.remove('dialog-open');
  dialogTrigger?.focus();
});
