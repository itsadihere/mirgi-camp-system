<?php

function whatsapp_ensure_schema($conn)
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $queries = [
        "CREATE TABLE IF NOT EXISTS whatsapp_settings (
            setting_key VARCHAR(100) PRIMARY KEY,
            setting_value LONGTEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS whatsapp_templates (
            template_key VARCHAR(100) PRIMARY KEY,
            template_name VARCHAR(150) NOT NULL,
            body LONGTEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS whatsapp_campaigns (
            campaign_key VARCHAR(100) PRIMARY KEY,
            campaign_name VARCHAR(150) NOT NULL,
            template_key VARCHAR(100) NOT NULL,
            status ENUM('active','paused','stopped') NOT NULL DEFAULT 'active',
            description TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS whatsapp_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campaign_key VARCHAR(100) NOT NULL,
            template_key VARCHAR(100) NOT NULL,
            patient_id INT NULL,
            camp_id INT NULL,
            phone VARCHAR(30) NOT NULL,
            payload_json LONGTEXT NULL,
            scheduled_at DATETIME NOT NULL,
            status ENUM('pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
            attempts INT NOT NULL DEFAULT 0,
            dedupe_key VARCHAR(190) NULL,
            last_error TEXT NULL,
            sent_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_whatsapp_dedupe (dedupe_key),
            KEY idx_whatsapp_queue_due (status, scheduled_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($queries as $sql) {
        mysqli_query($conn, $sql);
    }

    whatsapp_seed_defaults($conn);
    $initialized = true;
}

function whatsapp_seed_defaults($conn)
{
    $defaultSettings = [
        'whatsapp_enabled' => '0',
        'whatsapp_api_url' => '',
        'whatsapp_auth_header_name' => 'Authorization',
        'whatsapp_auth_header_value' => '',
        'whatsapp_phone_field' => 'number',
        'whatsapp_message_field' => 'message',
        'whatsapp_default_country_code' => '91',
        'whatsapp_extra_payload_json' => '{}',
        'whatsapp_batch_limit' => '20',
        'whatsapp_last_bootstrap_run' => '0',
        'whatsapp_last_quarterly_run' => '',
        'whatsapp_last_upcoming_backfill' => '',
        'whatsapp_auto_campaigns_review_required' => '1'
    ];

    foreach ($defaultSettings as $key => $value) {
        $stmt = mysqli_prepare($conn, 'INSERT IGNORE INTO whatsapp_settings (setting_key, setting_value) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $templates = [
        'new_camp_same_state' => [
            'name' => 'New Camp in Same State',
            'body' => "Namaste {{patient_name}},\nA new medical camp has been announced in {{state}}.\nCamp: {{camp_name}}\nDate: {{camp_date}}\nTime: {{start_time}} - {{end_time}}\nVenue: {{venue_name}}\nAddress: {{address}}\nReply or contact the team if you want to attend."
        ],
        'camp_reminder_day_before' => [
            'name' => 'Camp Reminder One Day Before',
            'body' => "Reminder for {{patient_name}}\nYour camp {{camp_name}} is tomorrow.\nDate: {{camp_date}}\nTime: {{start_time}} - {{end_time}}\nVenue: {{venue_name}}\nAddress: {{address}}\nPlease carry previous reports and ID proof."
        ],
        'camp_reminder_day_of' => [
            'name' => 'Camp Reminder On Camp Day',
            'body' => "Today is your camp day, {{patient_name}}.\nCamp: {{camp_name}}\nRegistration No: {{registration_number}}\nTime: {{start_time}} - {{end_time}}\nVenue: {{venue_name}}\nAddress: {{address}}\nPlease arrive on time."
        ],
        'medicine_instruction_day_of' => [
            'name' => 'Medicine Instruction On Camp Day',
            'body' => "Medicine instructions for {{patient_name}}\nCamp: {{camp_name}}\nPlease bring current medicines, old prescriptions, test reports, water, and eat light food before travel unless your doctor advised otherwise."
        ],
        'quarterly_condition_checkin' => [
            'name' => 'Quarterly Condition Check-in',
            'body' => "Namaste {{patient_name}},\nWe are checking on your condition. Please reply with one of these options:\n1. Improvement\n2. Same\n3. Worse\nYour response helps us support you better."
        ]
    ];

    foreach ($templates as $key => $template) {
        $stmt = mysqli_prepare($conn, 'INSERT IGNORE INTO whatsapp_templates (template_key, template_name, body) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sss', $key, $template['name'], $template['body']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $campaigns = [
        'new_camp_same_state' => ['New Camp Alert by State', 'new_camp_same_state', 'paused', 'Send a camp announcement to patients from the same state.'],
        'camp_reminder_day_before' => ['Camp Reminder One Day Before', 'camp_reminder_day_before', 'paused', 'Send reminder one day before the camp date.'],
        'camp_reminder_day_of' => ['Camp Reminder On Camp Day', 'camp_reminder_day_of', 'paused', 'Send reminder on the camp date.'],
        'medicine_instruction_day_of' => ['Medicine Instruction On Camp Day', 'medicine_instruction_day_of', 'paused', 'Send medicine instructions on the camp day.'],
        'quarterly_condition_checkin' => ['Quarterly Condition Check-in', 'quarterly_condition_checkin', 'paused', 'Every three months ask patients about their condition.']
    ];

    foreach ($campaigns as $key => $campaign) {
        $stmt = mysqli_prepare($conn, 'INSERT IGNORE INTO whatsapp_campaigns (campaign_key, campaign_name, template_key, status, description) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssss', $key, $campaign[0], $campaign[1], $campaign[2], $campaign[3]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function whatsapp_get_settings($conn)
{
    whatsapp_ensure_schema($conn);
    $settings = [];
    $result = mysqli_query($conn, 'SELECT setting_key, setting_value FROM whatsapp_settings');
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function whatsapp_set_setting($conn, $key, $value)
{
    whatsapp_ensure_schema($conn);
    $stmt = mysqli_prepare($conn, 'INSERT INTO whatsapp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function whatsapp_get_templates($conn)
{
    whatsapp_ensure_schema($conn);
    $templates = [];
    $result = mysqli_query($conn, 'SELECT * FROM whatsapp_templates ORDER BY template_name ASC');
    while ($row = mysqli_fetch_assoc($result)) {
        $templates[$row['template_key']] = $row;
    }
    return $templates;
}

function whatsapp_get_campaigns($conn)
{
    whatsapp_ensure_schema($conn);
    $campaigns = [];
    $result = mysqli_query($conn, 'SELECT * FROM whatsapp_campaigns ORDER BY campaign_name ASC');
    while ($row = mysqli_fetch_assoc($result)) {
        $campaigns[$row['campaign_key']] = $row;
    }
    return $campaigns;
}

function whatsapp_update_template($conn, $key, $body)
{
    $stmt = mysqli_prepare($conn, 'UPDATE whatsapp_templates SET body = ? WHERE template_key = ?');
    mysqli_stmt_bind_param($stmt, 'ss', $body, $key);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function whatsapp_update_campaign_status($conn, $campaignKey, $status)
{
    $stmt = mysqli_prepare($conn, 'UPDATE whatsapp_campaigns SET status = ? WHERE campaign_key = ?');
    mysqli_stmt_bind_param($stmt, 'ss', $status, $campaignKey);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function whatsapp_campaign_is_active($conn, $campaignKey)
{
    $campaigns = whatsapp_get_campaigns($conn);
    return isset($campaigns[$campaignKey]) && $campaigns[$campaignKey]['status'] === 'active';
}

function whatsapp_normalize_phone($conn, $phone)
{
    $settings = whatsapp_get_settings($conn);
    $digits = preg_replace('/\D+/', '', (string) $phone);
    if ($digits === '') {
        return '';
    }

    $countryCode = preg_replace('/\D+/', '', $settings['whatsapp_default_country_code'] ?? '91');
    if (strlen($digits) === 10 && $countryCode !== '') {
        return $countryCode . $digits;
    }

    return $digits;
}

function whatsapp_render_template($body, array $payload)
{
    foreach ($payload as $key => $value) {
        $body = str_replace('{{' . $key . '}}', (string) $value, $body);
    }

    return preg_replace('/{{\s*[^}]+\s*}}/', '', $body);
}

function whatsapp_enqueue_message($conn, $campaignKey, $phone, array $payload, $scheduledAt, $patientId = null, $campId = null, $dedupeKey = null)
{
    whatsapp_ensure_schema($conn);

    if (!whatsapp_campaign_is_active($conn, $campaignKey)) {
        return false;
    }

    $campaigns = whatsapp_get_campaigns($conn);
    $campaign = $campaigns[$campaignKey] ?? null;
    if (!$campaign) {
        return false;
    }

    $normalizedPhone = whatsapp_normalize_phone($conn, $phone);
    if ($normalizedPhone === '') {
        return false;
    }

    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $stmt = mysqli_prepare($conn, 'INSERT IGNORE INTO whatsapp_queue (campaign_key, template_key, patient_id, camp_id, phone, payload_json, scheduled_at, dedupe_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'ssiissss', $campaignKey, $campaign['template_key'], $patientId, $campId, $normalizedPhone, $payloadJson, $scheduledAt, $dedupeKey);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $affected > 0;
}

function whatsapp_send_message_http($conn, $phone, $message)
{
    $settings = whatsapp_get_settings($conn);
    if (($settings['whatsapp_enabled'] ?? '0') !== '1') {
        return ['success' => false, 'error' => 'WhatsApp messaging is disabled.'];
    }

    $url = trim($settings['whatsapp_api_url'] ?? '');
    if ($url === '') {
        return ['success' => false, 'error' => 'WhatsApp API URL is not configured.'];
    }

    $phoneField = trim($settings['whatsapp_phone_field'] ?? 'number');
    $messageField = trim($settings['whatsapp_message_field'] ?? 'message');
    $extra = json_decode($settings['whatsapp_extra_payload_json'] ?? '{}', true);
    if (!is_array($extra)) {
        $extra = [];
    }

    $payload = $extra;
    $payload[$phoneField] = $phone;
    $payload[$messageField] = $message;

    $headers = ['Content-Type: application/json'];
    $authHeaderName = trim($settings['whatsapp_auth_header_name'] ?? '');
    $authHeaderValue = trim($settings['whatsapp_auth_header_value'] ?? '');
    if ($authHeaderName !== '' && $authHeaderValue !== '') {
        $headers[] = $authHeaderName . ': ' . $authHeaderValue;
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError !== '') {
        return ['success' => false, 'error' => $curlError];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        return ['success' => false, 'error' => 'HTTP ' . $httpCode . ' Response: ' . $response];
    }

    return ['success' => true, 'response' => $response];
}

function whatsapp_dispatch_queue($conn, $limit = 20)
{
    whatsapp_ensure_schema($conn);
    $sent = 0;
    $failed = 0;
    $retried = 0;
    $limit = max(1, (int) $limit);

    // Do NOT attempt delivery (and do NOT consume attempts / mark failures)
    // unless the channel is actually enabled and configured. Otherwise queued
    // messages would be permanently burned to 'failed' before setup is done.
    $settings = whatsapp_get_settings($conn);
    if (($settings['whatsapp_enabled'] ?? '0') !== '1' || trim($settings['whatsapp_api_url'] ?? '') === '') {
        return ['sent' => 0, 'failed' => 0, 'retried' => 0, 'skipped' => true];
    }

    $maxAttempts = 3;
    $sql = "SELECT * FROM whatsapp_queue WHERE status='pending' AND scheduled_at <= NOW() ORDER BY scheduled_at ASC LIMIT {$limit}";
    $result = mysqli_query($conn, $sql);
    $templates = whatsapp_get_templates($conn);

    while ($row = mysqli_fetch_assoc($result)) {
        $payload = json_decode($row['payload_json'] ?? '{}', true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $templateBody = $templates[$row['template_key']]['body'] ?? '';
        $message = whatsapp_render_template($templateBody, $payload);
        $response = whatsapp_send_message_http($conn, $row['phone'], $message);

        if ($response['success']) {
            $update = mysqli_prepare($conn, "UPDATE whatsapp_queue SET status='sent', attempts=attempts+1, sent_at=NOW(), last_error=NULL WHERE id = ?");
            mysqli_stmt_bind_param($update, 'i', $row['id']);
            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
            $sent++;
        } else {
            $error = $response['error'] ?? 'Unknown send error';
            $attempts = (int) $row['attempts'] + 1;

            if ($attempts >= $maxAttempts) {
                // Give up only after real retries have been exhausted.
                $update = mysqli_prepare($conn, "UPDATE whatsapp_queue SET status='failed', attempts=?, last_error=? WHERE id = ?");
                mysqli_stmt_bind_param($update, 'isi', $attempts, $error, $row['id']);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
                $failed++;
            } else {
                // Keep pending and back off (15 min * attempt) so a transient
                // provider/network error does not permanently lose the message.
                $retryAt = date('Y-m-d H:i:s', time() + ($attempts * 900));
                $update = mysqli_prepare($conn, "UPDATE whatsapp_queue SET status='pending', attempts=?, scheduled_at=?, last_error=? WHERE id = ?");
                mysqli_stmt_bind_param($update, 'issi', $attempts, $retryAt, $error, $row['id']);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
                $retried++;
            }
        }
    }

    return ['sent' => $sent, 'failed' => $failed, 'retried' => $retried];
}

function whatsapp_build_payload_from_camp_row(array $row)
{
    return [
        'patient_name' => $row['full_name'] ?? 'Patient',
        'camp_name' => $row['camp_name'] ?? '',
        'camp_date' => $row['camp_date'] ?? '',
        'start_time' => $row['start_time'] ?? '10:00 AM',
        'end_time' => $row['end_time'] ?? 'Till camp close',
        'venue_name' => $row['venue_name'] ?? '',
        'address' => $row['address'] ?? '',
        'district' => $row['district'] ?? '',
        'state' => $row['state'] ?? '',
        'doctor_name' => $row['doctor_name'] ?? '',
        'registration_number' => $row['registration_number'] ?? ''
    ];
}

function whatsapp_schedule_new_camp_state_alerts($conn, $campId)
{
    $campId = (int) $campId;
    if ($campId <= 0) {
        return 0;
    }

    $campStmt = mysqli_prepare($conn, 'SELECT * FROM camps WHERE camp_id = ? LIMIT 1');
    mysqli_stmt_bind_param($campStmt, 'i', $campId);
    mysqli_stmt_execute($campStmt);
    $campResult = mysqli_stmt_get_result($campStmt);
    $camp = mysqli_fetch_assoc($campResult);
    mysqli_stmt_close($campStmt);

    if (!$camp || trim($camp['state']) === '') {
        return 0;
    }

    $state = $camp['state'];
    $recipients = mysqli_prepare($conn, 'SELECT patient_id, full_name, mobile FROM patients_master WHERE state = ? AND mobile <> ""');
    mysqli_stmt_bind_param($recipients, 's', $state);
    mysqli_stmt_execute($recipients);
    $recipientResult = mysqli_stmt_get_result($recipients);

    $count = 0;
    while ($patient = mysqli_fetch_assoc($recipientResult)) {
        $payload = whatsapp_build_payload_from_camp_row(array_merge($camp, $patient));
        $dedupe = 'camp-alert-' . $campId . '-patient-' . (int) $patient['patient_id'];
        if (whatsapp_enqueue_message($conn, 'new_camp_same_state', $patient['mobile'], $payload, date('Y-m-d H:i:s'), (int) $patient['patient_id'], $campId, $dedupe)) {
            $count++;
        }
    }

    mysqli_stmt_close($recipients);
    return $count;
}

function whatsapp_schedule_registration_notifications($conn, $registrationId)
{
    $registrationId = (int) $registrationId;
    if ($registrationId <= 0) {
        return 0;
    }

    $stmt = mysqli_prepare($conn, '
        SELECT registrations.registration_id, registrations.registration_number,
               patients_master.patient_id, patients_master.full_name, patients_master.mobile,
               camps.camp_id, camps.camp_name, camps.camp_date, camps.start_time, camps.end_time,
               camps.venue_name, camps.address, camps.state, camps.district, camps.doctor_name
        FROM registrations
        JOIN patients_master ON registrations.patient_id = patients_master.patient_id
        JOIN camps ON registrations.camp_id = camps.camp_id
        WHERE registrations.registration_id = ?
        LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $registrationId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row || trim($row['mobile']) === '') {
        return 0;
    }

    $payload = whatsapp_build_payload_from_camp_row($row);
    $campDate = $row['camp_date'];
    $scheduled = 0;

    $events = [
        'camp_reminder_day_before' => date('Y-m-d 09:00:00', strtotime($campDate . ' -1 day')),
        'camp_reminder_day_of' => date('Y-m-d 07:00:00', strtotime($campDate)),
        'medicine_instruction_day_of' => date('Y-m-d 06:00:00', strtotime($campDate)),
    ];

    foreach ($events as $campaignKey => $scheduledAt) {
        $dedupe = $campaignKey . '-registration-' . $registrationId;
        if (whatsapp_enqueue_message($conn, $campaignKey, $row['mobile'], $payload, $scheduledAt, (int) $row['patient_id'], (int) $row['camp_id'], $dedupe)) {
            $scheduled++;
        }
    }

    return $scheduled;
}

function whatsapp_backfill_upcoming_reminders($conn)
{
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $query = "
        SELECT registrations.registration_id
        FROM registrations
        JOIN camps ON registrations.camp_id = camps.camp_id
        WHERE registrations.is_deleted = 0
          AND camps.camp_date IN ('$today', '$tomorrow')
    ";
    $result = mysqli_query($conn, $query);
    $count = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $count += whatsapp_schedule_registration_notifications($conn, (int) $row['registration_id']);
    }

    whatsapp_set_setting($conn, 'whatsapp_last_upcoming_backfill', $today);
    return $count;
}

function whatsapp_schedule_quarterly_checkins($conn)
{
    $quarterKey = date('Y') . '-Q' . ceil(date('n') / 3);
    $patients = mysqli_query($conn, "SELECT patient_id, full_name, mobile FROM patients_master WHERE mobile <> ''");
    $count = 0;

    while ($patient = mysqli_fetch_assoc($patients)) {
        $payload = ['patient_name' => $patient['full_name'] ?: 'Patient'];
        $dedupe = 'quarterly-' . (int) $patient['patient_id'] . '-' . $quarterKey;
        if (whatsapp_enqueue_message($conn, 'quarterly_condition_checkin', $patient['mobile'], $payload, date('Y-m-d 10:00:00'), (int) $patient['patient_id'], null, $dedupe)) {
            $count++;
        }
    }

    whatsapp_set_setting($conn, 'whatsapp_last_quarterly_run', $quarterKey);
    return $count;
}

function whatsapp_bootstrap($conn)
{
    whatsapp_ensure_schema($conn);
    $settings = whatsapp_get_settings($conn);
    $lastRun = (int) ($settings['whatsapp_last_bootstrap_run'] ?? 0);
    $now = time();

    if (($now - $lastRun) < 900) {
        return;
    }

    whatsapp_set_setting($conn, 'whatsapp_last_bootstrap_run', (string) $now);

    $today = date('Y-m-d');
    if (($settings['whatsapp_last_upcoming_backfill'] ?? '') !== $today) {
        whatsapp_backfill_upcoming_reminders($conn);
    }

    $quarterKey = date('Y') . '-Q' . ceil(date('n') / 3);
    if (($settings['whatsapp_last_quarterly_run'] ?? '') !== $quarterKey) {
        whatsapp_schedule_quarterly_checkins($conn);
    }

    $batchLimit = (int) ($settings['whatsapp_batch_limit'] ?? 20);
    whatsapp_dispatch_queue($conn, $batchLimit);
}

function whatsapp_force_run($conn)
{
    whatsapp_ensure_schema($conn);
    $scheduled = whatsapp_backfill_upcoming_reminders($conn);
    $quarterKey = date('Y') . '-Q' . ceil(date('n') / 3);
    $settings = whatsapp_get_settings($conn);
    if (($settings['whatsapp_last_quarterly_run'] ?? '') !== $quarterKey) {
        $scheduled += whatsapp_schedule_quarterly_checkins($conn);
    }
    $batchLimit = (int) ($settings['whatsapp_batch_limit'] ?? 20);
    $dispatch = whatsapp_dispatch_queue($conn, $batchLimit);
    return ['scheduled' => $scheduled, 'sent' => $dispatch['sent'], 'failed' => $dispatch['failed']];
}
?>

