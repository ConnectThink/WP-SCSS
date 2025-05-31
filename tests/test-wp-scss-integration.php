<?php
/**
 * Integration tests for WP-SCSS plugin settings persistence
 */

class Test_WP_SCSS_Integration extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        
        // Clean slate for each test
        delete_option( 'wpscss_options' );
    }

    public function tearDown(): void {
        parent::tearDown();
        
        // Clean up
        delete_option( 'wpscss_options' );
    }

    /**
     * Test complete settings save and retrieve cycle
     * This simulates the exact user workflow from the bug report
     */
    public function test_settings_persistence_workflow() {
        // Simulate form submission data
        $form_data = array(
            'base_compiling_folder' => 'Current Theme',
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'cache_dir' => '/cache/',
            'compiling_options' => 'expanded',
            'sourcemap_options' => 'SOURCE_MAP_FILE',
            'errors' => 'show-logged-in',
            'enqueue' => '1',
            'always_recompile' => '0'
        );

        // Include and instantiate the settings class
        require_once dirname( dirname( __FILE__ ) ) . '/options.php';
        $settings = new Wp_Scss_Settings();

        // Sanitize the input (this is what WordPress does during save)
        $sanitized_data = $settings->sanitize( $form_data );

        // Save to database (WordPress handles this automatically)
        update_option( 'wpscss_options', $sanitized_data );

        // Retrieve and verify
        $saved_options = get_option( 'wpscss_options' );

        $this->assertIsArray( $saved_options );
        $this->assertEquals( 'Current Theme', $saved_options['base_compiling_folder'] );
        $this->assertEquals( '/scss/', $saved_options['scss_dir'] );
        $this->assertEquals( '/css/', $saved_options['css_dir'] );
        $this->assertEquals( '/cache/', $saved_options['cache_dir'] );
        $this->assertEquals( 'expanded', $saved_options['compiling_options'] );
        $this->assertEquals( 'SOURCE_MAP_FILE', $saved_options['sourcemap_options'] );
        $this->assertEquals( 'show-logged-in', $saved_options['errors'] );
        $this->assertEquals( '1', $saved_options['enqueue'] );
        $this->assertEquals( '0', $saved_options['always_recompile'] );
    }

    /**
     * Test the specific bug scenario: changing base location
     */
    public function test_base_location_change_persistence() {
        // Initial state: base location is "Uploads Directory"
        $initial_options = array(
            'base_compiling_folder' => 'Uploads Directory',
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'compiling_options' => 'compressed'
        );
        update_option( 'wpscss_options', $initial_options );

        // User changes base location to "Current Theme"
        $updated_form_data = array(
            'base_compiling_folder' => 'Current Theme',  // Changed
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'cache_dir' => '/cache/',
            'compiling_options' => 'compressed',
            'sourcemap_options' => 'SOURCE_MAP_NONE',
            'errors' => 'show',
            'enqueue' => '0',
            'always_recompile' => '0'
        );

        require_once dirname( dirname( __FILE__ ) ) . '/options.php';
        $settings = new Wp_Scss_Settings();

        // Process the update
        $sanitized_data = $settings->sanitize( $updated_form_data );
        update_option( 'wpscss_options', $sanitized_data );

        // Verify the change persisted
        $saved_options = get_option( 'wpscss_options' );
        $this->assertEquals( 'Current Theme', $saved_options['base_compiling_folder'] );
        
        // Verify other settings weren't lost
        $this->assertEquals( '/scss/', $saved_options['scss_dir'] );
        $this->assertEquals( '/css/', $saved_options['css_dir'] );
        $this->assertEquals( 'compressed', $saved_options['compiling_options'] );
    }

    /**
     * Test development mode setting persistence
     */
    public function test_development_mode_persistence() {
        $form_data = array(
            'base_compiling_folder' => 'Current Theme',
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'cache_dir' => '/cache/',
            'compiling_options' => 'expanded',  // Changed to expanded (development mode)
            'sourcemap_options' => 'SOURCE_MAP_INLINE',
            'errors' => 'show',
            'enqueue' => '1',
            'always_recompile' => '1'  // Development mode enabled
        );

        require_once dirname( dirname( __FILE__ ) ) . '/options.php';
        $settings = new Wp_Scss_Settings();

        $sanitized_data = $settings->sanitize( $form_data );
        update_option( 'wpscss_options', $sanitized_data );

        $saved_options = get_option( 'wpscss_options' );
        
        // Verify development settings are saved
        $this->assertEquals( 'expanded', $saved_options['compiling_options'] );
        $this->assertEquals( 'SOURCE_MAP_INLINE', $saved_options['sourcemap_options'] );
        $this->assertEquals( '1', $saved_options['always_recompile'] );
    }

    /**
     * Test checkbox handling in real WordPress context
     */
    public function test_checkbox_form_behavior() {
        // When checkboxes are checked, they submit their value
        $form_data_checked = array(
            'base_compiling_folder' => 'Current Theme',
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'enqueue' => '1',           // Checkbox checked
            'always_recompile' => '1'   // Checkbox checked
        );

        require_once dirname( dirname( __FILE__ ) ) . '/options.php';
        $settings = new Wp_Scss_Settings();

        $sanitized_data = $settings->sanitize( $form_data_checked );
        update_option( 'wpscss_options', $sanitized_data );

        $saved_options = get_option( 'wpscss_options' );
        $this->assertEquals( '1', $saved_options['enqueue'] );
        $this->assertEquals( '1', $saved_options['always_recompile'] );

        // When checkboxes are unchecked, they don't submit at all
        $form_data_unchecked = array(
            'base_compiling_folder' => 'Current Theme',
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            // No 'enqueue' or 'always_recompile' keys = unchecked
        );

        $sanitized_data = $settings->sanitize( $form_data_unchecked );
        update_option( 'wpscss_options', $sanitized_data );

        $saved_options = get_option( 'wpscss_options' );
        $this->assertEquals( '0', $saved_options['enqueue'] );
        $this->assertEquals( '0', $saved_options['always_recompile'] );
    }

    /**
     * Test that the bug is actually fixed by simulating the old broken behavior
     */
    public function test_old_sanitize_method_would_fail() {
        $form_data = array(
            'base_compiling_folder' => 'Current Theme',
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'cache_dir' => '/cache/',
            'compiling_options' => 'expanded',
            'sourcemap_options' => 'SOURCE_MAP_FILE',
            'errors' => 'show-logged-in',
            'enqueue' => '1',
            'always_recompile' => '0'
        );

        // Simulate the old broken sanitize method
        $old_broken_result = array();
        foreach( ['scss_dir', 'css_dir'] as $dir ){
            if( !empty( $form_data[$dir] ) ) {
                $old_broken_result[$dir] = sanitize_text_field( $form_data[$dir] );
                if(substr($old_broken_result[$dir], -1) != '/'){
                    $old_broken_result[$dir] .= '/';
                }
            }
        }
        // Old method only returned scss_dir and css_dir, losing everything else

        $this->assertCount( 2, $old_broken_result );  // Old method only saved 2 fields
        $this->assertArrayNotHasKey( 'base_compiling_folder', $old_broken_result );
        $this->assertArrayNotHasKey( 'compiling_options', $old_broken_result );

        // Now test our fixed method
        require_once dirname( dirname( __FILE__ ) ) . '/options.php';
        $settings = new Wp_Scss_Settings();
        $fixed_result = $settings->sanitize( $form_data );

        $this->assertCount( 9, $fixed_result );  // Fixed method saves all fields
        $this->assertArrayHasKey( 'base_compiling_folder', $fixed_result );
        $this->assertArrayHasKey( 'compiling_options', $fixed_result );
        $this->assertEquals( 'Current Theme', $fixed_result['base_compiling_folder'] );
    }
}