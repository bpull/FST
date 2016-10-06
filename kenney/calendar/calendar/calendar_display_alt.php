<?php

  # Initial Homepage display before any options are selected

  if (isset($events_list)) {
    $year = $events_list['year'];
    $start = $events_list['month_start'];
    $stop = $events_list['month_end'];
    $box = $events_list['box'];
  }

  $today = getdate();

  echo "<table class='table table-striped table-bordered' width=99%><tbody>";
  for ($month = $start; $month <= $stop; $month++) {
    $month_len = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $month_name = cal_info(0);
    $month_name = $month_name['months'][$month];
    echo "<tr><td bgcolor='#a7a7a7' colspan=4><font size=3em><b>$month_name $year</b></font></td></tr>";
    for ($day = 1; $day <= $month_len; $day++) {
      if (isset($events_list[$month][$day])) {
        $type = $events_list[$month][$day]['type'];
        $type_num = $events_list[$month][$day]['type_num'];
        if (isset($events_list[$month][$day]['event']['name'])) {
          $type_name = $events_list[$month][$day]['event']['name'];
          ###echo "$month_name $day - " . ucfirst($type) . " $type_num - ";
          if ($today['year'] == $year && $today['mon'] == $month && $today['mday'] == $day) {
            $color = " bgcolor='#FFFEBD'";
          } else {
            $color = "";
          }
          echo "<tr><td $color nowrap>$month_name $day</td><td $color nowrap>" . ucfirst($type) . " $type_num</td><td>";
          if (isset($events_list[$month][$day]['event']['box']['title'])) {
            echo "<a href='calendar.php?type=$type&event=$type_num'>$type_name</a>";
          } else {
            echo "$type_name";
          }
          if (isset($events_list[$month][$day]['event']['box']['title']['visible']) && $events_list[$month][$day]['event']['box']['title']['visible'] == False) {
            echo " <font color='purple'>(".$events_list[$month][$day]['event']['box']['title']['month']."/".$events_list[$month][$day]['event']['box']['title']['day'].")</font>";
          }
          echo "</td><td>";
          foreach($box as $i => $btype) {
            $btype_desc = ucwords(str_ireplace("-", " ", $btype));
            if (isset($events_list[$month][$day]['event']['box'][$btype])) {
              $line = $events_list[$month][$day]['event']['box'][$btype];
            }
            if ($btype == 'title') {
            } else {
              if (isset($events_list[$month][$day]['event']['box'][$btype])) {
                if ($events_list[$month][$day]['event']['box'][$btype]['type'] == 'src') {
                  $actual = $events_list[$month][$day]['event']['box'][$btype]['actual'];
                  if (file_exists($actual)) {
                    echo file_get_contents($actual);
                  }
                } else {
                  echo "(";
                  $key = $events_list[$month][$day]['event']['box'][$btype]['key'];
                  $ftype = $events_list[$month][$day]['event']['box'][$btype]['ftype'];
                  $fclass = $events_list[$month][$day]['event']['box'][$btype]['fclass'];
                  $fmon = $events_list[$month][$day]['event']['box'][$btype]['month'];
                  $fday = $events_list[$month][$day]['event']['box'][$btype]['day'];
                  echo "<a href=calendar.php?key=$key&type=$ftype&event=$fclass>$btype_desc</a>";
                  if ($today['mon'] < $fmon || ($today['mon'] == $fmon && $today['mday'] < $fday)) {
                    echo " <font color='purple'>($fmon/$fday)</font> ";
                  }
                  echo ") ";
                }
              }
            }
          }
          echo "</td></tr>";
        }
      }
    }
  }
  echo "</table>";
?>
