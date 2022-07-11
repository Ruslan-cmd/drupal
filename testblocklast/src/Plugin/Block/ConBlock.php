<?php

namespace Drupal\testblocklast\Plugin\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Psr\Container\ContainerInterface;

/**
 * Provides a 'Text' Block.
 *
 * @Block(
 *   id = "testblocklast_text_block_block",
 *   admin_label = @Translation("Text block last"),
 *   category = @Translation("TestLastBlock"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", required = FALSE, label = @Translation("Node")),
 *     "term" = @ContextDefinition("entity:taxonomy_term", required = FALSE, label = @Translation("Term"))
 *   }
 * )
 */
class ConBlock extends BlockBase{

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = '';
    $node = $this->getContextValue('node');
    $term = $this->getContextValue('term');
    $rss_output = [];
    if (!empty($node)){
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
      $rss_output = $view_builder->view($node, 'teaser');
      //$output = \Drupal::service('renderer')->render($rss_output);
    }
    elseif (!empty($term)) {
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('taxonomy_term');
      $rss_output = $view_builder->view($term, 'teaser');
     // $output = \Drupal::service('renderer')->render($rss_output);
    }

    return $rss_output;
  }
}
