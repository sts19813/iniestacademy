import assert from 'node:assert/strict';
import { readFile, stat } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = fileURLToPath(new URL('../', import.meta.url));
const html = await readFile(path.join(root, 'index.html'), 'utf8');
const css = await readFile(path.join(root, 'style.css'), 'utf8');
const js = await readFile(path.join(root, 'script.js'), 'utf8');
assert.equal((html.match(/<h1\b/g) || []).length, 1, 'Un solo encabezado principal');
assert.match(html, /<html lang="es-MX">/);
const ids = [...html.matchAll(/\bid="([^"]+)"/g)].map(match => match[1]);
assert.equal(new Set(ids).size, ids.length, 'Los IDs deben ser únicos');
for (const [, id] of html.matchAll(/href="#([^"]+)"/g)) assert.ok(ids.includes(id), `Destino de navegación: ${id}`);
const references = new Set([...html.matchAll(/(?:src|href)="(?!#)([^\"]+)"/g)].map(match => match[1]));
for (const reference of references) {
  assert.ok(!reference.includes('://'), `Recurso de ejecución local: ${reference}`);
  assert.ok((await stat(path.join(root, reference))).size > 0, `Recurso vacío: ${reference}`);
}
assert.doesNotMatch(js, /localStorage|sessionStorage|indexedDB|serviceWorker|document\.cookie|fetch\s*\(|XMLHttpRequest/);
assert.doesNotMatch(html, /<form\b|<input\b|<iframe\b|<script\b[^>]*>[^<]+<\/script>/);
assert.doesNotMatch(html + css + js, /https:\/\/www\.figma\.com\/api\/mcp\/asset/);
console.log(`Verificado: ${references.size} recursos locales, navegación y ausencia de captura/persistencia de datos privados.`);
