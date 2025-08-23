Write-Host "Starting Groq Meal Plan API Server..." -ForegroundColor Green
Set-Location "C:\xampp\htdocs\capstone_proj\Groq_Meal_Plan"
python -m uvicorn fastapi_app:app --host 0.0.0.0 --port 8002 --reload
Read-Host "Press Enter to exit"
