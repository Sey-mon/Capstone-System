# 🏥 Capstone Malnutrition Assessment & Management System

A comprehensive web-based system for malnutrition assessment, treatment planning, and nutrition management for children aged 0-5 years, built with Laravel, FastAPI, and AI-powered analysis.

## 🏗️ System Architecture

This capstone project consists of three integrated applications:

### 1. **Main Capstone System** (`capstone_system/`)
- **Framework**: Laravel 10 with MySQL
- **Purpose**: Core web application for patient management, assessments, and reporting
- **Users**: Admins, Nutritionists, Parents
- **Features**: Patient registration, assessment tracking, comprehensive reporting

### 2. **AI Meal Planning System** (`LLM/`)
- **Framework**: FastAPI with Groq AI integration
- **Purpose**: AI-powered meal plan generation and nutrition analysis
- **Features**: Personalized meal recommendations, nutritionist notes, parent recipe management

### 3. **Treatment Model System** (`RandomForest/`)
- **Framework**: Python with Random Forest ML model
- **Purpose**: Malnutrition assessment and evidence-based treatment planning
- **Features**: 95% accuracy assessment, WHO guidelines integration, comprehensive treatment protocols

## 🎯 Key Features

### 👨‍⚕️ **For Nutritionists**
- Complete patient management system
- AI-powered assessment tools with treatment planning
- Meal plan generation and review capabilities
- Progress tracking and monitoring schedules
- Professional profile management with verification system

### 👥 **For Parents**
- Child registration and profile management
- View assessment results and treatment plans
- Access personalized meal plans
- Upload and manage family recipes
- Track child's progress over time

### 🛠️ **For Administrators**
- User management and verification
- Comprehensive reporting and analytics
- System monitoring and data management
- Audit logging and security oversight
- Meal database administration

## 📊 Core Functionality

### **Assessment & Treatment Planning**
- Random Forest ML model with 95% accuracy
- WHO growth standards integration
- Evidence-based treatment protocols
- Personalized intervention recommendations
- Risk assessment and monitoring schedules

### **Meal Planning & Nutrition**
- Filipino cuisine-focused meal database
- AI-powered meal plan generation using Groq
- Nutrition analysis and recommendations
- Recipe management and sharing
- Professional nutritionist oversight

### **Data Management & Reporting**
- Patient demographics and medical history
- Assessment trends and analytics
- User activity monitoring
- PDF report generation
- Data export capabilities

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+ with Laravel 10
- MySQL 8.0+
- Python 3.9+
- Node.js 16+
- Composer
- npm/yarn

### Installation

#### 1. Main Laravel Application
```bash
cd capstone_system
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

#### 2. AI Meal Planning System
```bash
cd LLM
pip install -r requirements.txt
cp .env.example .env
# Add your GROQ_API_KEY to .env
python -m uvicorn fastapi_app:app --host 127.0.0.1 --port 8002

#### 3. Treatment Model System
```bash
cd RandomForest
pip install -r requirements.txt
python -m uvicorn api_server:app --host 127.0.0.1 --port 8001 
```

### Default Login Credentials
- **Admin**: admin@example.com / password123
- **Nutritionist**: nutritionist@example.com / password123
- **Parent**: parent@example.com / password123

## 📁 Project Structure

```
Capstone-System/
├── capstone_system/              # Main Laravel application
│   ├── app/                      # Laravel application logic
│   ├── resources/views/          # Blade templates
│   ├── public/js/               # Frontend JavaScript
│   ├── database/migrations/     # Database schema
│   └── routes/                  # Application routes
├── LLM/              # AI meal planning system
│   ├── fastapi_app.py           # FastAPI server
│   ├── nutrition_ai.py          # AI logic with Groq
│   ├── data_manager.py          # Database operations
│   └── requirements.txt         # Python dependencies
├── RandomForest/ # ML assessment system
│   ├── api_server.py            # Treatment API server
│   ├── malnutrition_model.py    # Random Forest model
│   ├── personalized_treatment_planner.py # Treatment protocols
│   └── complete_system_demo.py  # System demonstration
└── README.md                    # This file
```

## 🔧 Technology Stack

### **Backend**
- **Laravel 10**: Main web framework
- **FastAPI**: AI meal planning API
- **MySQL**: Primary database
- **Python**: ML model and data processing
- **Random Forest**: Machine learning algorithm

### **Frontend**
- **Blade Templates**: Server-side rendering
- **Bootstrap 5**: UI framework
- **JavaScript/jQuery**: Interactive features
- **Chart.js**: Data visualization
- **Font Awesome**: Icons

### **AI & ML**
- **Groq API**: Large language model for meal planning
- **Scikit-learn**: Machine learning library
- **WHO Growth Standards**: Assessment criteria
- **LangChain**: AI workflow management

## 📈 Assessment Features

### **Malnutrition Classification**
- Severe Acute Malnutrition (SAM)
- Moderate Acute Malnutrition (MAM)
- Normal nutrition status
- Risk factor analysis

### **Treatment Planning**
- Immediate action recommendations
- Personalized nutrition plans
- Medical intervention protocols
- Monitoring schedules
- Success criteria and discharge planning

### **Evidence-Based Protocols**
- WHO guidelines integration
- Age-appropriate interventions
- Weight and height monitoring
- Medical supplement recommendations
- Emergency warning signs

## 🍽️ Meal Planning System

### **AI-Powered Features**
- BMI-based meal recommendations
- Allergy and medical condition considerations
- Filipino cuisine focus
- Nutritionist review and notes
- Historical meal plan tracking

### **Database**
- Comprehensive Filipino meal database
- Nutrition facts per serving
- Recipe instructions and ingredients
- Course categorization
- Preparation and cooking times

## 📊 Reporting & Analytics

### **Available Reports**
- Patient assessment trends
- User activity analysis
- Nutrition intervention outcomes
- Growth monitoring statistics
- System usage analytics

### **Export Options**
- PDF report generation
- CSV data export
- Filterable date ranges
- Multi-user perspectives

## 🔐 Security Features

- Role-based access control
- Professional verification system
- Audit logging
- Data encryption
- Session management
- CSRF protection

## 🎓 Academic Integration

This system is designed for:
- **Capstone project requirements**
- **Healthcare research**
- **Nutrition program evaluation**
- **Clinical decision support**
- **Public health initiatives**

## 🏆 Project Achievements

✅ **Complete patient management system**  
✅ **AI-powered assessment with 95% accuracy**  
✅ **Evidence-based treatment planning**  
✅ **Comprehensive meal planning system**  
✅ **Multi-role user management**  
✅ **Advanced reporting and analytics**  
✅ **Mobile-responsive design**  
✅ **Professional verification workflow**  

## 📝 Documentation

- [`Treatment_Model_Random_Forest/README.md`](Treatment_Model_Random_Forest/README.md) - ML model documentation
- [`Treatment_Model_Random_Forest/EVIDENCE_VALIDATION.md`](RandomForest/EVIDENCE_VALIDATION.md) - Clinical validation guide
- [`Groq_Meal_Plan/README.md`](LLM/README.md) - Meal planning system guide

## 🤝 Contributing

This is a capstone project for academic purposes. For questions or collaboration:

1. Review the existing documentation
2. Check the issue tracker
3. Follow the established coding standards
4. Test thoroughly before submitting changes

## 📞 Support

For technical support or questions about the system:
- Review the documentation in each module
- Check the database seeders for sample data
- Refer to the evidence validation documentation for clinical protocols
- Use the demo scripts to understand system integration

---

**🎉 Capstone Project Status: Complete**

This comprehensive malnutrition assessment and management system represents a complete solution for healthcare professionals working with child nutrition programs, combining modern web technologies with evidence-based clinical protocols.