<?php
#start session
#TODO returieve old session data
session_start();

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
          <form action="index.php" method="get">
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
