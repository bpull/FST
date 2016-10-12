<?php
  session_start();
  require_once('mysql.inc.php');
  $db = new myConnectDB();
  $phrase = "pleaserefrainfromfallingdowntherabbithole";

  if (mysqli_connect_errno())
  {
      echo "<h5>ERROR: " . mysqli_connect_errno() . ": " . mysqli_connect_error() . " </h5><br>";
  }
  ?>
  <html>
  <head>
      <script>
        function logout() {
            document.location = "logout.php";
        }
      </script>
  </head>
  <body>

  <?php
  if(isset($_SESSION['user']) && isset($_SESSION['access'])){
      if($_SESSION['access'] == "user"){
        $query="SELECT original,used FROM fgst_data WHERE user = ?";
        $stmt = $db->stmt_init();
        $stmt->prepare($query);
        $stmt->bind_param('s', $_SESSION['user']);
        $result = $stmt->execute();
        $stmt->bind_result($original,$used);
        $stmt->fetch();
        $stmt->close();
        ?>
        <button type="button" style="position:absolute;top:10px;right:10px;" onclick="logout()">Logout</button>
          <div style="margin:auto;width:50%;text-align:center">
            <h1>
              Hello <?php echo $_SESSION['user']; ?>!
            </h1>
          </div>
          <div style="margin:auto;width:50%;text-align:center">
            <h1>
              You started with $<?php echo $original; ?>
            </h1>
          </div>
          <div style="margin:auto;width:50%;text-align:center">
            <h1>
              and have now spent $<?php echo $used; ?>
            </h1>
          </div>
          <div style="margin:auto;width:50%;text-align:center">
            <h1>
              Which leaves you with $<?php echo $original-$used; ?>!
            </h1>
          </div>
          <div style="margin:auto;width:50%;text-align:center">
              <h1>
                  Have you spent anything recently?
              </h1>
          </div>
          <div style="margin:auto;width:50%;text-align:center">
              <form method="post" action="update.php">
                  <input type="text" name="spent" id="spent">
                  <input type="hidden" name="used" id ="used" value="<?php echo"$used" ?>">
                  <input type="submit" value="Update">
              </form>
          </div
        <?php
      }
      elseif ($_SESSION['access'] == 'admin') {
        $query="SELECT user,original,used FROM fgst_data";
        $stmt = $db->stmt_init();
        $stmt->prepare($query);
        $result = $stmt->execute();
        $stmt->bind_result($user,$original,$used);
        while($stmt->fetch()){
          $now = $original - $used;
          echo "<h1>$user started with $$original and used $$used so they now have $$now</h1>";
        }

        $stmt->close();
      }
    }
    else{
      header("Location: index.php");

    }


?>
</body>
</html>
