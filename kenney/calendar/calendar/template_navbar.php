<?php
  # Set Default Title / Information
  if (!isset($PAGE_TITLE)) {$PAGE_TITLE = 'Page Title'; }
  if (!isset($NAVBAR_TITLE)) {$NAVBAR_TITLE = 'NavBar Title'; }
  if (!isset($NAVBAR_TITLE_URL)) {$NAVBAR_TITLE_URL = '#'; }
?>

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

            <li><a href="<?php echo $NAVBAR_TITLE_URL; ?>">
                <?php echo $NAVBAR_TITLE; ?></a></li>

            <?php

              foreach ($NAVBAR as $i => $navbar_item) {
                # Provide a clickable navbar option
                if ($navbar_item['type'] == 'url') {
                  echo "<li><a title='".$navbar_item['title']."' href='".$navbar_item['url']."'>";
                  if (isset($navbar_item['ltext'])) {
                    echo $navbar_item['ltext'];
                  }
                  if (isset($navbar_item['icon'])) {
                    echo "<span class='glyphicon ".$navbar_item['icon']."' aria-hidden='true'></span>";
                  }
                  if (isset($navbar_item['rtext'])) {
                    echo $navbar_item['rtext'];
                  }
                  if (isset($navbar_item['caret'])) {
                    echo '<span class="caret"></span>';
                  }
                  echo "</a></li>";
                # Allow direct text output
                } elseif ($navbar_item['type'] == 'direct') {
                  echo $navbar_item['text'];
                # Seperate left from right on the navbar
                } elseif ($navbar_item['type'] == 'seperator') {
                  echo '</ul><ul class="nav navbar-nav navbar-right">';
                # Allow for drop down menus within the navbar
                } elseif ($navbar_item['type'] == 'dropdown') {
                  echo '<li class="dropdown">';
                  echo '<a href="#" title="'.$navbar_item['title'].'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';
                  if (isset($navbar_item['ltext'])) {
                    echo $navbar_item['ltext'];
                  }
                  if (isset($navbar_item['icon'])) {
                    echo "<span class='glyphicon ".$navbar_item['icon']."' aria-hidden='true'></span>";
                  }
                  if (isset($navbar_item['rtext'])) {
                    echo $navbar_item['rtext'];
                  }
                  if (isset($navbar_item['caret'])) {
                    echo '<span class="caret"></span>';
                  }
                  echo '</a>';
                  echo '<ul class="dropdown-menu  scrollable-menu">';
                  foreach ($navbar_item['options'] as $ii => $row) {
                    if ($row['type'] == 'seperator') {
                      echo '<li role="separator" class="divider"></li>';
                    } elseif ($row['type'] == 'header') {
                      echo '<li class="dropdown-header">'.$row['text'].'</li>';
                    } elseif ($row['type'] == 'url') {
                      echo "<li><a title='".$row['title']."' href='".$row['url']."'>".$row['text']."</a></li>";
                    } elseif ($row['type'] == 'direct') {
                      echo $row['text'];
                    }
                  }
                  echo '</ul>';
                  echo '</li>';
                }
              }

            ?>
          </ul>
        </div> <!--/.nav-collapse -->
      </div>
    </nav>

    <!-- End TopBar and CSS Stuff! -->
