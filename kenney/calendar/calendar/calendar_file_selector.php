<?php


  if ($INSTRUCTOR && !empty($other)) {
    echo "<table class='table table-striped table-bordered'>";
    echo "<thead><th>Type</th><th>Category</th><th>File</th><th>Visible</th><th>Month</th><th>day</th></thead>";
    echo "<tbody>";
    foreach ($other as $fn => $value) {
      $link = "calendar.php?key=".$value['key'];
      if (isset($_GET['type'])) {
        $link .= '&type=' . $_GET['type'];
      }
      if (isset($_GET['event'])) {
        $link .= '&event=' . $_GET['event'];
      }
      echo "<tr><td>".$value['type']."</td><td>".$value['category']."</td><td><a href='$link'>$fn</a></td><td>".$value['visible']."</td><td>".$value['month']."</td><td>".$value['day']."</td></tr>";
    }
    echo "</table>";
  }

?>
