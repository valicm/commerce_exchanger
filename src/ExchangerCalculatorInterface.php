<?php

namespace Drupal\commerce_exchanger;

use Drupal\commerce_price\Price;

/**
 * Interface ExchangerCalculatorInterface
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
  function priceConversion(Price $price, $target_currency);

}
