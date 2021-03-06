<?php
namespace Civi\Cxn\App\Command;

use Civi\Cxn\App\AdhocConfig;
use Civi\Cxn\Rpc\AppMeta;
use Civi\Cxn\Rpc\CA;
use Civi\Cxn\Rpc\Constants;
use Civi\Cxn\Rpc\CxnStore\JsonFileCxnStore;
use Civi\Cxn\Rpc\KeyPair;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command {

  protected function configure() {
    $this
      ->setName('init')
      ->setDescription('Initialize the configuration files')
      ->setHelp('Example: cxnapp init "http://myapp.localhost"')
      ->addOption('id', NULL, InputOption::VALUE_REQUIRED, 'The applications guid. (Ex: "app:org.civicrm.myapp")(Default: random)')
      ->addArgument('url', InputArgument::REQUIRED, 'The registration URL where the app will be published')
      ->addArgument('basedn', InputArgument::OPTIONAL, 'The DN in the application certificate', 'O=DemoApp');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = new AdhocConfig();

    foreach(array($config->getDir(), $config->getDir() . '/local') as $dir) {
      if (!is_dir($dir)) {
        mkdir($dir);
      }
    }

    $appId = $input->getOption('id');
    if (!empty($appId) || !file_exists($config->getIdFile())) {
      $output->writeln("<info>Create id file ({$config->getIdFile()})</info>");
      if (empty($appId)) {
        $appId = AppMeta::createId();
      }
      file_put_contents($config->getIdFile(), $appId);
    }
    else {
      $output->writeln("<info>Found id file ({$config->getIdFile()})</info>");
      $appId = trim(file_get_contents($config->getIdFile()));
    }
    if (!AppMeta::validateAppId($appId)) {
      throw new \Exception("Malformed appId");
    }

    $appDn = $input->getArgument('basedn') . ", CN=$appId";

    if (!file_exists($config->getKeyFile())) {
      $output->writeln("<info>Create key file ({$config->getKeyFile()})</info>");
      $appKeyPair = KeyPair::create();
      KeyPair::save($config->getKeyFile(), $appKeyPair);
    }
    else {
      $output->writeln("<info>Found key file ({$config->getKeyFile()})</info>");
      $appKeyPair = KeyPair::load($config->getKeyFile());
    }

    if (!file_exists($config->getDemoCaFile())) {
      $output->writeln("<info>Create demo CA file ({$config->getDemoCaFile()})</info>");
      $demoCaDn = $input->getArgument('basedn') . ", CN=DemoCA";
      $demoCaCert = CA::create($appKeyPair, $demoCaDn);
      CA::save($config->getDemoCaFile(), $demoCaCert);
    }
    else {
      $output->writeln("<info>Found demo CA file ({$config->getDemoCaFile()})</info>");
      $demoCaCert = CA::load($config->getDemoCaFile());
    }

    if (!file_exists($config->getCsrFile())) {
      $output->writeln("<info>Create certificate request ({$config->getCsrFile()})</info>");
      $appCsr = CA::createCSR($appKeyPair, $appDn);
      file_put_contents($config->getCsrFile(), $appCsr);
    }
    else {
      $output->writeln("<info>Found certificate request ({$config->getCsrFile()})</info>");
      $appCsr = file_get_contents($config->getCsrFile());
    }

    if (!file_exists($config->getCertFile())) {
      $output->writeln("<info>Create certificate self-signed ({$config->getCertFile()})</info>");
      $appCert = CA::signCSR($appKeyPair, $demoCaCert, $appCsr);
      file_put_contents($config->getCertFile(), $appCert);
    }
    else {
      $output->writeln("<info>Found certificate ({$config->getCertFile()})</info>");
      $appCert = file_get_contents($config->getCertFile());
    }

    if (!file_exists($config->getMetadataFile())) {
      $output->writeln("<info>Create metadata file ({$config->getMetadataFile()})</info>");
      $appMeta = array(
        $appId => array(
          'title' => 'Example App',
          'desc' => 'This is the adhoc connection app. Once connected, the app-provider can make API calls to your site.',
          'appId' => $appId,
          'appCert' => '*PLACEHOLDER*',
          'appUrl' => $input->getArgument('url') . '/cxn/register',
          'perm' => array(
            'desc' => 'Description/rationale for permissions',
            'api' => array(
              array('version' => 3, 'entity' => '*', 'actions' => '*', 'required' => array(), 'fields' => '*'),
            ),
            'grant' => '*',
          ),
        ),
      );
      file_put_contents($config->getMetadataFile(), json_encode($appMeta, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0));
    }
    else {
      $output->writeln("<info>Found metadata file ({$config->getMetadataFile()})</info>");
      $appMeta = json_decode(file_get_contents($config->getMetadataFile()), TRUE);
    }

    if (!file_exists($config->getCxnStoreFile())) {
      $output->writeln("<info>Create cxnStore file ({$config->getCxnStoreFile()})</info>");
      $appCxnStore = new JsonFileCxnStore($config->getCxnStoreFile());
      $appCxnStore->save(array());
    }
    else {
      $output->writeln("<info>Found cxnStore file ({$config->getCxnStoreFile()})</info>");
      $appCxnStore = new JsonFileCxnStore($config->getCxnStoreFile());
      $appCxnStore->getCache();
    }

    print_r($appMeta[$appId]);
  }

}
