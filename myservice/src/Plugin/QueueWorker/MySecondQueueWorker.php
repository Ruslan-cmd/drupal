<?php
namespace Drupal\myservice\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "my_second_node_queue",
 *   title = @Translation("Update Node"),
 *   cron = {"time" = 60}
 * )
 */
class MySecondQueueWorker extends QueueWorkerBase
{
  public function loadNode($type, $swapi_id)
  {

    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => $type,
        'field_swapi_id' => $swapi_id,
      ]);
    foreach ($node as $concrete_node) {
      return $concrete_node;
    }
  }

  public function nodeType($api_url)
  {

    $array_of_url = explode('/', $api_url); //разделяю адрес АПИ на массив элементов
    $type_of_node = $array_of_url[4]; // название необходимой ноды - ВСЕГДА четвертый элемент данного массива
    return $type_of_node;
  }

  public function nodeId($api_url)
  {
    $array_of_url = explode('/', $api_url); //разделяю адрес АПИ на массив элементов
    $node_id = $array_of_url[5];
    return $node_id;
  }

  public function getIdArray($array)
  {  //получаю массив из адресов сущностей, которые нужно привязать
    foreach ($array as $ConcrArray) { // прохожусь по каждому адресу
      if ($ConcrArray != NULL) { // адрес должен быть не NULL
        $type = $this->nodeType($ConcrArray); // Узнаю тип ноды для связи
        $id = $this->nodeId($ConcrArray); // Узнаю айди ноды для связи
        $needNode = $this->loadNode($type, $id); //загружаю нужную ноду для связи
        $id_array[] = $needNode->id(); // получаю id этой ноды для будущей связи
      }
    }
    return $id_array;
  }


  public function processItem($data)
  {
    $type = $this->nodeType($data->url);
    $id = $this->nodeId($data->url);

    if ($type) {
      $node = $this->loadNode($type, $id);

      if ($data->films!=[]) {
        $array_films = $data->films; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_films); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_films', $id_array);
        $node->save();
      }

      if ($data->species!=[]) {
        $array_species = $data->species;
        $id_array = $this->getIdArray($array_species); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_species', $id_array);
        $node->save();
      }

      if ($data->starships!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array_starships = $data->starships; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_starships); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_starships', $id_array);
        $node->save();
      }

      if ($data->vehicles!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array_vehicles = $data->vehicles; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_vehicles); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_vehicles', $id_array);
        $node->save();
      }

      if ($data->characters!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array_characters = $data->characters; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_characters); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_characters', $id_array);
        $node->save();
      }

      if ($data->planets!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array_planets = $data->planets; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_planets); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_planets', $id_array);
        $node->save();
      }

      if ($data->pilots!=[]) {
        $array_pilots = $data->pilots; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_pilots); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_pilots', $id_array);
        $node->save();
      }

      if ($data->people!= []) {
        $array_people = $data->people; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_people); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_people', $id_array);
        $node->save();
      }

      if ($data->residents!= []) {
   $array_residents = $data->residents; // получаю список ссылок связанных сущностей
   $id_array = $this->getIdArray($array_residents); // загрузка новых нод из базы и получение их айдишников
   $node->set('field_residents', $id_array);
   $node->save();
 }

      if($data->homeworld){
        $homeworld[] = $data->homeworld; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($homeworld); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_homeworld', $id_array);
        $node->save();
      }
    }
  }
}
  /*if ($type == 'people'){
    $node = $this->loadNode($type,$id);

    if ($data->films!=[]){
      $array_films = $data->films; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_films); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_films', $id_array);
      $node->save();
    }

    if ($data->species!=[]){
      $array_species = $data->species;
      $id_array = $this->getIdArray($array_species); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_species', $id_array);
      $node->save();
    }

    if ($data->starships!=[]){ // проверяю, есть ли в принципе адреса у поля
      $array_starships = $data->starships; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_starships); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_starships', $id_array);
      $node->save();
    }

    if ($data->vehicles!=[]){ // проверяю, есть ли в принципе адреса у поля
      $array_vehicles = $data->vehicles; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_vehicles); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_vehicles', $id_array);
      $node->save();
    }

  }
  if ($type == 'films') {
    $node = $this->loadNode($type, $id);

    if ($data->species!=[]){
      $array_species = $data->species;
      $id_array = $this->getIdArray($array_species); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_species', $id_array);
      $node->save();
    }

    if ($data->starships!=[]){ // проверяю, есть ли в принципе адреса у поля
      $array_starships = $data->starships; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_starships); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_starships', $id_array);
      $node->save();
    }

    if ($data->vehicles!=[]){ // проверяю, есть ли в принципе адреса у поля
      $array_vehicles = $data->vehicles; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_vehicles); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_vehicles', $id_array);
      $node->save();
    }

    if ($data->characters!=[]){ // проверяю, есть ли в принципе адреса у поля
      $array_characters = $data->characters; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_characters); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_characters', $id_array);
      $node->save();
    }

    if ($data->planets!=[]){ // проверяю, есть ли в принципе адреса у поля
      $array_planets = $data->planets; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_planets); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_planets', $id_array);
      $node->save();
    }


  }
  if ($type == 'starships') {
    $node = $this->loadNode($type, $id);

      if ($data->films!=[]){
        $array_films = $data->films; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_films); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_films', $id_array);
        $node->save();
      }

      if ($data->pilots!=[]){
        $array_pilots = $data->pilots; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_pilots); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_pilots', $id_array);
        $node->save();
      }

    }

  if ($type == 'vehicles') {
    $node = $this->loadNode($type, $id);

    if ($data->films!=[]){
      $array_films = $data->films; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_films); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_films', $id_array);
      $node->save();
    }

    if ($data->pilots!=[]){
      $array_pilots = $data->pilots; // получаю список ссылок связанных сущностей
      $id_array = $this->getIdArray($array_pilots); // загрузка новых нод из базы и получение их айдишников
      $node->set('field_pilots', $id_array);
      $node->save();
    }

  }
    if ($type == 'species') {
      $node = $this->loadNode($type, $id);

      if ($data->films!=[]){
        $array_films = $data->films; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_films); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_films', $id_array);
        $node->save();
      }
      if ($data->people!=[]){
        $array_people = $data->people; // получаю список ссылок связанных сущностей
        $id_array = $this->getIdArray($array_people); // загрузка новых нод из базы и получение их айдишников
        $node->set('field_people', $id_array);
        $node->save();
      }
    }
  }

}

*/
/*public function speciesField($array_species){
    $id_array = $this->getIdArray($array_species);
    return $id_array;
  }

  public function starshipsField($array_starships){
    foreach ($array_starships as $starship){ // Прохожусь по каждому адресу связанной сущности
      if ($starship!=NULL) { // проверяю, явзяется ли NULL ( такое значение бывает )
        $type = $this->nodeType($starship); // получаю тип ноды по адресу
        $id = $this->nodeId($starship); // получаю id ноды по адресу
        $nodeStarship = $this->loadNode($type, $id);  //выгружаю ноду из базы данных
        $id_array[] = $nodeStarship->id(); // получаю массив id необходимых связанных сущностей
      }
    }
    return $id_array;
  }

  public function vehiclesField($array_vehicles){
    foreach ($array_vehicles as $vehicle){ // Прохожусь по каждому адресу связанной сущности
      if ($vehicle!=NULL) { // проверяю, явзяется ли NULL ( такое значение бывает )
        $type = $this->nodeType($vehicle); // получаю тип ноды по адресу
        $id = $this->nodeId($vehicle); // получаю id ноды по адресу
        $nodeVehicle = $this->loadNode($type, $id);  //выгружаю ноду из базы данных
        $id_array[] = $nodeVehicle->id(); // получаю массив id необходимых связанных сущностей
      }
    }
    return $id_array;
}

  public function charactersField($array_characters){
    foreach ($array_characters as $character){ // Прохожусь по каждому адресу связанной сущности
      if ($character!=NULL) { // проверяю, явзяется ли NULL ( такое значение бывает )
        $type = $this->nodeType($character); // получаю тип ноды по адресу
        $id = $this->nodeId($character); // получаю id ноды по адресу
        $nodeCharacter = $this->loadNode($type, $id);  //выгружаю ноду из базы данных
        $id_array[] = $nodeCharacter->id(); // получаю массив id необходимых связанных сущностей
      }
    }
    return $id_array;
  }

  public function planetsField($array_planets){
  foreach ($array_planets as $planet){ // Прохожусь по каждому адресу связанной сущности
    if ($planet!=NULL) { // проверяю, явзяется ли NULL ( такое значение бывает )
      $type = $this->nodeType($planet); // получаю тип ноды по адресу
      $id = $this->nodeId($planet); // получаю id ноды по адресу
      $nodePlanet = $this->loadNode($type, $id);  //выгружаю ноду из базы данных
      $id_array[] = $nodePlanet->id(); // получаю массив id необходимых связанных сущностей
    }
  }
  return $id_array;
}
*/
