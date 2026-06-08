<?php
   function clean($str){
        $str = @trim($str);
        $str = stripslashes($str);
        return $str;
  }
?>
