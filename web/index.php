<?php
use Civi\Cxn\Rpc\AppIdentity;
use Civi\Cxn\Rpc\AppServer;
use Civi\Cxn\Rpc\CaIdentity;
use Civi\Cxn\Rpc\Constants;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('display_error', 0);

$app = new Silex\Application();

$app['config'] = function() {
  return new \Civi\Cxn\Adhoc\AdhocConfig();
};

$app->get('/', function () use ($app) {
  return 'This is the adhoc connection app. Once connected, the app-provider can make API calls to your site.';
});

$app->get('/cxn', function () use ($app) {
  return 'This is the adhoc connection app. Once connected, the app-provider can make API calls to your site.';
});

$app->post('/cxn', function () use ($app) {
  /** @var \Civi\Cxn\Adhoc\AdhocConfig $config */
  $config = $app['config'];

  header('Content-type: ' . Constants::MIME_TYPE);
  $server = new \Civi\Cxn\Rpc\RegistrationServer($config->getMetadata(), $config->getKeyPair(), $config->getCxnStore());
  $server->handleAndRespond(file_get_contents('php://input'));
});

$app->run();
