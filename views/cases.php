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
  // Pagination setup
  $per_page = 50;
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($page < 1) { $page = 1; }

  // Count total rows
  $count_sql = "SELECT COUNT(*) AS c FROM covid_cases";
  $count_res = mysqli_query($conn, $count_sql);
  $total_rows = 0;
  if ($count_res) {
    $row = mysqli_fetch_assoc($count_res);
    $total_rows = (int)$row['c'];
  }
  $total_pages = $total_rows > 0 ? (int)ceil($total_rows / $per_page) : 1;
  if ($page > $total_pages) { $page = $total_pages; }

  $offset = ($page - 1) * $per_page;

  // Main query: cases with vaccine and lab names
  $sql = "SELECT c.case_id, c.test_date, c.patient_id, c.result, c.severity,
                 v.vaccine_name, t.lab_name
          FROM covid_cases c
          LEFT JOIN vaccine v ON c.vaccine_id = v.vaccine_id
          LEFT JOIN testing_lab t ON c.lab_id = t.lab_id
          ORDER BY c.test_date DESC, c.case_id DESC
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

  function page_link($p, $label, $disabled = false, $active = false, $base_url = '') {
    if ($disabled) {
      echo "<span class='pill' style='opacity:0.5;cursor:default;'>".htmlspecialchars($label)."</span>";
      return;
    }
    $class = 'pill';
    if ($active) { $class .= ' danger'; }
    $url = $base_url.'?page='.$p;
    echo "<a href='".htmlspecialchars($url)."' class='".$class."'>".htmlspecialchars($label)."</a>";
  }

  // Previous
  page_link(max(1, $page - 1), 'Previous', $page <= 1, false, $base_url);

  $max_links = 10;
  $start = max(1, $page - 4);
  $end = min($total_pages, $start + $max_links - 1);
  if ($end - $start + 1 < $max_links) {
    $start = max(1, $end - $max_links + 1);
  }

  if ($start > 1) {
    page_link(1, '1', false, $page == 1, $base_url);
    if ($start > 2) {
      echo "<span style='padding:0 4px;'>...</span>";
    }
  }

  for ($i = $start; $i <= $end; $i++) {
    if ($i == 1 || $i == $total_pages) { continue; }
    page_link($i, (string)$i, false, $i == $page, $base_url);
  }

  if ($end < $total_pages) {
    if ($end < $total_pages - 1) {
      echo "<span style='padding:0 4px;'>...</span>";
    }
    page_link($total_pages, (string)$total_pages, false, $page == $total_pages, $base_url);
  }

  // Next
  page_link(min($total_pages, $page + 1), 'Next', $page >= $total_pages, false, $base_url);
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
