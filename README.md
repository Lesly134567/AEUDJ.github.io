[README.md](https://github.com/user-attachments/files/25053223/README.md)
# 🚌 AEUDJ - Sistema de Transporte Estudiantil

Sistema web para la gestión de transporte entre Jarabacoa y La Vega, República Dominicana.

## 📋 Contenido del Proyecto

```
aeudj/
├── index.php              # Página principal con listado de horarios
├── registrar.php          # Registro e inicio de sesión de usuarios
├── votar.php              # Selección de horarios de transporte
├── lista.php              # Lista de pasajeros organizada por horario
├── gracias.php            # Confirmación de voto
├── cambios.php            # Gestión de cambios de horario
├── selector_cambios.php   # Selector para cambiar horarios
├── admin.php              # Panel de administración
├── marcar.php             # Marcar asistencia (admin)
├── marcar_tarde.php       # Marcar llegada tarde (admin)
├── no_subieron.php        # Lista de quienes no subieron
├── notificacion.php       # Notificaciones para admin
├── admin_votar_extra.php  # Votar por otros (admin)
├── bloquear.php           # Bloquear usuarios (admin)
├── panel.php              # Panel simple (legacy)
├── db.php                 # Conexión a base de datos
├── config.php             # Configuración global
├── conexion.php           # Test de conexión
├── test.php               # Verificación de conexión
└── aeudj.sql              # Estructura de la base de datos
```

## 🚀 Instalación Paso a Paso

### Paso 1: Requisitos
- **PHP** 7.4 o superior
- **MySQL/MariaDB** 5.7 o superior
- **Servidor web** (Apache/Nginx) o XAMPP/WAMP/MAMP
- Navegador web moderno

### Paso 2: Configurar la Base de Datos

1. **Abre phpMyAdmin** o tu cliente MySQL preferido
2. **Crea la base de datos** ejecutando el archivo `aeudj.sql`:

   ```bash
   # Método 1: Usando línea de comandos
   mysql -u root -p < aeudj.sql

   # Método 2: Usando phpMyAdmin
   # Ve a Importar > Seleccionar archivo > aeudj.sql > Continuar
   ```

3. **Verifica que se crearon las tablas**:
   - `usuarios` - Información de estudiantes
   - `votos` - Reservas de transporte
   - `cambios` - Historial de cambios
   - `notificaciones` - Mensajes del sistema

### Paso 3: Configurar la Conexión

Edita el archivo `config.php` con tus credenciales de base de datos:

```php
// config.php - Configuración de Base de Datos
define('DB_HOST', 'localhost');      // Servidor de base de datos
define('DB_PORT', 3306);             // Puerto (3306 por defecto)
define('DB_NAME', 'aeudj');          // Nombre de la base de datos
define('DB_USER', 'root');           // Usuario de MySQL
define('DB_PASS', 'tu_contraseña');  // Contraseña de MySQL
```

**Nota importante**: Si usas XAMPP por defecto, el usuario es `root` y la contraseña está vacía `''`.

### Paso 4: Configurar Credenciales de Admin

En el mismo archivo `config.php`, cambia las credenciales de administrador:

```php
// config.php - Credenciales de Administrador
define('ADMIN_USER', 'admin');       // Cambia esto
define('ADMIN_PASS', 'aeudj2025');   // Cambia esto por seguridad
```

### Paso 5: Instalar en el Servidor

#### Opción A: XAMPP (Local)
```bash
# Copia todos los archivos a la carpeta htdocs
cp -r /ruta/del/proyecto/* /opt/lampp/htdocs/aeudj/

# O en Windows, copia a C:\xampp\htdocs\aeudj\
```

#### Opción B: Hosting Web
1. Comprime todos los archivos en un `.zip`
2. Súbelo a tu hosting mediante FTP o el administrador de archivos
3. Extrae los archivos en la carpeta `public_html/` o equivalente

### Paso 6: Crear Carpeta de Imágenes

Crea una carpeta llamada `img` en la raíz del proyecto:

```bash
mkdir img
```

Y coloca ahí:
- `comite.jpg` - Logo del comité AEUDJ (opcional pero recomendado)
- `logo-comite.png` - Logo alternativo (opcional)

Si no tienes imágenes, el sistema funcionará igual pero sin mostrar logos.

### Paso 7: Verificar Instalación

1. **Test de conexión**: Abre `http://localhost/aeudj/test.php`
   - Debe mostrar: "✅ Conexión OK"

2. **Test de base de datos**: Abre `http://localhost/aeudj/conexion.php`
   - Debe mostrar: "Conexión a la base de datos exitosa."

3. **Página principal**: Abre `http://localhost/aeudj/`

## ⚙️ Configuración de Horarios

Los horarios de transporte están definidos en `config.php`:

```php
$transportSchedules = [
    ["time" => "7:00 AM",  "route" => "Jarabacoa → La Vega", "fullText" => "7:00 AM Jarabacoa → La Vega"],
    ["time" => "9:00 AM",  "route" => "Jarabacoa → La Vega", "fullText" => "9:00 AM Jarabacoa → La Vega"],
    ["time" => "12:10 PM", "route" => "La Vega → Jarabacoa", "fullText" => "12:10 PM La Vega → Jarabacoa"],
    // ... más horarios
];
```

**Para modificar horarios**: Edita este array en `config.php`.

## 🎮 Uso del Sistema

### Para Estudiantes:
1. **Registro**: Accede a `registrar.php` y crea tu cuenta
2. **Inicio de sesión**: Usa tu matrícula para entrar
3. **Seleccionar horario**: Elige tu viaje de ida y vuelta en `votar.php`
4. **Ver lista**: Consulta quién viaja en cada horario en `lista.php`
5. **Cambios**: Si necesitas cambiar tu horario de vuelta, usa los botones en `lista.php`

### Para Administradores:
1. **Acceso**: Ve a `admin.php` e ingresa las credenciales configuradas
2. **Marcar asistencia**: Indica quién subió, no subió o llegó tarde
3. **Lista de espera**: Gestiona cupos adicionales automáticamente
4. **Notificaciones**: Revisa cambios reportados por estudiantes

## 🔧 Solución de Problemas

### Error: "Error de conexión"
- Verifica que MySQL esté corriendo
- Revisa que las credenciales en `config.php` sean correctas
- Asegúrate de que la base de datos `aeudj` exista

### Error: "Matrícula ya registrada"
- Usa la opción "Iniciar sesión" en lugar de registrarte nuevamente

### Error: "No se muestran los horarios"
- Verifica que el archivo `config.php` esté incluyendo correctamente `$transportSchedules`

### Error 404 en imágenes
- Crea la carpeta `img/` y añade las imágenes necesarias
- O elimina las referencias a imágenes en los archivos HTML

## 📝 Notas Importantes

- **Ciclo de votación**: Los votos se reinician cada día a las 22:00 (10 PM)
- **Límite de pasajeros**: Se maneja lista de espera automáticamente
- **Zona horaria**: Configurada para República Dominicana (`America/Santo_Domingo`)
- **Seguridad**: Cambia las credenciales de admin por defecto en producción

## 🛠️ Soporte Técnico

Si encuentras errores adicionales:
1. Revisa los logs de error de PHP
2. Verifica permisos de carpetas (755 para directorios, 644 para archivos)
3. Asegúrate de que la extensión PDO de PHP esté habilitada

## 📄 Licencia

Proyecto desarrollado para AEUDJ - Asociación de Estudiantes Universitarios de Jarabacoa.

---
**Versión**: 1.0  
**Fecha**: 2025  
**Ubicación**: Jarabacoa, República Dominicana
