<?php

  # Database Configuration (Production Database)
  define('DATABASE_DB_HOST', 'XXXX.academy.usna.edu');
  define('DATABASE_USER', 'dbuser');
  define('DATABASE_PW', 'dbpassword');
  define('DATABASE_DB_NAME', 'dbname');

  # Development System information (Development Database)
  # Note: you will need to manually edit devlogin to specify
  #       valid IP Addresses!!!
  # Note 2: This was designed for off-site development!
  define('DATABASE_DEV_HOSTNAME', 'precision');
  define('DATABASE_DEV_DB_HOST', 'localhost');
  define('DATABASE_DEV_USER', 'dbuser');
  define('DATABASE_DEV_PW', 'dbpassword');
  define('DATABASE_DEV_DB_NAME', 'dbname');
  define('DATABASE_DEV_LOGIN_SCRIPT', '../../calendar/dbauth/devlogin.php');

  # Authentication key, and token id
  define('TOKENCODE', 'random-string-lkjdfl3924902klsjf');
  define('TOKENID', '1');

  # Authentication settings
  define('TOKEN_VALID_TIME_SECONDS',100);
  define('MAX_TIME_SINCE_LAST_LOGIN', 486400);
  define('MAX_TIME_SESSION', 486400);
  define('ALLOW_GUEST_SESSIONS', false);

  # Location of authentication server.php
  define('AUTH_SERVER','https://www.usna.edu/Users/cs/kenney/auth/server.php');

  # Login Server Data
  define('AUTH_TITLE', 'CS Dept System');
  define('AUTH_MESSAGE', 'Please log onto the Computer Science Department System');

 ?>
