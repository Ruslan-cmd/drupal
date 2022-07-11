<?php
namespace Drupal\myservice\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\myservice\Plugin\QueueWorker\MyQueueWorker;
use Drupal\node\Entity\Node;
/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "my_node_queue",
 *   title = @Translation("Create Node"),
 *   cron = {"time" = 60}
 * )
 */
class MyNodeQueueWorker extends QueueWorkerBase
{
  // Функция для определения типа материала, для дальнейшего создания ноды по данному типу
public function nodeType($api_url){

  $array_of_url = explode('/', $api_url); //разделяю адрес АПИ на массив элементов
  $type_of_node = $array_of_url[4]; // название необходимой ноды - ВСЕГДА четвертый элемент данного массива
  return $type_of_node;
}

public function nodeId($api_url){
  $array_of_url = explode('/', $api_url); //разделяю адрес АПИ на массив элементов
  $node_id = $array_of_url[5];
  return $node_id;
}

public function processItem($data){

      $api_url = (string)$data->url; //получаю URL будущей ноды
      $type_of_node =  $this->nodeType($api_url); //получаю тип ноды
      $node_id = $this->nodeId($api_url);

      preg_match_all("/\d+/", $api_url, $counter);

      //Вызываю создание нод для каждого типа материала
      if ($type_of_node == 'people'){
        //проверить на наличие ноды по полю id и если она есть
          $this->createPeopleNode($counter,$data,$node_id);

      }
      elseif ($type_of_node == 'films'){
        $this->createFilmsNode($counter,$data,$node_id);
      }
      elseif ($type_of_node == 'planets'){
        $this->createPlanetsNode($counter,$data,$node_id);
      }
      elseif ($type_of_node == 'species'){
       $this->createSpeciesNode($counter,$data,$node_id);
      }
      elseif ($type_of_node == 'starships'){
        $this->createStarshipsNode($counter,$data,$node_id);
      }
      elseif ($type_of_node == 'vehicles'){
        $this->createVehiclesNode($counter,$data,$node_id);
      }

    //  $secondQueue = \Drupal::queue('my_second_node_queue');
     // $secondQueue->createItem($data);
    }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createPeopleNode($counter, $data, $node_id){
  $node = Node::create(array(
    'type' => 'people' ,
    'title' => /*'people' . $counter[0][0]*/$data->name,
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
  public function createFilmsNode($counter, $data, $node_id){
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
  public function createPlanetsNode($counter, $data ,$node_id){
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
  public function createSpeciesNode($counter, $data ,$node_id)
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
  public function createStarshipsNode($counter, $data ,$node_id){
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
  public function createVehiclesNode($counter, $data, $node_id){
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
