set NODE_ENV=production && encore production --verbose

rd /s /q public
ping 127.0.0.1 -n 3 > nul
npm run build

