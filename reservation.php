<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connexion.php';
include 'generer_recu.php';

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

    // Vérifier si le nombre de places demandées est inférieur ou égal aux places disponibless
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
    
    if($stmt){
        // Vérifiez que l'ID de réservation est fourni (par exemple, depuis une requête GET ou POST)
        $idReserv = isset($_GET['idReserv']) ? (int)$_GET['idReserv'] : null;

        if ($idReserv) {
            generateRecu($idReserv);
        } else {
            echo json_encode(["error" => "ID de réservation requis."]);
        }
    }

    echo json_encode(["message" => "Réservation effectuée avec succès."]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

//   // Définition de la fonction generateRecu
// function generateRecu($idReserv) {
//     global $db; // S'assurer que $db est accessible dans la fonction
//     try {
//         $req = $db->prepare("SELECT * FROM reservations WHERE idReserv = :idReserv");
//         $req->bindParam(':idReserv', $idReserv, PDO::PARAM_INT);
//         $req->execute();
        
//         $levels = $req->fetchAll(PDO::FETCH_ASSOC);
        
//         class PDF extends FPDF {
//             function Header(){
//                 $this->Image('');
//                 $this->SetFont('Arial','B',15); 
//                 $this->Cell(30,10,'Réçu de la réservation',1,0, 'C');
//                 $this->Cell(80);
//                 $this->Ln(30);
//             }

//             function Footer(){}

//         }
//         $pdf = New PDF();
//         $pdf->AliasNbPages();
//         $pdf->SetFont('Times New Roman', '', 11);
//         $pdf->Output();

        
//         echo json_encode($levels); // Retourne les données sous forme JSON
//     } catch (PDOException $e) {
//         echo json_encode(["error" => $e->getMessage()]);
//     }
// }
?>
