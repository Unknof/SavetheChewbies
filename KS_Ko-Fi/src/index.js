import { createApp } from './server.js';

const app = createApp();

const port = Number.parseInt(process.env.PORT ?? '8787', 10);
app.listen(port, () => {
  // eslint-disable-next-line no-console
  console.log(`KS Ko-fi bridge listening on http://localhost:${port}`);
});
