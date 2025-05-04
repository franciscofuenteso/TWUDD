<?php
namespace TWUDD\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Ejemplo_Widget extends Widget_Base {
    public function get_name() {
        return 'twudd-ejemplo-widget';
    }

    public function get_title() {
        return __('Ejemplo Widget', 'twudd');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['twudd'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Contenido', 'twudd'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'texto',
            [
                'label' => __('Texto de ejemplo', 'twudd'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Texto predeterminado', 'twudd'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
<div class="twudd-ejemplo-widget">
    <p><?php echo esc_html($settings['texto']); ?></p>
</div>
<?php
    }

    protected function content_template() {
        ?>
<div class="twudd-ejemplo-widget">
    <p>{{{ settings.texto }}}</p>
</div>
<?php
    }
}