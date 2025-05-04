<?php
namespace TWUDD\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class P5_Canvas_Widget extends Widget_Base {
    public function get_name() {
        return 'twudd-p5-canvas';
    }

    public function get_title() {
        return __('P5.js Canvas', 'twudd');
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
                'label' => __('Contenido P5.js', 'twudd'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'p5_source_type',
            [
                'label' => __('Fuente del Sketch', 'twudd'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'code' => __('Editor de Código', 'twudd'),
                    'file' => __('Archivo Individual', 'twudd'),
                    'project' => __('Proyecto ZIP', 'twudd'),
                ],
                'default' => 'code',
            ]
        );

        $this->add_control(
            'p5_code',
            [
                'label' => __('Código P5.js', 'twudd'),
                'type' => \Elementor\Controls_Manager::CODE,
                'language' => 'javascript',
                'rows' => 10,
                'default' => "function setup() {\n  createCanvas(400, 400);\n}\n\nfunction draw() {\n  background(220);\n  ellipse(width/2, height/2, 80, 80);\n}",
                'condition' => [
                    'p5_source_type' => 'code',
                ],
            ]
        );

        $this->add_control(
            'p5_file',
            [
                'label' => __('Archivo P5.js', 'twudd'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'text/javascript',
                'condition' => [
                    'p5_source_type' => 'file',
                ],
            ]
        );

        $this->add_control(
            'p5_project',
            [
                'label' => __('Proyecto P5.js (ZIP)', 'twudd'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'application/zip',
                'condition' => [
                    'p5_source_type' => 'project',
                ],
            ]
        );

        $this->add_control(
            'canvas_width',
            [
                'label' => __('Ancho del Canvas', 'twudd'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 400,
                'min' => 100,
                'max' => 1200,
            ]
        );

        $this->add_control(
            'canvas_height',
            [
                'label' => __('Alto del Canvas', 'twudd'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 400,
                'min' => 100,
                'max' => 1200,
            ]
        );

        $this->add_control(
            'responsive',
            [
                'label' => __('Canvas Responsive', 'twudd'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sí', 'twudd'),
                'label_off' => __('No', 'twudd'),
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $widget_id = $this->get_id();
        
        // Generar un ID único para este widget
        $unique_id = 'twudd-p5-' . esc_attr($widget_id);
        
        // Contenedor del canvas
        echo '<div class="twudd-p5-container" id="' . $unique_id . '">';
        echo '</div>';
        
        // Estilos del contenedor
        echo '<style>
            #' . $unique_id . ' {
                width: ' . esc_attr($settings['canvas_width']) . 'px;
                height: ' . esc_attr($settings['canvas_height']) . 'px;
                ' . ($settings['responsive'] === 'yes' ? 'max-width: 100%;' : '') . '
                position: relative;
                overflow: hidden;
            }
            #' . $unique_id . ' canvas {
                position: absolute;
                top: 0;
                left: 0;
                width: 100% !important;
                height: 100% !important;
            }
            /* Ocultar cualquier canvas fuera del contenedor */
            body > canvas.p5Canvas {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                pointer-events: none !important;
                position: absolute !important;
                z-index: -9999 !important;
                width: 1px !important;
                height: 1px !important;
                top: -9999px !important;
                left: -9999px !important;
            }
        </style>';
        
        // Script para inicializar P5.js
        echo '<script>
            // Función para inicializar P5.js de forma segura
            (function() {
                // Eliminar cualquier instancia previa de P5.js
                if (window.p5InstancesToDestroy) {
                    window.p5InstancesToDestroy.forEach(function(instance) {
                        if (instance && typeof instance.remove === "function") {
                            instance.remove();
                        }
                    });
                }
                window.p5InstancesToDestroy = [];
                
                // Eliminar cualquier canvas existente
                document.querySelectorAll("canvas.p5Canvas").forEach(function(canvas) {
                    canvas.remove();
                });
                
                // Configurar un MutationObserver para detectar y eliminar canvas fuera del contenedor
                var containerId = "' . $unique_id . '";
                var container = document.getElementById(containerId);
                
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length) {
                            Array.from(mutation.addedNodes).forEach(function(node) {
                                if (node.nodeName === "CANVAS" && 
                                    node.className.includes("p5Canvas") && 
                                    node.parentNode !== container) {
                                    console.log("Eliminando canvas fuera del contenedor:", node.id);
                                    node.remove();
                                }
                            });
                        }
                    });
                });
                
                observer.observe(document.body, { childList: true, subtree: true });
                
                // Cargar P5.js si aún no está cargado
                if (typeof p5 === "undefined") {
                    var script = document.createElement("script");
                    script.src = "https://cdn.jsdelivr.net/npm/p5@1.6.0/lib/p5.js";
                    script.onload = function() {
                        // Guardar la función original de P5.js
                        window._originalP5 = p5;
                        
                        // Sobrescribir la función p5 para controlar la creación de instancias
                        window.p5 = function() {
                            var args = Array.from(arguments);
                            var lastArg = args[args.length - 1];
                            
                            // Si el último argumento no es el ID de nuestro contenedor, reemplazarlo
                            if (typeof lastArg === "string" && lastArg !== containerId) {
                                args[args.length - 1] = containerId;
                            } else if (typeof lastArg !== "string") {
                                args.push(containerId);
                            }
                            
                            // Crear la instancia con los argumentos modificados
                            var instance = new window._originalP5(...args);
                            window.p5InstancesToDestroy.push(instance);
                            return instance;
                        };
                        
                        // Copiar propiedades y métodos de la función original
                        for (var prop in window._originalP5) {
                            if (window._originalP5.hasOwnProperty(prop)) {
                                window.p5[prop] = window._originalP5[prop];
                            }
                        }
                        
                        initializeP5();
                    };
                    document.head.appendChild(script);
                } else {
                    initializeP5();
                }
                
                function initializeP5() {
                    // Limpiar el contenedor
                    if (container) {
                        container.innerHTML = "";
                    }
                    
                    // Configurar dimensiones
                    var canvasWidth = ' . esc_attr($settings['canvas_width']) . ';
                    var canvasHeight = ' . esc_attr($settings['canvas_height']) . ';
                    
                    // Inicializar según el tipo de fuente
                    var sourceType = "' . $settings['p5_source_type'] . '";
                    
                    if (sourceType === "code") {
                        // Código directo
                        var userCode = `' . $settings['p5_code'] . '`;
                        
                        // Crear una instancia de P5.js con el código del usuario
                        var sketch = function(p) {
                            // Sobrescribir createCanvas
                            var originalCreateCanvas = p.createCanvas;
                            p.createCanvas = function() {
                                var canvas = originalCreateCanvas.call(p, canvasWidth, canvasHeight);
                                
                                // Asegurar que el canvas esté dentro del contenedor
                                if (canvas.canvas && canvas.canvas.parentNode !== container) {
                                    container.appendChild(canvas.canvas);
                                }
                                
                                return canvas;
                            };
                            
                            // Definir setup y draw
                            if (userCode.includes("function setup()")) {
                                eval(userCode.replace("function setup()", "p.setup = function()").replace("function draw()", "p.draw = function()"));
                            } else {
                                p.setup = function() {
                                    p.createCanvas(canvasWidth, canvasHeight);
                                };
                                p.draw = function() {
                                    p.background(220);
                                    p.ellipse(p.width/2, p.height/2, 80, 80);
                                };
                            }
                        };
                        
                        // Crear la instancia
                        var instance = new p5(sketch, container);
                        window.p5InstancesToDestroy.push(instance);
                    }
                    ' . (!empty($settings['p5_project']['url']) ? '
                    else if (sourceType === "project") {
                        // Proyecto ZIP
                        var projectId = ' . intval($settings['p5_project']['id']) . ';
                        
                        // Mostrar mensaje de carga
                        container.innerHTML = "<div style=\'padding:20px;text-align:center;\'><p>Procesando proyecto...</p></div>";
                        
                        // Procesar el archivo ZIP
                        fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: "action=twudd_process_zip&nonce=' . wp_create_nonce('twudd_process_zip') . '&file_id=" + projectId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Proyecto procesado correctamente
                                var projectInfo = data.data;
                                var baseUrl = projectInfo.base_url;
                                var mainFile = projectInfo.main_file;
                                var files = projectInfo.files;
                                
                                // Crear un sandbox para el código externo
                                var sandbox = {
                                    setup: null,
                                    draw: null,
                                    preload: null,
                                    mousePressed: null,
                                    mouseMoved: null,
                                    mouseReleased: null,
                                    keyPressed: null,
                                    keyReleased: null
                                };
                                
                                // Cargar el archivo principal
                                fetch(baseUrl + "/" + mainFile)
                                    .then(response => response.text())
                                    .then(code => {
                                        // Ejecutar el código en un contexto aislado
                                        try {
                                            // Reemplazar funciones globales con asignaciones al sandbox
                                            var modifiedCode = code
                                                .replace(/function\\s+setup\\s*\\(\\s*\\)\\s*{/g, "sandbox.setup = function() {")
                                                .replace(/function\\s+draw\\s*\\(\\s*\\)\\s*{/g, "sandbox.draw = function() {")
                                                .replace(/function\\s+preload\\s*\\(\\s*\\)\\s*{/g, "sandbox.preload = function() {")
                                                .replace(/function\\s+mousePressed\\s*\\(\\s*\\)\\s*{/g, "sandbox.mousePressed = function() {")
                                                .replace(/function\\s+mouseMoved\\s*\\(\\s*\\)\\s*{/g, "sandbox.mouseMoved = function() {")
                                                .replace(/function\\s+mouseReleased\\s*\\(\\s*\\)\\s*{/g, "sandbox.mouseReleased = function() {")
                                                .replace(/function\\s+keyPressed\\s*\\(\\s*\\)\\s*{/g, "sandbox.keyPressed = function() {")
                                                .replace(/function\\s+keyReleased\\s*\\(\\s*\\)\\s*{/g, "sandbox.keyReleased = function() {");
                                            
                                            // Ejecutar el código modificado
                                            eval(modifiedCode);
                                            
                                            // Crear una instancia de P5.js con las funciones del sandbox
                                            var sketch = function(p) {
                                                // Sobrescribir createCanvas
                                                var originalCreateCanvas = p.createCanvas;
                                                p.createCanvas = function() {
                                                    var canvas = originalCreateCanvas.call(p, canvasWidth, canvasHeight);
                                                    
                                                    // Asegurar que el canvas esté dentro del contenedor
                                                    if (canvas.canvas && canvas.canvas.parentNode !== container) {
                                                        container.appendChild(canvas.canvas);
                                                    }
                                                    
                                                    return canvas;
                                                };
                                                
                                                // Sobrescribir loadImage para usar la URL base del proyecto
                                                var originalLoadImage = p.loadImage;
                                                p.loadImage = function(path, successCallback, failureCallback) {
                                                    // Si la ruta es una URL absoluta, usarla directamente
                                                    if (path.startsWith("http://") || path.startsWith("https://") || path.startsWith("data:")) {
                                                        return originalLoadImage.call(p, path, successCallback, failureCallback);
                                                    }
                                                    
                                                    // Si no, construir la URL relativa al proyecto
                                                    var fullPath = baseUrl + "/" + path;
                                                    return originalLoadImage.call(p, fullPath, successCallback, failureCallback);
                                                };
                                                
                                                // Exponer funciones y constantes de p5 al ámbito global para el sandbox
                                                var exposeP5Functions = function(pInstance) {
                                                    // Crear un objeto para almacenar las funciones y valores originales
                                                    var originalValues = {};
                                                    
                                                    // Guardar las funciones y valores originales y crear los globales
                                                    for (var key in pInstance) {
                                                        // Guardar el valor original si existe
                                                        if (window[key] !== undefined) {
                                                            originalValues[key] = window[key];
                                                        }
                                                        
                                                        if (typeof pInstance[key] === "function") {
                                                            // Crear una función global que llama a la función de p5
                                                            window[key] = (function(k) {
                                                                return function() {
                                                                    return pInstance[k].apply(pInstance, arguments);
                                                                };
                                                            })(key);
                                                        } else {
                                                            // Exponer constantes y otros valores
                                                            window[key] = pInstance[key];
                                                        }
                                                    }
                                                    
                                                    // Exponer constantes específicas de P5.js que podrían no estar en la instancia
                                                    var p5Constants = ["RGB", "HSB", "HSL", "DEGREES", "RADIANS", 
                                                        "CENTER", "CORNER", "CORNERS", "RADIUS",
                                                        "LEFT", "RIGHT", "TOP", "BOTTOM", "CENTER",
                                                        "PI", "TWO_PI", "HALF_PI", "QUARTER_PI"];
                                                    
                                                    for (var i = 0; i < p5Constants.length; i++) {
                                                        var constName = p5Constants[i];
                                                        if (p5[constName] !== undefined && window[constName] === undefined) {
                                                            window[constName] = p5[constName];
                                                            originalValues[constName] = undefined; // Para restaurar después
                                                        }
                                                    }
                                                    
                                                    return originalValues;
                                                };
                                                
                                                // Restaurar funciones originales
                                                var restoreOriginalFunctions = function(originalFunctions) {
                                                    for (var key in originalFunctions) {
                                                        window[key] = originalFunctions[key];
                                                    }
                                                };
                                                
                                                // Asignar funciones del sandbox a la instancia
                                                if (sandbox.preload) {
                                                    p.preload = function() {
                                                        try {
                                                            // Exponer funciones de p5
                                                            var originalFunctions = exposeP5Functions(p);
                                                            
                                                            // Ejecutar preload
                                                            sandbox.preload.call(p);
                                                            
                                                            // Restaurar funciones originales
                                                            restoreOriginalFunctions(originalFunctions);
                                                        } catch(e) {
                                                            console.error("Error en preload:", e);
                                                        }
                                                    };
                                                }
                                                
                                                if (sandbox.setup) {
                                                    p.setup = function() {
                                                        try {
                                                            // Exponer funciones de p5
                                                            var originalFunctions = exposeP5Functions(p);
                                                            
                                                            // Ejecutar setup
                                                            sandbox.setup.call(p);
                                                            
                                                            // Restaurar funciones originales
                                                            restoreOriginalFunctions(originalFunctions);
                                                            
                                                            // Si no se ha creado un canvas, crearlo
                                                            if (!p.canvas) {
                                                                p.createCanvas(canvasWidth, canvasHeight);
                                                            }
                                                        } catch(e) {
                                                            console.error("Error en setup:", e);
                                                            p.createCanvas(canvasWidth, canvasHeight);
                                                        }
                                                    };
                                                } else {
                                                    p.setup = function() {
                                                        p.createCanvas(canvasWidth, canvasHeight);
                                                    };
                                                }
                                                
                                                if (sandbox.draw) {
                                                    p.draw = function() {
                                                        try {
                                                            // Exponer funciones de p5
                                                            var originalFunctions = exposeP5Functions(p);
                                                            
                                                            // Ejecutar draw
                                                            sandbox.draw.call(p);
                                                            
                                                            // Restaurar funciones originales
                                                            restoreOriginalFunctions(originalFunctions);
                                                        } catch(e) {
                                                            console.error("Error en draw:", e);
                                                            p.background(220);
                                                            p.fill(255, 0, 0);
                                                            p.textSize(16);
                                                            p.text("Error en draw()", 10, 30);
                                                        }
                                                    };
                                                } else {
                                                    p.draw = function() {
                                                        p.background(220);
                                                        p.ellipse(p.width/2, p.height/2, 80, 80);
                                                    };
                                                }
                                                
                                                // Asignar otros eventos
                                                if (sandbox.mousePressed) p.mousePressed = function() { 
                                                    var originalFunctions = exposeP5Functions(p);
                                                    sandbox.mousePressed.call(p);
                                                    restoreOriginalFunctions(originalFunctions);
                                                };
                                                if (sandbox.mouseMoved) p.mouseMoved = function() { 
                                                    var originalFunctions = exposeP5Functions(p);
                                                    sandbox.mouseMoved.call(p);
                                                    restoreOriginalFunctions(originalFunctions);
                                                };
                                                if (sandbox.mouseReleased) p.mouseReleased = function() { 
                                                    var originalFunctions = exposeP5Functions(p);
                                                    sandbox.mouseReleased.call(p);
                                                    restoreOriginalFunctions(originalFunctions);
                                                };
                                                if (sandbox.keyPressed) p.keyPressed = function() { 
                                                    var originalFunctions = exposeP5Functions(p);
                                                    sandbox.keyPressed.call(p);
                                                    restoreOriginalFunctions(originalFunctions);
                                                };
                                                if (sandbox.keyReleased) p.keyReleased = function() { 
                                                    var originalFunctions = exposeP5Functions(p);
                                                    sandbox.keyReleased.call(p);
                                                    restoreOriginalFunctions(originalFunctions);
                                                };
                                            };
                                            
                                            // Crear la instancia
                                            var instance = new p5(sketch, container);
                                            window.p5InstancesToDestroy.push(instance);
                                        } catch (error) {
                                            console.error("Error al cargar el archivo:", error);
                                            container.innerHTML = "<div style=\'color:red;padding:20px;\'>Error al cargar el archivo: " + error.message + "</div>";
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Error al cargar el archivo principal:", error);
                                        container.innerHTML = "<div style=\'color:red;padding:20px;\'>Error al cargar el archivo principal: " + error.message + "</div>";
                                    });
                            } else {
                                // Error al procesar el proyecto
                                container.innerHTML = "<div style=\'color:red;padding:20px;\'>Error al procesar el proyecto: " + data.data + "</div>";
                            }
                        })
                        .catch(error => {
                            console.error("Error al procesar el proyecto:", error);
                            container.innerHTML = "<div style=\'color:red;padding:20px;\'>Error al procesar el proyecto: " + error.message + "</div>";
                        });
                    }
                    ' : '') . '
                    ' . (!empty($settings['p5_file']['url']) ? '
                    else if (sourceType === "file") {
                        // Archivo externo
                        var fileUrl = "' . esc_url($settings['p5_file']['url']) . '";
                        
                        // Crear un sandbox para el código externo
                        var sandbox = {
                            setup: null,
                            draw: null,
                            preload: null,
                            mousePressed: null,
                            mouseMoved: null,
                            mouseReleased: null,
                            keyPressed: null,
                            keyReleased: null
                        };
                        
                        // Cargar el archivo en un sandbox
                        fetch(fileUrl)
                            .then(response => response.text())
                            .then(code => {
                                // Ejecutar el código en un contexto aislado
                                try {
                                    // Reemplazar funciones globales con asignaciones al sandbox
                                    var modifiedCode = code
                                        .replace(/function\\s+setup\\s*\\(\\s*\\)\\s*{/g, "sandbox.setup = function() {")
                                        .replace(/function\\s+draw\\s*\\(\\s*\\)\\s*{/g, "sandbox.draw = function() {")
                                        .replace(/function\\s+preload\\s*\\(\\s*\\)\\s*{/g, "sandbox.preload = function() {")
                                        .replace(/function\\s+mousePressed\\s*\\(\\s*\\)\\s*{/g, "sandbox.mousePressed = function() {")
                                        .replace(/function\\s+mouseMoved\\s*\\(\\s*\\)\\s*{/g, "sandbox.mouseMoved = function() {")
                                        .replace(/function\\s+mouseReleased\\s*\\(\\s*\\)\\s*{/g, "sandbox.mouseReleased = function() {")
                                        .replace(/function\\s+keyPressed\\s*\\(\\s*\\)\\s*{/g, "sandbox.keyPressed = function() {")
                                        .replace(/function\\s+keyReleased\\s*\\(\\s*\\)\\s*{/g, "sandbox.keyReleased = function() {");
                                    
                                    // Ejecutar el código modificado
                                    eval(modifiedCode);
                                    
                                    // Crear una instancia de P5.js con las funciones del sandbox
                                    var sketch = function(p) {
                                        // Sobrescribir createCanvas
                                        var originalCreateCanvas = p.createCanvas;
                                        p.createCanvas = function() {
                                            var canvas = originalCreateCanvas.call(p, canvasWidth, canvasHeight);
                                            
                                            // Asegurar que el canvas esté dentro del contenedor
                                            if (canvas.canvas && canvas.canvas.parentNode !== container) {
                                                container.appendChild(canvas.canvas);
                                            }
                                            
                                            return canvas;
                                        };
                                        
                                        // Exponer funciones y constantes de p5 al ámbito global para el sandbox
                                        var exposeP5Functions = function(pInstance) {
                                            // Crear un objeto para almacenar las funciones y valores originales
                                            var originalValues = {};
                                            
                                            // Guardar las funciones y valores originales y crear los globales
                                            for (var key in pInstance) {
                                                // Guardar el valor original si existe
                                                if (window[key] !== undefined) {
                                                    originalValues[key] = window[key];
                                                }
                                                
                                                if (typeof pInstance[key] === "function") {
                                                    // Crear una función global que llama a la función de p5
                                                    window[key] = (function(k) {
                                                        return function() {
                                                            return pInstance[k].apply(pInstance, arguments);
                                                        };
                                                    })(key);
                                                } else {
                                                    // Exponer constantes y otros valores
                                                    window[key] = pInstance[key];
                                                }
                                            }
                                            
                                            // Exponer constantes específicas de P5.js que podrían no estar en la instancia
                                            var p5Constants = ["RGB", "HSB", "HSL", "DEGREES", "RADIANS", 
                                                "CENTER", "CORNER", "CORNERS", "RADIUS",
                                                "LEFT", "RIGHT", "TOP", "BOTTOM", "CENTER",
                                                "PI", "TWO_PI", "HALF_PI", "QUARTER_PI"];
                                            
                                            for (var i = 0; i < p5Constants.length; i++) {
                                                var constName = p5Constants[i];
                                                if (p5[constName] !== undefined && window[constName] === undefined) {
                                                    window[constName] = p5[constName];
                                                    originalValues[constName] = undefined; // Para restaurar después
                                                }
                                            }
                                            
                                            return originalValues;
                                        };
                                        
                                        // Restaurar funciones originales
                                        var restoreOriginalFunctions = function(originalFunctions) {
                                            for (var key in originalFunctions) {
                                                window[key] = originalFunctions[key];
                                            }
                                        };
                                        
                                        // Asignar funciones del sandbox a la instancia
                                        if (sandbox.preload) {
                                            p.preload = function() {
                                                try {
                                                    // Exponer funciones de p5
                                                    var originalFunctions = exposeP5Functions(p);
                                                    
                                                    // Ejecutar preload
                                                    sandbox.preload.call(p);
                                                    
                                                    // Restaurar funciones originales
                                                    restoreOriginalFunctions(originalFunctions);
                                                } catch(e) {
                                                    console.error("Error en preload:", e);
                                                }
                                            };
                                        }
                                        
                                        if (sandbox.setup) {
                                            p.setup = function() {
                                                try {
                                                    // Exponer funciones de p5
                                                    var originalFunctions = exposeP5Functions(p);
                                                    
                                                    // Ejecutar setup
                                                    sandbox.setup.call(p);
                                                    
                                                    // Restaurar funciones originales
                                                    restoreOriginalFunctions(originalFunctions);
                                                    
                                                    // Si no se ha creado un canvas, crearlo
                                                    if (!p.canvas) {
                                                        p.createCanvas(canvasWidth, canvasHeight);
                                                    }
                                                } catch(e) {
                                                    console.error("Error en setup:", e);
                                                    p.createCanvas(canvasWidth, canvasHeight);
                                                }
                                            };
                                        } else {
                                            p.setup = function() {
                                                p.createCanvas(canvasWidth, canvasHeight);
                                            };
                                        }
                                        
                                        if (sandbox.draw) {
                                            p.draw = function() {
                                                try {
                                                    // Exponer funciones de p5
                                                    var originalFunctions = exposeP5Functions(p);
                                                    
                                                    // Ejecutar draw
                                                    sandbox.draw.call(p);
                                                    
                                                    // Restaurar funciones originales
                                                    restoreOriginalFunctions(originalFunctions);
                                                } catch(e) {
                                                    console.error("Error en draw:", e);
                                                    p.background(220);
                                                    p.fill(255, 0, 0);
                                                    p.textSize(16);
                                                    p.text("Error en draw()", 10, 30);
                                                }
                                            };
                                        } else {
                                            p.draw = function() {
                                                p.background(220);
                                                p.ellipse(p.width/2, p.height/2, 80, 80);
                                            };
                                        }
                                        
                                        // Asignar otros eventos
                                        if (sandbox.mousePressed) p.mousePressed = function() { 
                                            var originalFunctions = exposeP5Functions(p);
                                            sandbox.mousePressed.call(p);
                                            restoreOriginalFunctions(originalFunctions);
                                        };
                                        if (sandbox.mouseMoved) p.mouseMoved = function() { 
                                            var originalFunctions = exposeP5Functions(p);
                                            sandbox.mouseMoved.call(p);
                                            restoreOriginalFunctions(originalFunctions);
                                        };
                                        if (sandbox.mouseReleased) p.mouseReleased = function() { 
                                            var originalFunctions = exposeP5Functions(p);
                                            sandbox.mouseReleased.call(p);
                                            restoreOriginalFunctions(originalFunctions);
                                        };
                                        if (sandbox.keyPressed) p.keyPressed = function() { 
                                            var originalFunctions = exposeP5Functions(p);
                                            sandbox.keyPressed.call(p);
                                            restoreOriginalFunctions(originalFunctions);
                                        };
                                        if (sandbox.keyReleased) p.keyReleased = function() { 
                                            var originalFunctions = exposeP5Functions(p);
                                            sandbox.keyReleased.call(p);
                                            restoreOriginalFunctions(originalFunctions);
                                        };
                                    };
                                    
                                    // Crear la instancia
                                    var instance = new p5(sketch, container);
                                    window.p5InstancesToDestroy.push(instance);
                                } catch (error) {
                                    console.error("Error al cargar el archivo:", error);
                                    container.innerHTML = "<div style=\'color:red;padding:20px;\'>Error al cargar el archivo: " + error.message + "</div>";
                                }
                            })
                            .catch(error => {
                                console.error("Error al cargar el archivo:", error);
                                container.innerHTML = "<div style=\'color:red;padding:20px;\'>Error al cargar el archivo: " + error.message + "</div>";
                            });
                    }
                    ' : '') . '
                }
                
                // Configurar un intervalo para eliminar canvas fuera del contenedor
                setInterval(function() {
                    document.querySelectorAll("body > canvas.p5Canvas").forEach(function(canvas) {
                        canvas.remove();
                    });
                }, 1000);
            })();
        </script>';
    }

    protected function content_template() {
        ?>
<div class="twudd-p5-container" id="twudd-p5-{{id}}">
    <# if (settings.p5_source_type==='code' ) { #>
        <div class="elementor-placeholder">
            <div class="elementor-placeholder-title">P5.js Canvas</div>
            <div class="elementor-placeholder-dimensions">
                {{settings.canvas_width}} x {{settings.canvas_height}}
            </div>
        </div>
        <# } else if (settings.p5_source_type==='file' && settings.p5_file.url) { #>
            <div class="elementor-placeholder">
                <div class="elementor-placeholder-title">P5.js Canvas (Archivo)</div>
                <div class="elementor-placeholder-dimensions">
                    {{settings.canvas_width}} x {{settings.canvas_height}}
                </div>
            </div>
            <# } else if (settings.p5_source_type==='project' && settings.p5_project.url) { #>
                <div class="elementor-placeholder">
                    <div class="elementor-placeholder-title">P5.js Canvas (Proyecto)</div>
                    <div class="elementor-placeholder-dimensions">
                        {{settings.canvas_width}} x {{settings.canvas_height}}
                    </div>
                </div>
                <# } #>
</div>
<style>
#twudd-p5- {
        {
        id
    }
}

    {
    width: {
            {
            settings.canvas_width
        }
    }

    px;

    height: {
            {
            settings.canvas_height
        }
    }

    px;

    <# if (settings.responsive==='yes') {
        #>max-width: 100%;
        <#
    }

    #>background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

#twudd-p5- {
        {
        id
    }
}

.elementor-placeholder {
    text-align: center;
}
</style>
<?php
    }
}