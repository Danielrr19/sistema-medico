# API de Gestión Médica

API RESTful con Laravel, Sanctum (autenticación), L5-Swagger (documentación) y MySQL. Evaluación parcial de *Ejecución de pruebas de validación y depuración*, Universidad Luterana Salvadoreña, Ciclo II-2026.

## Requisitos

- PHP 8.2 o superior (probado con PHP 8.4), con la extensión `pdo_sqlite` habilitada (las pruebas la usan)
- Composer
- MySQL (por ejemplo, el de XAMPP)

## Instalación

1. Clonar el repositorio e instalar las dependencias:

```bash
git clone https://github.com/Danielrr19/sistema-medico.git
cd sistema-medico
composer install
```

2. Crear el archivo de configuración y generar la clave de la aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

En Windows con PowerShell, el primer comando es `copy .env.example .env`.

3. Crear la base de datos en MySQL (desde phpMyAdmin o la consola de MySQL):

```sql
CREATE DATABASE gestion_medica_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Abrir el archivo `.env` y verificar que la conexión a la base de datos quede así:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_medica_db
DB_USERNAME=root
DB_PASSWORD=
```

5. Crear las tablas:

```bash
php artisan migrate
```

6. Generar la documentación de Swagger y levantar el servidor:

```bash
php artisan l5-swagger:generate
php artisan serve
```

## Uso

- Documentación interactiva (Swagger): http://localhost:8000/api/documentation
- Para probar los endpoints protegidos: ejecutar `POST /register` (o `POST /login`), copiar el `token` de la respuesta, pulsar **Authorize**, pegar el token (sin la palabra Bearer) y confirmar.

## Pruebas

```bash
php artisan test
```

Deben pasar las 49 pruebas (125 aserciones). Usan una base SQLite en memoria, así que no modifican los datos de MySQL.