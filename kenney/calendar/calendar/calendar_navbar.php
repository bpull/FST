<?php require_once('calendar_css.php'); ?>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!--
            <a class="navbar-brand" href="#">
              <img alt="Navbar!" src="css/images/web-icon.png" width="24">
            </a>
          -->
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">

            <li><a href="calendar.php?load=home">
                <?php echo $COURSE; ?> - <?php echo $COURSENAME; ?></a></li>

            <li><a title="Calendar" href="calendar.php?show=calendar_display">
                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                </a></li>

            <li><a title="Course Policy" href="calendar.php?load=policy">
                <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                </a></li>

            <li><a title="Resources" href="calendar.php?load=resources">
                <span class="glyphicon glyphicon-inbox" aria-hidden="true"></span>
                </a></li>

        <?php
          if ($INSTRUCTOR) {
            ?>
            <li><a title="Instructor Guide" href="calendar.php?load=instructor">
                <span class="glyphicon glyphicon-apple" aria-hidden="true"></span>
                </a></li>
            <?php
          }
        ?>

        <?php
          if (isset($find_student) && $find_student && isset($_GET['type']) && isset($_GET['event'])) {
            if (!isset($_GET['answers'])) {
              $unlock_link = "calendar.php?type=" . $_GET['type'] . "&event=" . $_GET['event'] . "&answers=yes#problems";
              echo "<li><a title='Show Problem Answers' href='$unlock_link'>";
              echo '<span class="glyphicon glyphicon-search" aria-hidden="true"></span>';
              echo '</a></li>';
            } else {
              $lock_link = "calendar.php?type=" . $_GET['type'] . "&event=" . $_GET['event'];
              echo "<li><a title='Hide Problem Answers' href='$lock_link'>";
              echo '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>';
              echo '</a></li>';
            }
          }

        ?>

          </ul>

          <ul class="nav navbar-nav navbar-right">
            <?php
              echo '<li class="dropdown">';
              echo '<a href="#" title="View files associated with lecture" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';
              echo $navbar_display;
              if ($navbar_display == '') {
                echo "Select Lecture";
              }
              echo '</a>';
              echo '<ul class="dropdown-menu  scrollable-menu">';
              if (isset($navbar_menus) && count($navbar_menus) > 0) {
                $navbar_menu_div = False;
                foreach ($navbar_menus[1] as $item) {
                  if (strpos($item, 'pagename:') === False) {
                    $item_desc = ucfirst($item);
                    $item_desc = str_ireplace("-", " ", $item_desc);
                    echo "<li><a href='#$item'>$item_desc</a></li>";
                    $navbar_menu_div = True;
                  }
                }
                if ($navbar_menu_div) {
                  echo '<li role="separator" class="divider"></li>';
                }
              }

              if ($INSTRUCTOR && !empty($other)) {
                echo '<li class="dropdown-header">Associated Files</li>';
                foreach ($other as $fn => $value) {
                  $link = "calendar.php?key=".$value['key'];
                  if (isset($_GET['type'])) {
                    $link .= '&type=' . $_GET['type'];
                  }
                  if (isset($_GET['event'])) {
                    $link .= '&event=' . $_GET['event'];
                  }
                  echo "<li><a href='$link'>$fn</a></li>";
                }
                echo '<li role="separator" class="divider"></li>';
              }

              # Print the page
              echo '<li class="dropdown-header">Options</li>';
              echo '<form method=post class="navbar-form navbar-left" role="search" target="_blank">';
              echo '<div class="input-group">';
              echo '    <input type="hidden" class="form-control" placeholder="Print" name="print" id="print">';
              echo '    <div class="input-group-btn">';
              echo '        <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-print"></i> Print</button>';
              echo '    </div>';
              echo '</div>';
              echo '</form>';

              # Searchbox
              echo '<form method=get class="navbar-form navbar-left" role="search">';
              echo '<div class="input-group">';
              echo '    <input type="hidden" name="show" value="calendar_search">';
              echo '    <input type="text" class="form-control" placeholder="Search" name="search" id="search">';
              echo '    <div class="input-group-btn">';
              echo '        <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>';
              echo '    </div>';
              echo '</div>';
              echo '</form>';

              # Logon (or show logoff page)
              if ($INSTRUCTOR) {
                echo '<form method=post class="navbar-form navbar-left" role="search">';
                echo '<div class="input-group">';
                echo '    <input type="hidden" class="form-control" placeholder="Password" name="password" id="password">';
                echo '    <div class="input-group-btn">';
                echo '        <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-lock"></i> Logoff</button>';
                echo '    </div>';
                echo '</div>';
                echo '</form>';
                echo "<li><a href='#'>Version 4.".CALENDAR_VERSION."</a></li>";
              } else {
                #echo "<li><a href='#'>Logon as Administrator</a></li>";
                echo '<form method=post class="navbar-form navbar-left" role="search">';
                echo '<div class="input-group">';
                echo '    <input type="password" class="form-control" placeholder="Password" name="password" id="password">';
                echo '    <div class="input-group-btn">';
                echo '        <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-lock"></i></button>';
                echo '    </div>';
                echo '</div>';
                echo '</form>';
              }

              echo '</ul>';
              echo '</li>';

              foreach ($navbar_dropdowns as $type => $icon) {
            ?>
            <li class="dropdown">
              <a href="#" title="Select <?php echo $type; ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="glyphicon <?php echo $icon; ?>" aria-hidden="true"></span>
                <?php echo ucwords($type); ?><span class="caret"></span></a>
              <ul class="dropdown-menu  scrollable-menu">
                <?php
                  foreach ($events[$type] as $cname => $cdata) {
                    if (isset($cdata['box']['title']) || $INSTRUCTOR) {
                      if (isset($_GET['type']) && isset($_GET['event']) && $_GET['type'] == $type && $_GET['event'] == $cname) {
                        echo "<li><a href='calendar.php?type=$type&event=$cname'><font color='black'><b>$cname - ".$cdata['name']."</b></font></a></li>";
                      } else {
                        echo "<li><a href='calendar.php?type=$type&event=$cname'>$cname - ".$cdata['name']."</a></li>";
                      }
                    } else {
                      echo "<li><a title='Material not online at this time' href='#'><font color='#AAAAAA'>$cname - ".$cdata['name']."</font></a></li>";
                    }
                  }
                ?>
              </ul>
            </li>
            <?php
              }
            ?>

          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

  <!-- End TopBar and CSS Stuff! -->
