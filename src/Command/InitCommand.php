<?php
namespace Civi\Cxn\Adhoc\Command;

use Civi\Cxn\Adhoc\AdhocConfig;
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
      ->setHelp('Example: cxn-adhoc init "http://myapp.localhost"')
      ->addArgument('url', InputArgument::REQUIRED, 'The registration URL where the app will be published')
      ->addArgument('dn', InputArgument::OPTIONAL, 'The DN in the application certificate', '/O=DemoApp');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = new AdhocConfig();

    if (!file_exists($config->getIdFile())) {
      $output->writeln("<info>Create id file ({$config->getIdFile()})</info>");
      $appId = base64_encode(crypt_random_string(Constants::APP_ID_CHARS));
      file_put_contents($config->getIdFile(), $appId);
    }
    else {
      $output->writeln("<info>Found id file ({$config->getIdFile()})</info>");
      $appId = trim(file_get_contents($config->getIdFile()));
    }

    if (!file_exists($config->getKeyFile())) {
      $output->writeln("<info>Create key file ({$config->getKeyFile()})</info>");
      $appKeyPair = KeyPair::create();
      KeyPair::save($config->getKeyFile(), $appKeyPair);
    }
    else {
      $output->writeln("<info>Found key file ({$config->getKeyFile()})</info>");
      $appKeyPair = KeyPair::load($config->getKeyFile());
    }

    if (!file_exists($config->getMetadataFile())) {
      $output->writeln("<info>Create metadata file ({$config->getMetadataFile()})</info>");
      $appMeta = array(
        $appId => array(
          'desc'=> 'This is the adhoc connection app. Once connected, the app-provider can make API calls to your site.',
          'appId' => $appId,
          'appCert' => CA::createSelfSignedCert($appKeyPair, $input->getArgument('dn')),
          'appUrl' => $input->getArgument('url'),
          'perm' => array(
            'api' => array(
              array('entity' => '*', 'action' => '*', 'params' => '*'),
            ),
            'sys' => array('administer CiviCRM'),
          ),
        ),
      );
      file_put_contents($config->getMetadataFile(), json_encode($appMeta));
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
