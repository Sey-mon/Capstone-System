@echo off
echo ===============================
echo   Quick Docker Test
echo ===============================
echo.

echo 1. Stopping any existing containers...
docker-compose -f docker-compose.dev.yml down

echo.
echo 2. Starting services (this may take a few minutes)...
docker-compose -f docker-compose.dev.yml up -d

echo.
echo 3. Waiting for services to start...
timeout /t 30 /nobreak >nul

echo.
echo 4. Checking container status...
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo.
echo 5. Testing service availability...
echo.

echo Testing Laravel (may take a moment to boot)...
timeout /t 15 /nobreak >nul
echo Visit: http://localhost:8000

echo.
echo Testing Groq Meal Plan API...
echo Visit: http://localhost:8001/docs

echo.
echo Testing Treatment Model API...
echo Visit: http://localhost:8002/docs

echo.
echo MySQL is available at: localhost:3307
echo.

echo ===============================
echo   Test Complete!
echo ===============================
echo.
echo Open these URLs in your browser:
echo - Laravel App: http://localhost:8000
echo - Groq Meal API Docs: http://localhost:8001/docs  
echo - Treatment API Docs: http://localhost:8002/docs
echo.
echo To view logs: docker-compose -f docker-compose.dev.yml logs [service_name]
echo To stop: docker-compose -f docker-compose.dev.yml down
echo.
pause
