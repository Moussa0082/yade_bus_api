<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


include 'connexion.php';
require('./fpdf/fpdf.php');

function generateRecu($idReserv) {
    global $db;

    try {
        $req = $db->prepare("SELECT *
        FROM reservation
        LEFT JOIN agence_compagnie ON agence_compagnie.idAgence = reservation.idAgence
        LEFT JOIN voyage ON voyage.idVoyage = reservation.idVoyage
        WHERE reservation.idReserv = :idReserv;
        ");
        $req->bindParam(':idReserv', $idReserv, PDO::PARAM_INT);
        $req->execute();
        
        $reservation = $req->fetch(PDO::FETCH_ASSOC);
        if ($reservation) {
            generatePDF($reservation);
        } else {
            echo json_encode(["error" => "Aucune réservation trouvée avec cet ID"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
function generatePDF($reservation) {
    $pdf = new PDF();
    $pdf->AddPage();

    // Styling for the ticket
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.5);

    // Header Section with Orange Background
    $pdf->SetFillColor(214, 53, 77);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 20, "TICKET DE VOYAGE", 0, 1, 'C', true);

    // Logo and Agency Information
    $pdf->Ln(10);
   
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, utf8_decode("Agence de Transport : " . $reservation['idCompagnie']), 0, 1, 'R');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, utf8_decode("Adresse : " . $reservation['adresse']), 0, 1, 'R');

    // Separator Line
    $pdf->SetLineWidth(0.2);
    $pdf->Line(10, 55, 200, 55);
    $pdf->Ln(10);

    // Passenger Information
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Informations du Passager", 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 8, "Nom :", 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['passager']), 0, 1);
    $pdf->Cell(50, 8, utf8_decode("Téléphone :"), 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['telephone']), 0, 1);
    $pdf->Cell(50, 8, "Email :", 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['email']), 0, 1);

    // Reservation Details
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode("Détails de la Réservation"), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 8, utf8_decode("Numéro de Confirmation :"), 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['numConfirmation']), 0, 1);
    $pdf->Cell(50, 8, "Option Billet :", 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['optionBillet']), 0, 1);
    $pdf->Cell(50, 8, utf8_decode("État :"), 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['etat']));

    // Voyage Information
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Informations de Voyage", 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 8, utf8_decode("Date de départ :"), 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['dateDepart']), 0, 1);
    $pdf->Cell(50, 8, utf8_decode("Date d'arrivée :"), 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['dateArrivee']), 0, 1);
    $pdf->Cell(50, 8, utf8_decode("Heure de départ :"), 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['heure']), 0, 1);
    $pdf->Cell(50, 8, utf8_decode("Lieu de départ :"), 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['idDepart']), 0, 1);
    $pdf->Cell(50, 8, "Destination :", 0, 0);
    $pdf->Cell(0, 8, utf8_decode($reservation['idDest']), 0, 1);

    // Footer Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 10, utf8_decode("Merci de voyager avec nous. Présentez ce ticket lors de votre embarquement."), 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->Cell(0, 10, utf8_decode("Imprimé le " . date("Y-m-d H:i:s")), 0, 0, 'C');

    $pdf->Output();
}


class PDF extends FPDF {
    function Header(){
        $this->Image('./logo.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, utf8_decode('Djaam Yadee'), 0, 1, 'C');
        $this->Ln(15);
    }

    function Footer(){
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Page ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Check if reservation ID is provided
$idReserv = isset($_GET['idReserv']) ? (int)$_GET['idReserv'] : null;
if ($idReserv) {
    generateRecu($idReserv);
} else {
    echo json_encode(["error" => "ID de réservation requis."]);
}
?>
