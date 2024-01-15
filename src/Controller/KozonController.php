<?php

namespace Drupal\kozon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\kozon\KozonStorage;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Link;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\module_hero\HeroStorage;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Component\Serialization\Json;

/**
 * This is our hero controller.
 */
class KozonController extends ControllerBase {
	/**
   * Returns a render-able array for a test page.
   */
  public function contentTerm($taxid) {
  	$term = Term::load($taxid);
  	$rawdata =[];
    if($term){
      $rawdata["taxonomy"] = $term->getName();
      $rawdata["tid"] = $taxid;
      $color = KozonStorage::getTaxForumColor($taxid);
      $rawdata["color"] = $color->color;
    }
    $taxArticles = KozonStorage::getMessagePosteByTaxId($taxid);
    $taxArticlesAll = KozonStorage::getCMessagePosteByTaxId($taxid);
    
    $k=0;
    $artTag = [];
    foreach ($taxArticles as $article) {
    	$artTag[$k]['title'] = $article->title;
    	$artTag[$k]['nid'] = $article->nid;
    	$artTag[$k]["user"] = (!empty($article->firstname))?ucwords(strtolower($article->firstname.' '.$article->lastname)):ucwords(strtolower($article->username));
      $commentsC = KozonStorage::getCommentCountPosteById($article->nid);
      $comments = KozonStorage::getCommentGetPosteById($article->nid);

      $date1 = new \DateTime("now");
      $date2 = new \DateTime();
      $time = '';
      if(!empty($comments)){
        $date2->setTimestamp($comments->datechange);
        $interval = $date2->diff($date1);
        
        if($interval->days > 0){
          $time = $interval->format('Il y a %a jours');
        }
        else{
          if($interval->h > 0){
            $time = $interval->format('Il y a %h heures %i minutes');
          }
          else{
            $time = $interval->format('Il y a %i minutes');
          }
        }
        $artTag[$k]["createdcomment"]=$time;

        
      }
      $artTag[$k]["usercomment"] = (!empty($comments->firstname))?ucwords(strtolower($comments->firstname.' '.$comments->lastname)):ucwords(strtolower($comments->username));
      
      $artTag[$k]["reponses"] = count($commentsC);
      $views = KozonStorage::selectCounterKForum($article->nid);
      $artTag[$k]["views"] = $views->counter;

	    $imageuser = File::load( $article->fid );
	    if($imageuser){
	    	$artTag[$k]["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
	    } 
      else{
        $artTag[$k]["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
      }
	    $ccid = KozonStorage::getCommentPosteCById($article->nid);
	    $artTag[$k]["comment_count"] = $ccid->ccid;

      $date2->setTimestamp($article->created);
      $interval = $date2->diff($date1);
      $time = '';
      if($interval->days > 0){
        $time = $interval->format('Il y a %a jours');
      }
      else{
        if($interval->h > 0){
          $time = $interval->format('Il y a %h heures %i minutes');
        }
        else{
          $time = $interval->format('Il y a %i minutes');
        }
      }

     $artTag[$k]["created"]=$time;
    	$k++;
    }
  	$rawdata["taxarticle"] = $artTag;

    if($k < $taxArticlesAll->cnid){
      $rawdata["taxarticlemore"] = 1;
    }
    else{
      $rawdata["taxarticlemore"] = 0;
    }
    $mobileDetector = \Drupal::service('mobile_detect');
    $is_mobile = $mobileDetector->isMobile();
    $rawdata["is_mobile"] = $is_mobile;

    return array(
      //Your theme hook name
      '#theme' => 'kozon_cat_theme_hook',      
      //Your variables
      '#items' => $rawdata,
      '#title' => 'Kozon Term',
      '#attached' => ['library' => ['core/drupal.ajax','kozon/kozon-query']],
      '#cache' => array('max-age' => 0),
    );
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function contentTermJs() {
    $taxid = $_POST['tid'];
    $page = $_POST['page'];
    $mobileDetector = \Drupal::service('mobile_detect');
    $is_mobile = $mobileDetector->isMobile();
    $term = Term::load($taxid);
    $rawdata =[];
    if($term){
      $rawdata["taxonomy"] = $term->getName();
      $rawdata["tid"] = $taxid;
      $color = KozonStorage::getTaxForumColor($taxid);
      $rawdata["color"] = $color->color;
    }
    $pagination = $page*10;
    $taxArticles = KozonStorage::getMessagePosteByTaxId($taxid,$pagination);
    $taxArticlesAll = KozonStorage::getCMessagePosteByTaxId($taxid);
    
    $k=0;
    $artTag = [];
    $html='';
    foreach ($taxArticles as $article) {
      $artTag[$k]['title'] = $article->title;
      $artTag[$k]['nid'] = $article->nid;
      $artTag[$k]["user"] = (!empty($article->firstname))?ucwords(strtolower($article->firstname.' '.$article->lastname)):ucwords(strtolower($article->username));
      $commentsC = KozonStorage::getCommentCountPosteById($article->nid);
      $comments = KozonStorage::getCommentGetPosteById($article->nid);

      $date1 = new \DateTime("now");
      $date2 = new \DateTime();
      $time = '';
      if(!empty($comments)){
        $date2->setTimestamp($comments->datechange);
        $interval = $date2->diff($date1);
        
        if($interval->days > 0){
          $time = $interval->format('Il y a %a jours');
        }
        else{
          if($interval->h > 0){
            $time = $interval->format('Il y a %h heures %i minutes');
          }
          else{
            $time = $interval->format('Il y a %i minutes');
          }
        }
        $artTag[$k]["createdcomment"]=$time;

        
      }
      $artTag[$k]["usercomment"] = (!empty($comments->firstname))?ucwords(strtolower($comments->firstname.' '.$comments->lastname)):ucwords(strtolower($comments->username));
      
      $artTag[$k]["reponses"] = count($commentsC);
      $views = KozonStorage::selectCounterKForum($article->nid);
      $artTag[$k]["views"] = $views->counter;

      $imageuser = File::load( $article->fid );
      if($imageuser){
        $artTag[$k]["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
      } 
      else{
        $artTag[$k]["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
      }
      $ccid = KozonStorage::getCommentPosteCById($article->nid);
      $artTag[$k]["comment_count"] = $ccid->ccid;
      $date1 = new \DateTime("now");
      $date2 = new \DateTime();

      $date2->setTimestamp($article->created);
      $interval = $date2->diff($date1);
      $time = '';
      if($interval->days > 0){
        $time = $interval->format('Il y a %a jours');
      }
      else{
        if($interval->h > 0){
          $time = $interval->format('Il y a %h heures %i minutes');
        }
        else{
          $time = $interval->format('Il y a %i minutes');
        }
      }

      $artTag[$k]["created"]=$time;
      
      if(!$is_mobile){
      $html.='<hr class="line-kcat">
        <div class="row kcat-list" style="margin-left: 10px; margin-top: 15px; margin-bottom: 15px;">
            <div class="col-sm-5 k-name-sub">
              <a href="/kozon/'.$artTag[$k]['tid'].'/'.$artTag[$k]['nid'].'">
                <div class="header-post">
                  <div class="view-field forum-username">'.$artTag[$k]['title'].' 
                    <span class="view-field forum-name-sub">Par '.$artTag[$k]['user'].'</span>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-sm-2 k-post-sub">
              '.$artTag[$k]['reponses'].'
            </div>
            <div class="col-sm-1 k-comm-sub">
            '.$artTag[$k]['views'].'
            </div>
            <div class="col-sm-4 k-user-sub k-last">
            '.$artTag[$k]['usercomment'].' <span class="forum-cat-time-sub">'.$artTag[$k]['createdcomment'].'</span>
            </div>
          </div>';
      }
      else{
        $html.='
        <div class="row kcat-list" style="margin-left: 10px; margin-top: 15px; margin-bottom: 15px;">
            <div class="col-sm-5 k-name-sub">
              <a href="/kozon/'.$artTag[$k]['tid'].'/'.$artTag[$k]['nid'].'">
                <div class="header-post">
                  <div class="view-field forum-username">'.$artTag[$k]['title'].' 
                  </div>
                  <span class="view-field forum-name-sub">Par '.$artTag[$k]['user'].'</span>
                </div>
              </a>
            </div>
          </div>';
      }
      $k++;
    }
    $rawdata["taxarticle"] = $artTag;

    if(($pagination+$k) < $taxArticlesAll->cnid){
      $page = $page+1;
      $html.='<div class="k-button more-sub-articles" page="'.$page.'" tid="'.$taxid.'"><a class="more-forum-categories" >VOIR PLUS</a></div>';
    }

    $data['#markup'] =t("$html");

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.forum-categories-sub', $html));
    return $response;
  }

  public function FileSizeConvert($bytes)
  {
      $bytes = floatval($bytes);
          $arBytes = array(
              0 => array(
                  "UNIT" => "TB",
                  "VALUE" => pow(1024, 4)
              ),
              1 => array(
                  "UNIT" => "GB",
                  "VALUE" => pow(1024, 3)
              ),
              2 => array(
                  "UNIT" => "MB",
                  "VALUE" => pow(1024, 2)
              ),
              3 => array(
                  "UNIT" => "KB",
                  "VALUE" => 1024
              ),
              4 => array(
                  "UNIT" => "B",
                  "VALUE" => 1
              ),
          );

      foreach($arBytes as $arItem)
      {
          if($bytes >= $arItem["VALUE"])
          {
              $result = $bytes / $arItem["VALUE"];
              $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
              break;
          }
      }
      return $result;
  }
	/**
   * Returns a render-able array for a test page.
   */
  public function content($taxid, NodeInterface $node) {
  //Do something with your variables here.

    $userid = \Drupal::currentUser()->id();
    $nodeId = $node->id();
    $rawdata =[];
    KozonStorage::addCounterK($userid,$nodeId);

    $forum = KozonStorage::getMessagePosteById($nodeId);
    $taxArticles = KozonStorage::getMessagePosteByTaxId($forum->tid);
    // Data to be passed in the url.
	  $likes = KozonStorage::getForumLike($nodeId);
	  $ban = 0;
    $likec = json_decode($likes->field_like_clicked_by);
    if(!empty($likec)){
      foreach($likec as $key => $value) {
          if($key == $userid){
            $ban = 1;
          }
      }
    }

	  $initial_data = [
	    'entity_type' => 'node',
	    'entity_id' => $nodeId,
	    'field_name' => 'field_like',
	    'likes' => (isset($likes->field_like_likes))?$likes->field_like_likes:'0',
	    'dislikes' => '0',
	    'kozon' => '1'
	  ];

	  $data = base64_encode(json_encode($initial_data));
	  $like_url = Url::fromRoute(
	    'like_kozon.manager', ['clicked' => 'like', 'data' => $data]
	  )->toString();
	  $destination = '';
	    if ($userid == 0) {
	      $destination = '?like-dislike-redirect=' . \Drupal::request()->getRequestUri();
	    }
	  $rawdata["like_url"] = $like_url . $destination;
	  $rawdata["likes"] = $likes->field_like_likes;
	  $rawdata["lidis_id"] = $nodeId;
	  $rawdata["like_band"] = $ban;

    $link_url = Url::fromRoute('kozon.add.comment',['arg'=>$nodeId]);
    $link_url->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small'],
      ]
    ]);

    $rawdata["comment_add"] = Link::fromTextAndUrl(t('RÉPONDRE'), $link_url)->toString();

    $link_edit = Url::fromRoute('kozon.edit.forum',['arg'=>$nodeId]);
    $link_edit->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 400]),
      ]
    ]);
    $user = user_load($userid);
    $roles = $user->getRoles();
    if($userid == $forum->uid)
    {
      $rawdata["forum_edit"] = Link::fromTextAndUrl(t('MODIFIER'), $link_edit)->toString();
    }
    else{
        $rawdata["forum_edit"] = '';
    }
    
    $countc = KozonStorage::getCommentPosteCById($nodeId);
    $rawdata['ccount']=$countc->ccid;

    $views = KozonStorage::selectCounterKForum($nodeId);
    $rawdata['counter']=$views->counter;
    
    $k=0;
    $artTag = [];
    foreach ($taxArticles as $article) {
    	if($k<3){
	    	$artTag[$k]['title'] = $article->title;
	    	$artTag[$k]['nid'] = $article->nid;
	    }
    	$k++;
    }
    $rawdata["taxarticle"] = $artTag;  
    $rawdata["taxarticlecount"] = $k;  

    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('forums');
    $term_data = [];
    $tc=0;
    foreach ($terms as $term) {
      if($tc < 4){
        $enableKozon = KozonStorage::getTaxForumEnableb($term->tid);
        if(!empty($enableKozon->value) && $enableKozon->value == 1){
          $color = KozonStorage::getTaxForumColor($term->tid);
          $term_data[] = array(
          "name" => $term->name,
          "tid" => $term->tid,
          "color" => $color->color,);
        }
      }
      $tc++;
    }
    $rawdata["taxterms"] = $term_data;  
    $rawdata["taxtermscount"] = $tc;  
	  

    $rawdata["title"] = $node->getTitle();
    $rawdata["body"] = str_replace('&nbsp;', '', $node->get('body')->value);

    $image = File::load( $node->get('field_forum_image')->target_id );
    if($image){
    	$rawdata["image"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($image->getFileUri()))->toString()); 
      $rawdata["image_name"] = $image->getFilename(); 
    } 

    $mobileDetector = \Drupal::service('mobile_detect');
    $is_mobile = $mobileDetector->isMobile();
    $rawdata["is_mobile"] = $is_mobile;

    $term = Term::load($forum->tid);
    if($term){
      $rawdata["taxonomy"] = $term->getName();
      $rawdata["tid"] = $forum->tid;
    }

    $rawdata["user"] = (!empty($forum->firstname))?ucwords(strtolower($forum->firstname.' '.$forum->lastname)):ucwords(strtolower($forum->username));

    $imageuser = File::load( $forum->fid );
    if($imageuser){
    	$rawdata["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
    } 
    else{
      $rawdata["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
    }

    $fileforum = File::load( $node->get('field_file')->target_id );
    if($fileforum){
    	$rawdata["fileforum"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($fileforum->getFileUri()))->toString());  
      $rawdata["fileforum_name"] = $fileforum->getFilename();  
      $rawdata["fileforum_size"] = $this->FileSizeConvert($fileforum->getSize());
      $urlParts = pathinfo($fileforum->getFilename());
      $extension = strtolower($urlParts["extension"]);
      $rawdata["fileforum_type"] = 'other';
      if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'ico' || $extension == 'svg' ){
        $rawdata["fileforum_type"] = 'image';
      }
    }
    
    //$rawdata["date"]=date('j F Y \á H:i', $forum->created);

    $date1 = new \DateTime("now");
    $date2 = new \DateTime();

    $date2->setTimestamp($forum->created);
    $interval = $date2->diff($date1);
    $time = '';
    if($interval->days > 0){
      $time = $interval->format('Il y a %a jours');
    }
    else{
      if($interval->h > 0){
        $time = $interval->format('Il y a %h heures %i minutes');
      }
      else{
        $time = $interval->format('Il y a %i minutes');
      }
    }

     $rawdata["date"]=$time;

    $comments = KozonStorage::getCommentPosteById($nodeId);
    $rawcomment = [];
    $i=0;
    foreach ($comments as $comment) {
    	$rawcomment[$i]['cid']=$comment->cid;
    	//$rawcomment[$i]['datechange']=date('j F Y \á H:i', $comment->datechange);
      $date1 = new \DateTime("now");
      $date2 = new \DateTime();

      $date2->setTimestamp($comment->datechange);
      $interval = $date2->diff($date1);
      $time = '';
      if($interval->days > 0){
        $time = $interval->format('Il y a %a jours');
      }
      else{
        if($interval->h > 0){
          $time = $interval->format('Il y a %h heures %i minutes');
        }
        else{
          $time = $interval->format('Il y a %i minutes');
        }
      }

      $rawcomment[$i]['datechange']=$time;
    	$rawcomment[$i]['body']=str_replace('&nbsp;', '', $comment->body);
    	$rawcomment[$i]['subject']=$comment->subject;
    	$rawcomment[$i]['user']=(!empty($comment->firstname))?ucwords(strtolower($comment->firstname.' '.$comment->lastname)):ucwords(strtolower($comment->username));
    	$imageuser = File::load( $comment->fid );
	    if($imageuser){
	    	$rawcomment[$i]["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
	    } 
      else{
        $rawcomment[$i]["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
      }
	    $filecomment= File::load( $comment->fileid );
	    if($filecomment){
	    	$rawcomment[$i]["filecomment"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($filecomment->getFileUri()))->toString());  
	    }
	    $rawcomment[$i]['pid']=$comment->pid;
	    $rawcomment[$i]['cid']=$comment->cid;
	    $commentChilds = KozonStorage::getCommentChildPosteById($comment->cid);
      $link_url = Url::fromRoute('kozon.add.comment_parent',['arg'=>$comment->cid]);
      $link_url->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);

      $link_like = Url::fromRoute('kozon.add.comment_like',['arg'=>$comment->cid]);
      $link_like->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);

      $link_editc = Url::fromRoute('kozon.edit.comment',['arg'=>$comment->cid, 'arg1'=>$nodeId]);
      $link_editc->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--smal edit-kozon-c'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(['width' => 400]),
        ]
      ]);
      
      if($userid == $comment->uid)
      {
        $rawcomment[$i]["comment_edit"] = Link::fromTextAndUrl(t('MODIFIER'), $link_editc)->toString();
      }
      else{
        /*if(in_array("administrator", $roles)){
         $rawcomment[$i]["comment_edit"] = Link::fromTextAndUrl(t('MODIFIER'), $link_editc)->toString();
        }
        else{
          $rawcomment[$i]["comment_edit"] = '';
        }*/
        $rawcomment[$i]["comment_edit"] = '';
      }
      
      $like_count = KozonStorage::selectLikeCommentC($comment->cid);
      $like_user = KozonStorage::selectLikeComment($userid, $comment->cid);
      $rawcomment[$i]['likecount']=$like_count->cclid;
      $rawcomment[$i]['likeuser']=!empty($like_user->clid)?$like_user->clid:'';
      $rawcomment[$i]['likelink']=Link::fromTextAndUrl(t('<i class="fas fa-heart"></i>'), $link_like)->toString();
      $rawcomment[$i]["comment_add"] = Link::fromTextAndUrl(t('RÉPONDRE'), $link_url)->toString();
	    $j=0;
	    $rawcchild = [];
	    foreach ($commentChilds as $value) {
	    	$rawcchild[$j]['cid']=$value->cid;
	    	//$rawcchild[$j]['datechange']=date('j F Y \á H:i', $value->datechange);
        $date1 = new \DateTime("now");
        $date2 = new \DateTime();

        $date2->setTimestamp($value->datechange);
        $interval = $date2->diff($date1);
        $time = '';
        if($interval->days > 0){
          $time = $interval->format('Il y a %a jours');
        }
        else{
          if($interval->h > 0){
            $time = $interval->format('Il y a %h heures %i minutes');
          }
          else{
            $time = $interval->format('Il y a %i minutes');
          }
        }

        $rawcchild[$j]['datechange']=$time;
	    	$rawcchild[$j]['body']=str_replace('&nbsp;', '', $value->body);
	    	$rawcchild[$j]['subject']=$value->subject;
	    	$rawcchild[$j]['user']=(!empty($value->firstname))?ucwords(strtolower($value->firstname.' '.$value->lastname)):ucwords(strtolower($value->username));
	    	$imageuser = File::load( $value->fid );
		    if($imageuser){
		    	$rawcchild[$j]["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
		    } 
        else{
          $rawcchild[$j]["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
        }
		    $filecomment= File::load( $value->fileid );
		    if($filecomment){
		    	$rawcchild[$j]["filecomment"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($filecomment->getFileUri()))->toString());  
		    }
		    $rawcchild[$j]['pid']=$value->pid;
		    $rawcchild[$j]['cid']=$value->cid;
        $link_editcc = Url::fromRoute('kozon.edit.comment',['arg'=>$value->cid, 'arg1'=>$nodeId]);
        $link_editcc->setOptions([
          'attributes' => [
            'class' => ['use-ajax', 'button', 'button--smal edit-kozon-c'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode(['width' => 400]),
          ]
        ]);
        $rawcchild[$j]["comment_edit"] = Link::fromTextAndUrl(t('MODIFIER'), $link_editcc)->toString();
        $link_urlcc = Url::fromRoute('kozon.add.comment_grandparent',['arg'=>$value->cid]);
        $link_urlcc->setOptions([
          'attributes' => [
            'class' => ['use-ajax', 'button', 'button--small'],
          ]
        ]);
        //$rawcchild[$j]["comment_add"] = Link::fromTextAndUrl(t('RÉPONDRE'), $link_urlcc)->toString();

        $l=0;
        $rawcchildg = [];
        $commentChildsg = KozonStorage::getCommentChildPosteById($value->cid);
        foreach ($commentChildsg as $valueg) {
          $rawcchildg[$l]['cid']=$valueg->cid;
          //$rawcchildg[$l]['datechange']=date('j F Y \á H:i', $valueg->datechange);
          $date1 = new \DateTime("now");
          $date2 = new \DateTime();

          $date2->setTimestamp($valueg->datechange);
          $interval = $date2->diff($date1);
          $time = '';
          if($interval->days > 0){
            $time = $interval->format('Il y a %a jours');
          }
          else{
            if($interval->h > 0){
              $time = $interval->format('Il y a %h heures %i minutes');
            }
            else{
              $time = $interval->format('Il y a %i minutes');
            }
          }

          $rawcchildg[$l]['datechange']=$time;
          $rawcchildg[$l]['body']=str_replace('&nbsp;', '', $valueg->body);
          $rawcchildg[$l]['subject']=$valueg->subject;
          $rawcchildg[$l]['user']=$valueg->username;
          $imageuser = File::load( $valueg->fid );
          if($imageuser){
            $rawcchildg[$l]["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
          } 
          else{
            $rawcchildg[$l]["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
          }
          $filecomment= File::load( $valueg->fileid );
          if($filecomment){
            $rawcchildg[$l]["filecomment"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($filecomment->getFileUri()))->toString());  
          }
          $rawcchildg[$l]['pid']=$valueg->pid;
          $rawcchildg[$l]['cid']=$valueg->cid;
          $link_editccg = Url::fromRoute('kozon.edit.comment',['arg'=>$valueg->cid, 'arg1'=>$nodeId]);
          $link_editccg->setOptions([
            'attributes' => [
              'class' => ['use-ajax', 'button', 'button--smal edit-kozon-c'],
              'data-dialog-type' => 'modal',
              'data-dialog-options' => Json::encode(['width' => 400]),
            ]
          ]);
          $rawcchildg[$l]["comment_edit"] = Link::fromTextAndUrl(t('MODIFIER'), $link_editccg)->toString();

          $l++;
        }
        //$rawcchild[$j]['grandchildren']=$rawcchildg;
        $j++;

	    }
	    $rawcomment[$i]['children']=$rawcchild;
    	$i++;
    }
    $rawdata["comment"]=$rawcomment;

    $link_tag = Url::fromRoute('kozon.add.tag',['arg'=>$nodeId,'arg1'=>$taxid]);
    $link_tag->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small'],
      ]
    ]);

    $tagUser = KozonStorage::selectTag($userid, $nodeId, $taxid);
    if(empty($tagUser)){
      $rawdata["post_tag"] = Link::fromTextAndUrl(t('<div class="bookmark-tag tag-kozon-'.$nodeId.' post-tag" aria-hidden="true"></div>'), $link_tag)->toString();
    }
    else{
      $rawdata["post_tag"] = Link::fromTextAndUrl(t('<div class="bookmark-tag tag-kozon-'.$nodeId.' post-tag post-active-tag" aria-hidden="true"></div>'), $link_tag)->toString();
    }

    //kint($rawdata);

    return array(
      //Your theme hook name
      '#theme' => 'kozon_theme_hook',      
      //Your variables
      '#items' => $rawdata,
      '#title' => 'Kozon',
      '#attached' => ['library' => ['core/drupal.ajax','kozon/kozon-query','core/drupal.dialog.ajax']],
      '#cache' => array('max-age' => 0),
    );
  }

  /**
        * Add a comment.
        */
  public function addCommentlikeJS($arg){
    $uid = \Drupal::currentUser()->id();
    $response = new AjaxResponse();
    KozonStorage::addLikeComment($uid, $arg);
    $html = KozonStorage::selectLikeCommentC($arg);
    
    $return = $response->addCommand(
        new HtmlCommand('#like_comment_'.$arg, $html->cclid)
      );
    return $return; 
  }

  /**
        * Add a comment.
        */
  public function addCommentJS($arg){
    $response = new AjaxResponse();
    $form = \Drupal::formBuilder()->getForm('Drupal\kozon\Form\AddCommentForm',$arg);
    $return = $response->addCommand(
        new HtmlCommand('.display-comment', $form)
      );
    return $return; 
  }

  /**
        * Save a comment.
        */
  public function saveCommentJS(){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }
    
    $postid = $_POST['postid'];
    $forum = KozonStorage::getMessagePosteById($postid);

    $body = $_POST['body'];
    $fileforum = $_POST['fileforum'];
    $response = new AjaxResponse();
    if(!empty($_POST['body']['value'])){
      KozonStorage::addComment($postid, $body, $fileforum);
      $return = $response->addCommand(
         new RedirectCommand('/kozon/'.$forum->tid.'/'.$postid)
        );
    }
    else{
      $return = $response;
    }
    return $return; 
  }

  /**
        * Save a comment.
        */
  public function updateCommentJS(){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }
    
    $postid = $_POST['postid'];
    $cid = $_POST['cid'];
    $forum = KozonStorage::getMessagePosteById($postid);

    $body = $_POST['body'];
    $fileforum = $_POST['fileforum'];

    $response = new AjaxResponse();
    if(!empty($_POST['body']['value'])){
      KozonStorage::updateComment($postid, $cid, $body, $fileforum);
      $return = $response->addCommand(
         new RedirectCommand('/kozon/'.$forum->tid.'/'.$postid)
        );
    }else{
      $return = $response;
    }
    return $return; 
  }

  /**
        * hide a comment form.
        */
  public function hideCommentJS(){
    $response = new AjaxResponse();
    $return = $response->addCommand(
        new HtmlCommand('.display-comment', '')
      );
    return $return; 
  }

  /**
        * Add a comment.
        */
  public function addCommentParentJS($arg){
    $response = new AjaxResponse();
    $form = \Drupal::formBuilder()->getForm('Drupal\kozon\Form\AddCommentChildForm',$arg);
    $return = $response->addCommand(
        new HtmlCommand('.display-comment-parent-'.$arg, $form)
      );
    return $return; 
  }

  /**
        * Save a comment.
        */
  public function saveCommentParentJS(){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }
    
    $compid = $_POST['compid'];
    $forum = KozonStorage::getCommentChildParentById($compid);
    $postid = $forum->nid;

    $body = $_POST['body'];
    $fileforum = $_POST['fileforum'];
    $response = new AjaxResponse();
    if(!empty($_POST['body']['value'])){
      KozonStorage::addCommentChild($postid, $body, $fileforum, $compid);
      $response = new AjaxResponse();
      $return = $response->addCommand(
         new RedirectCommand('/kozon/'.$forum->tid.'/'.$postid)
        );
    }
    else{
      $return = $response;
    }
    return $return; 
  }

  /**
        * hide a comment form.
        */
  public function hideCommentParentJS($arg){
    $response = new AjaxResponse();
    $return = $response->addCommand(
        new HtmlCommand('.display-comment-parent-'.$arg, '')
      );
    return $return; 
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function contentAllTags() {
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('forums');
    foreach ($terms as $term) {
      $aliasManager = \Drupal::service('path.alias_manager');
      $alias = $aliasManager->getAliasByPath('/taxonomy/term/'.$term->tid);
      $msgPst = HeroStorage::getForumPostebyTid($term->tid);
      $msgCom = HeroStorage::getForumCommentbyTid($term->tid);
      $usPost = HeroStorage::getLastMessagePosteByTid($term->tid);
      $userName = (!empty($usPost->firstname))?ucwords(strtolower($usPost->firstname.' '.$usPost->lastname)):(!empty($usPost->username)?ucwords(strtolower($usPost->username)):'');
        $term_object = taxonomy_term_load($term->tid);
      $color = KozonStorage::getTaxForumColor($term->tid);

      $taxArticles = KozonStorage::getMessagePosteByTaxId($term->tid);

      $commetCount = 0;
      $lastcomemnt = 0;
      foreach ($taxArticles as $article) {
        $commentsC = KozonStorage::getCommentCountPosteById($article->nid);
        $commetCount += count($commentsC);
        
        $lastC = KozonStorage::getCommentLastPosteById($article->nid);
        if($lastC->cid !== null){
          if($lastcomemnt < $lastC->cid){
            $lastcomemnt = $lastC->cid;
          }
        }

      }

      $comments = KozonStorage::getCommentPosteByCommentId($lastcomemnt);

      $date1 = new \DateTime("now");
      $date2 = new \DateTime();
      $timecomment = '';
      if(!empty($comments)){
        $date2->setTimestamp($comments->datechange);
        $interval = $date2->diff($date1);
        
        if($interval->days > 0){
          $timecomment = $interval->format('Il y a %a jours');
        }
        else{
          if($interval->h > 0){
            $timecomment = $interval->format('Il y a %h heures %i minutes');
          }
          else{
            $timecomment = $interval->format('Il y a %i minutes');
          }
        }
        
      }
      $usercomment = (!empty($comments->firstname))?ucwords(strtolower($comments->firstname.' '.$comments->lastname)):ucwords(strtolower($comments->username));
      
      if($msgPst->cpost>0){
        $date1 = new \DateTime("now");
        $date2 = new \DateTime();
        $date2->setTimestamp($usPost->created);
        $interval = $date2->diff($date1);
        $time = '';
        if($interval->days > 0){
          $time = $interval->format('Il y a %a jours');
        }
        else{
          if($interval->h > 0){
            $time = $interval->format('Il y a %h heures %i minutes');
          }
          else{
            $time = $interval->format('Il y a %i minutes');
          }
        }
      }
      else{
        $userName = '';
        $time = '';
      }
      $enableKozon = KozonStorage::getTaxForumEnableb($term->tid);
      if(!empty($enableKozon->value) && $enableKozon->value == 1){
        $term_data[] = array(
        "alias" => $alias,
        "name" => $term->name,
        "post" => $msgPst->cpost,
        "comm" => $commetCount,
        "user" => $userName,
        "time" => $time,
        "tid" => $term->tid,
        "color" => $color->color,
        "usercomment" => $usercomment,
        "timecomment" => $timecomment,
      );
      }

    }

    return array(
      //Your theme hook name
      '#theme' => 'kozon_all_cat_theme_hook',      
      //Your variables
      '#items' => $term_data,
      '#title' => 'Kozon catégories',
      '#attached' => ['library' => ['core/drupal.ajax','kozon/kozon-query']],
      '#cache' => array('max-age' => 0),
    );
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function contentAllSubjects() {
    $msgPst = KozonStorage::getAllMessagePoste();
    foreach ($msgPst as $msg) {
      $aliasManager = \Drupal::service('path.alias_manager');
      $alias = $aliasManager->getAliasByPath('/node/'.$msg->nid);
      $term = Term::load($msg->tid);
      if($term){
        $termName = $term->getName();
      }
      else{
        $termName = '';
      }

      $date1 = new \DateTime("now");
      $date2 = new \DateTime();

      $date2->setTimestamp($msg->created);
      $interval = $date2->diff($date1);
      $time = '';
      if($interval->days > 0){
        $time = $interval->format('Il y a %a jours');
      }
      else{
        if($interval->h > 0){
          $time = $interval->format('Il y a %h heures %i minutes');
        }
        else{
          $time = $interval->format('Il y a %i minutes');
        }
      }

      $file = File::load($msg->fid);
      $path = '';
      if($file){
        $url_image = Url::fromUri(file_create_url($file->getFileUri()))->toString();
        $path=file_url_transform_relative($url_image);
      }
      else{
        $path = '/themes/keonoo/images/nouserimage7.png';
      }
      $color = KozonStorage::getTaxForumColor($msg->tid);
      $term_data[] = array(
      "alias" => $alias,
      "name" => $msg->title,
      "term" => $termName,
      "pathImage" => $path,
      "user" => (!empty($msg->firstname))?ucwords(strtolower($msg->firstname.' '.$msg->lastname)):ucwords(strtolower($msg->username)),
      "time" => $time,
      "tid" => $msg->tid,
      "nid" => $msg->nid,
      "color" => $color->color,
      );
    } 

    return array(
      //Your theme hook name
      '#theme' => 'kozon_all_forums_theme_hook',      
      //Your variables
      '#items' => $term_data,
      '#title' => 'Kozon Subjects',
      '#attached' => ['library' => ['core/drupal.ajax','kozon/kozon-query']],
      '#cache' => array('max-age' => 0),
    );
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function contentMySubjects() {
    $account = \Drupal::currentUser(); 
    $uid = $account->id();
    $msgPst = KozonStorage::getMyMessagePoste($uid);
    $term_data =[];
    $rawdata =[];
    $totpostes = KozonStorage::getMessagePoste($uid);
    $i=0;
    $mobileDetector = \Drupal::service('mobile_detect');
    $is_mobile = $mobileDetector->isMobile();
    $html='<div class="forum-posts myforums mpostes">
  <div class="row mpostesh">
    <div class="col-12">
      <p class="mf-title">Mes messages postés ('.$totpostes->cnid.')</p>
    </div>
  </div>
  <div class="post-kozon-main">';
    foreach ($msgPst as $msg) {
      
      $nodeId = $msg->nid;

      $node = node_load($msg->nid);
      $forum = KozonStorage::getMessagePosteById($nodeId);
      $taxArticles = KozonStorage::getMessagePosteByTaxId($forum->tid);
      // Data to be passed in the url.
      $likes = KozonStorage::getForumLike($nodeId);

      $rawdata[$i]["likes"] = $likes->field_like_likes;
      $rawdata[$i]["lidis_id"] = $nodeId;
      
      $k=0;
      $artTag = [];
      foreach ($taxArticles as $article) {
        if($k<3){
          $artTag[$k]['title'] = $article->title;
          $artTag[$k]['nid'] = $article->nid;
        }
        $k++;
      }

      $rawdata[$i]["taxarticle"] = $artTag;  
      $rawdata[$i]["taxarticlecount"] = $k;    

      $rawdata[$i]["title"] = $node->getTitle();
      $rawdata[$i]["body"] = str_replace('&nbsp;', '', $node->get('body')->value);

      $image = File::load( $node->get('field_forum_image')->target_id );
      if($image){
        $rawdata[$i]["image"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($image->getFileUri()))->toString());  
        $rawdata[$i]["image_name"] = $image->getFilename();
      } 

      $term = Term::load($forum->tid);
      if($term){
        $rawdata[$i]["taxonomy"] = $term->getName();
        $rawdata[$i]["tid"] = $forum->tid;
      }

      $rawdata[$i]["user"] = (!empty($forum->firstname))?ucwords(strtolower($forum->firstname.' '.$forum->lastname)):ucwords(strtolower($forum->username));

      $imageuser = File::load( $forum->fid );
      if($imageuser){
        $rawdata[$i]["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
      }
      else{
        $rawdata[$i]["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
      } 

      $fileforum = File::load( $node->get('field_file')->target_id );
      if($fileforum){
        $rawdata[$i]["fileforum"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($fileforum->getFileUri()))->toString());  
        $rawdata[$i]["fileforum_name"] = $fileforum->getFilename();  
        $rawdata[$i]["fileforum_size"] = $this->FileSizeConvert($fileforum->getSize());
      }
      
      $rawdata[$i]["date"]=date('j F Y \à H:i', $forum->created);

      $rawdata[$i]["nid"]=$msg->nid;
      $countc = KozonStorage::getCommentPosteCById($msg->nid);
      $rawdata[$i]['ccount']=$countc->ccid;

      $views = KozonStorage::selectCounterKForum($msg->nid);
      $rawdata[$i]['counter']=(!empty($views->counter))?$views->counter:'0';

      $link_url = Url::fromRoute('kozon.delete.forum',['arg'=>$nodeId]);
      $link_url->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);

      $link_url_no = Url::fromRoute('kozon.delete.forum.no',['arg'=>$nodeId]);
      $link_url_no->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);

      $link_url_yes = Url::fromRoute('kozon.delete.forum.yes',['arg'=>$nodeId]);
      $link_url_yes->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);

      if(!empty($rawdata[$i]['tid'])){
        $link_url_non = Url::fromRoute('kozon.delete.forum.non',['arg'=>$nodeId,'arg1'=>$rawdata[$i]['tid']]);
        $link_url_non->setOptions([
          'attributes' => [
            'class' => ['use-ajax', 'button', 'button--small'],
          ]
        ]);
      }
      else{
        $link_url_non = Url::fromRoute('kozon.delete.forum.no',['arg'=>$nodeId]);
        $link_url_non->setOptions([
          'attributes' => [
            'class' => ['use-ajax', 'button', 'button--small'],
          ]
        ]);
      }

      $html.='
      <div class="row post-kozon">
        <div class="col-12">
          <div class="header-post">
            <div class="view-field ktitle-response"><a href="/kozon/'.$rawdata[$i]['tid'].'/'.$rawdata[$i]['nid'].'">'.$rawdata[$i]['title'].'</a></div>
            <div class="kozonbookmark"></div>
            <a href="/kozon/'.$rawdata[$i]['tid'].'"><div class="view-field kcat-response">'.$rawdata[$i]['taxonomy'].'</div></a>
          </div>
          <div class="content-post">';
            if(!empty($rawdata[$i]['imageuser'])){
                $html.='<img src="'.$rawdata[$i]['imageuser'].'" class="kfoto-forum my-auto">';
            }
            $html.='<div class="view-field forum-username">'.$rawdata[$i]['user'].'</div>
            <div class="view-field forum-time">'.$rawdata[$i]['date'].'</div>
            <div class="view-field forum-body">'.strip_tags($rawdata[$i]['body']).'</div>';
            if(!empty($rawdata[$i]['image'])){
            $html.='<div class="forum-filer">
                <div class="dfimage"></div><a href="'.$rawdata[$i]['image'].'" class="nameImagef">'.$rawdata[$i]['image_name'].'<a>
              </div>
                <!--<img src="'.$rawdata[$i]['image'].'" class="dfimage">-->';
            }
            if(!empty($rawdata[$i]['fileforum'])){
              $html.='<div class="dfimage"></div><a href="'.$rawdata[$i]['fileforum'].'">'.$rawdata[$i]['fileforum_name'].' ('.$rawdata[$i]['fileforum_size'].')</a>';
            }
          $html.='</div>';
          if(!$is_mobile){
                $html.='<div class="links-kozon like-kozon">
                <div class="link-kozon-redirect order-0"><a href="/kozon/'.$rawdata[$i]['tid'].'/'.$rawdata[$i]['nid'].'" class="mypost-resp">RÉPONDRE</a></div>
                    <div class="like_dislike">
                    <span><i class="fas fa-heart"></i> </span>
                    <span id="like_'.$rawdata[$i]['lidis_id'].'">'.$rawdata[$i]['likes'].'</span>
                  </div>
                  <div id="like_dislike_status_'.$rawdata[$i]['lidis_id'].'"></div>
                     
                  <div class="delete-forum">'.Link::fromTextAndUrl(t('SUPPRIMER'), $link_url)->toString().'</div><div class="fade-kozon"></div><div class="delete-forum-'.$rawdata[$i]['lidis_id'].' forum-dis-non"> '.Link::fromTextAndUrl(t('X'), $link_url_no)->toString().'<span>Etes-vous sûr(e) de vouloir supprimer votre contenu?</span><div class="sup-btn-yes">'.Link::fromTextAndUrl(t('OUI'), $link_url_yes)->toString().'</div><div class="sup-btn-no">'.Link::fromTextAndUrl(t('NON'), $link_url_non)->toString().'</div></div>
                  <div class="kozon-info-post">'.$rawdata[$i]['ccount'].' RÉPONSES | '.$rawdata[$i]['counter'].' VUES</div>
                  </div>

                  
              </div>
              </div>
            ';
          }
          else{

          $html.='<div class="links-kozon like-kozon">
              
                  <div class="like_dislike">
                  <span><i class="fas fa-heart"></i> </span>
                  <span id="like_'.$rawdata[$i]['lidis_id'].'">'.$rawdata[$i]['likes'].'</span>
                </div>
                <div id="like_dislike_status_'.$rawdata[$i]['lidis_id'].'"></div>
                   
                <div class="delete-forum">'.Link::fromTextAndUrl(t('SUPPRIMER'), $link_url)->toString().'</div><div class="fade-kozon"></div><div class="delete-forum-'.$rawdata[$i]['lidis_id'].' forum-dis-non"> '.Link::fromTextAndUrl(t('X'), $link_url_no)->toString().'<span>Etes-vous sûr(e) de vouloir supprimer votre contenu?</span><div class="sup-btn-yes">'.Link::fromTextAndUrl(t('OUI'), $link_url_yes)->toString().'</div><div class="sup-btn-no">'.Link::fromTextAndUrl(t('NON'), $link_url_non)->toString().'</div></div>
                <div class="link-kozon-redirect order-0"><a href="/kozon/'.$rawdata[$i]['tid'].'/'.$rawdata[$i]['nid'].'" class="mypost-resp">RÉPONDRE</a></div>
                <div class="kozon-info-post">'.$rawdata[$i]['ccount'].' RÉPONSES | '.$rawdata[$i]['counter'].' VUES</div>
                </div>

                
            </div>
            </div>
          ';
        }

      $i++;
    } 
    $html.='</div>';

    $term_data['postes'] = $rawdata;

    $term_data['totpostes'] = $totpostes->cnid;
    $pagination = floor($totpostes->cnid/6);

    $term_data['pagination'] = $pagination;

    $html.='<div class="users_page">';
    if($pagination > 0){
      $totpag = $pagination*6;
      if($totpag<$totpostes->cnid){
        $pagination ++;
      }
      $less = $pagination;
      for ($i=0; $i < $pagination; $i++) { 
        $j = $i+1;
        $k = $i*6;
        $link_page = Url::fromRoute('kozon.load.page',['arg'=>$k,'arg1'=>$j,'arg2'=>$pagination]);
        $link_page->setOptions([
          'attributes' => [
            'class' => ['use-ajax', 'button', 'button--small'],
          ]
        ]);
        if($i==0){
          $html .= '<div class="row pag-'.$i.'">';
          $html .= Link::fromTextAndUrl(t('<span class="page_change pag-selected page_change_'.$j.'" page="'.$k.'" style="display: block;">'.$j.'</span>'), $link_page)->toString();
        }
        else{
          $html .= Link::fromTextAndUrl(t('<span class="page_change page_change_'.$j.'" page="'.$k.'" style="display: block;">'.$j.'</span>'), $link_page)->toString();
        }
        
        $k=$i%17;
        if($i>0 && $k==0 && $i<$less){
          $html .= '<span class="page-more" prev="'.($i-16).'" next="'.($i+1).'">>></span></div><div class="row page-inactive pag-'.($i+1).'"><span class="page-less" prev="'.($i-16).'" next="'.($i+1).'"><<</span>';
        }
        elseif($i==$less){
          $html .= '</div>';
        }
      }
    }
    $html.='</div></div>';

    $data['#markup'] =t("$html");
    $data['#cache']['max-age'] = 0;
    $data['#title'] = 'Postes';
    $data['#attached']['library'] = ['core/drupal.ajax','kozon/kozon-query'];
    return $data;

    /*return array(
      //Your theme hook name
      '#theme' => 'my-forums',      
      //Your variables
      '#items' => $term_data,
      '#title' => 'Postes',
      '#attached' => ['library' => ['core/drupal.ajax','kozon/kozon-query']],
      '#cache' => array('max-age' => 0),
    );*/
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function loadPageJS($arg,$arg1,$arg2) {
    $account = \Drupal::currentUser(); 
    $uid = $account->id();
    $page = $arg;

    $msgPst = KozonStorage::getMyMessagePoste($uid,$page);
    $mobileDetector = \Drupal::service('mobile_detect');
    $is_mobile = $mobileDetector->isMobile();
    $term_data =[];
    $rawdata =[];
    $i=0;
    $html='';
    foreach ($msgPst as $msg) {
      
      $nodeId = $msg->nid;

      $node = node_load($msg->nid);
      $forum = KozonStorage::getMessagePosteById($nodeId);
      $taxArticles = KozonStorage::getMessagePosteByTaxId($forum->tid);
      // Data to be passed in the url.
      $likes = KozonStorage::getForumLike($nodeId);

      $rawdata[$i]["likes"] = $likes->field_like_likes;
      $rawdata[$i]["lidis_id"] = $nodeId;
      
      $k=0;
      $artTag = [];
      foreach ($taxArticles as $article) {
        if($k<3){
          $artTag[$k]['title'] = $article->title;
          $artTag[$k]['nid'] = $article->nid;
        }
        $k++;
      }

      $rawdata[$i]["taxarticle"] = $artTag;  
      $rawdata[$i]["taxarticlecount"] = $k;    

      $rawdata[$i]["title"] = $node->getTitle();
      $rawdata[$i]["body"] = str_replace('&nbsp;', '', $node->get('body')->value);

      $image = File::load( $node->get('field_forum_image')->target_id );
      if($image){
        $rawdata[$i]["image"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($image->getFileUri()))->toString());  
        $rawdata[$i]["image_name"] = $image->getFilename();
      } 

      $term = Term::load($forum->tid);
      if($term){
        $rawdata[$i]["taxonomy"] = $term->getName();
        $rawdata[$i]["tid"] = $forum->tid;
      }

      $rawdata[$i]["user"] = $forum->username;

      $imageuser = File::load( $forum->fid );
      if($imageuser){
        $rawdata[$i]["imageuser"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($imageuser->getFileUri()))->toString());  
      }
      else{
        $rawdata[$i]["imageuser"] = '/themes/keonoo/images/nouserimage7.png';
      } 

      $fileforum = File::load( $node->get('field_file')->target_id );
      if($fileforum){
        $rawdata[$i]["fileforum"] = file_url_transform_relative(\Drupal\Core\Url::fromUri(file_create_url($fileforum->getFileUri()))->toString());  
        $rawdata[$i]["fileforum_name"] = $fileforum->getFilename();  
        $rawdata[$i]["fileforum_size"] = $this->FileSizeConvert($fileforum->getSize());
      }
      
      $rawdata[$i]["date"]=date('j F Y \à H:i', $forum->created);

      $rawdata[$i]["nid"]=$msg->nid;
      $countc = KozonStorage::getCommentPosteCById($msg->nid);
      $rawdata[$i]['ccount']=$countc->ccid;

      $views = KozonStorage::selectCounterKForum($msg->nid);
      $rawdata[$i]['counter']=(!empty($views->counter))?$views->counter:'0';


      $link_url = Url::fromRoute('kozon.delete.forum',['arg'=>$nodeId]);
      $link_url->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);

      $link_url_no = Url::fromRoute('kozon.delete.forum.no',['arg'=>$nodeId]);
      $link_url_no->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);

      $link_url_yes = Url::fromRoute('kozon.delete.forum.yes',['arg'=>$nodeId]);
      $link_url_yes->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
        ]
      ]);


      if(!empty($rawdata[$i]['tid'])){
        $link_url_non = Url::fromRoute('kozon.delete.forum.non',['arg'=>$nodeId,'arg1'=>$rawdata[$i]['tid']]);
        $link_url_non->setOptions([
          'attributes' => [
            'class' => ['use-ajax', 'button', 'button--small'],
          ]
        ]);
      }
      else{
        $link_url_non = Url::fromRoute('kozon.delete.forum.no',['arg'=>$nodeId]);
        $link_url_non->setOptions([
          'attributes' => [
            'class' => ['use-ajax', 'button', 'button--small'],
          ]
        ]);
      }

      $html.='
      <div class="row post-kozon">
        <div class="col-12">
          <div class="header-post">
            <div class="view-field ktitle-response"><a href="/kozon/'.$rawdata[$i]['tid'].'/'.$rawdata[$i]['nid'].'">'.$rawdata[$i]['title'].'</a></div>
            <div class="kozonbookmark"></div>
            <a href="/kozon/'.$rawdata[$i]['tid'].'"><div class="view-field kcat-response">'.$rawdata[$i]['taxonomy'].'</div></a>
          </div>
          <div class="content-post">';
            if(!empty($rawdata[$i]['imageuser'])){
                $html.='<img src="'.$rawdata[$i]['imageuser'].'" class="kfoto-forum my-auto">';
            }
            $html.='<div class="view-field forum-username">'.$rawdata[$i]['user'].'</div>
            <div class="view-field forum-time">'.$rawdata[$i]['date'].'</div>
            <div class="view-field forum-body">'.strip_tags($rawdata[$i]['body']).'</div>';
            if(!empty($rawdata[$i]['image'])){
            $html.='<div class="forum-filer">
                <div class="dfimage"></div><a href="'.$rawdata[$i]['image'].'" class="nameImagef">'.$rawdata[$i]['image_name'].'<a>
              </div>
                <!--<img src="'.$rawdata[$i]['image'].'" class="dfimage">-->';
            }
            if(!empty($rawdata[$i]['fileforum'])){
              $html.='<div class="dfimage"></div><a href="'.$rawdata[$i]['fileforum'].'">'.$rawdata[$i]['fileforum_name'].' ('.$rawdata[$i]['fileforum_size'].')</a>';
            }
          $html.='</div>';
          if(!$is_mobile){
                $html.='<div class="links-kozon like-kozon">
                <div class="link-kozon-redirect order-0"><a href="/kozon/'.$rawdata[$i]['tid'].'/'.$rawdata[$i]['nid'].'" class="mypost-resp">RÉPONDRE</a></div>
                    <div class="like_dislike">
                    <span><i class="fas fa-heart"></i> </span>
                    <span id="like_'.$rawdata[$i]['lidis_id'].'">'.$rawdata[$i]['likes'].'</span>
                  </div>
                  <div id="like_dislike_status_'.$rawdata[$i]['lidis_id'].'"></div>
                     
                  <div class="delete-forum">'.Link::fromTextAndUrl(t('SUPPRIMER'), $link_url)->toString().'</div><div class="fade-kozon"></div><div class="delete-forum-'.$rawdata[$i]['lidis_id'].' forum-dis-non"> '.Link::fromTextAndUrl(t('X'), $link_url_no)->toString().'<span>Etes-vous sûr(e) de vouloir supprimer votre contenu?</span><div class="sup-btn-yes">'.Link::fromTextAndUrl(t('OUI'), $link_url_yes)->toString().'</div><div class="sup-btn-no">'.Link::fromTextAndUrl(t('NON'), $link_url_non)->toString().'</div></div>
                  <div class="kozon-info-post">'.$rawdata[$i]['ccount'].' RÉPONSES | '.$rawdata[$i]['counter'].' VUES</div>
                  </div>

                  
              </div>
              </div>
            ';
          }else{
          $html.='<div class="links-kozon like-kozon">
              
                  <div class="like_dislike">
                  <span><i class="fas fa-heart"></i> </span>
                  <span id="like_'.$rawdata[$i]['lidis_id'].'">'.$rawdata[$i]['likes'].'</span>
                </div>
                <div id="like_dislike_status_'.$rawdata[$i]['lidis_id'].'"></div>
                   
                <div class="delete-forum">'.Link::fromTextAndUrl(t('SUPPRIMER'), $link_url)->toString().'</div><div class="fade-kozon"></div><div class="delete-forum-'.$rawdata[$i]['lidis_id'].' forum-dis-non"> '.Link::fromTextAndUrl(t('X'), $link_url_no)->toString().'<span>Etes-vous sûr(e) de vouloir supprimer votre contenu?</span><div class="sup-btn-yes">'.Link::fromTextAndUrl(t('OUI'), $link_url_yes)->toString().'</div><div class="sup-btn-no">'.Link::fromTextAndUrl(t('NON'), $link_url_non)->toString().'</div></div>
                <div class="link-kozon-redirect order-0"><a href="/kozon/'.$rawdata[$i]['tid'].'/'.$rawdata[$i]['nid'].'" class="mypost-resp">RÉPONDRE</a></div>
                <div class="kozon-info-post">'.$rawdata[$i]['ccount'].' RÉPONSES | '.$rawdata[$i]['counter'].' VUES</div>
                </div>

                
            </div>
            </div>
          ';
        }

      $i++;
    } 

    $response = new AjaxResponse();
    for ($i=0; $i <= $arg2; $i++) {
      $j = $i+1; 
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.page_change_'.$j, 'removeClass', array('pag-selected')));
    }
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.page_change_'.$arg1, 'addClass', array('pag-selected')));
    $response->addCommand(new HtmlCommand('.post-kozon-main', ''));
    $response->addCommand(new HtmlCommand('.post-kozon-main', $html));
    return $response;
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function contentMyComments() {
  //Do something with your variables here.

    $account = \Drupal::currentUser(); 
    $uid = $account->id();
    $comments = KozonStorage::getMyCommentPostesAll($uid);
    $totcomments = KozonStorage::getMessageSuivis($uid);
    $rawdata = [];
    $rawcomment = [];
    $i=0;
    foreach ($comments as $comment) {
      $rawcomment[$i]['cid']=$comment->cid;
      $rawcomment[$i]['nid']=$comment->nid;
      $rawcomment[$i]['tid']=$comment->tid;
      $rawcomment[$i]['title']=$comment->title;
      $rawcomment[$i]['datechange']=date('d/m/Y \à H\hi', $comment->datechange);
      $rawcomment[$i]['datecreated']=date('d/m/Y \à H\hi', $comment->created);
      $rawcomment[$i]['body']=str_replace('&nbsp;', '', $comment->body);
      $rawcomment[$i]['subject']=$comment->subject;
      $rawcomment[$i]['user']=$comment->username;
      $rawcomment[$i]['nodeusername']=$comment->nodeusername;

      $countc = KozonStorage::getCommentPosteCById($comment->nid);
      $rawcomment[$i]['count']=$countc->ccid;
      $rawcomment[$i]['pid']=$comment->pid;
      $rawcomment[$i]['cid']=$comment->cid;
      $rawcomment[$i]['like']=$comment->likes;
      $views = KozonStorage::selectCounterKForum($comment->nid);
      $rawcomment[$i]['counter']=$views->counter;
      $color = KozonStorage::getTaxForumColor($comment->tid);
      $rawcomment[$i]['color']=$color->color;
      $i++;
    }
    $rawdata["comment"]=$rawcomment;
    $rawdata["totcomment"]=$totcomments->ccid;

    return array(
      //Your theme hook name
      '#theme' => 'kozon_theme_comment',      
      //Your variables
      '#items' => $rawdata,
      '#title' => 'Suivis',
      '#cache' => array('max-age' => 0),
    );
  }

  /**
        * Add a comment.
        */
  public function editForumJS($arg){
    $response = new AjaxResponse();
    $form = \Drupal::formBuilder()->getForm('Drupal\kozon\Form\EditForm',$arg);

    $options = [
          'dialogClass' => 'popup-dialog-class newk-f',
          'width' => '60%',
          'height' => 'auto',
          'max-height' => '800px',
          'z-index' => '2000',
        ];

    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand('Modifier message',$form, $options));

    return $response;
  }

  /**
        * Add a comment.
        */
  public function editCommentJS($arg, $arg1){
    $response = new AjaxResponse();
    $param = [$arg,$arg1];

    $form = \Drupal::formBuilder()->getForm('Drupal\kozon\Form\EditCommentForm',json_encode($param));

    $options = [
          'dialogClass' => 'popup-dialog-class newk-f',
          'width' => '60%',
          'height' => 'auto',
          'max-height' => '800px',
          'z-index' => '2000',
        ];

    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand('Modifier commentaire',$form, $options));

    return $response;
  }

  /**
        * Add a forum.
        */
  public function updateForumJS(){

    $nid = $_POST['nid'];
    $title = $_POST['title'];
    $taxforum = $_POST['taxforum'];
    $body = $_POST['body'];
    $fileforum = $_POST['fileforum'];

    $response = new AjaxResponse();
    if(!empty($_POST['body']['value'])){
      KozonStorage::editForum($nid, $body, $title, $taxforum, $fileforum);
      $return = $response->addCommand(
         new RedirectCommand('/kozon/'.$taxforum.'/'.$nid)
        );
    }
    else{
      $return = $response;
    }
    return $return; 
  }

  /**
        * Save a comment.
        */
  public function deleteForumJS($arg){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }

    $response = new AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.delete-forum-'.$arg, 'removeClass', array('forum-dis-non')));
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.delete-forum-'.$arg, 'addClass', array('forum-dis-yes')));
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.delete-forum-'.$arg, 'addClass', array('delete-cat-postes')));
    
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.fade-kozon', 'addClass', array('bg-popup')));
    return $response; 
  }

  /**
        * Save a comment.
        */
  public function deleteForumNoJS($arg){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }
    $response = new AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.delete-forum-'.$arg, 'removeClass', array('forum-dis-yes')));
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.delete-forum-'.$arg, 'addClass', array('forum-dis-non')));
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.delete-forum-'.$arg, 'removeClass', array('delete-cat-postes')));
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.fade-kozon', 'removeClass', array('bg-popup')));
    return $response; 
  }

  /**
        * Save a comment.
        */
  public function deleteForumYesJS($arg){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }

    KozonStorage::deleteForum($arg);


    $response = new AjaxResponse();
    $return = $response->addCommand(
       new RedirectCommand('/kozon/mysubjects')
      );
    return $return; 
  }

  /**
        * Save a comment.
        */
  public function deleteForumNonJS($arg,$arg1){
    $response = new AjaxResponse();
    $return = $response->addCommand(
       new RedirectCommand('/kozon/'.$arg1.'/'.$arg)
      );
    return $return; 
  }

  /**
        * Add a comment.
        */
  public function addCommentGrandParentJS($arg){
    $response = new AjaxResponse();
    $form = \Drupal::formBuilder()->getForm('Drupal\kozon\Form\AddCommentChildForm',$arg);
    $return = $response->addCommand(
        new HtmlCommand('.display-comment-grandparent-'.$arg, $form)
      );
    return $return; 
  }

  /**
        * hide a comment form.
        */
  public function hideCommentGrandParentJS($arg){
    $response = new AjaxResponse();
    $return = $response->addCommand(
        new HtmlCommand('.display-comment-grandparent-'.$arg, '')
      );
    return $return; 
  }

  /**
        * Add a tag.
        */
  public function addTagJS($arg,$arg1){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }
    KozonStorage::addTag($uid, $arg, $arg1);
    $response = new AjaxResponse();
    $tagUser = KozonStorage::selectTag($uid, $arg, $arg1);
    if(!empty($tagUser)){
      $return = $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.tag-kozon-'.$arg, 'addClass', array('post-active-tag')));
    }
    else{
      $return = $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.tag-kozon-'.$arg, 'removeClass', array('post-active-tag')));
    }
    return $return; 
  }

  /**
        * Add a tag.
        */
  public function removeTagJS($arg,$arg1){
    if(empty(\Drupal::currentUser()->id())){
      return;
    }
    else{
      $uid = \Drupal::currentUser()->id();
    }
    KozonStorage::addTag($uid, $arg, $arg1);
    $response = new AjaxResponse();
    $return = $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.tag-kozon-'.$arg, 'removeClass', array('post-active-tag')));
    return $return; 
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function contentMySuivis() {
  //Do something with your variables here.

    $account = \Drupal::currentUser(); 
    $uid = $account->id();
    $comments = KozonStorage::getMyCommentPostesAll($uid);
    $totcomments = KozonStorage::getMessageSuivis($uid);
    $rawdata = [];
    $rawcomment = [];
    $i=0;
    foreach ($comments as $comment) {
      $rawcomment[$i]['nid']=$comment->nid;
      $rawcomment[$i]['tid']=$comment->tid;
      $rawcomment[$i]['title']=$comment->title;

      $commentl = KozonStorage::getLastCommentPostesAll($comment->nid);
      if(!empty($commentl)){
        $rawcomment[$i]['datechange']=date('d/m/Y \à H\hi', $commentl->datechange);
        $rawcomment[$i]['cid']=$commentl->cid;
        $rawcomment[$i]['user']=(!empty($commentl->firstname))?ucwords(strtolower($commentl->firstname.' '.$commentl->lastname)):ucwords(strtolower($commentl->username));

        $rawcomment[$i]['nodeusername']=(!empty($comment->firstname))?ucwords(strtolower($comment->firstname.' '.$comment->lastname)):ucwords(strtolower($comment->nodeusername));

      }else{
        $rawcomment[$i]['datechange']='';
        $rawcomment[$i]['cid']='';
        $rawcomment[$i]['user']='';
        $rawcomment[$i]['nodeusername']='';
      }

      $countc = KozonStorage::getCommentPosteCById($comment->nid);
      $rawcomment[$i]['count']=$countc->ccid;
      $views = KozonStorage::selectCounterKForum($comment->nid);
      $rawcomment[$i]['counter']=$views->counter;
      $color = KozonStorage::getTaxForumColor($comment->tid);
      $rawcomment[$i]['color']=$color->color;
      $i++;
    }
    $rawdata["comment"]=$rawcomment;
    $rawdata["totcomment"]=$totcomments->ccid;

    return array(
      //Your theme hook name
      '#theme' => 'kozon_theme_comment',      
      //Your variables
      '#items' => $rawdata,
      '#title' => 'Suivis',
      '#cache' => array('max-age' => 0),
    );
  }

}
