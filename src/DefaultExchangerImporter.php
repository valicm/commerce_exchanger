<?php

namespace Drupal\commerce_exchanger;

use Drupal\commerce_exchanger\Entity\ExchangeRatesInterface;
use Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\ExchangerProviderRemoteInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class DefaultExchangerImporter
 *
 * @package Drupal\commerce_exchanger
 */
class DefaultExchangerImporter implements ExchangerImporterInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\commerce_exchanger\Entity\ExchangeRatesInterface[]
   */
  protected $providers;

  /**
   * DefaultExchangerImporter constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->providers = $entity_type_manager->getStorage('commerce_exchange_rates')->loadByProperties(['status' => TRUE]);
  }

  public function run($force = FALSE) {
    foreach ($this->providers as $provider) {
      assert($provider instanceof ExchangeRatesInterface);
      $plugin = $provider->getPlugin();
      assert($plugin instanceof ExchangerProviderRemoteInterface);

      //if (!$plugin->isManual() && ($force || $c->time() < time() + $c->getPeriod())) {
        $plugin->import();
      //}
    }
  }

}
