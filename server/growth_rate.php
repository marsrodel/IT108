<?php
require 'db.php';

header('Content-Type: application/json');

$yearly = [];
$monthly = [];

// Yearly growth of positive cases
$yearlySql = "
  SELECT
    yr AS period,
    total_positive_case,
    LAG(total_positive_case) OVER (ORDER BY yr) AS previous_positive_case,
    CASE
      WHEN LAG(total_positive_case) OVER (ORDER BY yr) IS NULL
           OR LAG(total_positive_case) OVER (ORDER BY yr) = 0
        THEN NULL
      ELSE ROUND(
        (total_positive_case - LAG(total_positive_case) OVER (ORDER BY yr))
        / LAG(total_positive_case) OVER (ORDER BY yr) * 100, 2
      )
    END AS growth_rate
  FROM (
    SELECT
      YEAR(test_date) AS yr,
      COUNT(*) AS total_positive_case
    FROM covid_cases
    WHERE result = 'Positive'
    GROUP BY YEAR(test_date)
  ) AS t
  ORDER BY yr;
";

if ($res = mysqli_query($conn, $yearlySql)) {
    while ($row = mysqli_fetch_assoc($res)) {
        $yearly[] = [
            'period' => $row['period'],
            'total_positive_case' => (int)$row['total_positive_case'],
            'previous_positive_case' => isset($row['previous_positive_case']) ? (int)$row['previous_positive_case'] : null,
            'growth_rate' => $row['growth_rate'] !== null ? (float)$row['growth_rate'] : null,
        ];
    }
    mysqli_free_result($res);
}

// Monthly growth of positive cases
$monthlySql = "
  SELECT
    month,
    total_positive_case,
    LAG(total_positive_case) OVER (ORDER BY month) AS previous_positive_case,
    CASE
      WHEN LAG(total_positive_case) OVER (ORDER BY month) IS NULL
           OR LAG(total_positive_case) OVER (ORDER BY month) = 0
        THEN NULL
      ELSE ROUND(
        (total_positive_case - LAG(total_positive_case) OVER (ORDER BY month))
        / LAG(total_positive_case) OVER (ORDER BY month) * 100, 2
      )
    END AS growth_rate
  FROM (
    SELECT
      DATE_FORMAT(test_date, '%Y-%m') AS month,
      COUNT(*) AS total_positive_case
    FROM covid_cases
    WHERE result = 'Positive'
    GROUP BY DATE_FORMAT(test_date, '%Y-%m')
  ) AS t
  ORDER BY month;
";

if ($res = mysqli_query($conn, $monthlySql)) {
    while ($row = mysqli_fetch_assoc($res)) {
        $monthly[] = [
            'period' => $row['month'],
            'total_positive_case' => (int)$row['total_positive_case'],
            'previous_positive_case' => isset($row['previous_positive_case']) ? (int)$row['previous_positive_case'] : null,
            'growth_rate' => $row['growth_rate'] !== null ? (float)$row['growth_rate'] : null,
        ];
    }
    mysqli_free_result($res);
}

echo json_encode([
    'yearly' => $yearly,
    'monthly' => $monthly,
]);
