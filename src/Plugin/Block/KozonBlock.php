<?php

namespace Drupal\kozon\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\kozon\KozonStorage;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Provides a block called "Kozon block".
 *
 * @Block(
 *  id = "kozon_home_block",
 *  admin_label = @Translation("Kozon block")
 * )
 */
class KozonBlock extends BlockBase {

    /**
   * {@inheritdoc}
   */
  public function build() {
    $data = KozonStorage::getForumPost();
    
    $i=0;
    $aliasManager = \Drupal::service('path.alias_manager');
    // If user is ananomous, the append the destination back url.
    $user = \Drupal::currentUser()->id();
    $destination = '';
    if ($user == 0) {
      $destination = '?like-dislike-redirect=' . \Drupal::request()->getRequestUri();
    }
    $rawdata = [];
    foreach ($data as $value) {

      $rawdata[$i]["name"]=(!empty($value->firstname))?ucwords(strtolower($value->firstname.' '.$value->lastname)):ucwords(strtolower($value->name));
      $rawdata[$i]["alias"]=$aliasManager->getAliasByPath('/node/'.$value->nid);
      $rawdata[$i]["title"]=$value->title;
      $date = date('j F Y \à H:i', $value->changed);
      $date = self::buildDate($date); 
      $rawdata[$i]["date"]=$date;
      $rawdata[$i]["body"]=(strlen($value->body_value)>100)?substr(strip_tags(str_replace('&nbsp;', '', $value->body_value)), 0, 100).'...':strip_tags(str_replace('&nbsp;', '', $value->body_value));
      $rawdata[$i]["user"]=$value->uid;
      $image = KozonStorage::getForumImage($value->nid);
      if($image){
        $rawdata[$i]["image"]=str_replace('public://', '/sites/default/files/styles/thumbnail/public/', $image->uri);
      }
      else {
        $rawdata[$i]["image"]='';
        $node = node_load($value->nid);
        $fileforum = File::load( $node->get('field_file')->target_id );
        if($fileforum){
          $filemime = $fileforum->getMimeType();
          if(strpos($filemime, 'image')!==false){
            $rawdata[$i]["image"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($fileforum->getFileUri()))->toString());   
          }
        }
      }

      
      // Data to be passed in the url.
      $likes = KozonStorage::getForumLike($value->nid);
      $ban = 0;
      $likec = json_decode($likes->field_like_clicked_by);
      if(is_array($likec)){
        foreach($likec as $key => $value) {
            if($key == $user){
              $ban = 1;
            }
        }
      }
      $initial_data = [
        'entity_type' => 'node',
        'entity_id' => $value->nid,
        'field_name' => 'field_like',
        'likes' => (isset($likes->field_like_likes))?$likes->field_like_likes:'0',
        'dislikes' => '0',
        'kozon' => '1'
      ];

      $data = base64_encode(json_encode($initial_data));
      $like_url = Url::fromRoute(
        'like_kozon.manager', ['clicked' => 'like', 'data' => $data]
      )->toString();

      $rawdata[$i]["like_url"] = $like_url . $destination;
      $rawdata[$i]["likes"] = $likes->field_like_likes;
      $rawdata[$i]["lidis_id"] = $value->nid;
      $rawdata[$i]["like_band"] = $ban;
      $rawdata[$i]["tid"] = $value->tid;
      $rawdata[$i]["nid"] = $value->nid;
      
      $i++;
      
    }

    return [  
      '#theme' => 'kozon_home_block',
      '#items' => $rawdata,
      '#attached' => ['library' => ['core/drupal.ajax']],
      '#cache' => array('max-age' => 0),
      ];
    
  }

  /**
   * {@inheritdoc}
   */
  public function buildDate($currentDateTime) {
    setlocale(LC_TIME, "fr_FR");

    $currentDateTime = str_replace('Monday', 'Lundi', $currentDateTime);
    $currentDateTime = str_replace('Tuesday', 'Mardi', $currentDateTime);
    $currentDateTime = str_replace('Wednesday', 'Mercredi', $currentDateTime);
    $currentDateTime = str_replace('Thursday', 'Jeudi', $currentDateTime);
    $currentDateTime = str_replace('Friday', 'Vendredi', $currentDateTime);
    $currentDateTime = str_replace('Saturday', 'Samedi', $currentDateTime);
    $currentDateTime = str_replace('Sunday', 'Dimanche', $currentDateTime);
    $currentDateTime = str_replace('January', 'Janvier', $currentDateTime);
    $currentDateTime = str_replace('February', 'Février', $currentDateTime);
    $currentDateTime = str_replace('March', 'Mars', $currentDateTime);
    $currentDateTime = str_replace('April', 'Avril', $currentDateTime);
    $currentDateTime = str_replace('May', 'Mai', $currentDateTime);
    $currentDateTime = str_replace('June', 'Juin', $currentDateTime);
    $currentDateTime = str_replace('July', 'Juillet', $currentDateTime);
    $currentDateTime = str_replace('August', 'Août', $currentDateTime);
    $currentDateTime = str_replace('September', 'Septembre', $currentDateTime);
    $currentDateTime = str_replace('October', 'Octobre', $currentDateTime);
    $currentDateTime = str_replace('November', 'Novembre', $currentDateTime);
    $currentDateTime = str_replace('December', 'Décembre', $currentDateTime);

    $currentDateTime = str_replace('monday', 'Lundi', $currentDateTime);
    $currentDateTime = str_replace('tuesday', 'Mardi', $currentDateTime);
    $currentDateTime = str_replace('wednesday', 'Mercredi', $currentDateTime);
    $currentDateTime = str_replace('thursday', 'Jeudi', $currentDateTime);
    $currentDateTime = str_replace('friday', 'Vendredi', $currentDateTime);
    $currentDateTime = str_replace('saturday', 'Samedi', $currentDateTime);
    $currentDateTime = str_replace('sunday', 'Dimanche', $currentDateTime);
    $currentDateTime = str_replace('january', 'Janvier', $currentDateTime);
    $currentDateTime = str_replace('february', 'Février', $currentDateTime);
    $currentDateTime = str_replace('march', 'Mars', $currentDateTime);
    $currentDateTime = str_replace('april', 'Avril', $currentDateTime);
    $currentDateTime = str_replace('may', 'Mai', $currentDateTime);
    $currentDateTime = str_replace('june', 'Juin', $currentDateTime);
    $currentDateTime = str_replace('july', 'Juillet', $currentDateTime);
    $currentDateTime = str_replace('august', 'Août', $currentDateTime);
    $currentDateTime = str_replace('september', 'Septembre', $currentDateTime);
    $currentDateTime = str_replace('october', 'Octobre', $currentDateTime);
    $currentDateTime = str_replace('november', 'Novembre', $currentDateTime);
    $currentDateTime = str_replace('december', 'Décembre', $currentDateTime);

      
    return $currentDateTime;
  }
}
