#!/bin/bash
cd /vercel/share/v0-project
php artisan migrate --path=database/migrations/2026_04_07_000001_add_payment_splits_to_orders_table.php --force
