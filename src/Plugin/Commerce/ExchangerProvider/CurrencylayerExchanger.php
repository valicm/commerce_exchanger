<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Component\Serialization\Json;
use Drupal\commerce_exchanger\Attribute\CommerceExchangerProvider;

/**
 * Provides the Currencylayer.com exchange rates.
 */
#[CommerceExchangerProvider(
  id: "currencylayer",
  label: new TranslatableMarkup("Currencylayer"),
  display_label: new TranslatableMarkup("currencylayer.com"),
  api_key: TRUE,
  enterprise: TRUE,
)]
class CurrencylayerExchanger extends ExchangerProviderRemoteBase {

  /**
   * {@inheritdoc}
   */
  public function apiUrl() {
    if ($this->isEnterprise()) {
      return 'https://api.currencylayer.com/live';
    }
    return 'http://api.currencylayer.com/live';
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteData($base_currency = NULL) {
    $data = NULL;

    $options = [
      'query' => ['access_key' => $this->getApiKey()],
    ];

    // Add base currency if we use enterprise model.
    if ($this->isEnterprise()) {
      $options['query']['base'] = $base_currency;
    }

    $request = $this->apiClient($options);

    if ($request) {
      $json = Json::decode($request);

      if (!empty($json['success'])) {

        // Leave base currency. In some cases we don't know base currency.
        // Currencylayer on free plan uses your address for base currency,
        // and in Drupal you could have different default value.
        $data['base'] = $json['source'];

        foreach ($json['quotes'] as $code => $rate) {
          $data['rates'][str_replace($json['source'], '', $code)] = $rate;
        }
      }
    }

    return $data;
  }

}
