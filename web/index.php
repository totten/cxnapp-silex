<?php
use Civi\Cxn\Rpc\Constants;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('display_error', 0);

$app = new Silex\Application();

$app['config'] = function () {
  return new \Civi\Cxn\App\AdhocConfig();
};

// OPTIONAL: Provide a nice endpoint for enterprising web surfers.
$app->get('/', function () use ($app) {
  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  $appMeta = $config->getMetadata();
  return new Response(
    $appMeta['title'] . "\n\n" . $appMeta['desc'],
    200,
    array('Content-Type' => 'text/plain')
  );
});

// OPTIONAL: Facilitate testing by publishing this
// app's metadata at a public URL.
$app->get('/cxn/metadata.json', function () use ($app) {
  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  return new Response(
    json_encode($config->getMetadata()),
    200,
    array('Content-Type' => 'application/javascript')
  );
});

// OPTIONAL: Facilitate testing by publishing a feed of all
// apps.
$app->get('/cxn/apps', function () use ($app) {
  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];
  $appMeta = $config->getMetadata();
  $message = new \Civi\Cxn\Rpc\Message\AppMetasMessage(
    $appMeta['appCert'],
    $config->getKeyPair(),
    array($config->getId() => $config->getMetadata())
  );

  return $message->toSymfonyResponse();
});

// REQUIRED: Provide an endpoint for processing registrations.
$app->post('/cxn/register', function () use ($app) {
  /** @var \Civi\Cxn\App\AdhocConfig $config */
  $config = $app['config'];

  $server = new \Civi\Cxn\Rpc\RegistrationServer($config->getMetadata(), $config->getKeyPair(), $config->getCxnStore());
  $server->setLog($config->getLog('RegistrationServer'));
  return $server->handle(file_get_contents('php://input'))->toSymfonyResponse();
});

$app->error(function ($e) use ($app) {
  $app['config']->getLog('index.php')->error("Unhandled exception", array(
    'exception' => $e,
  ));
});

$app->run();
