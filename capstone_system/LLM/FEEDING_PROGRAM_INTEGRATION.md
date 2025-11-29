# Feeding Program Integration Guide

## Overview
The feeding program system allows nutritionists to create batch meal plans for multiple patients enrolled in community feeding programs.

## 🔄 System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    NUTRITIONIST DASHBOARD                        │
│                  (meal-plans.blade.php)                         │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ├─ Individual Meal Plans (nutrition_chain.py)
                     │  └─> Single patient meal planning
                     │
                     └─ Feeding Programs (feeding_program_chain.py)
                        └─> Batch meal planning for groups
                           
┌─────────────────────────────────────────────────────────────────┐
│                   BACKEND FLOW                                   │
└─────────────────────────────────────────────────────────────────┘

Frontend (meal-plans.js)
    │
    ├─> Select multiple patients (checkboxes)
    │
    ├─> POST to FastAPI endpoints:
    │   │
    │   ├─ /feeding_program/batch_analysis
    │   │   └─> Returns nutritional overview of group
    │   │
    │   ├─ /feeding_program/meal_plan
    │   │   └─> Generates multi-week batch meal plan
    │   │
    │   └─ /feeding_program/assessment
    │       └─> Creates program assessment report
    │
    └─> Display formatted results
```

## 📋 Files Modified/Created

### New Files:
1. **`LLM/feeding_program_chain.py`** - Core feeding program logic
2. **`LLM/FEEDING_PROGRAM_INTEGRATION.md`** - This guide

### Modified Files:
1. **`LLM/fastapi_app.py`** - Added 3 new endpoints
2. **`LLM/README.md`** - Updated documentation
3. **`resources/views/nutritionist/meal-plans.blade.php`** - Added feeding program UI
4. **`public/js/nutritionist/meal-plans.js`** - Added feeding program functionality

## 🚀 How to Use

### Step 1: Start FastAPI Server
```bash
cd C:\xampp\htdocs\Capstone-System\capstone_system\LLM
uvicorn fastapi_app:app --reload --port 8001
```

### Step 2: Access Nutritionist Dashboard
Navigate to: `http://localhost:8000/nutritionist/meal-plans`

### Step 3: Select Patients for Feeding Program
1. Check the boxes next to patients you want to include
2. Click "Select All" to select all patients
3. Selected count will update automatically

### Step 4: Generate Batch Analysis (Optional)
- Click "Batch Analysis" button
- View aggregated nutritional needs:
  - Age distribution
  - Nutritional status breakdown
  - Common allergies
  - Medical conditions

### Step 5: Generate Feeding Program Meal Plan
1. Click "Generate Feeding Program" button
2. Fill in the modal:
   - **Program Duration**: 1-12 weeks
   - **Budget Level**: Low, Moderate, or High
   - **Barangay**: Optional location
   - **Available Ingredients**: Optional ingredient list
3. Click "Generate Feeding Program Plan"
4. Wait for AI to generate the plan (may take 30-60 seconds)

### Step 6: Review Results
The system will display:
- Program overview
- Batch nutritional analysis
- Weekly meal plans with:
  - Age-appropriate adaptations
  - Shopping lists
  - Batch cooking instructions
  - Nutritional summaries

## 🔌 API Endpoints

### 1. Batch Analysis
**Endpoint:** `POST http://127.0.0.1:8001/feeding_program/batch_analysis`

**Request Body:**
```json
[123, 456, 789]  // Array of patient IDs
```

**Response:**
```json
{
  "success": true,
  "batch_analysis": {
    "total_patients": 3,
    "age_groups": {
      "0-6_months": [],
      "6-12_months": [/* patients */],
      "12-24_months": [/* patients */],
      "24-60_months": [/* patients */]
    },
    "nutritional_priorities": {
      "underweight": 2,
      "stunted": 1,
      "normal": 0,
      "overweight": 0,
      "wasted": 0
    },
    "common_allergies": {},
    "medical_conditions": {}
  }
}
```

### 2. Generate Feeding Program Meal Plan
**Endpoint:** `POST http://127.0.0.1:8001/feeding_program/meal_plan`

**Request Body:**
```json
{
  "patient_ids": [123, 456, 789],
  "program_duration_weeks": 4,
  "budget_level": "moderate",
  "barangay": "Barangay 1",
  "available_ingredients": "manok, bangus, monggo, kangkong, saging"
}
```

**Response:**
```json
{
  "success": true,
  "meal_plan": "# Week 1 Feeding Program Meal Plan\n\n## Monday\n...",
  "batch_analysis": { /* ... */ },
  "program_duration_weeks": 4,
  "budget_level": "moderate",
  "generated_at": "2025-11-30 10:30:00",
  "total_patients": 3
}
```

### 3. Generate Feeding Program Assessment
**Endpoint:** `POST http://127.0.0.1:8001/feeding_program/assessment`

**Request Body:**
```json
{
  "patient_ids": [123, 456, 789],
  "barangay": "Barangay 1"
}
```

**Response:**
```json
{
  "success": true,
  "assessment": "# Feeding Program Assessment Report\n\n## Executive Summary\n...",
  "batch_analysis": { /* ... */ },
  "generated_at": "2025-11-30 10:30:00",
  "total_patients": 3
}
```

## 💡 Key Features

### Budget-Conscious Planning
- **Low Budget**: Focus on cost-effective ingredients (monggo, galunggong, kangkong)
- **Moderate Budget**: Balanced nutrition and cost (manok, bangus, variety of vegetables)
- **High Budget**: Optimal nutrition without cost constraints

### Age-Appropriate Adaptations
Each meal includes texture modifications:
- **6-12 months**: Pureed/mashed consistency
- **12-24 months**: Soft, small pieces
- **24-60 months**: Regular family food texture

### Batch Cooking Guidelines
- Large quantity preparation tips
- Food safety for batch cooking
- Storage and reheating instructions
- Shopping lists with estimated quantities

### Evidence-Based Recommendations
- Integrates with PDF knowledge base
- WHO nutrition guidelines
- Cultural appropriateness (Filipino cuisine)
- Seasonal food availability

## 🎯 Differences: Individual vs Feeding Program

| Feature | Individual Plans | Feeding Programs |
|---------|-----------------|------------------|
| **Target** | Single child | Multiple children |
| **User** | Parent | Nutritionist |
| **Focus** | Personalization | Cost-effectiveness |
| **Portions** | Single servings | Batch servings |
| **Budget** | Not primary | Critical factor |
| **Duration** | 7 days | 4-12 weeks |
| **Format** | Home cooking | Batch cooking |
| **Shopping** | Individual items | Bulk quantities |

## 🐛 Troubleshooting

### FastAPI Server Not Running
```bash
# Check if port 8001 is already in use
netstat -ano | findstr :8001

# Kill process if needed
taskkill /PID <process_id> /F

# Restart server
uvicorn fastapi_app:app --reload --port 8001
```

### CORS Errors
- FastAPI is configured to allow `*` origins for testing
- In production, update `allow_origins` in `fastapi_app.py`

### No Patients Showing
- Ensure patients are assigned to the logged-in nutritionist
- Check `nutritionist_id` in patients table

### Slow Response Time
- Generating feeding programs can take 30-60 seconds
- LLM is processing multiple patients and creating comprehensive plans
- Consider adding loading indicators

## 📊 Example Workflow

1. **Nutritionist logs in** → Sees assigned patients
2. **Selects 5 patients** with similar age range for a feeding program
3. **Clicks "Batch Analysis"** → Views group nutritional needs
4. **Clicks "Generate Feeding Program"**
5. **Sets parameters**:
   - Duration: 4 weeks
   - Budget: Moderate
   - Barangay: Barangay 1
   - Ingredients: "manok, bangus, monggo, sitaw, kangkong, saging"
6. **System generates**:
   - 4-week meal plan
   - Age adaptations for each meal
   - Weekly shopping lists
   - Batch cooking instructions
   - Nutritional analysis
7. **Nutritionist reviews and prints** for feeding program staff

## 🔮 Future Enhancements

- [ ] Save feeding programs to database
- [ ] Generate PDF reports
- [ ] Track program progress over time
- [ ] Compare baseline vs. post-program assessments
- [ ] Integration with inventory management
- [ ] Cost calculation and budget tracking
- [ ] Multi-barangay program coordination

## 📞 Support

For issues or questions:
1. Check this guide
2. Review `LLM/README.md`
3. Check FastAPI logs in terminal
4. Review browser console for JavaScript errors

---

**Last Updated:** November 30, 2025
**Version:** 1.0

