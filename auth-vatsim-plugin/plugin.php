<?php

return array(
    'id' =>             'vatsim:oauth2', # notrans
    'version' =>        '0.6',
    'name' =>           'VATSIM Authentication 2',
    'author' =>         'VATSIM Web Department <tech@vatsim.net>',
    'description' =>    'Authenticates using the VATSIM Connect OAuth2.0',
    'plugin' =>         'authentication.php:OauthAuthPlugin',
    'requires' => array(
        "ohmy/auth" => array(
            "version" => "*",
            "map" => array(
                "ohmy/auth/src" => 'lib',
            )
        ),
    ),
);

?>
