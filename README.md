osTicket plugin for VATSIM Connect
=========================

Based on [senzil/osticket-google-oauth2-plugin](https://github.com/senzil/osticket-google-oauth2-plugin) and sample code from [aaronpk](https://gist.github.com/aaronpk/3612742).

Installing
==========

Clone this repo or download the zip file and place the contents into
`include/plugins/auth-vatsim-plugin` folder.

After cloning, cd to the `/auth-vatsim-plugin` folder and `hydrate` the repo by downloading the third-party library
dependencies.

    php make.php hydrate
    
Visit your OSTicket admin panel to install the plugin, then set the `client_id` and `client_secret` in the plugin settings. Choose what sort of authentication you would like.


Blank Page on google redirect to system
=======================================
You can fix it fast changing a little the file /api/.htaccess

    <IfModule mod_rewrite.c>

    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.*/api)

    #RewriteRule ^(.*)$ %1/http.php/$1 [L]
    RewriteRule ^(.*)$ {put your schema:domain:port}/api/http.php/$1 [L]
    </IfModule>


    


