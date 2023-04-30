<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************
 * Classe processa_arquivo
 * -----------------
 * 
 * Processa arquivos enviados do site
 * 
 */
 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'autoload.php';

class processa_arquivo {

    function __construct() {

    }

    function recebe_arquivo() {
        $message = ''; //Se tudo estiver bem, a resposta deve ser OK
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
          $fileTmpPath = $_FILES['file']['tmp_name'];
          $fileName = $_FILES['file']['name'];
          $fileSize = $_FILES['file']['size'];
          $fileType = $_FILES['file']['type'];
          $fileNameCmps = explode(".", $fileName);
          $fileExtension = strtolower(end($fileNameCmps));
          $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
          $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'tif');
          if (in_array($fileExtension, $allowedfileExtensions))
          {
            $uploadFileDir = './upload_files/';
            $dest_path = $uploadFileDir . $newFileName;
            if(move_uploaded_file($fileTmpPath, $dest_path)) 
            {
              $message = [];
              $message['arquivo'] = $newFileName; //Se tudo estiver bem, a resposta deve ser o nome do arquivo
              $message['debug'] = "";
              if (isset($_POST['replace'])) {
                $host = $_SERVER['HTTP_HOST'];
                $protocol = $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
                $url = "$protocol://$host/";
                $endereco_img_replace = str_replace($url,"./",stripslashes($_POST['replace']));
                //Verifica se o arquivo dado pelo $_POST['replace'] existe
                if ($arquivo_antigo = realpath(stripslashes(html_entity_decode($endereco_img_replace)))) {
                  unlink($arquivo_antigo);
                }
              }
              $message = html_entity_decode(json_encode($message));
            }
            else 
            {
              header($_SERVER["SERVER_PROTOCOL"]." 400 Não Autorizado",true,400);
              $message = 'Erro ao mover o arquivo!';
            }
          }
          else
          {
            header($_SERVER["SERVER_PROTOCOL"]." 400 Não Autorizado",true,400);
            $message = 'Somente as extensões à seguir podem ser enviadas: ' . implode(',', $allowedfileExtensions);
          }
        }
        else
        {
          header($_SERVER["SERVER_PROTOCOL"]." 400 Não Autorizado",true,400);
          $message = 'Erro ao realizar o upload!<br>';
          $message .= 'Erro:' . $_FILES['file']['error'];
        }
        die($message);  
    }
}

require_once 'config.php';
require_once 'src/conecta_db.php';
require_once 'src/perfil.php';
require_once 'src/user.php';

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados

if (isset($_POST['uploadBtn']) && $_POST['uploadBtn'] == 'Upload' && isset($_SESSION['dados_cliente']))
{
    $user = new user();
    $user->pega_user_por_email($_SESSION['dados_cliente']['email']);
    $perfil = new perfil();
    $perfil->pega_perfil_por_id($user->pega_id_perfil());

    if ($perfil->admin() || $perfil->cadastrador()) {
        $processa_arquivo = new processa_arquivo();
        $processa_arquivo->recebe_arquivo();
    }
    header($_SERVER["SERVER_PROTOCOL"]." 400 Não Autorizado",true,400);
    die('Erro ao receber o arquivo!');
}
header($_SERVER["SERVER_PROTOCOL"]." 400 Não Autorizado",true,400);
die('Erro! Verifique a origem do upload!');
?>