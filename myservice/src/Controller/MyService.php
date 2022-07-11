<?php

namespace Drupal\myservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;

class MyService
{

  public function getResult($url)
  {
    try{
      // Отправка GET-запроса
      $request = \Drupal::httpClient()->get($url);
      // Ответ GET-запроса
      $response = $request->getBody()->getContents();
      $result = json_decode($response);
    }

  catch (Exception $e) {
    // Generic exception handling if something else gets thrown.
    \Drupal::logger('widget')->error($e->getMessage());
  }
    return $result;
  }

  public function existNode($type, $id)
  {
    $query = \Drupal::entityQuery('node')
      ->condition('type', $type)
      ->condition('field_swapi_id', $id);
    $nids = $query->execute();
    return $nids;
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

  //функция для получения массива из id нод для привязки
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

  public function createRelatedEntity($array_entity){
    // прохожусь по каждой связанной сущности, тоесть по url, получаю тип и id, этой ноды, проверяю на наличие,и если ее нет, то создаю
    foreach ($array_entity as $entity){
      $type = $this->nodeType($entity);
      $id = $this->nodeId($entity);
      $nids = $this->existNode($type,$id);
      if (!$nids){
        $entity = $this->getResult($entity);
        $this->createNode($entity);
      }
    }

  }
  //функция создания отношений
  public function createRelation($node,$array,$current_field){
    //вызываю функцию проверки на существование ноды с последующим созданием, если ее нет
    $this->createRelatedEntity($array);
    $id_array = $this->getIdArray($array);// загрузка новых нод из базы и получение их айдишников
    $node->set('field_'. $current_field , $id_array);
    $node->save();
  }

  public function updateNode($row_item){

    $type = $this->nodeType($row_item->url);
    $id = $this->nodeId($row_item->url);

    if ($type) {

      $node = $this->loadNode($type, $id); // получаю ноду из базы для того, чтобы в дальнейшем создать связи для нее
      // проверяю, если у ноды есть связанная сущность

      $array_of_related_fields = ['species','films', 'starships', 'vehicles' , 'characters' , 'planets' , 'pilots' , 'people', 'residents' ];

        foreach ($array_of_related_fields as $related_field){
          if (!empty($row_item->related_field)){
            $array = $row_item->$related_field;
            $current_field = $related_field;
            $this->createRelation($node,$array,$current_field);
          }
      }
      // }
      if ($row_item->films!=[]) {
        $array = $row_item->films; // получаю список ссылок связанных сущностей
        $current_field = 'films';
        $this->createRelation($node,$array,$current_field);

      }

      if ($row_item->species!=[]) {
        $array = $row_item->species;
        $current_field = 'species';
        $this->createRelation($node,$array,$current_field);
      }

      if ($row_item->starships!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array= $row_item->starships; // получаю список ссылок связанных сущностей
        $current_field = 'starships';
        $this->createRelation($node,$array,$current_field);
      }

      if ($row_item->vehicles!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array = $row_item->vehicles; // получаю список ссылок связанных сущностей
        $current_field = 'vehicles';
        $this->createRelation($node,$array,$current_field);
      }

      if ($row_item->characters!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array = $row_item->characters; // получаю список ссылок связанных сущностей
        $current_field = 'characters';
        $this->createRelation($node,$array,$current_field);
      }

      if ($row_item->planets!=[]) { // проверяю, есть ли в принципе адреса у поля
        $array= $row_item->planets; // получаю список ссылок связанных сущностей
        $current_field = 'planets';
        $this->createRelation($node,$array,$current_field);
      }

      if ($row_item->pilots!=[]) {
        $array = $row_item->pilots; // получаю список ссылок связанных сущностей
        $current_field = 'pilots';
        $this->createRelation($node,$array,$current_field);
      }

      if ($row_item->people!= []) {
        $array = $row_item->people; // получаю список ссылок связанных сущностей
        $current_field = 'people';
        $this->createRelation($node,$array,$current_field);
      }

      if ($row_item->residents!= []) {
        $array = $row_item->residents; // получаю список ссылок связанных сущностей
        $current_field = 'residents';
        $this->createRelation($node,$array,$current_field);
      }


        if (!empty($row_item->homeworld)){
          $array = array($row_item->homeworld); // получаю список ссылок связанных сущностей
          $current_field = 'homeworld';
          $this->createRelation($node,$array,$current_field);
        }

    }

  }

  public function createNode($row_item) {
    $api_url = (string)$row_item->url; //получаю URL будущей ноды
    $type_of_node = $this->nodeType($api_url); //получаю тип ноды
    $node_id = $this->nodeId($api_url);

    //  preg_match_all("/\d+/", $api_url, $counter);

    //Вызываю создание нод для каждого типа материала
    if ($type_of_node == 'people') { //проверить на наличие ноды по полю id и если она есть
      $this->createPeopleNode($row_item, $node_id);
    } elseif ($type_of_node == 'films') {
      $this->createFilmsNode($row_item, $node_id);
    } elseif ($type_of_node == 'planets') {
      $this->createPlanetsNode($row_item, $node_id);
    } elseif ($type_of_node == 'species') {
      $this->createSpeciesNode($row_item, $node_id);
    } elseif ($type_of_node == 'starships') {
      $this->createStarshipsNode($row_item, $node_id);
    } elseif ($type_of_node == 'vehicles') {
      $this->createVehiclesNode($row_item, $node_id);
    }
  }

  public function createPeopleNode($data, $node_id){
    $node = Node::create(array(
      'type' => 'people' ,
      'title' => $data->name,
      'field_swapi_id' => $node_id,
      'field_name' =>$data->name,
      'field_height' =>$data->height,
      'field_mass' =>$data->mass,
      'field_hair_color' =>$data->hair_color,
      'field_skin_color' =>$data->skin_color,
      'field_eye_color' =>$data->eye_color,
      'field_birth_year' =>$data->birth_year,
      'field_gender' =>$data->gender,
      'langcode' => 'ru',
      'status' => 1,
    ));
    $node->save();
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createFilmsNode($data, $node_id){
    $node = Node::create(array(
      'type' => 'films' ,
      'title' => /*'film' . $counter[0][0]*/$data->title,
      'field_swapi_id' => $node_id,
      'field_title' =>$data->title,
      'field_episode_id' =>$data->episode_id,
      'field_director' =>$data->director,
      'field_opening_crawl' =>$data->opening_crawl,
      'field_producer' =>$data->producer,
      'field_release_date' =>$data->release_date,
      'langcode' => 'ru',
      'status' => 1,
    ));
    $node->save();
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createPlanetsNode($data ,$node_id){
    $node = Node::create(array(
      'type' => 'planets' ,
      'title' => /*'planet' . $counter[0][0]*/$data->name,
      'field_swapi_id' => $node_id,
      'field_name' =>$data->name,
      'field_diameter' =>$data->diameter,
      'field_rotation_period' =>$data->rotation_period,
      'field_orbital_period' =>$data->orbital_period,
      'field_gravity ' =>$data->gravity,
      'field_population' =>$data->population,
      'field_climate' =>$data->climate,
      'field_terrain' =>$data->terrain,
      'field_surface_water' =>$data->surface_water,
      'langcode' => 'ru',
      'status' => 1,
    ));
    $node->save();
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createSpeciesNode($data ,$node_id)
  {
    $node = Node::create(array(
      'type' => 'species' ,
      'title' => /*'specie' . $counter[0][0]*/$data->name,
      'field_swapi_id' => $node_id,
      'field_name' =>$data->name,
      'field_language' =>$data->language,
      'field_skin_colors' =>$data->skin_colors,
      'field_hair_colors' =>$data->hair_colors,
      'field_eye_colors' =>$data->eye_colors,
      'field_designation' =>$data->designation,
      'field_classification' =>$data->classification,
      'field_average_lifespan' =>$data->average_lifespan,
      'field_average_height' =>$data->average_height,
      'langcode' => 'ru',
      'status' => 1,
    ));
    $node->save();
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createStarshipsNode($data ,$node_id){
    $node = Node::create(array(
      'type' => 'starships' ,
      'title' => /*'starship' . $counter[0][0]*/$data->name,
      'field_swapi_id' => $node_id,
      'field_name' =>$data->name,
      'field_model' =>$data->model,
      'field_starship_class' =>$data->starship_class,
      'field_manufacturer' =>$data->manufacturer,
      'field_cost_in_credits' =>$data->cost_in_credits,
      'field_length' =>$data->length,
      'field_crew' =>$data->crew,
      'field_passengers' =>$data->passengers,
      'field_max_atmosphering_speed' =>$data->max_atmosphering_speed,
      'field_hyperdrive_rating' =>$data->hyperdrive_rating,
      'field_MGLT' =>$data->MGLT,
      'cargo_capacity' =>$data->cargo_capacity,
      'field_consumables' =>$data->consumables,
      'langcode' => 'ru',
      'status' => 1,
    ));
    $node->save();
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createVehiclesNode($data, $node_id){
    $node = Node::create(array(
      'type' => 'vehicles' ,
      'title' => /*'vehicle' . $counter[0][0]*/$data->name,
      'field_swapi_id' => $node_id,
      'field_name' =>$data->name,
      'field_model' =>$data->model,
      'field_vehicle_class' =>$data->vehicle_class,
      'field_manufacturer' =>$data->manufacturer,
      'field_cost_in_credits' =>$data->cost_in_credits,
      'field_length' =>$data->length,
      'field_crew' =>$data->crew,
      'field_passengers' =>$data->passengers,
      'field_max_atmosphering_speed' =>$data->max_atmosphering_speed,
      'cargo_capacity' =>$data->cargo_capacity,
      'field_consumables' =>$data->consumables,
      'langcode' => 'ru',
      'status' => 1,
    ));
    $node->save();
  }

}
