@echo off
echo Starting Groq Meal Plan API Server...
cd /d "C:\xampp\htdocs\capstone_proj\Groq_Meal_Plan"
python -m uvicorn fastapi_app:app --host 0.0.0.0 --port 8002 --reload
pause
