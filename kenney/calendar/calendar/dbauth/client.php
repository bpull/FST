<?php

#################################################################################
# Authentication - Version 3.1 - 20151113

# Load in setting variables
if (!defined('AUTH_SERVER')) {
  require_once("config.inc.php");
}

# MySQL login information
require_once('mysql.inc.php');    # MySQL Library

# Location of Authentication script
$LOGIN_ADDRESS = AUTH_SERVER;
$LOGIN_TITLE = AUTH_TITLE;
$LOGIN_MESSAGE = AUTH_MESSAGE;

# If running on development server, use dev logon system.
if (gethostname() == DATABASE_DEV_HOSTNAME) {
  $LOGIN_ADDRESS = DATABASE_DEV_LOGIN_SCRIPT;
}

# Default tokencode for use with naval academy login
$default_tokencode = TOKENCODE;

# Token Code identification to allow for multiple users of the authentication system
$id_tokencode = TOKENID;

# Allow guests (does not autoredirect to the login pages)
if (!isset($allow_guest)) {
    $allow_guest = ALLOW_GUEST_SESSIONS;        # Set to true to allow guests (non logged in users)
}

# Force logon (if not logged in) based on web page $_GET
if (isset($_GET['logon'])) {
    $allow_guest = false;
}

# Returned Tokens are valid for a specific length of time (seconds)
$default_valid_token = TOKEN_VALID_TIME_SECONDS;

# Time since last logon that is allowed
$default_valid_login = MAX_TIME_SINCE_LAST_LOGIN;

# Time that a specific session is allowed to exist
$default_valid_session = MAX_TIME_SESSION;

#################################################################################
# Authentication Variables - Store a users login credentials
# These are the variables to check once this authentication script completes.
# <THESE ARE RESET FURTHER DOWN IN THE CODE!>
$user = 'no-one';
$admin = false;
$instructor = false;
$guest = false;
$access = array();
$display_department = "";
$display_fullname = "";
$display_firstname = "";
$display_lastname = "";

#################################################################################
# Start PHP Sessions - This should appear in no other PHP script (save login.php)
session_start();

#################################################################################
# Logoff the user, if requested, by overwriting the id field in auth_user
if ((isset($_SESSION['user']) && isset($_GET['logoff'])))
{

    # Retrieve user information from session
    $user = $_SESSION['user'];
    $token = session_id();

    # Build delete user query
    $query = "DELETE FROM auth_session WHERE id=? AND user=?";

    # Build MySQL PREPARE statement
    $stmt = $db->stmt_init();
    $stmt->prepare($query);
    $stmt->bind_param('ss', $token, $user);

    # Execute MySQL PREPARE statement
    $result = $stmt->execute();

    # Close the MySQL statement
    $stmt->close();

    # Remove the user variable
    unset($_SESSION['user']);

    # Destroy the current session information (force logoff)
    session_destroy();
    session_start();
}

#################################################################################
# Has data been submitted by the academy login system?  Verify username / token
if (isset($_GET['token']) && isset($_GET['user']) && isset($_GET['date']) &&
    (!isset($_SESSION['token']) || $_SESSION['token'] != $_GET['token'])) {

    # Retrieve submitted username and password combination (from web form)
    $user = $_GET['user'];
    $token = $_GET['token'];
    $dtg = $_GET['date'];
    $display_fullname = urldecode($_GET['fullname']);
    $display_firstname = urldecode($_GET['first']);
    $display_lastname = urldecode($_GET['last']);
    $display_department = urldecode($_GET['dept']);
    $correct_token = sha1($user . $default_tokencode . $dtg);
    $timediff = abs(intval($dtg)-time());
    $_POST['staylogedon'] = 1;

    $returned_user = "";
    if ($correct_token == $token && $timediff < $default_valid_token)
    {
        # AUTHENTICATED USER!
        $returned_user = $user;
    }

    # If user credentials were good, then:
    # - Store the session id in the database as the validity token (id)
    # - Retrieve and restore their last set of session data

    if ($returned_user != "")
    {

        # Build appropriate session information
        $_SESSION['user'] = $returned_user;

        $session = session_encode();
        $token = session_id();
        $token_time = time();

        # Add user information to database session table
        $query = "INSERT INTO auth_user (user, displayname, first, last, department, session) VALUES(?,?,?,?,?,?)";

        # Build MySQL PREPARE statement
        $stmt = $db->stmt_init();
        $stmt->prepare($query);
        $stmt->bind_param('ssssss', $returned_user, $display_fullname, $display_firstname, $display_lastname, $display_department, $session);

        # Execute MySQL PREPARE statement
        $result = $stmt->execute();

        # Update a user's information in the database session table
        $query2 = "UPDATE auth_user SET displayname=?, department=?, login=? WHERE user=?";

        # Build MySQL PREPARE statement
        $stmt2 = $db->stmt_init();
        $stmt2->prepare($query2);
        $stmt2->bind_param('ssss', $display_fullname, $display_department, $token_time, $returned_user);

        # Execute MySQL PREPARE statement
        $result = $stmt2->execute();

        # Retrieve session variables
        $query3 = "SELECT session FROM auth_user WHERE user=?";

        # Build MySQL PREPARE statement
        $stmt3 = $db->stmt_init();
        $stmt3->prepare($query3);
        $stmt3->bind_param('s', $returned_user);

        # Execute MySQL PREPARE statement
        $result = $stmt3->execute();

        # Provide Error Information
        if (!$result || $db->affected_rows == 0) {
            /*For debugging purposes, display the error - not nice, but effective*/
            echo '<h1>ERROR: '. $db->error . " for query *$query*</h1><hr>";
        }

        # Retrieve old session information
        $stmt3->bind_result($session);
        while($stmt3->fetch())
        {
            session_decode($session);
        }

        # close PREPARED statements
        $stmt->close();
        $stmt2->close();
        $stmt3->close();

        # Update a user's information in the database session table
        $query4 = "INSERT INTO auth_session (user, id, lastvisit) VALUES (?, ?, ?)";

        # Build MySQL PREPARE statement
        $stmt4 = $db->stmt_init();
        $stmt4->prepare($query4);
        $stmt4->bind_param('sss', $returned_user, $token, $token_time);

        # Execute MySQL PREPARE statement
        $result = $stmt4->execute();

        # Update a user's information in the database session table
        $query5 = "UPDATE auth_session SET lastvisit=? WHERE user=? AND id=?";

        # Build MySQL PREPARE statement
        $stmt5 = $db->stmt_init();
        $stmt5->prepare($query5);
        $stmt5->bind_param('sss', $token_time, $returned_user, $token);

        # Execute MySQL PREPARE statement
        $result = $stmt5->execute();

        # Force the user back to the correct answer, just in case.
        $_SESSION['user'] = $returned_user;

    }
}

#################################################################################
# Authentication Variables - Store a users login credentials
# These are the variables to check once this authentication script completes.
$user = 'no-one';
$admin = false;
$instructor = false;
$guest = false;
$access = array();
$display_department = "";
$display_fullname = "";
$display_firstname = "";
$display_lastname = "";

#################################################################################
# Verify valid logon session
if (true)
{
    # Retrieve submitted username and password combination
    $returned_user = 'no-one';
    $token = session_id();

    # Build authentication query
    # stay logged on is now the default ==> relogin without authentication
    $query = "SELECT user, session, login, displayname, department, lastvisit
                FROM auth_user
                  LEFT JOIN auth_session USING(user)
                WHERE auth_session.id=?";

    # Build MySQL PREPARE statement
    $stmt = $db->stmt_init();
    $stmt->prepare($query);
    $stmt->bind_param('s', $token);

    # Execute MySQL PREPARE statement
    $result = $stmt->execute();

    # Verify logon information
    if (!$result || $db->affected_rows == 0)
    {
        /*For debugging purposes, display the error - not nice, but effective*/
        echo '<h1>ERROR: '. $db->error . " for query *$query*</h1><hr>";
    }

    # There was a logged-on entry for this user!
    if ($result)
    {
        $stmt->bind_result($returned_user, $returned_session, $returned_token_time, $returned_displayname, $returned_department, $returned_last_visit);
        while($stmt->fetch())
        {
          if (((intval($returned_token_time) + $default_valid_login) > time()) &&
             (((intval($returned_last_visit) + $default_valid_session) > time()))) {
            $display_department = $returned_department;
            $display_fullname = $returned_displayname;
            $user = $returned_user;
          }
        }

        # Are they an instructor/staff member?
        # Search for m123456 <- and do not promote to instructor
        if (preg_match("/^M[0-9]{6}/", $returned_user) == 0 && preg_match("/^m[0-9]{6}/", $returned_user) == 0 && $returned_user != 'no-one') {
            $instructor = 1;
        }

        # Retrieve Access Flags
        $query3 = "SELECT access, value FROM auth_access WHERE user=?";
        $stmt3 = $db->stmt_init();
        $stmt3->prepare($query3);
        $stmt3->bind_param('s', $user);
        $result = $stmt3->execute();
        $stmt3->bind_result($returned_access, $returned_value);
        while($stmt3->fetch())
        {
          if(!isset($access[$returned_access])) {
            $access[$returned_access] = array();
          }
          $access[$returned_access][] = $returned_value;
        }
        $stmt3->close();
    }

    # End the MySQL PREPARE statement
    $stmt->close();

    # Restore or Save SESSION variables to database
    if ($user != 'no-one' && !isset($_SESSION['user']))
    {
        # Restore session from database
        session_decode($returned_session);
        $_SESSION['user'] = $user;
    } elseif ($user != 'no-one') {
        # Save session into database
        # Update a user's information in the database session table
        $query2 = "UPDATE auth_user SET session=? WHERE user=?";
        $session_var = session_encode();
        $token_time = time();

        # Build MySQL PREPARE statement
        $stmt2 = $db->stmt_init();
        $stmt2->prepare($query2);
        $stmt2->bind_param('ss', $session_var, $user);

        # Execute MySQL PREPARE statement
        $result = $stmt2->execute();

        # Close MySQL PREPARE statement
        $stmt2->close();

        # Update session times
        $query2 = "UPDATE auth_session SET lastvisit=? WHERE id=?";
        $session_var = session_encode();
        $token_time = time();

        # Build MySQL PREPARE statement
        $stmt2 = $db->stmt_init();
        $stmt2->prepare($query2);
        $stmt2->bind_param('ss', $token_time, $token);

        # Execute MySQL PREPARE statement
        $result = $stmt2->execute();

        # Close MySQL PREPARE statement
        $stmt2->close();
    }

    # Check to see if the user has the admin flag set in the auth_access table.
    if (isset($access['admin'])) {
      $admin = true;
    }
}

#################################################################################
# Present login screen if user is not valid
# Provide the login screen with the page that called it
# via ?source=CURRENT_PAGE, so that after
# login it will return to the page that requested authentication.
if (!isset($_SESSION['user']) || $_SESSION['user'] == 'no-one' || $user == 'no-one')
{
   if ($allow_guest) {
        $guest = true;
        $instructor = false;
        $admin = false;
   } else {
        # Determine input variables
        $myURI = $_SERVER['REQUEST_URI'];
        $myURI = explode("?", $myURI);
        $myURI = $myURI[0];

        # Lets login
        $location = "Location: $LOGIN_ADDRESS?source=" . $myURI . "&server=" . $_SERVER['SERVER_NAME'] . "&display=" . urlencode($LOGIN_MESSAGE) . "&title=" . urlencode($LOGIN_TITLE) . "&tokenid=" . urlencode($id_tokencode);
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
        {
            $location .= "&https=on";
        }
        header($location);

        # If failure of redirect...
        $user = 'no-one';
        $admin = false;
        $instructor = false;
        $guest = false;
        $access = array();
        $display_department = "";
        $display_fullname = "";
        $display_firstname = "";
        $display_lastname = "";
   }
}

# Determine page to redirect to when changing courses or projects
$myURI = $_SERVER['REQUEST_URI'];
$myURI = explode("?", $myURI);
$myURI = $myURI[0];

// # Required Database Tables Below!!!
// # Database configuration for use with this authentication script
// DROP TABLE IF EXISTS auth_access;
// DROP TABLE IF EXISTS auth_session;
// DROP TABLE IF EXISTS auth_user;
// CREATE TABLE `auth_user` (
//   `user` VARCHAR(45) NOT NULL,
//   `displayname` VARCHAR(250) NULL,
//   `first` VARCHAR(250) NULL,
//   `last` VARCHAR(250) NULL,
//   `department` VARCHAR(250) NULL,
//   `session` VARCHAR(60000) NULL,
//   `login` BIGINT NULL,
//   CONSTRAINT PK_auth_user PRIMARY KEY (user));
//
// # Store Session IDs of successfully logged on instances
// DROP TABLE IF EXISTS auth_session;
// CREATE TABLE auth_session (
//  `user` VARCHAR(99) NOT NULL,
//  `id` VARCHAR(99) NOT NULL,
//  `lastvisit` bigint NULL,
//  CONSTRAINT PK_auth_session PRIMARY KEY (user, id),
//  CONSTRAINT FK_auth_session_user FOREIGN KEY(user)
//   REFERENCES auth_user (user)
//   ON DELETE CASCADE ON UPDATE CASCADE);
//
// # Store any specific accesses this user has beyond being a simple student/instructor
// DROP TABLE IF EXISTS auth_access;
// CREATE TABLE `auth_access` (
//   `user` VARCHAR(45) NOT NULL,
//   `access` VARCHAR(250) NOT NULL,
//   `value` VARCHAR(250) NOT NULL,
//   CONSTRAINT PK_auth_access PRIMARY KEY (user, access, value),
//   CONSTRAINT FK_auth_access_user FOREIGN KEY(user)
//     REFERENCES auth_user (user)
//     ON DELETE CASCADE ON UPDATE CASCADE);

// Become another user
if ($admin && isset($_REQUEST['become'])) {
  unset($_SESSION['admin-user-override']);
  unset($_SESSION['admin-instructor-override']);
  $_SESSION['admin-user-override'] = $_REQUEST['become'];
}

// Cancel Administrative override
if (isset($_REQUEST['end-override']) || isset($_REQUEST['end-become'])) {
  if (isset($_SESSION['admin-user-override'])) {
    unset($_SESSION['admin-user-override']);
    unset($_SESSION['admin-instructor-override']);
  }
}

// Become another user
// For Debugging as necessary!
if ($admin && isset($_SESSION['admin-user-override'])) {
  $user = $_SESSION['admin-user-override'];
  $admin = false;
  $instructor = false;
  if (preg_match("/^M[0-9]{6}/", $user) == 0 && preg_match("/^m[0-9]{6}/", $user) == 0 && $user != 'no-one') {
      $instructor = 1;
  }
  $guest = false;
  $access = array();
  $display_department = "USNA";
  $display_fullname = "Administrator Override";
  $display_firstname = "John Paul";
  $display_lastname = "Jones";
}

?>
