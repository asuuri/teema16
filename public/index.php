<!DOCTYPE html>
<html>
    <head>
        <title>Teema16</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script>
            function newTweet(data) {
                console.log(data);
            }
            window.addEventListener('message', function(event) {
                newTweet(event.data);
            });
        </script>
    </head>
    <body>
        <div>teema16</div>
        <iframe src="read.php" style="display: none;">
    </body>
</html>
