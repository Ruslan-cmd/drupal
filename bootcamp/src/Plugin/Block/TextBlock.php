<?php

namespace Drupal\bootcamp\Plugin\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Text' Block.
 *
 * @Block(
 *   id = "bootcamp_text_block_block",
 *   admin_label = @Translation("Text block"),
 *   category = @Translation("TextBlock"),
 * )
 */
class TextBlock extends BlockBase{
    /**
     * {@inheritdoc}
     */
    public function build() {
       $config = $this->getConfiguration();

       return [
           '#markup' => $config['bootcamp_text_block_settings']['value'],
       ];
    }
    /**
     * {@block}
     */
    protected function blockAccess(AccountInterface $account) {
        return AccessResult::allowedIfHasPermission($account, 'access content');
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $config = $this->getConfiguration();

        $form['bootcamp_text_block_settings'] = [
            '#type' => 'text_format',
            '#allowed_formats' => ['full_html' => 'full_html'],
            '#title' => $this->t('Content'),
            '#default_value' => isset($config['bootcamp_text_block_settings']['value']) ? $config['bootcamp_text_block_settings']['value'] : '',
        ];

        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['bootcamp_text_block_settings'] = $form_state->getValue('bootcamp_text_block_settings');
    }
}