@echo off
echo Starting Malnutrition Assessment API Server...
echo.

REM Check if Python is available
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python is not installed or not in PATH
    echo Please install Python and try again
    pause
    exit /b 1
)

REM Change to the project directory
cd /d "%~dp0"

echo Current directory: %CD%
echo.

REM Check if required files exist
if not exist "api_server.py" (
    echo Error: api_server.py not found
    echo Please ensure you're in the correct directory
    pause
    exit /b 1
)

if not exist "malnutrition_model.py" (
    echo Error: malnutrition_model.py not found
    echo Please ensure all required files are present
    pause
    exit /b 1
)

echo Checking Python environment...
python -c "import fastapi" >nul 2>&1
if errorlevel 1 (
    echo Error: FastAPI not installed
    echo Installing required packages...
    pip install -r requirements.txt
    if errorlevel 1 (
        echo Error: Failed to install packages
        pause
        exit /b 1
    )
)

echo.
echo ========================================
echo   Malnutrition Assessment API Server
echo ========================================
echo.
echo Server will start at: http://127.0.0.1:8001
echo API Documentation: http://127.0.0.1:8001/docs
echo.
echo Press Ctrl+C to stop the server
echo.

REM Start the API server
python api_server.py

echo.
echo Server stopped.
pause
