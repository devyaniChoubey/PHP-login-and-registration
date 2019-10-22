<?php 
function clean($string){
   return htmlentities($string);
}

function redirect($location){
    return header("Location : {$location}");
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
     if(strlen($email) > $max){
        $errors[] =  "Your email is greater than {$max} characters";
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
             echo $error;  
          }
      }else{
        if(register_user($username , $email , $first_name ,
         $last_name , $password)){
             echo "User Registered";
        }
      }

    }
}


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
       $validation_code = md5($username + microtime());
       $sql = "INSERT INTO users(first_name , last_name , username , password , validation_code , active, email) VALUES('$first_name','$last_name','$username','$password','$validation_code',0,'$email')";
       
       $result = query($sql);
       confirm($result);
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


?>