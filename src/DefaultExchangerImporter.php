<?php

namespace Drupal\commerce_exchanger;

use Drupal\commerce_exchanger\Entity\ExchangeRatesInterface;
use Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\ExchangerProviderRemoteInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;

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
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * DefaultExchangerImporter constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\State\StateInterface $state
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, StateInterface $state) {
    $this->providers = $entity_type_manager->getStorage('commerce_exchange_rates')->loadByProperties(['status' => TRUE]);
    $this->state = $state;
  }

  public function run($force = FALSE) {
    foreach ($this->providers as $provider) {
      assert($provider instanceof ExchangeRatesInterface);
      $plugin = $provider->getPlugin();

      if ($plugin instanceof ExchangerProviderRemoteInterface) {
        $last_update = $this->state->get('commerce_exchanger.' . $provider->id() . ' .last_update_time');
        $cron_setup = $provider->getPluginConfiguration()['cron'] ?? 1;
        $cron_schedule = time() - (24 / $cron_setup) * 60 * 60;
        // Exclude manual plugins. Check either time or force import.
        if ($force || $last_update < $cron_schedule) {
          $plugin->import();
          // Update last imported time.
          $this->state->set('commerce_exchanger.' . $provider->id() . ' .last_update_time', time());
        }
      }
    }
  }

}
