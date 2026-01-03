-- Create Admin User for CRM
-- Username: admin
-- Password: admin123
-- 
-- IMPORTANT: Change password after first login!

-- Check if admin user already exists, if not create it
INSERT INTO `users` (
    `username`, 
    `full_name`, 
    `phone`, 
    `password`, 
    `role`, 
    `balance`, 
    `sms_enabled`, 
    `created_at`, 
    `updated_at`
) 
SELECT 
    'admin', 
    'Administrator', 
    '', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin', 
    0.00, 
    1, 
    NOW(), 
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `username` = 'admin'
);

-- Verify admin user was created
SELECT 
    id, 
    username, 
    full_name, 
    role, 
    created_at 
FROM `users` 
WHERE `username` = 'admin';

