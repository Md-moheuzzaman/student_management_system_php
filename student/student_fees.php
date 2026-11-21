<?php
session_start();
require_once '../database/dbconfig.php';

$bkash_config_file = __DIR__ . '/../database/bkash_config.php';
if (is_file($bkash_config_file)) {
    require_once $bkash_config_file;
}

function student_fees_h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function student_fees_rows($conn, $sql, $types = '', $params = array()) {
    $statement = $conn->prepare($sql);
    if (!$statement) {
        error_log('Student fees: could not prepare database query.');
        throw new RuntimeException('Fee information is temporarily unavailable.');
    }

    if ($types !== '' && !$statement->bind_param($types, ...$params)) {
        error_log('Student fees: could not bind database query parameters.');
        throw new RuntimeException('Fee information is temporarily unavailable.');
    }

    if (!$statement->execute()) {
        error_log('Student fees: database query failed.');
        throw new RuntimeException('Fee information is temporarily unavailable.');
    }

    $result = $statement->get_result();
    if (!$result) {
        error_log('Student fees: could not read database query results.');
        throw new RuntimeException('Fee information is temporarily unavailable.');
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function student_fees_bkash_setting($name, $default = '') {
    if (defined($name)) {
        return trim((string) constant($name));
    }

    $value = getenv($name);
    return $value === false ? $default : trim($value);
}

function student_fees_bkash_request($endpoint, $body, $headers = array()) {
    if (!function_exists('curl_init')) {
        error_log('bKash sandbox: PHP cURL extension is not available.');
        return false;
    }

    $base_url = rtrim(student_fees_bkash_setting('BKASH_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout'), '/');
    $url = $base_url . '/' . ltrim($endpoint, '/');
    $http_headers = array_merge(
        array('Accept: application/json', 'Content-Type: application/json'),
        $headers
    );

    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => $http_headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ));

    $response = curl_exec($curl);
    $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);

    if ($response === false || $http_code < 200 || $http_code >= 300) {
        error_log('bKash sandbox: ' . $endpoint . ' request failed (HTTP ' . $http_code . '). ' . $curl_error);
        return false;
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        error_log('bKash sandbox: ' . $endpoint . ' returned invalid JSON.');
        return false;
    }

    return $decoded;
}

function student_fees_bkash_token($settings) {
    $result = student_fees_bkash_request(
        'token/grant',
        array(
            'app_key'    => $settings['app_key'],
            'app_secret' => $settings['app_secret'],
        ),
        array(
            'username: ' . $settings['username'],
            'password: ' . $settings['password'],
        )
    );

    if (!$result || !isset($result['statusCode'], $result['id_token']) || (string) $result['statusCode'] !== '0000' || (string) $result['id_token'] === '') {
        error_log('bKash sandbox: token grant failed.');
        return false;
    }

    return (string) $result['id_token'];
}

function student_fees_bkash_authorized_request($endpoint, $body, $settings) {
    $token = student_fees_bkash_token($settings);
    if (!$token) {
        return false;
    }

    return student_fees_bkash_request(
        $endpoint,
        $body,
        array(
            'Authorization: ' . $token,
            'X-App-Key: ' . $settings['app_key'],
        )
    );
}

function student_fees_bkash_callback_url() {
    $configured = student_fees_bkash_setting('BKASH_CALLBACK_URL');
    if ($configured !== '') {
        $url = $configured;
    } else {
        $host = isset($_SERVER['SERVER_NAME']) ? (string) $_SERVER['SERVER_NAME'] : '';
        $is_https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
        if ($host === '') {
            return false;
        }

        $scheme = $is_https ? 'https' : 'http';
        $default_port = $is_https ? 443 : 80;
        $port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : $default_port;
        $path = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '/student/student_fees.php';
        $url = $scheme . '://' . $host . ($port !== $default_port ? ':' . $port : '') . $path;
    }

    $parts = parse_url($url);
    if (!$parts || empty($parts['scheme']) || empty($parts['host'])
        || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
        return false;
    }

    $scheme = strtolower($parts['scheme']);
    $host = strtolower($parts['host']);
    $is_local_http = $scheme === 'http' && in_array($host, array('localhost', '127.0.0.1', '::1', '[::1]'), true);
    if ($scheme !== 'https' && !$is_local_http) {
        return false;
    }

    return $url;
}

function student_fees_bkash_redirect($result, $fee_id = 0) {
    header('Location: student_fees.php?' . http_build_query(array(
        'payment' => $result,
        'fee_id'  => (int) $fee_id,
    )));
    exit;
}

function student_fees_bkash_result_is_valid($result, $payment_id, $invoice, $amount) {
    return is_array($result)
        && isset($result['statusCode'], $result['transactionStatus'], $result['paymentID'], $result['merchantInvoiceNumber'], $result['amount'], $result['trxID'])
        && (string) $result['statusCode'] === '0000'
        && (string) $result['transactionStatus'] === 'Completed'
        && hash_equals($payment_id, (string) $result['paymentID'])
        && hash_equals($invoice, (string) $result['merchantInvoiceNumber'])
        && (!isset($result['currency']) || (string) $result['currency'] === 'BDT')
        && is_numeric($result['amount'])
        && (int) round((float) $result['amount'] * 100) === (int) round((float) $amount * 100)
        && (string) $result['trxID'] !== '';
}

function student_fees_bkash_handle_callback($conn, $settings) {
    $fee_id = isset($_GET['fee_id']) && is_scalar($_GET['fee_id']) ? abs((int) $_GET['fee_id']) : 0;
    $invoice = isset($_GET['invoice']) && is_string($_GET['invoice']) ? $_GET['invoice'] : '';
    $state = isset($_GET['state']) && is_string($_GET['state']) ? $_GET['state'] : '';
    $expected_state = hash_hmac('sha256', $fee_id . '|' . $invoice, $settings['app_secret']);

    if ($fee_id < 1 || $invoice === '' || $state === '' || !hash_equals($expected_state, $state)) {
        http_response_code(400);
        exit('Invalid bKash callback.');
    }

    $callback_status = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
    if ($callback_status !== 'success') {
        student_fees_bkash_redirect($callback_status === 'cancel' ? 'cancelled' : 'failed', $fee_id);
    }

    $payment_id = isset($_GET['paymentID']) && is_string($_GET['paymentID']) ? trim($_GET['paymentID']) : '';
    if ($payment_id === '') {
        student_fees_bkash_redirect('failed', $fee_id);
    }

    $fees = student_fees_rows(
        $conn,
        'SELECT id, student_id, amount, status FROM fees WHERE id = ? LIMIT 1',
        'i',
        array($fee_id)
    );
    if (!$fees) {
        http_response_code(404);
        exit('Fee record not found.');
    }

    $fee = $fees[0];
    if ($fee['status'] === 'Paid') {
        student_fees_bkash_redirect('already_paid', $fee_id);
    }
    if ($fee['status'] !== 'Unpaid') {
        student_fees_bkash_redirect('failed', $fee_id);
    }

    $result = student_fees_bkash_authorized_request(
        'execute',
        array('paymentID' => $payment_id),
        $settings
    );

    if (!student_fees_bkash_result_is_valid($result, $payment_id, $invoice, $fee['amount'])) {
        $result = student_fees_bkash_authorized_request(
            'payment/status',
            array('paymentID' => $payment_id),
            $settings
        );
    }

    if (!student_fees_bkash_result_is_valid($result, $payment_id, $invoice, $fee['amount'])) {
        error_log('bKash sandbox: payment could not be verified for fee ' . $fee_id . '.');
        student_fees_bkash_redirect('failed', $fee_id);
    }

    $receipt = 'R-' . str_pad((string) $fee_id, 5, '0', STR_PAD_LEFT);
    $statement = $conn->prepare(
        "UPDATE fees
            SET status = 'Paid',
                payment_date = CURDATE(),
                receipt_no = COALESCE(receipt_no, ?)
          WHERE id = ? AND student_id = ? AND status = 'Unpaid'"
    );
    if (!$statement) {
        error_log('bKash sandbox: could not prepare the fee update for fee ' . $fee_id . '.');
        student_fees_bkash_redirect('failed', $fee_id);
    }

    $statement->bind_param('sii', $receipt, $fee_id, $fee['student_id']);
    if (!$statement->execute()) {
        error_log('bKash sandbox: could not update fee ' . $fee_id . ' after payment.');
        student_fees_bkash_redirect('failed', $fee_id);
    }

    if ($statement->affected_rows !== 1) {
        $current = student_fees_rows($conn, 'SELECT status FROM fees WHERE id = ? LIMIT 1', 'i', array($fee_id));
        student_fees_bkash_redirect($current && $current[0]['status'] === 'Paid' ? 'already_paid' : 'failed', $fee_id);
    }

    student_fees_bkash_redirect('success', $fee_id);
}

$settings = array(
    'username'   => student_fees_bkash_setting('BKASH_USERNAME'),
    'password'   => student_fees_bkash_setting('BKASH_PASSWORD'),
    'app_key'    => student_fees_bkash_setting('BKASH_APP_KEY'),
    'app_secret' => student_fees_bkash_setting('BKASH_APP_SECRET'),
);
$base_url = rtrim(student_fees_bkash_setting('BKASH_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout'), '/');
$base_parts = parse_url($base_url);
$base_is_sandbox = $base_parts && !empty($base_parts['scheme']) && strtolower($base_parts['scheme']) === 'https'
    && !empty($base_parts['host']) && strtolower($base_parts['host']) === 'tokenized.sandbox.bka.sh';
$gateway_configured = $base_is_sandbox && !in_array('', $settings, true);
$callback_configured = student_fees_bkash_callback_url() !== false;
$checkout_enabled = $gateway_configured && $callback_configured;

if (isset($_GET['bkash_callback'])) {
    if (!$conn || !$gateway_configured) {
        http_response_code(503);
        exit('Payment confirmation is temporarily unavailable. Check the bKash sandbox configuration.');
    }
    try {
        student_fees_bkash_handle_callback($conn, $settings);
    } catch (RuntimeException $error) {
        error_log('bKash sandbox: callback could not be processed.');
        http_response_code(503);
        exit('Payment confirmation is temporarily unavailable.');
    }
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student = null;
$fees = array();
$page_error = '';
$payment_messages = array(
    'success'      => array('success', 'bKash sandbox payment verified. Your fee is marked as paid.'),
    'already_paid' => array('info', 'This fee has already been paid.'),
    'cancelled'    => array('warning', 'The bKash sandbox payment was cancelled. No fee status was changed.'),
    'failed'       => array('danger', 'The payment could not be verified. Your fee remains unpaid. Please try again or contact the administrator.'),
);
$payment_key = isset($_GET['payment']) && is_string($_GET['payment']) ? $_GET['payment'] : '';
$payment_message = isset($payment_messages[$payment_key]) ? $payment_messages[$payment_key] : null;

if (!$conn) {
    $page_error = 'Fee information is temporarily unavailable.';
} else {
    try {
        $student_rows = student_fees_rows(
            $conn,
            'SELECT id, name FROM students WHERE user_id = ? LIMIT 1',
            'i',
            array((int) $_SESSION['id'])
        );
        $student = $student_rows ? $student_rows[0] : null;
    } catch (RuntimeException $error) {
        $page_error = $error->getMessage();
    }
}

if (isset($_POST['start_bkash_payment']) && $_SERVER['REQUEST_METHOD'] === 'POST' && $student && $page_error === '') {
    $nonce = isset($_POST['fee_payment_nonce']) && is_string($_POST['fee_payment_nonce']) ? $_POST['fee_payment_nonce'] : '';
    if (empty($_SESSION['fee_payment_nonce']) || !hash_equals($_SESSION['fee_payment_nonce'], $nonce)) {
        $page_error = 'Your session expired. Reload the page and try again.';
    } else {
        unset($_SESSION['fee_payment_nonce']);
        $fee_id = isset($_POST['fee_id']) ? (int) $_POST['fee_id'] : 0;

        try {
            $selected_fee = student_fees_rows(
                $conn,
                'SELECT id, amount, status FROM fees WHERE id = ? AND student_id = ? LIMIT 1',
                'ii',
                array($fee_id, (int) $student['id'])
            );
        } catch (RuntimeException $error) {
            $selected_fee = array();
            $page_error = $error->getMessage();
        }

        if ($page_error === '' && (!$selected_fee || $selected_fee[0]['status'] !== 'Unpaid' || (float) $selected_fee[0]['amount'] <= 0)) {
            $page_error = 'This fee is not available for payment.';
        } elseif ($page_error === '' && !$gateway_configured) {
            $page_error = 'bKash sandbox credentials are missing or the API URL is not the sandbox URL. Configure BKASH_USERNAME, BKASH_PASSWORD, BKASH_APP_KEY, BKASH_APP_SECRET and BKASH_BASE_URL.';
        } elseif ($page_error === '' && !($callback_url = student_fees_bkash_callback_url())) {
            $page_error = 'A public HTTPS callback URL is required. Set BKASH_CALLBACK_URL to this page’s public HTTPS URL.';
        } elseif ($page_error === '') {
            try {
                $invoice = 'F' . $fee_id . '-' . bin2hex(random_bytes(4));
            } catch (Exception $error) {
                $invoice = '';
                error_log('bKash sandbox: could not generate a payment invoice.');
            }

            if ($invoice === '') {
                $page_error = 'Could not start the sandbox payment. Please try again.';
            } else {
                $state = hash_hmac('sha256', $fee_id . '|' . $invoice, $settings['app_secret']);
                $callback_url .= (strpos($callback_url, '?') === false ? '?' : '&') . http_build_query(array(
                    'bkash_callback' => 1,
                    'fee_id'         => $fee_id,
                    'invoice'        => $invoice,
                    'state'          => $state,
                ));

                $result = student_fees_bkash_authorized_request(
                    'create',
                    array(
                        'mode'                  => '0011',
                        'payerReference'        => (string) $student['id'],
                        'callbackURL'           => $callback_url,
                        'amount'                => number_format((float) $selected_fee[0]['amount'], 2, '.', ''),
                        'currency'              => 'BDT',
                        'intent'                => 'sale',
                        'merchantInvoiceNumber' => $invoice,
                    ),
                    $settings
                );

                $checkout_url = $result && isset($result['statusCode'], $result['paymentID'], $result['bkashURL'])
                    && (string) $result['statusCode'] === '0000'
                    && (string) $result['paymentID'] !== ''
                    ? (string) $result['bkashURL']
                    : '';
                $checkout_parts = $checkout_url !== '' ? parse_url($checkout_url) : false;

                if (!$checkout_parts || empty($checkout_parts['scheme']) || strtolower($checkout_parts['scheme']) !== 'https'
                    || empty($checkout_parts['host']) || strtolower($checkout_parts['host']) !== 'tokenized.sandbox.bka.sh') {
                    error_log('bKash sandbox: create payment failed for fee ' . $fee_id . '.');
                    $page_error = 'Could not start the bKash sandbox payment. Check the credentials and try again.';
                } else {
                    header('Location: ' . $checkout_url);
                    exit;
                }
            }
        }
    }
}

if ($student && $page_error === '') {
    try {
        $fees = student_fees_rows(
            $conn,
            'SELECT id, amount, payment_date, status, receipt_no FROM fees WHERE student_id = ? ORDER BY id DESC',
            'i',
            array((int) $student['id'])
        );
    } catch (RuntimeException $error) {
        $page_error = $error->getMessage();
    }
}

if (empty($_SESSION['fee_payment_nonce'])) {
    $_SESSION['fee_payment_nonce'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Fees | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
</head>
<body>
<div class="app">
  <?php include '../includes/sidebar_student.php'; ?>
  <div class="main">
    <?php include '../includes/topbar.php'; ?>
    <main class="content">
      <div class="head">
        <div>
          <h1>My Fees</h1>
          <p><?php echo $student ? 'Fee records for ' . student_fees_h($student['name']) . '.' : 'View your fee records and payment status.'; ?></p>
        </div>
      </div>

      <?php if ($payment_message): ?>
        <div class="alert alert-<?php echo student_fees_h($payment_message[0]); ?>" role="alert"><?php echo student_fees_h($payment_message[1]); ?></div>
      <?php endif; ?>
      <?php if ($page_error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?php echo student_fees_h($page_error); ?></div>
      <?php endif; ?>

      <?php if (!$student && $page_error === ''): ?>
        <div class="alert alert-warning">Your student profile has not been created yet. Please ask the administrator to add you in the Students page.</div>
      <?php elseif ($student && $page_error === ''): ?>
        <?php if (!$gateway_configured): ?>
          <div class="alert alert-info">bKash sandbox checkout is not configured. Configure the sandbox credentials and sandbox API URL to enable payments.</div>
        <?php elseif (!$callback_configured): ?>
          <div class="alert alert-info">Set BKASH_CALLBACK_URL to this page’s public HTTPS URL to enable bKash sandbox checkout.</div>
        <?php endif; ?>
        <section class="card">
          <h2>Fee records</h2>
          <div class="table-wrap">
            <table class="table align-middle mb-0">
              <thead><tr><th>Fee #</th><th>Amount</th><th>Status</th><th>Payment date</th><th>Receipt</th><th>Action</th></tr></thead>
              <tbody>
              <?php if (!$fees): ?>
                <tr><td colspan="6" class="muted text-center p-4">No fee records are assigned to your account yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($fees as $fee): ?>
                <tr>
                  <td><?php echo (int) $fee['id']; ?></td>
                  <td>৳<?php echo number_format((float) $fee['amount'], 2); ?></td>
                  <td><?php echo student_fees_h($fee['status']); ?></td>
                  <td><?php echo $fee['payment_date'] ? student_fees_h(date('d M Y', strtotime($fee['payment_date']))) : '—'; ?></td>
                  <td><?php echo $fee['receipt_no'] ? student_fees_h($fee['receipt_no']) : '—'; ?></td>
                  <td>
                    <?php if ($fee['status'] === 'Unpaid' && $checkout_enabled): ?>
                      <form method="post" action="student_fees.php">
                        <input type="hidden" name="fee_id" value="<?php echo (int) $fee['id']; ?>">
                        <input type="hidden" name="fee_payment_nonce" value="<?php echo student_fees_h($_SESSION['fee_payment_nonce']); ?>">
                        <button class="btn btn-sm" type="submit" name="start_bkash_payment" value="1">Pay with bKash (Sandbox)</button>
                      </form>
                    <?php elseif ($fee['status'] === 'Unpaid'): ?>
                      <span class="text-muted">Sandbox not configured</span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-foot"><span>Sandbox only — no live payment will be taken.</span></div>
        </section>
      <?php endif; ?>
    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
document.querySelectorAll('aside nav a').forEach(function (link) {
  link.classList.toggle('on', link.getAttribute('href') === 'student_fees.php');
});
</script>
</body>
</html>