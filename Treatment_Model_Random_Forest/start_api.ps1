# Malnutrition Assessment API Server Startup Script
# PowerShell version for better compatibility

Write-Host "Starting Malnutrition Assessment API Server..." -ForegroundColor Green
Write-Host ""

# Check if Python is available
try {
    $pythonVersion = python --version 2>&1
    Write-Host "Python found: $pythonVersion" -ForegroundColor Blue
} catch {
    Write-Host "Error: Python is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Python and try again" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Change to the script directory
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptPath

Write-Host "Current directory: $PWD" -ForegroundColor Blue
Write-Host ""

# Check if required files exist
if (-not (Test-Path "api_server.py")) {
    Write-Host "Error: api_server.py not found" -ForegroundColor Red
    Write-Host "Please ensure you're in the correct directory" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

if (-not (Test-Path "malnutrition_model.py")) {
    Write-Host "Error: malnutrition_model.py not found" -ForegroundColor Red
    Write-Host "Please ensure all required files are present" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Checking Python environment..." -ForegroundColor Blue
try {
    python -c "import fastapi" 2>$null
    Write-Host "FastAPI is installed" -ForegroundColor Green
} catch {
    Write-Host "FastAPI not found. Installing required packages..." -ForegroundColor Yellow
    try {
        pip install -r requirements.txt
        Write-Host "Packages installed successfully" -ForegroundColor Green
    } catch {
        Write-Host "Error: Failed to install packages" -ForegroundColor Red
        Read-Host "Press Enter to exit"
        exit 1
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Malnutrition Assessment API Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Server will start at: http://127.0.0.1:8001" -ForegroundColor Yellow
Write-Host "API Documentation: http://127.0.0.1:8001/docs" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Magenta
Write-Host ""

# Start the API server
try {
    python api_server.py
} catch {
    Write-Host ""
    Write-Host "Error starting server: $_" -ForegroundColor Red
} finally {
    Write-Host ""
    Write-Host "Server stopped." -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
}
