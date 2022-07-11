<?php

namespace Drupal\drush\Commands;

use Drupal\drush\BatchService;
use Drush\Commands\DrushCommands;
/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use
 */
class BatchCommands extends DrushCommands
{

  /**
   * A custom Drush command to displays the given text.
   *
   * @command drush-command-example:print-me
   * @param $text Argument with text to be printed
   * @option uppercase Uppercase the text
   * @aliases ccepm,cce-print-me
   */
  public function printMe($text = 'Hello world!', $options = ['uppercase' => FALSE])
  {
    if ($options['uppercase']) {
      $text = strtoupper($text);
    }
    $this->output()->writeln($text);
  }

  /**
   * A custom Drush command to displays the given text.
   *
   * @command drush-command-example:go-api
   * @param $date Argument with text to be printed
   * @aliases go-api
   */
  public function goApi($date = null) {
    //дата 0 - не ввели дату
    if ($date === null){
      $this->output()->writeln('Вы не ввели дату');
      return;
    }

    $timestamp = strtotime($date);  //перевод даты в нужный формат
    $service = \Drupal::service('myservice.my'); //обраение к сервису
    $request = $service->getResult('https://swapi.dev/api/'); // получение адресов апи
    foreach ($request as $api_url) { //цикл для каждого url апишки
      $url = $api_url;
      do {
        $request_items = $service->getResult($url); // обращаюсь к сервису, получаю уже на основе точного url элементы
        $result_items = $request_items->results; // выкинул служебную информацию, более структурировано выглядит

        foreach ($result_items as $row_item) { //прохожусь по каждому элементу

          $timestamp_swapi = strtotime($row_item->edited); // получаю дату со свапи
          if ($timestamp < $timestamp_swapi) { //если дата меньше чем дата свапи, делаею дальнейшую обработку элемента

            $type = $service->nodeType($row_item->url); // получаю ID ноды для дальнейшей проверки из БД
            $id = $service->nodeId($row_item->url); // получаю тип ноды для дальнейшей проверки из БД
            $nids = $service->existNode($type, $id); // вызываю функцию проверки ноды в БД, если есть, то вернется ID
            $this->output()->writeln('Обработана сущность: ' . $type . $id);  // более корректный вывод в самой команде
            if (empty($nids)) {
              $service->createNode($row_item); // вызываю метод создания ноды из сервиса
              $service->updateNode($row_item); // сразу апдейт, чтобы 2 раза не запускать прогу , если нод реферанца нет - создат и свяжет ТОЛЬКО на нужную референц
            } else {
              $service->updateNode($row_item);
            }
          }
        }

        $url = $request_items->next; // меняем УРЛ чтобы перейти на следующую страницу в апи

      } while ($url); // пока есть урл, т е пока у меня есть следующая страница
    }
  }
    /**
     * A custom Drush command to displays the given text.
     *
     * @command drush-command-example:go-batch-api
     * @param $date Argument with text to be printed
     * @aliases go-batch-api
     */

    public function goBatchApi($date = null) {
      if ($date === null){
        $this->output()->writeln('Вы не ввели дату');
      }
      else {
        $timestamp = strtotime($date);
        $service = \Drupal::service('myservice.my');
        $request = $service->getResult('https://swapi.dev/api/');
        $operations = [];
        // Prepare the operation. Here we could do other operations on nodes.
        $this->output()->writeln("Preparing batch: ");

      //  foreach ($request as $api_url) {
          //$url = $api_url;
          $url = 'https://swapi.dev/api/people/';
          do {
            $request_items = $service->getResult($url);
            $this->output()->writeln($url);
            $result_items = $request_items->results; // выкинул служебную информацию
            foreach ($result_items as $row_item) {
              $timestamp_swapi = strtotime($row_item->edited);
              if ($timestamp < $timestamp_swapi){
                $items[] = $row_item;
              }
            }

            $operations[] = [
              '\Drupal\drush\BatchService::processItems', [$items] ];

            $items = [];
            $url = $request_items->next;

          } while ($url);
       // }
        $this->output()->writeln('1');
        // 4. Create the batch.
        $batch = [
          'title' => t('test drush'),
          'operations' => $operations,
          'finished' => '\Drupal\drush\BatchService::finished',
        ];
        // 5. Add batch operations as new batch sets.
        foreach ($operations as $row){
          $this->output()->writeln($row);
        }
        batch_set($batch);
        $this->output()->writeln('2');
        // 6. Process the batch sets.
        drush_backend_batch_process();
        $this->output()->writeln('3');
      }
  }
}
