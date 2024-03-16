<?php

require_once '../vendor/autoload.php'; // Load all classes with Composer

session_start();

use App\Page;

$page = new Page();

if (isset($_SESSION['user']) && $_SESSION['user']['Role'] == 'Admin') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve and sanitize the POST data
        $userId = filter_input(INPUT_POST, 'userId', FILTER_SANITIZE_NUMBER_INT);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Update the user's role in the database
        if ($page->updateUserRole($userId, $role)) {
            // Redirect back to the admin page with a success message
            header('Location: index.php?message=Role updated successfully');
        } else {
            // Redirect back with an error message
            header('Location: index.php?message=Error updating role');
        }
    }
} else {
    // Redirect non-admins back to the home page
    header('Location: index.php');
    exit;
}

?>
