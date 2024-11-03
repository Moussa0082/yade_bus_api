<?php
header("Access-Control-Allow-Origin: *"); // Autoriser les requêtes de toutes origines
header("Content-Type: application/json; charset=UTF-8");

include 'connexion.php'; // Inclure le fichier de connexion

try {
    $stmt = $db->query("SELECT libelle, idPays, idLevel FROM levels INNER JOIN voyage ON levels.idLevel = voyage.idDepart");

    $levels = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
         // Ajouter chaque ligne de résultat au tableau
         $levels[] = [
            'libelle' => $row['libelle'],
            'idPays' => $row['idPays'],
            'idLevel' => $row['idLevel']
        ];
    }

    echo json_encode($levels); // Retourne les données sous forme JSON
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
