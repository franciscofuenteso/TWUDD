<?php
/**
 * Plugin Name: TWUDD Elementor Extensions
 * Plugin URI: https://example.com/twudd-elementor-plugin
 * Description: Extensiones personalizadas para Elementor y el tema Hello
 * Version: 1.0.0
 * Author: Francisco Fuentes O.
 * Author URI: https://example.com
 * Text Domain: twudd
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Elementor tested up to: 3.15.0
 */

// Prevenir el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Clase principal del plugin
final class TWUDD_Elementor_Plugin {
    const VERSION = '1.0.0';
    const MINIMUM_ELEMENTOR_VERSION = '3.5.0';
    const MINIMUM_PHP_VERSION = '7.4';

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            return;
        }

        // Registrar categoría personalizada
        add_action('elementor/elements/categories_registered', [$this, 'register_categories']);
        
        // Cargar widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Permitir subida de archivos JavaScript (solo para administradores)
        add_filter('upload_mimes', [$this, 'allow_js_upload']);
        add_filter('upload_mimes', [$this, 'force_js_mime'], 999);
        add_filter('wp_check_filetype_and_ext', [$this, 'check_filetype_js'], 10, 4);
        add_filter('site_option_upload_filetypes', [$this, 'allow_js_filetypes']);
        add_filter('upload_mimes_overrides', [$this, 'allow_js_upload']);
        
        // Permitir subida de archivos ZIP (solo para administradores)
        add_filter('upload_mimes', [$this, 'allow_zip_upload']);
        
        // Verificar contenido de archivos JavaScript subidos
        add_filter('wp_handle_upload', [$this, 'verify_js_upload']);
        
        // Agregar endpoint para procesar archivos ZIP
        add_action('wp_ajax_twudd_process_zip', [$this, 'process_zip_file']);
        add_action('wp_ajax_nopriv_twudd_process_zip', [$this, 'process_zip_file']);
    }
    
    /**
     * Permitir la subida de archivos JavaScript solo para administradores
     * NOTA: Esto sigue siendo un riesgo de seguridad potencial y debe usarse con precaución
     */
    public function allow_js_upload($mimes) {
        // Solo permitir a administradores subir archivos JavaScript
        if (current_user_can('administrator')) {
            $mimes['js'] = 'text/javascript';
        }
        return $mimes;
    }
    
    /**
     * Forzar la inclusión de JavaScript en los tipos MIME permitidos
     * Esta función se ejecuta con prioridad alta para asegurar que se aplique después de otros filtros
     */
    public function force_js_mime($mimes) {
        // Asegurar que los archivos JavaScript estén permitidos
        if (current_user_can('administrator')) {
            $mimes['js'] = 'text/javascript';
            
            // También permitir otros tipos MIME comunes para JavaScript
            $mimes['javascript'] = 'text/javascript';
            $mimes['js'] = 'application/javascript';
            $mimes['js'] = 'application/x-javascript';
        }
        return $mimes;
    }
    
    /**
     * Permitir archivos JavaScript en la lista de tipos de archivos permitidos
     */
    public function allow_js_filetypes($filetypes) {
        if (current_user_can('administrator')) {
            if (is_string($filetypes)) {
                // Agregar js a la lista de tipos de archivos permitidos
                if (strpos($filetypes, 'js') === false) {
                    $filetypes .= ' js';
                }
            }
        }
        return $filetypes;
    }
    
    /**
     * Verificar el contenido de archivos JavaScript subidos
     * Esta función escanea los archivos JS en busca de código potencialmente malicioso
     */
    public function verify_js_upload($file) {
        // Solo verificar archivos JavaScript
        if ($file['type'] === 'text/javascript' || $file['type'] === 'application/javascript') {
            // Patrones potencialmente peligrosos
            $suspicious_patterns = [
                'eval\s*\(', // eval()
                'document\.write\s*\(', // document.write()
                'new\s+Function\s*\(', // new Function()
                '(?<!\.)(setTimeout|setInterval)\s*\(', // setTimeout/setInterval
                '\.innerHTML\s*=', // .innerHTML =
                'document\.cookie', // document.cookie
                'document\.location\s*=', // document.location =
                'window\.location\s*=', // window.location =
                '<script', // <script tags
                'fetch\s*\(', // fetch()
                'XMLHttpRequest', // XMLHttpRequest
                'localStorage', // localStorage
                'sessionStorage', // sessionStorage
            ];
            
            // Leer el contenido del archivo
            $content = file_get_contents($file['file']);
            
            // Verificar patrones sospechosos
            $suspicious_found = false;
            $suspicious_matches = [];
            
            foreach ($suspicious_patterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $content, $matches)) {
                    $suspicious_found = true;
                    $suspicious_matches[] = $matches[0];
                }
            }
            
            // Si se encontraron patrones sospechosos, registrar y mostrar advertencia
            if ($suspicious_found && current_user_can('administrator')) {
                $message = sprintf(
                    __('ADVERTENCIA: El archivo JavaScript "%s" contiene código potencialmente peligroso: %s. Se recomienda revisar el archivo antes de usarlo.', 'twudd'),
                    basename($file['file']),
                    implode(', ', $suspicious_matches)
                );
                
                // Mostrar advertencia al administrador
                add_action('admin_notices', function() use ($message) {
                    printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
                });
                
                // Registrar en el log
                error_log('TWUDD Security Warning: ' . $message);
            }
        }
        
        return $file;
    }
    
    /**
     * Verificar y permitir archivos JavaScript
     * Esta función permite explícitamente los archivos .js
     */
    public function check_filetype_js($data, $file, $filename, $mimes) {
        if (current_user_can('administrator')) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Permitir archivos .js
            if ($ext === 'js') {
                $data['ext'] = 'js';
                $data['type'] = 'text/javascript';
            }
        }
        
        return $data;
    }
    
    /**
     * Permitir la subida de archivos ZIP
     */
    public function allow_zip_upload($mimes) {
        // Solo permitir a administradores subir archivos ZIP
        if (current_user_can('administrator')) {
            $mimes['zip'] = 'application/zip';
        }
        return $mimes;
    }
    
    /**
     * Procesar un archivo ZIP para extraer sus contenidos
     * Esta función es llamada vía AJAX
     */
    public function process_zip_file() {
        // Verificar nonce para seguridad
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'twudd_process_zip')) {
            wp_send_json_error('Nonce inválido');
            return;
        }
        
        // Verificar permisos
        if (!current_user_can('administrator')) {
            wp_send_json_error('Permisos insuficientes');
            return;
        }
        
        // Verificar que se proporcionó un ID de archivo
        if (!isset($_POST['file_id']) || empty($_POST['file_id'])) {
            wp_send_json_error('ID de archivo no proporcionado');
            return;
        }
        
        $file_id = intval($_POST['file_id']);
        $file = get_attached_file($file_id);
        
        if (!$file || !file_exists($file)) {
            wp_send_json_error('Archivo no encontrado');
            return;
        }
        
        // Verificar que el archivo es un ZIP
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'zip') {
            wp_send_json_error('El archivo no es un ZIP');
            return;
        }
        
        // Crear directorio temporal para extraer el ZIP
        $upload_dir = wp_upload_dir();
        $extract_dir = $upload_dir['basedir'] . '/twudd_p5_projects/' . $file_id;
        
        // Limpiar directorio si ya existe
        if (file_exists($extract_dir)) {
            $this->rrmdir($extract_dir);
        }
        
        // Crear directorio
        if (!wp_mkdir_p($extract_dir)) {
            wp_send_json_error('No se pudo crear el directorio para extraer el ZIP');
            return;
        }
        
        // Extraer ZIP
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            wp_send_json_error('No se pudo abrir el archivo ZIP');
            return;
        }
        
        $zip->extractTo($extract_dir);
        $zip->close();
        
        // Buscar archivos principales (sketch.js, index.js, main.js)
        $main_files = [
            'sketch.js',
            'index.js',
            'main.js',
            'app.js',
            'script.js'
        ];
        
        $main_file = null;
        foreach ($main_files as $file_name) {
            if (file_exists($extract_dir . '/' . $file_name)) {
                $main_file = $file_name;
                break;
            }
        }
        
        // Si no se encontró un archivo principal, buscar cualquier archivo .js
        if (!$main_file) {
            $js_files = glob($extract_dir . '/*.js');
            if (!empty($js_files)) {
                $main_file = basename($js_files[0]);
            }
        }
        
        if (!$main_file) {
            wp_send_json_error('No se encontró un archivo JavaScript principal en el ZIP');
            return;
        }
        
        // Crear URL para acceder a los archivos
        $extract_url = $upload_dir['baseurl'] . '/twudd_p5_projects/' . $file_id;
        
        // Devolver información sobre el proyecto extraído
        wp_send_json_success([
            'main_file' => $main_file,
            'base_url' => $extract_url,
            'files' => $this->list_directory_files($extract_dir)
        ]);
    }
    
    /**
     * Eliminar un directorio y su contenido recursivamente
     */
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Listar archivos en un directorio recursivamente
     */
    private function list_directory_files($dir, $base_dir = '') {
        if (empty($base_dir)) {
            $base_dir = $dir;
        }
        
        $files = [];
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            $path = $dir . '/' . $item;
            $relative_path = substr($path, strlen($base_dir) + 1);
            
            if (is_dir($path)) {
                $files = array_merge($files, $this->list_directory_files($path, $base_dir));
            } else {
                $files[] = $relative_path;
            }
        }
        
        return $files;
    }
    
    public function register_categories($elements_manager) {
        $elements_manager->add_category(
            'twudd',
            [
                'title' => __('TWUDD', 'twudd'),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    private function check_dependencies() {
        // Verificar PHP
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return false;
        }

        // Verificar Elementor
        if (!defined('ELEMENTOR_VERSION')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return false;
        }

        // Verificar versión de Elementor
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return false;
        }

        return true;
    }

    public function admin_notice_minimum_php_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            __('TWUDD Elementor Extensions requiere PHP versión %s o superior. Su versión actual es %s.', 'twudd'),
            self::MINIMUM_PHP_VERSION,
            PHP_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
    }

    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = __('TWUDD Elementor Extensions requiere que Elementor esté instalado y activado.', 'twudd');
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
    }

    public function admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            __('TWUDD Elementor Extensions requiere Elementor versión %s o superior. Su versión actual es %s.', 'twudd'),
            self::MINIMUM_ELEMENTOR_VERSION,
            ELEMENTOR_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
    }

    public function register_widgets($widgets_manager) {
        // Registrar widgets personalizados
        require_once(__DIR__ . '/includes/widgets/ejemplo-widget.php');
        require_once(__DIR__ . '/includes/widgets/p5-canvas-widget.php');
        
        $widgets_manager->register(new \TWUDD\Widgets\Ejemplo_Widget());
        $widgets_manager->register(new \TWUDD\Widgets\P5_Canvas_Widget());
    }
}

// Inicializar el plugin
TWUDD_Elementor_Plugin::instance();