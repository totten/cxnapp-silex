<?php
use Civi\Cxn\Rpc\Constants;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('display_error', 0);

$app = new Silex\Application();

$app['config'] = function () {
  return new \Civi\Cxn\App\AdhocConfig();
};

// OPTIONAL: Provide a nice endpoint for enterprising web surfers.
$app->get('/', function () use ($app) {
  header('Content-type: text/plain');

  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  $appMeta = $config->getMetadata();
  return $appMeta['desc'];
});

// OPTIONAL: Facilitate testing by publishing this
// app's metadata at a public URL.
$app->get('/cxn/metadata.json', function () use ($app) {
  header('Content-Type: application/javascript');

  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  return json_encode($config->getMetadata());
});

// OPTIONAL: Facilitate testing by publishing a feed of all
// apps.
$app->get('/cxn/apps', function () use ($app) {
  header('Content-Type: ' . Constants::MIME_TYPE);

  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  $appMeta = $config->getMetadata();
  $message = new \Civi\Cxn\Rpc\Message\AppMetasMessage(
    $appMeta['appCert'],
    $config->getKeyPair(),
    array($config->getId() => $config->getMetadata())
  );

  $message->send();
  exit();
});

// REQUIRED: Provide an endpoint for processing registrations.
$app->post('/cxn/register', function () use ($app) {
  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];

  header('Content-type: ' . Constants::MIME_TYPE);
  $server = new \Civi\Cxn\Rpc\RegistrationServer($config->getMetadata(), $config->getKeyPair(), $config->getCxnStore());
  $server->setLog($config->getLog('RegistrationServer'));
  $server->handle(file_get_contents('php://input'))->send();
  exit();
});

$app->error(function ($e) use ($app) {
  $app['config']->getLog('index.php')->error("Unhandled exception", array(
    'exception' => $e,
  ));
});

$app->run();
