<?php
use Civi\Cxn\Rpc\Constants;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('display_error', 0);

$app = new Silex\Application();

$app['config'] = function() {
  return new \Civi\Cxn\App\AdhocConfig();
};

$app->get('/', function () use ($app) {
  header('Content-type: text/plain');

  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  $appMeta = $config->getMetadata();
  return $appMeta['desc'];
});

$app->get('/cxn/metadata.json', function () use ($app) {
  header('Content-Type: application/javascript');

  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  $appMeta = $config->getMetadata();
  return json_encode($appMeta);
});

$app->post('/cxn/register', function () use ($app) {
  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];

  header('Content-type: ' . Constants::MIME_TYPE);
  $server = new \Civi\Cxn\Rpc\RegistrationServer($config->getMetadata(), $config->getKeyPair(), $config->getCxnStore());
  $server->handleAndRespond(file_get_contents('php://input'));
});

$app->run();
