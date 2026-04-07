-- ======================
-- CLIENT (DFD: INPUT DATA CLIENT)
-- ======================
INSERT INTO clients (company_name, industry, address, notes)
VALUES ('PT Sukses Selalu', 'Finance', 'Surabaya', 'Client baru');

-- ======================
-- CONTRACT (DFD: SIMPAN CONTRACT)
-- ======================
INSERT INTO contracts (client_id, service_id, contract_value, start_date, end_date, status)
VALUES (1, 1, 15000000, '2026-04-01', '2026-06-01', 'ongoing');

-- ======================
-- PAYMENT (DFD: INPUT PAYMENT)
-- ======================
INSERT INTO payments (contract_id, amount, payment_date, payment_method, status)
VALUES (1, 7500000, '2026-04-10', 'transfer', 'pending');

-- ======================
-- UPDATE CONTRACT STATUS (DFD: UPDATE CONTRACT)
-- ======================
UPDATE contracts
SET status = 'done'
WHERE id = 1;

-- ======================
-- UPDATE PAYMENT STATUS
-- ======================
UPDATE payments
SET status = 'paid'
WHERE id = 1;

-- ======================
-- OUTPUT CLIENT
-- ======================
SELECT * FROM clients;

-- ======================
-- OUTPUT CONTRACT BY CLIENT
-- ======================
SELECT * FROM contracts WHERE client_id = 1;

-- ======================
-- OUTPUT PAYMENT BY CONTRACT
-- ======================
SELECT * FROM payments WHERE contract_id = 1;

-- ======================
-- OUTPUT DOCUMENT
-- ======================
SELECT * FROM documents WHERE contract_id = 1;

-- ======================
-- OUTPUT COMMUNICATION LOG
-- ======================
SELECT * FROM communication_logs WHERE client_id = 1;