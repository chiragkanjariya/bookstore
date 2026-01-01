# Cron Job Setup for Order Status Updates

This document provides step-by-step instructions for setting up automated cron jobs on an Ubuntu server to update order statuses (shipped and delivered) by checking with the Maruti API.

## Prerequisites

- Ubuntu server with PHP and Laravel installed
- Laravel application deployed and running
- Maruti API credentials configured in `.env` file
- SSH access to the server

## Cron Jobs Overview

Two cron jobs need to be configured:

1. **Update Shipped Orders**: Checks Maruti API for orders that have been shipped and updates their status
2. **Update Delivered Orders**: Checks Maruti API for orders that have been delivered and updates their status

Both jobs run hourly to ensure timely status updates and customer notifications.

## Setup Steps

### 1. Access Your Server

```bash
ssh user@your-server-ip
```

### 2. Navigate to Your Laravel Project

```bash
cd /path/to/your/laravel/project
```

### 3. Test the Commands Manually

Before setting up cron jobs, test the commands to ensure they work correctly:

```bash
# Test shipped orders update
php artisan orders:update-shipped

# Test delivered orders update
php artisan orders:update-delivered
```

You should see output indicating the number of orders checked and updated.

### 4. Open Crontab Editor

```bash
crontab -e
```

If this is your first time, you may be asked to choose an editor. Select `nano` (option 1) for simplicity.

### 5. Add Cron Jobs

Add the following lines to your crontab file:

```bash
# Laravel Scheduler - runs every minute and handles all scheduled tasks
* * * * * cd /path/to/your/laravel/project && php artisan schedule:run >> /dev/null 2>&1
```

**Important**: Replace `/path/to/your/laravel/project` with the actual path to your Laravel application.

The Laravel scheduler will automatically run the hourly commands defined in `routes/console.php`:
- `orders:update-shipped` - runs every hour
- `orders:update-delivered` - runs every hour

### 6. Save and Exit

- If using `nano`: Press `Ctrl + X`, then `Y`, then `Enter`
- If using `vim`: Press `Esc`, type `:wq`, then `Enter`

### 7. Verify Cron Jobs

Check that your cron jobs are properly configured:

```bash
crontab -l
```

You should see the Laravel scheduler entry you just added.

## Alternative: Direct Cron Jobs (Without Laravel Scheduler)

If you prefer to run the commands directly without the Laravel scheduler, use these cron entries instead:

```bash
# Update shipped orders every hour
0 * * * * cd /path/to/your/laravel/project && php artisan orders:update-shipped >> /var/log/laravel-shipped-orders.log 2>&1

# Update delivered orders every hour
0 * * * * cd /path/to/your/laravel/project && php artisan orders:update-delivered >> /var/log/laravel-delivered-orders.log 2>&1
```

This will:
- Run the shipped orders check at the top of every hour (e.g., 1:00, 2:00, 3:00)
- Run the delivered orders check at the top of every hour
- Log output to `/var/log/laravel-shipped-orders.log` and `/var/log/laravel-delivered-orders.log`

## Monitoring and Logs

### View Laravel Logs

```bash
tail -f /path/to/your/laravel/project/storage/logs/laravel.log
```

### View Cron Job Logs (if using direct cron jobs)

```bash
# View shipped orders log
tail -f /var/log/laravel-shipped-orders.log

# View delivered orders log
tail -f /var/log/laravel-delivered-orders.log
```

### Check Cron Job Execution

```bash
# View system cron log
grep CRON /var/log/syslog
```

## Troubleshooting

### Cron Jobs Not Running

1. **Check cron service status**:
   ```bash
   sudo systemctl status cron
   ```

2. **Restart cron service**:
   ```bash
   sudo systemctl restart cron
   ```

3. **Check file permissions**:
   ```bash
   # Ensure Laravel can write to storage and logs
   sudo chown -R www-data:www-data /path/to/your/laravel/project/storage
   sudo chmod -R 775 /path/to/your/laravel/project/storage
   ```

### Commands Failing

1. **Check PHP path**:
   ```bash
   which php
   ```
   Use the full path in your cron job if needed (e.g., `/usr/bin/php` instead of `php`)

2. **Check environment variables**:
   Cron jobs don't have the same environment as your shell. You may need to source your `.env` file or use full paths.

3. **Test command manually**:
   ```bash
   cd /path/to/your/laravel/project && php artisan orders:update-shipped
   ```

### No Orders Being Updated

1. **Check Maruti API credentials** in `.env`:
   ```
   SHREE_MARUTI_ENABLED=true
   SHREE_MARUTI_CLIENT_CODE=your_client_code
   SHREE_MARUTI_USERNAME=your_username
   SHREE_MARUTI_PASSWORD=your_password
   ```

2. **Check order statuses** in database:
   ```bash
   php artisan tinker
   ```
   ```php
   // Check orders ready to ship
   \App\Models\Order::where('status', 'ready_to_ship')->count();
   
   // Check shipped orders
   \App\Models\Order::where('status', 'shipped')->count();
   ```

3. **Check Laravel logs** for API errors:
   ```bash
   tail -100 /path/to/your/laravel/project/storage/logs/laravel.log | grep "ShreeMaruti"
   ```

## Email Notifications

The cron jobs automatically send email notifications to customers when:
- Orders are marked as shipped (includes tracking number)
- Orders are marked as delivered

Ensure your email service is properly configured in `.env`:
```
MAIL_API_BASE_URL=https://mail.ipdc.org/api/v1
MAIL_AUTH_TOKEN=your_auth_token
```

## Frequency Adjustment

To change how often the cron jobs run, modify the schedule in `routes/console.php`:

```php
// Run every 30 minutes
Schedule::command('orders:update-shipped')->everyThirtyMinutes();
Schedule::command('orders:update-delivered')->everyThirtyMinutes();

// Run every 2 hours
Schedule::command('orders:update-shipped')->everyTwoHours();
Schedule::command('orders:update-delivered')->everyTwoHours();

// Run daily at 9 AM
Schedule::command('orders:update-shipped')->dailyAt('09:00');
Schedule::command('orders:update-delivered')->dailyAt('09:00');
```

After making changes, no need to update crontab - the Laravel scheduler will pick up the changes automatically.

## Security Considerations

1. **Restrict log file access**:
   ```bash
   sudo chmod 640 /var/log/laravel-*.log
   ```

2. **Use environment-specific credentials**: Ensure production Maruti API credentials are different from staging/development

3. **Monitor API usage**: Keep track of Maruti API calls to avoid rate limiting

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check system logs: `/var/log/syslog`
3. Test commands manually before troubleshooting cron
4. Verify Maruti API connectivity and credentials
