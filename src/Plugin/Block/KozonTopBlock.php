<?php

namespace Drupal\kozon\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;


/**
 * Provides a block called "Kozon top block".
 *
 * @Block(
 *  id = "module_kozon_top",
 *  admin_label = @Translation("Kozon top block")
 * )
 */
class KozonTopBlock extends BlockBase {

   /**
   * {@inheritdoc}
   */
    public function build() {
        $taxonomies_type_content = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('type_of_content');
        $title = "";
        $autor = "";
        $desc = "";
        $image_id = "";
        foreach($taxonomies_type_content  as $taxonomy){
            if($taxonomy->name=="Kozon"){
                //$variables['type_content'] = $taxonomy; 
                $title = (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_title_c')->getValue())?\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_title_c')->getValue()[0]['value']:'';
                $autor = (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_author_c')->getValue())?\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_author_c')->getValue()[0]['value']:'';
                $desc = (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_description_c')->getValue())?\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_description_c')->getValue()[0]['value']:'';
                $image_id = (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_image_c')->getValue())?\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy->tid)->get('field_h_image_c')->getValue()[0]['target_id']:'';
            }        
        }


        $file = File::load($image_id);
        $url_image = "";
        if($file){
          $url_image =  \Drupal\Core\Url::fromUri(file_create_url($file->getFileUri()))->toString();   
        }


        $html = "<div class='category_header_content'>
                  <img class='subcategory_img' src='$url_image'>
                  <div class='category_header_text '>
                      <div class='category_header_title text-center'>$title</div>
                      <div class='category_header_desc text-center'>$desc</div>
                      <div class='category_header_author text-center'>$autor</div>
                  </div>
              </div>";
      $data['#markup'] =t("$html");
      $data['#cache']['max-age'] = 0;

      return $data;

    }

  
  
}
