<?php

namespace Drupal\testblock\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Url;
/**
 * Provides a resource to get user by email.
 *
 * @RestResource(
 *   id = "get_user_by_email_resource",
 *   label = @Translation("Get user by email resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/get-user-by-email",
 *     "create" = "/api/v1/get-user-by-email",
 *   }
 * )
 */
class GetUserByEmailResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Default limit entities per request.
   */
  protected $limit = 10;

  /**
   * Constructs a new ListArticlesResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('dummy'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   */
  public function get(Request $request) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $cache = CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'max-age' => 600,
        'contexts' => ['url.query_args'],
      ],
    ]);
    $response = [
      'count' => 0,
      'next_page' => FALSE,
      'prev_page' => FALSE,
    ];
 // page = 0;
    // получаю параметры текущей страницы из запроса пользователя, если нет то выставляю дефолтные значения
   // $request = \Drupal::request();
    $request_query = $request->query;
    $request_query_array = $request_query->all();
    $limit = $request_query->get('limit') ?: $this->limit;
    $page = $request_query->get('page') ?: 0;

    // Find out how many articles do we have.
    // Узнаю, сколько всего у меня людей на сайте и устанавливаю предыдущую и следующую страницу
    //колличество людей на сайте
    $query = \Drupal::entityQuery('node')->condition('type', 'people');
    $articles_count = $query->count()->execute();

    // текущая позиция - лимит с шагом в 10
    $position = $limit * ($page + 1);
    //если колличество людей больше текущей позиции - задаю следующую страницу, получаю ее урл
    if ($articles_count > $position) {
      $next_page_query = $request_query_array;
      $next_page_query['page'] = $page + 1;
      $response['next_page'] = Url::createFromRequest($request)
        ->setOption('query', $next_page_query)
        ->toString(TRUE)
        ->getGeneratedUrl();  //протестировать
    }
    $response['count'] = $articles_count;

// если мы перешли на следующую страницу - нужно сделать предыдущую по аналогии
    if ($page > 0) {
      $prev_page_query = $request_query_array;
      $prev_page_query['page'] = $page - 1;
      $response['prev_page'] = Url::createFromRequest($request)
        ->setOption('query', $prev_page_query)
        ->toString(TRUE)
        ->getGeneratedUrl();
    }



    // Find articles.
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'people')
      ->sort('created', 'DESC')
      ->pager($limit);
    $result = $query->execute();
    $articles = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadMultiple($result);



    /** @var \Drupal\node\Entity\Node $article */
    $testarrayfield = ['field_films' , 'field_species'];

    foreach ($articles as $article) {
      foreach ($testarrayfield as $field) {
        $rez[$field] = $this->getRelated($article,$cache, $field);
      }

      $response['results'][] = [
        'name' => $article->get('field_name')->value,
        'height' =>$article->get('field_height')->value ,
        'mass' => $article->get('field_mass')->value,
        'hair_color' => $article->get('field_hair_color')->value,
        'skin_color' => $article->get('field_skin_color')->value,
        'eye_color' => $article->get('field_eye_color')->value,
        'birth_year' =>$article->get('field_birth_year')->value ,
        'gender' => $article->get('field_gender')->value,
        'homeworld' =>$rez['field_homeworld'],
        'films' => $rez['field_films'],
        'species' =>$rez['field_species'],
        'vehicles' =>$rez['field_vehicles'],
        'starships' =>$rez['field_starships'],
        'created' => $article->getCreatedTime(),
        'edited' => $article->getChangedTime(),
        'url' => $article->get('field_url')->value,
      ];
      $cache->addCacheableDependency($article);
    }

    return (new ResourceResponse($response, 200))->addCacheableDependency($cache);
  }
  public function getRelated($article, &$cache, $field){

    $related = $article->get($field)->referencedEntities();
    foreach ($related as $value){
      $cache->addCacheableDependency($value);
      $res_film[] = [
        $value->id() => $value->label()
      ];
    }
    return $res_film;
  }

  public function post($data) {
    if (!$this->currentUser->hasPermission('create article content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      // для каждого персонажа
      foreach ($data as $row){
        //делаю запрос в БД для того чтобы узнать колличество нод которые у меня на сайте
        $query = \Drupal::entityQuery('node')->condition('type', 'people');
        $articles_count = $query->count()->execute();
        ++$articles_count; //нужно для свапи айди
        $node = Node::create(array(
          'type' => 'people' ,
          'title' => $row['name'],
          'field_swapi_id' => $articles_count,
          'field_name' => $row['name'],
          'field_height' => $row['height'],
          'field_mass' =>$row['mass'],
          'field_hair_color' =>$row['hair_color'],
          'field_skin_color' =>$row['skin_color'],
          'field_eye_color' =>$row['eye_color'],
          'field_birth_year' =>$row['birth_year'],
          'field_gender' =>$row['gender'],
          'langcode' => 'ru',
          'status' => 1,
        ));
// прохожусь по данным о фильме -  айдишник и название
           foreach ($row['films'] as $film){
             foreach ($film as $key => $value){
               $array_films_keys[] = $key; // вытягиваю айдишник
             }
           }
          foreach ($row['species'] as $specie){
          foreach ($specie as $key => $value){
            $array_specie_keys[] = $key;
          }
        }
        foreach ($row['vehicles'] as $vehicle){
          foreach ($vehicle as $key => $value){
            $array_vehicle_keys[] = $key;
          }
        }
        foreach ($row['starships'] as $starship){
          foreach ($starship as $key => $value){
            $array_starship_keys[] = $key;
          }
        }
        foreach ($row['homeworld'] as $homeworld){
          foreach ($homeworld as $key => $value){
            $array_homeworld_keys[] = $key;
          }
        }
        $node->save();
        $node->set('field_films',$array_films_keys);
        $node->set('field_species',$array_specie_keys);
        $node->set('field_vehicles',$array_vehicle_keys);
        $node->set('field_starships',$array_starship_keys);
        $node->set('field_homeworld',$array_homeworld_keys);
       // $node->set('field_films',$array_films_keys);
        $node->save();

      }

      return new ResourceResponse($node);
    } catch (\Exception $e) {
      return new ResourceResponse('Something went wrong during entity creation. Check your data.', 400);
    }
  }

}
