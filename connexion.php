<?php 

try {
    $db = new PDO('mysql:host=localhost; dbname=yade','root','');
    // echo'Connexion etablie !';
} catch (Exception $e) {
    die($e->getMessage()); 
}

// faire disparaitre les voyages dont le nombre de place sont fini 
//Nom
// prenom
// numero 
// adresse
// nbre de place 1
?>