<?php
include '../server/db.php';
// Simple aggregates
$total_cases = 0;
$positive_cases = 0;
$severe_critical = 0;
$vaccinated = 0;

$q1 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM covid_cases");
if ($q1) { $row = mysqli_fetch_assoc($q1); $total_cases = (int)$row['c']; }
$q2 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM covid_cases WHERE result='Positive'");
if ($q2) { $row = mysqli_fetch_assoc($q2); $positive_cases = (int)$row['c']; }

// Focus severity and vaccination on Positive cases for meaningful ratios
$q3 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM covid_cases WHERE result='Positive' AND severity IN ('Severe','Critical')");
if ($q3) { $row = mysqli_fetch_assoc($q3); $severe_critical = (int)$row['c']; }
$q4 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM covid_cases WHERE vaccine_id IS NOT NULL");
if ($q4) { $row = mysqli_fetch_assoc($q4); $vaccinated = (int)$row['c']; }

$positivity_pct = $total_cases > 0 ? (($positive_cases / $total_cases) * 100) : 0;
$sevcrit_pct = $positive_cases > 0 ? round(($severe_critical / $positive_cases) * 100) : 0;
$vac_pct = $total_cases > 0 ? round(($vaccinated / $total_cases) * 100) : 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>COVID Analytics - Dashboard</title>
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
        <a class="nav-link active" href="index.php">Dashboard</a>
        <a class="nav-link" href="cases.php">Cases</a>
        <a class="nav-link" href="patients.php">Patients</a>
      </div>
    </nav>
  </header>

  <main class="container layout">
    <section class="section">
      <div class="hero">
        <div class="hero-content">
          <div class="hero-text">
            <div class="badge">Health • COVID-19 Analytics</div>
            <h1 class="hero-title">Track COVID-19 cases, severity, and vaccinations</h1>
            <p class="hero-sub">Analytical views based on records from the MySQL database</p>
            <div class="hero-actions">
              <a href="/Covid/views/cases.php" class="button">View Cases</a>
              <a href="/Covid/views/patients.php" class="button light">Explore Patients</a>
              <button type="button" class="button ghost" id="seeGrowthRateBtn">See Growth Rate</button>
            </div>
          </div>
        </div>
      </div>
    </section>

  

    <section class="section">
      <h1 class="page-title">Dashboard</h1>
      <p class="subtitle">Overview of COVID-19 testing and outcomes</p>
      <div class="grid cols-4">
        <div class="card"><div class="card-body kpi"><div><div class="label">Total Results</div><div class="value" style="color:var(--accent)"><?php echo number_format($total_cases); ?></div></div><span class="pill moderate">All Time</span></div></div>
        <div class="card"><div class="card-body kpi"><div><div class="label">Positive</div><div class="value" style="color:var(--danger)"><?php echo number_format($positivity_pct, 2); ?>%</div></div><span class="pill danger">Positivity</span></div></div>
        <div class="card"><div class="card-body kpi"><div><div class="label">Severe/Critical</div><div class="value" style="color:var(--warning)"><?php echo $sevcrit_pct; ?>%</div></div><span class="pill warning">Severity</span></div></div>
        <div class="card"><div class="card-body kpi"><div><div class="label">Vaccinated</div><div class="value" style="color:var(--success)"><?php echo $vac_pct; ?>%</div></div><span class="pill success">Coverage</span></div></div>
      </div>
    </section>

    <section class="section">
      <div class="grid cols-2">
        <div class="card">
          <div class="card-body">
            <h2 class="section-title">Cases Over Time (Positive)</h2>
            <div class="field-row" style="display:flex;align-items:center;gap:8px;margin:8px 0 12px">
              <label for="yearFilter" class="label">Year:</label>
              <select id="yearFilter" class="status-select">
                <option value="all">All</option>
                <option value="2020">2020</option>
                <option value="2021">2021</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
              </select>
            </div>
            <div class="chart-placeholder" style="height:320px">
              <canvas id="casesOverTime"></canvas>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <h2 class="section-title">Severity Distribution</h2>
            <div class="field-row" style="display:flex;align-items:center;gap:8px;margin:8px 0 12px">
              <label for="statusFilter" class="label">Status:</label>
              <select id="statusFilter" class="status-select">
                <option value="all">All</option>
                <option value="positive">Positive</option>
                <option value="negative">Negative</option>
              </select>
            </div>
            <div class="chart-placeholder" style="height:320px">
              <canvas id="severityDist"></canvas>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <h1 class="page-title">More Analytics</h1>
      <p class="subtitle">Quick access to supporting datasets</p>
      <div class="grid cols-3">
        <a href="#" class="card labs-card" style="text-decoration:none;">
          <div class="card-body">
            <h2 class="section-title">Testing Labs</h2>
            <p class="subtitle">Laboratory capacity and details</p>
          </div>
        </a>
        <a href="#" class="card vaccines-card" style="text-decoration:none;">
          <div class="card-body">
            <h2 class="section-title">Vaccines</h2>
            <p class="subtitle">Vaccine inventory and usage</p>
          </div>
        </a>
        <a href="#" class="card locations-card" style="text-decoration:none;">
          <div class="card-body">
            <h2 class="section-title">Covered Locations</h2>
            <p class="subtitle">Cities and regions in this dataset</p>
          </div>
        </a>
      </div>
    </section>
<?php
  // Simple labs data for dashboard table
  $labs_res = mysqli_query($conn, "SELECT lab_name, city, capacity_per_day FROM testing_lab ORDER BY lab_id ASC LIMIT 50");

  // Simple vaccines data for dashboard table
  $vaccines_res = mysqli_query($conn, "SELECT * FROM vaccine ORDER BY vaccine_id ASC LIMIT 50");

  // Simple locations data for dashboard table
  $locations_res = mysqli_query($conn, "SELECT city, region, country FROM location ORDER BY location_id ASC LIMIT 50");
?>

    <div class="section" id="vaccines-section">
      <h1 class="page-title">Vaccines</h1>
      <div class="card">
        <div class="card-body">
          <table class="table vaccines-table">
            <colgroup>
              <col style="width:33.3%"><!-- Vaccine -->
              <col style="width:33.3%"><!-- Manufacturer (if available) -->
              <col style="width:33.3%"><!-- Doses Required -->
            </colgroup>
            <thead>
              <tr>
                <th>Vaccine</th>
                <th>Manufacturer</th>
                <th>Doses Required</th>
              </tr>
            </thead>
            <tbody>
<?php
  if ($vaccines_res) {
    while ($v = mysqli_fetch_assoc($vaccines_res)) {
      $name = isset($v['vaccine_name']) ? htmlspecialchars($v['vaccine_name']) : '';
      $mfr  = isset($v['manufacturer']) ? htmlspecialchars($v['manufacturer']) : '';
      $doses = isset($v['doses_required']) ? htmlspecialchars($v['doses_required']) : '';
      echo "              <tr>";
      echo "<td>{$name}</td>";
      echo "<td>{$mfr}</td>";
      echo "<td>{$doses}</td>";
      echo "</tr>\n";
    }
  }
?>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="labs-section">
      <h1 class="page-title">Testing Labs</h1>
      <div class="card">
        <div class="card-body">
          <table class="table labs-table">
            <colgroup>
              <col style="width:33.3%"><!-- Lab -->
              <col style="width:33.3%"><!-- City -->
              <col style="width:33.3%"><!-- Capacity/Day -->
            </colgroup>
            <thead>
              <tr>
                <th>Lab</th>
                <th>City</th>
                <th>Capacity/Day</th>
              </tr>
            </thead>
            <tbody>
<?php
  if ($labs_res) {
    while ($lab = mysqli_fetch_assoc($labs_res)) {
      $lab_name = htmlspecialchars($lab['lab_name']);
      $city     = htmlspecialchars($lab['city']);
      $cap      = htmlspecialchars($lab['capacity_per_day']);
      echo "              <tr>";
      echo "<td>{$lab_name}</td>";
      echo "<td>{$city}</td>";
      echo "<td>{$cap}</td>";
      echo "</tr>\n";
    }
  }
?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

 <section class="section" id="locations-section">
      <h1 class="page-title">Covered Locations</h1>
      <div class="card">
        <div class="card-body">
          <div class="locations-table-scroll">
          <table class="table locations-table">
            <colgroup>
              <col style="width:33.3%"><!-- City -->
              <col style="width:33.3%"><!-- Region -->
              <col style="width:33.3%"><!-- Country -->
            </colgroup>
            <thead>
              <tr>
                <th>City</th>
                <th>Region</th>
                <th>Country</th>
              </tr>
            </thead>
            <tbody>
<?php
  if ($locations_res) {
    while ($loc = mysqli_fetch_assoc($locations_res)) {
      $city    = htmlspecialchars($loc['city']);
      $region  = htmlspecialchars($loc['region']);
      $country = htmlspecialchars($loc['country']);
      echo "              <tr>";
      echo "<td>{$city}</td>";
      echo "<td>{$region}</td>";
      echo "<td>{$country}</td>";
      echo "</tr>\n";
    }
  }
?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Growth Rate modal (Yearly & Monthly) -->
    <div id="growthModal" class="modal-growth">
      <div class="modal-growth-content">
        <div class="modal-growth-header">
          <h2 class="modal-growth-title">Growth Rate of Positive Cases</h2>
          <button type="button" class="modal-growth-close" aria-label="Close growth rate">×</button>
        </div>
        <div class="modal-growth-body">
          <p class="subtitle">Year-over-year and month-to-month growth of positive COVID-19 cases.</p>
          <div class="growth-section">
            <h3 class="growth-subtitle">Yearly Growth (Positive Cases)</h3>
            <div class="growth-table-scroll">
              <table class="growth-table growth-table-yearly">
                <thead>
                  <tr>
                    <th>Year</th>
                    <th>Total Positive Cases</th>
                    <th>Previous Year</th>
                    <th>Growth Rate</th>
                  </tr>
                </thead>
                <tbody id="growthYearlyBody">
                  <!-- Filled via JS -->
                </tbody>
              </table>
            </div>
          </div>
          <div class="growth-section">
            <h3 class="growth-subtitle">Monthly Growth (Positive Cases)</h3>
            <div class="growth-table-scroll">
              <table class="growth-table growth-table-monthly">
                <thead>
                  <tr>
                    <th>Month</th>
                    <th>Total Positive Cases</th>
                    <th>Previous Month</th>
                    <th>Growth Rate</th>
                  </tr>
                </thead>
                <tbody id="growthMonthlyBody">
                  <!-- Filled via JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer"> 2025 COVID Analytics. For academic use.</footer>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="../js/app.js"></script>
  <script src="../js/dashboard.js"></script>
</body>
</html>
