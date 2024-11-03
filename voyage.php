<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connexion.php';

$idDepart = isset($_GET['idDepart']) ? (int)$_GET['idDepart'] : "";
$idDest = isset($_GET['idDest']) ? (int)$_GET['idDest'] : "";
$dateDepart = isset($_GET['dateDepart']) ? $_GET['dateDepart'] : null; // Optionnel

try {
    // Préparation de la requête avec les jointures et gestion de dateDepart optionnel
    $stmt = $db->prepare("
    SELECT DISTINCT
        voyage.*,
        compagnie.ninea AS compagnieNom,
        COALESCE(agence_compagnie.adresse, '') AS agenceNom,
        COALESCE(departLevel.libelle, '') AS departNom,
        COALESCE(destLevel.libelle, '') AS destNom
    FROM voyage
    LEFT JOIN compagnie ON voyage.idCompagnie = compagnie.idCompagnie
    LEFT JOIN agence_compagnie ON agence_compagnie.idCompagnie = voyage.idCompagnie
    LEFT JOIN levels AS departLevel ON voyage.idDepart = departLevel.idLevel
    LEFT JOIN levels AS destLevel ON voyage.idDest = destLevel.idLevel
    WHERE voyage.idDepart = :idDepart 
      AND voyage.idDest = :idDest 
      AND (voyage.dateDepart = :dateDepart OR :dateDepart IS NULL)
    GROUP BY voyage.idVoyage
");

    // Associer les paramètres
    $stmt->bindParam(':idDepart', $idDepart, PDO::PARAM_INT);
    $stmt->bindParam(':idDest', $idDest, PDO::PARAM_INT);
    $stmt->bindParam(':dateDepart', $dateDepart, PDO::PARAM_STR);

    $stmt->execute();

    $voyages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $voyages[] = $row;
    }
    
    // Vérifier si aucun voyage n'a été trouvé
    if (empty($voyages)) {
        echo json_encode(["message" => "Aucun voyage trouvé pour les critères spécifiés."]);
    } else {
        echo json_encode($voyages); // Retourne les données sous forme JSON
    }
    } catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
