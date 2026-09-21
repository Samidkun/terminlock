import { defineConfig, devices } from '@playwright/test';

const PORT = 8125;
const EXTERNAL_URL = process.env.REHEARSAL_BASE_URL || '';

export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list']],
    use: {
        baseURL: EXTERNAL_URL || `http://127.0.0.1:${PORT}`,
        trace: 'retain-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
    webServer: EXTERNAL_URL ? undefined : {
        command: `php artisan serve --port=${PORT}`,
        url: `http://127.0.0.1:${PORT}/`,
        reuseExistingServer: true,
        timeout: 60_000,
        env: {
            DB_CONNECTION: 'pgsql',
            DB_HOST: '127.0.0.1',
            DB_PORT: '5434',
            DB_DATABASE: 'terminlock_test',
            DB_USERNAME: 'terminlock',
            DB_PASSWORD: 'terminpass',
        },
    },
});
