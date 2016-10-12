<?php
#start session
#TODO returieve old session data
session_start();
require_once('mysql.inc.php');    # MySQL Connection Library
$db = new myConnectDB();          # Connect to MySQL
$phrase = "pleaserefrainfromfallingdowntherabbithole";

if (mysqli_connect_errno())
{
    echo "<h5>ERROR: " . mysqli_connect_errno() . ": " . mysqli_connect_error() . " </h5><br>";
}


if(isset($_SESSION['user']) || isset($_SESSION['access'])){
    header("Location: user.php");
}


if(isset($_POST['username']) && $_POST['username'] != ""){
  $query="SELECT password FROM auth_pass WHERE user = ?";
  $stmt = $db->stmt_init();
  $stmt->prepare($query);
  $stmt->bind_param('s', $_POST['username']);
  $result = $stmt->execute();
  $stmt->bind_result($pwd);
  $stmt->fetch();
  $stmt->close();
  if(password_verify($_POST['password'],$pwd)){
    $_SESSION['user'] = $_POST['username'];
    $query="SELECT access FROM auth_access WHERE user = ?";
    $stmt = $db->stmt_init();
    $stmt->prepare($query);
    $stmt->bind_param('s', $_POST['username']);
    $result = $stmt->execute();
    $stmt->bind_result($access);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['access'] = $access;
    header("Location: user.php");
  }
  else{
    echo "<script>alert('Username/Password do not match')</script>";
  }
}

 ?>
 <HTML>
    <head>
      <link rel="stylesheet" type="text/css" href="css/login.css">
    </head>
    <body>
      <div class="header_gold"></div>
      <div class="header_main">
        <div class="header_top">
          United States Naval Academy
        </div>
        <div class="header_bottom">
          Financial Grant Status Tracker (FGST) - Prototype
        </div>
        <img class="logo" src="images/logo.png" alt="Academy Crest">
      </div>
      <div id="content"></div>
      <div class="login">
        <div id="login_title">Enter your Username and Password</div>
        <div class="login_user">
          <form action="index.php" method="post">
            <table>
              <tbody>
                <tr>
                  <td>
                    <lable>
                      Username:
                    </lable>
                  </td>
                  <td>
                    <span class="ctrl">
                      <input type="text" name="username" id ="username">
                    </span>
                  </td>
                </tr>
                <tr>
                  <td>
                    Password:
                  </td>
                  <td>
                    <span class="ctrl">
                      <input type="password" name="password" id ="password">
                    </span>
                  </td>
                </tr>
                <tr>
                  <td></td>
                  <td align="right">
                    <input type="submit" value="Login">
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
        <div class="weird_line"><div>
      </div>
    </body>
</HTML>
