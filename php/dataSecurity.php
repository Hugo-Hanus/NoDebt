<?php
namespace dataSecurity;

      function secureString($text){

        return htmlentities(htmlspecialchars(strip_tags(trim($text))));

    }

    function generateNewMDP() {

      $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      return substr(str_shuffle($chars),0,8);
  
  }


?>