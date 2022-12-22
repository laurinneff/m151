DROP TRIGGER IF EXISTS update_balance;

DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  pwd_hash VARCHAR(255) NOT NULL
);

CREATE TABLE accounts (
  id CHAR(36) NOT NULL PRIMARY KEY,
  user_id CHAR(36) NOT NULL REFERENCES users(id),
  balance DECIMAL(10, 2) UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL
);

CREATE TABLE transactions (
  id CHAR(36) NOT NULL PRIMARY KEY,
  account_from CHAR(36) NOT NULL REFERENCES accounts(id),
  account_to CHAR(36) NOT NULL REFERENCES accounts(id),
  amount DECIMAL(10, 2) NOT NULL,
  description VARCHAR(255) NOT NULL,
  timestamp DATETIME NOT NULL
);

-- Trigger to update the balance of the account when a transaction is created
CREATE TRIGGER update_balance
  AFTER INSERT ON transactions FOR EACH ROW
BEGIN

  START TRANSACTION;

  UPDATE accounts
  SET balance = balance + NEW.amount
  WHERE id = NEW.account_to;

  UPDATE accounts
  SET balance = balance - NEW.amount
  WHERE id = NEW.account_from;

  COMMIT;

END;
