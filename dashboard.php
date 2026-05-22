<?php
$title = "SPMR Suraksha";
$description = "Security Application for Govt. SPMR College";
$LOGIN_URI = $_SERVER['REQUEST_URI'];
include 'lib/header.php';

$attendance = [
  'total' => 0,
  'in_time' => 0,
  'late' => 0,
  'absent' => 0,
];

$weeklyTrend = [0, 0, 0, 0, 0, 0, 0];
$todaysVisitors = [];
$alerts = [];

try {
  require_once 'lib/db.php';
  $pdo = DB::conn();

  $today = date('Y-m-d');
  $onTimeCutoff = '10:00:00';

  $activeGuardsStmt = $pdo->query("SELECT COUNT(*) AS c FROM guards WHERE status = 'active'");
  $activeGuards = (int) $activeGuardsStmt->fetch()['c'];

  $markedStmt = $pdo->prepare('SELECT COUNT(DISTINCT guard_id) AS c FROM visitor_logs WHERE log_date = :today');
  $markedStmt->execute([':today' => $today]);
  $totalMarked = (int) $markedStmt->fetch()['c'];

  $inTimeStmt = $pdo->prepare(
    'SELECT COUNT(DISTINCT guard_id) AS c FROM visitor_logs WHERE log_date = :today AND log_time <= :cutoff'
  );
  $inTimeStmt->execute([
    ':today' => $today,
    ':cutoff' => $onTimeCutoff,
  ]);
  $inTime = (int) $inTimeStmt->fetch()['c'];

  $attendance['total'] = $totalMarked;
  $attendance['in_time'] = $inTime;
  $attendance['late'] = max(0, $totalMarked - $inTime);
  $attendance['absent'] = max(0, $activeGuards - $totalMarked);

  $trendStmt = $pdo->query(
    'SELECT log_date, COUNT(DISTINCT guard_id) AS c '
    . 'FROM visitor_logs '
    . 'WHERE log_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE() '
    . 'GROUP BY log_date'
  );
  $trendMap = [];
  while ($row = $trendStmt->fetch()) {
    $trendMap[(string) $row['log_date']] = (int) $row['c'];
  }

  $weeklyTrend = [];
  for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime('-' . $i . ' day'));
    $weeklyTrend[] = $trendMap[$day] ?? 0;
  }

  $visitorStmt = $pdo->prepare(
    'SELECT visitor_userid, log_time FROM visitor_logs WHERE log_date = :today ORDER BY log_time DESC LIMIT 20'
  );
  $visitorStmt->execute([':today' => $today]);
  while ($row = $visitorStmt->fetch()) {
    $userId = (string) $row['visitor_userid'];
    $todaysVisitors[] = [
      'name' => 'Visitor ' . $userId,
      'userId' => $userId,
      'time' => (string) $row['log_time'],
      'status' => 'Checked In',
    ];
  }

  if ($attendance['absent'] > 5) {
    $alerts[] = [
      'level' => 'High',
      'message' => $attendance['absent'] . ' active guards are not marked today.',
    ];
  }
  if ($attendance['late'] > 0) {
    $alerts[] = [
      'level' => 'Medium',
      'message' => $attendance['late'] . ' guards reported after ' . $onTimeCutoff . '.',
    ];
  }
  if (count($todaysVisitors) > 0) {
    $alerts[] = [
      'level' => 'Info',
      'message' => count($todaysVisitors) . ' visitor entries recorded so far.',
    ];
  }
} catch (Throwable $e) {
  $alerts[] = [
    'level' => 'Info',
    'message' => 'Live dashboard data unavailable. Showing empty state until database is configured.',
  ];
}

if (empty($todaysVisitors)) {
  $todaysVisitors[] = [
    'name' => 'No visitors yet',
    'userId' => '-',
    'time' => '-',
    'status' => 'Waiting',
  ];
}

if (empty($alerts)) {
  $alerts[] = [
    'level' => 'Info',
    'message' => 'No security alerts at this time.',
  ];
}

$maxTrend = max($weeklyTrend);
$minTrend = min($weeklyTrend);
$spread = max(1, $maxTrend - $minTrend);
$points = [];
foreach ($weeklyTrend as $index => $value) {
  $x = 20 + ($index * 60);
  $y = 180 - (int) ((($value - $minTrend) / $spread) * 130);
  $points[] = $x . ',' . $y;
}
$polylinePoints = implode(' ', $points);
?>

<style>
  .dashboard-shell {
    --accent: #0f9d58;
    --accent-dark: #0b6c3d;
    --surface: #f4f7f5;
    --ink: #112218;
    --warning: #c77d00;
    --danger: #9d0208;
    --card-radius: 18px;
    color: var(--ink);
  }

  .dashboard-shell .dashboard-hero {
    background: linear-gradient(115deg, #e8f7ee 0%, #f7f1e8 45%, #e7f1fb 100%);
    border-radius: 24px;
    border: 1px solid #d6e2da;
    box-shadow: 0 14px 40px rgba(17, 34, 24, 0.08);
  }

  .dashboard-shell .dashboard-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 6px 14px;
    font-size: 0.85rem;
    background: rgba(15, 157, 88, 0.12);
    color: var(--accent-dark);
    font-weight: 700;
  }

  .dashboard-shell .stat-card {
    border-radius: var(--card-radius);
    background: #ffffff;
    border: 1px solid #dfebe2;
    box-shadow: 0 12px 24px rgba(13, 31, 20, 0.06);
  }

  .dashboard-shell .stat-number {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
  }

  .dashboard-shell .section-card {
    border-radius: var(--card-radius);
    border: 1px solid #dfebe2;
    background: #ffffff;
    box-shadow: 0 12px 24px rgba(13, 31, 20, 0.05);
  }

  .dashboard-shell .badge-warning-soft {
    background: rgba(199, 125, 0, 0.12);
    color: #8a5a00;
  }

  .dashboard-shell .badge-danger-soft {
    background: rgba(157, 2, 8, 0.12);
    color: #7f0106;
  }

  .dashboard-shell .badge-info-soft {
    background: rgba(30, 95, 193, 0.12);
    color: #1e5fc1;
  }

  .dashboard-shell .visitor-table thead th {
    background: var(--surface);
    border-bottom: 0;
  }

  @media (max-width: 768px) {
    .dashboard-shell .stat-number {
      font-size: 1.7rem;
    }
  }
</style>

<div class="container dashboard-shell py-2 py-md-3">
  <div class="w-100 text-center">
    <a href="/"><img class="img-fluid abstract-brand" src="/logo-wide.png" alt=""></a>
  </div>
  <div class="col-md-6 mx-auto text-center">
    <h1 class="display-4 mt-4 mb-3">SPMR Suraksha</h1>
    <p class="lead mb-4">Security Application for Govt. SPMR College</p>
    <?php include "lib/login.php"; ?>
  </div>

  <section class="dashboard-hero p-4 p-md-5 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
      <div>
        <span class="dashboard-pill mb-3">Security Operations Dashboard</span>
        <h2 class="h3 fw-bold mb-2">Live Campus Monitoring</h2>
        <p class="mb-0 text-secondary">Today, <?php echo htmlspecialchars(date('d M Y'), ENT_QUOTES); ?>. Attendance and visitor events refresh from gate logs.</p>
      </div>
      <div class="text-md-end">
        <div class="small text-uppercase fw-semibold text-secondary">Shift Window</div>
        <div class="h5 mb-0">08:00 AM - 04:00 PM</div>
      </div>
    </div>
  </section>

  <section class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <article class="stat-card p-3 p-md-4 h-100">
        <div class="text-secondary small text-uppercase fw-semibold">Total Marked</div>
        <div class="stat-number mt-2"><?php echo (int) $attendance['total']; ?></div>
      </article>
    </div>
    <div class="col-6 col-lg-3">
      <article class="stat-card p-3 p-md-4 h-100">
        <div class="text-secondary small text-uppercase fw-semibold">In Time</div>
        <div class="stat-number mt-2 text-success"><?php echo (int) $attendance['in_time']; ?></div>
      </article>
    </div>
    <div class="col-6 col-lg-3">
      <article class="stat-card p-3 p-md-4 h-100">
        <div class="text-secondary small text-uppercase fw-semibold">Late</div>
        <div class="stat-number mt-2" style="color:#8a5a00;"><?php echo (int) $attendance['late']; ?></div>
      </article>
    </div>
    <div class="col-6 col-lg-3">
      <article class="stat-card p-3 p-md-4 h-100">
        <div class="text-secondary small text-uppercase fw-semibold">Absent</div>
        <div class="stat-number mt-2 text-danger"><?php echo (int) $attendance['absent']; ?></div>
      </article>
    </div>
  </section>

  <section class="row g-3 mb-4">
    <div class="col-lg-8">
      <article class="section-card p-3 p-md-4 h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="h5 mb-0">Weekly Attendance Trend</h3>
          <span class="small text-secondary">Last 7 days</span>
        </div>
        <svg viewBox="0 0 420 210" class="w-100" role="img" aria-label="Weekly attendance line chart">
          <rect x="0" y="0" width="420" height="210" fill="#f7faf8"></rect>
          <g stroke="#d8e5dc" stroke-width="1">
            <line x1="20" y1="180" x2="400" y2="180"></line>
            <line x1="20" y1="135" x2="400" y2="135"></line>
            <line x1="20" y1="90" x2="400" y2="90"></line>
            <line x1="20" y1="45" x2="400" y2="45"></line>
          </g>
          <polyline fill="none" stroke="#0f9d58" stroke-width="4" points="<?php echo htmlspecialchars($polylinePoints, ENT_QUOTES); ?>"></polyline>
          <?php foreach ($points as $point): ?>
            <?php $xy = explode(',', $point); ?>
            <circle cx="<?php echo (int) $xy[0]; ?>" cy="<?php echo (int) $xy[1]; ?>" r="4" fill="#0f9d58"></circle>
          <?php endforeach; ?>
        </svg>
      </article>
    </div>

    <div class="col-lg-4">
      <article class="section-card p-3 p-md-4 h-100">
        <h3 class="h5 mb-3">Security Alerts</h3>
        <div class="d-grid gap-2">
          <?php foreach ($alerts as $alert): ?>
            <?php
              $level = (string) $alert['level'];
              $pillClass = 'badge-info-soft';
              if ($level === 'High') {
                $pillClass = 'badge-danger-soft';
              } elseif ($level === 'Medium') {
                $pillClass = 'badge-warning-soft';
              }
            ?>
            <div class="border rounded-3 p-3">
              <span class="badge <?php echo htmlspecialchars($pillClass, ENT_QUOTES); ?>"><?php echo htmlspecialchars($level, ENT_QUOTES); ?></span>
              <p class="mb-0 mt-2 small"><?php echo htmlspecialchars((string) $alert['message'], ENT_QUOTES); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </article>
    </div>
  </section>

  <section class="section-card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="h5 mb-0">Today's Visitors</h3>
      <span class="small text-secondary"><?php echo count($todaysVisitors); ?> visitors logged</span>
    </div>
    <div class="table-responsive">
      <table class="table visitor-table align-middle mb-0">
        <thead>
          <tr>
            <th scope="col">Visitor</th>
            <th scope="col">User ID</th>
            <th scope="col">Check-in Time</th>
            <th scope="col">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($todaysVisitors as $visitor): ?>
            <tr>
              <td><?php echo htmlspecialchars((string) $visitor['name'], ENT_QUOTES); ?></td>
              <td><span class="mono"><?php echo htmlspecialchars((string) $visitor['userId'], ENT_QUOTES); ?></span></td>
              <td><?php echo htmlspecialchars((string) $visitor['time'], ENT_QUOTES); ?></td>
              <td>
                <?php $isEscort = ((string) $visitor['status']) === 'Awaiting Escort'; ?>
                <span class="badge <?php echo $isEscort ? 'badge-warning-soft' : 'badge-info-soft'; ?>">
                  <?php echo htmlspecialchars((string) $visitor['status'], ENT_QUOTES); ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

</div>
