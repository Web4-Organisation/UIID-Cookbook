<?php
$lastResult = null;
if (isset($_SESSION['last_api_result'])) {
    $lastResult = $_SESSION['last_api_result'];
    unset($_SESSION['last_api_result']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - UIID Demosuite</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            display: flex; 
            background-color: #121212; 
            color: #e0e0e0;
            margin: 0;
        }
        nav { 
            width: 250px; 
            border-right: 1px solid #333; 
            padding: 20px; 
            background-color: #1e1e1e;
        }
        nav h2 {
            color: #ffffff;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
        }
        nav ul {
            list-style: none;
            padding: 0;
        }
        nav ul li a {
            color: #a0a0a0;
            text-decoration: none;
            display: block;
            padding: 8px 0;
            transition: color 0.3s;
        }
        nav ul li a:hover {
            color: #ffffff;
        }
        main { 
            flex-grow: 1; 
            padding: 20px; 
        }
        h1, h2, h3 {
            color: #ffffff;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        form { 
            background-color: #1e1e1e;
            border: 1px solid #333; 
            padding: 20px; 
            margin-bottom: 20px; 
            border-radius: 8px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            color: #a0a0a0;
        }
        input[type='text'] { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 15px; 
            box-sizing: border-box; 
            background-color: #333;
            border: 1px solid #555;
            color: #e0e0e0;
            border-radius: 4px;
        }
        button { 
            padding: 12px 20px; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        pre { 
            background-color: #1e1e1e; 
            border: 1px solid #333;
            padding: 15px; 
            border-radius: 5px; 
            white-space: pre-wrap; 
            word-wrap: break-word; 
            color: #d0d0d0;
        }
        .logout { 
            display: inline-block; 
            margin-top: 20px; 
            padding: 10px 15px; 
            background-color: #c9302c; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            transition: background-color 0.3s;
        }
        .logout:hover {
            background-color: #ac2925;
        }
    </style>
</head>
<body>
    <nav>
        <h2>API Endpoints</h2>
        <ul>
            <li><a href="#core">Core ID</a></li>
            <li><a href="#alias">Alias</a></li>
            <li><a href="#audit">Audit</a></li>
        </ul>
        <a href="index.php?action=logout" class="logout">Logout</a>
    </nav>
    <main>
        <h1>UIID API Dashboard</h1>
        
        <?php if ($lastResult !== null): ?>
        <h2>Last API Result:</h2>
        <pre><?php echo htmlspecialchars(json_encode($lastResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        <?php endif; ?>

        <h2 id="core">Core ID Endpoints</h2>
        
        <form action="index.php?action=api_call" method="post">
            <h3>GET /api/v1/core/kyc/status</h3>
            <input type="hidden" name="method" value="GET">
            <input type="hidden" name="endpoint" value="/api/v1/core/kyc/status">
            <button type="submit">Get KYC Status</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>GET /api/v1/core/applications</h3>
            <input type="hidden" name="method" value="GET">
            <input type="hidden" name="endpoint" value="/api/v1/core/applications">
            <button type="submit">Get Authorized Applications</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>POST /api/v1/core/key-value/update</h3>
            <input type="hidden" name="method" value="POST">
            <input type="hidden" name="endpoint" value="/api/v1/core/key-value/update">
            <label for="kv_key">Key:</label>
            <input type="text" id="kv_key" name="params[theme]" value="dark">
            <label for="kv_value">Value:</label>
            <input type="text" id="kv_value" name="params[notifications_enabled]" value="false">
            <button type="submit">Update Key-Value</button>
        </form>

        <h2 id="alias">Alias Endpoints</h2>

        <form action="index.php?action=api_call" method="post">
            <h3>GET /api/v1/aliases</h3>
            <input type="hidden" name="method" value="GET">
            <input type="hidden" name="endpoint" value="/api/v1/aliases">
            <button type="submit">Get All Aliases</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>POST /api/v1/aliases/create</h3>
            <input type="hidden" name="method" value="POST">
            <input type="hidden" name="endpoint" value="/api/v1/aliases/create">
            <label for="alias_name">Alias Name:</label>
            <input type="text" id="alias_name" name="params[name]" required>
            <button type="submit">Create Alias</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>GET /api/v1/aliases/data</h3>
            <input type="hidden" name="method" value="GET">
            <input type="hidden" name="endpoint" value="/api/v1/aliases/data">
            <label for="alias_id_data">Alias ID:</label>
            <input type="text" id="alias_id_data" name="params[alias_id]" required>
            <button type="submit">Get Alias Data</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>GET /api/v1/aliases/private-data</h3>
            <input type="hidden" name="method" value="GET">
            <input type="hidden" name="endpoint" value="/api/v1/aliases/private-data">
            <label for="alias_id_private_data">Alias ID:</label>
            <input type="text" id="alias_id_private_data" name="params[alias_id]" required>
            <button type="submit">Get Private Alias Data</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>POST /api/v1/aliases/private-data/update</h3>
            <input type="hidden" name="method" value="POST">
            <input type="hidden" name="endpoint" value="/api/v1/aliases/private-data/update">
            <label for="alias_id_update">Alias ID:</label>
            <input type="text" id="alias_id_update" name="params[alias_id]" required>
            <label for="alias_data_key">Key:</label>
            <input type="text" id="alias_data_key" name="params[key]" value="private_key">
            <label for="alias_data_value">Value:</label>
            <input type="text" id="alias_data_value" name="params[value]" value="private_value">
            <button type="submit">Update Private Alias Data</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>POST /api/v1/aliases/delete</h3>
            <input type="hidden" name="method" value="POST">
            <input type="hidden" name="endpoint" value="/api/v1/aliases/delete">
            <label for="alias_id_delete">Alias ID:</label>
            <input type="text" id="alias_id_delete" name="params[alias_id]" required>
            <button type="submit">Delete Alias</button>
        </form>

        <h2 id="audit">Audit Log Endpoints</h2>

        <form action="index.php?action=api_call" method="post">
            <h3>GET /api/v1/audit/core</h3>
            <input type="hidden" name="method" value="GET">
            <input type="hidden" name="endpoint" value="/api/v1/audit/core">
            <button type="submit">Get Core Audit Logs</button>
        </form>

        <form action="index.php?action=api_call" method="post">
            <h3>GET /api/v1/audit/alias</h3>
            <input type="hidden" name="method" value="GET">
            <input type="hidden" name="endpoint" value="/api/v1/audit/alias">
            <label for="alias_id_audit">Alias ID:</label>
            <input type="text" id="alias_id_audit" name="params[alias_id]" required>
            <button type="submit">Get Alias Audit Logs</button>
        </form>

    </main>
</body>
</html>