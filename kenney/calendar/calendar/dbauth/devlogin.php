<?php

############################################################
############################################################
##
## DEVELOPER LOG ON - NOT FOR NORMAL USE
##
## This overrides all password requirements if the hostname
## and IP address requirements below are met!
##
############################################################
############################################################

# Load in setting variables
if (!defined('AUTH_SERVER')) {
  require_once("config.inc.php");
}

if (gethostname() != DATABASE_DEV_HOSTNAME) {
    echo "Unauthorized Host";
    die;
} elseif ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&
    $_SERVER['REMOTE_ADDR'] != '192.168.2.97' &&
    $_SERVER['REMOTE_ADDR'] != '192.168.2.98' &&
    $_SERVER['REMOTE_ADDR'] != '192.168.2.99' &&
    $_SERVER['REMOTE_ADDR'] != '192.168.2.96') {
    echo "Unauthorized IP Address";
    die;
}

############################################################
############################################################
##
## DEVELOPER LOG ON - NOT FOR NORMAL USE
##
############################################################
############################################################

# Login version 3.04 - 20151110

# Authentication token
$default_tokencode = TOKENCODE;
$default_time = time();

# Determine if the forwarding address was http or https
$return_address = "http";
if (isset($_GET['https']) || isset($_POST['https']))
{
  $return_address .= "s";
}
$return_address .= "://";

# grab redirection information (what page redirected us here?)
$source_rcvd = false;
if (isset($_GET['source']) || isset($_POST['source']))
{
    $source_rcvd = true;
    if (isset($_GET['source']))
    {
      $source = $_GET['source'];
      $srcsrv = $_GET['server'];
    } else {
      $source = $_POST['source'];
      $srcsrv = $_POST['server'];
    }
}

if (isset($_POST['title'])) {
  $_GET['title'] = $_POST['title'];
}

if (isset($_POST['display'])) {
  $_GET['display'] = $_POST['display'];
}

if (isset($_REQUEST['password'])) {

  // Successfully Authenticated!
  $userid = $_REQUEST['login'];
  //$userdata = $AUTH->userInfo();
  $fullname = "Developer";
  $fulldept = "Developer Department";
  $fulllast = "LastName";
  $fullfirst = "FirstName";
  $token = sha1($userid . $default_tokencode . $default_time);

  header("Location: $return_address$srcsrv$source?token=$token&user=$userid&date=$default_time&fullname=$fullname&first=$fullfirst&last=$fulllast&dept=$fulldept");

} elseif (false) {

    # An Error has occurred. Otherwise, direct people to log in.
    #echo (@$_REQUEST['error'] && $_REQUEST['error']!='Login'
    #            ?"ERROR: ".$_REQUEST['error']
    #            :(@$this && $this->error() && $this->error()!='Login'
    #                ?"<span class='red'>ERROR: ".@$this->error()
    #                :'Log In'));

    // REQUIRED CONFIG FOR THE TEMPLATE LIBRARIES
    global $CORE, $ROOT, $TEMPLATE, $CONFIGVARS, $AUTH;
    $ROOT = ".";  // this is the relative path to your site root
    $CORE = "/www/htdocs"; // this is the web root directory
    $TEMPLATE = "/templates/standard"; //This is the current template

    //Required Libraries
    require_once $CORE . $TEMPLATE . '/includes/config.php';
    require_once $CORE . $TEMPLATE . '/lib/utils.php';

    ///////////////////////////////////////////////////////////////
    // Authentication
    ///////////////////////////////////////////////////////////////
    include_once $CORE. $TEMPLATE . '/lib/auth.php';
    $customLogin = realpath($ROOT).'/login.php';

    $authConf = array(
        'auto'            => false,
        'logoff'          => @$_REQUEST['logout'],
        'authuser'        => @$_REQUEST['login'],
        'authpass'        => @$_REQUEST['password'],
        'cookie'          => false,
        'LDAP_Anonymous'  => true,
        'loginPage'       => $customLogin,
    );
    //  Start Authentication Process
    $AUTH = new Auth_Usna_External(setLdapConfig($CONFIGVARS->LDAPSERVER, $authConf));
    $AUTH->_debug = false;
    // perform the Authentication
    $AUTH->auth();

    if($AUTH->_error == "") {
        // Successfully Authenticated!
        $userid = $AUTH->user();
        $userdata = $AUTH->userInfo();
        $fullname = urlencode($userdata['displayname']);
        $fulldept = urlencode($userdata['department']);
        $fulllast = urlencode($userdata['sn']);
        $fullfirst = urlencode($userdata['givenname']);
        $token = sha1($userid . $default_tokencode . $default_time);

        header("Location: $return_address$srcsrv$source?token=$token&user=$userid&date=$default_time&fullname=$fullname&first=$fullfirst&last=$fulllast&dept=$fulldept");
    }
}

include_once('css.inc.php');
echo $css_header;

if (isset($_GET['title'])) {
  $title = urldecode($_GET['title']);
  echo "<title>$title</title>";
} else {
  echo "<title>System Login</title>";
}

echo "<form class='form-signin' method=POST>";

if (isset($_GET['https']) || isset($_POST['https'])) {
  $HTTPSMODE = "<input type=hidden name=https value='on'>";
} else {
  $HTTPSMODE = "";
}

if (isset($_GET['title'])) {
  $title = urlencode($title);
  echo "<input type=hidden name=title value='$title'";
}

if (isset($_GET['display'])) {
  $addlinfo = urldecode($_GET['display']);
  $addlinfoq = urlencode($addlinfo);
  echo "<input type=hidden name=display value='$addlinfoq'>";
  echo '<div class="datagrid">';
}

# Display Login Page
echo "<input type=hidden name=source value='$source'>" .
    "<input type=hidden name=server value='$srcsrv'>" . $HTTPSMODE;

echo '   <div class="container">
      <div class="row">
        <div class="col-md-4">
          <div class="jumbotron">';

echo " <h4>$addlinfo</h4>";

echo '
        <h2 class="form-signin-heading">Logon as</h2>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="text" name="login" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="hidden" name="password" class="form-control" placeholder="Password" required>
        <br>
        <button class="btn btn-primary btn-block" type="submit">Sign in</button>
            </p>
          </div>
        </div>';

# Display required disclaimer

echo '        <div class="col-md-8">
          <div class="jumbotron">';

echo "<h2><font color=red>Developer Mode</font></h2>";

echo "<h3>Connecting From " . $_SERVER['REMOTE_ADDR'] . '</h3>';

echo '
            </div>
          </div>
        </div>
      </div>

    </div> <!-- /container -->
  </body>
</html>';

?>
