<!DOCTYPE html>
<html>
    <head>
        <title>Teema16</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css?family=Libre+Franklin:400,700&subset=latin-ext" rel="stylesheet">
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
                //console.log(data);

                if (data.hasOwnProperty('retweeted_status')) {
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

                $div.append($userImage, $user, $content, $('<span/>', {'class': 'tick'}));

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
                console.log('bark!');
                barkTime = Date.now();
            }

            window.addEventListener('message', function(event) {
                if (event.data.hasOwnProperty('watchdog')) {
                    watchdog();
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
                    if (delta > 1500) {
                        console.log('reload');
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
        <iframe src="read.php?clean" style="display: none;">
    </body>
</html>
