<?php
namespace Drupal\localtask\Controller;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\node\NodeInterface;

class DrupalLocalTask {

  public function getResult($url)
  {
    //иньъек.зависимости протестировать
    try{
      // Отправка GET-запроса
      $request = \Drupal::httpClient()->get($url);
      // Ответ GET-запроса
      $response = $request->getBody()->getContents();
      $result = $response;
    }

    catch (Exception $e) {
      // Generic exception handling if something else gets thrown.
      \Drupal::logger('widget')->error($e->getMessage());
    }
    return $result;
  }

public function swapi($node){
 // $perms = array_keys(\Drupal::service('user.permissions')->getPermissions());
  $type = $node->getType();
  $id = $node->get('field_swapi_id')->value;
  $conc = 'https://swapi.dev/api/'.$type.'/'.$id.'/';
  $request = $this->getResult($conc);

  return [
    '#markup' => $request,
  ];
}
  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
public function access(\Drupal\node\NodeInterface $node , AccountInterface $account){
  $type = $node->getType();
  $perm = 'edit own '. $type .' content';
  return AccessResult::allowedIf($account->hasPermission($perm));
}
}
