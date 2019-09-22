<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use Drupal\commerce_exchanger\Annotation\CommerceExchangerProvider;
use Drupal\Component\Serialization\Json;

/**
 * Provides the Fixer.io exchange rates.
 *
 * @CommerceExchangerProvider(
 *   id = "fixer",
 *   label = "Fixer.io",
 *   display_label = "Fixer.io",
 *   historical_rates = TRUE,
 *   enterprise = TRUE,
 *   api_key= TRUE,
 * )
 */
class FixerExchanger extends ExchangerProviderRemoteBase {

  /**
   * {@inheritdoc}
   */
  public function apiUrl() {
    return 'http://data.fixer.io/api/latest';
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
    if (!empty($base_currency) && $this->isEnterprise()) {
      $options['query']['base'] = $base_currency;
    }

    $request = $this->apiClient($options);

    if ($request) {
      $json = Json::decode($request);

      if ($json->success) {
        // Leave base currency. In some cases we don't know base currency.
        // Fixer.io on free plan uses your address for base currency, and in
        // Drupal you could have different default value.
        $data['base'] = $json->base;

        // Loop and build array.
        foreach ($json->rates as $key => $value) {
          $data['rates'][$key] = $value;
        }
      }
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function processRemoteData() {
    $exchange_rates = [];

    if ($data = $this->getRemoteData()) {

      // Based on cross sync settings fetch and process data.
      if ($this->useCrossSync()) {
        $exchange_rates = $this->crossSyncCalculate($data['base'], $data['rates']);
      }

      // Fetch each currency.
      else {
        $exchange_rates = $this->enterpriseCalculate($data);
      }
    }

    return $exchange_rates;
  }

  /**
   * {@inheritdoc}
   */
  public function enterpriseCalculate($data) {
    $exchange_rates = [];

    // Enabled currency.
    $currencies = $this->currencies;

    foreach ($currencies as $base => $name) {
      // Foreach enabled currency fetch others.
      if ($data[$base]) {
        $get_rates = $this->mapExchangeRates($data[$base]['rates'], $base);
        $exchange_rates[$base] = $get_rates[$base];
      }
    }

    return $exchange_rates;
  }

}
