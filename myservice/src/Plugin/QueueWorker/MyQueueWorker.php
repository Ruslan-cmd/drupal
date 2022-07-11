<?php
namespace Drupal\myservice\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\myservice\Controller\MyService;
/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "my_test_queue",
 *   title = @Translation("My queue worker"),
 *   cron = {"time" = 60}
 * )
 */

class MyQueueWorker extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */


  // 1 API
  public function processItem($data)
  {

      //$queue = \Drupal::queue('my_node_queue'); //объявляю очередь
   //   $secondQueue = \Drupal::queue('my_second_node_queue');

      $url = $data; // перезаписываю адреса
      $service = \Drupal::service('myservice.my'); //обращаюсь к сервису

    do {
      $request = $service->getResult($url);
      $result = $request->results; // выкинул служебную информацию
      foreach ($result as $row_item){

        $type =$service->nodeType($row_item->url); // получаю ID ноды для дальнейшей проверки из БД
        $id = $service->nodeId($row_item->url); // получаю тип ноды для дальнейшей проверки из БД
        $nids = $service->existNode($type, $id); // вызываю функцию проверки ноды в БД, если есть, то вернется ID

        if (empty($nids)){
         // $queue->createItem($row_item);
           $service->createNode($row_item); // вызываю метод создания ноды из сервиса
           $service->updateNode($row_item);
        }
        else {
          $service->updateNode($row_item);
        }

      }

      $url = $request->next;

    } while ($url);
  }
}

