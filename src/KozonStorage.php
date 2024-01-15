<?php

/**
 * @file
 * Contains \Drupal\kozon\KozonStorage.
 */

namespace Drupal\kozon;	

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\comment\Entity\Comment;
use Drupal\Component\Utility\Random;
 
 class KozonStorage {
	
	/**
	* {@inheritdoc}
	*/
        static function getForumPost(){
            return db_query('SELECT fd.nid, ufd.name, fd.title, ufd.uid, fd.changed, nb.body_value, tc.taxonomy_forums_target_id tid, fn.field_first_name_value firstname, ln.field_last_name_value lastname 
                FROM {node_field_data} fd 
                INNER JOIN {node__body} nb ON nb.entity_id=fd.nid 
                INNER JOIN {users_field_data} ufd ON ufd.uid = fd.uid 
                LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=fd.nid 
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ufd.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ufd.uid
                WHERE fd.type=:type order by nid desc limit 2', array(':type'=>'forum'))->fetchAll();
        }

    /**
	* {@inheritdoc}
	*/
        static function getForumImage($nid){
            return db_query('select fm.uri from {node__field_forum_image} ffi INNER JOIN {file_managed} fm ON fm.fid = ffi.field_forum_image_target_id where ffi.entity_id=:nid order by ffi.field_forum_image_target_id desc limit 1', array(':nid'=>$nid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getForumLike($nid){
            return db_query('select fl.field_like_likes, fl.field_like_clicked_by from {node__field_like} fl where fl.entity_id=:nid', array(':nid'=>$nid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getForumLikeByUser($nid){
            return db_query('select fl.field_like_likes from {node__field_like} fl where fl.entity_id=:nid', array(':nid'=>$nid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getMessagePosteById($nid){
            return db_query('SELECT fd.nid nid, fd.title as title, tc.taxonomy_forums_target_id tid, ud.name username, pt.user_picture_target_id fid, fd.created created, fi.comment_count comment_count, ud.uid uid, fn.field_first_name_value firstname, ln.field_last_name_value lastname
                FROM {node_field_data} fd 
                LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=fd.nid 
                LEFT JOIN {users_field_data} ud ON ud.uid=fd.uid 
                LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
                LEFT JOIN {forum_index} fi ON fi.nid = fd.nid 
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
                WHERE fd.type=:type and fd.nid=:nid', array(':type'=>'forum', ':nid'=>$nid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getMessagePosteByTaxId($tid,$pagination=''){
            return db_query('SELECT fd.nid nid, fd.title as title, ud.name username, pt.user_picture_target_id fid, fd.created created, fi.comment_count comment_count, fn.field_first_name_value firstname, ln.field_last_name_value lastname
                FROM {node_field_data} fd 
                LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=fd.nid 
                LEFT JOIN {users_field_data} ud ON ud.uid=fd.uid 
                LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
                LEFT JOIN {forum_index} fi ON fi.nid = fd.nid 
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
                WHERE fd.type=:type and tc.taxonomy_forums_target_id=:tid order by fd.nid desc limit 10 offset '.(empty($pagination)?'0':$pagination), array(':type'=>'forum', ':tid'=>$tid))->fetchAll();
        }

     /**
    * {@inheritdoc}
    */
    static function getCMessagePosteByTaxId($tid){
        return db_query('SELECT COUNT(fd.nid) AS cnid 
                FROM {node_field_data} fd 
                LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=fd.nid 
                WHERE fd.type=:type and tc.taxonomy_forums_target_id=:tid', array(':type'=>'forum', ':tid'=>$tid))->fetchObject();
    }

    /**
    * {@inheritdoc}
    */
        static function getCommentPosteById($nid){
            return db_query('SELECT cd.cid cid, cd.changed datechange, cd.subject subject, cd.uid user, pt.user_picture_target_id fid, cf.field_file_target_id fileid, cd.pid pid, cb.comment_body_value body, ud.name username, ud.uid uid, fn.field_first_name_value firstname, ln.field_last_name_value lastname
                FROM {comment_field_data} cd 
                LEFT JOIN {users_field_data} ud ON ud.uid=cd.uid 
                LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
                LEFT JOIN {comment__field_file} cf ON cf.entity_id = cd.cid
                LEFT JOIN {comment__comment_body} cb ON cb.entity_id = cd.cid
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
                WHERE cd.entity_id=:nid AND cd.pid is null ORDER BY cd.cid', array(':nid'=>$nid))->fetchAll();
        }

    /**
    * {@inheritdoc}
    */
        static function getCommentGetPosteById($nid){
            return db_query('SELECT cd.cid cid, cd.changed datechange, cd.subject subject, cd.uid user, pt.user_picture_target_id fid, cf.field_file_target_id fileid, cd.pid pid, cb.comment_body_value body, ud.name username, ud.uid uid, fn.field_first_name_value firstname, ln.field_last_name_value lastname
                FROM {comment_field_data} cd 
                LEFT JOIN {users_field_data} ud ON ud.uid=cd.uid 
                LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
                LEFT JOIN {comment__field_file} cf ON cf.entity_id = cd.cid
                LEFT JOIN {comment__comment_body} cb ON cb.entity_id = cd.cid
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
                WHERE cd.entity_id=:nid AND cd.pid is null ORDER BY cd.cid DESC', array(':nid'=>$nid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getCommentCountPosteById($nid){
            return db_query('SELECT cd.cid cid
                FROM {comment_field_data} cd 
                WHERE cd.entity_id=:nid AND cd.pid is null', array(':nid'=>$nid))->fetchAll();
        }

    /**
    * {@inheritdoc}
    */
        static function getCommentLastPosteById($nid){
            return db_query('SELECT cd.cid cid
                FROM {comment_field_data} cd 
                WHERE cd.entity_id=:nid AND cd.pid is null order by cd.cid desc', array(':nid'=>$nid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getCommentPosteByCommentId($cid){
            return db_query('SELECT cd.cid cid, cd.changed datechange, cd.subject subject, cd.uid user, pt.user_picture_target_id fid, cf.field_file_target_id fileid, cd.pid pid, cb.comment_body_value body, ud.name username, fn.field_first_name_value firstname, ln.field_last_name_value lastname
                FROM {comment_field_data} cd 
                LEFT JOIN {users_field_data} ud ON ud.uid=cd.uid 
                LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
                LEFT JOIN {comment__field_file} cf ON cf.entity_id = cd.cid
                LEFT JOIN {comment__comment_body} cb ON cb.entity_id = cd.cid
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
                WHERE cd.cid=:cid ORDER BY cd.cid', array(':cid'=>$cid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getCommentPosteCById($nid){
            return db_query('SELECT count(cd.cid) as ccid
                FROM {comment_field_data} cd 
                WHERE cd.entity_id=:nid', array(':nid'=>$nid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getCommentChildPosteById($cid){
            return db_query('SELECT cd.cid cid, cd.changed datechange, cd.subject subject, cd.uid user, pt.user_picture_target_id fid, cf.field_file_target_id fileid, cd.pid pid, cb.comment_body_value body, ud.name username, fn.field_first_name_value firstname, ln.field_last_name_value lastname
                FROM {comment_field_data} cd 
                LEFT JOIN {users_field_data} ud ON ud.uid=cd.uid 
                LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
                LEFT JOIN {comment__field_file} cf ON cf.entity_id = cd.cid
                LEFT JOIN {comment__comment_body} cb ON cb.entity_id = cd.cid
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
                WHERE cd.pid=:cid AND cd.pid is not null ORDER BY cd.pid', array(':cid'=>$cid))->fetchAll();
        }

    /**
    * {@inheritdoc}
    */
        static function getCommentChildParentById($cid){
            return db_query('SELECT cd.cid cid, tc.taxonomy_forums_target_id tid, cd.entity_id nid
                FROM {comment_field_data} cd 
                LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=cd.entity_id
                WHERE cd.cid=:cid ', array(':cid'=>$cid))->fetchObject();
        }

     /**
    * {@inheritdoc}
    */
        static function getLastMessagePoste(){
            return db_query('select fd.nid nid, fd.title as title, tc.taxonomy_forums_target_id tid, ud.name username, pt.user_picture_target_id fid, fd.created created 
                from {node_field_data} fd 
                LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=fd.nid 
                LEFT JOIN {users_field_data} ud ON ud.uid=fd.uid 
                LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
                where fd.type=:type order by fd.nid desc limit 6', array(':type'=>'forum'))->fetchAll();
        }

    /**
    * {@inheritdoc}
    */
        static function getArticleByTid($tid){
            return db_query('select fd.title as title from {node__field_type_of_content} tc INNER JOIN {node_field_data} fd ON fd.nid=tc.entity_id where field_type_of_content_target_id=:tid order by entity_id desc limit 3', array(':tid'=>$tid))->fetchAll();
        }

        
    /**
    * {@inheritdoc}
    */
    static function addForum($uid, $body, $title, $taxforum, $iconforum, $fileforum){

        $node = Node::create(['type' => 'forum']);
        $node->set('title', $title);
        $node->set('body', $body);
        $node->set('uid', $uid);
        $node->set('taxonomy_forums', ['target_id' => $taxforum]);
        $node->set('field_icon', ['target_id' => $iconforum]);
        $node->set('field_file', ['target_id' => $fileforum['fids']]);
        $node->status = 1;
        $node->enforceIsNew();
        $node->save();
        $term = Term::load($taxforum);
        $name = $term->getName();
        $name = strtolower(str_replace(' ', '-', $name));
        $title = strtolower(str_replace(' ', '-', $title));
        $path = '/forum/'.$name.'/'.$title;
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        
        $pathcur = \Drupal::service('path.alias_storage')->aliasExists($path, $language);
        $i=0;
        while ( $pathcur == 1) {
            $path = $path.'-'.$i;
            $pathcur = \Drupal::service('path.alias_storage')->aliasExists($path, $language);
            $i++;
        }
        \Drupal::service('path.alias_storage')->save('/node/'.$node->id(), $path, $language);

        drupal_set_message( "Node with nid " . $node->id() . " saved!\n");
    }

     /**
    * {@inheritdoc}
    */
    static function editForum($nid, $body, $title, $taxforum, $fileforum=''){
        $node = node_load($nid);
        $node->set('title', $title);
        $node->set('body', $body);
        $node->set('taxonomy_forums', ['target_id' => $taxforum]);
        if( $fileforum ){
            $node->set('field_file', ['target_id' => $fileforum['fids']]);
        }
        $node->save();
        
    }

    /**
    * {@inheritdoc}
    */
        static function getForumPostebyTid($tid){
            return db_query('select count(taxonomy_forums_target_id) cpost from {node__taxonomy_forums} where taxonomy_forums_target_id=:tid', array(':tid'=>$tid))->fetchObject();
        }

        /**
    * {@inheritdoc}
    */
        static function getForumCommentbyTid($tid){
            return db_query('select SUM(comment_count) scom from {forum_index} where tid=:tid', array(':tid'=>$tid))->fetchObject();
        }


    /**
    * {@inheritdoc}
    */
        static function getLastMessagePosteByTid($tid){
            return db_query('select ud.name username, fd.created created, fn.field_first_name_value firstname, ln.field_last_name_value lastname 
                from {forum_index} fi
                LEFT JOIN {node_field_data} fd ON fd.nid=fi.nid 
                LEFT JOIN {users_field_data} ud ON ud.uid=fd.uid 
                LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
                LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
                where tid=:tid order by fi.nid desc limit 1', array(':tid'=>$tid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
    static function addComment($postid, $body, $fileforum) {
        $kozonSto = new KozonStorage();
        $node = node_load($postid);
        // Comment on the first 5 topics.
        $date = new \DateTime();

        $date->modify('+1 minute');
        $comment = Comment::create([
        'entity_id' => $node->id(),
        'field_name' => 'comment_forum',
        'entity_type' => 'node',
        'node_type' => 'node_type_' . $node->bundle(),
        'subject' => $kozonSto->randomString(20),
        'comment_body' => $body,
        'created' => $date->getTimestamp(),
        'uid' => \Drupal::currentUser()->id(),
        'field_file' => ['target_id' => $fileforum['fids'],'display' => 1],
        ]);
        $comment->save();
  }

  /**
    * {@inheritdoc}
    */
    static function updateComment($postid, $cid, $body, $fileforum) {
        $comment = \Drupal::entityTypeManager()->getStorage('comment')->load($cid);

        $comment->set('comment_body', $body);
        if( $fileforum ){
            $comment->set('field_file', ['target_id' => $fileforum['fids']]);
        }
        $comment->save();
  }

  /**
    * {@inheritdoc}
    */
    static function addCommentChild($postid, $body, $fileforum, $compid) {
        $kozonSto = new KozonStorage();
        $node = node_load($postid);
        // Comment on the first 5 topics.
        $date = new \DateTime();

        $date->modify('+1 minute');
        $comment = Comment::create([
        'entity_id' => $node->id(),
        'field_name' => 'comment_forum',
        'entity_type' => 'node',
        'node_type' => 'node_type_' . $node->bundle(),
        'subject' => $kozonSto->randomString(20),
        'comment_body' => $body,
        'created' => $date->getTimestamp(),
        'uid' => \Drupal::currentUser()->id(),
        'pid' => $compid,
        'field_file' => ['target_id' => $fileforum['fids'],'display' => 1],
        ]);
        $comment->save();
  }

  /**
   * Generates a pseudo-random string of ASCII characters of codes 32 to 126.
   *
   * Do not use this method when special characters are not possible (e.g., in
   * machine or file names that have already been validated); instead, use
   * \Drupal\simpletest\TestBase::randomMachineName(). If $length is greater
   * than 3 the random string will include at least one ampersand ('&') and
   * at least one greater than ('>') character to ensure coverage for special
   * characters and avoid the introduction of random test failures.
   *
   * @param int $length
   *   Length of random string to generate.
   *
   * @return string
   *   Pseudo-randomly generated unique string including special characters.
   *
   * @see \Drupal\Component\Utility\Random::string()
   */
  static function randomString($length = 8) {
    $kozonSto = new KozonStorage();
    if ($length < 4) {
      return $kozonSto->getRandomGenerator()->string($length, TRUE);
    }

    // To prevent the introduction of random test failures, ensure that the
    // returned string contains a character that needs to be escaped in HTML by
    // injecting an ampersand into it.
    $replacement_pos = floor($length / 2);
    // Remove 2 from the length to account for the ampersand and greater than
    // characters.
    $string = $kozonSto->getRandomGenerator()->string($length - 2, TRUE);
    return substr_replace($string, '>&', $replacement_pos, 0);
  }

  /**
   * Gets the random generator for the utility methods.
   *
   * @return \Drupal\Component\Utility\Random
   *   The random generator.
   */
  protected function getRandomGenerator() {
      $randomGenerator = new Random();
    return $randomGenerator;
  }

  /**
    * {@inheritdoc}
    */
    static function getAllMessagePoste(){
        return db_query('select fd.nid nid, fd.title as title, tc.taxonomy_forums_target_id tid, ud.name username, pt.user_picture_target_id fid, fd.created created, fn.field_first_name_value firstname, ln.field_last_name_value lastname 
            from {node_field_data} fd 
            LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=fd.nid 
            LEFT JOIN {users_field_data} ud ON ud.uid=fd.uid 
            LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
            LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
            LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
            where fd.type=:type order by fd.nid desc', array(':type'=>'forum'))->fetchAll();
    }

    /**
    * {@inheritdoc}
    */
    static function getMyMessagePoste($uid,$pagination=''){
        return db_query('SELECT fd.nid nid, fd.title as title, tc.taxonomy_forums_target_id tid, ud.name username, pt.user_picture_target_id fid, fd.created created, fn.field_first_name_value firstname, ln.field_last_name_value lastname  
            from {node_field_data} fd 
            LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=fd.nid 
            LEFT JOIN {users_field_data} ud ON ud.uid=fd.uid 
            LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
            LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
            LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
            where fd.uid=:uid and fd.type=:type and fd.status=1 order by fd.nid desc limit 6 offset '.(empty($pagination)?'0':$pagination), array(':uid'=>$uid,':type'=>'forum'))->fetchAll();
    }

        /**
    * {@inheritdoc}
    */
        static function getMyMessageComment($uid){
            return db_query('select count(nid) as cnid from {node_field_data} where uid=:uid and type=:type', array(':uid'=>$uid,':type'=>'forum'))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
    static function getMyCommentPoste($uid){
        return db_query('SELECT cd.cid cid, cd.changed datechange, cd.subject subject, cd.uid user, pt.user_picture_target_id fid, cf.field_file_target_id fileid, cd.pid pid, cb.comment_body_value body, ud.name username, cd.entity_id nid, tc.taxonomy_forums_target_id tid
            FROM {comment_field_data} cd 
            LEFT JOIN {users_field_data} ud ON ud.uid=cd.uid 
            LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
            LEFT JOIN {comment__field_file} cf ON cf.entity_id = cd.cid
            LEFT JOIN {comment__comment_body} cb ON cb.entity_id = cd.cid
            LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=cd.entity_id
            LEFT JOIN {node_field_data} fd ON fd.nid = cd.entity_id
            WHERE cd.uid=:uid AND cd.pid is null ORDER BY cd.cid', array(':uid'=>$uid))->fetchAll();
    }

    /**
    * {@inheritdoc}
    */
    static function getMyCommentPostesAllOld($uid){
        return db_query('SELECT DISTINCT cd.cid cid, cd.changed datechange, cd.subject subject, cd.uid user, pt.user_picture_target_id fid, cf.field_file_target_id fileid, cd.pid pid, ud.name username, fd.nid nid, fd.title as title, udn.name nodeusername, fd.created created, tc.taxonomy_forums_target_id tid, lk.field_like_likes likes
            FROM {comment_field_data} cd 
            LEFT JOIN {users_field_data} ud ON ud.uid=cd.uid 
            LEFT JOIN {user__user_picture} pt ON pt.entity_id = ud.uid 
            LEFT JOIN {comment__field_file} cf ON cf.entity_id = cd.cid
            LEFT JOIN {comment__comment_body} cb ON cb.entity_id = cd.cid
            LEFT JOIN {node__taxonomy_forums} tc ON tc.entity_id=cd.entity_id
            LEFT JOIN {node_field_data} fd ON fd.nid = cd.entity_id
            LEFT JOIN {users_field_data} udn ON udn.uid=fd.uid
            LEFT JOIN {node__field_like} lk ON lk.entity_id=fd.nid
            WHERE cd.uid=:uid AND cd.pid is null ORDER BY cd.cid DESC', array(':uid'=>$uid))->fetchAll();
    }

    /**
    * {@inheritdoc}
    */
    static function getMyCommentPostesAll($uid){
        return db_query('SELECT DISTINCT cd.forumid nid, fd.title as title, udn.name nodeusername, cd.catid tid, fn.field_first_name_value firstname, ln.field_last_name_value lastname 
            FROM {kozon_tag} cd 
            LEFT JOIN {node_field_data} fd ON fd.nid = cd.forumid
            LEFT JOIN {users_field_data} udn ON udn.uid=fd.uid
            LEFT JOIN {user__field_first_name} fn ON fn.entity_id = udn.uid
            LEFT JOIN {user__field_last_name} ln ON ln.entity_id = udn.uid
            WHERE cd.uid=:uid ORDER BY cd.forumid DESC', array(':uid'=>$uid))->fetchAll();
    }

    /**
    * {@inheritdoc}
    */
    static function getLastCommentPostesAll($nid){
        return db_query('SELECT DISTINCT cd.cid cid, cd.changed datechange, ud.name username, fn.field_first_name_value firstname, ln.field_last_name_value lastname 
            FROM {comment_field_data} cd 
            LEFT JOIN {users_field_data} ud ON ud.uid=cd.uid 
            LEFT JOIN {user__field_first_name} fn ON fn.entity_id = ud.uid
            LEFT JOIN {user__field_last_name} ln ON ln.entity_id = ud.uid
            WHERE cd.entity_id=:nid AND cd.pid is null ORDER BY cd.cid DESC Limit 1', array(':nid'=>$nid))->fetchObject();
    }

    /**
    * {@inheritdoc}
    */
        static function getMessagePoste($uid){
            return db_query('select count(nid) as cnid from {node_field_data} where uid=:uid and type=:type', array(':uid'=>$uid,':type'=>'forum'))->fetchObject();
        }

     /**
    * {@inheritdoc}
    */
        static function getMessageSuivisOld($uid){
            return db_query('select count(cid) as ccid from {comment_field_data} where uid=:uid and comment_type=:type', array(':uid'=>$uid,':type'=>'comment_forum'))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
        static function getMessageSuivis($uid){
            return db_query('select count(ktid) as ccid from {kozon_tag} where uid=:uid', array(':uid'=>$uid))->fetchObject();
        }

        /**
    * {@inheritdoc}
    */
        static function getTaxForumColor($tid){
            return db_query('select field_color_value color from {taxonomy_term__field_color} where entity_id=:tid', array(':tid'=>$tid))->fetchObject();
        }

        /**
    * {@inheritdoc}
    */
    static function addCounterK($uid, $forumid=0, $catid=0){
        $kozonSto = new KozonStorage();
        $count = $kozonSto->selectCounterK($uid, $forumid);
        if(!$count){
            db_insert('kozon_visited')
            ->fields(array(
              'uid' => $uid,
              'catid' => $catid,
              'forumid' => $forumid,
              'counter' => 1,
              'timestamp' => REQUEST_TIME,
            ))
            ->execute();
        }else{
            $kozonSto->updateCounterK($uid, $forumid, $count->counter);
        }

    }

    /**
    * {@inheritdoc}
    */
    static function updateCounterK($uid, $forumid, $counter){
        $counter = $counter + 1;
        db_update('kozon_visited')
            ->fields(array(
              'counter' => intval($counter),
              'timestamp' => REQUEST_TIME
            ))
            ->condition('uid',$uid)
            ->condition('forumid',$forumid)
            ->execute();
    }

    /**
    * {@inheritdoc}
    */
    static function selectCounterK($uid, $forumid){
        return db_query('select counter counter from {kozon_visited} where uid=:uid and forumid = :forumid', array(':uid'=>$uid,':forumid'=>$forumid))->fetchObject();
    }

    /**
    * {@inheritdoc}
    */
    static function selectCounterKForum($forumid){
        return db_query('SELECT SUM(counter) AS counter FROM {kozon_visited} WHERE forumid = :forumid', array(':forumid'=>$forumid))->fetchObject();
    }

    /**
    * {@inheritdoc}
    */
    static function addLikeComment($uid, $comid=0){
        $kozonSto = new KozonStorage();
        $count = $kozonSto->selectLikeComment($uid, $comid);
        if(!$count){
            db_insert('like_comment')
            ->fields(array(
              'uid' => $uid,
              'comid' => $comid,
              'timestamp' => REQUEST_TIME,
            ))
            ->execute();
        }
        else{
            $kozonSto->removeLikeComment($uid, $comid);
        }

    }

    /**
    * {@inheritdoc}
    */
    static function removeLikeComment($uid, $comid=0){
        $kozonSto = new KozonStorage();
        $count = $kozonSto->selectLikeComment($uid, $comid);
        if($count){
            db_delete('like_comment')
            ->condition('uid', $uid)    
            ->condition('comid', $comid)            
            ->execute();
        }

    }

    /**
    * {@inheritdoc}
    */
    static function selectLikeComment($uid, $comid){
        return db_query('select clid from {like_comment} where uid=:uid and comid = :comid', array(':uid'=>$uid,':comid'=>$comid))->fetchObject();
    }

    /**
    * {@inheritdoc}
    */
    static function selectLikeCommentC($comid){
        return db_query('SELECT COUNT(clid) AS cclid FROM {like_comment} WHERE comid = :comid', array(':comid'=>$comid))->fetchObject();
    }

    /**
    * {@inheritdoc}
    */
    static function deleteForum($postid) {
        $node = node_load($postid);
        $node->delete();
  }

  /**
    * {@inheritdoc}
    */
        static function getTaxForumEnableb($tid){
            return db_query('select field_enable_kozon_category_value value from {taxonomy_term__field_enable_kozon_category} where entity_id=:tid', array(':tid'=>$tid))->fetchObject();
        }

    /**
    * {@inheritdoc}
    */
    static function addTag($uid, $forumid=0, $catid=0){
        $kozonSto = new KozonStorage();
        $count = $kozonSto->selectTag($uid, $forumid, $catid);
        error_log('add');
        error_log(print_r($count,true));
        if(empty($count)){
            db_insert('kozon_tag')
            ->fields(array(
              'uid' => $uid,
              'catid' => $catid,
              'forumid' => $forumid,
              'timestamp' => REQUEST_TIME,
            ))
            ->execute();
        }
        else{
            $kozonSto->removeTag($uid, $forumid, $catid);
        }

    }

    /**
    * {@inheritdoc}
    */
    static function removeTag($uid, $forumid=0, $catid=0){
        $kozonSto = new KozonStorage();
        $count = $kozonSto->selectTag($uid, $forumid, $catid);
        error_log('remove');
        error_log(print_r($count,true));
        if(!empty($count)){
            db_delete('kozon_tag')
            ->condition('uid', $uid)    
            ->condition('catid', $catid) 
            ->condition('forumid', $forumid)            
            ->execute();
        }

    }

    /**
    * {@inheritdoc}
    */
    static function selectTag($uid, $forumid, $catid){
        return db_query('select ktid from {kozon_tag} where uid=:uid and forumid = :forumid and catid = :catid', array(':uid'=>$uid,':forumid'=>$forumid,':catid'=>$catid))->fetchObject();
    }

 }
 
