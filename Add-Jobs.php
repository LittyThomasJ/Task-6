<?php
  /*
  Plugin Name: Add Jobs
  Plugin URI: https://jobs.com/
  Description: Declares a plugin that will create a custom post type displaying jobs.
  Version: 1.1
  Author: Litty thomas
  Author URI: http://litty4ever.com/
  License: GPLv2
  */
  	if( !defined('ABSPATH') ) : exit(); endif;

	  /**
	   * Define plugin constants
	   */
  define( 'MYPLUGIN_PATH', trailingslashit( plugin_dir_path(__FILE__) ) );
  define( 'MYPLUGIN_URL', trailingslashit( plugins_url('/', __FILE__) ) );
  //require_once MYPLUGIN_PATH . 'Settings/settings.php';
  // class for creating cutsom post type
  class JobsCustomType{
  	// Function for creating custom post type for jobs 
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
  // Class for creating the metabox inside the custom post type
  class JobsMetabox extends JobsCustomType{
	// function for creating metabox
    public function my_admin() {
    	// Adding metabox
    	add_meta_box( 'job_meta_box',
    		'Job Details',
    		array($this, 'display_job_meta_box'),
    		'jobs', 'normal', 'high'
    	);
    }
    // Function for displaying metabox
    public function display_job_meta_box( $object ) {
    	// Validating contents using nonce field 
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
    // Function for saving contents of metabox
    function save_custom_meta_box($post_id){
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

  }
  // Class for creating Settings page inherits 
  class JobsSettings extends JobsMetabox{
  	// Initialized usng constructor
    public function __construct(){
      add_action( 'init', array($this,'create_jobs') );
      add_action( 'admin_init', array($this,'my_admin' ));
      // For calling save_custom_meta_box
      add_action("save_post", array($this,"save_custom_meta_box"));
      add_filter('the_content',array($this,'display_front_end'),20,1);
      // add_action('wp_enqueue_scripts', array($this,'Style_contents'));
      add_action('admin_menu', array($this,'add_jobs_submenu_example'));
      add_action( 'admin_init', array($this,'myplugin_settings_init' ));
    }
    // Function for adding submenu 'Settings'
    function add_jobs_submenu_example(){

     add_submenu_page(
                     'edit.php?post_type=jobs', //$parent_slug
                     'Admin Page',  //$page_title
                     'Settings',        //$menu_title
                     'manage_options',           //$capability
                     'myplugin-settings-page',//$menu_slug
                     array($this,'jobs_submenu_render_page')//$function
     );
    }

	//add_submenu_page callback function
    function jobs_submenu_render_page($result) {
      ?>
      <div class="container">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
          <?php
              // security field
              settings_fields( 'myplugin-settings-page' );

              // output settings section here
              do_settings_sections('myplugin-settings-page');

              // save settings button
              submit_button( 'Save Settings' );
          ?>
        </form>
      </div>
      <?php
    }
    // Function for creating settings page contents
    function myplugin_settings_init() {

      // Setup settings section
      add_settings_section(
          'myplugin_settings_section',
          '',
          '',
          'myplugin-settings-page'
      );

      // Register field for organization name
      register_setting(
          'myplugin-settings-page',
          'myplugin_settings_organization_name_field',
          array(
              'type' => 'string',
              'sanitize_callback' => 'sanitize_text_field',
              'default' => ''
          )
      );

      // Add field for organization name
      add_settings_field(
          'myplugin_settings_organization_name_field',
          __( 'Organization name', 'my-plugin' ),
          array($this,'myplugin_settings_organization_name_field_callback'),
          'myplugin-settings-page',
          'myplugin_settings_section'
      );
      // Registe field for description
	    register_setting(
	        'myplugin-settings-page',
	        'myplugin_settings_description_field',
	        array(
	            'type' => 'string',
	            'sanitize_callback' => 'sanitize_textarea_field',
	            'default' => ''
	        )
	    );

	     // Add field for description
	     add_settings_field(
	        'myplugin_settings_description_field',
	        __( 'Description', 'my-plugin' ),
	        array($this,'myplugin_settings_description_field_callback'),
	        'myplugin-settings-page',
	        'myplugin_settings_section'
	    );
	     // Register vacancy field
      register_setting(
          'myplugin-settings-page',
          'myplugin_settings_vacancy_field',
          array(
              'type' => 'int',
              'sanitize_callback' => 'sanitize_text_field',
              'default' => ''
          )
      );

      // Add vacancy fields
      add_settings_field(
          'myplugin_settings_vacancy_field',
          __( 'Number of Vacancies', 'my-plugin' ),
          array($this,'myplugin_settings_vacancy_field_callback'),
          'myplugin-settings-page',
          'myplugin_settings_section'
      );
      // Register title visibility field
    register_setting(
        'myplugin-settings-page',
        'myplugin_settings_title_visibility_field',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add title visibility fields
    add_settings_field(
        'myplugin_settings_title_visibility_field',
        __( 'Display options', 'my-plugin' ),
        array($this,'myplugin_settings_title_visibility_field_callback'),
        'myplugin-settings-page',
        'myplugin_settings_section'
    );
	//  Register email visibility field
        register_setting(
        'myplugin-settings-page',
        'myplugin_settings_email_visibilty_field',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add email visibility fields
    add_settings_field(
        'myplugin_settings_email_visibilty_field',
        __( 'Display options', 'my-plugin' ),
        array($this,'myplugin_settings_email_visibilty_field_callback'),
        'myplugin-settings-page',
        'myplugin_settings_section'
    );
     // Register expiry date field
      register_setting(
          'myplugin-settings-page',
          'myplugin_settings_date_field',
          array(
              'type' => 'string',
              'sanitize_callback' => 'sanitize_text_field',
              'default' => ''
          )
      );

      // Add expiry date fields
      add_settings_field(
          'myplugin_settings_date_field',
          __( 'Expiry date ', 'my-plugin' ),
          array($this,'myplugin_settings_date_field_callback'),
          'myplugin-settings-page',
          'myplugin_settings_section'
      );
      // Register color field
      register_setting(
          'myplugin-settings-page',
          'myplugin_settings_color_field',
          array(
              'type' => 'string',
              'sanitize_callback' => 'sanitize_text_field',
              'default' => ''
          )
      );

      // Add color fields
      add_settings_field(
          'myplugin_settings_color_field',
          __( 'Choose a color', 'my-plugin' ),
          array($this,'myplugin_settings_color_field_callback'),
          'myplugin-settings-page',
          'myplugin_settings_section'
      );
    }
    // callback function for organization name
    function myplugin_settings_organization_name_field_callback() {
    	// Retrieving values from input field using get_option()
      $myplugin_organization_name_field = get_option('myplugin_settings_organization_name_field');
      ?>
      <input type="text" name="myplugin_settings_organization_name_field" class="regular-text" value="<?php echo isset($myplugin_organization_name_field) ? esc_attr( $myplugin_organization_name_field ) : ''; ?>" />
      <?php
    }
    // callback function for description field
	function myplugin_settings_description_field_callback() {
		// Retrieving values from input field using get_option()
	    $myplugin_description_field = get_option('myplugin_settings_description_field');
	    ?>
	    <textarea name="myplugin_settings_description_field" class="regular-text" rows="5"><?php echo isset($myplugin_description_field) ? esc_textarea( $myplugin_description_field ) : ''; ?></textarea>
	    <?php
	}
	//// callback function for vacancy field
	function myplugin_settings_vacancy_field_callback() {
		// Retrieving values from input field using get_option()
      $myplugin_vacancy_field = get_option('myplugin_settings_vacancy_field');
      ?>
      <input type="number" name="myplugin_settings_vacancy_field" class="regular-text" value="<?php echo isset($myplugin_vacancy_field) ? esc_attr( $myplugin_vacancy_field ) : ''; ?>" min=1 max=100/>
      <?php
    }
    // callback function for title visibility
	function myplugin_settings_title_visibility_field_callback() {
		// Retrieving values from input field using get_option()
	    $myplugin_title_visibility_field = get_option( 'myplugin_settings_title_visibility_field' );
	    ?>
	    <label for="value1">
	        <input type="radio" name="myplugin_settings_title_visibility_field" value="value1" <?php checked( 'value1', $myplugin_title_visibility_field ); ?>/> Title only
	    </label>
	    <label for="value2">
	        <input type="radio" name="myplugin_settings_title_visibility_field" value="value2" <?php checked( 'value2', $myplugin_title_visibility_field ); ?>/> Title and contents
	    </label>
	    <?php
	}
	// // callback function for email visibility
	function myplugin_settings_email_visibilty_field_callback() {
		// Retrieving values from input field using get_option()
      $myplugin_email_visibilty_field = get_option('myplugin_settings_email_visibilty_field');
      ?>
      <input type="checkbox" name="myplugin_settings_email_visibilty_field" value="1" <?php checked(1, $myplugin_email_visibilty_field, true); ?> />Show email
      <?php
    }
    // callback function for date field
    function myplugin_settings_date_field_callback() {
    	// Retrieving values from input field using get_option()
      $myplugin_date_field = get_option('myplugin_settings_date_field');
      ?>
      <input type="date" name="myplugin_settings_date_field" class="regular-text" value="<?php echo isset($myplugin_date_field) ? esc_attr( $myplugin_date_field ) : ''; ?>" />
      <?php
    }
    // callback function for color field
    function myplugin_settings_color_field_callback() {
    	// Retrieving values from input field using get_option()
      $myplugin_color_field = get_option('myplugin_settings_color_field');
      ?>
      <input type="color" name="myplugin_settings_color_field" class="regular-text" value="<?php echo isset($myplugin_color_field) ? esc_attr( $myplugin_color_field ) : ''; ?>" />
      <?php
    }
    // Function for displaying contents in front end
    public function display_front_end($val){
      global $post;
      // Initialzing variables with null values
      $test=$title=$email=$date=$myplugin_organization_name_field=$myplugin_description_field=$myplugin_vacancy_field = $myplugin_email_visibilty_field = $myplugin_title_visibility_field =$myplugin_date_field ="";
      $content = "<div>";
      // Terniary operation is done with retrieving values
      (empty(get_post_meta($post->ID, "_meta-box-title", true))) ? '' : ($title = '<p>Job Type : ' . get_post_meta($post->ID, "_meta-box-title", true) . '</p>') ;
      $date =  get_post_meta($post->ID, '_meta-box-date', true);
      (empty(get_post_meta($post->ID, '_meta-box-email', true))) ? '' : $email = '<p>Email : ' . get_post_meta($post->ID, '_meta-box-email', true) . '</p>';
      (empty(get_option('myplugin_settings_organization_name_field'))) ? '' : $myplugin_organization_name_field = '<p>Organization name : ' . get_option('myplugin_settings_organization_name_field') . '</p>';
      (empty(get_option('myplugin_settings_description_field'))) ? '' : $myplugin_description_field = '<p>Description : ' . get_option('myplugin_settings_description_field') . '</p>';
      (empty(get_option('myplugin_settings_vacancy_field')))? '' : $myplugin_vacancy_field = '<p>Number of vacancy : ' . get_option('myplugin_settings_vacancy_field') . '</p>';
      $myplugin_email_visibilty_field = get_option('myplugin_settings_email_visibilty_field');
      $myplugin_title_visibility_field = get_option( 'myplugin_settings_title_visibility_field' );
      $myplugin_date_field = get_option('myplugin_settings_date_field');
      ($myplugin_email_visibilty_field == 1) ? $email = $email : $email ='';
      // Checking whether date field is empty or not
      if(!empty($date) && !empty($myplugin_date_field)){
        if ($date >= $myplugin_date_field) {
        	// If date field in metabox is greater than date field in settings is page, job expired
        	$content .= "<p> Job Expired </p>";
        } else{
        	// retrieving data with terniary operation 
          (empty(get_post_meta($post->ID, '_meta-box-date', true))) ? '' : $date = '<p>Last date :'. $date .'</p>';
          // Terniary operation to find whether the visbility field checked for title oly or not
        	($myplugin_title_visibility_field == 'value1') ? ($content .= "$title") : ($content .= "$title $myplugin_organization_name_field  $myplugin_description_field $myplugin_vacancy_field $date $email ");
        }
      } else{
      	$content .= "<p>Nothing to show</p>";
      }
      $content .= "</div>";
      return $val . $content;

    }
  }
  // Object created
  new JobsSettings();
?>
