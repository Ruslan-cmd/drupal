<?php

namespace Drupal\link_test\Controller;

use Drupal\bootcamp\Form\BootcampSettingsForm;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
/**
 * Provides route responses for the DrupalBook module.
 */
//унаследовал класс
class LinkPage
{

  public function content(){
    $element  = array(
      '#markup' => 'Hello',
    );
    $media = Media::loadMultiple();
    foreach ($media as $media_file){
    $mid =  $media_file->get('mid')->value;
    $fid = $media_file->getSource()->getSourceFieldValue($media_file);
    $file = File::load($fid);
    $url[] = $file->url();
    }
    return $element;
  }


}

