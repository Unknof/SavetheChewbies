export async function postDiscordMessage({ webhookUrl, content }) {
  const resp = await fetch(webhookUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      content,
      allowed_mentions: { parse: [] },
    }),
  });

  if (!resp.ok) {
    const text = await resp.text().catch(() => '');
    throw new Error(`Discord webhook failed: ${resp.status} ${resp.statusText} ${text}`);
  }
}
