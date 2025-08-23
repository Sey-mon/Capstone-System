@echo off
echo ====================================
echo  Capstone Project - Development
echo ====================================
echo.

echo Checking Docker...
docker --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Docker is not running. Please start Docker Desktop.
    pause
    exit /b 1
)

echo Starting development environment...
echo.
echo Services will be available at:
echo - Laravel App: http://localhost:8000
echo - Groq Meal API: http://localhost:8001/docs
echo - Treatment API: http://localhost:8002/docs
echo - MySQL: localhost:3307
echo.

docker-compose -f docker-compose.dev.yml up --build

echo.
echo Development environment stopped.
pause
