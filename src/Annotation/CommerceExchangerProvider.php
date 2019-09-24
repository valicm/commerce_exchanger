<?php

namespace Drupal\commerce_exchanger\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Commerce exchange rates provider item annotation object.
 *
 *  Plugin namespace: Plugin\Commerce\ExchangerProvider.
 *
 * @see \Drupal\commerce_exchanger\ExchangerProviderManager
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceExchangerProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * If provider supports test / live mode.
   *
   * @var bool
   */
  public $modes = FALSE;

  /**
   * Only currency which external provider supports.
   *
   * @var string
   */
  public $base_currency;

  /**
   * If external provider need api key.
   *
   * @var bool
   */
  public $api_key = FALSE;

  /**
   * If external provider need authentication.
   *
   * @var bool
   */
  public $auth = FALSE;

  /**
   * External provider supports fetching by any currency.
   *
   * @var bool
   */
  public $enterprise = FALSE;

  /**
   * External provider supports historical rates.
   *
   * @var bool
   */
  public $historical_rates = FALSE;

  /**
   * If exchange list provider refresh currencies only once.
   *
   * @var bool
   */
  public $refresh_once = FALSE;

  /**
   * There is no external connection.
   *
   * @var bool
   */
  public $manual = FALSE;

  /**
   * Method for request.
   *
   * @var bool
   */
  public $method = 'GET';

}
