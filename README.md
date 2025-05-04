# TWUDD Elementor Extensions

Plugin de WordPress que extiende las capacidades de Elementor con widgets personalizados y funcionalidades avanzadas.

## Descripción

TWUDD Elementor Extensions es un plugin diseñado para añadir widgets personalizados y funcionalidades a Elementor, el popular constructor de páginas para WordPress. El plugin está diseñado para ser extensible y modular, permitiendo agregar nuevas características fácilmente.

## Requisitos

- WordPress 5.9 o superior
- Elementor 3.5.0 o superior
- PHP 7.4 o superior

## Widgets incluidos

### P5.js Canvas

Widget que permite integrar sketches de [P5.js](https://p5js.org/) en páginas de WordPress. Ofrece tres modos de uso:

- **Editor de Código**: Escribe código P5.js directamente en Elementor
- **Archivo Individual**: Carga un archivo JavaScript con tu código P5.js
- **Proyecto ZIP**: Carga un proyecto P5.js completo con múltiples archivos y recursos

Consulta la [guía de usuario](docs/guia-p5js.md) para obtener más información sobre cómo crear sketches P5.js compatibles con el widget.

### Widget de Ejemplo

Widget básico que muestra cómo crear widgets personalizados para Elementor.

## Instalación

1. Descarga el archivo ZIP del plugin
2. Ve a WordPress > Plugins > Añadir nuevo > Subir plugin
3. Selecciona el archivo ZIP descargado
4. Activa el plugin

## Uso

1. Edita una página con Elementor
2. Busca los widgets en la sección "TWUDD"
3. Arrastra el widget deseado a la página
4. Configura el widget según tus necesidades

## Desarrollo

El plugin está diseñado para ser extensible. Puedes agregar nuevos widgets y funcionalidades siguiendo la estructura del plugin.

### Estructura del plugin

```
├── docs/                  # Documentación
├── includes/              # Archivos principales del plugin
│   └── widgets/           # Widgets personalizados
├── LICENSE                # Licencia MIT
├── README.md              # Este archivo
└── twudd.php              # Archivo principal del plugin
```

## Licencia

Este plugin está licenciado bajo la [Licencia MIT](LICENSE).

## Contribuir

Las contribuciones son bienvenidas. Si tienes alguna idea o sugerencia, no dudes en abrir un issue o enviar un pull request.

---

Desarrollado por [Tu Nombre](https://tu-sitio-web.com)
