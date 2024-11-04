<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connexion.php';

// Récupérer les données de la requête
$idVoyage = isset($_POST['idVoyage']) ? (int)$_POST['idVoyage'] : null;
$nbPlace = isset($_POST['nbPlace']) ? (int)$_POST['nbPlace'] : null;
$passager = isset($_POST['passager']) ? $_POST['passager'] : null;
$telephone = isset($_POST['telephone']) ? $_POST['telephone'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;

// Vérification des paramètres requis
if ($telephone === null) {
    echo json_encode(["error" => "Le numéro de téléphone est  requis."]);

    exit();
}

try {
    // Vérifier le nombre de places disponibles dans la table voyage
    $stmt = $db->prepare("SELECT nbPlace FROM voyage WHERE idVoyage = :idVoyage");
    $stmt->bindParam(':idVoyage', $idVoyage, PDO::PARAM_INT);
    $stmt->execute();

    $voyage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$voyage) {
        echo json_encode(["error" => "Voyage non trouvé."]);
        exit();
    }

    // Vérifier si le nombre de places demandées est inférieur ou égal aux places disponibles
    if ($nbPlace > $voyage['nbPlace']) {
        echo json_encode(["error" => "Le nombre de places demandées dépasse le nombre de places disponibles."]);
        exit();
    }

    // Créer la réservation en incluant les détails du passager
    $stmt = $db->prepare("INSERT INTO reservations (idVoyage, etat, nbPlace, passager, telephone, email, dateReserv) 
                            VALUES (:idVoyage, 'en attente', :nbPlace, :passager, :telephone, :email, NOW())");
    $stmt->bindParam(':idVoyage', $idVoyage, PDO::PARAM_INT);
    $stmt->bindParam(':nbPlace', $nbPlace, PDO::PARAM_INT);
    $stmt->bindParam(':passager', $passager, PDO::PARAM_STR);
    $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    // Mettre à jour le nombre de places dans la table voyage
    $stmt = $db->prepare("UPDATE voyage SET nbPlace = nbPlace - :nbPlace WHERE idVoyage = :idVoyage");
    $stmt->bindParam(':nbPlace', $nbPlace, PDO::PARAM_INT);
    $stmt->bindParam(':idVoyage', $idVoyage, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(["message" => "Réservation effectuée avec succès."]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
