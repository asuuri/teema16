<!DOCTYPE html>
<html>
    <head>
        <title>Teema16</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css?family=Material+Icons|Libre+Franklin:400,700&subset=latin-ext" rel="stylesheet">
        <link href='css/main.css' rel='stylesheet' type='text/css' />

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script>
            var isOdd = true;
            var tweets = [];
            var barkTime = Date.now();
            function showTweet($div) {
                $('#tweetContainer').prepend($div);

                $div.css({
                    'margin-top': '-' + ($div.outerHeight(true) - 20) + 'px'
                });

                $div.animate(
                    {
                        'margin-top': '10px'
                    },
                    {
                        'duration': 500,
                        'always': function() {
                            $div.css({'opacity': 1});
                        }
                    }
                );
            };

            function newTweet(data) {
                var text;

                if (data.hasOwnProperty('retweeted_status')) {
                    var $retDiv = $('#' + data.retweeted_status.id + ' .retweets');
                    if($retDiv.length) {
                        $retDiv.text(data.retweeted_status.retweet_count);
                    }
                    return;
                } else if (document.getElementById(data.id)) {
                    return;
                } else {
                    text = data.text;
                }

                var $div = $(
                    '<div/>',
                    {
                        'id': data.id,
                        'class': 'tweet ' + (isOdd?'odd':'even'),
                        'style': 'opacity: 0;'
                    }
                );

                var $content = $('<span/>', {'class': 'content'});


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
                        'class': 'usrImage'
                    }
                );

                var $retweets = $('<span/>', {'class': 'retweets', 'text': data.retweet_count});

                $div.append($userImage, $user, $content, $retweets, $('<span/>', {'class': 'tick'}));

                if (data.hasOwnProperty('entities') &&
                    data.entities.hasOwnProperty('media')) {
                    var imgUrl = data.entities.media[0].media_url_https;
                    var $mediaImage = $(
                        '<img/>',
                        {
                            'src': imgUrl,
                            'class': 'mediaImage'
                        }
                    );
                    $mediaImage.on('load', function() { tweets.push($div); });
                    $div.append($mediaImage);
                } else {
                    tweets.push($div);
                }



                isOdd = !isOdd;
            }

            function watchdog() {
                barkTime = Date.now();
            }

            function reload() {
                var iframe = document.getElementById('iframeWindow');
                iframe.contentWindow.location.reload();
            }

            window.addEventListener('message', function(event) {
                if (event.data.hasOwnProperty('watchdog')) {
                    watchdog();
                } else if (event.data.hasOwnProperty('reload_frame')) {
                    reload();
                } else {
                    newTweet(event.data);
                }
            });

            setInterval(
                function() {
                    if (tweets[0]) {
                        showTweet(tweets[0]);
                        tweets.splice(0, 1);
                    }
                },
                100
            );

            setInterval(
                function() {
                    var delta = Date.now() - barkTime;
                    if (delta > 2500) {
                        barkTime = Date.now();
                        reload();
                    }
                },
                1000
            );
        </script>
    </head>
    <body>
        <?php
        require_once '../config.php';
        printf('<h1 class="hashTag">%s</h1>', $config['track']);
        ?>
        <div id="tweetContainer"></div>
        <iframe id="iframeWindow" src="read.php" style="display: none;">
    </body>
</html>
