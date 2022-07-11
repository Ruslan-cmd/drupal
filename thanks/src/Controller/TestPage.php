<?php

namespace Drupal\thanks\Controller;


class TestPage {

  public function content(){
    /**
     * {@inheritdoc}
     */
      return [
        '#theme' => 'dummy_example_first',
      ];
  }
}
