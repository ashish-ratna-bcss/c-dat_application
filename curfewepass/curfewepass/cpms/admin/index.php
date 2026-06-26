<?php
session_start();
error_reporting(0);
require_once('dbcontroller.php');
$db_handle = new DBController();
$login_error = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $query = "SELECT id FROM tbladmin WHERE username='$username' AND password='$password'";
    $results = $db_handle->runQuery($query);
    if (!empty($results)) {
        $_SESSION['cpmsaid'] = $results[0]['id'];
        header('location:dashboard.php');
        exit;
    }
    $login_error = 'Invalid username or password.';
}
?>

<!DOCTYPE html>
<html>

<head>
 
    <title>Ganesh Pass Management System | Login Page</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
   <link href="assets/css/style.css" rel="stylesheet" />
   <link href="assets/css/main-style.css" rel="stylesheet" />

</head>

<body class="body-Login-back">

    <div class="container">
       
        <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center logo-margin ">
              <h3 style="color: white;">Curfew e-Pass Management System</h3>
                </div>
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">                  
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign In</h3>
                    </div>
                    <div class="panel-body">
                        <form role="form" method="post" name="login">
                            <fieldset>
                                <div class="form-group">
                                    <label for="login-username">Username</label>
                                     <input type="text" class="form-control"  required="true" name="username" value="<?php if(isset($_COOKIE["user_login"])) { echo $_COOKIE["user_login"]; } ?>">
                                                
                                </div>
                                <div class="form-group">
                                    <label for="login-password">Password</label>
                                    <input type="password" class="form-control" name="password" required="true" value="<?php if(isset($_COOKIE["userpassword"])) { echo $_COOKIE["userpassword"]; } ?>">
                                                
                                </div>
                                <div class="checkbox">
                                  
                                        <input type="checkbox" id="remember" name="remember" <?php if(isset($_COOKIE["user_login"])) { ?> checked <?php } ?> />
                <label for="keep_me_logged_in">Keep me signed in</label>
                                   

<label style="padding-left: 40px">
    <a href="forgot-password.php">Lost Password?</a></label>
                                </div>

                                <!-- Change this to a button or input when using this as a form -->
                               
                                <input type="submit" value="Login" class="btn btn-lg btn-success btn-block" name="login" >
                            </fieldset>
                        </form>
                        <?php if ($login_error !== '') { ?>
                        <p class="text-danger text-center"><?php echo htmlspecialchars($login_error); ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>

</body>

</html>
