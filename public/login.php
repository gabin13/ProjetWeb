<?php

require_once '../vendor/autoload.php'; // Autoload our classes with Composer

use App\Page;

$page = new Page();

// Assurez-vous que session_start() n'est appelé qu'une seule fois et au début.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$msg = false;

if (isset($_POST['send'])) {
    $email = $_POST['email']; // Récupération de l'email depuis le formulaire
    $password = $_POST['password']; // Récupération du mot de passe depuis le formulaire
    
    $user = $page->getUserByEmail(['email' => $email]); // Utilisez la clé 'email' pour correspondre à votre méthode getUserByEmail
    
    if (!$user) {
        $msg = "Email ou mot de passe incorrect";
    } else {
        // La clé à utiliser ici doit correspondre à la colonne de votre base de données, qui est 'PasswordHash', et non 'password'
        if (!password_verify($password, $user['PasswordHash'])) {
            $msg = "Email ou mot de passe incorrect !";
        } else {
            // Si les identifiants sont valides, enregistrez l'utilisateur dans la session
            $_SESSION['user'] = $user; // Stocke les informations de l'utilisateur dans la session
            header("Location: index.php"); // Redirection vers index.php
            exit; // Arrêt de l'exécution du script après la redirection
        }
    }
}

// Assurez-vous que le nom des variables passées à Twig correspond aux attentes de votre template
echo $page->render('login.html', ['msg' => $msg]);
