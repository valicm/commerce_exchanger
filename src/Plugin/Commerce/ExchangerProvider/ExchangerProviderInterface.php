<?php

namespace Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines an interface for Commerce exchange rates provider plugins.
 */
interface ExchangerProviderInterface extends PluginInspectionInterface, PluginFormInterface, DerivativeInspectionInterface {

  /**
   * Return config object name where exchange rates are saved.
   *
   * @return string
   */
  public function getConfigName();

}
