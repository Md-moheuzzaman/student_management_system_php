<?php
require __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Administrator access is required.');
}

$pageTitle = 'Fees';
$activePage = 'fees';
$searchInput = $_GET['q'] ?? '';
$statusInput = $_GET['status'] ?? 'All';
$search = is_string($searchInput) ? substr(trim($searchInput), 0, 100) : '';
$status = is_string($statusInput) ? $statusInput : 'All';
if (!in_array($status, ['All', 'Paid', 'Unpaid', 'Partial'], true)) {
    $status = 'All';
}
$fees = [];
$paymentBreakdown = [
  'Paid' => ['records' => 0, 'amount' => 0.0],
  'Unpaid' => ['records' => 0, 'amount' => 0.0],
  'Partial' => ['records' => 0, 'amount' => 0.0],
];
$summary = ['total' => 0, 'paid' => 0, 'unpaid' => 0, 'collected' => 0];
$databaseError = false;

try {
    $db = db_connection();
    $summary['total'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM fees');
    $summary['paid'] = (int) db_scalar($db, "SELECT COUNT(*) FROM fees WHERE status = 'Paid'");
    $summary['unpaid'] = (int) db_scalar($db, "SELECT COUNT(*) FROM fees WHERE status <> 'Paid'");
    $summary['collected'] = (float) db_scalar($db, "SELECT COALESCE(SUM(amount), 0) FROM fees WHERE status = 'Paid'");

    foreach (db_rows($db, 'SELECT status, COUNT(*) AS records, COALESCE(SUM(amount), 0) AS amount FROM fees GROUP BY status') as $group) {
      $paymentBreakdown[$group['status']] = [
        'records' => (int) $group['records'],
        'amount' => (float) $group['amount'],
      ];
    }

    $pattern = '%' . $search . '%';
    $stmt = $db->prepare("SELECT f.id, f.amount, f.payment_date, f.status, f.receipt_no, s.name, s.email, c.class_name, c.section FROM fees f JOIN students s ON s.id = f.student_id LEFT JOIN classes c ON c.id = s.class_id WHERE (? = '' OR s.name LIKE ? OR s.email LIKE ?) AND (? = 'All' OR f.status = ?) ORDER BY f.id DESC LIMIT 100");
    $stmt->bind_param('sssss', $search, $pattern, $pattern, $status, $status);
    $stmt->execute();
    $fees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    $databaseError = true;
}

  $maxPaymentAmount = max(array_column($paymentBreakdown, 'amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Ustudent</title>
<script>try { document.documentElement.dataset.theme = localStorage.getItem('admin-theme') === 'dark' ? 'dark' : 'light'; } catch (error) { document.documentElement.dataset.theme = 'light'; }</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-shell">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="admin-main">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
    <main class="dashboard-content">
      <div class="welcome-row"><div><p class="eyebrow">PAYMENTS &amp; ACCOUNTS</p><h2>Fee records</h2><p>Track payment status and collection.</p></div></div>
      <?php if ($databaseError): ?><div class="alert alert-error" role="alert">Fee data is unavailable. Check the database connection and imported schema.</div><?php endif; ?>
      <section class="stats-grid fee-stats" aria-label="Fee summary">
        <article class="stat-card stat-card-green"><div class="stat-card-top"><span>FEE RECORDS</span></div><strong><?= number_format($summary['total']) ?></strong><p>All payment records</p></article>
        <article class="stat-card stat-card-white"><div class="stat-card-top"><span>PAID</span></div><strong><?= number_format($summary['paid']) ?></strong><p>Settled records</p></article>
        <article class="stat-card stat-card-gold"><div class="stat-card-top"><span>OUTSTANDING</span></div><strong><?= number_format($summary['unpaid']) ?></strong><p>Unpaid or partial</p></article>
        <article class="stat-card stat-card-white"><div class="stat-card-top"><span>COLLECTED</span></div><strong class="stat-currency">৳<?= number_format($summary['collected'], 2) ?></strong><p>Paid fee amount</p></article>
      </section>
      <section class="panel fee-chart-panel" aria-labelledby="fee-chart-title">
        <div class="panel-heading"><div><p class="eyebrow">PAYMENT BREAKDOWN</p><h3 id="fee-chart-title">Amounts by status</h3></div><span class="panel-count">All fee records</span></div>
        <div class="fee-chart" aria-label="Recorded fee amount grouped by payment status">
          <?php foreach ($paymentBreakdown as $paymentStatus => $breakdown): ?>
            <?php $barWidth = $maxPaymentAmount > 0 ? round($breakdown['amount'] / $maxPaymentAmount * 100, 1) : 0; ?>
            <div class="fee-chart-row">
              <div class="fee-chart-label"><span class="status-pill <?= e(strtolower($paymentStatus)) ?>"><?= e($paymentStatus) ?></span><span><?= number_format($breakdown['records']) ?> records</span><strong>৳<?= number_format($breakdown['amount'], 2) ?></strong></div>
              <div class="fee-chart-track" aria-hidden="true"><span class="fee-chart-bar <?= e(strtolower($paymentStatus)) ?>" style="width:<?= $barWidth ?>%"></span></div>
            </div>
          <?php endforeach; ?>
        </div>
        <p class="table-footnote">Bar lengths compare the total recorded amount for each status.</p>
      </section>
      <section class="panel record-panel">
        <form class="record-filters" method="get" action="fees.php">
          <label class="filter-search"><span>Search fees</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="Student name or email"></label>
          <label><span>Payment status</span><select name="status"><?php foreach (['All', 'Paid', 'Unpaid', 'Partial'] as $option): ?><option value="<?= e($option) ?>"<?= $status === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></label>
          <button class="button button-green filter-submit" type="submit">Apply filters</button>
          <?php if ($search !== '' || $status !== 'All'): ?><a class="filter-reset" href="fees.php">Reset</a><?php endif; ?>
        </form>
        <?php if (!$fees): ?><div class="empty-state"><span class="empty-mark" aria-hidden="true">৳</span><div><strong><?= $databaseError ? 'No records to display' : 'No matching fee records' ?></strong><p><?= $databaseError ? 'Connect to your student database to load fee records.' : 'Try a different name or payment status.' ?></p></div></div>
        <?php else: ?><div class="record-table-wrap"><table class="record-table"><thead><tr><th>STUDENT</th><th>CLASS</th><th>AMOUNT</th><th>PAYMENT DATE</th><th>RECEIPT</th><th>STATUS</th></tr></thead><tbody>
          <?php foreach ($fees as $fee): ?><tr><td><span class="student-name"><?= e($fee['name']) ?><small><?= e($fee['email']) ?></small></span></td><td><?= e(trim(($fee['class_name'] ?? 'Unassigned') . ' ' . ($fee['section'] ?? ''))) ?></td><td>৳<?= number_format((float) $fee['amount'], 2) ?></td><td><?= $fee['payment_date'] ? e(date('M j, Y', strtotime($fee['payment_date']))) : '—' ?></td><td><?= e($fee['receipt_no'] ?: '—') ?></td><td><span class="status-pill <?= e(strtolower($fee['status'])) ?>"><?= e($fee['status']) ?></span></td></tr><?php endforeach; ?>
        </tbody></table></div><p class="table-footnote">Showing <?= count($fees) ?> of up to 100 matching records</p><?php endif; ?>
      </section>
    </main>
    <?php require __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>
<script src="../assets/js/admin-theme.js"></script>
</body>
</html>