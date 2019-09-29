<?php

namespace Drupal\commerce_exchanger;

use Drupal\commerce_price\Price;

/**
 * Interface ExchangerCalculatorInterface.
 *
 * @package Drupal\commerce_exchanger
 */
interface ExchangerCalculatorInterface {

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
  public function priceConversion(Price $price, $target_currency);

  /**
   * Get all exchange rates.
   *
   * @return array
   *   Return exchange rates for currency resolver exchange plugin used.
   *
   */
  public function getExchangeRates();

  /**
   * Return configuration file of active provider.
   *
   * @return string
   *   Return provider.
   */
  public function getExchangerId();

}
