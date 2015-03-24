<?php
namespace Civi\Cxn\App;

use Psr\Log\AbstractLogger;

class SimpleFileLogger extends AbstractLogger {

  private $file;
  private $prefix;

  function __construct($file, $prefix = '') {
    $this->file = $file;
    $this->prefix = $prefix;
  }

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   * @param string $message
   * @param array $context
   * @return null
   */
  public function log($level, $message, array $context = array()) {
    $now = date('c');
    $prefix = $this->prefix ? "<{$this->prefix}> " : "";
    $out = "{$prefix}$now [$level] $message\n";
    if (!empty($context['exception'])) {
      $out .= $context['exception']->getMessage() . "\n";
      $out .= $context['exception']->getTraceAsString() . "\n";
      unset($context['exception']);
    }
    if (!empty($context)) {
      $out .= print_r($context, TRUE) . "\n";
    }
    file_put_contents($this->file, $out, FILE_APPEND);
  }

}
