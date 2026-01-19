# 🌟 Perfumería Luxury - Backend API

E-commerce de perfumería con API REST completa desarrollado con Laravel 10.

## 🎯 Descripción

Sistema backend para tienda online de perfumes exclusivos

## 🛠️ Stack Tecnológico

- **Framework**: Laravel 10
- **Base de datos**: MySQL
- **Autenticación**: Laravel Sanctum
- **PHP**: 8.1+
- **Composer**: Gestor de dependencias

## 📋 Características

### Backend API
- ✅ Autenticación con tokens (Sanctum)
- ✅ Sistema de roles (Admin/Customer)
- ✅ CRUD completo de productos
- ✅ Gestión de categorías
- ✅ Sistema de pedidos
- ✅ Carrito de compras
- ✅ Filtros y búsqueda
- ✅ Panel de administración

### Modelos y Relaciones
- **User**: Usuarios con roles
- **Category**: Categorías de perfumes
- **Product**: Productos con stock y precios
- **Order**: Pedidos de clientes
- **OrderItem**: Detalle de pedidos

## 🚀 Instalación

### Requisitos
- PHP >= 8.1
- Composer
- MySQL
- Git

### Pasos
```bash
# 1. Clonar repositorio
git clone https://github.com/jpiedradev/perfumeria-api.git
cd perfumeria-api

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_DATABASE=perfumeria_db
DB_USERNAME=root
DB_PASSWORD=

# 5. Crear base de datos
# Crear 'perfumeria_db' en MySQL

# 6. Migrar base de datos
php artisan migrate

# 7. Insertar datos de prueba
# Ejecutar SQL proporcionado en /database/sql/seed_data.sql

# 8. Iniciar servidor
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

## 📚 Endpoints API

### Autenticación
```
POST   /api/register      - Registrar usuario
POST   /api/login         - Iniciar sesión
POST   /api/logout        - Cerrar sesión
GET    /api/user          - Usuario actual
```

### Productos
```
GET    /api/products           - Listar productos
GET    /api/products/{id}      - Ver producto
GET    /api/products/featured  - Productos destacados
```

### Categorías
```
GET    /api/categories    - Listar categorías
```

### Pedidos (requiere autenticación)
```
POST   /api/orders        - Crear pedido
GET    /api/orders        - Mis pedidos
GET    /api/orders/{id}   - Detalle de pedido
```

### Admin (requiere rol admin)
```
POST   /api/admin/products       - Crear producto
PUT    /api/admin/products/{id}  - Actualizar producto
DELETE /api/admin/products/{id}  - Eliminar producto
GET    /api/admin/orders         - Todos los pedidos
PATCH  /api/admin/orders/{id}    - Cambiar estado
```

## 📊 Estructura de Base de Datos
```
users
├── id
├── name
├── email
├── password
├── role (customer/admin)
├── phone
└── address

categories
├── id
├── name
├── slug
└── description

products
├── id
├── category_id (FK)
├── name
├── slug
├── description
├── price
├── stock
├── image
├── brand
├── size
└── featured

orders
├── id
├── user_id (FK)
├── total
├── status
├── shipping_address
└── phone

order_items
├── id
├── order_id (FK)
├── product_id (FK)
├── quantity
└── price
```

## 🎨 Frontend

El frontend con React + Vite + Shadcn/ui + Aceternity UI estará en un repositorio separado:
- Repositorio: `perfumeria-web` (próximamente)

## 📦 Datos de Prueba

- **3 Usuarios**: 1 admin, 2 clientes
- **3 Categorías**: Nicho, Diseñador, Árabe
- **16 Productos**: Perfumes realistas con precios variados

## 🔧 Comandos Útiles
```bash
# Limpiar caché
php artisan optimize:clear

# Ver rutas
php artisan route:list

# Consola interactiva
php artisan tinker

# Resetear BD
php artisan migrate:fresh

# Info de BD
php artisan db:show
```

## 👨‍💻 Autor

**Johan Piedra**
- GitHub: [@jpiedradev](https://github.com/jpiedradev)
- Email: jpiedra.dev@gmail.com

## 📄 Licencia

Este proyecto es de código abierto bajo la Licencia MIT.

---

⭐ **Si te gustó este proyecto, dale una estrella en GitHub**
