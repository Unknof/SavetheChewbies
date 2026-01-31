import test from 'node:test';
import assert from 'node:assert/strict';
import request from 'supertest';

import { createApp } from '../src/server.js';

test('accepts valid Ko-fi urlencoded webhook', async () => {
  const sent = [];
  const app = createApp({
    kofiVerificationToken: 'token123',
    sendDiscord: async (content) => sent.push(content),
  });

  const payload = {
    verification_token: 'token123',
    type: 'Donation',
    from_name: 'Tester',
    message: '[TEAM] hello',
    amount: '5',
    currency: 'USD',
    message_id: 'm1',
    kofi_transaction_id: 't1',
    timestamp: new Date().toISOString(),
  };

  const resp = await request(app)
    .post('/webhooks/kofi')
    .type('form')
    .send({ data: JSON.stringify(payload) });

  assert.equal(resp.status, 200);
  assert.equal(resp.body.ok, true);
  assert.equal(sent.length, 1);
  assert.match(sent[0], /TEAM FUND/);
});

test('rejects invalid verification token', async () => {
  const app = createApp({ kofiVerificationToken: 'token123', sendDiscord: async () => {} });

  const payload = {
    verification_token: 'wrong',
    type: 'Donation',
  };

  const resp = await request(app)
    .post('/webhooks/kofi')
    .type('form')
    .send({ data: JSON.stringify(payload) });

  assert.equal(resp.status, 401);
});
