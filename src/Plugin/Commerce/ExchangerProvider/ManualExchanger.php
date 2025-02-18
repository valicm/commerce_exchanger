<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\commerce_exchanger\Attribute\CommerceExchangerProvider;

/**
 * Provides Manual handling currencies.
 */
#[CommerceExchangerProvider(
  id: "manual",
  label: new TranslatableMarkup("Manual"),
  display_label: new TranslatableMarkup("Manual"),
  manual: TRUE
)]
class ManualExchanger extends ExchangerProviderBase {
  // Nothing to do.
}
