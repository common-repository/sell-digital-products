<?php

/*
Plugin Name: Sell digital products
Plugin URI: https://www.oronjo.com/
Description: Automatically populate your Oronjo products into Wordpress, includes a shopping cart.
Author: orangewise
Version: 1.0.1
Author URI: http://wordpress.org/extend/plugins/profile/rluitwieler
*/ 

/*  Copyright 2008  Ronald Luitwieler  (email : ro@oronjo.nl)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace WPSE\selldigitalproducts;

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
  die( "Aren't you supposed to come here via WP-Admin?" );
}

$oronjo_is_setup = 0;
$oronjo_url = 'https://www.oronjo.com/';
$prefix = 'oronjo';
if (__NAMESPACE__ == 'WPSE\selldigitalproducts') {
  $oronjo_license_product = 'jKRrwdcMiLZd3q2p4';
  update_option('oronjo_license_'.basename(plugin_basename( __FILE__ ), '.php'), $oronjo_license_product);
}


function save_oronjo_settings() {
  global $prefix;
  $option_oronjoapikey   = sanitize_text_field($_POST['oronjoapikey']);
  $option_post_status    = sanitize_text_field($_POST['post_status']);
  $option_cat_id         = sanitize_text_field($_POST['cat_id']);
  $option_tag            = sanitize_text_field($_POST['oronjo_tag']);
  $option_item_count     = sanitize_text_field($_POST['oronjo_number_of_items']);
  $option_updated_only   = sanitize_option($prefix.'_updated_only', $_POST['updated_only']);
  $option_image_tag      = sanitize_option($prefix.'_use_image_tag', $_POST['use_image_tag']);
  $option_images         = sanitize_option($prefix.'_use_images', $_POST['use_images']);

  update_option($prefix.'_oronjoapikey', $option_oronjoapikey);
  update_option($prefix.'_post_status', $option_post_status);
  update_option($prefix.'_cat_id', $option_cat_id);
  update_option($prefix.'_tag', $option_tag);
  update_option($prefix.'_updated_only', $option_updated_only);
  update_option($prefix.'_use_image_tag', $option_image_tag);
  update_option($prefix.'_use_images', $option_images);
  update_option($prefix.'_number_of_items', $option_item_count);

  if (__NAMESPACE__ == 'WPSE\selldigitalproducts') {
    $option_cart_position  = sanitize_text_field($_POST['cart_position']);
    $option_cart_top_margin= sanitize_text_field($_POST['cart_top_margin']);
    $option_license        = sanitize_text_field($_POST['oronjo_license']);
    update_option($prefix.'_cart_position', $option_cart_position);
    update_option($prefix.'_cart_top_margin', $option_cart_top_margin);
    update_option($prefix.'_license', $option_license);
    validate_oronjo_license(true);
  }
};




function oronjo_subpanel() {
  global $oronjo_url, $prefix, $oronjo_license_product;
  valid_oronjo_license();
  if (isset($_POST['save_oronjo_settings'])) {
    save_oronjo_settings();
    echo '<div class="updated"><p>Sell Digital Products with Oronjo have been saved. If you would like to import your Oronjo products into Wordpress, hit the \'Import Oronjo Products\' button.</p></div>'; 
  }
  if (isset($_POST['create_oronjo_posts'])) {
    save_oronjo_settings();
    echo '<div class="updated"><p>Your Oronjo Content has been imported/updated, check the log at the bottom of the page...</p></div>';
  }
  ?>
<div class="wrap">
  <h2 style="text-align: center;">
    <a href="<?php echo $oronjo_url; ?>" target="top">
    <img src="<?php echo plugins_url('images/icon-cart-256x256.png', __FILE__); ?>" 
         width="128" height="128"
         alt="Sell digital products, sell downloads with Oronjo" 
         align="middle" border="0"></a> 
    Sell Digital Products with Oronjo
  </h2>

  <form method="post" style="padding-top: 20px;">
    <table>

      <tr valign="top">
        <th scope="row" width="20%" style="text-align: right; padding-right: 20px;">
          Oronjo API Key
        </th>
        <td width="25%">
          <input name="oronjoapikey" type="text" id="oronjoapikey" value="<?php echo esc_attr(get_option($prefix.'_oronjoapikey')); ?>" size="45" />
        </td> 
        <td width="55%">
          You can find your <a href="<?php echo $oronjo_url; ?>account" target="top">Oronjo API Key</a> on your account (tab 'Integrations').
        </td>
      </tr>


      <tr valign="top">
        <th scope="row" style="text-align: right; padding-right: 20px;">
          Publish Status
        </th>
        <td>
          <select name="post_status" id="post_status">
          <option <?php if(get_option($prefix.'_post_status') == 'publish') { echo 'selected'; } ?> value="publish">Published</option>
          <option <?php if(get_option($prefix.'_post_status') == 'draft')   { echo 'selected'; } ?> value="draft">Draft</option>
          </select>
        </td>
        <td>
          This will be the status of newly imported/updated Oronjo products.
        </td>
      </tr>


      <tr valign="top">
        <th scope="row" style="text-align: right; padding-right: 20px;">
          Category
        </th>
        <td>
          <select name="cat_id" id="cat_id" class='postform'>
          <?php 
          $oronjo_cat_id = get_option($prefix.'_cat_id');
          if ($oronjo_cat_id==''){$oronjo_cat_id=1;}
          wp_dropdown_cats(0,$oronjo_cat_id,0,0,0); 
          ?>
          </select>
        </td>
        <td>
          Default category of newly imported/updated Oronjo products.
        </td>
      </tr>


      <tr valign="top">
        <th scope="row" style="text-align: right; padding-right: 20px;">
          Tag
        </th>
        <td>
          <input name="oronjo_tag" type="text" id="oronjo_tag" value="<?php echo esc_attr(get_option($prefix.'_tag')); ?>" size="45" />
        </td>
        <td>
          Default tag(s) of newly imported/updated Oronjo products. Separate tags with commas.
        </td>
      </tr>


      <tr valign="top">
        <th scope="row" style="text-align: right; padding-right: 20px;">
          # of Products
        </th>
        <td>
          <select name="oronjo_number_of_items" id="oronjo_number_of_items">
          <option <?php if(get_option($prefix.'_number_of_items') == '1')  { echo 'selected'; } ?> value="1"  >Import 1 product</option>
          <option <?php if(get_option($prefix.'_number_of_items') == '5')  { echo 'selected'; } ?> value="5"  >Import 5 products</option>
          <option <?php if(get_option($prefix.'_number_of_items') == '10') { echo 'selected'; } ?> value="10" >Import 10 products</option>
          <option <?php if(get_option($prefix.'_number_of_items') == '25') { echo 'selected'; } ?> value="25" >Import 25 products</option>
          <option <?php if(get_option($prefix.'_number_of_items') == '50') { echo 'selected'; } ?> value="50" >Import 50 products</option>
          <option <?php if(get_option($prefix.'_number_of_items') == '100'){ echo 'selected'; } ?> value="100">Import 100 products</option>
          <option <?php if(get_option($prefix.'_number_of_items') == 'all'){ echo 'selected'; } ?> value="all">Import all products</option>
          </select>
        </td> 
        <td>
          Number of products that will be imported/updated each time you hit the 'Import Oronjo Products' button.
        </td>
      </tr>


      <tr valign="top">
        <th scope="row" style="text-align: right; padding-right: 20px;">
          Price Tag
        </th>
        <td>
          <fieldset>
            <input 
                 id="use_image_tag" 
                 name="use_image_tag" 
                 type="checkbox" 
                 value="1" <?php checked( get_option($prefix.'_use_image_tag' ) ); ?>
                 > Use image   
          </fieldset>
        </td>
        <td>
          Check this box if you want to use the Oronjo price tag image. If you prefer a text link, leave this checkbox 'unchecked'.
        </td>
      </tr>


      <tr valign="top">
        <th scope="row" style="text-align: right; padding-right: 20px;">
          Product images
        </th>
        <td>
          <fieldset>
            <input 
                 id="use_images" 
                 name="use_images" 
                 type="checkbox" 
                 value="1" <?php checked( get_option($prefix.'_use_images' ) ); ?>
                 > Add product images to posts   
          </fieldset>
        </td>
        <td>
          Check this box if you want to add your product images to imported/updated products. If you do not want to include images, leave this checkbox 'unchecked'.
        </td>
      </tr>


      <tr valign="top">
        <th scope="row" style="text-align: right; padding-right: 20px;">
          Updated Products
        </th>
        <td>
          <fieldset>
            <input 
                id="updated_only" 
                name="updated_only" 
                type="checkbox" value="1" <?php checked( get_option( $prefix.'_updated_only' ) ); ?>
                > Import updated products only
          </fieldset>
        </td>
        <td>
          Process only products that have been updated on Oronjo since the last import run.
          If you would like to process all your Oronjo products, leave this checkbox 'unchecked'.  
          <p>
            It is possible to skip updating individual posts, read this 
            <a href="http://www.oronjo.nl/wiki/oronjo-wordpress-plugin.html#import-and-skip-updating-individual-posts" target="_blank">wiki entry</a> for the details.
          </p>
        </td>
      </tr>

      <?php
        if (__NAMESPACE__ == 'WPSE\selldigitalproducts') {
        ?>
          <tr valign="top">
            <th scope="row" style="text-align: right; padding-right: 20px;">
              Cart position
            </th>
            <td>
              <select name="cart_position" id="cart_position">
              <option <?php if(get_option($prefix.'_cart_position') == 'left') { echo 'selected'; } ?> value="left">Left</option>
              <option <?php if(get_option($prefix.'_cart_position') == 'right'){ echo 'selected'; } ?> value="right">Right</option>
              </select>
            </td>
            <td>
              You can position the cart <span class="dashicons dashicons-cart"></span> on the left side or right side of the screen.
            </td>
          </tr>

          <tr valign="top">
            <th scope="row" style="text-align: right; padding-right: 20px;">
              Top margin
            </th>
            <td>
              <select name="cart_top_margin" id="cart_top_margin">
              <option <?php if(get_option($prefix.'_cart_top_margin') == '10%') { echo 'selected'; } ?> value="10%">10%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '15%') { echo 'selected'; } ?> value="15%">15%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '20%') { echo 'selected'; } ?> value="20%">20%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '25%') { echo 'selected'; } ?> value="25%">25%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '30%') { echo 'selected'; } ?> value="30%">30%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '35%') { echo 'selected'; } ?> value="35%">35%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '40%') { echo 'selected'; } ?> value="40%">40%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '45%') { echo 'selected'; } ?> value="45%">45%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '50%') { echo 'selected'; } ?> value="50%">50%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '55%') { echo 'selected'; } ?> value="55%">55%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '60%') { echo 'selected'; } ?> value="60%">60%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '65%') { echo 'selected'; } ?> value="65%">65%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '70%') { echo 'selected'; } ?> value="70%">70%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '75%') { echo 'selected'; } ?> value="75%">75%</option>
              <option <?php if(get_option($prefix.'_cart_top_margin') == '80%') { echo 'selected'; } ?> value="80%">80%</option>
              </select>
            </td>
            <td>
              The amount of space above the cart.
            </td>
          </tr>

          <tr valign="top">
            <th scope="row" width="20%" style="text-align: right; padding-right: 20px;">
              License
            </th>
            <td width="25%">
              <input name="oronjo_license" type="text" id="oronjo_license" value="<?php echo esc_attr(get_option($prefix.'_license')); ?>" size="45" />
              <p>
               <?php 
                if(get_option($prefix.'_license_valid')) { 
                    echo 'This is a valid license. '; 
                  } else { 
                    echo 'This is an INVALID license. '; 
                  }
                  echo get_option($prefix.'_lic_msg_'.basename(plugin_basename( __FILE__ ), '.php')); 
               ?>
              </p>            
            </td> 
            <td width="55%">
              You can find your license on your <a href="<?php echo $oronjo_url; ?>account" target="top">Oronjo account</a> (tab 'Integrations'). You can buy a license <a href="<?php echo $oronjo_url.'p/'.$oronjo_license_product; ?>" target="top">here</a>.
              Don't know how to use your license? Read this <a href="http://www.oronjo.nl/wiki/oronjo-license-setup.html" target="_blank">wiki entry</a> for more information.

            </td>
          </tr>



      <?php
        }
      ?>



      <tr valign="top">
        <th scope="row"></th>
        <td style="padding-top: 20px;">
          <input type="submit" name="save_oronjo_settings" value="Save Settings" class="button button-primary" />
          <input type="submit" name="create_oronjo_posts" value="Import Oronjo Products" class="button" />
        </td>
        <td style="padding-top: 20px;">
          <?php
            if (__NAMESPACE__ == 'WPSE\oronjowordpressplugin') {
              echo '<span style="font-style: italic;">Please switch to our new  <a href="https://wordpress.org/plugins/sell-content/">\'Sell Content\'</a> plugin which is 100% compatible with the Oronjo Wordpress Plugin.</span>';
            }
          ?>
        </td>
      </tr>


    </table>
  </form>
</div>

<?php
  if (isset($_POST['create_oronjo_posts'])) {
    echo '  <div class="wrap">';
    create_oronjo_posts();
    echo '</div>';
  }
?>
<?php } // end Oronjo_subpanel()


function oronjo_image_tag($productId) {
  global $oronjo_url;
  return '<a href="#" class="OWAddToCart" data-product-id="'.esc_attr($productId).'"><img src="'.$oronjo_url.'live/images/'.esc_html($productId).'"></a>';
}

function oronjo_link_tag($productId, $price, $currency) {
  // Price tag.
  global $oronjo_url;
  if ((float)$price > 0) {
    $price_tag = 'Buy now for '.number_format($price, 2).' '.$currency;
  } else {
    $price_tag = 'Free download!';
  }
  return '<a href="'.$oronjo_url.'p/'.esc_html($productId).'">'.esc_html($price_tag).'</a>';
}

function oronjo_price_tag($content, $use_image_tag, $productId, $price, $currency) {
  // Tag or link?
  if ($use_image_tag == 1) {
    return $content.'<p>'.oronjo_image_tag($productId).'</p>';
  } else {
    return $content.'<p>'.oronjo_link_tag($productId, $price, $currency).'</p>';
  }
}

function oronjo_product_image($content, $use_images, $image, $userId) {
  if ($use_images == 1 and strlen($image)>0 ) {
    $image_url = 'https://oronjo-meteor.s3.amazonaws.com/prod/users/'.$userId.'/images/'.$image;
    $prod_image = '<img class="alignnone size-full" src="'.esc_attr($image_url).'" alt="'.esc_attr($image).'" />';
    return  '<p>'.$prod_image.'</p>'.$content;
  } else {
    return $content;
  }
}

function valid_oronjo_license($force=false) {
  global $wpdb, $oronjo_url;
  $oronjo_license_product = get_option('oronjo_license_'.basename(plugin_basename( __FILE__ ), '.php'));
  if (!$oronjo_license_product) {
    return;
  }
  $diff = strtotime('now') - get_option('oronjo_lic_check_'.basename(plugin_basename( __FILE__ ), '.php'));
  if ($force || $diff > 60 * 60 * 24) {
    // Check every 10 seconds, clean message.
    update_option('oronjo_lic_msg_'.basename(plugin_basename( __FILE__ ), '.php'), '');

    $oronjo_api             = $oronjo_url.'api/1';
    $oronjo_license_checked = get_option('oronjo_license_checked');
    $oronjo_license         = get_option('oronjo_license');
    // echo '<br>License<hr><pre>';
    // echo $oronjo_license_checked.'<br>';
    // echo $oronjo_license_product.' '.__NAMESPACE__.'<br>';
    // echo $oronjo_license.'<br>';
    // echo '<br><hr></pre>';

    if ($oronjo_license && $oronjo_license_product) {
      $args = array(
        'headers' => array( "origin" => get_site_url() )
      );
      $query = '/?license='.$oronjo_license.'&product='.$oronjo_license_product;
      $response = wp_remote_get($oronjo_api.$query, $args);
      $err = $response->errors;
      if ($err) {
        echo $response->get_error_message();
        die( $oronjo_api."<br>Oops, something is wrong. Please try again later...<br><hr>" );
        return false;
      }
      $response_code = $response['response']['code'];


      if( $response_code != 200 ) {
        if (is_admin() ) {
          $body = json_decode($response['body']);
          $error = http_build_query($body);
          echo '<div class="error"><p><strong>Oops, there is an error...</strong></p><p>'.$error.'</p>'; 
          echo '<p>Please send this error to <a href="mailto:support.oronjo@orangewise.com?SUBJECT=Oronjo%20Wordpress%20Plugin&amp;BODY='.$error.'">support.oronjo@orangewise.com</a>.</p></div>';
        }
        return false;
      } else {
        update_option('oronjo_lic_check_'.basename(plugin_basename( __FILE__ ), '.php'), strtotime('now'));
        $body = json_decode($response['body']);
        $license = $body->{'license'};
        $message = $body->{'message'};
        update_option('oronjo_lic_msg_'.basename(plugin_basename( __FILE__ ), '.php'), $message);
        if ($license == 'valid') {
          return true;
        } else {
          return false;
        }
      }
    } else {
      return false;
    }
  } else {
    return true;
  }
}

function validate_oronjo_license($force) {
  global $prefix;
  if (valid_oronjo_license($force)) {
    update_option($prefix.'_license_valid', true);
  } else {
    update_option($prefix.'_license_valid', false);
  }
}

function create_oronjo_posts(){
  global $wpdb, $oronjo_url, $prefix;
  $current_user        = wp_get_current_user();
  $oronjo_user_id      = $current_user->ID;
  $oronjo_oronjoapikey = get_option($prefix.'_oronjoapikey');
  $oronjo_api          = $oronjo_url.'api/1';
  $oronjo_tag          = get_option($prefix.'_tag');
  $use_image_tag       = get_option($prefix.'_use_image_tag');
  $use_images          = get_option($prefix.'_use_images');
  $post_status         = get_option($prefix.'_post_status');
  $oronjo_cat_id       = get_option($prefix.'_cat_id');
  $maxitems            = get_option($prefix.'_number_of_items');
  $updated_only        = get_option($prefix.'_updated_only');
  $allow_updates       = 'Allow Oronjo Updates';

  if ($oronjo_cat_id != '1') {
    $cat = array($oronjo_cat_id);
  }
  echo '<br><hr>';
  echo '<h2>Import Log</h2>';
  echo 'Updating Wordpress with your products from Oronjo!<br>';
  echo 'Oronjo Wordpress posts will be created with a status of '.$post_status.'.<br>';
  echo '<hr>';

  $args = array(
    'headers' => array( "origin" => get_site_url() )
  );
  $query = '/?products='.$oronjo_oronjoapikey.'&count='.$maxitems.'&updated_only='.$updated_only;
  $response = wp_remote_get( $oronjo_api.$query, $args );
  $err = $response->errors;
  if ($err) {
    echo $response->get_error_message();
    die( $oronjo_api."<br>Oops, something is wrong. Please try again later...<br><hr>" );
  }
  $response_code = $response['response']['code'];

  if( $response_code != 200 ) {
     $body = json_decode($response['body']);
     echo '<strong>Oops... There is an error:</strong><br>';
     echo $body->{'error'};
     $error = http_build_query($response);
     echo '<br>Please send this error to <a href="mailto:support.oronjo@orangewise.com?SUBJECT=Oronjo%20Wordpress%20Plugin&amp;BODY='.$error.'">support.oronjo@orangewise.com</a> <hr>';
     echo 'Response details:<pre>';
     print_r( $response );
     echo '</pre>';
  } else {
    // echo 'Response:<pre>';
    // print_r( $response );
    // echo '</pre>';
    $body = json_decode($response['body']);
    $sites = $body->{'sites'};
    $site = $sites[0];
    // echo 'Response:<pre>';
    // print_r( $site->products);
    // echo '</pre>';

    $items = $site->products;
    if (empty($items)) {
      echo 'No Oronjo products found that need to be imported/updated.<br><hr>';
    } else { 
      foreach ( $items as $item ) {
        // print_r($item);
        $productId  = sanitize_text_field($item->_id);
        $title      = $date = $categories = $content = $post_id = '';
        $title      = addslashes(trim($item->name));
        $post_name  = sanitize_title($title);
        $date       = strtotime($item->updated);
        $post_date  = gmdate('Y-m-d H:i:s', $date);
        $categories = sanitize_title($oronjo_cat_id);
        $price      = $item->price;
        $currency   = $item->currency;
        $image      = $item->image;
        $userId     = $item->userId;
        $content    = addslashes(trim($item->description));

        // Product image;
        $content = oronjo_product_image($content, $use_images, $image, $userId);

        // Price tag
        $content = oronjo_price_tag($content, $use_image_tag, $productId, $price, $currency);

        // update or insert?
        $insert_or_update_sql = $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE  meta_key = %s AND meta_value = %s ",
            '_product._id',
            $productId
          );
        $ID = $wpdb->get_var($insert_or_update_sql);

        // Allow updates
        $allow_updates_sql = $wpdb->prepare(
            "SELECT count(*) FROM $wpdb->postmeta WHERE  meta_key = %s AND post_id = %s ",
            $allow_updates,
            $ID
          );
        $allow_updates_count = $wpdb->get_var($allow_updates_sql);

        if ($ID and $allow_updates_count > 0) {
          echo "Updating $title (productId: $productId).<br>";
          $skip = false;
        } elseif ($ID and $allow_updates_count == 0) {
          echo "Skip updating $title. Custom Field '$allow_updates' not found.<br>";
          $skip = true;
        } else {
          echo "Inserting $title .<br>";
          $skip = false;
        }
        echo '<hr>';
 
        if ($skip == false) {
          // Insert/update the post.
          $post_values = array(
             'post_status'            => $post_status
            ,'post_type'              => 'post'
            ,'ID'                     => $ID
            ,'post_author'            => $oronjo_user_id
            ,'post_date'              => $post_date
            ,'post_date_gmt'          => $post_date
            ,'comment_status'         => get_option('default_comment_status')
            ,'post_category'          => $cat
            ,'tags_input'             => ''
            ,'ping_status'            => get_option('default_ping_status')
            ,'post_parent'            => 0
            ,'menu_order'             => 0
            ,'to_ping'                =>  ''
            ,'pinged'                 => ''
            ,'post_password'          => ''
            ,'post_title'             => $title
            ,'post_content'           => $content
            ,'post_content_filtered'  => ''
            ,'post_excerpt'           => ''
          );

          // print_r($post_values);
          $postId = wp_insert_post($post_values); 
          wp_set_post_terms( $postId, explode(',', $oronjo_tag));
          if (!$ID) {
            add_post_meta( $postId, '_product._id', $productId, true );
            add_post_meta( $postId, $allow_updates, 'Yes', true );
          }        
        }
      }
    }
 }
 echo 'Finished.';
 echo '<hr>';
}


function oronjo_footer() {
  global $prefix;

  if (__NAMESPACE__ == 'WPSE\selldigitalproducts') {
  ?>
<!-- Icon prototypes  -->
<div id="cartIcon" style="display: none;"><span class="dashicons dashicons-cart"></span></div>
<div id="delIcon" style="display: none;"><span class="dashicons dashicons-no"></span></div>
<div id="downloadIcon" style="display: none;"><span class="dashicons dashicons-download"></span></div>
<div id="cartIconDiv" style="top: <?php echo get_option($prefix.'_cart_top_margin'); ?>; <?php echo get_option($prefix.'_cart_position'); ?>: 0;"></div>
<div id="checkoutDiv" style="display: none;">
  <a href="#" id="checkout" class="btn btn-block btn-success btn-lg">Checkout with Paypal</a>
  <div class="sep">OR</div>
  <a href="#" data-dismiss="modal" class="btn btn-block btn-link btn-lg">Continue Shopping</a>
</div>
<!-- Cart modal  -->
<div id="cartModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-content cart-modal">

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h1 class="modal-title text-center">Cart</h1>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <div class="modal-body">
            <div class="cart-contents">
            </div> 
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      <p class="modal-title text-center"><a href="#" class="btn btn-link OWOpenCartOnOronjo">Powered by Oronjo</a></p>
    </div>

  </div> 
</div> 
  <?php
}}

function oronjo_setup()
{
   global $oronjo_is_setup;
   if($oronjo_is_setup) {
      return;
   } 
}


function oronjo_add_action_links ( $links ) {
  $mylinks = array(
  '<a href="' . admin_url('options-general.php?page=sell-digital-products.php') . '">Settings</a>',
  );
  return array_merge( $mylinks , $links );
}


function oronjo_admin_menu() {
   if (function_exists('add_options_page')) {
     oronjo_setup();
     add_options_page('Sell digital products Settings', 'Sell digital products', 8, basename(__FILE__), __NAMESPACE__.'\\oronjo_subpanel');
     add_filter('plugin_action_links_' . plugin_basename(__FILE__), __NAMESPACE__.'\\oronjo_add_action_links');
  }
}

function oronjo_scripts_and_styles() {
  wp_register_script ('alljs' ,  plugins_url('/js/all.js',  __FILE__),       array( 'jquery' ) );
  wp_register_style  ('allcss' , plugins_url('/css/all.css',  __FILE__) );

  wp_enqueue_style   ('dashicons' );
  wp_enqueue_script  ('alljs' );
  wp_enqueue_style   ('allcss' );
}


add_action('admin_menu',         __NAMESPACE__.'\\oronjo_admin_menu'); 
add_action('wp_enqueue_scripts', __NAMESPACE__.'\\oronjo_scripts_and_styles');
add_action('wp_footer',          __NAMESPACE__.'\\validate_oronjo_license');
add_action('wp_footer',          __NAMESPACE__.'\\oronjo_footer');
?>