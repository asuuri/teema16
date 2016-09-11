<!DOCTYPE html>
<html>
    <head>
        <title>Teema16</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='css/main.css' rel='stylesheet' type='text/css' />

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script>
            function newTweet(data) {
                console.log(data);
                $div = $('<div/>', {'id': data.id, 'class': 'tweet'});

                $content = $('<span/>', {'class': 'content'});
                if (data.hasOwnProperty('retweeted_status')) {
                    $content.html(data.retweeted_status.text);
                } else {
                    $content.html(data.text);
                }

                $user = $('<strong/>', {'class': 'user'});
                $user.html(data.user.name);
                $user.append(
                    $(
                        '<span/>',
                        {
                            'class': 'username',
                            'html': ' @' + data.user.screen_name
                        }
                    )
                );

                $userImage = $(
                    '<img/>',
                    {
                        'src': data.user.profile_image_url_https,
                        'class': 'usrImage'
                    }
                );

                $div.append($userImage, $user, $content);

                if (data.hasOwnProperty('entities') &&
                    data.entities.hasOwnProperty('media')) {
                    imgUrl = data.entities.media[0].media_url_https;

                    $mediaImage = $(
                        '<img/>',
                        {'src': imgUrl, 'class': 'mediaImage'}
                    );
                }

                $div.append($mediaImage);

                $('#tweetContainer').prepend($div);
            }
            window.addEventListener('message', function(event) {
                newTweet(event.data);
            });
        </script>
    </head>
    <body>
        <div id="tweetContainer"></div>
        <iframe src="read.php" style="display: none;">
    </body>
</html>
