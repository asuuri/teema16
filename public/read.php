<?php

set_time_limit(1000);
$endTime = time() + 100;
//$endTime = time() + 600; //10 mins

?><!DOCTYPE html>
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

$tweets = array_reverse($twitter->readFirstOnes(20));

foreach ($tweets as $tweet): ?>
        <script>
            if (parent) {
                parent.postMessage(<?php echo json_encode($tweet); ?>, '*');
            }
        </script>
<?php
endforeach;

if ($config['fifo_path']) {
    $handle = fopen($config['fifo_path'], 'r');

    if ($handle) {
        stream_set_blocking($handle, 0);
        stream_set_timeout($handle, 0, 500);
    } else {
        $err = error_get_last();
        echo "Unable to open fifo. </body></html>";
        die;
    }
} else {
    echo "Unable to open fifo. </body></html>";
    die;
}

if ($handle) {
    $newTime = time();
    $time = $newTime;
    while ($newTime < $endTime) {

        echo
        $buffer = fgets($handle);
        if ($buffer) { ?>

        <script>
        if (parent) {
            parent.postMessage(<?php
                echo trim($buffer);
            ?>, '*');
        }
        </script>
<?php
        }

        usleep(1000);
        if (($newTime - $time) >= 1) {
            $time = $newTime; ?>

        <p>Bark!</p>
        <script>
        if (parent) {
            parent.postMessage({'watchdog':<?php
                echo $newTime;
            ?>}, '*');
        }
        </script>















<?php
        }

        flush();
        $newTime = time();
    }
}

fclose($handle);?>
    <p>Bye!</p>
    <script>
    if (parent) {
        parent.postMessage({'reload_frame':true}, '*');
    }
    </script>
</body>
</html>
