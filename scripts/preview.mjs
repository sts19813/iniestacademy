import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { securityHeaders } from './security-headers.mjs';
import './build.mjs';

const publicRoot = fileURLToPath(new URL('../dist/', import.meta.url));
const port = Number(process.env.PORT || 4173);
const mime = { '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8', '.js': 'text/javascript; charset=utf-8', '.svg': 'image/svg+xml', '.png': 'image/png', '.ttf': 'font/ttf' };

const server = createServer(async (request, response) => {
  for (const [name, value] of Object.entries(securityHeaders)) response.setHeader(name, value);
  if (!['GET', 'HEAD'].includes(request.method)) {
    response.writeHead(405, { Allow: 'GET, HEAD' }).end();
    return;
  }
  let pathname;
  try { pathname = decodeURIComponent(new URL(request.url, 'http://localhost').pathname); }
  catch { response.writeHead(400).end(); return; }
  const permitted = ['/', '/index.html', '/style.css', '/script.js'].includes(pathname)
    || /^\/assets\/[a-zA-Z0-9_-]+\.(css|png|svg|ttf)$/.test(pathname);
  if (!permitted) { response.writeHead(404).end(); return; }
  const file = path.join(publicRoot, pathname === '/' ? 'index.html' : pathname);
  try {
    const info = await stat(file);
    if (!info.isFile()) { response.writeHead(404).end(); return; }
    const content = await readFile(file);
    response.writeHead(200, { 'Content-Type': mime[path.extname(file)], 'Content-Length': content.byteLength });
    response.end(request.method === 'HEAD' ? undefined : content);
  } catch { response.writeHead(404).end(); }
});
server.listen(port, '127.0.0.1', () => console.log(`Local: http://127.0.0.1:${port}`));
for (const signal of ['SIGINT', 'SIGTERM']) process.on(signal, () => server.close(() => process.exit(0)));
