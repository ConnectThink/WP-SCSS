<?php
class Wp_Scss_Settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'WP-SCSS', 
            'manage_options', 
            'wpscss_options', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'wpscss_options' );
        ?>
        <div class="wrap">
            <h2>WP-SCSS Settings</h2>   
            <p>
              <span class="version">Version <em><?php echo get_option('wpscss_version'); ?></em>
              <br/>
              <span class="author">By: <a href="http://connectthink.com" target="_blank">Connect Think</a></span>
              <br/>
              <span class="repo">Help & Issues: <a href="https://github.com/ConnectThink/WP-SCSS" target="_blank">Github</a></span>
            </p>        
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'wpscss_options_group' );   
                do_settings_sections( 'wpscss_options' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'wpscss_options_group',    // Option group
            'wpscss_options',          // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        // Paths to Directories
        add_settings_section(
            'wpscss_paths_section',             // ID
            'Configure Paths',                  // Title
            array( $this, 'print_paths_info' ), // Callback
            'wpscss_options'                    // Page
        );  

        add_settings_field(
            'wpscss_scss_dir',                     // ID
            'Scss Location',                       // Title 
            array( $this, 'input_text_callback' ), // Callback
            'wpscss_options',                      // Page
            'wpscss_paths_section',                // Section
            array(                                 // args
                'name' => 'scss_dir',
            )
        );      

        add_settings_field(
            'wpscss_css_dir',                       // ID
            'CSS Location',                         // Title 
            array( $this, 'input_text_callback' ),  // Callback
            'wpscss_options',                       // Page
            'wpscss_paths_section',                 // Section
            array(                                  // args
                'name' => 'css_dir',
            )
        );

        // Compiling Options
        add_settings_section(
            'wpscss_compile_section',             // ID
            'Compiling Options',                  // Title
            array( $this, 'print_compile_info' ), // Callback
            'wpscss_options'                      // Page
        );  

        add_settings_field(
            'Compiling Mode',                        // ID
            'Compiling Mode',                        // Title
            array( $this, 'input_select_callback' ), // Callback
            'wpscss_options',                        // Page
            'wpscss_compile_section',                // Section
            array(                                   // args
                'name' => 'compiling_options',
                'type' => apply_filters( 'wp_scss_compiling_modes',
                    array(
                        'Leafo\ScssPhp\Formatter\Expanded'   => 'Expanded',
                        'Leafo\ScssPhp\Formatter\Nested'     => 'Nested',
                        'Leafo\ScssPhp\Formatter\Compressed' => 'Compressed',
                        'Leafo\ScssPhp\Formatter\Compact'    => 'Compact',
                        'Leafo\ScssPhp\Formatter\Crunched'   => 'Crunched',
                        'Leafo\ScssPhp\Formatter\Debug'      => 'Debug'
                    )
                )
            )
        );   
        
        add_settings_field(
	        'Source Map Mode',					   // ID
	        'Source Map Mode',					   // Title
	        array( $this, 'input_select_callback' ), // Callback
	        'wpscss_options',						   // Page
	        'wpscss_compile_section',				   // Section
	        array(									   // args
		        'name' => 'sourcemap_options',
		        'type' => apply_filters( 'wp_scss_sourcemap_modes' ,
		        	array(
			        	'SOURCE_MAP_NONE' 	=> 'None',
			        	'SOURCE_MAP_INLINE'	=> 'Inline',
			        	'SOURCE_MAP_FILE'	=> 'File'
		        	)
		        )
	        )
        );              

        add_settings_field(
            'Error Display',                         // ID
            'Error Display',                         // Title
            array( $this, 'input_select_callback' ), // Callback
            'wpscss_options',                        // Page
            'wpscss_compile_section',                // Section   
            array(                                   // args
                'name' => 'errors',
                'type' => apply_filters( 'wp_scss_error_diplay',
                    array(
                        'show'           => 'Show in Header',
                        'show-logged-in' => 'Show to Logged In Users',
                        'hide'           => 'Print to Log',
                    )								
                )
            )
        );      

        // Enqueuing Options
        add_settings_section(
            'wpscss_enqueue_section',             // ID
            'Enqueuing Options',                  // Title
            array( $this, 'print_enqueue_info' ), // Callback
            'wpscss_options'                      // Page
        );  

        add_settings_field(
            'Enqueue Stylesheets',                     // ID
            'Enqueue Stylesheets',                     // Title
            array( $this, 'input_checkbox_callback' ), // Callback
            'wpscss_options',                          // Page
            'wpscss_enqueue_section',                  // Section      
            array(                                     // args
                'name' => 'enqueue'
            )
        );      
                   
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {
        foreach( ['scss_dir', 'css_dir'] as $dir ){
            if( !empty( $input[$dir] ) ) {
                $input[$dir] = sanitize_text_field( $input[$dir] );

                // Add a trailing slash if not already present
                if(substr($input[$dir], -1) != '/'){
                    $input[$dir] .= '/';
                }
            }
        }
			
        return $input;
    }

    /** 
     * Print the Section text
     */
    public function print_paths_info() {
        print 'Add the paths to your directories below. Paths should start with the root of your theme. example: "/library/scss/"';
    }
    public function print_compile_info() {
        print 'Choose how you would like SCSS and source maps to be compiled and how you would like the plugin to handle errors';
    }
    public function print_enqueue_info() {
        print 'WP-SCSS can enqueue your css stylesheets in the header automatically.';
    }

    /** 
	 * Text Fields' Callback
     */
    public function input_text_callback( $args ) {
        printf(
            '<input type="text" id="%s" name="wpscss_options[%s]" value="%s" />',
            esc_attr( $args['name'] ), esc_attr( $args['name'] ), esc_attr( isset($this->options[$args['name']]) ? $this->options[$args['name']] : '' )
        );
    }

    /** 
     * Select Boxes' Callbacks
     */
    public function input_select_callback( $args ) {
        $this->options = get_option( 'wpscss_options' );  
        
        $html = sprintf( '<select id="%s" name="wpscss_options[%s]">', esc_attr( $args['name'] ), esc_attr( $args['name'] ) );  
            foreach( $args['type'] as $value => $title ) {
                $html .= '<option value="' . esc_attr( $value ) . '"' . selected( isset($this->options[esc_attr( $args['name'] )]) ? $this->options[esc_attr( $args['name'] )] : '', esc_attr( $value ), false) . '>' . esc_attr( $title ) . '</option>';
            }
        $html .= '</select>';  
      
        echo $html;  
    }

    /** 
     * Checkboxes' Callbacks
     */
    public function input_checkbox_callback( $args ) {  
        $this->options = get_option( 'wpscss_options' );  
        
        $html = '<input type="checkbox" id="' . esc_attr( $args['name'] ) . '" name="wpscss_options[' . esc_attr( $args['name'] ) . ']" value="1"' . checked( 1, isset( $this->options[esc_attr( $args['name'] )] ) ? $this->options[esc_attr( $args['name'] )] : 0, false ) . '/>';   
        $html .= '<label for="' . esc_attr( $args['name'] ) . '"></label>';
      
        echo $html;  
    } 

}

