<?php
  class myConnectDB extends mysqli{
    public function __construct($hostname="mope.academy.usna.edu",
        $user="m172412",
        $password="DATA",
        $dbname="m172412"){
      parent::__construct($hostname, $user, $password, $dbname);
    }
  }
?>
