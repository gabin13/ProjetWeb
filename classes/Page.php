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

    public function addIntervention($clientEmail, $intervenantEmail, $dateIntervention, $titreIntervention, $description, $urgenceLevel) {
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

        $stmt = $this->pdo->prepare("INSERT INTO intervenantassignments (IntervenantID, InterventionID) VALUES ((SELECT UserID FROM users WHERE Email = :intervenantEmail), :interventionID)");
        $stmt->bindParam(':intervenantEmail', $intervenantEmail);
        $stmt->bindParam(':interventionID', $interventionID);
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

    public function checkInterventionExists($clientID, $titreIntervention) {
        $sql = "SELECT COUNT(*) FROM interventions WHERE ClientID = :clientID AND Title = :titreIntervention";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['clientID' => $clientID, 'titreIntervention' => $titreIntervention]);
        $count = $stmt->fetchColumn();
        
        return $count > 0; // Retourne true si une intervention existe déjà, false sinon
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

    public function addComment($CommentText, $UserId, $InterventionId) {
        $sql = "INSERT INTO comments (CommentText, UserID, InterventionID, CommentDateTime) VALUES (:CommentText, :UserId, :InterventionId, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':CommentText' => $CommentText,
            ':UserId' => $UserId,
            ':InterventionId' => $InterventionId
        ]);
    }
    
    public function getCommentsByInterventionId($interventionId) {
        $sql = "SELECT c.CommentText, c.CommentDateTime, u.UserName 
                FROM comments c
                JOIN users u ON c.UserID = u.UserID
                WHERE c.InterventionID = :interventionId
                ORDER BY c.CommentDateTime DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':interventionId' => $interventionId]);
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
    
    
    
    
    
    
    
}
