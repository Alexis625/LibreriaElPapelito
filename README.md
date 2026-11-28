# LibreriaElPapelito
Sistema de gestión de inventario y ventas para Mini Librería El Papelito, desarrollado con PHP y MySQL, usando mvc
Descripción

El sistema reúne herramientas para administrar las operaciones principales de una librería desde una aplicación web local. Incluye módulos para el control de productos e inventario, compras, ventas, clientes, proveedores, reservaciones, usuarios y reportes.


- PHP 8.0 o superior – lógica del servidor.
- MySQL – almacenamiento de datos.
- PDO – conexión entre PHP y la base de datos.
- HTML5 – estructura de las páginas.
- CSS3 – estilos e interfaz.
- JavaScript – interacciones del lado del cliente.
- Apache – servidor web local (incluido en XAMPP).
- XAMPP – entorno de desarrollo local.

- 
Funcionalidades y módulos
- Autenticación: inicio y cierre de sesión.
- Panel principal: acceso al dashboard administrativo.
- Productos: registro, edición, consulta y cambio de estado de productos.
- Categorías: consulta, creación y eliminación de categorías.
- Inventario: ajustes y registro de movimientos.
- Proveedores: administración de proveedores.
- Compras: registro y consulta de compras.
- Ventas: registro y consulta de ventas.
- Clientes: administración de información de clientes.
- Reservaciones: gestión y actualización del estado de las reservaciones.
- Reportes: consulta de información y exportación de reportes.
- Usuarios: administración de cuentas del sistema.

Requisitos
Antes de instalar el proyecto, asegúrate de tener:
- Windows, Linux o macOS.
- XAMPP instalado, con Apache y MySQL disponibles.
- PHP 8.0 o superior.
- Extensión PDO y controlador pdo_mysql habilitados en PHP.
- Navegador web actualizado.
Instalación y configuración
1. Copiar el proyecto
Coloca la carpeta el_papelito dentro del directorio htdocs de XAMPP. En Windows, normalmente será:
C:\xampp\htdocs\el_papelito
2. Iniciar XAMPP
Abre el panel de control de XAMPP e inicia los servicios Apache y MySQL. La configuración actual del proyecto utiliza localhost y el puerto MySQL predeterminado 3306.
3. Crear e importar la base de datos
1. Abre phpMyAdmin desde http://localhost/phpmyadmin/.
2. Crea una base de datos llamada sistema_libreria, si el archivo SQL no la crea automáticamente.
3. Selecciona la base de datos sistema_libreria.
4. Entra en la pestaña Importar.
5. Selecciona el archivo database/sistema_libreria.sql y ejecuta la importación.
El archivo SQL contiene las tablas y datos iniciales del sistema.
4. Revisar la conexión a la base de datos
Abre el archivo:
app/config/database.php
La configuración local predeterminada del proyecto es:
private $host = "localhost";
private $dbname = "sistema_libreria";
private $username = "root";
private $password = "";
Si el MySQL utiliza otros datos de acceso, modifica estos valores. En una instalación estándar de XAMPP, el usuario suele ser root y la contraseña puede estar vacía, pero debes confirmar la configuración de su equipo.
5. Abrir el sistema
Con Apache y MySQL activos, visita:
http://localhost/el_papelito/public/
La configuración de rutas incluida utiliza /el_papelito/public como ruta base. Si cambias el nombre de la carpeta o instalas el proyecto en otra ubicación también hay que actualizar la ruta base en routes/web.php y public/.htaccess, además de revisar las redirecciones que contienen esa ruta.
Base de datos
La base de datos se llama sistema_libreria y contiene las siguientes tablas principales:
- usuarios y roles
- productos y categorias
- proveedores
- compras y detalle_compra
- ventas y detalle_venta
- clientes
- reservas y detalle_reserva
- movimientos_inventario
