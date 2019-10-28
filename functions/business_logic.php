<?php
    function getUserDetails($email) {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = query($sql);
        confirm($result);
        $row = fetch_array($result);
        return $row;
    }

    function getProfProjects($email) {
        $sql = "SELECT * FROM projects WHERE prof_email = '$email'";
        $result = query($sql);
        confirm($result);
        $data=array();
        while($row = mysqli_fetch_array($result)){
            $data[]=$row;
        }
        mysqli_free_result($result);
        return $data;
    }

?>