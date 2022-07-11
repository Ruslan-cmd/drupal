<?php
/**
 * @file
 * Contains \Drupal\bootcamp\Controller\FirstPage.
 *
 */

namespace Drupal\bootcamp\Controller;

use Drupal\bootcamp\Form\BootcampSettingsForm;
/**
 * Provides route responses for the DrupalBook module.
 */
//унаследовал класс
class FirstPage extends BootcampSettingsForm{
    /**
     * Returns a simple page
     * @return array
     * A simple renderable array
     */
    public function content(){

        $config = $this->config('bootcamp.settings');

        $text = $config->get('text');

         if ($text){

             $element = array(
                 '#markup' => '<h2>'. $text,
             );
         }
         else {
             $element = array(
                 '#markup' => '<h2>Спасибо за вашу заявку</h2>',
             );
         }
        return $element;
    }
}