<?php

require_once '../vendor/autoload.php';
session_start();

use App\Page;

// Vérifie si l'utilisateur est connecté et a le rôle 'Admin'
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Admin') {
    header('Location: login.php');
    exit;
}

$page = new Page();

// Vérifie si l'ID de l'utilisateur a bien été transmis
if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];
    $result = $page->deleteUser($userId);

    if ($result) {
        $_SESSION['message'] = 'Utilisateur supprimé avec succès.';
    } else {
        $_SESSION['error'] = 'Erreur lors de la suppression de l\'utilisateur.';
    }
}

header('Location: index.php');
exit;
?>
