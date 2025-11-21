<?php
include '../server/db.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>COVID Analytics - Cases</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <header class="app-header">
    <nav class="nav container">
      <div class="brand">
        <img src="../assets/covid_icon.png" alt="COVID Analytics" class="brand-badge" />
        <div class="brand-title">COVID Analytics</div>
      </div>
      <button class="nav-toggle" aria-label="Toggle navigation" onclick="toggleNav()">Menu</button>
      <div class="nav-links" id="navLinks">
        <a class="nav-link" href="index.php">Dashboard</a>
        <a class="nav-link active" href="cases.php">Cases</a>
        <a class="nav-link" href="patients.php">Patients</a>
      </div>
    </nav>
  </header>

  <main class="container layout">
    <section class="section">
      <div class="hero">
        <div class="hero-content">
          <div class="hero-text">
            <div class="badge">Browse • COVID-19 Cases</div>
            <h1 class="hero-title">Explore case results and severity trends</h1>
            <p class="hero-sub">Paginated records from the covid_cases table.</p>
            <div class="hero-actions">
              <a href="index.php" class="button light">Back to Dashboard</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <h1 class="page-title">Cases</h1>
      <p class="subtitle">Paginated results from covid_cases (50 per page)</p>
<?php
  // Result filter setup
  $allowed_results = ['all', 'Positive', 'Negative'];
  $result_filter = isset($_GET['result']) ? $_GET['result'] : 'all';
  if (!in_array($result_filter, $allowed_results, true)) {
    $result_filter = 'all';
  }

  // Severity filter setup
  $allowed_severity = ['all', 'Mild', 'Moderate', 'Severe', 'Critical'];
  $severity_filter = isset($_GET['severity']) ? $_GET['severity'] : 'all';
  if (!in_array($severity_filter, $allowed_severity, true)) {
    $severity_filter = 'all';
  }

  // Vaccine filter setup (by vaccine_id)
  $vaccine_options = [];
  $vaccines_res = mysqli_query($conn, "SELECT vaccine_id, vaccine_name FROM vaccine ORDER BY vaccine_name ASC");
  if ($vaccines_res) {
    while ($vrow = mysqli_fetch_assoc($vaccines_res)) {
      $vid = (int)$vrow['vaccine_id'];
      $vname = $vrow['vaccine_name'] !== null ? $vrow['vaccine_name'] : '';
      $vaccine_options[$vid] = $vname;
    }
  }

  $vaccine_filter = isset($_GET['vaccine']) ? $_GET['vaccine'] : 'all';
  if ($vaccine_filter !== 'all') {
    $vaccine_filter = (int)$vaccine_filter;
    if (!array_key_exists($vaccine_filter, $vaccine_options)) {
      $vaccine_filter = 'all';
    }
  }

  // Lab filter setup (by lab_id)
  $lab_options = [];
  $labs_res = mysqli_query($conn, "SELECT lab_id, lab_name FROM testing_lab ORDER BY lab_name ASC");
  if ($labs_res) {
    while ($lrow = mysqli_fetch_assoc($labs_res)) {
      $lid = (int)$lrow['lab_id'];
      $lname = $lrow['lab_name'] !== null ? $lrow['lab_name'] : '';
      $lab_options[$lid] = $lname;
    }
  }

  $lab_filter = isset($_GET['lab']) ? $_GET['lab'] : 'all';
  if ($lab_filter !== 'all') {
    $lab_filter = (int)$lab_filter;
    if (!array_key_exists($lab_filter, $lab_options)) {
      $lab_filter = 'all';
    }
  }

  // Year filter setup
  $allowed_years = ['all', '2020', '2021', '2022', '2023', '2024', '2025'];
  $year_filter = isset($_GET['year']) ? $_GET['year'] : 'all';
  if (!in_array($year_filter, $allowed_years, true)) {
    $year_filter = 'all';
  }
?>
      <div class="toolbar" style="margin:8px 0 14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <form method="get" id="casesFilterForm" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <div class="field" style="min-width:160px;">
            <label for="resultFilter" class="label">Result</label>
            <select id="resultFilter" name="result" class="status-select">
              <option value="all" <?php echo $result_filter === 'all' ? 'selected' : ''; ?>>All</option>
              <option value="Positive" <?php echo $result_filter === 'Positive' ? 'selected' : ''; ?>>Positive</option>
              <option value="Negative" <?php echo $result_filter === 'Negative' ? 'selected' : ''; ?>>Negative</option>
            </select>
          </div>
          <div class="field" style="min-width:160px;">
            <label for="severityFilter" class="label">Severity</label>
            <select id="severityFilter" name="severity" class="status-select">
              <option value="all" <?php echo $severity_filter === 'all' ? 'selected' : ''; ?>>All</option>
              <option value="Mild" <?php echo $severity_filter === 'Mild' ? 'selected' : ''; ?>>Mild</option>
              <option value="Moderate" <?php echo $severity_filter === 'Moderate' ? 'selected' : ''; ?>>Moderate</option>
              <option value="Severe" <?php echo $severity_filter === 'Severe' ? 'selected' : ''; ?>>Severe</option>
              <option value="Critical" <?php echo $severity_filter === 'Critical' ? 'selected' : ''; ?>>Critical</option>
            </select>
          </div>
          <div class="field" style="min-width:180px;">
            <label for="vaccineFilter" class="label">Vaccine</label>
            <select id="vaccineFilter" name="vaccine" class="status-select">
              <option value="all" <?php echo $vaccine_filter === 'all' ? 'selected' : ''; ?>>All</option>
<?php foreach ($vaccine_options as $vid => $vname): ?>
              <option value="<?php echo (int)$vid; ?>" <?php echo ($vaccine_filter !== 'all' && (int)$vaccine_filter === (int)$vid) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($vname); ?>
              </option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="field" style="min-width:140px;">
            <label for="yearFilter" class="label">Year</label>
            <select id="yearFilter" name="year" class="status-select">
              <option value="all" <?php echo $year_filter === 'all' ? 'selected' : ''; ?>>All</option>
              <option value="2020" <?php echo $year_filter === '2020' ? 'selected' : ''; ?>>2020</option>
              <option value="2021" <?php echo $year_filter === '2021' ? 'selected' : ''; ?>>2021</option>
              <option value="2022" <?php echo $year_filter === '2022' ? 'selected' : ''; ?>>2022</option>
              <option value="2023" <?php echo $year_filter === '2023' ? 'selected' : ''; ?>>2023</option>
              <option value="2024" <?php echo $year_filter === '2024' ? 'selected' : ''; ?>>2024</option>
              <option value="2025" <?php echo $year_filter === '2025' ? 'selected' : ''; ?>>2025</option>
            </select>
          </div>
          <div class="field" style="min-width:180px;">
            <label for="labFilter" class="label">Lab</label>
            <select id="labFilter" name="lab" class="status-select">
              <option value="all" <?php echo $lab_filter === 'all' ? 'selected' : ''; ?>>All</option>
<?php foreach ($lab_options as $lid => $lname): ?>
              <option value="<?php echo (int)$lid; ?>" <?php echo ($lab_filter !== 'all' && (int)$lab_filter === (int)$lid) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($lname); ?>
              </option>
<?php endforeach; ?>
            </select>
          </div>
          <?php if (isset($_GET['page']) && (int)$_GET['page'] > 1): ?>
            <input type="hidden" name="page" value="<?php echo (int)$_GET['page']; ?>">
          <?php endif; ?>
        </form>
      </div>
<?php
  // Pagination setup
  $per_page = 50;
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($page < 1) { $page = 1; }

  // Build WHERE clause based on filters
  $whereParts = [];
  if ($result_filter === 'Positive') {
    $whereParts[] = "result = 'Positive'";
  } elseif ($result_filter === 'Negative') {
    $whereParts[] = "result = 'Negative'";
  }

  if ($severity_filter !== 'all') {
    $whereParts[] = "severity = '" . mysqli_real_escape_string($conn, $severity_filter) . "'";
  }

  if ($vaccine_filter !== 'all') {
    $whereParts[] = 'vaccine_id = ' . (int)$vaccine_filter;
  }

  if ($lab_filter !== 'all') {
    $whereParts[] = 'lab_id = ' . (int)$lab_filter;
  }

  if ($year_filter !== 'all') {
    $whereParts[] = 'YEAR(test_date) = ' . (int)$year_filter;
  }

  $where = '';
  if (!empty($whereParts)) {
    $where = 'WHERE ' . implode(' AND ', $whereParts);
  }

  // Count total rows with filter
  $count_sql = "SELECT COUNT(*) AS c FROM covid_cases {$where}";
  $count_res = mysqli_query($conn, $count_sql);
  $total_rows = 0;
  if ($count_res) {
    $row = mysqli_fetch_assoc($count_res);
    $total_rows = (int)$row['c'];
  }
  $total_pages = $total_rows > 0 ? (int)ceil($total_rows / $per_page) : 1;
  if ($page > $total_pages) { $page = $total_pages; }

  $offset = ($page - 1) * $per_page;

  // Main query: cases with vaccine and lab names, with optional filters
  $sql = "SELECT c.case_id, c.test_date, c.patient_id, c.result, c.severity,
          v.vaccine_name, t.lab_name
          FROM covid_cases c
          LEFT JOIN vaccine v ON c.vaccine_id = v.vaccine_id
          LEFT JOIN testing_lab t ON c.lab_id = t.lab_id";
  if (!empty($whereParts)) {
    // Reuse same conditions but prefix with table alias
    $aliased = [];
    foreach ($whereParts as $cond) {
      // replace column names with c. prefix where appropriate
      $aliased[] = str_replace(
        ['result =',        'severity =',      'vaccine_id =',      'lab_id =',        'YEAR(test_date)'],
        ['c.result =', 'c.severity =', 'c.vaccine_id =', 'c.lab_id =', 'YEAR(c.test_date)'],
        $cond
      );
    }
    $sql .= ' WHERE ' . implode(' AND ', $aliased);
  }
  $sql .= " ORDER BY c.test_date DESC, c.case_id DESC
          LIMIT {$per_page} OFFSET {$offset}";
  $res = mysqli_query($conn, $sql);
?>
      <div class="card">
        <div class="card-body">
          <div class="cases-grid">
            <div class="cases-grid-header">
              <div>Date</div>
              <div>Patient ID</div>
              <div>Result</div>
              <div>Severity</div>
              <div>Vaccine</div>
              <div>Lab</div>
            </div>
            <div class="cases-grid-scroll">
<?php
if ($res) {
  while ($r = mysqli_fetch_assoc($res)) {
    $date       = htmlspecialchars($r['test_date']);
    $patient_id = htmlspecialchars($r['patient_id']);
    $result     = htmlspecialchars($r['result']);
    $severity   = htmlspecialchars($r['severity']);
    $vaccine    = htmlspecialchars($r['vaccine_name']);
    $lab        = htmlspecialchars($r['lab_name']);
?>
              <div class="cases-grid-row">
                <div><?php echo $date; ?></div>
                <div><?php echo $patient_id; ?></div>
                <div>
<?php if ($result === 'Positive') { ?>
                  <span class="pill danger"><?php echo $result; ?></span>
<?php } elseif ($result === 'Negative') { ?>
                  <span class="pill negative"><?php echo $result; ?></span>
<?php } else { ?>
                  <span class="pill"><?php echo $result; ?></span>
<?php } ?>
                </div>
                <div>
<?php if ($severity === 'Mild') { ?>
                  <span class="pill mild"><?php echo $severity; ?></span>
<?php } elseif ($severity === 'Moderate') { ?>
                  <span class="pill moderate"><?php echo $severity; ?></span>
<?php } elseif ($severity === 'Severe') { ?>
                  <span class="pill severe"><?php echo $severity; ?></span>
<?php } elseif ($severity === 'Critical') { ?>
                  <span class="pill critical"><?php echo $severity; ?></span>
<?php } else { ?>
                  <span class="pill"><?php echo $severity; ?></span>
<?php } ?>
                </div>
                <div><?php echo $vaccine; ?></div>
                <div><?php echo $lab; ?></div>
              </div>
<?php
  }
}
?>
            </div>
          </div>

<?php if ($total_pages > 1): ?>
          <div class="pagination" style="margin-top:16px;display:flex;justify-content:center;gap:8px;align-items:center;flex-wrap:wrap;">
<?php
  $base_url = strtok($_SERVER['REQUEST_URI'], '?');

  // Keep current filters in pagination links
  $query_parts = [];
  if ($result_filter !== 'all') {
    $query_parts[] = 'result='.urlencode($result_filter);
  }
  if ($severity_filter !== 'all') {
    $query_parts[] = 'severity='.urlencode($severity_filter);
  }
  if ($vaccine_filter !== 'all') {
    $query_parts[] = 'vaccine='.(int)$vaccine_filter;
  }
  if ($lab_filter !== 'all') {
    $query_parts[] = 'lab='.(int)$lab_filter;
  }
  if ($year_filter !== 'all') {
    $query_parts[] = 'year='.(int)$year_filter;
  }
  $query_base = implode('&', $query_parts);

  function page_link($p, $label, $disabled = false, $active = false, $base_url = '', $query_base = '') {
    if ($disabled) {
      echo "<span class='pill' style='opacity:0.5;cursor:default;'>".htmlspecialchars($label)."</span>";
      return;
    }
    $class = 'pill';
    if ($active) { $class .= ' danger'; }
    $params = 'page='.$p;
    if ($query_base !== '') {
      $params .= '&'.$query_base;
    }
    $url = $base_url.'?'.$params;
    echo "<a href='".htmlspecialchars($url)."' class='".$class."'>".htmlspecialchars($label)."</a>";
  }

  // Previous
  page_link(max(1, $page - 1), 'Previous', $page <= 1, false, $base_url, $query_base);

  $max_links = 10;
  $start = max(1, $page - 4);
  $end = min($total_pages, $start + $max_links - 1);
  if ($end - $start + 1 < $max_links) {
    $start = max(1, $end - $max_links + 1);
  }

  if ($start > 1) {
    page_link(1, '1', false, $page == 1, $base_url, $query_base);
    if ($start > 2) {
      echo "<span style='padding:0 4px;'>...</span>";
    }
  }

  for ($i = $start; $i <= $end; $i++) {
    if ($i == 1 || $i == $total_pages) { continue; }
    page_link($i, (string)$i, false, $i == $page, $base_url, $query_base);
  }

  if ($end < $total_pages) {
    if ($end < $total_pages - 1) {
      echo "<span style='padding:0 4px;'>...</span>";
    }
    page_link($total_pages, (string)$total_pages, false, $page == $total_pages, $base_url, $query_base);
  }

  // Next
  page_link(min($total_pages, $page + 1), 'Next', $page >= $total_pages, false, $base_url, $query_base);
?>
          </div>
<?php endif; ?>

        </div>
      </div>
    </section>
  </main>

  <footer class="footer"> 2025 COVID Analytics. For academic use.</footer>
  <script src="../js/app.js"></script>
  <script src="../js/cases.js"></script>
</body>
</html>
