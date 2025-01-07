<?php
header("Access-Control-Allow-Origin: *"); // Autoriser les requêtes de toutes origines
header("Content-Type: application/json; charset=UTF-8");

include 'connexion.php'; // Inclure le fichier de connexion

// Vérifier si le paramètre idDepart est passé dans l'URL
$idDepart = isset($_GET['idDepart']) ? (int)$_GET['idDepart'] : ""; // Valeur par défaut de 11956 si non spécifié

try {
    // Préparation de la requête avec un paramètre
    $stmt = $db->prepare("SELECT distinct libelle, idPays, idLevel , idVoyage FROM levels INNER JOIN voyage ON levels.idLevel = voyage.idDest WHERE voyage.idDepart = :idDepart");
    $stmt->bindParam(':idDepart', $idDepart, PDO::PARAM_INT);
    $stmt->execute();

    $levels = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ajout de chaque ligne de résultat au tableau
        $levels[] = [
            'libelle' => $row['libelle'],
            'idPays' => $row['idPays'],
            'idLevel' => $row['idLevel'],
            'idVoyage' => $row['idVoyage']
        ];
    }

    echo json_encode($levels); // Retourne les données sous forme JSON
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>









