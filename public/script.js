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
      'La clínica se realizará del 20 al 24 de marzo de 2027. Consulta la inscripción en la sección de sede y precio.',
      'El precio vigente se muestra en la sección de sede y precio y el pago se realiza mediante Stripe. La sede es Centro Universitario Montejo, en Mérida. El horario está pendiente de confirmación.',
    ],
  },
  kit: {
    title: 'Todo listo para salir a la cancha',
    paragraphs: ['El kit de entrenamiento del diseño incluye 1 botella y 1 uniforme de fútbol: camiseta, pantalones y medias.', 'La disponibilidad, las tallas y las condiciones de entrega se confirmarán al abrir las inscripciones.'],
  },
  clinics: {
    title: 'Aprender el juego. Disfrutar el fútbol.',
    paragraphs: ['5 días de entrenamiento deportivo con el staff y entrenadores de Iniesta Academy Global. Una experiencia basada en los conceptos futbolísticos de Andrés Iniesta.', 'Los grupos se organizan por edad y nivel. La clínica será del 20 al 24 de marzo de 2027. El horario está pendiente de confirmación.'],
  },
  contacto: {
    title: 'Hablemos de fútbol',
    paragraphs: ['Los canales de atención de Mérida están pendientes de confirmación.', 'Publicaremos los datos oficiales de contacto cuando estén confirmados.'],
  },
  ubicacion: {
    title: 'Nos vemos en Mérida',
    paragraphs: ['La clínica se realizará en Centro Universitario Montejo, Mérida, Yucatán.', 'Consulta la ubicación en Google Maps desde la sección de sede.'],
  },
  privacidad: {
    title: 'Aviso de privacidad',
    paragraphs: ['El aviso de privacidad oficial está pendiente de aprobación y se publicará antes de recopilar datos personales.', 'El formulario solicita datos del participante y del tutor o comprador; el pago se realiza en Stripe.'],
  },
  terminos: {
    title: 'Términos y condiciones',
    paragraphs: ['La clínica se realizará del 20 al 24 de marzo de 2027. Consulta el precio vigente en la sección de sede y precio. El horario está por confirmar.'],
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
