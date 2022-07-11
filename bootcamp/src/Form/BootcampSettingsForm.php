<?php
namespace Drupal\bootcamp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class BootcampSettingsForm extends ConfigFormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'bootcamp_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'bootcamp.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('bootcamp.settings');

        $form['text'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Текст, который вы хотите увидеть на странице заявки: '),
            '#default_value' => $config->get('text'),
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
// Retrieve the configuration
        $this->configFactory->getEditable('bootcamp.settings')
// Set the submitted configuration setting
            ->set('text', $form_state->getValue('text'))
            ->save();

        parent::submitForm($form, $form_state);
    }
}
