const https = require('https');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

const PORT = 5174;
const HOST = '0.0.0.0';
const CERT_DIR = path.join(__dirname, '.cert');
const DIST_DIR = path.join(__dirname, 'dist');

const MIME_TYPES = {
  '.html': 'text/html',
  '.js': 'application/javascript',
  '.css': 'text/css',
  '.json': 'application/json',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
  '.webp': 'image/webp',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.ttf': 'font/ttf',
  '.webmanifest': 'application/manifest+json'
};

function serveBuild() {
  console.log('Building production version...');

  exec('npm run build', (error, stdout, stderr) => {
    if (error) {
      console.error('Build failed:', error.message);
      process.exit(1);
    }

    console.log(stdout);

    const certPath = path.join(CERT_DIR, 'cert.pem');
    const keyPath = path.join(CERT_DIR, 'key.pem');

    if (!fs.existsSync(certPath) || !fs.existsSync(keyPath)) {
      console.error('SSL certificates not found!');
      console.log('Run: ./setup-https.sh');
      process.exit(1);
    }

    const options = {
      key: fs.readFileSync(keyPath),
      cert: fs.readFileSync(certPath)
    };

    const server = https.createServer(options, (req, res) => {
      let filePath = path.join(DIST_DIR, req.url === '/' ? 'index.html' : req.url);

      if (req.url === '/sw.js' || req.url === '/manifest.webmanifest') {
        filePath = path.join(DIST_DIR, req.url.substring(1));
      }

      const ext = path.extname(filePath).toLowerCase();
      const contentType = MIME_TYPES[ext] || 'application/octet-stream';

      fs.readFile(filePath, (err, content) => {
        if (err) {
          if (err.code === 'ENOENT') {
            fs.readFile(path.join(DIST_DIR, 'index.html'), (err2, content2) => {
              if (err2) {
                res.writeHead(404);
                res.end('404 Not Found');
              } else {
                res.writeHead(200, { 'Content-Type': 'text/html' });
                res.end(content2, 'utf-8');
              }
            });
          } else {
            res.writeHead(500);
            res.end('Server Error: ' + err.code);
          }
        } else {
          const headers = {
            'Content-Type': contentType,
            'Cache-Control': req.url.includes('sw.js') ? 'no-cache' : 'public, max-age=0'
          };

          res.writeHead(200, headers);
          res.end(content, 'utf-8');
        }
      });
    });

    server.listen(PORT, HOST, () => {
      console.log('HTTPS Server running!');
      console.log('');
      console.log('  Local:   https://localhost:5174/');
      console.log('  Network: https://192.168.1.10:5174/');
      console.log('');
      console.log('Open on your iPhone: https://192.168.1.10:5174');
      console.log('');
    });
  });
}

serveBuild();
