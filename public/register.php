<?php

require_once '../vendor/autoload.php'; // Autoload our classes with Composer

use App\Page;
use App\Session; // Utilisez la classe Session pour gérer la session

$page = new Page();
$session = new Session(); // Créez une instance de Session

$session->start(); // Démarrez la session si ce n'est pas déjà fait

$msg = false;

if (isset($_POST['send'])) {
    // Récupérez les données du formulaire
    $name = $_POST['name']; // Assurez-vous d'avoir un champ `name` dans votre formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT); // Hachez le mot de passe

    // Vérifiez si l'email existe déjà
    $existingUser = $page->getUserByEmail(['email' => $email]);
    
    if ($existingUser) {
        $msg = "Cet email est déjà utilisé.";
    } else {
        // Insertion de l'utilisateur dans la base de données
        // Assurez-vous que les noms des clés correspondent exactement aux noms des colonnes de votre base de données.
        $page->insert([
            'Name' => $name,
            'Email' => $email,
            'PasswordHash' => $passwordHash, // Assurez-vous que ceci est le hash et non le mot de passe en clair
            'Role' => 'Client' // Ou tout autre rôle par défaut que vous souhaitez attribuer
        ]);
        

        // Vérifiez si l'utilisateur a été correctement inséré
        $newUser = $page->getUserByEmail(['email' => $email]);
        if ($newUser) {
            $session->add('user', $newUser); // Ajoutez les infos de l'utilisateur à la session
            header("Location: index.php"); // Redirection
            exit; // Assurez-vous d'appeler exit après une redirection
        } else {
            $msg = "Une erreur s'est produite lors de l'inscription.";
        }
    }
}

echo $page->render('register.html', ['msg' => $msg]);
