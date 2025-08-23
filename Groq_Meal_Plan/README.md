# Parent Nutrition Management System

A comprehensive nutrition management system with separate interfaces for parents and nutritionists, powered by Groq AI. The system now uses a **meal-based database** instead of individual food components.

## 🏗️ System Overview

### **Two Separate Applications:**
1. **Parent Interface** (`parent_ui.py`) - Manage children's meal plans and parent recipes
2. **Nutritionist Interface** (`nutritionist_ui.py`) - Review plans, add notes, manage knowledge base
3. **Admin Interface** (`admin_ui.py`) - Manage meal database, view logs, system administration

### **Key Features:**
- 👨‍👩‍👧‍👦 **For Parents**: Child meal plan management, parent recipe input
- 👩‍⚕️ **For Nutritionists**: Client overview, meal plan notes, knowledge base management
- 🛠️ **For Admins**: Meal database management, system logs, knowledge base oversight
- 🧠 **AI-Powered**: Groq API for personalized meal recommendations
- 🇵🇭 **Filipino-Focused**: Built-in Filipino nutrition knowledge and meal database

## 📋 Setup Instructions

### 1. Install Dependencies
```bash
pip install -r requirements.txt
```

### 2. Environment Setup
Your `.env` file should contain:
```
GROQ_API_KEY=your_actual_api_key_here
```

### 3. Database Setup
```bash
# Create the new meals table
mysql -u your_username -p your_database < create_meals_table.sql

# Optional: Migrate existing food data to meals
python migrate_to_meals.py
```

### 4. Run Applications
```bash
# For Parents
streamlit run parent_ui.py --server.port 8501

# For Nutritionists (different port)  
streamlit run nutritionist_ui.py --server.port 8502

# For Admins (different port)
streamlit run admin_ui.py --server.port 8503

# Or use the launcher
launch.bat
```

## 📁 Project Structure

### **Core Applications:**
- **`parent_ui.py`** - Parent interface for meal planning
- **`nutritionist_ui.py`** - Nutritionist interface for client management
- **`admin_ui.py`** - Admin interface for system management
- **`nutrition_ai.py`** - Core AI logic with Groq
- **`nutrition_chain.py`** - LangChain-based meal plan generation
- **`data_manager.py`** - Database operations for meals and users

### **Database Tools:**
- **`create_meals_table.sql`** - SQL script to create the new meals table
- **`migrate_to_meals.py`** - Migration script from old food tables to meals
- **`meal_data_parser.py`** - Tool to convert meal text to SQL INSERT statements

### **Configuration:**
- **`launch.bat`** - Easy launcher script
- **`requirements.txt`** - Dependencies
- **`.env`** - API keys

## 🍽️ New Meal Database Structure

The system now uses a comprehensive **meals table** instead of separate food tables:

```sql
Table: meals
- meal_id (Primary Key)
- meal_name, description, course, keywords
- prep_time_minutes, cook_time_minutes, servings
- ingredients (JSON), instructions, image_url
- Nutrition data: calories_kcal, protein_g, carbohydrates_g, fat_g, etc.
- timestamps: created_at, updated_at
```

### **Benefits of Meal-Based System:**
- **Complete meal information** in one place
- **Recipe instructions** and ingredient lists
- **Course categorization** (Main Course, Soup, Dessert, etc.)
- **Preparation and cooking times**
- **Comprehensive nutrition data per serving**
- **Better AI meal recommendations**

## 🎯 Features by User Type

### **Parents Can:**
- View all their children's meal plans
- Generate new meal plans based on child's BMI, allergies, conditions
- Input parent recipes (simple text area format)
- View historical meal plans (6 months)
- See nutritionist notes on their meal plans

### **Nutritionists Can:**
- View all parents and their meal plans
- Add notes to any meal plan (simple note-taking, no approval workflow)
- Upload and manage Filipino nutrition knowledge
- Browse comprehensive meal database with nutrition facts
- Review parent-uploaded recipes with professional notes

### **Admins Can:**
- Manage the complete meal database
- View and edit meal details, nutrition information
- Monitor system logs and user activities
- Manage knowledge base uploads
- Oversee meal plan generation and notes

## 📊 Data Flow

1. **Admin** manages meal database with complete Filipino dishes and nutrition data
2. **Parent** generates meal plan → AI considers child's BMI, allergies, medical conditions
3. **System** uses meal database + Filipino nutrition knowledge for recommendations
4. **Nutritionist** reviews and adds notes to meal plans
5. **Parent** can view updated meal plans with nutritionist notes

## 🔄 Migration from Food Tables

If you have existing food data, use the migration tools:

```bash
# Backup existing food tables and migrate to meals
python migrate_to_meals.py

# Add new meals using the parser tool
python meal_data_parser.py
```

## 🍽️ Adding New Meals

Use the meal data parser to easily add new meals:

```
Meal Name: Chicken Tinola
Description: Traditional Filipino soup with chicken and vegetables
Course: Main Course
Keywords: soup, chicken, ginger, healthy, Filipino
Prep Time: 15
Cook Time: 45
Servings: 4
Ingredients: chicken, ginger, onion, garlic, sayote, malunggay leaves
Instructions: Saute aromatics, add chicken, simmer with vegetables
Calories: 250
Protein: 28
Carbohydrates: 8
Fat: 12
Fiber: 2
Sodium: 890
Calcium: 45
Iron: 2.1
Vitamin C: 15
```

---

## Data Sources

Meal and nutrition information in this system is based on:
- Philippine Food Composition Tables from [FNRI DOST](https://i.fnri.dost.gov.ph/)
- Traditional Filipino recipes and cooking methods
- WHO nutrition guidelines for children 0-5 years

**Start with any interface based on your role!** 👨‍👩‍👧‍👦 or 👩‍⚕️ or 🛠️
