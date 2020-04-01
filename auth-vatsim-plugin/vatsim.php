<?php

use ohmy\Auth2;
use AccessDenied;

function apiRequest($url, $token, $headers=array())
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $headers[] = 'Accept: application/json';
    if($token)
        $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    return json_decode($response);
}

class VATSIMAuth {
    var $config;
    var $access_token;

    function __construct($config) {
        $this->config = $config;
    }

    function triggerAuth() {
        global $ost;
        $self = $this;
        return Auth2::legs(3)
            ->set('client_id', $this->config->get('client_id'))
            ->set('client_secret', $this->config->get('client_secret'))
            ->set('redirect', rtrim($ost->getConfig()->getURL(), '/') . '/api/auth/ext')
            ->set('scope', 'full_name vatsim_details email')

            ->authorize('https://auth.vatsim.net/oauth/authorize')
            ->access('https://auth.vatsim.net/oauth/token')

            ->finally(function($data) use ($self) {
                $self->access_token = $data['access_token'];
            });
    }
}

class VATSIMStaffAuthBackend extends ExternalStaffAuthenticationBackend {
    static $id = "vatsim";
    static $name = "VATSIM";

    static $service_name = "VATSIM";

    var $config;

    function __construct($config) {
        $this->config = $config;
        $this->vatsim = new VATSIMAuth($config);
    }

    function signOn() {
        // TODO: Check session for auth token
        if (isset($_SESSION[':oauth']['scid'])) {
            if (($staff = StaffSession::lookup(array('username' => $_SESSION[':oauth']['scid'])))
                && $staff->getId()
            ) {
                if (!$staff instanceof StaffSession) {
                    // osTicket <= v1.9.7 or so
                    $staff = new StaffSession($staff->getId());
                }
                return $staff;
            }
            else {
                return new AccessDenied('Your credentials are valid but you do not have a staff account.');
            }
        }
    }

    static function signOut($user) {
        parent::signOut($user);
        unset($_SESSION[':oauth']);
    }


    function triggerAuth() {
        parent::triggerAuth();
        $this->vatsim->triggerAuth();
        $token = $this->vatsim->access_token;
        $response = apiRequest("https://auth.vatsim.net/api/user", $token);

        if(!isset($response->data->cid))
            return;
        $_SESSION[':oauth']['scid'] = 's'.$response->data->cid;
        Http::redirect(ROOT_PATH . 'scp');
    }
}

class VATSIMClientAuthBackend extends ExternalUserAuthenticationBackend {
    static $id = "vatsim.client";
    static $name = "VATSIM";

    static $service_name = "VATSIM";

    function __construct($config) {
        $this->config = $config;
        $this->vatsim = new VATSIMAuth($config);
    }

    function supportsInteractiveAuthentication() {
        return false;
    }

    function signOn() {
        if (isset($_SESSION[':oauth']['cid'])) {
            $acct = ClientAccount::lookupByUsername($_SESSION[':oauth']['cid']);
            if ($acct && $acct->getId()) {
                return new ClientSession(new EndUser($acct->getUser()));
            }
            else {
                $info['name'] = $_SESSION[':oauth']['name_first'] . " " . $_SESSION[':oauth']['name_last'];
                $info['email'] = $_SESSION[':oauth']['email'];
                $info['first'] = $_SESSION[':oauth']['name_first'];
                $info['last'] = $_SESSION[':oauth']['name_last'];
                $info['username'] = $_SESSION[':oauth']['cid'];

                $client = new ClientCreateRequest($this, $_SESSION[':oauth']['cid'], $info);
                return $client->attemptAutoRegister();
            }
        }
    }

    static function signOut($user) {
        parent::signOut($user);
        unset($_SESSION[':oauth']);
    }

    function triggerAuth() {
        require_once INCLUDE_DIR . 'class.json.php';
        parent::triggerAuth();
        $this->vatsim->triggerAuth();
        $token = $this->vatsim->access_token;

        $response = apiRequest("https://auth.vatsim.net/api/user", $token);

        if(!isset($response->data->cid))
            return;
        $_SESSION[':oauth']['cid'] = $response->data->cid;
        $_SESSION[':oauth']['email'] = $response->data->personal->email;
        $_SESSION[':oauth']['name_first'] = $response->data->personal->name_first;
        $_SESSION[':oauth']['name_last'] = $response->data->personal->name_last;
        Http::redirect(ROOT_PATH . 'login.php');
    }
}
