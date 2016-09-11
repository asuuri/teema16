<!DOCTYPE html>
<html>
    <head>
        <title>Teema16</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
<?php

require_once '../config.php';

$handle = stream_socket_client($config['local_socket_path']);

if ($handle) {
    while (($buffer = fgets($handle)) !== false) { ?>
        <script>
        parent.postMessage(<?php
            echo trim($buffer);
            ob_flush();
            flush();
        ?>, '*');
        </script>

        
        
        
        
        
        
        
        
        

        
        
        
        
        
        
<?php
    }
}

fclose($handle);?>
</body>
</html>