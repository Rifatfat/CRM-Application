-- ======================
-- CLIENTS
-- ======================
INSERT INTO clients (id, company_name, industry, address, notes, created_at, updated_at) VALUES
(1, 'PT Maju Jaya Abadi', 'Retail', 'Jakarta', 'Client potensial besar', NOW(), NOW()),
(2, 'CV Digital Nusantara', 'Technology', 'Bandung', 'Startup berkembang', NOW(), NOW());

-- ======================
-- SERVICES
-- ======================
INSERT INTO services (id, name, description, base_price, created_at, updated_at) VALUES
(1, 'Website Development', 'Pembuatan website company profile', 10000000, NOW(), NOW()),
(2, 'Mobile App Development', 'Pembuatan aplikasi mobile', 20000000, NOW(), NOW());

-- ======================
-- CONTRACTS
-- ======================
INSERT INTO contracts (id, client_id, service_id, contract_value, start_date, end_date, status, created_at, updated_at) VALUES
(1, 1, 1, 12000000, '2026-01-01', '2026-03-01', 'ongoing', NOW(), NOW()),
(2, 2, 2, 25000000, '2026-02-01', '2026-05-01', 'done', NOW(), NOW());

-- ======================
-- CONTACTS
-- ======================
INSERT INTO contacts (id, client_id, name, position, email, phone) VALUES
(1, 1, 'Budi Santoso', 'Manager', 'budi@majujaya.com', '081234567890'),
(2, 2, 'Siti Rahma', 'CEO', 'siti@digitalnusantara.com', '082345678901');

-- ======================
-- PAYMENTS
-- ======================
INSERT INTO payments (id, contract_id, amount, payment_date, payment_method, status, created_at) VALUES
(1, 1, 6000000, '2026-02-01', 'transfer', 'paid', NOW()),
(2, 1, 6000000, '2026-03-01', 'transfer', 'paid', NOW());

-- ======================
-- DOCUMENTS
-- ======================
INSERT INTO documents (id, client_id, contract_id, uploaded_by, file_name, file_path, document_type, uploaded_at) VALUES
(1, 1, 1, 1, 'contract_pt_majujaya.pdf', '/docs/contract1.pdf', 'contract', NOW());

-- ======================
-- COMMUNICATION LOGS
-- ======================
INSERT INTO communication_logs (id, client_id, user_id, communication_type, notes, communication_date, created_at) VALUES
(1, 1, 1, 'meeting', 'Meeting awal dengan client', '2026-01-05', NOW());

-- ======================
-- USERS
-- ======================
INSERT INTO users (id, name, email, password, role_id, created_at, updated_at) VALUES
(1, 'Admin CRM', 'admin@crm.com', 'password123', 1, NOW(), NOW());