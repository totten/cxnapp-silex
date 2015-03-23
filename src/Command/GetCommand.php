<?php
namespace Civi\Cxn\Adhoc\Command;

use Civi\Cxn\Adhoc\AdhocConfig;
use Civi\Cxn\Rpc\JsonFileCxnStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCommand extends Command {

  protected function configure() {
    $this
      ->setName('get')
      ->setDescription('Get a list of connections')
      ->addArgument('cxnId', InputArgument::OPTIONAL, 'Connection ID');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = new AdhocConfig();

    $appCxnStore = $config->getCxnStore();

    if ($input->getArgument('cxnId')) {
      $cxn = $appCxnStore->getByCxnId($input->getArgument('cxnId'));
      print_r($cxn);
    }
    else {
      $rows = array();
      foreach ($appCxnStore->getAll() as $cxn) {
        $rows[] = array($cxn['cxnId'], $cxn['siteUrl']);
      }

      $table = $this->getApplication()->getHelperSet()->get('table');
      $table
        ->setHeaders(array('Link ID', 'Site URL'))
        ->setRows($rows);
      $table->render($output);
    }
  }

}
