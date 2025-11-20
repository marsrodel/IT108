<?php
include '../server/db.php';

// Patients pagination setup (50 per page)
$per_page = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }

$count_sql = "SELECT COUNT(*) AS c FROM patient";
$count_res = mysqli_query($conn, $count_sql);
$total_rows = 0;
if ($count_res) {
  $row = mysqli_fetch_assoc($count_res);
  $total_rows = (int)$row['c'];
}
$total_pages = $total_rows > 0 ? (int)ceil($total_rows / $per_page) : 1;
if ($page > $total_pages) { $page = $total_pages; }

$offset = ($page - 1) * $per_page;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>COVID Analytics - Patients</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/patients.css">
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
        <a class="nav-link" href="cases.php">Cases</a>
        <a class="nav-link active" href="patients.php">Patients</a>
      </div>
    </nav>
  </header>

  <main class="container layout">
    <section class="section">
      <div class="hero">
        <div class="hero-content">
          <div class="hero-text">
            <div class="badge">Browse • Patients</div>
            <h1 class="hero-title">Demographics and locations of patients</h1>
            <p class="hero-sub">Browse patient demographics and locations with pagination.</p>
            <div class="hero-actions">
              <a href="index.php" class="button light">Back to Dashboard</a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="section">
      <h1 class="page-title">Patients</h1>
      <p class="subtitle">Basic info with location (50 per page)</p>
      <div class="card">
        <div class="card-body">
          <div class="patients-grid">
            <div class="patients-grid-header">
              <div>Patient</div>
              <div>Gender</div>
              <div>Age</div>
              <div>City</div>
              <div>Region</div>
              <div>Country</div>
            </div>
            <div class="patients-grid-scroll">
<?php
$sql = "SELECT p.first_name, p.last_name, p.gender, p.age,
               l.city, l.region, l.country
        FROM patient p
        LEFT JOIN location l ON p.location_id = l.location_id
        ORDER BY p.patient_id ASC
        LIMIT {$per_page} OFFSET {$offset}";
$res = mysqli_query($conn, $sql);
if ($res) {
  while ($r = mysqli_fetch_assoc($res)) {
    $name    = htmlspecialchars($r['first_name'].' '.$r['last_name']);
    $gender  = htmlspecialchars($r['gender']);
    $age     = htmlspecialchars($r['age']);
    $city    = htmlspecialchars($r['city']);
    $region  = htmlspecialchars($r['region']);
    $country = htmlspecialchars($r['country']);
?>
              <div class="patients-grid-row">
                <div><?php echo $name; ?></div>
                <div><?php echo $gender; ?></div>
                <div><?php echo $age; ?></div>
                <div><?php echo $city; ?></div>
                <div><?php echo $region; ?></div>
                <div><?php echo $country; ?></div>
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

  if (!function_exists('patients_page_link')) {
    function patients_page_link($p, $label, $disabled = false, $active = false, $base_url = '') {
      if ($disabled) {
        echo "<span class='pill' style='opacity:0.5;cursor:default;'>".htmlspecialchars($label)."</span>";
        return;
      }
      $class = 'pill';
      if ($active) { $class .= ' danger'; }
      $url = $base_url.'?page='.$p;
      echo "<a href='".htmlspecialchars($url)."' class='".$class."'>".htmlspecialchars($label)."</a>";
    }
  }

  // Previous
  patients_page_link(max(1, $page - 1), 'Previous', $page <= 1, false, $base_url);

  $max_links = 10;
  $start = max(1, $page - 4);
  $end = min($total_pages, $start + $max_links - 1);
  if ($end - $start + 1 < $max_links) {
    $start = max(1, $end - $max_links + 1);
  }

  if ($start > 1) {
    patients_page_link(1, '1', false, $page == 1, $base_url);
    if ($start > 2) {
      echo "<span style='padding:0 4px;'>...</span>";
    }
  }

  for ($i = $start; $i <= $end; $i++) {
    if ($i == 1 || $i == $total_pages) { continue; }
    patients_page_link($i, (string)$i, false, $i == $page, $base_url);
  }

  if ($end < $total_pages) {
    if ($end < $total_pages - 1) {
      echo "<span style='padding:0 4px;'>...</span>";
    }
    patients_page_link($total_pages, (string)$total_pages, false, $page == $total_pages, $base_url);
  }

  // Next
  patients_page_link(min($total_pages, $page + 1), 'Next', $page >= $total_pages, false, $base_url);
?>
          </div>
<?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">&copy; 2025 COVID Analytics. For academic use.</footer>
  <script src="../js/app.js"></script>
  <script src="../js/patients.js"></script>
</body>
</html>
