<?php

namespace Drupal\commerce_exchanger;

use Drupal\commerce_price\Price;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class DefaultExchangerCalculator
 *
 * @package Drupal\commerce_exchanger
 */
class DefaultExchangerCalculator implements ExchangerCalculatorInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\commerce_exchanger\Entity\ExchangeRatesInterface[]
   */
  protected $providers;

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
    $this->providers = $entity_type_manager->getStorage('commerce_exchange_rates')->loadByProperties(['status' => TRUE]);
  }

  /**
   * Currency conversion for prices.
   *
   * @param \Drupal\commerce_price\Price $price
   *   Price object.
   * @param string $target_currency
   *   Target currency.
   *
   * @return \Drupal\commerce_price\Price|static
   *   Return updated price object with new currency.
   */
  public function priceConversion(Price $price, $target_currency) {
    // Get currency conversion settings.
    $config = \Drupal::config('commerce_currency_resolver.currency_conversion');

    // Get specific settings.
    $mapping = $config->get('exchange');

    // Current currency.
    $current_currency = $price->getCurrencyCode();

    // Determine rate.
    $rate = NULL;
    if (isset($mapping[$current_currency][$target_currency])) {
      $rate = $mapping[$current_currency][$target_currency]['value'];
    }

    // Fallback to use 1 as rate.
    if (empty($rate)) {
      $rate = '1';
    }

    // Convert. Convert rate to string.
    $price = $price->convert($target_currency, (string) $rate);
    $rounder = \Drupal::service('commerce_price.rounder');
    $price = $rounder->round($price);
    return $price;
  }


}
