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
    
    
}
