<?php

namespace Drupal\commerce_exchanger;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;

/**
 * Provides the Exchanger manager to store and get rates.
 */
class ExchangerManager implements ExchangerManagerInterface {

  /**
   * The ExchangerManager constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(protected Connection $database, protected TimeInterface $time) {}

  /**
   * {@inheritdoc}
   */
  public function getLatest($exchanger_id): array {
    $results = $this->database->select(ExchangerManagerInterface::EXCHANGER_LATEST_RATES, 'e')
      ->fields('e', ['source', 'target', 'value', 'manual'])
      ->condition('e.exchanger', $exchanger_id)
      ->execute()->fetchAll();

    $output = [];

    foreach ($results as $result) {
      $output[$result->source][$result->target] = [
        'value' => $result->value,
        'manual' => $result->manual,
      ];
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function setLatest($exchanger_id, array $rates): void {
    $query = $this->database->insert(ExchangerManagerInterface::EXCHANGER_LATEST_RATES)
      ->fields(['exchanger', 'source', 'target', 'value', 'timestamp', 'manual']);

    $time = $this->time->getCurrentTime();

    foreach ($rates as $source => $rate) {
      foreach ($rate as $target => $values) {
        // Only allow saving numerical values.
        if (!is_numeric($values['value'])) {
          throw new \RuntimeException("Rate value must be a valid numeric value. Make sure the value is not empty and use '.' as a delimiter.");
        }
        $query->values([
          'exchanger' => $exchanger_id,
          'source' => $source,
          'target' => $target,
          'value' => $values['value'],
          'timestamp' => $time,
          'manual' => $values['manual'] ?? 0,
        ]);
      }
    }
    // Delete old records, in case we don't hold some currencies anymore.
    $this->database->delete(ExchangerManagerInterface::EXCHANGER_LATEST_RATES)->condition('exchanger', $exchanger_id)->execute();
    $query->execute();

    // Invalidate custom cache tag used on field formatter.
    Cache::invalidateTags([ExchangerManagerInterface::EXCHANGER_RATES_CACHE_TAG]);
  }

  /**
   * {@inheritdoc}
   */
  public function getHistorical($exchanger_id, ?string $date = NULL): array {
    $results = $this->database->select(ExchangerManagerInterface::EXCHANGER_HISTORICAL_RATES, 'e')
      ->fields('e', ['source', 'target', 'value', 'date'])
      ->condition('e.exchanger', $exchanger_id);

    if ($date) {
      $results->condition('e.date', $date);
    }

    $results->execute()->fetchAll();

    $output = [];

    foreach ($results as $result) {
      $output[$result->date][$result->source][$result->target] = [
        'value' => (float) $result->value,
        'manual' => $result->manual,
      ];
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function setHistorical($exchanger_id, array $rates, ?string $date = NULL): void {

    if (!$date) {
      $date = date('Y-m-d', $this->time->getCurrentTime());
    }

    foreach ($rates as $source => $rate) {
      foreach ($rate as $target => $values) {
        $query = $this->database->merge(ExchangerManagerInterface::EXCHANGER_HISTORICAL_RATES);
        $query->keys([
          'exchanger' => $exchanger_id,
          'source' => $source,
          'target' => $target,
          'date' => $date,
        ]);
        $query->fields([
          'exchanger' => $exchanger_id,
          'source' => $source,
          'target' => $target,
          'value' => $values['value'],
          'date' => $date,
          'manual' => $values['manual'] ?? 0,
        ]);
        $query->execute();
      }
    }
  }

}
