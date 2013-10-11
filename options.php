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
            <?php screen_icon(); ?>
            <h2>WP-SCSS Settings</h2>   
            <p>
              <span class="version">Version <em><?php echo get_option('wpscss_version'); ?></em>
              <br/>
              <span class="author">By: <a href="http://connectthink.com">Connect Think</a></span>
              <br/>
              <span class="repo">Help & Issues: <a href="#">Github</a></span>
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
            'wpscss_options_group', // Option group
            'wpscss_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        // Paths to Directories
        add_settings_section(
            'wpscss_paths_section', // ID
            'Configure Paths', // Title
            array( $this, 'print_paths_info' ), // Callback
            'wpscss_options' // Page
        );  
        add_settings_field(
            'wpscss_scss_dir', // ID
            'Scss Location', // Title 
            array( $this, 'scss_dir_callback' ), // Callback
            'wpscss_options', // Page
            'wpscss_paths_section' // Section           
        );      
        add_settings_field(
            'wpscss_css_dir', 
            'CSS Location', 
            array( $this, 'css_dir_callback' ), 
            'wpscss_options', 
            'wpscss_paths_section'
        );

        // Compiling Options
        add_settings_section(
            'wpscss_compile_section', // ID
            'Compiling Options', // Title
            array( $this, 'print_compile_info' ), // Callback
            'wpscss_options' // Page
        );  
        add_settings_field(
            'Compiling Mode', 
            'Compiling Mode', 
            array( $this, 'compiling_mode_callback' ), // Callback
            'wpscss_options', // Page
            'wpscss_compile_section' // Section           
        );      
        add_settings_field(
            'Error Display', 
            'Error Display', 
            array( $this, 'errors_callback' ), // Callback
            'wpscss_options', // Page
            'wpscss_compile_section' // Section   
        );            

        // Compiling Options
        add_settings_section(
            'wpscss_compile_section', // ID
            'Compiling Options', // Title
            array( $this, 'print_compile_info' ), // Callback
            'wpscss_options' // Page
        );  
        add_settings_field(
            'Compiling Mode', 
            'Compiling Mode', 
            array( $this, 'compiling_mode_callback' ), // Callback
            'wpscss_options', // Page
            'wpscss_compile_section' // Section           
        );      
        add_settings_field(
            'Error Display', 
            'Error Display', 
            array( $this, 'errors_callback' ), // Callback
            'wpscss_options', // Page
            'wpscss_compile_section' // Section   
        );            
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {

        if( !empty( $input['wpscss_scss_dir'] ) )
            $input['wpscss_scss_dir'] = sanitize_text_field( $input['wpscss_scss_dir'] );

        if( !empty( $input['wpscss_css_dir'] ) )
            $input['wpscss_css_dir'] = sanitize_text_field( $input['wpscss_css_dir'] );

        return $input;
    }

    /** 
     * Print the Section text
     */
    public function print_paths_info() {
        print 'Add the paths to your directories below. Paths should start with the root of your theme. example: "/library/scss/"';
    }
    public function print_compile_info() {
        print 'Choose how you would like SCSS to be compiled and how you would like the plugin to handle errors';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function scss_dir_callback() {
        printf(
            '<input type="text" id="scss_dir" name="wpscss_options[scss_dir]" value="%s" />',
            esc_attr( $this->options['scss_dir'])
        );
    }
    public function css_dir_callback() {
        printf(
            '<input type="text" id="css_dir" name="wpscss_options[css_dir]" value="%s" />',
            esc_attr( $this->options['css_dir'])
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function compiling_mode_callback() {
        $this->options = get_option( 'wpscss_options' );  
        
        $html = '<select id="compiling_options" name="wpscss_options[compiling_options]">';  
            $html .= '<option value="scss_formatter"' . selected( $this->options['compiling_options'], 'scss_formatter', false) . '>Expanded</option>';  
            $html .= '<option value="scss_formatter_nested"' . selected( $this->options['compiling_options'], 'scss_formatter_nested', false) . '>Nested</option>';  
            $html .= '<option value="scss_formatter_compressed"' . selected( $this->options['compiling_options'], 'scss_formatter_compressed', false) . '>Compressed</option>';  
        $html .= '</select>';  
      
    echo $html;  
    }
    public function errors_callback() {
        $this->options = get_option( 'wpscss_options' );  
        
        $html = '<select id="errors" name="wpscss_options[errors]">';  
            $html .= '<option value="show"' . selected( $this->options['errors'], 'show', false) . '>Show in Header</option>';  
            $html .= '<option value="hide"' . selected( $this->options['errors'], 'hide', false) . '>Hide</option>';  
        $html .= '</select>';  
      
    echo $html;  

    }

}

