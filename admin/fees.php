<?php
session_start();
include '../database/dbconfig.php';

// Admin Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function rows($conn, $sql, $types = '', $params = []) {
    $st = $conn->prepare($sql);
    if (!$st) return [];
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

$statuses = ['Paid', 'Partial', 'Unpaid'];

// Receipt number like R-00012 (only for fees that have a payment)
function makeReceipt($conn, $feeId) {
    $receipt = 'R-' . str_pad((string)$feeId, 5, '0', STR_PAD_LEFT);
    $st = $conn->prepare("UPDATE fees SET receipt_no = ? WHERE id = ? AND receipt_no IS NULL");
    $st->bind_param("si", $receipt, $feeId);
    $st->execute();
}

/* ---------- Add a fee record ---------- */
if (isset($_POST['add_fee'])) {
    $studentId = (int)($_POST['student_id'] ?? 0);
    $amount    = (float)($_POST['amount'] ?? 0);
    $status    = $_POST['status'] ?? 'Unpaid';
    $payDate   = $_POST['payment_date'] ?? '';

    $exists = rows($conn, "SELECT id FROM students WHERE id = ?", "i", [$studentId]);
    if (!$exists || $amount <= 0 || !in_array($status, $statuses, true)) {
        header("Location: fees.php?msg=error");
        exit;
    }
    // Unpaid fees have no payment date; paid ones default to today
    if ($status === 'Unpaid') {
        $payDate = null;
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $payDate)) {
        $payDate = date('Y-m-d');
    }

    $st = $conn->prepare("INSERT INTO fees (student_id, amount, payment_date, status) VALUES (?, ?, ?, ?)");
    $st->bind_param("idss", $studentId, $amount, $payDate, $status);
    if ($st->execute()) {
        if ($status !== 'Unpaid') makeReceipt($conn, $conn->insert_id);
        header("Location: fees.php?msg=added");
    } else {
        header("Location: fees.php?msg=error");
    }
    exit;
}

/* ---------- Mark an unpaid/partial fee as paid ---------- */
if (isset($_POST['mark_paid'])) {
    $feeId = (int)$_POST['id'];
    $today = date('Y-m-d');
    $st = $conn->prepare("UPDATE fees SET status = 'Paid', payment_date = COALESCE(payment_date, ?) WHERE id = ?");
    $st->bind_param("si", $today, $feeId);
    $st->execute();
    makeReceipt($conn, $feeId);
    header("Location: fees.php?msg=paid");
    exit;
}

/* ---------- Delete a fee record ---------- */
if (isset($_POST['delete_fee'])) {
    $feeId = (int)$_POST['id'];
    $st = $conn->prepare("DELETE FROM fees WHERE id = ?");
    $st->bind_param("i", $feeId);
    $st->execute();
    header("Location: fees.php?msg=deleted");
    exit;
}

/* ---------- Page data ---------- */
$fStatus = $_GET['status'] ?? '';
$fSearch = trim($_GET['q'] ?? '');
if (!in_array($fStatus, $statuses, true)) $fStatus = '';
$like = '%' . $fSearch . '%';

$fees = rows($conn,
    "SELECT f.*, s.name, s.email
       FROM fees f JOIN students s ON s.id = f.student_id
      WHERE (? = '' OR f.status = ?)
        AND (? = '' OR s.name LIKE ? OR f.receipt_no LIKE ?)
      ORDER BY f.id DESC
      LIMIT 200",
    "sssss", [$fStatus, $fStatus, $fSearch, $like, $like]);

$students = rows($conn, "SELECT id, name FROM students ORDER BY name");

// summary cards (all records, ignoring the filter)
$sum = (rows($conn,
    "SELECT COALESCE(SUM(CASE WHEN status IN ('Paid','Partial') THEN amount END),0) AS collected,
            COALESCE(SUM(CASE WHEN status = 'Unpaid' THEN amount END),0) AS pending,
            SUM(status = 'Paid') AS paid_n, SUM(status = 'Partial') AS partial_n, SUM(status = 'Unpaid') AS unpaid_n
       FROM fees")[0] ?? []) + ['collected' => 0, 'pending' => 0, 'paid_n' => 0, 'partial_n' => 0, 'unpaid_n' => 0];

$msgs = [
    'added'   => ['success', 'Fee record added.'],
    'paid'    => ['success', 'Fee marked as paid.'],
    'deleted' => ['success', 'Fee record deleted.'],
    'error'   => ['danger',  'Could not save. Choose a student and enter an amount above 0.'],
];
$flash = $msgs[$_GET['msg'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fees | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/fees.css">
</head>
<body>
<div class="app">

  <?php include '../includes/sidebar.php'; ?>

  <div class="main">
    <?php include '../includes/topbar.php'; ?>

    <main class="content">

      <div class="head">
        <div>
          <h1>Fees</h1>
          <p>Record student fees, payments and receipts.</p>
        </div>
      </div>

      <?php if ($flash): ?><div class="alert alert-<?php echo $flash[0]; ?>"><?php echo h($flash[1]); ?></div><?php endif; ?>

      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span><div><b>৳<?php echo number_format((float)$sum['collected'], 2); ?></b><span>Collected</span></div></div>
        <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b>৳<?php echo number_format((float)$sum['pending'], 2); ?></b><span>Unpaid amount</span></div></div>
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span><div><b><?php echo (int)$sum['paid_n']; ?></b><span>Paid</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg></span><div><b><?php echo (int)$sum['partial_n'] + (int)$sum['unpaid_n']; ?></b><span>Partial / unpaid</span></div></div>
      </section>

      <!-- Add fee -->
      <section class="card mb-4">
        <h2>Add fee record</h2>
        <div class="card-body">
          <form class="row g-3 align-items-end" method="post" action="fees.php">
            <div class="col-md-4">
              <label class="form-label" for="stu">Student</label>
              <select class="form-select" id="stu" name="student_id" required>
                <option value="">Select student</option>
                <?php foreach ($students as $s): ?>
                  <option value="<?php echo (int)$s['id']; ?>"><?php echo h($s['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="amt">Amount</label>
              <input class="form-control" type="number" id="amt" name="amount" min="0.01" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="st">Status</label>
              <select class="form-select" id="st" name="status">
                <?php foreach ($statuses as $s): ?><option><?php echo $s; ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="pd">Payment date</label>
              <input class="form-control" type="date" id="pd" name="payment_date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2 d-grid">
              <button class="btn" type="submit" name="add_fee" value="1">Add fee</button>
            </div>
          </form>
        </div>
      </section>

      <!-- Fee list -->
      <section class="card">
        <h2>All fee records</h2>
        <form class="tool-bar" method="get" action="fees.php">
          <div class="search">
            <input type="search" name="q" value="<?php echo h($fSearch); ?>" placeholder="Search by student or receipt no" aria-label="Search">
          </div>
          <select class="form-select" name="status" aria-label="Filter by status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $s): ?>
              <option <?php echo $fStatus === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn" type="submit">Filter</button>
        </form>

        <div class="table-wrap">
          <table>
            <thead><tr><th>Receipt</th><th>Student</th><th>Amount</th><th>Payment date</th><th>Status</th><th class="num">Actions</th></tr></thead>
            <tbody>
            <?php if (!$fees): ?>
              <tr><td colspan="6" class="muted" style="padding:32px;text-align:center">No fee records found.</td></tr>
            <?php endif; ?>
            <?php foreach ($fees as $f): ?>
              <tr>
                <td class="receipt"><?php echo h($f['receipt_no'] ?: '—'); ?></td>
                <td><div class="who"><span class="dot"><?php echo h(strtoupper(substr($f['name'], 0, 2))); ?></span>
                  <div><b><?php echo h($f['name']); ?></b><small><?php echo h($f['email']); ?></small></div></div></td>
                <td class="amount">৳<?php echo number_format((float)$f['amount'], 2); ?></td>
                <td><?php echo $f['payment_date'] ? h(date('d M Y', strtotime($f['payment_date']))) : '—'; ?></td>
                <td><span class="pill <?php echo strtolower($f['status']); ?>"><?php echo h($f['status']); ?></span></td>
                <td>
                  <form class="row-actions" method="post" action="fees.php"
                        onsubmit="return event.submitter.name !== 'delete_fee' || confirm('Delete this fee record?');">
                    <input type="hidden" name="id" value="<?php echo (int)$f['id']; ?>">
                    <?php if ($f['status'] !== 'Paid'): ?>
                      <button class="small-btn" type="submit" name="mark_paid" value="1">Mark paid</button>
                    <?php endif; ?>
                    <button class="small-btn danger" type="submit" name="delete_fee" value="1">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="card-foot"><span>Showing the latest <?php echo count($fees); ?> record(s)</span></div>
      </section>

    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
// Highlight "Fees" in the sidebar
document.querySelectorAll('aside nav a').forEach(function (a) {
  a.classList.toggle('on', a.getAttribute('href') === 'fees.php');
});
// Payment date is not needed for unpaid fees
var st = document.getElementById('st'), pd = document.getElementById('pd');
function togglePay() { pd.disabled = st.value === 'Unpaid'; }
st.addEventListener('change', togglePay); togglePay();
</script>
</body>
</html>