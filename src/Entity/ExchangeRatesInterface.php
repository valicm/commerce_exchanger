<?php

namespace Drupal\commerce_exchanger\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Exchange rates entities.
 */
interface ExchangeRatesInterface extends ConfigEntityInterface {

  public const COMMERCE_EXCHANGER_IMPORT = 'latest_exchange_rates';

  /**
   * How config name for stored data should be called.
   *
   * @return string
   */
  public function getExchangerConfigName();

}
