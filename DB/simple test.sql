INSERT INTO users (id, name, email, pwd_hash) VALUES (
	'8ee4ba6f-6fa0-42c3-9320-2aa34de43884',
	'John Doe',
	'john@doe.com',
	'123456'
);

INSERT INTO accounts (id, user_id, balance, name) VALUES (
	'ccdf7d1a-b1f5-42af-92eb-0555602cefec',
	'8ee4ba6f-6fa0-42c3-9320-2aa34de43884',
	1000,
	'account 1'
);

INSERT INTO accounts (id, user_id, balance, name) VALUES (
	'f1d2b3c4-5e6f-7a8b-9c0d-1e2f3a4b5c6d',
	'8ee4ba6f-6fa0-42c3-9320-2aa34de43884',
	1000,
	'account 2'
);

INSERT INTO transactions (id, account_from, account_to, amount, description, timestamp) VALUES (
  '55c86b88-a739-48a1-b953-eabc62222654',
  'ccdf7d1a-b1f5-42af-92eb-0555602cefec',
  'f1d2b3c4-5e6f-7a8b-9c0d-1e2f3a4b5c6d',
  500,
  'Test transaction',
  NOW()
);
