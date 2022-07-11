<?php
/**
 * @file
 * Contains \Drupal\formApi\Form\FormApiTask.
 */
namespace Drupal\formApi\Form;

use Drupal\Core\Ajax\AddCssCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
class FormApiTask extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name:'),
    );
    $form['email'] = [
      '#title' => 'Email',
      '#type' => 'email',
    ];

    $form['radio'] = array(
      '#type' => 'radios',
      '#title' => t('Наличие сайта'),
      '#options' => array('1' => 'У меня есть сайт', '2' => 'У меня нет сайта'),
      '#default_value' => 'У меня нет сайта',
    );

    $form['site_address'] = array (
      '#type' => 'textfield',
      '#title' => ('Site address:'),
      '#states' => array(
        'visible' => array(
          ':input[name = "radio"]' => array(
            'value' => '1',
          ),
        ),
      ),
      );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => 'Submit this form',
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];



    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
  }

  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state){

    $response = new AjaxResponse();
    //обнуление стилей при повторном сабмите - необходимо удалить стиль надписи под полем, чтобы их не было 1000
    $selector = '.domen-error';
    $response->addCommand(new RemoveCommand($selector));
    $selector = '.email-error';
    $response->addCommand(new RemoveCommand($selector));
    $selector = '.name-error';
    $response->addCommand(new RemoveCommand($selector));

    //ВАЛИДАЦИЯ адреса сайта
    //она зависит от того, выбрал ли я нужную радиокнопку. Если выбрано "у меня нет сайта" , то валидация не нужна
    if ($form_state->getValue('radio') === '1') {     //выбрал "У меня есть сайт"
      $reg = '/(https?:\/\/)?(www.)?([\da-z\.-]+)\.([a-z\.]{2,6})/ui';
      if (preg_match($reg, $form_state->getValue('site_address'), $array_of_site_address)) {
        // Если пользователь повторно ввел все нормально в это поле, то при сабмите сбросим красную рамку
        if (end($array_of_site_address) === 'ru' || end($array_of_site_address) === 'рф') {
          $style = '<style>#edit-site-address{border:1px solid grey !important;}</style>';
          $response->addCommand(new AddCssCommand($style));
          // при успешной валидации убираем из результата http или https или www

          $result = preg_replace('/((https?:\/\/)||(www\.))/ui', '', $form_state->getValue('site_address'));
          $valid_site_address = true;

        } else {
          //ошибка домена
          $style = '<style>#edit-site-address{border:3px solid red}</style>';
          $response->addCommand(new AddCssCommand($style));
          $selector = '#edit-site-address';
          $content = '<p class = "domen-error">Вы ввели неправильную доменную зону, необходимая зона: "рф" или "ru"</p>';
          $response->addCommand(new AfterCommand($selector, $content));
          $style = '<style>.domen-error{color: red !important;}</style>';
          $response->addCommand(new AddCssCommand($style));
        }
      } else {
        //ошибка ввода самого адреса
        $style = '<style>#edit-site-address{border:3px solid red}</style>';
        $response->addCommand(new AddCssCommand($style));
        $selector = '#edit-site-address';
        if ($form_state->getValue('site_address') == NULL) {
          $content = '<p class = "domen-error">Необходимо ввести адрес сайта</p>';
        } else {
          $content = '<p class = "domen-error">Вы ввели адрес неправильно, пример адреса "www.example.ru"</p>';
        }

        $response->addCommand(new AfterCommand($selector, $content));
        $style = '<style>.domen-error{color: red !important;}</style>';
        $response->addCommand(new AddCssCommand($style));
      }
    }
    else {
      $radio = true;
      $result = '';
    }
     // ВАЛИДАЦИЯ email
     if (filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL))
     {
       $style = '<style>#edit-email{border:1px solid grey !important;}</style>';
       $response->addCommand(new AddCssCommand($style));
       $valid_email = true;
     }
     else
     {
       $style = '<style>#edit-email{border:3px solid red}</style>';
       $response->addCommand(new AddCssCommand($style));
       $selector = '#edit-email';

       if ($form_state->getValue('email') == NULL){
         $content = '<p class = "email-error">Необходимо ввести email</p>';
       }
       else {
         $content = '<p class = "email-error">Вы ввели email неправильно, пример адреса "example@mail.ru"</p>';
       }

       $response->addCommand(new AfterCommand($selector, $content));
       $style = '<style>.email-error{color: red !important;}</style>';
       $response->addCommand(new AddCssCommand($style));
     }

    //Валидация name
    if ($form_state->getValue('name') == NULL){

      $style = '<style>#edit-name{border:3px solid red}</style>';
      $response->addCommand(new AddCssCommand($style));
      $selector = '#edit-name';
      $content = '<p class = "name-error">Необходимо ввести имя</p>';
      $response->addCommand(new AfterCommand($selector, $content));
      $style = '<style>.name-error{color: red !important;}</style>';
      $response->addCommand(new AddCssCommand($style));

    }
    else {
      $valid_name = true;
      $style = '<style>#edit-name{border:1px solid grey !important;}</style>';
      $response->addCommand(new AddCssCommand($style));
    }

    //после валидации обращаюсь к БД и заполняю поля у таблицы

    if ($valid_email && $valid_name && ($valid_site_address || $radio)){

      $connection = \Drupal::service('database');
      $result = $connection->insert('students')
        ->fields([
          'name' => $form_state->getValue('name'),
          'email' => $form_state->getValue('email'),
          'site_address' => $result,
        ])
        ->execute();

      $content1['#attached']['library'][] = 'core/drupal.dialog.ajax';



      $myConfigPage = \Drupal\config_pages\Entity\ConfigPages::config('custom_config');
      $string_modal = $myConfigPage->get('field_modal_title')->value;
      $current_user = \Drupal::currentUser();
      $user = \Drupal\user\Entity\User::load($current_user->id());
      $name  = $user->get('name')->value;
      $user_last_name =  $user->field_last_name->value;
      if ($name == NULL){

        $content1['#result'] = $string_modal.", гость!";
      }
      else {
        $content1['#result'] = $string_modal. ', ' . $user_last_name. ' ' .$name;
      }

      $content1['#theme'] = 'form_theme';
      $title = 'Сообщение';
      $response->addCommand(new OpenModalDialogCommand($title, $content1, ['width' => '200', 'height' => '200']));
      //$response->addCommand(new AlertCommand('Спасибо за заполнение, нажмите "OK" для продолжения'));
      $url = '/';
      $response->addCommand(new RedirectCommand($url));

    }


    return $response;

  }


  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
