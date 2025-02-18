<?php

declare(strict_types=1);

namespace Drupal\commerce_exchanger\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a Commerce exchange rates provider attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CommerceExchangerProvider extends Plugin {

  /**
   * Constructs a CommerceExchangerProvider attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The label of the plugin.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $display_label
   *   (optional) The condition display label, defaults to the label.
   * @param bool $modes
   *   (optional) External provider supports test / live mode.
   * @param string|null $base_currency
   *   (optional) Base currency upon exchange rates are based.
   * @param bool $api_key
   *   (optional) API key for external source.
   * @param bool $auth
   *   (optional) Authentication for interacting with external source.
   * @param bool $enterprise
   *   (optional) External source supports fetch by any base currency.
   * @param bool $refresh_once
   *   (optional) Refresh exchange rates only once a day.
   * @param bool $manual
   *   (optional) No external source for exchange rates.
   * @param string $method
   *   (optional) Method to interact with remote source.
   * @param bool $transform_rates
   *   (optional) The needs to be transformed - reverse calculated.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
    public ?TranslatableMarkup $display_label = NULL,
    public readonly ?string $base_currency = NULL,
    public readonly bool $modes = FALSE,
    public readonly bool $api_key = FALSE,
    public readonly bool $auth = FALSE,
    public readonly bool $enterprise = FALSE,
    public readonly bool $refresh_once = FALSE,
    public readonly bool $manual = FALSE,
    public readonly string $method = 'GET',
    public readonly bool $transform_rates = FALSE,
  ) {
    if (empty($this->display_label)) {
      $this->display_label = $this->label;
    }
  }

}
