

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


include 'connexion.php';
require('./fpdf/fpdf.php');

function generateRecu($idReserv) {
    global $db;

    try {
        $req = $db->prepare("SELECT * FROM reservation WHERE idReserv = :idReserv");
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

    // Orange Header
    $pdf->SetFillColor(214, 53, 77);
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 20, "BUS TICKET", 0, 1, 'C', true);

    // Reset text color for content
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(5);

    // Ticket information layout
    $pdf->Cell(100, 10, "Nom du passager :", 0, 0);
    $pdf->Cell(90, 10, utf8_decode($reservation['passager']), 0, 1);

    $pdf->Cell(100, 10, "ID du voyage : ", 0, 0);
    $pdf->Cell(90, 10, utf8_decode($reservation['idVoyage']), 0, 1);

    $pdf->Cell(100, 10, "Nombre de places : ", 0, 0);
    $pdf->Cell(90, 10, utf8_decode($reservation['nbPlace']), 0, 1);

    $pdf->Cell(100, 10, utf8_decode("Téléphone : "), 0, 0);
    $pdf->Cell(90, 10, utf8_decode($reservation['telephone']), 0, 1);

    $pdf->Cell(100, 10, "Date:", 0, 0);
    $pdf->Cell(90, 10, utf8_decode($reservation['dateReserv']), 0, 1);

    $pdf->Cell(100, 10, "Option Billet: ", 0, 0);
    $pdf->Cell(90, 10, utf8_decode($reservation['optionBillet']), 0, 1);


    

    // Barcode placeholder
    // $pdf->Rect(60, 100, 80, 20, 'D'); // Draw a rectangle for the barcode area
    $pdf->Cell(0, 20, "Barcode", 0, 1, 'C'); // Placeholder text for barcode

    $pdf->Output();
}

class PDF extends FPDF {
    function Header(){
        $this->Image('./logo.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, utf8_decode('Réçu de la réservation'), 0, 1, 'C');
        $this->Ln(10);
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
