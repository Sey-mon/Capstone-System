# Docker Setup for Capstone Project

This project has been dockerized to provide a consistent development and production environment. The system consists of multiple services:

- **Laravel Application** (Main web application)
- **Groq Meal Plan API** (FastAPI service)
- **Treatment Model API** (FastAPI service)
- **MySQL Database**
- **Nginx Reverse Proxy** (Production only)

## Prerequisites

- Docker Desktop for Windows
- Git

## Quick Start

### 1. Development Mode (Recommended)

```powershell
# Simple startup - just double-click this file:
.\start-dev.bat

# Or manually:
docker-compose -f docker-compose.dev.yml up --build
```

### 2. Production Mode

```powershell
docker-compose up --build
```

### 3. Quick Test

```powershell
# Test all services quickly:
.\quick-test.bat
```

## Service URLs

- **Laravel App**: http://localhost:8000
- **Groq Meal Plan API**: http://localhost:8001/docs
- **Treatment Model API**: http://localhost:8002/docs
- **MySQL Database**: localhost:3307 (dev) / localhost:3306 (prod)

## Service Details

### Laravel Application
- **Port**: 8000
- **Technology**: PHP 8.2, Laravel
- **Database**: MySQL (containerized)

### Groq Meal Plan API
- **Port**: 8001
- **Technology**: Python 3.11, FastAPI
- **Dependencies**: groq, langchain, streamlit, etc.

### Treatment Model API
- **Port**: 8002
- **Technology**: Python 3.11, FastAPI
- **Dependencies**: pandas, scikit-learn, numpy, etc.

### MySQL Database
- **Dev Port**: 3307 (to avoid conflict with XAMPP)
- **Prod Port**: 3306
- **Database**: capstone_db
- **Username**: laravel_user
- **Password**: laravel_password

## Docker Commands

### Basic Operations

```powershell
# Start all services
docker-compose up

# Start services in background
docker-compose up -d

# Stop all services
docker-compose down

# Rebuild and start services
docker-compose up --build

# View logs
docker-compose logs

# View logs for specific service
docker-compose logs laravel_app
```

### Development Commands

```powershell
# Start development environment
docker-compose -f docker-compose.dev.yml up

# Execute commands in Laravel container
docker-compose exec laravel_app php artisan migrate
docker-compose exec laravel_app php artisan tinker

# Access MySQL
docker-compose exec mysql mysql -u laravel_user -p capstone_db

# SSH into containers
docker-compose exec laravel_app bash
docker-compose exec groq_meal_api bash
docker-compose exec treatment_model_api bash
```

### Maintenance Commands

```powershell
# Remove all containers and volumes
docker-compose down -v

# Remove unused Docker resources
docker system prune

# View running containers
docker-compose ps

# Restart specific service
docker-compose restart laravel_app
```

## Environment Configuration

### Laravel (.env.docker)
The Laravel application uses `.env.docker` which is automatically copied during build. Key configurations:

- Database connection points to the MySQL container
- API URLs point to the internal container names
- Debug mode can be toggled for development/production

### Python APIs
Both FastAPI services use environment variables:

- `DB_HOST=mysql` (container name)
- `DB_DATABASE=capstone_db`
- `DB_USERNAME=laravel_user`
- `DB_PASSWORD=laravel_password`

## Volume Mounts

### Development Mode
- Source code is mounted as volumes for hot reloading
- Database data persists in named volumes

### Production Mode
- Code is copied into containers during build
- Only data volumes are mounted

## Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 3306, 8000, 8001, 8002 are not in use
2. **Database connection**: Wait for MySQL to fully start before accessing applications
3. **Permission issues**: Ensure Docker has permission to access the project directory

### Useful Commands

```powershell
# Check container status
docker-compose ps

# View container logs
docker-compose logs [service_name]

# Restart a specific service
docker-compose restart [service_name]

# Force recreate containers
docker-compose up --force-recreate

# Remove everything and start fresh
docker-compose down -v --remove-orphans
docker-compose up --build
```

### Laravel Specific

```powershell
# Clear Laravel caches
docker-compose exec laravel_app php artisan config:clear
docker-compose exec laravel_app php artisan cache:clear
docker-compose exec laravel_app php artisan route:clear

# Run migrations
docker-compose exec laravel_app php artisan migrate

# Generate app key
docker-compose exec laravel_app php artisan key:generate
```

## Production Deployment

For production deployment:

1. Use `docker-compose.yml` (not the dev version)
2. Set proper environment variables
3. Use external database for better performance
4. Configure proper SSL certificates for Nginx
5. Set up proper logging and monitoring

## Security Notes

- Change default database passwords in production
- Use environment variables for sensitive data
- Configure proper firewall rules
- Use HTTPS in production
- Regularly update base images

## API Documentation

- **Groq Meal Plan API**: http://localhost:8001/docs
- **Treatment Model API**: http://localhost:8002/docs

## Support

For issues with the Docker setup, check:

1. Docker Desktop is running
2. No port conflicts exist
3. Sufficient disk space available
4. Windows features for containers are enabled
