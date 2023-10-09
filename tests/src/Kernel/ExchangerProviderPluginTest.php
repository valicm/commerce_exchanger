<?php

namespace Drupal\Tests\commerce_exchanger\Kernel;

use Drupal\commerce_exchanger\Entity\ExchangeRates;
use Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\ExchangerProviderRemoteInterface;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * @coversDefaultClass \Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\ExchangerProviderRemoteBase
 * @group commerce_exchanger
 */
class ExchangerProviderPluginTest extends CommerceKernelTestBase {

  /**
   * Exchange rate plugin.
   *
   * @var \Drupal\commerce_exchanger_test\Plugin\Commerce\ExchangerProvider\TestExchanger
   */
  protected $test;

  /**
   * Exchange rate plugin.
   *
   * @var \Drupal\commerce_exchanger_test\Plugin\Commerce\ExchangerProvider\TestEnterpriseExchanger
   */
  protected $enterprise;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_exchanger',
    'commerce_exchanger_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() :void {
    parent::setUp();

    $this->installConfig(['commerce_exchanger']);

    // The parent has already imported USD.
    $currency_importer = $this->container->get('commerce_price.currency_importer');
    $currency_importer->import('AUD');
    $currency_importer->import('EUR');

    $exchange_entity = ExchangeRates::create([
      'id' => 'test',
      'label' => 'Test',
      'plugin' => 'test',
    ]);
    $exchange_entity->save();

    $this->test = $exchange_entity->getPlugin();

    $fixer_entity = ExchangeRates::create([
      'id' => 'enterprise',
      'label' => 'Enterprise',
      'plugin' => 'enterprise',
      'configuration' => [
        'enterprise' => TRUE,
      ],
    ]);
    $fixer_entity->save();

    $this->enterprise = $fixer_entity->getPlugin();
  }

  /**
   * Test generic settings for plugins.
   *
   * ::@covers ::useCrossSync
   * ::@covers ::isEnterprise.
   */
  public function testPluginSettings() {
    $this->assertTrue($this->test->useCrossSync());
    $this->assertFalse($this->test->isEnterprise());
    $this->assertEquals('EUR', $this->test->getBaseCurrency());
    $this->assertFalse($this->enterprise->useCrossSync());
    $this->assertTrue($this->enterprise->isEnterprise());
    $this->assertEmpty($this->enterprise->getBaseCurrency());
  }

  /**
   * Test cross sync import.
   *
   * ::@covers ::import
   * ::@covers ::buildExchangeRates
   * ::@covers ::importCrossSync
   * ::@covers ::processRemoteData
   * ::@covers ::crossSyncCalculate
   * ::@covers ::mapExchangeRates.
   */
  public function testImportCrossSync() {
    $this->test->import();
    $rates = $this->container->get('commerce_exchanger.calculate')->getExchangeRates();
    $this->assertEquals(round(1 / 1.659999, ExchangerProviderRemoteInterface::EXCHANGER_ROUND_PRECISION), $rates['EUR']['AUD']['value']);
    $this->assertEquals(round(1 / 1.19, ExchangerProviderRemoteInterface::EXCHANGER_ROUND_PRECISION), $rates['EUR']['USD']['value']);
    $this->assertEquals(1.659999, $rates['AUD']['EUR']['value']);
    $this->assertEquals(1.19, $rates['USD']['EUR']['value']);
  }

  /**
   * Test enterprise import.
   *
   * ::@covers ::import
   * ::@covers ::buildExchangeRates
   * ::@covers ::mapExchangeRates
   * ::@covers ::processRemoteData
   * ::@covers ::importEnterprise.
   */
  public function testImportEnterprise() {
    // We need to disable test which is loaded first in default calculator.
    $test = ExchangeRates::load('test');
    $test->set('status', 0)->save();

    // Import rates.
    $this->enterprise->import();
    $rates = $this->container->get('commerce_exchanger.calculate')->getExchangeRates();

    $this->assertEquals(1.19, $rates['EUR']['USD']['value']);
    $this->assertEquals(0.840336, $rates['USD']['EUR']['value']);
  }

}
