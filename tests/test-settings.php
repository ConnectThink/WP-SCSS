<?php
/**
 * Tests for WP_Scss_Settings class
 */

class Test_WP_SCSS_Settings extends WP_UnitTestCase {

    private $settings_instance;

    public function setUp(): void {
        parent::setUp();
        
        // Include the options file to load the class
        require_once dirname( dirname( __FILE__ ) ) . '/options.php';
        
        $this->settings_instance = new Wp_Scss_Settings();
    }

    public function tearDown(): void {
        parent::tearDown();
        
        // Clean up options
        delete_option( 'wpscss_options' );
    }

    /**
     * Test that the sanitize method preserves all form fields
     * This tests the bug fix for settings not being saved
     */
    public function test_sanitize_preserves_all_fields() {
        $input = array(
            'base_compiling_folder' => 'Current Theme',
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'cache_dir' => '/cache/',
            'compiling_options' => 'compressed',
            'sourcemap_options' => 'SOURCE_MAP_NONE',
            'errors' => 'show',
            'enqueue' => '1',
            'always_recompile' => '1'
        );

        $result = $this->settings_instance->sanitize( $input );

        // Test that all fields are preserved
        $this->assertEquals( 'Current Theme', $result['base_compiling_folder'] );
        $this->assertEquals( '/scss/', $result['scss_dir'] );
        $this->assertEquals( '/css/', $result['css_dir'] );
        $this->assertEquals( '/cache/', $result['cache_dir'] );
        $this->assertEquals( 'compressed', $result['compiling_options'] );
        $this->assertEquals( 'SOURCE_MAP_NONE', $result['sourcemap_options'] );
        $this->assertEquals( 'show', $result['errors'] );
        $this->assertEquals( '1', $result['enqueue'] );
        $this->assertEquals( '1', $result['always_recompile'] );
    }

    /**
     * Test directory path sanitization with trailing slash addition
     */
    public function test_sanitize_adds_trailing_slashes() {
        $input = array(
            'scss_dir' => '/scss',
            'css_dir' => '/css',
            'cache_dir' => '/cache'
        );

        $result = $this->settings_instance->sanitize( $input );

        $this->assertEquals( '/scss/', $result['scss_dir'] );
        $this->assertEquals( '/css/', $result['css_dir'] );
        $this->assertEquals( '/cache/', $result['cache_dir'] );
    }

    /**
     * Test that directory paths already with trailing slashes are preserved
     */
    public function test_sanitize_preserves_existing_trailing_slashes() {
        $input = array(
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'cache_dir' => '/cache/'
        );

        $result = $this->settings_instance->sanitize( $input );

        $this->assertEquals( '/scss/', $result['scss_dir'] );
        $this->assertEquals( '/css/', $result['css_dir'] );
        $this->assertEquals( '/cache/', $result['cache_dir'] );
    }

    /**
     * Test checkbox field handling
     */
    public function test_sanitize_checkbox_fields() {
        // Test checkbox checked
        $input_checked = array(
            'enqueue' => '1',
            'always_recompile' => '1'
        );

        $result = $this->settings_instance->sanitize( $input_checked );
        $this->assertEquals( '1', $result['enqueue'] );
        $this->assertEquals( '1', $result['always_recompile'] );

        // Test checkbox unchecked (empty)
        $input_unchecked = array();

        $result = $this->settings_instance->sanitize( $input_unchecked );
        $this->assertEquals( '0', $result['enqueue'] );
        $this->assertEquals( '0', $result['always_recompile'] );
    }

    /**
     * Test handling of empty/missing fields
     */
    public function test_sanitize_handles_empty_fields() {
        $input = array(
            'base_compiling_folder' => '',
            'scss_dir' => '',
            'css_dir' => '/css/',
            'compiling_options' => '',
        );

        $result = $this->settings_instance->sanitize( $input );

        // Empty fields should not be set in result
        $this->assertArrayNotHasKey( 'base_compiling_folder', $result );
        $this->assertArrayNotHasKey( 'scss_dir', $result );
        $this->assertArrayNotHasKey( 'compiling_options', $result );
        
        // Non-empty fields should be preserved
        $this->assertEquals( '/css/', $result['css_dir'] );
        
        // Checkboxes should default to '0'
        $this->assertEquals( '0', $result['enqueue'] );
        $this->assertEquals( '0', $result['always_recompile'] );
    }

    /**
     * Test text field sanitization
     */
    public function test_sanitize_text_fields() {
        $input = array(
            'base_compiling_folder' => '<script>alert("xss")</script>Current Theme',
            'compiling_options' => 'compressed<script>',
            'sourcemap_options' => 'SOURCE_MAP_NONE',
            'errors' => 'show'
        );

        $result = $this->settings_instance->sanitize( $input );

        // Should strip harmful content
        $this->assertEquals( 'Current Theme', $result['base_compiling_folder'] );
        $this->assertEquals( 'compressed', $result['compiling_options'] );
        $this->assertEquals( 'SOURCE_MAP_NONE', $result['sourcemap_options'] );
        $this->assertEquals( 'show', $result['errors'] );
    }

    /**
     * Test regression: the original bug where only scss_dir and css_dir were preserved
     * This ensures our fix works correctly
     */
    public function test_bug_fix_all_settings_preserved() {
        // Simulate the exact scenario from the bug report
        $input = array(
            'base_compiling_folder' => 'Current Theme',  // This was getting lost
            'scss_dir' => '/scss/',
            'css_dir' => '/css/',
            'cache_dir' => '/cache/',
            'compiling_options' => 'expanded',           // This was getting lost
            'sourcemap_options' => 'SOURCE_MAP_FILE',    // This was getting lost
            'errors' => 'show-logged-in',                // This was getting lost
            'enqueue' => '1',                           // This was getting lost
            'always_recompile' => '0'                   // This was getting lost
        );

        $result = $this->settings_instance->sanitize( $input );

        // All 9 fields should be present in the result
        $this->assertCount( 9, $result );
        
        // Specifically test the fields that were getting lost
        $this->assertEquals( 'Current Theme', $result['base_compiling_folder'] );
        $this->assertEquals( 'expanded', $result['compiling_options'] );
        $this->assertEquals( 'SOURCE_MAP_FILE', $result['sourcemap_options'] );
        $this->assertEquals( 'show-logged-in', $result['errors'] );
        $this->assertEquals( '1', $result['enqueue'] );
        $this->assertEquals( '0', $result['always_recompile'] );
    }
}