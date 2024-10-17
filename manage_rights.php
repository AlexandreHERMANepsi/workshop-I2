<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de la Blacklist</title>
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
        input[type="text"] {
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
    <h1>Gestion de la Blacklist</h1>
    <p><a href="index.php">Accueil</a></p>

    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $blacklist_file = '/etc/squid/blacklist.txt';

    function ajouterSiteBlacklist($site, $fichier) {
        $site = parse_url($site, PHP_URL_HOST) ?: $site;

        if (!file_exists($fichier)) {
            return ["type" => "error", "message" => "Erreur : Le fichier blacklist n'existe pas."];
        }

        $lignes = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (in_array($site, $lignes)) {
            return ["type" => "error", "message" => "Le site '$site' est déjà dans la blacklist."];
        }

        if (file_put_contents($fichier, $site . PHP_EOL, FILE_APPEND | LOCK_EX)) {
            shell_exec('sudo squid -k reconfigure');
            return ["type" => "success", "message" => "Le site '$site' a été ajouté à la blacklist."];
        } else {
            return ["type" => "error", "message" => "Erreur lors de l'ajout du site à la blacklist."];
        }
    }

    function supprimerSiteBlacklist($site, $fichier) {
        if (!file_exists($fichier)) {
            return ["type" => "error", "message" => "Erreur : Le fichier blacklist n'existe pas."];
        }

        $lignes = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouveau_contenu = array_diff($lignes, [$site]);

        if (file_put_contents($fichier, implode(PHP_EOL, $nouveau_contenu) . PHP_EOL)) {
            shell_exec('sudo squid -k reconfigure');
            return ["type" => "success", "message" => "Le site '$site' a été supprimé de la blacklist."];
        } else {
            return ["type" => "error", "message" => "Erreur lors de la suppression du site."];
        }
    }

    $message = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['site'])) {
            $message = ajouterSiteBlacklist($_POST['site'], $blacklist_file);
        } elseif (!empty($_POST['supprimer_site'])) {
            $message = supprimerSiteBlacklist($_POST['supprimer_site'], $blacklist_file);
        }
    }

    if ($message) {
        echo "<div class='message {$message['type']}'>{$message['message']}</div>";
    }
    ?>

    <form method="POST" action="">
        <h2>Ajouter un site à la blacklist</h2>
        <label for="site">Site à ajouter :</label>
        <input type="text" name="site" id="site" placeholder="www.exemple.com" required>
        <input type="submit" value="Ajouter">
    </form>

    <h2>Sites dans la blacklist</h2>
    <?php
    if (file_exists($blacklist_file)) {
        $sites = file($blacklist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!empty($sites)) {
            echo '<ul>';
            foreach ($sites as $site) {
                echo '<li>' . htmlspecialchars($site) . ' 
                <form method="POST" action="">
                    <input type="hidden" name="supprimer_site" value="' . htmlspecialchars($site) . '">
                    <input type="submit" value="Supprimer">
                </form>
                </li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Aucun site n\'est blacklisté.</p>';
        }
    } else {
        echo '<p>Le fichier blacklist n\'existe pas.</p>';
    }
    ?>
</body>
</html>