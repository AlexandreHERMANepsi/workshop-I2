<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: #fff;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .delete-link {
            color: #e74c3c;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1>Gestion des Utilisateurs</h1>
    <p><a href="index.php">Accueil</a></p>

    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Path to the Squid passwd file
    $passwd_file = '/etc/squid/passwd';

    // Function to delete a user
    function delete_user($username, $passwd_file) {
        $lines = file($passwd_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $new_lines = [];
        
        foreach ($lines as $line) {
            list($user, $password_hash) = explode(":", $line);
            if ($user !== $username) {
                $new_lines[] = $line;
            }
        }
        
        // Write the remaining users back to the file
        file_put_contents($passwd_file, implode("\n", $new_lines) . "\n");
        return true;
    }

    // Function to add a new user
    function add_user($username, $password, $passwd_file) {
        // Check if the user already exists
        $lines = file($passwd_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            list($existing_user, $password_hash) = explode(":", $line);
            if ($existing_user === $username) {
                return ["type" => "error", "message" => "L'utilisateur existe déjà."];
            }
        }

        // Generate the APR1-MD5 hashed password using the openssl command
        $escaped_password = escapeshellarg($password);
        $hashed_password = trim(shell_exec("openssl passwd -apr1 $escaped_password"));

        if ($hashed_password) {
            // Append the new user to the file
            $result = file_put_contents($passwd_file, "$username:$hashed_password\n", FILE_APPEND);
            
            // Check if the file write was successful
            if ($result !== false) {
                return ["type" => "success", "message" => "Nouvel utilisateur '$username' ajouté."];
            } else {
                return ["type" => "error", "message" => "Erreur lors de l'écriture dans le fichier des utilisateurs."];
            }
        } else {
            return ["type" => "error", "message" => "Erreur lors du hachage du mot de passe."];
        }
    }

    $message = null;

    // Handle user deletion
    if (isset($_GET['delete'])) {
        $user_to_delete = $_GET['delete'];
        if (delete_user($user_to_delete, $passwd_file)) {
            $message = ["type" => "success", "message" => "Utilisateur '$user_to_delete' supprimé."];
        } else {
            $message = ["type" => "error", "message" => "Erreur lors de la suppression de l'utilisateur."];
        }
    }

    // Handle adding a new user
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username']) && isset($_POST['new_password'])) {
        $new_username = trim($_POST['new_username']);
        $new_password = trim($_POST['new_password']);
        
        if (!empty($new_username) && !empty($new_password)) {
            $message = add_user($new_username, $new_password, $passwd_file);
        } else {
            $message = ["type" => "error", "message" => "Veuillez entrer un nom d'utilisateur et un mot de passe valides."];
        }
    }

    if ($message) {
        echo "<div class='message {$message['type']}'>{$message['message']}</div>";
    }
    ?>

    <h2>Liste des utilisateurs</h2>
    <?php
    if (file_exists($passwd_file) && is_readable($passwd_file)) {
        $file_contents = file($passwd_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!empty($file_contents)) {
            echo "<ul>";
            foreach ($file_contents as $line) {
                list($username, $password_hash) = explode(":", $line);
                echo "<li>" . htmlspecialchars($username) . 
                     " <a href='?delete=" . urlencode($username) . "' class='delete-link' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cet utilisateur ?\");'>Supprimer</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aucun utilisateur trouvé.</p>";
        }
    } else {
        echo "<p>Impossible de lire le fichier des utilisateurs.</p>";
    }
    ?>

    <h2>Ajouter un utilisateur</h2>
    <form method="post">
        <label for="new_username">Nom d'utilisateur:</label>
        <input type="text" id="new_username" name="new_username" required>
        <label for="new_password">Mot de passe:</label>
        <input type="password" id="new_password" name="new_password" required>
        <input type="submit" value="Ajouter">
    </form>
</body>
</html>