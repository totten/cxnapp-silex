<?php
namespace Civi\Cxn\App;

use Civi\Cxn\App\Command\CallCommand;
use Civi\Cxn\App\Command\InitCommand;
use Symfony\Component\Console\Command\ListCommand;

class Application extends \Symfony\Component\Console\Application {

  /**
   * Primary entry point for execution of the standalone command.
   *
   * @return
   */
  public static function main($binDir) {
    $application = new Application('cxnapp', '@package_version@');
    $application->run();
  }

  public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN') {
    parent::__construct($name, $version);
    $this->setCatchExceptions(TRUE);
    $this->addCommands($this->createCommands());
  }

  /**
   * Construct command objects
   *
   * @return array of Symfony Command objects
   */
  public function createCommands() {
    $commands = array();
    $commands[] = new InitCommand();
    $commands[] = new \Civi\Cxn\App\Command\GetCommand();
    $commands[] = new CallCommand();
    return $commands;
  }
}
