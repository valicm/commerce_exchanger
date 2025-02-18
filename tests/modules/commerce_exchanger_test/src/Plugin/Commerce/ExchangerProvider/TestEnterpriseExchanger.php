<?php

namespace Drupal\commerce_exchanger_test\Plugin\Commerce\ExchangerProvider;

use Drupal\commerce_exchanger\Attribute\CommerceExchangerProvider;
use Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\ExchangerProviderRemoteBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides the enterprise exchange rates.
 */
#[CommerceExchangerProvider(
  id: "enterprise",
  label: new TranslatableMarkup("Enterprise"),
  display_label: new TranslatableMarkup("Enterprise"),
  api_key: TRUE,
  enterprise: TRUE,
)]
class TestEnterpriseExchanger extends ExchangerProviderRemoteBase {

  /**
   * {@inheritdoc}
   */
  public function apiUrl() {
    return 'http://example.enterprise';
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteData($base_currency = NULL) {
    $data = [
      'EUR' => [
        'AUD' => 1.659999,
        'USD' => 1.190000,
      ],
      'USD' => [
        'AUD' => 1.394957,
        'EUR' => 0.840336,
      ],
      'AUD' => [
        'EUR' => 0.602409,
        'USD' => 0.716867,
      ],
    ];

    return $data[$base_currency];
  }

}
