# Ambiguous Column Error Fix - Deployment Guide

## Problem
SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'deleted_at' in where clause is ambiguous

## Root Cause
The `deleted_at` column exists in both `units` and `drivers` tables. When joining tables, MySQL needs to know which table's `deleted_at` column to reference.

## Solution Applied
Fixed in `app/Http/Controllers/AnalyticsController.php` line 121:

**Before:**
```php
->whereNull('deleted_at')
```

**After:**
```php
->whereNull('units.deleted_at')
```

## Complete Query Fixed
```sql
-- Before (ambiguous)
SELECT units.*, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name, 0 as total_collected, 0 as days_operated 
FROM units 
LEFT JOIN drivers as d ON units.driver_id = d.id 
WHERE deleted_at IS NULL 
ORDER BY units.plate_number ASC 
LIMIT 5

-- After (fixed)
SELECT units.*, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name, 0 as total_collected, 0 as days_operated 
FROM units 
LEFT JOIN drivers as d ON units.driver_id = d.id 
WHERE units.deleted_at IS NULL 
ORDER BY units.plate_number ASC 
LIMIT 5
```

## Files to Update on Hostinger
1. `app/Http/Controllers/AnalyticsController.php`

## Quick Deployment Steps

### Option 1: File Manager (Recommended)
1. Login to Hostinger hPanel
2. Go to File Manager
3. Navigate to your project folder
4. Upload the updated `AnalyticsController.php`
5. Replace the existing file

### Option 2: Git Pull
```bash
ssh username@your-domain.com
cd public_html/your-project
git pull origin main
```

### Option 3: FTP/SFTP
Upload the modified file using FileZilla or similar

## Verification
After deployment, test:
1. Analytics page loads without 500 error
2. Top performing units display correctly
3. No SQL ambiguity errors in logs

## Clear Cache (if needed)
```bash
php artisan cache:clear
php artisan config:clear
```

## Related Fixes Applied Previously
- GROUP BY strict mode fix (line 73)
- Ambiguous column fix (line 121)

Both fixes ensure MySQL 5.7+ strict mode compatibility.
