<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/role_check.php';

requireRole(['superadmin', 'admin']);
whatsapp_ensure_schema($conn);

$manualTemplateStmt = mysqli_prepare($conn, 'INSERT IGNORE INTO whatsapp_templates (template_key, template_name, body) VALUES (?, ?, ?)');
$manualTemplateKey = 'manual_custom_audience';
$manualTemplateName = 'Manual Custom Audience Broadcast';
$manualTemplateBody = "Namaste {{patient_name}},\n{{custom_message}}";
mysqli_stmt_bind_param($manualTemplateStmt, 'sss', $manualTemplateKey, $manualTemplateName, $manualTemplateBody);
mysqli_stmt_execute($manualTemplateStmt);
mysqli_stmt_close($manualTemplateStmt);

$manualCampaignStmt = mysqli_prepare($conn, 'INSERT IGNORE INTO whatsapp_campaigns (campaign_key, campaign_name, template_key, status, description) VALUES (?, ?, ?, ?, ?)');
$manualCampaignKey = 'manual_custom_audience';
$manualCampaignName = 'Manual Custom Audience Broadcast';
$manualCampaignStatus = 'active';
$manualCampaignDescription = 'Send a custom message to a filtered and selected audience.';
mysqli_stmt_bind_param($manualCampaignStmt, 'sssss', $manualCampaignKey, $manualCampaignName, $manualTemplateKey, $manualCampaignStatus, $manualCampaignDescription);
mysqli_stmt_execute($manualCampaignStmt);
mysqli_stmt_close($manualCampaignStmt);

function whatsapp_audience_filters($conn, array $source)
{
    $where = ["registrations.is_deleted=0", "patients_master.mobile <> ''"];

    if (isset($source['aud_camp_id']) && $source['aud_camp_id'] !== '' && $source['aud_camp_id'] !== 'all') {
        $campId = (int) $source['aud_camp_id'];
        $where[] = "registrations.camp_id={$campId}";
    }

    if (!empty($source['aud_state'])) {
        $state = mysqli_real_escape_string($conn, trim($source['aud_state']));
        $where[] = "patients_master.state='{$state}'";
    }

    if (!empty($source['aud_district'])) {
        $district = mysqli_real_escape_string($conn, trim($source['aud_district']));
        $where[] = "patients_master.district='{$district}'";
    }

    if (!empty($source['aud_gender'])) {
        $gender = mysqli_real_escape_string($conn, trim($source['aud_gender']));
        $where[] = "patients_master.gender='{$gender}'";
    }

    if (!empty($source['aud_age_group'])) {
        if ($source['aud_age_group'] === 'above70') {
            $where[] = 'patients_master.age > 70';
        } else {
            $age = (int) $source['aud_age_group'];
            if ($age > 0) {
                $where[] = "patients_master.age < {$age}";
            }
        }
    }

    return implode(' AND ', $where);
}

function whatsapp_filter_value(array $source, $key, $default = '')
{
    return isset($source[$key]) ? (string) $source[$key] : $default;
}

function whatsapp_render_audience_hidden_fields(array $source)
{
    $keys = ['aud_camp_id', 'aud_state', 'aud_district', 'aud_gender', 'aud_age_group', 'aud_query'];
    foreach ($keys as $key) {
        $value = whatsapp_filter_value($source, $key, '');
        echo '<input type="hidden" name="' . h($key) . '" value="' . h($value) . '">';
    }
}

$message = '';
$error = '';
$settings = whatsapp_get_settings($conn);

if (($settings['whatsapp_auto_campaigns_review_required'] ?? '1') === '1') {
    $autoCampaignKeys = [
        'new_camp_same_state',
        'camp_reminder_day_before',
        'camp_reminder_day_of',
        'medicine_instruction_day_of',
        'quarterly_condition_checkin'
    ];

    foreach ($autoCampaignKeys as $campaignKey) {
        whatsapp_update_campaign_status($conn, $campaignKey, 'paused');
    }

    whatsapp_set_setting($conn, 'whatsapp_auto_campaigns_review_required', '0');
    $settings = whatsapp_get_settings($conn);
}

if (isset($_POST['save_settings'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $map = [
            'whatsapp_enabled' => isset($_POST['whatsapp_enabled']) ? '1' : '0',
            'whatsapp_api_url' => trim($_POST['whatsapp_api_url'] ?? ''),
            'whatsapp_auth_header_name' => trim($_POST['whatsapp_auth_header_name'] ?? ''),
            'whatsapp_auth_header_value' => trim($_POST['whatsapp_auth_header_value'] ?? ''),
            'whatsapp_phone_field' => trim($_POST['whatsapp_phone_field'] ?? 'number'),
            'whatsapp_message_field' => trim($_POST['whatsapp_message_field'] ?? 'message'),
            'whatsapp_default_country_code' => preg_replace('/\D+/', '', $_POST['whatsapp_default_country_code'] ?? '91'),
            'whatsapp_extra_payload_json' => trim($_POST['whatsapp_extra_payload_json'] ?? '{}'),
            'whatsapp_batch_limit' => (string) max(1, (int) ($_POST['whatsapp_batch_limit'] ?? 20)),
        ];
        foreach ($map as $key => $value) {
            whatsapp_set_setting($conn, $key, $value);
        }
        $message = 'WhatsApp API settings saved.';
        $settings = whatsapp_get_settings($conn);
    }
}

if (isset($_POST['save_templates'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        foreach (whatsapp_get_templates($conn) as $templateKey => $template) {
            $field = 'template_' . $templateKey;
            if (isset($_POST[$field])) {
                whatsapp_update_template($conn, $templateKey, trim($_POST[$field]));
            }
        }
        $message = 'Templates updated successfully.';
    }
}

if (isset($_POST['save_campaigns'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        foreach (whatsapp_get_campaigns($conn) as $campaignKey => $campaign) {
            $field = 'campaign_' . $campaignKey;
            $status = $_POST[$field] ?? $campaign['status'];
            if (in_array($status, ['active', 'paused', 'stopped'], true)) {
                whatsapp_update_campaign_status($conn, $campaignKey, $status);
            }
        }
        $message = 'Campaign status updated.';
    }
}

if (isset($_POST['send_test'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $phone = trim($_POST['test_phone'] ?? '');
        $body = trim($_POST['test_message'] ?? '');
        $response = whatsapp_send_message_http($conn, whatsapp_normalize_phone($conn, $phone), $body);
        if ($response['success']) {
            $message = 'Test WhatsApp message sent successfully.';
        } else {
            $error = 'Test send failed: ' . ($response['error'] ?? 'Unknown error');
        }
    }
}

if (isset($_POST['run_now'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $stats = whatsapp_force_run($conn);
        $message = 'WhatsApp processor ran now. Scheduled: ' . $stats['scheduled'] . ', Sent: ' . $stats['sent'] . ', Failed: ' . $stats['failed'];
    }
}

if (isset($_POST['queue_custom_audience'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $customMessage = trim($_POST['custom_message'] ?? '');
        $selectAllAudience = ($_POST['select_all_audience'] ?? '0') === '1';
        $excludedRows = array_values(array_filter(array_map('intval', $_POST['excluded_recipients'] ?? [])));
        $sendAt = trim($_POST['send_at'] ?? '');
        $scheduleTimestamp = $sendAt !== '' ? strtotime($sendAt) : time();

        if (!$selectAllAudience) {
            $error = 'Use Select All Audience before sending, then exclude only the patients you do not want.';
        } elseif ($customMessage === '') {
            $error = 'Enter the message body before queuing the campaign.';
        } elseif ($scheduleTimestamp === false) {
            $error = 'The scheduled date and time is invalid.';
        } else {
            $scheduleAt = date('Y-m-d H:i:s', $scheduleTimestamp);
            $audienceWhere = whatsapp_audience_filters($conn, $_POST);
            if (!empty($excludedRows)) {
                $audienceWhere .= ' AND registrations.registration_id NOT IN (' . implode(',', $excludedRows) . ')';
            }

            $query = "
                SELECT registrations.registration_id, registrations.registration_number,
                       patients_master.patient_id, patients_master.full_name, patients_master.mobile,
                       camps.camp_id, camps.camp_name, camps.camp_date, camps.start_time, camps.end_time,
                       camps.venue_name, camps.address, camps.state, camps.district, camps.doctor_name
                FROM registrations
                JOIN patients_master ON registrations.patient_id = patients_master.patient_id
                JOIN camps ON registrations.camp_id = camps.camp_id
                WHERE {$audienceWhere}
                ORDER BY registrations.registration_id DESC
            ";
            $result = mysqli_query($conn, $query);
            $queued = 0;

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $payload = whatsapp_build_payload_from_camp_row($row);
                    $payload['custom_message'] = $customMessage;
                    $dedupe = 'manual-' . (int) $row['registration_id'] . '-' . time() . '-' . mt_rand(1000, 9999);
                    if (whatsapp_enqueue_message($conn, 'manual_custom_audience', $row['mobile'], $payload, $scheduleAt, (int) $row['patient_id'], (int) $row['camp_id'], $dedupe)) {
                        $queued++;
                    }
                }
            }

            audit_log($conn, 'whatsapp.broadcast_queued', 'whatsapp_campaign', 'manual_custom_audience', ['recipients' => $queued, 'scheduled_at' => $scheduleAt]);
            $message = 'Custom audience campaign queued for ' . $queued . ' recipients.';
        }
    }
}

$settings = whatsapp_get_settings($conn);
$templates = whatsapp_get_templates($conn);
$campaigns = whatsapp_get_campaigns($conn);
$queueSummary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(status='pending') as pending_count, SUM(status='sent') as sent_count, SUM(status='failed') as failed_count FROM whatsapp_queue"));
$recentQueue = mysqli_query($conn, 'SELECT * FROM whatsapp_queue ORDER BY id DESC LIMIT 20');
$campsList = mysqli_query($conn, 'SELECT camp_id, camp_name, camp_date FROM camps ORDER BY camp_date DESC');

$audienceSource = $_GET;
$audienceWhere = whatsapp_audience_filters($conn, $audienceSource);
$audienceCountResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM registrations JOIN patients_master ON registrations.patient_id = patients_master.patient_id JOIN camps ON registrations.camp_id = camps.camp_id WHERE {$audienceWhere}");
$audienceTotal = (int) (mysqli_fetch_assoc($audienceCountResult)['total'] ?? 0);
$audienceQuery = trim($_GET['aud_query'] ?? '');
$excludeSearchSql = "
    SELECT registrations.registration_id, registrations.registration_number,
           patients_master.full_name, patients_master.age, patients_master.gender,
           camps.camp_name, camps.camp_date
    FROM registrations
    JOIN patients_master ON registrations.patient_id = patients_master.patient_id
    JOIN camps ON registrations.camp_id = camps.camp_id
    WHERE {$audienceWhere}
";
if ($audienceQuery !== '') {
    $escapedAudienceQuery = mysqli_real_escape_string($conn, $audienceQuery);
    $excludeSearchSql .= " AND (patients_master.full_name LIKE '%{$escapedAudienceQuery}%' OR registrations.registration_number LIKE '%{$escapedAudienceQuery}%')";
}
$excludeSearchSql .= ' ORDER BY patients_master.full_name ASC LIMIT 25';
$excludeSearchResults = mysqli_query($conn, $excludeSearchSql);
$matchedSearchCount = $excludeSearchResults ? mysqli_num_rows($excludeSearchResults) : 0;
?>

<div class="content">
<div class="card-box">
<h4>WhatsApp Campaign Manager</h4>
<p>Configure API access, edit templates, and control automated WhatsApp campaigns.</p>
<?php if ($message !== '') { ?><div class="alert alert-success"><?= h($message); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><?= h($error); ?></div><?php } ?>
<div class="alert alert-warning">Automatic WhatsApp campaigns are paused right now. Start them only when you are ready from the campaign controls below.</div>

<div class="row g-3 mb-4">
<div class="col-md-4"><div class="card-box text-center"><h6>Pending Queue</h6><h3><?= (int) ($queueSummary['pending_count'] ?? 0); ?></h3></div></div>
<div class="col-md-4"><div class="card-box text-center"><h6>Sent</h6><h3><?= (int) ($queueSummary['sent_count'] ?? 0); ?></h3></div></div>
<div class="col-md-4"><div class="card-box text-center"><h6>Failed</h6><h3><?= (int) ($queueSummary['failed_count'] ?? 0); ?></h3></div></div>
</div>

<div class="card-box mb-4">
<h5>Custom Audience Broadcast</h5>
<p>Filter registrations first, then use Select All Audience. If someone should not receive the message, search them by patient name or registration number and exclude them.</p>
<form method="GET" class="mb-4">
<div class="row g-3">
<div class="col-md-3"><label>Camp</label><select name="aud_camp_id" class="form-control"><option value="all">View All</option><?php mysqli_data_seek($campsList, 0); while ($camp = mysqli_fetch_assoc($campsList)) { $selected = (($_GET['aud_camp_id'] ?? 'all') == $camp['camp_id']) ? 'selected' : ''; echo "<option value='" . (int) $camp['camp_id'] . "' {$selected}>" . h($camp['camp_name']) . ' (' . h($camp['camp_date']) . ")</option>"; } ?></select></div>
<div class="col-md-2"><label>State</label><input type="text" name="aud_state" value="<?= h($_GET['aud_state'] ?? ''); ?>" class="form-control"></div>
<div class="col-md-2"><label>District</label><input type="text" name="aud_district" value="<?= h($_GET['aud_district'] ?? ''); ?>" class="form-control"></div>
<div class="col-md-2"><label>Gender</label><select name="aud_gender" class="form-control"><option value="">All</option><option value="Male" <?= (($_GET['aud_gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option><option value="Female" <?= (($_GET['aud_gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option><option value="Other" <?= (($_GET['aud_gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option></select></div>
<div class="col-md-3"><label>Age Group</label><select name="aud_age_group" class="form-control"><option value="">All</option><option value="5" <?= (($_GET['aud_age_group'] ?? '') === '5') ? 'selected' : ''; ?>>Below 5</option><option value="10" <?= (($_GET['aud_age_group'] ?? '') === '10') ? 'selected' : ''; ?>>Below 10</option><option value="20" <?= (($_GET['aud_age_group'] ?? '') === '20') ? 'selected' : ''; ?>>Below 20</option><option value="50" <?= (($_GET['aud_age_group'] ?? '') === '50') ? 'selected' : ''; ?>>Below 50</option><option value="70" <?= (($_GET['aud_age_group'] ?? '') === '70') ? 'selected' : ''; ?>>Below 70</option><option value="above70" <?= (($_GET['aud_age_group'] ?? '') === 'above70') ? 'selected' : ''; ?>>Above 70</option></select></div>
<div class="col-md-4"><label>Search For Exclusion</label><input type="text" name="aud_query" value="<?= h($audienceQuery); ?>" class="form-control" placeholder="Patient name or registration number"></div>
</div>
<br>
<button class="btn btn-spiritual">Apply Filters</button>
</form>

<form method="POST">
<?= csrf_input(); ?>
<?php whatsapp_render_audience_hidden_fields($_GET); ?>
<div class="row g-3 mb-3 align-items-end">
<div class="col-md-4">
<div class="form-check">
<input class="form-check-input" type="checkbox" id="selectAllAudience" checked>
<label class="form-check-label" for="selectAllAudience">Select All Audience</label>
</div>
<input type="hidden" name="select_all_audience" id="selectAllAudienceValue" value="1">
<small class="text-muted d-block mt-2">Current filter matches <strong><?= $audienceTotal; ?></strong> registrations.</small>
</div>
<div class="col-md-4"><label>Schedule At</label><input type="datetime-local" name="send_at" class="form-control"><small class="text-muted d-block mt-2">Leave empty to queue immediately.</small></div>
<div class="col-md-4"><label>Excluded by search</label><div class="form-control" style="min-height: 42px; background: #f8f4ef;"><?= $matchedSearchCount; ?> result(s) ready to exclude</div></div>
<div class="col-12"><label>Custom Message</label><textarea name="custom_message" class="form-control" rows="5" placeholder="Write the message you want to send to this filtered audience."></textarea></div>
</div>

<div class="card-box" style="background:#fff8ef; border:1px solid #f0dcc0;">
<h6>Exclude Recipients</h6>
<p class="mb-3">Only the searched matches are shown here. Tick anyone who should not receive this campaign.</p>
<?php if ($matchedSearchCount > 0) { ?>
<div class="row g-2">
<?php while ($row = mysqli_fetch_assoc($excludeSearchResults)) { ?>
<div class="col-md-6">
<label class="d-flex align-items-start gap-2 p-2" style="border:1px solid #ead4b3; border-radius:10px; background:#fff; cursor:pointer;">
<input type="checkbox" name="excluded_recipients[]" value="<?= (int) $row['registration_id']; ?>" class="mt-1 audience-exclude-checkbox">
<span>
<strong><?= h($row['full_name']); ?></strong>
<span class="d-block text-muted"><?= h($row['registration_number']); ?><?php if (!empty($row['camp_name'])) { ?> | <?= h($row['camp_name']); ?><?php } ?><?php if (!empty($row['camp_date'])) { ?> (<?= h($row['camp_date']); ?>)<?php } ?></span>
<span class="d-block text-muted">Age <?= (int) $row['age']; ?>, <?= h($row['gender']); ?></span>
</span>
</label>
</div>
<?php } ?>
</div>
<?php } else { ?>
<div class="text-muted">Search by patient name or registration number to exclude specific people from this audience.</div>
<?php } ?>
</div>
<br>
<button class="btn btn-spiritual" name="queue_custom_audience">Queue Message for Filtered Audience</button>
</form>
</div>

<div class="card-box mb-4">
<h5>API Settings</h5>
<form method="POST">
<?= csrf_input(); ?>
<div class="form-check mb-3">
<input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" <?= ($settings['whatsapp_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
<label class="form-check-label" for="whatsapp_enabled">Enable WhatsApp messaging</label>
</div>
<div class="row g-3">
<div class="col-md-6"><label>API URL</label><input type="text" name="whatsapp_api_url" class="form-control" value="<?= h($settings['whatsapp_api_url'] ?? ''); ?>" placeholder="https://your-provider.example/send"></div>
<div class="col-md-3"><label>Auth Header Name</label><input type="text" name="whatsapp_auth_header_name" class="form-control" value="<?= h($settings['whatsapp_auth_header_name'] ?? 'Authorization'); ?>"></div>
<div class="col-md-3"><label>Auth Header Value</label><input type="text" name="whatsapp_auth_header_value" class="form-control" value="<?= h($settings['whatsapp_auth_header_value'] ?? ''); ?>" placeholder="Bearer YOUR_TOKEN"></div>
<div class="col-md-3"><label>Phone Field</label><input type="text" name="whatsapp_phone_field" class="form-control" value="<?= h($settings['whatsapp_phone_field'] ?? 'number'); ?>"></div>
<div class="col-md-3"><label>Message Field</label><input type="text" name="whatsapp_message_field" class="form-control" value="<?= h($settings['whatsapp_message_field'] ?? 'message'); ?>"></div>
<div class="col-md-3"><label>Default Country Code</label><input type="text" name="whatsapp_default_country_code" class="form-control" value="<?= h($settings['whatsapp_default_country_code'] ?? '91'); ?>"></div>
<div class="col-md-3"><label>Batch Limit</label><input type="number" name="whatsapp_batch_limit" class="form-control" value="<?= h($settings['whatsapp_batch_limit'] ?? '20'); ?>" min="1" max="100"></div>
<div class="col-12"><label>Extra Payload JSON</label><textarea name="whatsapp_extra_payload_json" class="form-control" rows="4" placeholder='{"instance_id":"abc"}'><?= h($settings['whatsapp_extra_payload_json'] ?? '{}'); ?></textarea></div>
</div>
<br>
<button class="btn btn-spiritual" name="save_settings">Save Settings</button>
</form>
</div>

<div class="card-box mb-4">
<h5>Campaign Controls</h5>
<form method="POST">
<?= csrf_input(); ?>
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead style="background:#ffecd9;"><tr><th>Campaign</th><th>Description</th><th>Template</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($campaigns as $campaignKey => $campaign) { ?>
<tr>
<td><?= h($campaign['campaign_name']); ?></td>
<td><?= h($campaign['description']); ?></td>
<td><?= h($campaign['template_key']); ?></td>
<td><select name="campaign_<?= h($campaignKey); ?>" class="form-select"><option value="active" <?= $campaign['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="paused" <?= $campaign['status'] === 'paused' ? 'selected' : ''; ?>>Paused</option><option value="stopped" <?= $campaign['status'] === 'stopped' ? 'selected' : ''; ?>>Stopped</option></select></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<button class="btn btn-spiritual" name="save_campaigns">Save Campaign Status</button>
</form>
</div>

<div class="card-box mb-4">
<h5>Message Templates</h5>
<form method="POST">
<?= csrf_input(); ?>
<?php foreach ($templates as $templateKey => $template) { ?>
<div class="mb-4"><label><?= h($template['template_name']); ?> <small class="text-muted">(<?= h($templateKey); ?>)</small></label><textarea name="template_<?= h($templateKey); ?>" class="form-control" rows="5"><?= h($template['body']); ?></textarea></div>
<?php } ?>
<button class="btn btn-spiritual" name="save_templates">Save Templates</button>
</form>
</div>

<div class="row g-4">
<div class="col-lg-6"><div class="card-box h-100"><h5>Send Test Message</h5><form method="POST"><?= csrf_input(); ?><label>Phone Number</label><input type="text" name="test_phone" class="form-control" placeholder="91XXXXXXXXXX" required><label class="mt-3">Message</label><textarea name="test_message" class="form-control" rows="5" required>Namaste, this is a test WhatsApp message from <?= h(APP_NAME); ?>.</textarea><br><button class="btn btn-spiritual" name="send_test">Send Test</button></form></div></div>
<div class="col-lg-6"><div class="card-box h-100"><h5>Manual Processor Run</h5><p>Use this after saving your API to queue and send reminders immediately for verification.</p><form method="POST"><?= csrf_input(); ?><button class="btn btn-dark" name="run_now">Run Queue Now</button></form></div></div>
</div>

<div class="card-box mt-4">
<h5>Recent WhatsApp Queue</h5>
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead style="background:#ffecd9;"><tr><th>ID</th><th>Campaign</th><th>Phone</th><th>Scheduled</th><th>Status</th><th>Error</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($recentQueue)) { ?>
<tr><td><?= (int) $row['id']; ?></td><td><?= h($row['campaign_key']); ?></td><td><?= h($row['phone']); ?></td><td><?= h($row['scheduled_at']); ?></td><td><?= h($row['status']); ?></td><td><?= h($row['last_error'] ?? ''); ?></td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllAudience = document.getElementById('selectAllAudience');
    const selectAllAudienceValue = document.getElementById('selectAllAudienceValue');
    const exclusionCheckboxes = document.querySelectorAll('.audience-exclude-checkbox');

    function syncAudienceSelection() {
        if (selectAllAudience && selectAllAudienceValue) {
            selectAllAudienceValue.value = selectAllAudience.checked ? '1' : '0';
            exclusionCheckboxes.forEach(function (checkbox) {
                checkbox.disabled = !selectAllAudience.checked;
                if (!selectAllAudience.checked) {
                    checkbox.checked = false;
                }
            });
        }
    }

    if (selectAllAudience) {
        selectAllAudience.addEventListener('change', syncAudienceSelection);
        syncAudienceSelection();
    }
});
</script>
