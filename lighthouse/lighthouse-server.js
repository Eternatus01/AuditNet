import http from 'http';
import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';

const PORT = 3000;

async function runLighthouse(url) {
  const chrome = await chromeLauncher.launch({
    chromePath: "/usr/bin/chromium-browser",
    chromeFlags: ["--headless", "--no-sandbox", "--disable-gpu", "--disable-dev-shm-usage"],
  });

  const options = {
    logLevel: "error",
    output: "json",
    onlyCategories: ["performance", "accessibility", "best-practices", "seo"],
    port: chrome.port,
  };

  const runnerResult = await lighthouse(url, options);
  await chrome.kill();

  return runnerResult.lhr;
}

const server = http.createServer(async (req, res) => {
  // Разрешаем CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  res.setHeader('Content-Type', 'application/json');

  // Обработка OPTIONS запроса
  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  // Только POST запросы
  if (req.method !== 'POST') {
    res.writeHead(405);
    res.end(JSON.stringify({ error: 'Method not allowed. Use POST.' }));
    return;
  }

  let body = '';
  req.on('data', chunk => {
    body += chunk.toString();
  });

  req.on('end', async () => {
    try {
      const data = JSON.parse(body);
      const url = data.url;

      if (!url) {
        res.writeHead(400);
        res.end(JSON.stringify({ error: 'URL required in request body' }));
        return;
      }

      const result = await runLighthouse(url);
      
      res.writeHead(200);
      res.end(JSON.stringify(result));
    } catch (error) {
      console.error('Lighthouse error:', error.message);
      res.writeHead(500);
      res.end(JSON.stringify({ error: error.message }));
    }
  });
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`Lighthouse server listening on port ${PORT}`);
});

