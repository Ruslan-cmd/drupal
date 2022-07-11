<?php

namespace Drupal\drush\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DrushSettingsForm extends FormBase {


  public $progressvalue;


  protected $nodeBundles;

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * Node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  static $count = 0;
  /**
   * BatchForm constructor.
   */

  public function __construct() {
    $this->batchBuilder = new BatchBuilder();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drush_batch';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['help'] = [
      '#markup' => $this->t('This form set entered publication date to all content of selected type.'),
    ];

    $form['date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Publication date'),
      '#required' => TRUE,
      '#default_value' => new DrupalDateTime('2000-01-01 00:00:00', 'Europe/Moscow'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['run'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run batch'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $date = $form_state->getValue('date');
    $timestamp = strtotime($date);

    $service = \Drupal::service('myservice.my');
    $request = $service->getResult('https://swapi.dev/api/');

    $this->batchBuilder
      ->setTitle($this->t('Processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));


      foreach ($request as $api_url) {
      $url = $api_url;
//$url = 'https://swapi.dev/api/people/';
      do {
        $request_items = $service->getResult($url);
        $result_items = $request_items->results; // выкинул служебную информацию
        foreach ($result_items as $row_item) {
          $timestamp_swapi = strtotime($row_item->edited);
          if ($timestamp < $timestamp_swapi){
            $items[] = $row_item;
          }
        }

        $this->batchBuilder->addOperation('\Drupal\drush\BatchService::processItems', [$items]);
        $items = [];
        $url = $request_items->next;

      } while ($url);
    }

   // $this->batchBuilder->setFile(drupal_get_path('module', 'drush') . '/src/Form/DrushSettingsForm.php');
    $this->batchBuilder->setFinishCallback('\Drupal\drush\BatchService::processItems');
    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Processor for batch operations.
   */
 /* public function processItems($items,array &$context) {

    // Elements per operation.
    $limit = 5;
    $service = \Drupal::service('myservice.my');
    // Set default progress values.
    // если прогресс пустой
    if (empty($context['sandbox']['progress'])) {
      //$context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($items);
    }
    // если пустой массив элементов
    // Save items to array which will be changed during processing.
    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['items'] = $items;
    }
    $counter = 0;
    // есои массив элементов не пустой и если прогресс не равен 0, то распиливем массив и выкидываем уже проверенные элементы
    //это при втором и дальнейшем заходе
    if (!empty($context['sandbox']['items'])) {
      // Remove already processed items.
      if ($context['sandbox']['progress'] != 0) {
        array_splice($context['sandbox']['items'], 0, $limit);
      }

      foreach ($context['sandbox']['items'] as $row_item) {
        //если счетчик не достиг лимита то закидываем каждый элемент
        if ($counter != $limit) {
          $this->processItem($row_item,$service);
          $counter++;
          $count++;
          $context['sandbox']['progress']++;
//          $context['message'] = $this->t('Now processing node' . $counter);

         // $context['message'] = t('Now processing Batch API overview', array('Batch API overview' => $counter));
          $context['message'] = $this->t('Now processing node :progress of :count', [
            ':progress' =>  $context['sandbox']['progress'],
            ':count' => $context['sandbox']['max'],
          ]);
          // выводим сообщение о прогрессе, сколько выполнено и сколько максимум
          //$context['message'] = $this->t('Обработка элементов:');
        }
      }
//      $context['message'] = $this->t('Now processing node :progress of :count', [
//        ':progress' => $context['sandbox']['progress'],
//        ':count' => $context['sandbox']['max'],
//      ]);
    }
    // Increment total processed item values. Will be used in finished
    // callback.
    $context['results']['processed'] += $count;
    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }


  public function processItem($row_item,$service) {

    $type = $service->nodeType($row_item->url); // получаю ID ноды для дальнейшей проверки из БД
    $id = $service->nodeId($row_item->url); // получаю тип ноды для дальнейшей проверки из БД
    $nids = $service->existNode($type, $id); // вызываю функцию проверки ноды в БД, если есть, то вернется ID
    if (empty($nids)){
      $service->createNode($row_item); // вызываю метод создания ноды из сервиса
      $service->updateNode($row_item);
    }
    else {
      $service->updateNode($row_item);
    }
  }
*/
  /**
   * Finished callback for batch.
   */
  /*
  public function finished($success, $results, $operations) {
    $message = $this->t('Number of nodes affected by batch: @count', [
      '@count' => $results['processed'],
    ]);
    $this->messenger()
      ->addStatus($message);
  }
*/
}
