<?php

namespace Drupal\bootcamp\Plugin\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
/**
 * Provides a 'Box' Block.
 *
 * @Block(
 *   id = "bootcamp_box_block_block",
 *   admin_label = @Translation("Box block"),
 *   category = @Translation("BoxBlock"),
 * )
 */
class BoxBlock extends BlockBase{

    public function build() {

        $config = $this->getConfiguration();

        $first_fid = Media::load($config['background_image'])->field_media_image->target_id;
        $first_file = File::load($first_fid);
        $first_url = $first_file->createFileUrl();

        $second_fid = Media::load($config['image_title'])->field_media_image->target_id;
        $second_file = File::load($second_fid);
        $second_url = $second_file->createFileUrl();

        $content = [
          'title' => $config['title'],
        'description' => $config['description'],
        'signature' => $config['signature'],
        'background_url' => $first_url,
        'top_background_url' => $second_url,
            ];

        return $content;
    }
    /**
     * {@inheritDoc}
     */
    protected function blockAccess(AccountInterface $account) {
        return AccessResult::allowedIfHasPermission($account, 'access content');
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $config = $this->getConfiguration();


        $form['background_image'] = [
            '#type' => 'media_library',
            '#allowed_bundles' => ['image'],
            '#title' => t('Выберите фоновую картинку'),
            '#default_value' => !empty($config['background_image'])
                ? $config['background_image']
                : [],
        ];

        $form['image_title'] = [
            '#type' => 'media_library',
            '#allowed_bundles' => ['image'],
            '#title' => t('Выберите фоновую картинку заголовка'),
            '#default_value' => !empty($config['image_title'])
                ? $config['image_title']
                : [],
        ];

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => t('Заголовок'),
            '#default_value' => !empty($config['title'])
                ? $config['title']
                : [],
        ];

        $form['description'] = [
            '#type' => 'textfield',
            '#title' => t('Описание'),
            '#default_value' => !empty($config['description'])
                ? $config['description']
                : [],
        ];


        $form['signature'] = [
            '#type' => 'textfield',
            '#title' => t('Подпись'),
            '#default_value' => !empty($config['signature'])
                ? $config['signature']
                : [],
        ];

        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['background_image'] = $form_state->getValue('background_image');
        $this->configuration['image_title'] = $form_state->getValue('image_title');
        $this->configuration['title'] = $form_state->getValue('title');
        $this->configuration['description'] = $form_state->getValue('description');
        $this->configuration['signature'] = $form_state->getValue('signature');
    }
}
