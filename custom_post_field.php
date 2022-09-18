<?php
/**
 * Plugin Name:       Sponsors
 * Description:       Add logos automatically by shortcode
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Amen Khaled
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       custom-logo
 * Domain Path:       /languages
 */

class custom_sponsors_type
{

     function __construct()
    {
        add_action('wp_enqueue_scripts',[$this,'add_shortcode_scripts']);
        add_action( 'init', [$this,'custom_sponsors_field'], 0 );
        add_shortcode( 'sponsors_list', [$this,'create_shortcode_sponsors_post_type'] );

        add_action( 'init', [$this,'create_sponsors_page'] );
        add_action( 'add_meta_boxes', [$this,'CreateUrlfield' ]) ;
        add_action('save_post',[$this,'save_sponsor_url']);



    }
    public function add_shortcode_scripts(){
         wp_enqueue_style('bootstrap-css','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',array(),'3.3.6',false);
         wp_enqueue_script('bootstrap-js','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js',array('jquery'),'3.3.6',true);
    }
    public function custom_sponsors_field(){

      $labels = array(
          'name'                => _x( 'sponsors', 'Post Type General Name', 'custom-logo' ),
          'singular_name'       => _x( 'Sponsors', 'Post Type Singular Name', 'custom-logo' ),
          'menu_name'           => __( 'Sponsors', 'custom-logo' ),
          'parent_item_colon'   => __( 'Parent Sponsor', 'custom-logo' ),
          'all_items'           => __( 'All Sponsors', 'custom-logo' ),
          'view_item'           => __( 'View Sponsor', 'custom-logo' ),
          'add_new_item'        => __( 'Add New Sponsor', 'custom-logo' ),
          'add_new'             => __( 'Add New', 'custom-logo' ),
          'edit_item'           => __( 'Edit Sponsor', 'custom-logo' ),
          'update_item'         => __( 'Update Sponsor', 'custom-logo' ),
          'search_items'        => __( 'Search Sponsor', 'custom-logo' ),
          'not_found'           => __( 'Not Found', 'custom-logo' ),
          'not_found_in_trash'  => __( 'Not found in Trash', 'custom-logo' ),
      );

// Set other options for Custom Post Type

      $args = array(
          'label'               => __( 'sponsors', 'custom-logo' ),
          'description'         => __( 'Sponsor news and reviews', 'custom-logo' ),
          'labels'              => $labels,
          // Features this CPT supports in Post Editor
          'supports' => array(
              'title',
              'editor',
              'excerpt',
              'thumbnail',
              'custom-fields',
              'revisions'
          ),
          // You can associate this CPT with a taxonomy or custom taxonomy.
          'taxonomies'          => array( 'genres' ),
          /* A hierarchical CPT is like Pages and can have
          * Parent and child items. A non-hierarchical CPT
          * is like Posts.
          */
          'hierarchical'        => false,
          'public'              => true,
          'show_ui'             => true,
          'show_in_menu'        => true,
          'show_in_nav_menus'   => true,
          'show_in_admin_bar'   => true,
          'menu_position'       => 5,
          'can_export'          => true,
          'has_archive'         => true,
          'exclude_from_search' => false,
          'publicly_queryable'  => true,
          'capability_type'     => 'post',
          'show_in_rest' => true,

      );

      // Registering your Custom Post Type
      register_post_type( 'sponsors', $args );

  }
    // >> Create Shortcode to Display Sponsors Post Types
    /*Create custom MetaBox*/
    function CreateUrlfield()
    {
        $screen = 'sponsors';
        add_meta_box('my-meta-box-id','Sponsors Url',[$this,'displayUrl'],$screen,'normal','high');
    }

    /*Display PostMeta*/
      public   function displayUrl($post)
    {
        global $wbdb;
        $metaUrl = 'metaUrl';
        $displayMetaUrl = get_post_meta( $post->ID,$metaUrl, true );
        ?>
        <h2>Sponsors Url</h2>
        <label for="my_meta_box_text">Sponsors Url</label>
        <input type="url" class="form-control" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo $displayMetaUrl;?>" />
        <?php
    }

    /*Save Post Meta*/
   public function save_sponsor_url($post)
    {
        $url = $_POST['my_meta_box_text'];
        update_post_meta(  $post, 'metaUrl', $url);
    }
    public function create_shortcode_sponsors_post_type($attr){
        extract(shortcode_atts(array(
            'logo' => '',
            'title'=> '',
            'description' => '',

        ),$attr)) ;
        $$result = "";
        $args = array(
            'post_type' => 'sponsors',
            'publish_status' => 'published',
             'orderby' => 'parent' ,
            'posts_per_page' => 10
        );

        $query = new WP_Query($args);

        if($query->have_posts()) :
       ?>

        <div class="container">
            <div class="row">
        <?php
            while($query->have_posts()) :

                $query->the_post() ;

                ?>

                <?php

                $result .='<div class="col-md-3"><div class="sponsors-item text-center">' . '<div class="sponsors-poster">' .

                    (($logo == true) ?     get_the_post_thumbnail($post->ID,'full', array('class' => 'logo-responsive')) : '')
                     . '</div>' .'<div class="sponsors-name">' .
                    (($title == true) ?      get_the_title()   : '')


                . '</div>' . '<div class="sponsors-desc">' ;

                if (($description == true)){
                    $result .= get_the_content();
                    $result .= '<a href="'. get_post_meta(get_the_ID(), 'metaUrl', true) .'" >Read more</a>';

                }



                   $result .=  '</div>' . '</div> </div>';





            endwhile;

            wp_reset_postdata();
?>
            </div>
        </div>
                <?php
        endif;

        return $result;


    }
    public function create_sponsors_page(){
         if (get_page_by_title("Sponsors") ==  NULL ){
             $post_arr_data = array(
                 "post_title" => "Sponsors",
                 "post_name" => "sponsors_page",
                 "post_status" => "publish",
                 "post_content" => "[sponsors_list logo='true' title='true' description='true']",
                 "post_type" => "page"
             );

             wp_insert_post($post_arr_data);
         }

     }


}
$active = new custom_sponsors_type();