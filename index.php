<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Systemctl Supervisor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f4fe; /* Hintergrundfarbe */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: top;
            height: 100vh;
            margin: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            color: #6a49f2; /* Hauptfarbe */
        }
        table {
            width: 80%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #6a49f2; /* Hauptfarbe */
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        button {
            border: none;
            padding: 10px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
        }
        .start {
            background-color: #2f99a0; /* Sekundärfarbe */
        }
        .reboot {
            background-color: #FFC107; /* Gelb */
        }
        .stop {
            background-color: #F44336; /* Rot */
        }
    </style>
</head>
<body>
    <?php
    error_reporting(-1);

    function getServices() {
        $output = shell_exec('systemctl list-units --type=service --no-legend --plain');
        $services = explode("\n", trim($output));
        return array_map(function($service) {
            $parts = preg_split('/\s+/', $service);
            return [
                'name' => $parts[0],
                'status' => $parts[2]
            ];
        }, $services);
    }

    function manageService($action, $serviceName) {
        switch ($action) {
            case 'start':
                shell_exec("sudo systemctl start $serviceName");
                break;
            case 'stop':
                shell_exec("sudo systemctl stop $serviceName");
                break;
            case 'restart':
                shell_exec("sudo systemctl restart $serviceName");
                break;
        }
    }

    if (isset($_GET['action']) && isset($_GET['service'])) {
        $action = $_GET['action'];
        $serviceName = $_GET['service'];
        manageService($action, $serviceName);
    }
    ?>

    <div class="header">
        <h1>My Systemctl Supervisor</h1>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Start</th>
                <th>Restart</th>
                <th>Stop</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $services = getServices();
            foreach ($services as $service) {
                $name = htmlspecialchars($service['name']);
                $status = htmlspecialchars($service['status']);
                $canStart = $status === 'inactive' || $status === 'failed';
                $canRestart = $status === 'active' || $status === 'failed';
                $canStop = $status === 'active';

                echo "<tr>
                        <td>$name</td>
                        <td>$status</td>
                        <td>" . ($canStart ? "<a href=\"?action=start&service=" . urlencode($name) . "\"><button class=\"start\">Start</button></a>" : "Started") . "</td>
                        <td>" . ($canRestart ? "<a href=\"?action=restart&service=" . urlencode($name) . "\"><button class=\"reboot\">Restart</button></a>" : "N/A") . "</td>
                        <td>" . ($canStop ? "<a href=\"?action=stop&service=" . urlencode($name) . "\"><button class=\"stop\">Stop</button></a>" : "N/A") . "</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
