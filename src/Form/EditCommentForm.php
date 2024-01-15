<?php
/**
 * @file
 * Contains Drupal\kozon\Form\AddCommentForm
 */

namespace Drupal\kozon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\kozon\KozonStorage;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityBase;
use Drupal\file\Entity\File;

/**
 * Class AddForm.
 *
 * @package Drupal\kozon\Form\AddForm
 */
class EditCommentForm extends FormBase {

  use StringTranslationTrait;

  protected $account;

  public function __construct(){
    $this->account = \Drupal::currentUser();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kozon_comment_add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$arg = NULL) {
    global $base_url;
    $args = json_decode($arg);
    error_log(print_r($args,true));
    $comment = KozonStorage::getCommentPosteByCommentId($args[0]);
    error_log(print_r($comment,true));
    error_log(print_r($comment->cid,true));
    error_log(print_r($comment->body,true));
    $form = array(
        '#attributes' => array('enctype' => 'multipart/form-data'),
        'title' => array(
          '#markup' => '<a class="kcomment"><p>Ma réponse</p></a>',
        ),
        'postid' => array(
          '#type' => 'hidden',
          '#value' => $args[1],
        ),
        'cid' => array(
          '#type' => 'hidden',
          '#value' => $comment->cid,
        ),
        'body' => array(
          '#type' => 'text_format',
          '#size' => 20,
          '#format' => 'full_html',
          '#attributes' => array(
            'style' => 'width: 90%',
          ),
          '#default_value' => $comment->body,
          '#required' => TRUE,
        ),
        'fileforum' => array(
          '#type' => 'managed_file',
          '#attributes' => array(
            'style' => 'width: 90%; margin-top: 15px;',
          ),
          '#title' => t('AJOUTER UNE PIÈCE JOINTE'),
          '#upload_location' => 'public://forum_files/',
        ),
        
        'submit' => array(
          '#type' => 'submit',
          '#value' => $this->t('VALIDER'),
          '#ajax'  => array(
            'url' => Url::fromRoute('kozon.update.comment'),
          ),
          '#attributes' => array(
            'class' => array('ksubmit-add'),
          ),
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
