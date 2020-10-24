<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\RequestOptions;

/**
 * Provides the transferwise.com exchange rates.
 *
 * @CommerceExchangerProvider(
 *   id = "transferwise",
 *   label = "TransferWise",
 *   display_label = "TransferWise",
 *   historical_rates = TRUE,
 *   enterprise = TRUE,
 *   api_key = TRUE,
 *   modes = TRUE,
 * )
 */
class TransferWiseExchanger extends ExchangerProviderRemoteBase {

  /**
   * {@inheritdoc}
   */
  public function apiUrl() {
    return 'https://api.transferwise.com/v1/rates';
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteData($base_currency = NULL) {
    $data = NULL;

    $source = $this->isEnterprise() ? $base_currency : $this->getBaseCurrency();

    $options = [
      RequestOptions::QUERY => ['source' => $source],
      RequestOptions::HEADERS => [
        'Authorization' => 'Bearer ' . $this->getApiKey(),
      ],
    ];

    $request = $this->apiClient($options);

    if ($request) {
      $rates = Json::decode($request);

      $data['base'] = $source;

      foreach ($rates as $rate) {
        $data['rates'][$rate['target']] = $rate['rate'];
      }
    }

    return $data;
  }

}
