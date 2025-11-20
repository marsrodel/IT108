<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
include 'db.php';

// Optional status filter: positive, negative, or all
$status = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : 'all';
$where = '';
if ($status === 'positive') {
  $where = " WHERE UPPER(TRIM(result))='POSITIVE'";
} elseif ($status === 'negative') {
  $where = " WHERE UPPER(TRIM(result))='NEGATIVE'";
}

// Pivot-style aggregation across one row for accuracy and stability
$sql = "SELECT 
  SUM(CASE WHEN UPPER(TRIM(severity)) = 'MILD' THEN 1 ELSE 0 END) AS mild,
  SUM(CASE WHEN UPPER(TRIM(severity)) = 'MODERATE' THEN 1 ELSE 0 END) AS moderate,
  SUM(CASE WHEN UPPER(TRIM(severity)) = 'SEVERE' THEN 1 ELSE 0 END) AS severe,
  SUM(CASE WHEN UPPER(TRIM(severity)) = 'CRITICAL' THEN 1 ELSE 0 END) AS critical
FROM covid_cases" . $where;

$mild = 0; $moderate = 0; $severe = 0; $critical = 0;
$res = mysqli_query($conn, $sql);
if ($res) {
  if ($r = mysqli_fetch_assoc($res)) {
    $mild = (int)$r['mild'];
    $moderate = (int)$r['moderate'];
    $severe = (int)$r['severe'];
    $critical = (int)$r['critical'];
  }
}

$out = array(
  'labels' => array('Mild','Moderate','Severe','Critical'),
  'counts' => array($mild,$moderate,$severe,$critical)
);
echo json_encode($out);
?>
