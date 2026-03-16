import http from 'http';
import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';

const PORT = 3000;

async function runLighthouse(url) {
  const chrome = await chromeLauncher.launch({
    chromePath: "/usr/bin/chromium-browser",
    chromeFlags: [
      "--headless",
      "--no-sandbox",
      "--disable-gpu",
      "--disable-dev-shm-usage",
      "--disable-software-rasterizer",
      "--disable-extensions",
      "--disable-setuid-sandbox",
      "--no-first-run",
      "--disable-background-networking",
      "--disable-default-apps",
      "--disable-sync",
      "--metrics-recording-only",
      "--mute-audio",
      "--no-default-browser-check",
      "--disable-features=TranslateUI",
      "--disable-ipc-flooding-protection",
      "--disable-renderer-backgrounding",
      "--disable-backgrounding-occluded-windows",
      "--disable-client-side-phishing-detection",
      "--disable-component-update",
      "--disable-default-apps",
      "--disable-domain-reliability",
      "--disable-features=AudioServiceOutOfProcess",
      "--disable-hang-monitor",
      "--disable-popup-blocking",
      "--disable-prompt-on-repost",
      "--disable-sync",
      "--enable-automation",
      "--enable-features=NetworkService,NetworkServiceInProcess",
      "--force-color-profile=srgb",
      "--metrics-recording-only",
      "--no-default-browser-check",
      "--no-first-run",
      "--password-store=basic",
      "--use-mock-keychain",
      "--disable-blink-features=AutomationControlled",
    ],
  });

  const options = {
    logLevel: "error",
    output: "json",
    onlyCategories: ["performance", "accessibility", "best-practices", "seo"],
    port: chrome.port,
    
    // Уменьшенные таймауты для быстрой загрузки
    maxWaitForLoad: 30000,  // 30 сек вместо 45
    maxWaitForFcp: 15000,   // 15 сек вместо 30
    
    // Desktop конфигурация (вместо Mobile)
    formFactor: 'desktop',
    screenEmulation: {
      mobile: false,
      width: 1350,
      height: 940,
      deviceScaleFactor: 1,
      disabled: false,
    },
    
    // ПОЛНОСТЬЮ отключаем throttling (эмуляцию медленной сети/CPU)
    throttlingMethod: 'provided',
    throttling: {
      rttMs: 0,            // Нет задержки сети
      throughputKbps: 0,   // Безлимитная пропускная способность
      requestLatencyMs: 0, // Нет задержки запросов
      downloadThroughputKbps: 0,
      uploadThroughputKbps: 0,
      cpuSlowdownMultiplier: 1, // НЕ замедляем CPU (1x = реальная скорость)
    },
    
    // Используем реальную сеть и CPU сервера
    emulatedUserAgent: false,
    
    // Пропускаем некоторые долгие проверки
    skipAudits: ['screenshot-thumbnails', 'final-screenshot'],
  };

  try {
    const runnerResult = await lighthouse(url, options);
    return runnerResult.lhr;
  } finally {
    await chrome.kill();
  }
}

const server = http.createServer(async (req, res) => {
  // Health check endpoint - ПЕРВЫМ!
  if (req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'ok', timestamp: new Date().toISOString() }));
    return;
  }
  
  req.setTimeout(180000); // 3 минуты
  res.setTimeout(180000);
  
  // Разрешаем CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  res.setHeader('Content-Type', 'application/json');

  // Обработка OPTIONS запроса
  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  // Только POST запросы для анализа
  if (req.method !== 'POST') {
    res.writeHead(405);
    res.end(JSON.stringify({ error: 'Method not allowed. Use POST for analysis.' }));
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

