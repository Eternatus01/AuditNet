import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';

async function runLighthouse(url) {
  const chrome = await chromeLauncher.launch({
    chromePath: "/usr/bin/chromium-browser",
    chromeFlags: ["--headless", "--no-sandbox", "--disable-gpu"],
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

const url = process.argv[2];
if (!url) {
  console.error(JSON.stringify({ error: "URL required" }));
  process.exit(1);
}

runLighthouse(url)
  .then((result) => {
    console.log(JSON.stringify(result));
  })
  .catch((error) => {
    console.error(JSON.stringify({ error: error.message }));
    process.exit(1);
  });
