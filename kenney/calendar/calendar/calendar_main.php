<?php

  # Calendar Version 4.0
  define('CALENDAR_VERSION', '20160914A');

  # Load in the configuration for all virtual files
  $virtual = array();
  if (file_exists($CLASS_FILE)) {
    $vdata = file($CLASS_FILE);
    foreach($vdata as $line) {
      $line = explode("\t", trim($line));
      if (count($line) == 4) {
        if (!isset($virtual[$line[2]][$line[3]])) {
          $virtual[$line[2]][$line[3]] = array();
        }
        $virtual[$line[2]][$line[3]][] = array($line[0], $line[1]);
      }
    }
  }
  #$virtual = array('class'=>array(1 => array(array('answers.html', 'homework/answers05.html'))));

  # Load in the configuration for all access configurations
  $access = array();
  if (file_exists($ACCESS_FILE)) {
    $vdata = file($ACCESS_FILE);
    foreach($vdata as $line) {
      if (strpos(trim($line), "\t") !== False) {
        $line = explode("\t", trim($line));
        if (count($line) == 3) {
          $access[$line[0]] = array('month'=> intval($line[1]), 'day'=> intval($line[2]));
        }
      } else {
        $line = trim($line);
        $line = preg_split('/\s+/', $line);
        if (count($line) == 3) {
          $access[$line[0]] = array('month'=> intval($line[1]), 'day'=> intval($line[2]));
        }
      }
    }
  }
  #$access = array('class01.html' => array('month'=>2, 'day'=>4));
  # Validate a file to see if it should be accessible by the students

  # Returns array(VisibleInGeneral, VisibleOnCalendar, Year, Month, Day)
  function validate_file($instructor, $filename, $filewithpath, $access) {
    if (is_dir($filewithpath)) {
      return array(False, False, 1, 1, 1, '');
    }
    $today = getdate();
    $open_year = $today['year'];
    $open_month = 1;
    $open_day = 1;
    $cat_file = $filename;
    if (isset($access[basename($filewithpath)])) {
      $open_day = intval($access[basename($filewithpath)]['day']);
      $open_month = intval($access[basename($filewithpath)]['month']);
      if (isset($access[basename($filewithpath)]['year'])) {
        $open_year = intval($access[basename($filewithpath)]['year']);
      }
    } elseif (isset($access[basename($filename)])) {
      $open_day = intval($access[basename($filename)]['day']);
      $open_month = intval($access[basename($filename)]['month']);
      if (isset($access[basename($filename)]['year'])) {
        $open_year = intval($access[basename($filename)]['year']);
      }
    } else {
      $cp = array_reverse(explode('.', basename($filewithpath)));
      if (count($cp) > 3 && is_numeric($cp[1]) && is_numeric($cp[2])) {
        $open_day = intval($cp[1]);
        $open_month = intval($cp[2]);
        $cat_file = $cp[3] . '.' . $cp[0];
      } else {
        return array(True, True, 1, 1, 1, $cat_file);
      }
    }
    if ($today['year'] >= $open_year && $today['mon'] > $open_month) {
      return array(True, True, $open_year, $open_month, $open_day, $cat_file);
    } elseif ($today['year'] >= $open_year && $today['mon'] >= $open_month && $today['mday'] >= $open_day) {
      return array(True, True, $open_year, $open_month, $open_day, $cat_file);
    } elseif ($instructor) {
      return array(True, False, $open_year, $open_month, $open_day, $cat_file);
    }
    return array(False, False, $open_year, $open_month, $open_day, $cat_file);
  }

  # Determine what type of category the file is
  # types = html, link, src, txt, powerpoint, pdf (aka the extension!)
  # category =
  function categorize_file($instructor, $filename, $filewithpath, $filereduced, $categories) {
    $mytype = '';
    $myboxblock = '';
    if (isset($categories[$filereduced])) {
      $mytype = $categories[$filereduced]['type'];
      $myboxblock = $categories[$filereduced]['boxblock'];
    } elseif (isset($categories[$filename])) {
      $mytype = $categories[$filename]['type'];
      $myboxblock = $categories[$filename]['boxblock'];
    }
    return array($mytype, $myboxblock);
  }

  # Retrieve a list of directories and their files, that are sourced from the
  # content directories.  Files will be validated for access
  function get_files($instructor=False, $sources=array('class', 'lab', 'project', 'exam', 'review', 'capstone', 'continued-lab'), $file_path = '.', $access=array(), $virtual=array(), $categories=array(), $secret='really') {
    $results = array();
    $basedir = scandir($file_path);
    foreach ($sources as $i => $l0) {
      if (in_array($l0, $basedir) && is_dir($file_path . '/' . $l0)) {
        $level1 = scandir($file_path . '/' . $l0);
        sort($level1);
        foreach ($level1 as $i => $l1) {
          if (substr($l1, 0, 1) != '.' && is_dir($file_path . '/' . $l0 . '/' . $l1)) {
            // The following code allows the system recursively look through the Directory
            // structure within a class, and the system can now process RELATIVE links
            // that go into those subordinate directories
            $level2 = array();
            try {
              $Iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($file_path . '/' . $l0 . '/' . $l1),
                RecursiveIteratorIterator::LEAVES_ONLY,
                RecursiveIteratorIterator::CATCH_GET_CHILD);
              foreach($Iterator as $name => $object) {
                $name = explode("/", $name);
                unset($name[2]);
                unset($name[1]);
                unset($name[0]);
                $name = implode("/", $name);
                $level2[] = $name;
              }
            } catch (Exception $e) {
              // Unable to recurse, so don't bother...
            }
            //$level2 = scandir($file_path . '/' . $l0 . '/' . $l1);
            $results[$l0][$l1] = array();
            sort($level2);
            foreach ($level2 as $i => $l2) {
              $key = sha1($secret.$l0.$l1.$l2);
              if (substr($l2, 0, 1) != '.' && validate_file($instructor, $l2, $file_path . '/' . $l0 . '/' . $l1 . '/' . $l2, $access)[0]) {
                if (is_link($file_path . '/' . $l0 . '/' . $l1 . '/' . $l2)) {
                  if (validate_file($instructor, readlink($file_path . '/' . $l0 . '/' . $l1 . '/' . $l2), readlink($file_path . '/' . $l0 . '/' . $l1 . '/' . $l2), $access)[0]) {
                    $vf = validate_file($instructor, readlink($file_path . '/' . $l0 . '/' . $l1 . '/' . $l2), readlink($file_path . '/' . $l0 . '/' . $l1 . '/' . $l2), $access);
                    $category = categorize_file($instructor, $l2, readlink($file_path . '/' . $l0 . '/' . $l1 . '/' . $l2), $vf[5], $categories);
                    $results[$l0][$l1][$l2] = array('actual' => $file_path . '/' . $l0 . '/' . $l1 . '/' . readlink($file_path . '/' . $l0 . '/' . $l1 . '/' . $l2),
                                                    'visible' => $vf[1],
                                                    'year' => $vf[2],
                                                    'month' => $vf[3],
                                                    'day' => $vf[4],
                                                    'type' => $category[0],
                                                    'category' => $category[1],
                                                    'key' => $key);
                  }
                } else {
                  $vf = validate_file($instructor, $l2, $file_path . '/' . $l0 . '/' . $l1 . '/' . $l2, $access);
                  $category = categorize_file($instructor, $l2, $file_path . '/' . $l0 . '/' . $l1 . '/' . $l2, $vf[5], $categories);
                  $results[$l0][$l1][$l2] = array('actual' => $file_path . '/' . $l0 . '/' . $l1 . '/' . $l2,
                                                  'visible' => $vf[1],
                                                  'year' => $vf[2],
                                                  'month' => $vf[3],
                                                  'day' => $vf[4],
                                                  'type' => $category[0],
                                                  'category' => $category[1],
                                                  'key' => $key);
                }
              }
            }
          }
        }
      }
    }
    # Add Virtual files, format like array('class'=>array(1 => array(array('answers.html', 'homework/answers05.html'))))
    foreach ($virtual as $l0 => $l1array) {
      if (isset($results[$l0])) {
        $classes = array_keys($results[$l0]);
        foreach ($l1array as $day => $l2array) {
          if (isset($classes[$day-1])) {
            foreach ($l2array as $i => $values) {
              $virt_file = $values[0];
              $real_file = $values[1];
              if (validate_file($instructor, $real_file, $real_file, $access)[0]) {
                $key = sha1($secret.$real_file.$virt_file);
                $vf = validate_file($instructor, $real_file, $real_file, $access);
                $category = categorize_file($instructor, $virt_file, $real_file, $vf[5], $categories);
                $results[$l0][$classes[$day-1]][$virt_file] = array('actual' => $real_file,
                                                                    'visible' => $vf[1],
                                                                    'year' => $vf[2],
                                                                    'month' => $vf[3],
                                                                    'day' => $vf[4],
                                                                    'type' => $category[0],
                                                                    'category' => $category[1],
                                                                    'key' => $key);
              }
            }
          }
        }
      }
    }
    #####################################################
    # Verify that the class is not blocked by security...
    $today = getdate();
    foreach ($results as $l0 => $classes) {
      $counter = 1;
      foreach ($classes as $l1 => $data) {
        foreach ($data as $filename => $fdata) {
          if ($fdata['year'] == 1 && $fdata['month'] == 1 && $fdata['day'] == 1) {
            $cate = $fdata['category'];
            if (isset($access[$l0."_".$counter]) && $cate == 'title') {
              $sspec = $l0."_".$counter;
              $results[$l0][$l1][$filename]['month'] = $access[$sspec]['month'];
              $results[$l0][$l1][$filename]['day'] = $access[$sspec]['day'];
              if (($today['mon'] > $access[$sspec]['month']) || ($today['mon'] == $access[$sspec]['month'] && $today['mday'] >= $access[$sspec]['day'])) {
              } else {
                $results[$l0][$l1][$filename]['visible'] = False;
                if (!$instructor) {
                  unset($results[$l0][$l1][$filename]);
                }
              }
            } elseif (isset($access[$l0."_".$counter."/".$cate])) {
              $sspec = $l0."_".$counter."/".$cate;
              $results[$l0][$l1][$filename]['month'] = $access[$sspec]['month'];
              $results[$l0][$l1][$filename]['day'] = $access[$sspec]['day'];
              if (($today['mon'] > $access[$sspec]['month']) || ($today['mon'] == $access[$sspec]['month'] && $today['mday'] >= $access[$sspec]['day'])) {
              } else {
                $results[$l0][$l1][$filename]['visible'] = False;
                if (!$instructor) {
                  unset($results[$l0][$l1][$filename]);
                }
              }
            } elseif (isset($access[$l0."_".$counter."/".$filename])) {
              $sspec = $l0."_".$counter."/".$filename;
              $results[$l0][$l1][$filename]['month'] = $access[$sspec]['month'];
              $results[$l0][$l1][$filename]['day'] = $access[$sspec]['day'];
              if (($today['mon'] > $access[$sspec]['month']) || ($today['mon'] == $access[$sspec]['month'] && $today['mday'] >= $access[$sspec]['day'])) {
              } else {
                $results[$l0][$l1][$filename]['visible'] = False;
                if (!$instructor) {
                  unset($results[$l0][$l1][$filename]);
                }
              }
            } elseif (isset($access[$l0."_".$counter."/all"])) {
              $sspec = $l0."_".$counter."/all";
              $results[$l0][$l1][$filename]['month'] = $access[$sspec]['month'];
              $results[$l0][$l1][$filename]['day'] = $access[$sspec]['day'];
              if (($today['mon'] > $access[$sspec]['month']) || ($today['mon'] == $access[$sspec]['month'] && $today['mday'] >= $access[$sspec]['day'])) {
              } else {
                $results[$l0][$l1][$filename]['visible'] = False;
                if (!$instructor) {
                  unset($results[$l0][$l1][$filename]);
                }
              }
            }
          }
        }
        $counter++;
      }
    }
    return $results;
  }

  # Build type tree
  function build_types($BOX, $HTML, $LINK, $PDF, $PPT, $SRC) {
    $categories = array();
    foreach (array('html'=>$HTML, 'link'=>$LINK, 'pdf'=>$PDF, 'ppt'=>$PPT, 'src'=>$SRC) as $item => $values) {
      foreach ($values as $cat => $value) {
        $categories[$value] = array('type'=>$item, 'boxblock'=>$cat);
      }
    }
    return $categories;
  }

  # Update the events with the date of the class
  function calendar_events($events, $YEAR, $MONTH_START, $DAY_START, $WEEKENDS, $MONTH_END, $DOW, $OVERRIDE, $BOX) {
    $results = array('year'=>$YEAR, 'month_start'=>$MONTH_START, 'month_end'=>$MONTH_END, 'box'=>$BOX);
    $results_counter = array();
    $POSDOW = array(0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun');
    for($month=$MONTH_START; $month <= $MONTH_END; $month++) {
      $month_len = cal_days_in_month(CAL_GREGORIAN, $month, $YEAR);
      $month_name = cal_info(0);
      $month_name = $month_name['months'][$month];
      $first = date("w", mktime(0, 0, 0, $month, 1, $YEAR));
      for($day=1; $day <= $month_len; $day++) {
        $day_type = '';
        $day_num = '';
        $force_day = False;
        if ($month == $MONTH_START && $day < $DAY_START) {
        } elseif (isset($OVERRIDE[$month][$day])) {
          $day_type = $OVERRIDE[$month][$day];
          $force_day = True;
        } elseif (isset($DOW[$POSDOW[$first]])) {
          $day_type = $DOW[$POSDOW[$first]];
        }
        if ($day_type != '') {
          if (isset($events[$day_type])) {
            if (!isset($results_counter[$day_type])) {
              $results_counter[$day_type] = 1;
            } else {
              $results_counter[$day_type]++;
            }
          }
          if (isset($events[$day_type]) && isset($events[$day_type][$results_counter[$day_type]])) {
            $event = $events[$day_type][$results_counter[$day_type]];
            $day_num = $results_counter[$day_type];
          } else {
            $event = array();
          }
          if ($force_day || !empty($event)) {
            $results[$month][$day] = array('month'=>$month, 'day'=>$day, 'type'=>$day_type, 'type_num'=>$day_num, 'dow'=>$first, 'dow_eng'=>$POSDOW[$first], 'event'=>$event);
          }
        }
        $first++;
        if ($first == 7) {$first = 0;}
      }
    }
    return $results;
  }

  # Build events (these will be used to display the calendar)
  function build_events($files, $categories, $BOX) {
    $events = array();
    foreach ($files as $cat => $catarray) {
      $events[$cat] = array();
      $counter = 1;
      foreach ($catarray as $event => $earray) {
        $cevent = explode('.', $event);
        if (count($cevent) > 1) {
          $cevent = $cevent[1];
        } else {
          $cevent = $cevent[0];
        }
        $events[$cat][$counter] = array('name' => $cevent, 'box' => array());
        $fcounter = 0;
        foreach ($earray as $fn => $fndata) {
          if ($fndata['category'] != '' && !isset($events[$cat][$counter]['box'][$fndata['category']])) {
            $events[$cat][$counter]['box'][$fndata['category']] = $fndata;
            $events[$cat][$counter]['box'][$fndata['category']]['filename'] = $fn;
            $events[$cat][$counter]['box'][$fndata['category']]['ftype'] = $cat;
            $events[$cat][$counter]['box'][$fndata['category']]['fclass'] = $counter;
            $events[$cat][$counter]['box'][$fndata['category']]['fid'] = $fcounter;
          }
          $fcounter++;
        }
        $counter++;
      }
    }
    return $events;
  }

  # Build keypairs (these link files to a sha1 hash)
  function build_keypairs($files) {
    $keypairs = array();
    foreach ($files as $type => $classes) {
      foreach ($classes as $classname => $classfiles) {
        foreach ($classfiles as $filename => $values) {
          $keypairs[$values['key']] = $values['actual'];
        }
      }
    }
    return $keypairs;
  }

  # load and provide the contents of a file to the browser
  # sets appropriate mime types
  function provide_file($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $MODE = 'txt';
    $attachment = "attachment; ";
    if ($ext != '') {
      $MODE = $ext;
    }
    switch ($MODE) {
      case "bz2": $ctype="application/x-bzip2"; break;
      case "css": $ctype="text/css"; break;
      case "gz": $ctype="application/x-gzip"; break;
      case "gzip": $ctype="application/x-gzip"; break;
      case "java": $ctype="text/x-java-source"; $attachment=""; break;
      case "tgz": $ctype="application/x-compressed"; break;
      case "pdf": $ctype="application/pdf"; $attachment=""; break;
      case "zip": $ctype="application/zip"; break;
      case "doc": $ctype="application/msword"; break;
      case "docx": $ctype="application/vnd.openxmlformats-officedocument.wordprocessingml.document"; break;
      case "xls": $ctype="application/vnd.ms-excel"; break;
      case "xlsx": $ctype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; break;
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
      case "pptx": $ctype="application/vnd.openxmlformats-officedocument.presentationml.presentation"; break;
      case "svg": $ctype="image/svg+xml"; $attachment=""; break;
      case "gif": $ctype="image/gif"; $attachment=""; break;
      case "png": $ctype="image/png"; $attachment=""; break;
      case "jpe": case "jpeg":
      case "jpg": $ctype="image/jpg"; $attachment=""; break;
      case "sql":
      case "txt": $ctype="text/plain"; $attachment=""; break;
      case "htm": $ctype="text/html"; $attachment=""; break;
      case "html": $ctype="text/html"; $attachment=""; break;
      case "htmls": $ctype="text/html"; $attachment=""; break;
      default: $ctype="application/octet-stream";
    }

    header("Content-Type: $ctype");
    header('Content-Disposition: '.$attachment.'filename="'.basename($filename).'"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    echo file_get_contents($filename);

  }

  # Determine the valid categories for files
  $categories = build_types($BOX, $HTML, $LINK, $PDF, $PPT, $SRC);

  # Perform Authentication
  session_start();
  if (isset($_POST['password']) && $_POST['password'] == $ADMIN) {
    $_SESSION["cal4-$COURSE"] = sha1($SECRET.$ADMIN.$COURSE);
  } elseif (isset($_POST['password']) && isset($_SESSION["cal4-$COURSE"])) {
    unset($_SESSION["cal4-$COURSE"]);
  }
  if (isset($_SESSION["cal4-$COURSE"])) {
    if ($_SESSION["cal4-$COURSE"] == sha1($SECRET.$ADMIN.$COURSE)) {
      $INSTRUCTOR = True;
    }
  }

  # Get all files that are available to the user within the directory structure
  if (!isset($INSTRUCTOR)) {
    $INSTRUCTOR = False;
  }
  $files = get_files($INSTRUCTOR, array('class', 'lab', 'project', 'exam', 'review', 'capstone', 'continued-lab'), '.', $access, $virtual, $categories, $SECRET);

  # Retrieve a list of file=>keys
  $keypairs = build_keypairs($files);

  # Build an array of all of the events (day by day, class or lab by class)
  $events = build_events($files, $categories, $BOX);
  $events_list = calendar_events($events, $YEAR, $MONTH_START, $DAY_START, $WEEKENDS, $MONTH_END, $DOW, $OVERRIDE, $BOX);

  # Was a specific event requested, if so process the html and;
  #  - embed any local images
  #  - remove all <inst> tags if not the instructor
  #  - remove all <student> tags if answers were not requested
  # Place the results in $contents
  $contents = '';
  $actual = '';
  $other = array();
  $find_student = False;
  $find_instructor = False;
  $navbar_display = "";
  $file_type = '?';

  # Decide what to show if coming to the main page without anything selected
  if (!isset($_GET['event']) && !isset($_GET['type']) && !isset($_GET['load']) && !isset($_GET['show']) && !isset($_GET['key'])) {
    if ($DEFAULT_TODAYS_LECTURE) {
      $today = getdate();
      $today_mon = $today['mon'];
      $today_day = $today['mday'];
      if (isset($events_list[$today_mon][$today_day]['event']['box']['title'])) {
        if (isset($events_list[$today_mon][$today_day]['event']['box']['title']['type']) && $events_list[$today_mon][$today_day]['event']['box']['title']['type'] == 'html') {
          $_GET['type'] = $events_list[$today_mon][$today_day]['type'];
          $_GET['event'] = $events_list[$today_mon][$today_day]['type_num'];
        }
      } else {
        $_GET['load'] = 'home';
      }
    } else {
      $_GET['load'] = 'home';
    }
  }

  # Retrieve the other files in the specific lecture
  if (isset($_GET['event']) && isset($_GET['type']) && isset($events[$_GET['type']])) {
    $other = array_keys($files[$_GET['type']]);
    $other = $files[$_GET['type']][$other[$_GET['event']-1]];
  }

  # Check to see if event and type were provided and if they are valid
  if (isset($_GET['event']) && isset($_GET['type']) && isset($events[$_GET['type']])) {

    # If a valid keypair is provided use that file as the source
    if (isset($_GET['key']) && isset($keypairs[$_GET['key']])) {
      $actual = $keypairs[$_GET['key']];
      $ext = pathinfo($actual, PATHINFO_EXTENSION);
    } elseif (isset($events[$_GET['type']][$_GET['event']]['box']['title']['actual']) && isset($events[$_GET['type']][$_GET['event']]['box']['title']['type'])) {
      $actual = $events[$_GET['type']][$_GET['event']]['box']['title']['actual'];
      $file_type = $events[$_GET['type']][$_GET['event']]['box']['title']['type'];
      $ext = pathinfo($actual, PATHINFO_EXTENSION);
    } else {
      $file_type = '?';
      $ext = '?';
    }

    # If the source is not html, provide the unprocessed file
    if ($file_type != 'html' && $ext != 'html' && $actual != '' && $ext != 'htm' && !isset($_GET['navbaronly'])) {
      provide_file($actual);
      die;
    } elseif ($ext == 'htm') {
      $_GET['nocss'] = 'yes';
    }

    # Set the navbar title to the title of this lecture
    if (isset($events[$_GET['type']][$_GET['event']]['name'])) {
      $navbar_display = $events[$_GET['type']][$_GET['event']]['name'];
    }

    # Retrieve the contents of this file
    if ($actual != '') {
      $contents = file_get_contents($actual);
    }
  }

  # Check to see if a web page is requested
  # This will provide .html files in the root directory
  if (isset($_GET['load'])) {
    $actual = $_GET['load'].'.html';
    if (file_exists($actual)) {
      $contents = file_get_contents(basename($actual));
    }
  }

  # Search for student and instructor tags
  $find_student = (strpos($contents, '<student>') > 0);
  $find_instructor = (strpos($contents, '<inst>') > 0);

  # Remove the contents of <inst>...</inst> tags if not log on
  if (!isset($INSTRUCTOR) || !$INSTRUCTOR) {
    $contents = preg_replace('/<inst[^>]*>([\s\S]*?)<\/inst[^>]*>/', '', $contents);
  }

  # Remove the contents of <student>...</student> tags if answers are to be hidden
  if (!isset($_GET['answers'])) {
    $contents = preg_replace('/<student[^>]*>([\s\S]*?)<\/student[^>]*>/', '', $contents);
  }

  # Search for <inject src="">tags! and directly copy the contents into this tag area
  preg_match_all('/<inject[^>]+>/i', $contents, $injects);
  foreach($injects[0] as $row => $inject_tag) {
    preg_match_all('/src=("[^"]*")/i',$inject_tag, $tag_src);
    $tag_src = substr($tag_src[1][0],1,-1);
    if ($tag_src != "" && isset($other[$tag_src])) {
      $inject_data = file_get_contents($other[$tag_src]['actual']);
      $inject_src[] = $tag_src;
      $contents = str_ireplace('<inject src="'.$tag_src.'">', $inject_data, $contents);
      $contents = str_ireplace("<inject src='$tag_src'>", $inject_data, $contents);
    }
  }

  # Search for <inject src="">tags! and directly copy the contents into this tag area
  preg_match_all('/<codeinject[^>]+>/i', $contents, $injects);
  foreach($injects[0] as $row => $inject_tag) {
    preg_match_all('/src=("[^"]*")/i',$inject_tag, $tag_src);
    $tag_src = substr($tag_src[1][0],1,-1);
    if ($tag_src != "" && isset($other[$tag_src])) {
      $inject_data = file_get_contents($other[$tag_src]['actual']);
      $inject_data = str_ireplace('<', '&lt;', $inject_data);
      $inject_data = str_ireplace('>', '&gt;', $inject_data);
      $contents = str_ireplace('<codeinject src="'.$tag_src.'">', $inject_data, $contents);
      $contents = str_ireplace("<codeinject src='$tag_src'>", $inject_data, $contents);
    }
  }

  # Remove TABs, they are evil!
  if (True) {
    $contents = str_ireplace("\t", '  ', $contents);
  }

  # Replace image <img src> and anchor <a href> links with the key'd version of the file
  foreach ($other as $fn => $value) {
    $link = "calendar.php?key=".$value['key'];
    if (isset($_GET['type'])) {
      $link .= '&type=' . $_GET['type'];
    }
    if (isset($_GET['event'])) {
      $link .= '&event=' . $_GET['event'];
    }
    $contents = str_ireplace('<img src="'.$fn.'"', '<img src="'.$link.'"', $contents);
    $contents = str_ireplace("<img src='".$fn."'", "<img src='".$link."'", $contents);
    $contents = str_ireplace('<a href="'.$fn.'"', '<a href="'.$link.'"', $contents);
    $contents = str_ireplace("<a href='".$fn."'", "<a href='".$link."'", $contents);
  }

  # Find any <a name> tags that are used to define anchors
  # This will be used to provide menus via the navbar
  preg_match_all('/<a name=\"(.*?)\"\><\/a>/s', $contents, $navbar_menus);

  # What types of courses do you want to highlight
  $navbar_dropdowns = array('class' => 'glyphicon-apple',
                            'lab' => 'glyphicon-knight');

  # Show the navbar
  if (isset($_GET['nocss'])) {
    # Do not load any css (debugging mode)
  } elseif (!isset($_POST['print'])) {
    require_once('calendar/calendar_navbar.php');
  } else {
    require_once('calendar/calendar_css.php');
  }

  # Provide the content of the .html file
  echo '<!-- Begin providing the contents of the page -->';
  echo '<div class="container">';

  # If requested only the navbar (so that contents can be revealed)
  if (isset($_GET['navbaronly']) && !isset($_GET['show'])) {
    $_GET['show'] = 'calendar_file_selector';
  }

  # If a php script was requested run it (assuming its valid)
  if (isset($_GET['show'])) {
    $actual = 'calendar/' . basename($_GET['show']) . '.php';
    if (file_exists($actual)) {
      require_once($actual);
    }
  }

  if (!isset($_GET['navbaronly'])) {
    echo $contents;
  }

  if ($INSTRUCTOR) {

  # Debugging if desired
  #print "<pre><code>";
  #echo "INSTRUCTOR=$INSTRUCTOR <br>";
  #print_r($events);
  #print_r($events_list);
  #print_r($keypairs);
  #print_r($files);
  #print_r($categories);
  #print_r($other);
  #print_r($_POST);
  #print_r($access);
  #print_r($inject_src);
  #echo "</code></pre>";

  }

  echo "</div> <!-- /container --></body></html>";

?>
