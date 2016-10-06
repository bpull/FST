<?php

# Login version 3.04 - 20151110

# Load in setting variables
require_once("config.inc.php");

# Authentication token
$default_tokencode = TOKENCODE;
$default_tokenid = TOKENID;     # <- Not yet implemented!!!
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
    $source = $_POST['source'];
    $srcsrv = $_POST['server'];
    if (isset($_GET['source']))
    {
        $source = $_GET['source'];
	      $srcsrv = $_GET['server'];
    }
}

if (isset($_POST['title'])) {
  $_GET['title'] = $_POST['title'];
}

if (isset($_POST['display'])) {
  $_GET['display'] = $_POST['display'];
}

if (isset($_REQUEST['password']))
{

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

    $show_message = 'Usename / Password Combination was Incorrect';

    if($AUTH->_error == "") {
        // Successfully Authenticated!
        $userid = $AUTH->user();
        $userdata = $AUTH->userInfo();
        $fullname = urlencode($userdata['displayname']);
        $fulldept = urlencode($userdata['department']);
        $fulllast = urlencode($userdata['sn']);
        $fullfirst = urlencode($userdata['givenname']);
        $token = sha1($userid . $default_tokencode . $default_time);

        #echo "<hr>";
        #print_r($userdata);
        #echo "<hr>";
        header("Location: $return_address$srcsrv$source?token=$token&user=$userid&date=$default_time&fullname=$fullname&first=$fullfirst&last=$fulllast&dept=$fulldept");

        #echo "AUTHENTICATED as $userid !!!";
        #echo "TOKEN CODE=$token";
        #    $password = sha1($_POST['password'].$_POST['user']);
        #$default_login = "http://zee.academy.usna.edu/~kenney/submit/login/login.php";
        #$default_tokencode = "theworldishappy3399113";
        #$default_time = time();
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

echo '<div class="container">
       <div class="row">
        <div class="col-md-4">
         <div class="jumbotron">';

echo " <h4>$addlinfo</h4>";
if (isset($show_message)) {
  echo "<h4><font color='red'>$show_message</font></h4>";
}

echo ' <h2 class="form-signin-heading">Please sign in</h2>
       <label for="inputEmail" class="sr-only">Email address</label>
       <input type="text" name="login" class="form-control" placeholder="Username" required autofocus>
       <label for="inputPassword" class="sr-only">Password</label>
       <input type="password" name="password" class="form-control" placeholder="Password" required>
       <br>
       <button class="btn btn-primary btn-block" type="submit">Sign in</button>
      </p>
    </div>
  </div>';

# Display required disclaimer

echo '
<div class="col-md-8">
 <div class="jumbotron">
  <p>
   <b>You are accessing a U.S. Government (USG) Information System (IS)
      that is provided for USG-authorized use only.
   </b>
   <br>
   <br>
   By using this IS (which includes any device attached to this IS),
   you consent to the following conditions:
   <ol>
    <li>The USG routinely intercepts and monitors communications on this IS for
    purposes including, but not limited to, penetration testing, COMSEC monitoring,
    network operations and defense, personnel misconduct (PM), law enforcement (LE),
    and counterintelligence (CI) investigations.</li>

    <li>At any time, the USG may inspect and seize data stored on this IS.</li>

    <li>Communications using, or data stored on, this IS are not private, are
    subject to routine monitoring, interception, and search, and may be disclosed or
    used for any USG-authorized purpose.</li>

    <li>This IS includes security measures (e.g., authentication and access controls)
    to protect USG interests--not for your personal benefit or privacy.</li>

    <li>Notwithstanding the above, using this IS does not constitute consent to PM,
    LE or CI investigative searching or monitoring of the content of privileged
    communications, or work product, related to personal representation or services
    by attorneys, psychotherapists, or clergy, and their assistants. Such
    communications and work product are private and confidential. See User Agreement
    for details.</li>
   </ol>
  </p>
 </div>
</div>
</div>
</div>

</div> <!-- /container -->
</body>
</html>';

?>
