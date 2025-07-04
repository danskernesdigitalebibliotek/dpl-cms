const fs = require('fs');
const file = process.argv[2];
let content = fs.readFileSync(file, 'utf8');
content = content.replace(/"""\s*[\s\S]*?\s*"""\n?/g, '');
fs.writeFileSync(file, content);
