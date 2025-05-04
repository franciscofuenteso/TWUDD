# Guía para crear sketches P5.js compatibles con el widget TWUDD

Esta guía te ayudará a crear sketches de P5.js que funcionen correctamente con el widget P5.js Canvas de TWUDD para Elementor.

## Índice

1. [Modos de uso del widget](#modos-de-uso-del-widget)
2. [Estructura básica de un sketch P5.js](#estructura-básica-de-un-sketch-p5js)
3. [Creación de un proyecto ZIP](#creación-de-un-proyecto-zip)
4. [Mejores prácticas](#mejores-prácticas)
5. [Solución de problemas comunes](#solución-de-problemas-comunes)
6. [Ejemplos](#ejemplos)

## Modos de uso del widget

El widget P5.js Canvas de TWUDD ofrece tres modos de uso:

1. **Editor de Código**: Permite escribir código P5.js directamente en el editor de Elementor.
2. **Archivo Individual**: Permite cargar un archivo JavaScript con el código P5.js.
3. **Proyecto ZIP**: Permite cargar un archivo ZIP con un proyecto P5.js completo, incluyendo múltiples archivos JavaScript, imágenes, sonidos, etc.

## Estructura básica de un sketch P5.js

Un sketch P5.js básico debe incluir al menos las funciones `setup()` y `draw()`:

```javascript
function setup() {
  createCanvas(400, 400);
}

function draw() {
  background(220);
  ellipse(width/2, height/2, 80, 80);
}
```

### Consideraciones importantes

- **No es necesario incluir la biblioteca P5.js** en tu código, ya que el widget la carga automáticamente.
- **No es necesario crear una instancia de P5.js** con `new p5()`, ya que el widget lo hace automáticamente.
- **El tamaño del canvas** se configura en el widget, por lo que el valor que pases a `createCanvas()` será ignorado.
- **Todas las funciones de P5.js** están disponibles, incluyendo `preload()`, `mousePressed()`, `keyPressed()`, etc.

## Creación de un proyecto ZIP

Para crear un proyecto ZIP que funcione correctamente con el widget:

1. Crea una carpeta para tu proyecto.
2. Crea un archivo principal (por ejemplo, `sketch.js`) que contenga las funciones `setup()` y `draw()`.
3. Agrega cualquier otro archivo JavaScript, imágenes, sonidos, etc. que necesites.
4. Comprime la carpeta en un archivo ZIP.

### Estructura recomendada

```
mi-proyecto/
├── sketch.js (archivo principal)
├── clases/
│   ├── Particula.js
│   └── Sistema.js
└── assets/
    ├── imagenes/
    │   └── textura.png
    └── sonidos/
        └── efecto.mp3
```

### Archivo principal

El widget buscará automáticamente un archivo principal en el siguiente orden:

1. `sketch.js`
2. `index.js`
3. `main.js`
4. `app.js`
5. `script.js`

Si ninguno de estos archivos existe, se utilizará el primer archivo JavaScript encontrado.

### Carga de recursos

Para cargar imágenes, sonidos u otros recursos, utiliza rutas relativas:

```javascript
function preload() {
  // Correcto: ruta relativa
  textura = loadImage('assets/imagenes/textura.png');
  
  // También funciona: ruta relativa con ./
  sonido = loadSound('./assets/sonidos/efecto.mp3');
  
  // Incorrecto: ruta absoluta (no funcionará)
  // textura = loadImage('/assets/imagenes/textura.png');
}
```

## Mejores prácticas

### 1. Usa el modo de instancia

Aunque el widget funciona con el modo global de P5.js, es recomendable usar el modo de instancia para evitar conflictos con otras bibliotecas:

```javascript
// En lugar de esto:
function setup() {
  createCanvas(400, 400);
}

// Considera usar esto:
const sketch = function(p) {
  p.setup = function() {
    p.createCanvas(400, 400);
  };
  
  p.draw = function() {
    p.background(220);
    p.ellipse(p.width/2, p.height/2, 80, 80);
  };
};

// No es necesario crear la instancia, el widget lo hará automáticamente
// new p5(sketch);
```

### 2. Limpia los recursos

Si tu sketch crea recursos que necesitan ser liberados (como conexiones WebSocket, temporizadores, etc.), asegúrate de limpiarlos cuando el sketch se detenga:

```javascript
let temporizador;

function setup() {
  createCanvas(400, 400);
  temporizador = setInterval(hacerAlgo, 1000);
}

// Esta función será llamada cuando el widget se destruya
function remove() {
  clearInterval(temporizador);
}
```

### 3. Maneja los errores

Incluye manejo de errores en tu código para evitar que el sketch se rompa por completo:

```javascript
function preload() {
  try {
    imagen = loadImage('assets/imagen.png');
  } catch (error) {
    console.error('Error al cargar la imagen:', error);
    // Crear una imagen de reemplazo
    imagen = createGraphics(100, 100);
    imagen.background(255, 0, 0);
    imagen.text('Error', 10, 50);
  }
}
```

### 4. Optimiza el rendimiento

- Evita crear nuevos objetos en cada frame en la función `draw()`.
- Utiliza `p5.Vector.set()` en lugar de crear nuevos vectores.
- Considera usar `noLoop()` si tu animación no necesita actualizarse constantemente.
- Utiliza `frameRate()` para limitar la velocidad de fotogramas si es necesario.

## Solución de problemas comunes

### El canvas no aparece

- Asegúrate de llamar a `createCanvas()` en la función `setup()`.
- Verifica que no haya errores en la consola del navegador.
- Comprueba que el tamaño del canvas configurado en el widget sea razonable.

### Las imágenes o sonidos no cargan

- Verifica que las rutas sean correctas y relativas al archivo principal.
- Asegúrate de cargar los recursos en la función `preload()`.
- Comprueba que los archivos estén incluidos en el ZIP y con los nombres correctos.

### El sketch funciona localmente pero no en el widget

- Asegúrate de no estar utilizando características específicas del navegador que puedan estar bloqueadas.
- Verifica que no estés utilizando bibliotecas externas que no estén incluidas en el ZIP.
- Comprueba que no estés utilizando rutas absolutas para cargar recursos.

## Ejemplos

### Ejemplo básico

```javascript
function setup() {
  createCanvas(400, 400);
}

function draw() {
  background(220);
  ellipse(mouseX, mouseY, 80, 80);
}
```

### Ejemplo con carga de imágenes

```javascript
let img;

function preload() {
  img = loadImage('assets/imagen.png');
}

function setup() {
  createCanvas(400, 400);
}

function draw() {
  background(220);
  image(img, mouseX, mouseY, 100, 100);
}
```

### Ejemplo con clases

```javascript
class Particula {
  constructor(x, y) {
    this.pos = createVector(x, y);
    this.vel = createVector(random(-1, 1), random(-1, 1));
  }
  
  update() {
    this.pos.add(this.vel);
    
    if (this.pos.x < 0 || this.pos.x > width) {
      this.vel.x *= -1;
    }
    if (this.pos.y < 0 || this.pos.y > height) {
      this.vel.y *= -1;
    }
  }
  
  display() {
    ellipse(this.pos.x, this.pos.y, 20, 20);
  }
}

let particulas = [];

function setup() {
  createCanvas(400, 400);
  for (let i = 0; i < 10; i++) {
    particulas.push(new Particula(random(width), random(height)));
  }
}

function draw() {
  background(220);
  for (let p of particulas) {
    p.update();
    p.display();
  }
}
```

### Ejemplo con modo de instancia

```javascript
const sketch = function(p) {
  let x = 100;
  let y = 100;
  
  p.setup = function() {
    p.createCanvas(400, 400);
  };
  
  p.draw = function() {
    p.background(0);
    p.fill(255);
    p.rect(x, y, 50, 50);
  };
  
  p.mousePressed = function() {
    x = p.mouseX;
    y = p.mouseY;
  };
};

// No es necesario crear la instancia, el widget lo hará automáticamente
// new p5(sketch);
```

### Ejemplo completo con proyecto ZIP

Estructura del proyecto:

```
mi-proyecto/
├── sketch.js
├── Particula.js
└── assets/
    └── textura.png
```

sketch.js:
```javascript
let sistema;
let textura;

function preload() {
  textura = loadImage('assets/textura.png');
}

function setup() {
  createCanvas(400, 400);
  sistema = new SistemaParticulas(width/2, height/2, textura);
}

function draw() {
  background(0);
  sistema.run();
}

function mouseMoved() {
  sistema.addParticula(mouseX, mouseY);
}
```

Particula.js:
```javascript
class Particula {
  constructor(x, y, textura) {
    this.pos = createVector(x, y);
    this.vel = createVector(random(-1, 1), random(-1, 1));
    this.acc = createVector(0, 0.1);
    this.textura = textura;
    this.vida = 255;
  }
  
  update() {
    this.vel.add(this.acc);
    this.pos.add(this.vel);
    this.vida -= 2;
  }
  
  display() {
    tint(255, this.vida);
    image(this.textura, this.pos.x, this.pos.y, 20, 20);
  }
  
  isDead() {
    return this.vida <= 0;
  }
}

class SistemaParticulas {
  constructor(x, y, textura) {
    this.origen = createVector(x, y);
    this.particulas = [];
    this.textura = textura;
  }
  
  addParticula(x, y) {
    this.particulas.push(new Particula(x, y, this.textura));
  }
  
  run() {
    for (let i = this.particulas.length - 1; i >= 0; i--) {
      let p = this.particulas[i];
      p.update();
      p.display();
      if (p.isDead()) {
        this.particulas.splice(i, 1);
      }
    }
  }
}
```

---

Con esta guía, deberías poder crear sketches P5.js que funcionen correctamente con el widget P5.js Canvas de TWUDD para Elementor. Si tienes alguna pregunta o problema, no dudes en contactar al soporte.
