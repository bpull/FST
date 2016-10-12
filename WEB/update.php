<?php
    session_start();
    require_once('mysql.inc.php');

    $db = new myConnectDB();
    $phrase = "pleaserefrainfromfallingdowntherabbithole";

    if (mysqli_connect_errno())
    {
        echo "<h5>ERROR: " . mysqli_connect_errno() . ": " . mysqli_connect_error() . " </h5><br>";
    }
    $newval = $_POST['spent'] + $_POST['used'];
    $query="UPDATE fgst_data SET used = ? WHERE user = ?";

    $stmt = $db->stmt_init();
    $stmt->prepare($query);
    $stmt->bind_param('ss', $newval,$_SESSION['user']);
    $result = $stmt->execute();
    header("Location: index.php");

?>
