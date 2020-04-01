<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class OauthAuthPlugin extends Plugin {
    var $config_class = "OauthPluginConfig";

    function bootstrap() {
        $config = $this->getConfig();

        $vatsim = $config->get('v-enabled');
        if (in_array($vatsim, array('all', 'staff'))) {
            require_once('vatsim.php');
            StaffAuthenticationBackend::register(
                new VATSIMStaffAuthBackend($this->getConfig()));
        }
        if (in_array($vatsim, array('all', 'client'))) {
            require_once('vatsim.php');
            UserAuthenticationBackend::register(
                new VATSIMClientAuthBackend($this->getConfig()));
        }
    }
}

require_once(INCLUDE_DIR.'UniversalClassLoader.php');
use Symfony\Component\ClassLoader\UniversalClassLoader_osTicket;
$loader = new UniversalClassLoader_osTicket();
$loader->registerNamespaceFallbacks(array(
    dirname(__file__).'/lib'));
$loader->register();