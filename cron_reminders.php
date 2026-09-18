<?php
/*
 * WhatsApp queue processor — intended to be run by a scheduler.
 *
 *   CLI:   php cron_reminders.php
 *   HTTP:  https://your-domain/medical-camp-system/cron_reminders.php?token=SECRET
 *
 * The secret lives in the whatsapp_settings table (key: whatsapp_cron_token).
 * Unauthenticated HTTP requests are rejected so the endpoint cannot be
 * triggered by random visitors.
 */

include 'includes/db_connect.php';

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    $settings = whatsapp_get_settings($conn);
    $expected = (string) ($settings['whatsapp_cron_token'] ?? '');
    $provided = (string) ($_GET['token'] ?? '');

    if ($expected === '' || !hash_equals($expected, $provided)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('Forbidden');
    }
}

$stats = whatsapp_force_run($conn);

header('Content-Type: text/plain; charset=UTF-8');
echo 'WhatsApp queue processed.' . PHP_EOL;
echo 'Scheduled: ' . $stats['scheduled'] . PHP_EOL;
echo 'Sent: ' . $stats['sent'] . PHP_EOL;
echo 'Failed: ' . $stats['failed'] . PHP_EOL;
