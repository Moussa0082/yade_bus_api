<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");

// Autoriser les méthodes POST et PUT
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

include 'connexion.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST': // Ajouter un nouveau ticket
        // Récupérer les données JSON envoyées
        $data = json_decode(file_get_contents("php://input"));
        
        // Vérifier que toutes les données requises sont présentes
        if (!empty($data->numero) && !empty($data->validite) && !empty($data->idVoyage) && !empty($data->idAgence)) {
            try {
                // Récupérer le nombre de places disponibles pour le voyage
                $stmt = $db->prepare("SELECT nbPlace FROM voyages WHERE idVoyage = :idVoyage");
                $stmt->bindParam(':idVoyage', $data->idVoyage);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $nbPlaceDisponible = $result['nbPlace'];

                    // Vérifier s'il y a suffisamment de places
                    if ($nbPlaceDisponible < 1) {
                        echo json_encode(["message" => "Aucune place disponible pour ce voyage."]);
                    } elseif ($data->reserved > $nbPlaceDisponible) {
                        echo json_encode(["message" => "Le nombre de places demandées excède le nombre de places disponibles."]);
                    } else {
                        // Préparation de l'insertion
                        $stmt = $db->prepare("INSERT INTO tickets (numero, validite, idVoyage, reserved, idAgence) VALUES (:numero, :validite, :idVoyage, :reserved, :idAgence)");
                        $stmt->bindParam(':numero', $data->numero);
                        $stmt->bindParam(':validite', $data->validite);
                        $stmt->bindParam(':idVoyage', $data->idVoyage);
                        $stmt->bindParam(':reserved', $data->reserved);
                        $stmt->bindParam(':idAgence', $data->idAgence);
                        
                        // Exécuter l'insertion
                        if ($stmt->execute()) {
                            // Mettre à jour le nombre de places dans la table voyages
                            $newNbPlace = $nbPlaceDisponible - $data->reserved;
                            $updateStmt = $db->prepare("UPDATE voyages SET nbPlace = :newNbPlace WHERE idVoyage = :idVoyage");
                            $updateStmt->bindParam(':newNbPlace', $newNbPlace);
                            $updateStmt->bindParam(':idVoyage', $data->idVoyage);
                            $updateStmt->execute();
                            
                            echo json_encode(["message" => "Ticket ajouté avec succès"]);
                        } else {
                            echo json_encode(["message" => "Échec de l'ajout du ticket"]);
                        }
                    }
                } else {
                    echo json_encode(["message" => "Voyage non trouvé."]);
                }
            } catch (PDOException $e) {
                echo json_encode(["error" => $e->getMessage()]);
            }
        } else {
            echo json_encode(["message" => "Données incomplètes"]);
        }
        break;
    
        case 'PUT': // Mettre à jour un ticket existant
            // Récupérer les données JSON envoyées
            $data = json_decode(file_get_contents("php://input"));
            
            // Vérifier que toutes les données requises sont présentes
            if (!empty($data->idTicket) && !empty($data->numero) && !empty($data->validite) && !empty($data->idVoyage) && isset($data->reserved) && !empty($data->idAgence)) {
                try {
                    // Récupérer le nombre de places disponibles pour le voyage
                    $stmt = $db->prepare("SELECT nbPlace FROM voyages WHERE idVoyage = :idVoyage");
                    $stmt->bindParam(':idVoyage', $data->idVoyage);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
                    if ($result) {
                        $nbPlaceDisponible = $result['nbPlace'];
    
                        // Récupérer le nombre de tickets déjà réservés pour ce ticket
                        $stmtTicket = $db->prepare("SELECT reserved FROM tickets WHERE idTicket = :idTicket");
                        $stmtTicket->bindParam(':idTicket', $data->idTicket);
                        $stmtTicket->execute();
                        $ticketData = $stmtTicket->fetch(PDO::FETCH_ASSOC);
    
                        if ($ticketData) {
                            $currentReserved = $ticketData['reserved'];
                            $remainingPlaces = $nbPlaceDisponible + $currentReserved;
    
                            // Vérifier si la mise à jour des places est possible
                            if ($data->reserved > $remainingPlaces) {
                                echo json_encode(["message" => "Le nombre de places demandées excède le nombre de places disponibles."]);
                            } else {
                                // Préparation de la mise à jour
                                $stmt = $db->prepare("UPDATE tickets SET numero = :numero, validite = :validite, idVoyage = :idVoyage, reserved = :reserved, idAgence = :idAgence WHERE idTicket = :idTicket");
                                $stmt->bindParam(':idTicket', $data->idTicket);
                                $stmt->bindParam(':numero', $data->numero);
                                $stmt->bindParam(':validite', $data->validite);
                                $stmt->bindParam(':idVoyage', $data->idVoyage);
                                $stmt->bindParam(':reserved', $data->reserved);
                                $stmt->bindParam(':idAgence', $data->idAgence);
                                
                                // Exécuter la mise à jour
                                if ($stmt->execute()) {
                                    // Mettre à jour le nombre de places dans la table voyages
                                    $newNbPlace = $remainingPlaces - $data->reserved;
                                    $updateStmt = $db->prepare("UPDATE voyages SET nbPlace = :newNbPlace WHERE idVoyage = :idVoyage");
                                    $updateStmt->bindParam(':newNbPlace', $newNbPlace);
                                    $updateStmt->bindParam(':idVoyage', $data->idVoyage);
                                    $updateStmt->execute();
                                    
                                    echo json_encode(["message" => "Ticket mis à jour avec succès"]);
                                } else {
                                    echo json_encode(["message" => "Échec de la mise à jour du ticket"]);
                                }
                            }
                        } else {
                            echo json_encode(["message" => "Ticket non trouvé."]);
                        }
                    } else {
                        echo json_encode(["message" => "Voyage non trouvé."]);
                    }
                } catch (PDOException $e) {
                    echo json_encode(["error" => $e->getMessage()]);
                }
            } else {
                echo json_encode(["message" => "Données incomplètes"]);
            }
            break;
    
    
    default:
        echo json_encode(["message" => "Méthode non supportée"]);
        break;
}
?>
