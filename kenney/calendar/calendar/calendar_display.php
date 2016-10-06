<?php

  # Default Calendar Layout

  if (isset($events_list)) {
    $year = $events_list['year'];
    $start = $events_list['month_start'];
    $stop = $events_list['month_end'];
    $box = $events_list['box'];
  }

  $today = getdate();

  $box_width = "20";
  if ($WEEKENDS) {
    $box_width = "14";
  }

  for ($month = $start; $month <= $stop; $month++) {
    $month_len = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $month_name = cal_info(0);
    $month_name = $month_name['months'][$month];
    $dow = date("w", mktime(0, 0, 0, $month, 1, $year));
    if ($dow == 0) {$dow = 7;}
    echo "<h4>$month_name $year</h4>";
    echo "<table class='table table-striped table-bordered' width=99%><thead><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th>";
    if ($WEEKENDS) {
      echo "<th>Saturday</th><th>Sunday</th>";
    }
    echo "</thead><tbody>";
    $week_open = False;
    if (($dow != 1 && $dow < 6) || $WEEKENDS) {
      echo "<tr valign=top>";
      $week_open = True;
      for ($spacer = 1; ($spacer < $dow); $spacer++) {
        if ($spacer < 6 || ($WEEKENDS && $spacer < 8)) {
          echo "<td width=$box_width% bgcolor='#eaeaea'>&nbsp; </td>";
        }
      }
    }
    for ($day = 1; $day <= $month_len; $day++) {
      if ($WEEKENDS || $dow < 6) {
        if (!$week_open) {
          echo "<tr valign=top>";
          $week_open = True;
        }
        if ($today['year'] == $year && $today['mon'] == $month && $today['mday'] == $day) {
          echo "<td valign=top width=$box_width% bgcolor='#FFFEBD'><a name='today'></a>";
        } else {
          echo "<td valign=top width=$box_width%>";
        }
        if ($INSTRUCTOR && isset($events_list[$month][$day]['type']) && isset($events_list[$month][$day]['type_num'])) {
          echo "<a href=calendar.php?navbaronly=1&type=".$events_list[$month][$day]['type']."&event=".$events_list[$month][$day]['type_num'].">";
        }
        echo "$day ";
        if ($INSTRUCTOR) {
          echo "</a>";
        }
        echo "<center>";
        if (isset($events_list[$month][$day])) {
          echo "<b>" . strtoupper($events_list[$month][$day]['type']) . ' ' . $events_list[$month][$day]['type_num'] . "</b><br>";
          foreach($box as $i => $btype) {
            $btype_desc = ucwords(str_ireplace("-", " ", $btype));
            if (isset($events_list[$month][$day]['event']['box'][$btype])) {
              $line = $events_list[$month][$day]['event']['box'][$btype];
            }
            if ($btype == 'title') {
              if (isset($events_list[$month][$day]['event']['box'][$btype])) {
                echo "<a href=calendar.php?type=" . $events_list[$month][$day]['type'] . "&event=" . $events_list[$month][$day]['type_num'] . ">";
                echo $events_list[$month][$day]['event']['name'];
                echo "</a>";
                $fmon = $events_list[$month][$day]['event']['box'][$btype]['month'];
                $fday = $events_list[$month][$day]['event']['box'][$btype]['day'];
                if ($today['mon'] < $fmon || ($today['mon'] == $fmon && $today['mday'] < $fday)) {
                  echo " <font color='purple'>($fmon/$fday)</font>";
                }
              } elseif (isset($events_list[$month][$day]['event']['name'])) {
                echo $events_list[$month][$day]['event']['name'];
              }
            } else {
              if (isset($events_list[$month][$day]['event']['box'][$btype])) {
                if ($events_list[$month][$day]['event']['box'][$btype]['type'] == 'src') {
                  $actual = $events_list[$month][$day]['event']['box'][$btype]['actual'];
                  if (file_exists($actual)) {
                    echo file_get_contents($actual);
                  }
                } else {
                  $key = $events_list[$month][$day]['event']['box'][$btype]['key'];
                  $ftype = $events_list[$month][$day]['event']['box'][$btype]['ftype'];
                  $fclass = $events_list[$month][$day]['event']['box'][$btype]['fclass'];
                  $fmon = $events_list[$month][$day]['event']['box'][$btype]['month'];
                  $fday = $events_list[$month][$day]['event']['box'][$btype]['day'];
                  echo "<a href=calendar.php?key=$key&type=$ftype&event=$fclass>$btype_desc</a>";
                  if ($today['mon'] < $fmon || ($today['mon'] == $fmon && $today['mday'] < $fday)) {
                    echo " <font color='purple'>($fmon/$fday)</font>";
                  }
                }
              }
            }
            echo "<br>";
          }
        } else {
          echo "<br><br><br><br><br><br><br>";
        }
        echo "</center></td>";
      }
      $dow++;
      if ($dow > 7) {
        echo "</tr>";
        $week_open = False;
        $dow = 1;
      }
    }


    if ($week_open) {
      echo "</tr>";
    }
    echo "</tbody></table>";
  }

?>
