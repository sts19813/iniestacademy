'use strict';

const token = window.location.hash.slice(1);
if (/^[a-f0-9]{64}$/.test(token)) {
  history.replaceState(null, '', window.location.pathname);
  document.querySelector('#setup-token').value = token;
  document.querySelector('#setup-link-form').requestSubmit();
}
