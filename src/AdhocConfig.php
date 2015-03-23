<?php

namespace Civi\Cxn\App;

use Civi\Cxn\Rpc\CxnStore\JsonFileCxnStore;
use Civi\Cxn\Rpc\KeyPair;

/**
 * Class AdhocConfig
 *
 * @package Civi\Cxn\Rpc
 */
class AdhocConfig {

  private $id;
  private $keyPair;
  private $metadata;
  private $cxnStore;

  public function getIdFile() {
    return dirname(__DIR__) . '/app/id.txt';
  }

  public function getId() {
    if (!$this->id) {
      if (!file_exists($this->getIdFile())) {
        throw new \RuntimeException("Missing id file.");
      }

      $this->id = trim(file_get_contents($this->getIdFile()));
    }
    return $this->id;
  }

  public function getKeyFile() {
    return dirname(__DIR__) . '/app/keys.json';
  }

  public function getKeyPair() {
    if (!$this->keyPair) {
      if (!file_exists($this->getKeyFile())) {
        throw new \RuntimeException("Missing key file.");
      }

      $this->keyPair = KeyPair::load($this->getKeyFile());
    }
    return $this->keyPair;
  }

  public function getMetadataFile() {
    return dirname(__DIR__) . '/app/metadata.json';
  }

  public function getMetadata() {
    if (!$this->metadata) {
      if (!file_exists($this->getMetadataFile())) {
        throw new \RuntimeException("Missing metadata file.");
      }

      $this->metadata = json_decode(file_get_contents($this->getMetadataFile()), TRUE);

      if (empty($this->metadata[$this->getId()])) {
        throw new \RuntimeException("Metadata file does not contain the required appId");
      }
    }
    return $this->metadata[$this->getId()];
  }

  public function getCxnStoreFile() {
    return dirname(__DIR__) . '/app/cxnStore.json';
  }

  public function getCxnStore() {
    if (!$this->cxnStore) {
      if (!file_exists($this->getCxnStoreFile())) {
        throw new \RuntimeException("Missing cxnStore file.");
      }
      $this->cxnStore = new JsonFileCxnStore($this->getCxnStoreFile());
    }
    return $this->cxnStore;
  }

}
