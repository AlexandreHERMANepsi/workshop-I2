<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation des Enfants</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            padding: 20px 0;
            border-bottom: 2px solid #3498db;
            margin-bottom: 30px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: box-shadow 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card h2 {
            margin-top: 0;
            color: #3498db;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .card p {
            color: #333;
            line-height: 1.6;
            font-size: 16px;
        }
        .site-link {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .site-link:hover {
            text-decoration: underline;
        }
        /* Retour bouton */
        .back-button {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<h1>Informations sur la navigation des enfants</h1>

<div class="container">
    <?php
    // Chemin du fichier de log Squid
    $logFile = '/var/log/squid/access.log';

    // Vérifier si le fichier existe et est lisible
    if (file_exists($logFile) && is_readable($logFile)) {
        // Lire les lignes du fichier de log
        $logContent = file($logFile,  FILE_SKIP_EMPTY_LINES);

        // Tableau pour stocker les accès refusés par utilisateur
        $deniedData = [];

        // Parcourir chaque ligne du fichier de log
        foreach ($logContent as $line) {
            // Extraire les champs du fichier de log Squid
            $parts = preg_split('/\s+/', $line);

            if (count($parts) > 7) {
                // Le format attendu : timestamp, duration, client_ip, status, size, method, URL, user (si disponible)
                $status = $parts[3]; // Statut (ex: TCP_DENIED/403)
                $url = $parts[6]; // L'URL (généralement le 6ème élément)
                $userOrComputer = $parts[7]; // Identifiant utilisateur ou nom d'ordinateur (le 7ème élément)

                // Filtrer les lignes avec "DENIED"
                if (strpos($status, 'DENIED') !== false) {
                    // Si le champ utilisateur/ordinateur n'est pas vide
                    if (!empty($userOrComputer) && $userOrComputer !== "-") {
                        // Ajouter les informations au tableau
                        $deniedData[$userOrComputer][] = [
                            'url' => $url,
                            'status' => $status
                        ];
                    }
                }
            }
        }

        // Afficher les informations d'accès refusés pour chaque utilisateur/ordinateur
        if (!empty($deniedData)) {
            foreach ($deniedData as $userOrComputer => $deniedSites) {
                echo '<div class="card">';
                echo "<h2>$userOrComputer</h2><p>";
                foreach ($deniedSites as $site) {
                    echo "Accès refusé à <a href=\"{$site['url']}\" class=\"site-link\">{$site['url']}</a> (Statut : {$site['status']})<br>";
                }
                echo '</p></div>';
            }
        } else {
            echo "<p>Aucun accès refusé trouvé.</p>";
        }
    } else {
        echo "<p>Le fichier de log Squid n'est pas accessible.</p>";
    }
    ?>
</div>

<!-- Bouton retour -->
<a href="index.php" class="back-button">Retour à la gestion du proxy</a>

</body>
</html>
