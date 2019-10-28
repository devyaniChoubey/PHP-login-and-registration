<?php include('phpmail.php');

function clean($string){
   return htmlentities($string);
}

function redirect($location){
    return header("Location : {$location}");
}

function validation_errors($error){
    echo "<div class='alert alert-danger alert-dismissible' role='alert'>
    <button type='button' class='close' data-dismiss='alert'>
        <span aria-hidden='true'>×</span><span class='sr-only'>Close</span>
    </button> Warning!<span>{$error}</span>
    </div>";
}

function set_message($message){
    if(!empty($message)){
        $_SESSION['message'] = $message;
    }else{
        $message = "";
    }
}

function display_message(){
    if(isset($_SESSION['message'])){
        echo $_SESSION['message'];
    }
}

function token_generator(){
    $token = $_SESSION['token'] = md5(uniqid(mt_rand() , true));
     return $token;
}


function send_mail($email, $subject, $msg, $headers)
{
    return send_php_mail($email, $subject, $msg, $headers);
}

/******************Validation Functions**************/
function validate_user_registration(){
    $errors = [];
    $max = 20;
    $min = 3;

    if($_SERVER['REQUEST_METHOD'] == "POST"){
      $first_name = clean($_POST['first_name']);
      $last_name  = clean($_POST['last_name']);
      $username   = clean($_POST['username']);
      $email      = clean($_POST['email']);
      $password   = clean($_POST['password']);
      $confirm_password = clean($_POST['confirm_password']);
        
      if(strlen($first_name) < $min){
         $errors[] =  "Your first name is less than {$min} characters";
      }
      if(strlen($last_name) < $min){
        $errors[] =  "Your last name is less than {$min} characters";
     }
     if(strlen($last_name) > $max){
        $errors[] =  "Your last name is greater than {$max} characters";
     }
     if(strlen($first_name) > $max){
        $errors[] =  "Your first name is greater than {$max} characters";
     }
     
     
     if($password !== $confirm_password){
         $errors[] = "Password do not match";
     }
     if(email_exists($email)){
         $errors[] = "Sorry this email is already taken";
     }
     if(username_exists($username)){
         $errors[] = "Sorry this username is already taken";
     }

      if(!empty($errors)){
          foreach($errors as $error){
             validation_errors($error);
          }
      }else{
        
        register_user($username , $email , $first_name ,
         $last_name , $password);
           
            
        // }else{
        //     set_message('<p class="bg-danger text-center">Sorry we could not register the user</p>');
        //     header('Location: index.php');
        // }
      }

    }
}

/******************Register User Functions**************/
function register_user($username , $email , $first_name , $last_name , $password){
    $first_name = escape($first_name);
    $last_name = escape($last_name);
    $username = escape($username);
    $email = escape($email);
    $password = escape($password);
    
    if(email_exists($email)){
        return false;
    }else if(username_exists($username)){
        return false;
    }else{
       $password = md5($password);
       $validation_code = md5($username);
       $sql = "INSERT INTO users(first_name , last_name , username , password , validation_code , active, email) VALUES('$first_name','$last_name','$username','$password','$validation_code',0,'$email')";
       
       $result = query($sql);
       confirm($result);
       $subject = "Activate Account";
             
       $msg = " <h2>Please click the link below to activate your account<h2>
       <a href=\"http://localhost/login/activate.php?email=$email&code=$validation_code\">Link Here</a>";

       $header = "From : devyanichoubey16@gmail.com";

       if(send_mail($email , $subject , $msg , $header)){
           set_message('<p class="bg-success text-center">Please check your email or spam folder</p>');
           header('Location: index.php');
           echo "User Registered";  
       }else{
        set_message('<p class="bg-danger text-center">Email not sent</p>');
        header('Location: index.php'); 
       }
     
       return true;
    }
}



function email_exists($email){
    $sql = "SELECT id FROM users WHERE email = '$email'";

    $result = query($sql);

    if(row_count($result) == 1){
        return true;
    }else{
        return false;
    }
}

function username_exists($username){
    $sql = "SELECT id FROM users WHERE username = '$username'";

    $result = query($sql);

    if(row_count($result) == 1){
        return true;
    }else{
        return false;
    }
}

/******************User Activation Functions**************/
function activate_user(){
    if($_SERVER['REQUEST_METHOD'] === "GET"){
        if(isset($_GET['email'])){
            echo $email = clean($_GET['email']);
            echo $validation_code = clean($_GET['code']);

            $sql = "SELECT id FROM users WHERE email = '". escape($_GET['email']) ."' AND validation_code = '" .escape($_GET['code']). "'";
            $result = query($sql);
            confirm($result);

            if(row_count($result) == 1){
              $sql2 = "UPDATE users SET active = 1, validation_code = 0 WHERE email = '". escape($email)."' AND validation_code = '". escape($validation_code). "'";
              $result2 = query($sql2);
              confirm($result2);
              set_message('<p class="bg-success text-center">Your account is now successfully activated please login</p>');
              header('Location: login.php');
            }else{
                set_message('<p class="bg-danger text-center">Sorry your account is not activated</p>');
                header('Location: login.php');
               
            }
        }
    }
}

function validate_user_login(){
    $errors = [];
    if($_SERVER['REQUEST_METHOD'] === "POST"){
       

        $email    = clean($_POST['email']);
        $password = clean($_POST['password']);
        $remember = isset($_POST['remember']);

        if(empty($email)){
            $errors[] = "Email field can not be empty";
        }
        if(empty($password)){
            $errors[] = "Password field can not be empty";
        }

        if(!empty($errors)){
            foreach ($errors as $error){
               validation_errors($error);
            }
        }else{
            if(login_user($email , $password, $remember)){
                header('Location: admin.php');
                $_SESSION['email'] = $email;
                
            }else{
                echo "<p class='bg-danger text-center'>Invalid Credentials</p>";
            }
        }
    }

}


function login_user($email , $password , $remember){
      $sql = "SELECT id , password FROM users WHERE email = '". escape($email) ."' AND active = 1";
      $result = query($sql);
      confirm($result);
      if(row_count($result) == 1){
          $row = fetch_array($result);
          $db_password = $row['password'];
          if(md5($password) == $db_password){
             if($remember = "on"){
                 setcookie('email' , $email , time() + 120);
             }
              return true;
          }else{
              return false;
          }
      }else{
          return false;
      }
}

/******************Logged in Functions**************/
function logged_in(){
    if(isset($_SESSION['email']) || isset($_COOKIE['email'])){
        return true;
    }else{
        return false;
    }
}

/******************Password Recover Functions**************/
function recover_password(){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token']){


        $email = clean($_POST['email']);
        if(email_exists($email)){
           $validation_code = md5($email);
           setcookie('temp_access_code', $validation_code , time() + 900);
           
           $sql = "UPDATE users SET validation_code = '".escape($validation_code)."' WHERE email = '".escape($email)."'";
           $result = query($sql);
           confirm($result);

           $subject = "Please reset your password";
           $msg = "<h2>Here is your password reset code</h2> <h1>{$validation_code}</h1>
           <h2>Click here to reset your password</h2> <a href=\"http://localhost/login/code.php?email=$email&code=$validation_code\">Link Here</a>";
           $header = "From : devyanichoubey16@gmail.com";
           send_mail($email , $subject , $msg , $header);
           set_message("<p class='bg-success text-center'>Please check your email or spam folder for password recovery link</p>");
           header('Location: index.php');
        }else{
            echo "<p class='bg-danger text-center'>The email does not exist</p>";
        }
        }else{
            header('Location: index.php');
        }
        if(isset($_POST['cancel-submit'])){
            header('Location: login.php');
        }
    }
}

/*****************Code Validation*************/
function validation_code(){
    if(isset($_COOKIE['temp_access_code'])){
      
          if(!isset($_GET['email']) && !isset($_GET['code'])){
             header('Location: index.php');
          }else if(empty($_GET['email']) || empty($_GET['code'])){
             header('Location: index.php'); 
          }else{
               if(isset($_POST['code'])){
                   $email           = clean($_GET['email']);
                   $validation_code = clean($_POST['code']);
                   $sql = "SELECT id FROM users WHERE validation_code ='".escape($validation_code)."' AND email = '".escape($email)."'";
                   $result = query($sql);
                   
                   if(row_count($result) == 1){
                    setcookie('temp_access_code', $validation_code , time()+300);
                    header("Location: reset.php?email=$email&code=$validation_code"); 
                   }else{
                    set_message("<p class='bg-danger text-center'>Sorry wrong validation code</p>");
             
                   }
               }
             
          }
      
    }else{
        echo "<p class='bg-danger text-center'>Your validation code cookie has expired</p>";
        header('Location: recover.php');
    }
}


/*****************Password Reset *********/
function password_reset(){
    if(isset($_COOKIE['temp_access_code'])){
        if(isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']){
            if(isset($_GET['email']) && isset($_GET['code'])){
              if($_POST['password'] === $_POST['confirm_password']){
                  $updated_password = md5($_POST['password']);
                  $sql = "UPDATE users SET  validation_code = 0 ,active =1, password = '".escape($updated_password)."' WHERE email = '".escape($_GET['email'])."'";
                  query($sql);
                  set_message("<p class='bg-success text-center'>Your password has been successfully updated, please login</p>");
                  header('Location: login.php');
                }
       }}
    }else{
        echo "<p class='bg-danger text-center'>Sorry your reset token has expired</p>";
        header('Location: recover.php');
    }
   

}
?>