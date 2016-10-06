<?php

  if (isset($events_list)) {
    $year = $events_list['year'];
    $start = $events_list['month_start'];
    $stop = $events_list['month_end'];
    $box = $events_list['box'];
  }

  $today = getdate();

  # Build the days of the week for the Summer
  #$SUMMER_MONTH_START = 5;
  #$SUMMER_DAY_START = 31;
  #$SUMMER_NOT_DAY = array(month => array(day=>'reason not class'));
  if (!isset($SUMMER_MONTH_START) || !isset($SUMMER_DAY_START)) {
    echo "<h4>Error: Please define \$SUMMER_MONTH_START and \$SUMMER_DAY_START</h4>";
  }
  if (!isset($SUMMER_MONTH_START)) {
    $SUMMER_MONTH_START = 5;
  }
  if (!isset($SUMMER_DAY_START)) {
    $SUMMER_DAY_START = 31;
  }
  if (!isset($SUMMER_NOT_DAY)) {
    $SUMMER_NOT_DAY = array();
  }
  $SUMMER_DAYS = array();
  for ($month = $SUMMER_MONTH_START; $month <= $SUMMER_MONTH_START+1; $month++) {
    $month_len = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $month_name = cal_info(0);
    $month_name = $month_name['months'][$month];
    $dow = date("w", mktime(0, 0, 0, $month, $SUMMER_DAY_START, $year));
    for ($day = $SUMMER_DAY_START; $day <= $month_len; $day++) {
      if ($WEEKENDS || ($dow > 0 && $dow <6)) {
        if (!isset($SUMMER_NOT_DAY) || !isset($SUMMER_NOT_DAY[$month]) || !isset($SUMMER_NOT_DAY[$month][$day])) {
          $SUMMER_DAYS[] = "$month_name $day";
        }
      }
      $dow++;
      $dow = $dow%7;
    }
    $SUMMER_DAY_START=1;
  }

  echo "<table class='table table-striped table-bordered' width=99%><tbody>";
  $class_day = 1;
  $shown_week = False;
  for ($month = $start; $month <= $stop; $month++) {
    $month_len = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $month_name = cal_info(0);
    $month_name = $month_name['months'][$month];
    $dow = date("w", mktime(0, 0, 0, $month, 1, $year));
    #echo "<tr><td bgcolor='#a7a7a7' colspan=4><font size=3em><b>$month_name $year</b></font></td></tr>";
    for ($day = 1; $day <= $month_len; $day++) {
      if ($dow == 0) {
        $shown_week = False;
      }
      if (isset($events_list[$month][$day])) {
        if (!$shown_week) {
          if (isset($SUMMER_DAYS) && isset($SUMMER_DAYS[$class_day-1]) && $today['month'].' '.$today['mday'] == $SUMMER_DAYS[$class_day-1]) {
            echo "<tr><td bgcolor='#FFFEBD' colspan=3><font size=3em><b>".$SUMMER_DAYS[$class_day-1]." - Summer School Day $class_day</b></font></td></tr>";
          } else {
            echo "<tr><td bgcolor='#a7a7a7' colspan=3><font size=3em><b>".$SUMMER_DAYS[$class_day-1]." - Summer School Day $class_day</b></font></td></tr>";
          }
          $shown_week = True;
          $class_day++;
        }
        $type = $events_list[$month][$day]['type'];
        $type_num = $events_list[$month][$day]['type_num'];
        if (isset($events_list[$month][$day]['event']['name'])) {
          $type_name = $events_list[$month][$day]['event']['name'];
          ###echo "$month_name $day - " . ucfirst($type) . " $type_num - ";
          $color = "";
          #echo "<tr><td $color nowrap>$month_name $day</td><td $color nowrap>" . ucfirst($type) . " $type_num</td><td>";
          echo "<tr><td $color nowrap>" . ucfirst($type) . " $type_num</td><td>";
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
      // Increment day of week counter...
      $dow++;
      $dow = $dow%7;
    }
  }
  echo "</table>";
?>
