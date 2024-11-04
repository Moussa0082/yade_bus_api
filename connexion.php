<?php 

try {
    $db = new PDO('mysql:host=localhost; dbname=yade','root','p0f2R4T3J9U4o1o');
    // echo'Connexion etablie !';
} catch (Exception $e) {
    die($e->getMessage()); 
}

?>