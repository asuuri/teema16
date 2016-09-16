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
require_once '../src/twitter.php';
$time = time();
$twitter = new Twitter($config);

$tweets = array_reverse($twitter->readFirstOnes(7));

foreach ($tweets as $tweet) { ?>
        <script>
        parent.postMessage(<?php echo json_encode($tweet); ?>, '*');
        </script>
<?php
}

if ($config['fifo_path']) {
    $handle = fopen($config['fifo_path'], 'r');

    if ($handle) {
        stream_set_blocking($handle, false);
    } else {
        $err = error_get_last();
        var_dump($err);
        echo "Unable to open fifo. </body></html>";
        die;
    }
} else {
    echo "Unable to open fifo. </body></html>";
    die;
}

if ($handle) {
    while (true) { 
        $buffer = fgets($handle); 
        if ($buffer) { ?>
        
        <script>
        parent.postMessage(<?php
            echo trim($buffer);
        ?>, '*');
        </script>

        
        
        
        
        
        
        
        
        

        
        
        
        
        
        
<?php
        }

        //sleep(0.2);

        $newTime = time();
        if ($newTime - $time >= 1) {
            $time = $newTime; ?>
       
        <script>
        parent.postMessage({'watchdog':<?php
            echo $newTime;
        ?>}, '*');
        </script>















<?php
        }

        flush();
    }
}

fclose($handle);?>
</body>
</html>
