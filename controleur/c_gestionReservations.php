<?php

$action = $_GET['action'];
include "./modeles/Reservation.php";
include "./modeles/ReservationDAO.php";
include "./modeles/SoireeDAO.php";
include "./modeles/Outils.php";

$conn = ConnexionBdPdo::getConnexion();

switch ($action) 
{
    // On affiche la page de réservation
    case "afficherFormReservation":
        if (!isset($_SESSION['loggedin'])) {
            echo "<script>window.location.replace('./index.php?controleur=gestionCompte&action=seConnecter')</script>";
            exit;
        }
        include './vues/v_Reservation.php';

        $soireeDAO = new SoireeDAO($conn);
        $libelles = $soireeDAO->getLibelles();

        break;

    // On effectue une réservation
    case "effectuerReservation":
        // Si tous les champs sont remplis
        if ((!empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['tel']) && Outils::isDigits($_POST['tel'], 10, 10) == true)) 
        {
            // On créé une nouvelle réservation
            $nvlReservation = new Reservation($_POST['libelles'], $_POST['nom'], $_POST['prenom'], $_POST['tel'], $_SESSION['email']);
            $nvlReservationDAO = new ReservationDAO($conn); // Connexion à la BD

            $soireeDAO = new SoireeDAO($conn); // Connexion à la BD
            // Si le nombre de places disponibles est inf à 1 : message "plus de place"
            if ($soireeDAO->getNbPlacesFromDate($_POST['libelles']) < 1) {

                echo "<script>document.getElementById('errorMessage').className = 'navbar-brand'</script>";
                echo "<script>document.getElementById('errorMessage').style.color = '#FF0000'</script>";
                echo "<script>document.getElementById('errorMessage').innerText = 'Il n\'y a plus de place pour cette soirée.'</script>";

            } 
            // Sinon si le nb de places dispo est sup à 0
            else {
                // On ajoute une nouvelle réservation et on enlève une place dans le nb de places restantes pour la soirée
                $nvlReservationDAO->ajouterReservation($nvlReservation);
                $soireeDAO->decreasePlace($_POST['libelles']);

                // Message de confirmation
                echo "<script>document.getElementById('errorMessage').className = 'navbar-brand'</script>";
                echo "<script>document.getElementById('errorMessage').style.color = '#00FF00'</script>";
                echo "<script>document.getElementById('errorMessage').innerText = 'Réservation effectuée !'</script>";
            }

        } 
        // Sinon si un ou plusieurs champs ne sont pas remplis : message d'erreur
        else if (isset($_POST['submitReservation'])) {
            echo "<script>document.getElementById('errorMessage').className = 'navbar-brand'</script>";
            echo "<script>document.getElementById('errorMessage').style.color = '#FF0000'</script>";
            echo "<script>document.getElementById('errorMessage').innerText = 'Certains champs ne sont pas valides !'</script>";
        }
        include './vues/v_Reservation.php';
        break;

    // On affiche la page des réservations du clients
    case 'mesReservations':
        $reservationDAO = new ReservationDAO($conn);
        include './vues/v_MesReservations.php';

        break;

    // On annulela réservation
    case 'annulerReservation':

        if (!empty($_POST['idAnnulation'])) {
            $idAnnulation = $_POST['idAnnulation'];

            $reservationDAO = new ReservationDAO($conn); // Connexion à la BD
            $reservationDAO->annulerReservation($idAnnulation, $_SESSION['email']); // annulation de la réservation

            $soireeDAO = new SoireeDAO($conn); // Connexion à la BD
            $soireeDAO->increasePlaceById($idAnnulation); // on ajoute une place sup pour la soiree

            // Message de confirmation
            echo "<script>document.getElementById('errorMessage').className = 'navbar-brand'</script>";
            echo "<script>document.getElementById('errorMessage').style.color = '#00FF00'</script>";
            echo "<script>document.getElementById('errorMessage').innerText = 'La réservation a bien été annulée !'</script>";

        }
        break;
}
