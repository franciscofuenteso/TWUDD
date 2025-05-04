<?php
/**
 * WordPress and Elementor Stubs for IDE Intellisense
 * This file provides type hints and definitions for WordPress and Elementor core functions and constants
 */

// WordPress Core Constants
defined('ABSPATH') || define('ABSPATH', '/path/to/wordpress/');
defined('ELEMENTOR_VERSION') || define('ELEMENTOR_VERSION', '3.5.0');

// Elementor Namespace Stubs
namespace Elementor {
    /**
     * Elementor Widget Base Class Stub
     */
    abstract class Widget_Base {
        /**
         * Get widget name.
         *
         * @return string Widget name.
         */
        abstract public function get_name();

        /**
         * Get widget title.
         *
         * @return string Widget title.
         */
        abstract public function get_title();

        /**
         * Get widget icon.
         *
         * @return string Widget icon.
         */
        public function get_icon() {
            return 'eicon-code';
        }

        /**
         * Get widget categories.
         *
         * @return array Widget categories.
         */
        public function get_categories() {
            return ['basic'];
        }

        /**
         * Register widget controls.
         */
        abstract protected function register_controls();

        /**
         * Render widget output on the frontend.
         */
        abstract protected function render();

        /**
         * Render widget output in the editor.
         */
        abstract protected function content_template();

        /**
         * Get settings for display.
         *
         * @return array
         */
        public function get_settings_for_display($setting = null) {
            return [];
        }

        /**
         * Start controls section.
         *
         * @param string $section_id Section ID.
         * @param array  $args       Section arguments.
         */
        protected function start_controls_section($section_id, $args = []) {}

        /**
         * Add control to the section.
         *
         * @param string $control_id Control ID.
         * @param array  $args       Control arguments.
         */
        protected function add_control($control_id, $args = []) {}

        /**
         * End controls section.
         */
        protected function end_controls_section() {}
    }

    /**
     * Elementor Controls Manager Stub
     */
    class Controls_Manager {
        const TAB_CONTENT = 'content';
        const TEXT = 'text';
    }
}

// Namespace Stubs for TWUDD Widgets
namespace TWUDD\Widgets {
    /**
     * Stub for Ejemplo_Widget to help IDE recognize the type
     */
    class Ejemplo_Widget extends \Elementor\Widget_Base {
        public function get_name() { return 'twudd-ejemplo-widget'; }
        public function get_title() { return 'Ejemplo Widget'; }
        protected function register_controls() {}
        protected function render() {}
        protected function content_template() {}
    }
}

// Restore global namespace
namespace {
    // WordPress Core Functions
    if (!function_exists('add_action')) {
        /**
         * Hooks a function on to a specific action.
         *
         * @param string   $hook_name     The name of the action to add the callback to.
         * @param callable $callback      The callback function to be called when the action is triggered.
         * @param int      $priority      Optional. The priority of the callback. Default 10.
         * @param int      $accepted_args Optional. The number of arguments the callback accepts. Default 1.
         * @return true
         */
        function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {
            return true;
        }
    }

    if (!function_exists('__')) {
        /**
         * Retrieve the translation of $text.
         *
         * @param string $text   Text to translate.
         * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
         * @return string Translated text.
         */
        function __($text, $domain = 'default') {
            return $text;
        }
    }

    if (!function_exists('did_action')) {
        /**
         * Retrieve the number of times an action is fired.
         *
         * @param string $hook_name The name of the action hook.
         * @return int The number of times the action hook is fired.
         */
        function did_action($hook_name) {
            return 0;
        }
    }

    if (!function_exists('version_compare')) {
        /**
         * Compares two version numbers.
         *
         * @param string $version1 First version number.
         * @param string $version2 Second version number.
         * @param string $operator Optional. Comparison operator.
         * @return mixed
         */
        function version_compare($version1, $version2, $operator = null) {
            return true;
        }
    }

    if (!function_exists('esc_html')) {
        /**
         * Escape HTML entities
         *
         * @param string $text Text to escape
         * @return string Escaped text
         */
        function esc_html($text) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
    }
}