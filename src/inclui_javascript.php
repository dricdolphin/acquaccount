<?php
/*****************
 * Classe inclui_javascript
 * ------------------------
 * Contém o cabeçalho de JavaScript utilizado pelo sistema
 */


 class inclui_javascript {

    function __construct() {

    }

    function exibe_html () {
        $html = "        <script src=\"https://accounts.google.com/gsi/client\" async defer></script>
        <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js\" async defer></script>
        <script src=\"https://apis.google.com/js/api.js\" async defer></script>
        <script src=\"https://www.gstatic.com/charts/loader.js\" async defer></script>
        <script src=\"https://cdn.jsdelivr.net/npm/underscore@latest/underscore-umd-min.js\" async defer></script>
        <script src=\"./js/processa_login_google.js\" async defer></script>
        <script src=\"./js/uploads.js\" async defer></script>
        <script src=\"./js/processa_csv.js\" async defer></script>
        <script src=\"./js/processa_forms.js\" async defer></script>
        <script src=\"./js/valida_forms.js\" async defer></script>
        <script src=\"./js/w3c_js.js\" async defer></script>
        <script src=\"./js/google_charts.js\" async defer></script>";
        
        return  $html;
    }
 }

?>