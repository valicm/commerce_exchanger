<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use Drupal\commerce_exchanger\Annotation\CommerceExchangerProvider;

/**
 * Provides Manual handling currencies.
 *
 * @CommerceExchangerProvider(
 *   id = "manual",
 *   label = "Manual",
 *   display_label = "Manual",
 *   manual = TRUE
 * )
 */
class ManualExchanger extends ExchangerProviderBase {
  // Nothing to do.
}
