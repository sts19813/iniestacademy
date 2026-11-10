import { cp, mkdir, rm } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = fileURLToPath(new URL('../', import.meta.url));
const output = path.join(root, 'dist');
await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
for (const file of ['index.html', 'style.css', 'script.js', 'assets', '_headers']) {
  await cp(path.join(root, file), path.join(output, file), { recursive: true });
}
console.log('Vista previa preparada en dist/. Sin documentos internos ni configuración privada.');
