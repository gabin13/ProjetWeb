<?php
// Démarrer la session
session_start();

// Effacer toutes les données de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion ou la page d'accueil
header("Location: index.php");
exit;
