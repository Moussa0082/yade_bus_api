<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connexion.php';

// Récupérer les données de la requête
$data = json_decode(file_get_contents('php://input'), true);
$idVoyage = isset($data['idVoyage']) ? (int)$data['idVoyage'] : null;
$nbPlace = isset($data['nbPlace']) ? (int)$data['nbPlace'] : null;
$passager = isset($data['passager']) ? $data['passager'] : null;
$telephone = isset($data['telephone']) ? $data['telephone'] : null;

// Vérification des paramètres requis
if (!$idVoyage || !$nbPlace || !$passager || !$telephone) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(["error" => "Paramètres requis manquants."]);
    exit();
}

// Vérification que nbPlace est valide
if ($nbPlace <= 0) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(["error" => "Le nombre de places doit être supérieur à 0."]);
    exit();
}

try {
    // Vérification si le voyage existe et récupération du nombre de places disponibles
    $stmt = $db->prepare("SELECT nbPlace FROM voyage WHERE idVoyage = :idVoyage");
    $stmt->bindParam(':idVoyage', $idVoyage, PDO::PARAM_INT);
    $stmt->execute();
    $voyage = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voyage) {
        http_response_code(404); // 404 Not Found
        echo json_encode(["error" => "Voyage non trouvé."]);
        exit();
    }

    $placesDisponibles = (int)$voyage['nbPlace'];

    // Vérification si le nombre de places demandées est valide
    if ($nbPlace > $placesDisponibles) {
        http_response_code(400); // 400 Bad Request
        echo json_encode(["error" => "Le nombre de places demandées dépasse le nombre de places disponibles ($placesDisponibles places restantes)."]);
        exit();
    }

    // Démarrer une transaction pour garantir la cohérence des données
    $db->beginTransaction();

    // Créer la réservation
    $stmt = $db->prepare("
        INSERT INTO reservation (idVoyage, etat, nbPlace, passager, telephone, dateReserv) 
        VALUES (:idVoyage, 'en attente', :nbPlace, :passager, :telephone, NOW())
    ");
    $stmt->bindParam(':idVoyage', $idVoyage, PDO::PARAM_INT);
    $stmt->bindParam(':nbPlace', $nbPlace, PDO::PARAM_INT);
    $stmt->bindParam(':passager', $passager, PDO::PARAM_STR);
    $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
    $stmt->execute();

    // Mettre à jour le nombre de places dans la table voyage
    $stmt = $db->prepare("UPDATE voyage SET nbPlace = nbPlace - :nbPlace WHERE idVoyage = :idVoyage");
    $stmt->bindParam(':nbPlace', $nbPlace, PDO::PARAM_INT);
    $stmt->bindParam(':idVoyage', $idVoyage, PDO::PARAM_INT);
    $stmt->execute();

    // Valider la transaction
    $db->commit();

    http_response_code(200); // 200 OK
    echo json_encode([
        "message" => "Réservation effectuée avec succès.",
        "placesRestantes" => $placesDisponibles - $nbPlace
    ]);
} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $db->rollBack();
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(["error" => "Une erreur est survenue : " . $e->getMessage()]);
}

?>
