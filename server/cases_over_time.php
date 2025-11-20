<?php
header('Content-Type: application/json');
include 'db.php';
$rows = array();
// Optional year filter: when provided, return monthly totals for that year.
// Otherwise, return yearly aggregates.
$year = isset($_GET['year']) ? trim($_GET['year']) : 'all';

if ($year !== '' && strtolower($year) !== 'all') {
  $y = (int)$year;
  $sql = "SELECT DATE_FORMAT(test_date,'%b') AS m, COUNT(*) AS c
          FROM covid_cases
          WHERE YEAR(test_date) = {$y} AND result = 'positive'
          GROUP BY YEAR(test_date), MONTH(test_date)
          ORDER BY YEAR(test_date), MONTH(test_date)";
  $res = mysqli_query($conn, $sql);
  if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
      $rows[] = array('label' => $r['m'], 'count' => (int)$r['c']);
    }
  }
} else {
  // Aggregate cases by calendar year for the high-level dashboard view
  $sql = "SELECT YEAR(test_date) AS y, COUNT(*) AS c
          FROM covid_cases
          WHERE result = 'positive'
          GROUP BY YEAR(test_date)
          ORDER BY YEAR(test_date)";
  $res = mysqli_query($conn, $sql);
  if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
      $rows[] = array('label' => (string)$r['y'], 'count' => (int)$r['c']);
    }
  }
}
echo json_encode($rows);
?>
