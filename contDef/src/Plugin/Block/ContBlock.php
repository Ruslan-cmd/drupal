<?php


namespace Drupal\contDef\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Session\AccountInterface;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

/**
 * Provides a 'ContDef' Block.
 *
 * @Block(
 *   id = "cont_definition_block",
 *   admin_label = @Translation("ContDef block"),
 *   category = @Translation("ContDefBlock"),
 * )
 */

class ContBlock extends BlockBase
{
  /**
   * {@inheritdoc}
   */
  public function build(){
    $context = EntityContext::fromEntityType(\Drupal::entityTypeManager()->getDefinition('user'));
  }
}
