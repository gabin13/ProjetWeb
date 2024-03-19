<?php

namespace App;

use PDO;
use PDOException;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Page {
    private $twig;
    private $pdo;
    public $session;

    public function __construct() {
        $this->session = new Session();

        // Connexion à la base de données
        try {
            // Assurez-vous que les paramètres de connexion correspondent à votre configuration
            $this->pdo = new \PDO('mysql:host=mysql;dbname=gestion', "root", "");
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            die();
        }

        // Initialisation de Twig
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader, [
            'cache' => false, // Mettez à true ou spécifiez un dossier de cache en production
            'debug' => true,
        ]);
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function render(string $name, array $data = []) {
        echo $this->twig->render($name, $data);
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE Email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]); // Assurez-vous que $email est une chaîne.
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users ORDER BY Name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserRole($userId, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET Role = :role WHERE UserID = :userId");
        return $stmt->execute([':userId' => $userId, ':role' => $role]);
    }

    public function getIntervenants() {
        $stmt = $this->pdo->prepare("SELECT Email FROM users WHERE Role = 'Intervenant'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addIntervention($clientEmail, $intervenantEmail, $dateIntervention, $titreIntervention, $description, $urgenceLevel, $standardisteId) {
        // Insérer l'intervention
        $stmt = $this->pdo->prepare("INSERT INTO interventions (Title, Description, InterventionDate, UrgencyLevelID, ClientID) VALUES (:titre, :description, :dateIntervention, :urgenceLevel, (SELECT UserID FROM users WHERE Email = :clientEmail))");
        $stmt->bindParam(':titre', $titreIntervention);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':dateIntervention', $dateIntervention);
        $stmt->bindParam(':urgenceLevel', $urgenceLevel);
        $stmt->bindParam(':clientEmail', $clientEmail);
        $success = $stmt->execute();
    
        if (!$success) {
            return false;
        }
    
        $interventionID = $this->pdo->lastInsertId();
    
        // Modification ici pour inclure StandardisteID
        $stmt = $this->pdo->prepare("INSERT INTO intervenantassignments (IntervenantID, InterventionID, StandardisteID) VALUES ((SELECT UserID FROM users WHERE Email = :intervenantEmail), :interventionID, :standardisteId)");
        $stmt->bindParam(':intervenantEmail', $intervenantEmail);
        $stmt->bindParam(':interventionID', $interventionID);
        $stmt->bindParam(':standardisteId', $standardisteId); // Ajout de l'ID du standardiste
        $success = $stmt->execute();
    
        return $success;
    }
    

    public function getIntervenantsEmails() {
        $stmt = $this->pdo->prepare("SELECT Email FROM users WHERE Role = 'Intervenant'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInterventionsByClientEmail($clientEmail) {
        $sql = "SELECT * FROM interventions WHERE ClientID = (SELECT UserID FROM users WHERE Email = :email)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $clientEmail]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkInterventionExistsAndNotClosed($clientID, $titreIntervention) {
        // Supposons que le statut "clôturée" ait un StatusID de 4
        $sql = "SELECT COUNT(*) FROM interventions 
                WHERE ClientID = :clientID 
                AND Title = :titreIntervention 
                AND StatusID != 4"; // Ajout de la condition pour exclure les interventions clôturées
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['clientID' => $clientID, 'titreIntervention' => $titreIntervention]);
        $count = $stmt->fetchColumn();
        
        return $count > 0; // Retourne true si une intervention non clôturée existe déjà, false sinon
    }
    

    public function getClientIDByEmail($email) {
        $sql = "SELECT UserID FROM users WHERE Email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn();
    }

    public function addUser($name, $email, $passwordHash, $role = 'Client') {
        $sql = "INSERT INTO users (Name, Email, PasswordHash, Role) VALUES (:name, :email, :passwordHash, :role)";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':passwordHash' => $passwordHash,
            ':role' => $role
        ]);
        
        return $success;
    }


    public function getInterventionsWithIntervenantEmail() {
        $sql = "SELECT i.InterventionID, i.Title, i.Description, u.Email AS IntervenantEmail
                FROM interventions i
                JOIN intervenantassignments ia ON i.InterventionID = ia.InterventionID
                JOIN users u ON ia.IntervenantID = u.UserID
                WHERE i.ClientID = :clientID AND u.Role = 'Intervenant'";
    
        $stmt = $this->pdo->prepare($sql);
        // Vous devez passer l'ID du client comme paramètre pour filtrer les interventions de ce client spécifique.
        $stmt->execute([':clientID' => $this->session->get('user')['UserID']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    
    

    public function getInterventionsByIntervenant($intervenantId) {
        $pdo = $this->getPdo();
        try {
            $sql = "SELECT i.*, s.Description AS StatusName
                    FROM interventions i
                    INNER JOIN intervenantassignments ia ON i.InterventionID = ia.InterventionID
                    LEFT JOIN interventionstatus s ON i.StatusID = s.StatusID
                    WHERE ia.IntervenantID = :intervenantId";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':intervenantId', $intervenantId, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


    
            
    
            return $results;
        } catch (PDOException $e) {
            die('Erreur lors de la récupération des interventions : ' . $e->getMessage());
        }
    }

    public function updateInterventionStatus($interventionId, $newStatus) {
        $stmt = $this->pdo->prepare("UPDATE interventions SET StatusID = :newStatus WHERE InterventionID = :interventionId");
        return $stmt->execute([':interventionId' => $interventionId, ':newStatus' => $newStatus]);
    }

    public function hasOngoingIntervention($intervenantId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM interventions i
            JOIN intervenantassignments ia ON i.InterventionID = ia.InterventionID
            WHERE ia.IntervenantID = :intervenantId AND i.StatusID = 2
        ");
        $stmt->execute([':intervenantId' => $intervenantId]);
        $count = $stmt->fetchColumn();
        
        return $count > 0;
    }
    
    public function closePastDueInterventions() {
        $today = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("UPDATE interventions SET StatusID = 5 WHERE StatusID = 1 AND InterventionDate < :today");
        $stmt->execute([':today' => $today]);
    }

    public function countUrgentInterventionsWaiting($intervenantEmail) {
        // Récupérer l'ID de l'intervenant à partir de son email
        $sqlIntervenantId = "SELECT UserID FROM users WHERE Email = :intervenantEmail";
        $stmtIntervenantId = $this->pdo->prepare($sqlIntervenantId);
        $stmtIntervenantId->execute([':intervenantEmail' => $intervenantEmail]);
        $intervenantId = $stmtIntervenantId->fetchColumn();
    
        if (!$intervenantId) {
            return false; // Intervenant non trouvé
        }
    
        // Compter les interventions urgentes et en attente pour cet intervenant
        $sql = "SELECT COUNT(*) 
                FROM interventions 
                JOIN intervenantassignments ON interventions.InterventionID = intervenantassignments.InterventionID
                WHERE intervenantassignments.IntervenantID = :intervenantId
                AND interventions.StatusID = 1
                AND interventions.UrgencyLevelID = 3";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':intervenantId' => $intervenantId]);
        $count = $stmt->fetchColumn();
    
        return $count;
    }


    
    public function addComment($commentText, $interventionID, $userID) {
        // Validation de base

        if ($this->userHasCommented($interventionID, $userID)) {
            // L'utilisateur a déjà commenté cette intervention
            return false; // Ou retourner un message spécifique
        }

        if (empty($commentText) || empty($interventionID) || empty($userID)) {
            return false; // Retourne immédiatement si l'une des valeurs est vide
        }
    
        try {
            $sql = "INSERT INTO comments (CommentText, InterventionID, UserID, CommentDateTime) VALUES (?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            // Exécution avec les paramètres positionnels pour éviter les problèmes liés au typage
            $success = $stmt->execute([$commentText, $interventionID, $userID]);
    
            return $success;
        } catch (PDOException $e) {
            // Log de l'erreur pour le débogage
            error_log('Erreur lors de l\'ajout d\'un commentaire: ' . $e->getMessage());
            return false;
        }
    }

    
    public function userHasCommented($interventionID, $userID) {
        $sql = "SELECT COUNT(*) FROM comments WHERE InterventionID = :interventionID AND UserID = :userID";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':interventionID' => $interventionID,
            ':userID' => $userID
        ]);
        $count = $stmt->fetchColumn();
    
        return $count > 0; // Retourne true si l'utilisateur a déjà commenté, sinon false
    }

    public function getInterventionsByStandardiste($standardisteId) {
        $sql = "SELECT interventions.*, interventionstatus.Description AS StatusDescription, urgencylevels.Level AS UrgencyLevelDescription
                FROM interventions
                JOIN interventionstatus ON interventions.StatusID = interventionstatus.StatusID
                JOIN urgencylevels ON interventions.UrgencyLevelID = urgencylevels.UrgencyLevelID
                JOIN intervenantassignments ON interventions.InterventionID = intervenantassignments.InterventionID
                WHERE intervenantassignments.StandardisteID = :standardisteId";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':standardisteId' => $standardisteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function closeInterventionStatus($interventionId) {
        $statusCloturee = 4; // Assurez-vous que 4 est l'ID du statut "Clôturée"
        $stmt = $this->pdo->prepare("UPDATE interventions SET StatusID = :newStatus WHERE InterventionID = :interventionId");
        return $stmt->execute([':interventionId' => $interventionId, ':newStatus' => $statusCloturee]);
    }

    public function ccancelInterventionStatus($interventionId) {
        $statusCloturee = 4; // Assurez-vous que 4 est l'ID du statut "Clôturée"
        $stmt = $this->pdo->prepare("UPDATE interventions SET StatusID = :newStatus WHERE InterventionID = :interventionId");
        return $stmt->execute([':interventionId' => $interventionId, ':newStatus' => $statusCloturee]);
    }

    
    
    
    
  
}
