<?php
/*************************
 * Classe processa_login_google
 * ----------------------------
 * 
 * Classe utilizada para processar os dados de login 
 * via Google API
 * 
 */

class processa_login_google {
    private $client;
    
    function __construct() {
        require_once 'config.php';
        require_once 'vendor/autoload.php';
        $this->client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend
    }

    function valida_token($token) {
        $payload = $this->client->verifyIdToken($token);
        if ($payload) {
            $userid = $payload['sub'];
            // If request specified a G Suite domain:
            //$domain = $payload['hd'];
            return $payload;
        } else {
            // Invalid ID token
            return false;
        }
    }

    function revokeToken($token) {
        return $this->client->revokeToken($token);
    }

    function botao_google() {
        $data_auto_select = "true";
        if (isset($_GET['logout'])) {
          if ($_GET['logout'] == 1) {
            $data_auto_select = "false";
          }
        }
        
        $html = "
        <div class=\"w3-bar-item w3-button w3-hover-none w3-hover-text-light-grey\">
          <div id=\"g_id_onload\"
            data-client_id=\"".GOOGLE_CLIENT_ID."\" 
            data-auto_select=\"{$data_auto_select}\"      
            data-login_uri=\"".GOOGLE_REDIRECT_URL."\">
          </div>
          
          <div class=\"g_id_signin\"
              data-type=\"standard\"
              data-shape=\"rectangular\"
              data-theme=\"outline\"
              data-text=\"signin_with.\"
              data-size=\"large\"
              data-logo_alignment=\"left\">
          </div>
        </div>";

        return $html;
    }
}
?>