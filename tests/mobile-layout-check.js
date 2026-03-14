import { chromium } from 'playwright';

const baseUrl = process.env.MOBILE_TEST_BASE_URL || 'http://127.0.0.1:8000';
const routes = ['/', '/login', '/dashboard', '/posts'];

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 375, height: 812 } });

const results = [];

for (const route of routes) {
    const url = new URL(route, baseUrl).toString();

    try {
        const response = await page.goto(url, { waitUntil: 'networkidle' });
        const status = response?.status() ?? 0;
        const finalUrl = page.url();

        const overflow = await page.evaluate(() => {
            const bodyOverflow = document.body.scrollWidth > window.innerWidth;
            const offenders = Array.from(document.querySelectorAll('*'))
                .map((element) => {
                    const rect = element.getBoundingClientRect();

                    return {
                        tag: element.tagName.toLowerCase(),
                        id: element.id || null,
                        className: typeof element.className === 'string' ? element.className : null,
                        width: Math.round(rect.width),
                        right: Math.round(rect.right),
                    };
                })
                .filter((element) => element.width > window.innerWidth || element.right > window.innerWidth + 1)
                .slice(0, 10);

            return {
                bodyOverflow,
                scrollWidth: document.body.scrollWidth,
                viewportWidth: window.innerWidth,
                offenders,
            };
        });

        results.push({
            route,
            status,
            finalUrl,
            overflow: overflow.bodyOverflow,
            scrollWidth: overflow.scrollWidth,
            viewportWidth: overflow.viewportWidth,
            offenders: overflow.offenders,
        });
    } catch (error) {
        results.push({
            route,
            error: error.message,
        });
    }
}

await browser.close();

for (const result of results) {
    console.log(JSON.stringify(result));
}
