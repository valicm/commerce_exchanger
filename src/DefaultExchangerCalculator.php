<?php

namespace Drupal\commerce_exchanger;

use Drupal\commerce_exchanger\Exception\ExchangeRatesDataMismatchException;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class DefaultExchangerCalculator.
 *
 * @package Drupal\commerce_exchanger
 */
class DefaultExchangerCalculator implements ExchangerCalculatorInterface {

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * List of providers.
   *
   * @var \Drupal\commerce_exchanger\Entity\ExchangeRatesInterface[]
   */
  protected $providers;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal commerce price rounder service.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  protected $rounder;

  /**
   * DefaultExchangerCalculator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Drupal config factory.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   Drupal commerce price rounder service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactory $config_factory, RounderInterface $rounder) {
    $this->configFactory = $config_factory;
    $this->providers = $entity_type_manager->getStorage('commerce_exchange_rates')->loadMultiple();
    $this->rounder = $rounder;
  }

  /**
   * {@inheritdoc}
   */
  public function priceConversion(Price $price, $target_currency) {
    $exchange_rates_config = $this->getExchangerId();

    if (empty($exchange_rates_config)) {
      throw new ExchangeRatesDataMismatchException('Not any active exchanger rates present');
    }

    $exchange_rates = $this->getExchangeRates();

    // Current currency.
    $current_currency = $price->getCurrencyCode();

    // Determine rate.
    $rate = $exchange_rates[$current_currency][$target_currency]['value'] ?? 1;

    // Convert. Convert rate to string.
    $price = $price->convert($target_currency, (string) $rate);
    $price = $this->rounder->round($price);
    return $price;
  }

  /**
   * {@inheritdoc}
   */
  public function getExchangeRates() {
    return $this->configFactory->get($this->getExchangerId())->get() ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getExchangerId() {
    foreach ($this->providers as $provider) {
      if ($provider->status()) {
        return $provider->getExchangerConfigName();
      }
    }

    return '';
  }

}
