<?php
require_once __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require './unsafe/creditals.php';

$helper = $fb->getRedirectLoginHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (! isset($accessToken)) {
  if ($helper->getError()) {
    header('HTTP/1.0 401 Unauthorized');
    echo "Error: " . $helper->getError() . "\n";
    echo "Error Code: " . $helper->getErrorCode() . "\n";
    echo "Error Reason: " . $helper->getErrorReason() . "\n";
    echo "Error Description: " . $helper->getErrorDescription() . "\n";
  } else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Bad request';
  }
  exit;
}

// Logged in
echo '<h3>Access Token</h3>';
var_dump($accessToken->getValue());

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($accessToken);
echo '<h3>Metadata</h3>';
var_dump($tokenMetadata);

// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId('2029638407338210'); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
$tokenMetadata->validateExpiration();

if (! $accessToken->isLongLived()) {
  // Exchanges a short-lived access token for a long-lived one
  try {
    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
    exit;
  }

  echo '<h3>Long-lived</h3>';
  var_dump($accessToken->getValue());
}
	try {
          // Get the \Facebook\GraphNodes\GraphUser object for the current user.
          // If you provided a 'default_access_token', the '{access-token}' is optional.
          $response = $fb->get('/me?fields=name,email,location,gender,birthday,hometown', $accessToken);
      } catch(\Facebook\Exceptions\FacebookResponseException $e) {
          echo $e->getMessage();
      } catch(\Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo $e->getMessage();
      }

      // Get user details from facebook.
      $me = $response->getGraphUser();

     print_r($me);
	echo '<br>';
	$email = $me['email'];
	$name = $me['name'];
$_SESSION['fb_access_token'] = (string) $accessToken;
require './repetitive/dbconnect.php';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $dsn = "mysql:host=$server;dbname=$database;charset=$charset";
    try {
         $link = new PDO($dsn, $user, $pass);
    } catch (\PDOException $e) {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
    } 
        if (!$link) {
            printf($messErr_connectionDatabaseFailed);
            printf("<br />");
        }else{
          $stmt = $link -> prepare('SELECT * FROM Users WHERE email=?');
          $stmt -> bindParam(1, $email);
          $stmt -> execute();
          $data = $stmt-> fetch();
          if($data['email'] == $email and $data['passwordHash'] != 'facebook login'){
              header("Location: ./login.php?status=user_exists");
              exit;
          }else if($data['email'] == $email and $data['passwordHash'] == 'facebook login'){
              $_SESSION['id']=$data['userID'];
              $_SESSION['email']=$email;
              $_SESSION['privilege']=$data['privilege'];
              header("Location: ./shop.php");
              exit;
          }else{
              $stmt = $link -> prepare('INSERT INTO Users (email, userName, passwordHash, privilege) VALUES (?,?,?,1)');
              $stmt -> execute([$email,$name,'facebook login']);
              header("Location: ./shop.php");
              exit;
          }

        }
?>
