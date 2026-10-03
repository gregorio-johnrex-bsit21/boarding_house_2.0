const fs = require('fs');
const html = fs.readFileSync('boarding_house_map.html', 'utf8');
const m = html.match(/const GLB\s*=\s*"([^"]+)"/);
if (!m) {
  console.error('GLB string not found');
  process.exit(1);
}
const buf = Buffer.from(m[1], 'base64');
fs.writeFileSync('sally_bh.glb', buf);
console.log('Wrote ' + buf.length + ' bytes to sally_bh.glb');