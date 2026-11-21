<?php
session_start();
require_once '../database/dbconfig.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

function student_fees_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function student_fees_rows($conn, $sql, $types = '', $params = [])
{
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

function student_fees_redirect($message)
{
    $_SESSION['student_fees_message'] = $message;
    unset($_SESSION['student_fees_csrf_token']);
    header('Location: student_fees.php');
    exit;
}

$student = null;
$fees = [];
$pageError = '';

if (!$conn) {
    $pageError = 'Fee information is temporarily unavailable.';
} else {
    try {
        $studentRows = student_fees_rows(
            $conn,
            'SELECT id, name FROM students WHERE user_id = ? LIMIT 1',
            'i',
            [(int)$_SESSION['id']]
        );
        $student = $studentRows ? $studentRows[0] : null;
    } catch (RuntimeException $error) {
        $pageError = $error->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_fee'])) {
    $postedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
        ? $_POST['csrf_token']
        : '';
    $sessionToken = $_SESSION['student_fees_csrf_token'] ?? '';

    if (!is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
        student_fees_redirect('expired');
    }

    if (!$student || $pageError !== '') {
        student_fees_redirect('error');
    }

    $feeId = isset($_POST['fee_id']) ? (int)$_POST['fee_id'] : 0;
    try {
        $selectedFees = student_fees_rows(
            $conn,
            'SELECT id, status FROM fees WHERE id = ? AND student_id = ? LIMIT 1',
            'ii',
            [$feeId, (int)$student['id']]
        );
    } catch (RuntimeException $error) {
        error_log('Student fees: could not load fee record for payment.');
        student_fees_redirect('error');
    }

    if (!$selectedFees) {
        student_fees_redirect('not_found');
    }
    if ($selectedFees[0]['status'] === 'Paid') {
        student_fees_redirect('already_paid');
    }
    if ($selectedFees[0]['status'] !== 'Unpaid') {
        student_fees_redirect('not_payable');
    }

    $receipt = 'R-' . str_pad((string)$feeId, 5, '0', STR_PAD_LEFT);
    $studentId = (int)$student['id'];
    try {
        $statement = $conn->prepare(
            "UPDATE fees
                SET status = 'Paid',
                    payment_date = CURDATE(),
                    receipt_no = COALESCE(receipt_no, ?)
              WHERE id = ? AND student_id = ? AND status = 'Unpaid'"
        );
        if (!$statement) {
            throw new RuntimeException('Could not prepare the fee payment update.');
        }

        if (!$statement->bind_param('sii', $receipt, $feeId, $studentId)) {
            throw new RuntimeException('Could not bind the fee payment update.');
        }
        if (!$statement->execute()) {
            throw new RuntimeException('Could not record the fee payment.');
        }
    } catch (RuntimeException $error) {
        error_log('Student fees: could not record payment for fee ' . $feeId . '.');
        student_fees_redirect('error');
    }

    if ($statement->affected_rows !== 1) {
        try {
            $currentFee = student_fees_rows(
                $conn,
                'SELECT status FROM fees WHERE id = ? AND student_id = ? LIMIT 1',
                'ii',
                [$feeId, (int)$student['id']]
            );
        } catch (RuntimeException $error) {
            student_fees_redirect('error');
        }
        student_fees_redirect($currentFee && $currentFee[0]['status'] === 'Paid' ? 'already_paid' : 'error');
    }

    student_fees_redirect('success');
}

$messages = [
    'success' => ['success', 'Payment recorded. Your fee is marked as paid.'],
    'already_paid' => ['info', 'This fee has already been paid.'],
    'not_found' => ['danger', 'That fee record does not belong to your account.'],
    'not_payable' => ['warning', 'Only unpaid fee records can be paid here. Contact the administrator about a partial payment.'],
    'expired' => ['warning', 'Your session expired. Reload the page and try again.'],
    'error' => ['danger', 'Could not record the payment. Please try again or contact the administrator.'],
];
$messageKey = $_SESSION['student_fees_message'] ?? '';
unset($_SESSION['student_fees_message']);
$paymentMessage = $messages[$messageKey] ?? null;

$receiptValue = $_GET['receipt'] ?? '';
$receiptId = is_scalar($receiptValue) && ctype_digit((string)$receiptValue) ? (int)$receiptValue : 0;
$receiptRecord = null;
if ($student && $pageError === '' && $receiptId > 0) {
    try {
        $receiptRows = student_fees_rows(
            $conn,
            "SELECT f.id, f.amount, f.payment_date, f.status, f.receipt_no, s.name, s.email
               FROM fees f
               JOIN students s ON s.id = f.student_id
              WHERE f.id = ? AND f.student_id = ? AND f.status = 'Paid' AND f.receipt_no IS NOT NULL
              LIMIT 1",
            'ii',
            [$receiptId, (int)$student['id']]
        );
        $receiptRecord = $receiptRows ? $receiptRows[0] : null;
    } catch (RuntimeException $error) {
        $pageError = $error->getMessage();
    }
}

if ($student && $pageError === '' && $receiptId === 0) {
    try {
        $fees = student_fees_rows(
            $conn,
            'SELECT id, amount, payment_date, status, receipt_no FROM fees WHERE student_id = ? ORDER BY id DESC',
            'i',
            [(int)$student['id']]
        );
    } catch (RuntimeException $error) {
        $pageError = $error->getMessage();
    }
}

if (empty($_SESSION['student_fees_csrf_token'])) {
    $_SESSION['student_fees_csrf_token'] = bin2hex(random_bytes(32));
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
<style>
@media print {
  .side, .top, .site-footer, .receipt-actions { display: none !important; }
  .receipt-card { box-shadow: none !important; }
}
</style>
</head>
<body class="<?php echo $receiptRecord ? 'printing-receipt' : ''; ?>">
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

      <?php if ($paymentMessage): ?>
        <div class="alert alert-<?php echo student_fees_h($paymentMessage[0]); ?>" role="alert"><?php echo student_fees_h($paymentMessage[1]); ?></div>
      <?php endif; ?>
      <?php if ($pageError !== ''): ?>
        <div class="alert alert-danger" role="alert"><?php echo student_fees_h($pageError); ?></div>
      <?php elseif (!$student): ?>
        <div class="alert alert-warning">Your student profile has not been created yet. Please ask the administrator to complete your profile.</div>
      <?php elseif ($receiptId > 0): ?>
        <?php if ($receiptRecord): ?>
          <section class="card receipt-card">
            <h2>Fee payment receipt</h2>
            <div class="card-body">
              <p><b>Receipt number:</b> <?php echo student_fees_h($receiptRecord['receipt_no']); ?></p>
              <p><b>Student:</b> <?php echo student_fees_h($receiptRecord['name']); ?></p>
              <p><b>Email:</b> <?php echo student_fees_h($receiptRecord['email']); ?></p>
              <p><b>Fee record:</b> <?php echo (int)$receiptRecord['id']; ?></p>
              <p><b>Amount paid:</b> ৳<?php echo number_format((float)$receiptRecord['amount'], 2); ?></p>
              <p><b>Payment date:</b> <?php echo $receiptRecord['payment_date'] ? student_fees_h(date('d M Y', strtotime($receiptRecord['payment_date']))) : '—'; ?></p>
              <p><b>Status:</b> <?php echo student_fees_h($receiptRecord['status']); ?></p>
              <p class="muted mb-0">This receipt confirms that the payment was recorded in the student management system.</p>
            </div>
            <div class="card-foot receipt-actions">
              <a class="btn ghost" href="student_fees.php">Back to fees</a>
              <button class="btn" type="button" onclick="window.print()">Print receipt</button>
            </div>
          </section>
        <?php else: ?>
          <div class="alert alert-warning">This paid fee receipt could not be found in your account.</div>
        <?php endif; ?>
      <?php else: ?>
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
                  <td><?php echo (int)$fee['id']; ?></td>
                  <td>৳<?php echo number_format((float)$fee['amount'], 2); ?></td>
                  <td><?php echo student_fees_h($fee['status']); ?></td>
                  <td><?php echo $fee['payment_date'] ? student_fees_h(date('d M Y', strtotime($fee['payment_date']))) : '—'; ?></td>
                  <td><?php echo $fee['receipt_no'] ? student_fees_h($fee['receipt_no']) : '—'; ?></td>
                  <td>
                    <?php if ($fee['status'] === 'Unpaid'): ?>
                      <form method="post" action="student_fees.php">
                        <input type="hidden" name="fee_id" value="<?php echo (int)$fee['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo student_fees_h($_SESSION['student_fees_csrf_token']); ?>">
                        <button class="btn btn-sm" type="submit" name="pay_fee" value="1">Pay now</button>
                      </form>
                    <?php elseif ($fee['status'] === 'Paid' && $fee['receipt_no']): ?>
                      <a class="btn btn-sm" href="student_fees.php?receipt=<?php echo (int)$fee['id']; ?>">View receipt</a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
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
