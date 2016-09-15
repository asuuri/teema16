<!DOCTYPE html>
<html>
    <head>
        <title>Teema16</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='css/main.css' rel='stylesheet' type='text/css' />

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script>
            var isOdd = true;
            function newTweet(data) {
                var text;
                console.log(data);
                var $div = $(
                    '<div/>',
                    {'id': data.id, 'class': 'tweet ' + (isOdd?'odd':'even')}
                );

                var $content = $('<span/>', {'class': 'content'});
                if (data.hasOwnProperty('retweeted_status')) {
                    text = data.retweeted_status.text;
                } else {
                    text = data.text;
                }

                $.each(data.entities.hashtags, function(index, hashtag) {
                    text = text.replace(
                        '#' + hashtag.text,
                        '<span class="hashtag">#' + hashtag.text + '</span>'
                    );
                });

                $.each(data.entities.user_mentions, function(index, user) {
                    text = text.replace(
                        '@' + user.screen_name,
                        '<span class="mentioned">@' + user.screen_name + '</span>'
                    );
                });

                $content.html(text);

                var $user = $('<strong/>', {'class': 'user'});
                $user.html('@' + data.user.screen_name);

                var $userImage = $(
                    '<img/>',
                    {
                        'src': data.user.profile_image_url_https,
                        'class': 'usrImage',
                        'onload': function () {console.log($div)}
                    }
                );

                $div.append($userImage, $user, $content, $('<span/>', {'class': 'tick'}));

                if (data.hasOwnProperty('entities') &&
                    data.entities.hasOwnProperty('media')) {
                    imgUrl = data.entities.media[0].media_url_https;

                    var $mediaImage = $(
                        '<img/>',
                        {'src': imgUrl, 'class': 'mediaImage'}
                    );
                    $div.append($mediaImage);
                }

                $('#tweetContainer').prepend($div);
                isOdd = !isOdd;
            }

            function watchdog(time) {
                console.debug(time);
            }

            window.addEventListener('message', function(event) {
                if (event.data.hasOwnProperty('watchdog')) {
                    watchdog(event.data.watchdog);
                } else {
                    newTweet(event.data);
                }
            });
        </script>
    </head>
    <body>
        <?php
        require_once '../config.php';
        printf('<h1 class="hashTag">%s</h1>', $config['track']);
        ?>
        <div id="tweetContainer"></div>
        <iframe src="read.php" style="display: none;">
    </body>
</html>
