<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use GuzzleHttp\Exception\GuzzleException;

/**
 * Base class for Commerce exchanger provider plugins.
 */
abstract class ExchangerProviderRemoteBase extends ExchangerProviderBase implements ExchangerProviderRemoteInterface {

  /**
   * {@inheritdoc}
   */
  public function getMethod() {
    return $this->pluginDefinition['method'] ?? 'GET';
  }

  /**
   * {@inheritdoc}
   */
  public function isEnterprise() {
    return $this->configuration['enterprise'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getApiKey() {
    return $this->configuration['api_key'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthData() {
    return $this->configuration['auth'];
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseCurrency() {
    return $this->configuration['base_currency'];
  }

  /**
   * {@inheritdoc}
   */
  public function useCrossSync() {
    return $this->configuration['use_cross_sync'];
  }

  /**
   * {@inheritdoc}
   */
  public function apiClient(array $options) {
    $data = [];

    // Prepare for client.
    $client = $this->httpClientFactory->fromOptions();

    try {
      $response = $client->request($this->getMethod(), $this->apiUrl(), $options);

      // Expected result.
      $data = $response->getBody()->getContents();

    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function processRemoteData() {
    $exchange_rates = [];

    // User defined or provider defined base currency.
    $base_currency = $this->getBaseCurrency();

    // If we have only base currency upon calculation is done,
    // force cross sync, regardless of the settings.
    // Or if we choose to use cross sync.
    if ((!$this->isEnterprise() && !empty($base_currency)) || $this->useCrossSync()) {
      // @todo legacy from currency resolver. Needed to make data validator to ensure same structure.
      if ($data = $this->getRemoteData()) {
        $base_currency = $data['base'] ?? $base_currency;
        $base_rates = $data['rates'] ?? $data;
        // Based on cross sync settings fetch and process data.
        $exchange_rates = $this->crossSyncCalculate($base_currency, $base_rates);
      }
    }

    else {
      // Fetch per each currency and make a data set.
      foreach ($this->currencies as $code => $currency) {
        // @todo legacy from currency resolver. Needed to make data validator to ensure same structure.
        $currency_data = $this->getRemoteData($code);
        $base_currency = $data['base'] ?? $code;
        $base_rates = $currency_data['rates'] ?? $currency_data;

        if (!empty($base_rates)) {
          $get_rates = $this->mapExchangeRates($base_rates, $base_currency);
          $exchange_rates[$code] = $get_rates[$code];
        }

      }
    }

    return $exchange_rates;
  }

  /**
   * Rates calculation for currencies when we use cross sync conversion.
   *
   * @param string $base_currency
   *   Base currency upon which we have exchange rates.
   * @param array $data
   *   Currency and rate array. Data should be in format: $data[$code] = $rate.
   *
   * @return array
   *   Return data prepared for saving.
   */
  protected function crossSyncCalculate($base_currency, array $data) {
    $exchange_rates = [];

    // Enabled currency.
    $currencies = $this->currencies;

    if ($data) {
      foreach ($currencies as $currency_code => $name) {
        $recalculate = $this->reverseCalculate($currency_code, $base_currency, $data);

        if (!empty($recalculate)) {
          // Prepare data.
          $get_rates = $this->mapExchangeRates($recalculate, $currency_code);
          $exchange_rates[$currency_code] = $get_rates[$currency_code];
        }
      }
    }

    return $exchange_rates;
  }

  /**
   * Helper function to create array for exchange rates.
   *
   * @param array $data
   *   New fetched data, array format: currency_code => rate.
   * @param string $base_currency
   *   Parent currency upon we build array.
   *
   * @return array
   *   Return array prepared for saving in Drupal config.
   */
  protected function mapExchangeRates(array $data, $base_currency) {

    // Get current exchange rates.
    $mapping = $this->configFactory->get($this->getConfigName())->getRawData();

    // Set defaults.
    $exchange_rates = [];
    $exchange_rates[$base_currency] = [];

    // Loop trough data, set new values or leave manually defined.
    foreach ($data as $currency => $rate) {
      // Skip base currency to map to itself.
      if ($currency != $base_currency) {
        if (empty($mapping[$base_currency][$currency]['sync'])) {
          $exchange_rates[$base_currency][$currency]['value'] = $rate;
          $sync_settings = $mapping[$base_currency][$currency]['sync'] ?? 0;
          $exchange_rates[$base_currency][$currency]['sync'] = $sync_settings;
        }

        else {
          $exchange_rates[$base_currency][$currency] = $mapping[$base_currency][$currency];
        }
      }
    }

    return $exchange_rates;
  }

  /**
   * Recalculate currencies from exchange rate between two other currencies.
   *
   * @param string $target_currency
   *   Currency to which should be exchange rate calculated.
   * @param string $base_currency
   *   Base currency upon which we have exchange rates.
   * @param array $data
   *   Currency and rate array.
   *
   * @return array
   *   Return recalculated data.
   */
  protected function reverseCalculate($target_currency, $base_currency, array $data) {

    // Get all enabled currencies.
    $currencies = $this->currencies;

    // If we accidentally sent same target and base currency.
    $rate_target_currency = !empty($data[$target_currency]) ? $data[$target_currency] : 1;

    // Get rate based from base currency.
    $currency_default = 1 / $rate_target_currency;

    $recalculated = [];
    $recalculated[$base_currency] = $currency_default;

    // Recalculate all data.
    foreach ($data as $currency => $rate) {
      if ($currency !== $target_currency && isset($currencies[$currency])) {
        $recalculated[$currency] = $rate * $currency_default;
      }
    }

    return $recalculated;
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $exchange_rates = $this->processRemoteData();

    // Write new data.
    if (!empty($exchange_rates)) {
      $file = $this->configFactory->getEditable($this->getConfigName());
      $file->setData(['rates' => $exchange_rates]);
      $file->save();
    }
  }

}
