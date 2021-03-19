<?php
  /*
  Plugin Name: Add Jobs
  Plugin URI: https://jobs.com/
  Description: Declares a plugin that will create a custom post type displaying jobs.
  Version: 1.0
  Author: Litty thomas
  Author URI: http://litty4ever.com/
  License: GPLv2
  */
  class JobsCustomType{
    // public function __construct(){
    //   add_action( 'init', array($this,'create_movie_review') );
    // }

    public function create_jobs() {
    	register_post_type( 'jobs',
    		array(
    			'labels' => array(
    				'name' => 'Jobs',
    				'singular_name' => 'Jobs',
    				'add_new' => 'Add New',
    				'add_new_item' => 'Add New Jobs',
    				'edit' => 'Edit',
    				'edit_item' => 'Edit Jobs',
    				'new_item' => 'New Jobs',
    				'view' => 'View',
    				'view_item' => 'View Jobs',
    				'search_items' => 'Search Jobs',
    				'not_found' => 'No Jobs found',
    				'not_found_in_trash' => 'No Jobs found in Trash',
    				'parent' => 'Parent Jobs'
    			),

    			'public' => true,
    			'menu_position' => 15,
          'supports' => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
			    'taxonomies' => array( '' ),
    			'menu_icon' => 'dashicons-groups',
    			'has_archive' => true
    		)
    	);
    }
  }
  class JobsMetabox extends JobsCustomType{
    public function __construct(){
      add_action( 'init', array($this,'create_jobs') );
      add_action( 'admin_init', array($this,'my_admin' ));
      // For calling save_custom_meta_box
      add_action("save_post", array($this,"save_custom_meta_box"));
      add_filter('the_content',array($this,'display_front_end'),20,1);
      // add_action('wp_enqueue_scripts', array($this,'Style_contents'));
      add_action('admin_menu', array($this,'add_jobs_submenu_example'));
    }
    public function my_admin() {
    	add_meta_box( 'job_meta_box',
    		'Job Details',
    		array($this, 'display_job_meta_box'),
    		'jobs', 'normal', 'high'
    	);
    }

    public function display_job_meta_box( $object ) {
    	// Retrieve current jobs
      wp_nonce_field(basename(__FILE__), "meta-box-nonce");
      ?>
      <!-- The contents within Custom metabox -->
      <div>
        <label for="meta-box-title">Job Title</label>
        <!-- value of the input is fetched using get_post_meta -->
        <input name="meta-box-title" type="text" value="<?php echo esc_html(get_post_meta($object->ID, "_meta-box-title", true)); ?>">
        <br><br>
        <!-- For email -->
        <label for="meta-box-email">Email &nbsp &nbsp &nbsp</label>

        <input name="meta-box-email" type="email" value="<?php echo esc_html(get_post_meta($object->ID, "_meta-box-email", true)); ?>">
        <br>
        <br>
        <label for="meta-box-date">Date &nbsp &nbsp &nbsp </label>
        <input name="meta-box-date" type="date" value="<?php echo esc_html(get_post_meta($object->ID, "_meta-box-date", true)); ?>">


      </div>
      <?php
    }
    // for saving contents of metabox
    function save_custom_meta_box($post_id){
      //write_log('stringfff');
      // For verifying using wp_verify_nonce, Verifies that a correct security nonce was used with time limit.
      if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;
      // To check the user have the capability to edit
      if(!current_user_can("edit_post", $post_id))
        return $post_id;
      // aborting the logic that is to follow beneath the condition, if doing autosave = true
      if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;
      $meta_box_title_value = "";
      $meta_box_email_value = "";
      $meta_box_date_value = "";
      // checking for the condition if meta-box-text is posted
      if(isset($_POST["meta-box-title"])){
        //Sanitized data is fetched to a variable which is posted
        $meta_box_title_value = sanitize_text_field($_POST["meta-box-title"]);
      }
      // Updates a post meta field based on the given post ID.
      update_post_meta($post_id, "_meta-box-title", $meta_box_title_value);
      // checking for the condition if meta-box-checkbox is posted
      if(isset($_POST["meta-box-email"])){
          //Sanitized data is fetched to a variable which is posted
          $meta_box_email_value = sanitize_text_field($_POST["meta-box-email"]);
      }
      // Updates a post meta field based on the given post ID.
      update_post_meta($post_id, "_meta-box-email", $meta_box_email_value);
      if(isset($_POST["meta-box-date"])){
          //Sanitized data is fetched to a variable which is posted
          $meta_box_date_value = sanitize_text_field($_POST["meta-box-date"]);
      }
      // Updates a post meta field based on the given post ID.
      update_post_meta($post_id, "_meta-box-date", $meta_box_date_value);
    }
    public function display_front_end($val){
      global $post;
      $test=$title=$email=$date="";
      //write_log('df');
      // Retrieves a post meta field for the given post ID.
      $title = get_post_meta($post->ID, "_meta-box-title", true);
      // Retrieves a post meta field for the given post ID.
      $email = get_post_meta($post->ID, '_meta-box-email', true);
      // Retrieves a post meta field for the given post ID.
      $date = get_post_meta($post->ID, '_meta-box-date', true);
      // Content which is displayed
      $test = "<div><h2 class='Add_Jobs'>JOB ADDED</h2> <p>Job Type : $title</p><p> Email : $email </p><p> Date : $date </p> </div>";
      // value returned for displaying
      return $val . $test;

    }
    // public function Style_contents() {
    //   wp_enqueue_style( 'slider', get_template_directory_uri() . '/css/slide.css',false,'1.1','all');
    //
    // }
    function add_jobs_submenu_example(){

     add_submenu_page(
                     'edit.php?post_type=jobs', //$parent_slug
                     'Admin ',  //$page_title
                     'Settings',        //$menu_title
                     'manage_options',           //$capability
                     'settings_submenu',//$menu_slug
                     array($this,'jobs_submenu_render_page')//$function
     );
    }

//add_submenu_page callback function

    function jobs_submenu_render_page($result) {
      wp_nonce_field(basename(__FILE__), "meta-box-nonce");
      ?>
      <div class="container">
        <h1>Admin Page</h1>
        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row"><label for="name">Organization name</label></th>
              <td> <input type="text" name="name" value=""> </td>
            </tr>
            <tr>
              <th scope="row"><label for="content">Description</label></th>
              <td> <textarea name="content" rows="8" cols="80"></textarea> </td>
            </tr>
            <tr>
              <th scope="row"><label for="no-of-jobs">Number of vacancies</label></th>
              <td> <input type="number" name="name" value=""> </td>
            </tr>
            <tr>
              <th scope="row"> <label for="title">Show title</label> </th>
              <td> <input type="radio" name="title" > <label for="title-only">Show title</label> <input type="radio" name="title" > <label for="title-content">Show title and content</label> </td>
            </tr>
            <tr>
              <th scope="row"><label for="show-email">Show email</label></th>
              <td> <input name="show-email" type="checkbox" value="true"> </td>
            </tr>
            <tr>
              <th scope="row"><label for="expiry-date">Expiry date</label></th>
              <td> <input type="date" name="expiry-date" value=""> </td>
            </tr>
            <tr>
              <th scope="row"><label for="name">Set color</label></th>
              <td> <input type="color" name="color" value=""> </td>
            </tr>
            <tr>
              <th scope="row"> <input type="submit" name="submit" value="Submit"> </th>

            </tr>
          </tbody>

        </table>


      </div>
      <?php
    }

  }
  new JobsMetabox();
  // For debugging purpose
  if (!function_exists('write_log')) {
  	function write_log ( $log )  {
  		if ( true === WP_DEBUG ) {
  			if ( is_array( $log ) || is_object( $log ) ) {
  				error_log( print_r( $log, true ) );
  			} else {
  				error_log( $log );
  			}
  		}
  	}
  }
?>
