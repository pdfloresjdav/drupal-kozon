<?php
/**
 * @file
 * Contains Drupal\kozon\Form\EditForm
 */

namespace Drupal\kozon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\module_hero\HeroStorage;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityBase;
use Drupal\file\Entity\File;
use Drupal\kozon\KozonStorage;

/**
 * Class AddForm.
 *
 * @package Drupal\kozon\Form\EditForm
 */
class EditForm extends FormBase {

  use StringTranslationTrait;

  protected $account;

  public function __construct(){
    $this->account = \Drupal::currentUser();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kozon_forum_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = NULL) {
    global $base_url;
    $node = node_load($arg);
    $title = $node->getTitle();
    $body = $node->get('body')->value;
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('forums');
    foreach ($terms as $term) {
      $enableKozon = KozonStorage::getTaxForumEnableb($term->tid);
      if(!empty($enableKozon->value) && $enableKozon->value == 1){
        $term_data[$term->tid] = $term->name;
      }
    }

    $termsicons = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('icon');
    foreach ($termsicons as $term) {
      $icon = HeroStorage::getImageId($term->tid);
      if(isset($icon)){
        $drupal_file_uri = File::load($icon->field_image_target_id)->getFileUri();
        $image_path = file_url_transform_relative(file_create_url($drupal_file_uri));
        $term_data_icon[$term->tid] =  '<img src="'.$image_path.'" alt="'.$term->name.'" height="30" width="30">';
      }
      else{
        $term_data_icon[$term->tid] = $term->name;
      }
    }
    
    $form = array(
        '#attributes' => array('enctype' => 'multipart/form-data', 'class' => 'knew-forum'),
        'explanations' => array(
          '#markup' => '',
        ),
        'nid' => array(
          '#type' => 'hidden',
          '#value' => $arg,
        ),
        'title' => array(
          '#type' => 'textfield',
          '#size' => 20,
          '#maxlength' => 255,
          '#default_value' => $title,
          '#attributes' => array(
            'style' => 'width: 100%',
            'class' => array('favorites-add-textfield'),
          ),
          '#required' => TRUE,
        ),
        'taxforum' => array(
          '#type' => 'select',
          '#options' => $term_data,
          '#empty_option' => 'Catégorie du message *',
          '#default_value' => $node->get('taxonomy_forums')->target_id,
          '#required' => TRUE,
        ),
        'body' => array(
          '#type' => 'text_format',
          '#size' => 20,
          '#format' => 'full_html',
          '#attributes' => array(
            'style' => 'width: 100%',
          ),
          '#default_value' => $body,
          '#required' => TRUE,
        ),
        'fileforum' => array(
          '#type' => 'managed_file',
          '#attributes' => array(
            'style' => 'width: 100%; margin-top: 15px;',
            'class' => 'kattach-upload',
          ),
          '#title' => t('AJOUTER UNE PIÉCE JOINTE'),
          '#upload_location' => 'public://forum_files/',
          '#require' => false
        ),
        
        'submit' => array(
          '#type' => 'submit',
          '#value' => $this->t('VALIDER'),
          '#ajax'  => array(
            'url' => Url::fromRoute('kozon.update.forum'),
          ),
          '#attributes' => array(
            'class' => array('ksubmit-add'),
          ),
        ),

        'obligatories' => array(
          '#markup' => '<p class="kobligatories">* champs obligatoires</p>',
        ),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   * @todo obsolete?
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   * @todo obsolete?
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
