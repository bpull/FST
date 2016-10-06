<?php

  $allow_guest = true;
  require_once("client.php");

  echo "Auth Info <br /><br />";

  echo "user=$user<br />admin=$admin<br />instructor=$instructor<br />guest=$guest<br />";
  echo "<pre>Auth:";
  print_r($access);
  echo "</pre>";
  echo "dept=$display_department<br />fullname=$display_fullname<br />first=$display_firstname<br />last=$display_lastname<br /><br />";

  echo "<a href='test.php?logon=1'>logon</a> <a href='test.php?logoff=1'>logoff</a>";

 ?>
