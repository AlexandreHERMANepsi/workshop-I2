<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Proxy</title>
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
        h1 {
            color: #2c3e50;
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .menu {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .menu a {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .menu a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    echo "<h1>Gestion du Proxy</h1>";

    echo '<div class="menu">';
    echo '<a href="manage_users.php">Gérer les utilisateurs</a>';
    echo '<a href="manage_rights.php">Gérer les droits</a>';
    echo '<a href="notification.php">Historique</a>';
    echo '</div>';
    ?>
<!-- Icône de notification -->
    <a href="notification.php" class="Historiques">
        <i class="fas fa-bell"></i>
    </a>

</body>
</html>
