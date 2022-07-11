<?php
namespace Drupal\drush;

use Drupal\Core\Datetime\Element\Datetime;

 class BatchService {

   /**
    * Processor for batch operations.
    */
   public function processItems($items, &$context) {

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

           self::processItem($row_item,$service);
           $counter++;
           $count++;
           $context['sandbox']['progress']++;
           $context['message'] = t('Now processing node :progress of :count', [
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
     //изначально финиш равен 1, если я уберу условие то цикл 1 раз пройдет, пока финиш меньше он будет вновь и вновь заходить
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

   /**
    * Finished callback for batch.
    */
   public function finished($success, $results, $operations)
   {
     $messenger = \Drupal::messenger();
     $messenger->addMessage(t('@count results processed.', ['@count' => $results['processed']]));
   }
 }
