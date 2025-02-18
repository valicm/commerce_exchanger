<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\commerce_exchanger\Attribute\CommerceExchangerProvider;

/**
 * Provides EuropeanCentralBank.
 */
#[CommerceExchangerProvider(
  id: "ecb",
  label: new TranslatableMarkup("European Central Bank"),
  display_label: new TranslatableMarkup("European Central Bank"),
  base_currency: "EUR",
  refresh_once: TRUE,
)]
class EuropeanCentralBankExchanger extends ExchangerProviderRemoteBase {

  /**
   * {@inheritdoc}
   */
  public function apiUrl() {
    return 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteData($base_currency = NULL) {
    $data = NULL;

    $request = $this->apiClient([]);

    if ($request) {

      try {
        $xml = new \SimpleXMLElement($request);
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
        return NULL;
      }

      // Loop and build array.
      foreach ($xml->Cube->Cube->Cube as $rate) {
        $code = (string) $rate['currency'];
        $rate = (string) $rate['rate'];
        $data[$code] = $rate;
      }

    }

    return $data;
  }

}
