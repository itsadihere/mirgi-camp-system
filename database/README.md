# Database provisioning

This folder holds the reproducible database definition for `mcms_db`.

## Files
- **schema.sql** — full table structure (no data). Recreates every table.
- **seed_reference.sql** — lookup/reference data only (diseases). No patient PII.

## Provision a fresh environment
```bash
# 1. Create the database
mysql -u root -p -e "CREATE DATABASE mcms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# 2. Load structure
mysql -u root -p mcms_db < database/schema.sql

# 3. Load reference data
mysql -u root -p mcms_db < database/seed_reference.sql
```

## First admin user
No user accounts are shipped in the seed (passwords must never be committed).
Create the first superadmin manually after loading the schema, e.g. from PHP:

```php
// scratch script, run once, then delete
$hash = password_hash('CHOOSE_A_STRONG_PASSWORD', PASSWORD_DEFAULT);
// INSERT INTO users (username, full_name, role, password, status)
// VALUES ('admin', 'Administrator', 'superadmin', '<hash>', 1);
```

## Keeping schema.sql current
After any schema change, regenerate:
```bash
mysqldump -u <user> -p --no-data --skip-comments mcms_db > database/schema.sql
```

> Runtime-managed tables (`whatsapp_*`, `audit_log`) are also created automatically
> by the application on first use, but they are included here so the schema is
> complete and reviewable.
