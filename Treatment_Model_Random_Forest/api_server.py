"""
Secure FastAPI Server for Malnutrition Assessment and Treatment Planning
Provides REST API endpoints for Laravel application integration
"""

from fastapi import FastAPI, HTTPException, Depends, Security, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from fastapi.middleware.cors import CORSMiddleware
from fastapi.middleware.trustedhost import TrustedHostMiddleware
from pydantic import BaseModel, validator
from typing import Optional, Dict, Any, List
import hashlib
import hmac
import os
import logging
from datetime import datetime, timedelta
import jwt
import uvicorn

# Import our modules
from malnutrition_model import MalnutritionAssessment
from personalized_treatment_planner import PersonalizedTreatmentPlanner
from data_manager import DataManager

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Security configuration
SECRET_KEY = os.getenv("API_SECRET_KEY", "your-super-secret-key-change-this-in-production")
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30
API_KEY = os.getenv("API_KEY", "malnutrition-api-key-2025")

# Initialize FastAPI with security headers
app = FastAPI(
    title="Malnutrition Assessment API",
    description="Secure API for malnutrition assessment and treatment planning",
    version="1.0.0",
    docs_url="/docs",  # Swagger UI
    redoc_url="/redoc"  # ReDoc
)

# Security middleware
security = HTTPBearer()

# CORS configuration - restrict to your Laravel domain in production
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000", "http://127.0.0.1:8000"],  # Add your Laravel domain
    allow_credentials=True,
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)

# Trusted host middleware - add your domains
app.add_middleware(
    TrustedHostMiddleware,
    allowed_hosts=["localhost", "127.0.0.1", "*.your-domain.com"]
)

# Initialize our modules
try:
    malnutrition_model = MalnutritionAssessment()
    treatment_planner = PersonalizedTreatmentPlanner()
    data_manager = DataManager()
    logger.info("All modules initialized successfully")
except Exception as e:
    logger.error(f"Failed to initialize modules: {e}")
    raise

# Pydantic models for request/response validation
class ChildData(BaseModel):
    """Child data for assessment"""
    age_months: int
    weight_kg: float
    height_cm: float
    gender: str
    muac_cm: Optional[float] = None
    has_edema: bool = False
    appetite: str = "good"  # good, poor, very_poor
    diarrhea_days: int = 0
    fever_days: int = 0
    vomiting: bool = False
    
    @validator('age_months')
    def validate_age(cls, v):
        if not 0 <= v <= 60:  # 0-5 years
            raise ValueError('Age must be between 0 and 60 months')
        return v
    
    @validator('weight_kg')
    def validate_weight(cls, v):
        if not 1.0 <= v <= 50.0:  # Reasonable weight range
            raise ValueError('Weight must be between 1.0 and 50.0 kg')
        return v
    
    @validator('height_cm')
    def validate_height(cls, v):
        if not 30.0 <= v <= 150.0:  # Reasonable height range
            raise ValueError('Height must be between 30.0 and 150.0 cm')
        return v
    
    @validator('gender')
    def validate_gender(cls, v):
        if v.lower() not in ['male', 'female', 'm', 'f']:
            raise ValueError('Gender must be male, female, M, or F')
        return v.lower()
    
    @validator('appetite')
    def validate_appetite(cls, v):
        if v.lower() not in ['good', 'poor', 'very_poor']:
            raise ValueError('Appetite must be good, poor, or very_poor')
        return v.lower()

class SocioeconomicData(BaseModel):
    """Socioeconomic data for treatment personalization"""
    is_4ps_beneficiary: bool = False
    household_size: int = 4
    monthly_income: Optional[float] = None
    has_electricity: bool = True
    has_clean_water: bool = True
    mother_education: str = "primary"  # none, primary, secondary, tertiary
    father_present: bool = True
    
    @validator('household_size')
    def validate_household_size(cls, v):
        if not 1 <= v <= 20:
            raise ValueError('Household size must be between 1 and 20')
        return v
    
    @validator('mother_education')
    def validate_education(cls, v):
        if v.lower() not in ['none', 'primary', 'secondary', 'tertiary']:
            raise ValueError('Education must be none, primary, secondary, or tertiary')
        return v.lower()

class AssessmentRequest(BaseModel):
    """Complete assessment request"""
    child_data: ChildData
    socioeconomic_data: Optional[SocioeconomicData] = None

class Token(BaseModel):
    """JWT Token response"""
    access_token: str
    token_type: str

class ApiKeyAuth(BaseModel):
    """API Key authentication"""
    api_key: str

# Security functions
def verify_api_key(api_key: str) -> bool:
    """Verify API key with constant-time comparison"""
    return hmac.compare_digest(api_key, API_KEY)

def create_access_token(data: dict, expires_delta: Optional[timedelta] = None):
    """Create JWT access token"""
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt

def verify_token(credentials: HTTPAuthorizationCredentials = Security(security)):
    """Verify JWT token"""
    try:
        payload = jwt.decode(credentials.credentials, SECRET_KEY, algorithms=[ALGORITHM])
        username: str = payload.get("sub")
        if username is None:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Could not validate credentials",
                headers={"WWW-Authenticate": "Bearer"},
            )
        return username
    except jwt.PyJWTError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Could not validate credentials",
            headers={"WWW-Authenticate": "Bearer"},
        )

# API Endpoints

@app.get("/")
async def root():
    """Health check endpoint"""
    return {
        "message": "Malnutrition Assessment API is running",
        "status": "healthy",
        "timestamp": datetime.utcnow().isoformat(),
        "version": "1.0.0"
    }

@app.post("/auth/token", response_model=Token)
async def login_for_access_token(auth_data: ApiKeyAuth):
    """Authenticate with API key and get JWT token"""
    if not verify_api_key(auth_data.api_key):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid API key",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        data={"sub": "laravel_app"}, expires_delta=access_token_expires
    )
    
    logger.info("API key authenticated successfully")
    return {"access_token": access_token, "token_type": "bearer"}

@app.post("/assess/complete")
async def complete_assessment(
    request: AssessmentRequest,
    current_user: str = Depends(verify_token)
):
    """
    Complete malnutrition assessment with treatment planning
    Returns assessment results and personalized treatment plan
    """
    try:
        logger.info(f"Processing complete assessment for user: {current_user}")
        
        # Prepare child data for assessment
        child_data = request.child_data
        socio_data = request.socioeconomic_data or SocioeconomicData()
        
        # Convert gender format
        gender = 'male' if child_data.gender.lower() in ['male', 'm'] else 'female'
        
        # Perform malnutrition assessment
        assessment_result = malnutrition_model.assess_malnutrition(
            age_months=child_data.age_months,
            weight_kg=child_data.weight_kg,
            height_cm=child_data.height_cm,
            gender=gender,
            muac_cm=child_data.muac_cm,
            has_edema=child_data.has_edema
        )
        
        # Generate treatment plan
        # Prepare patient data for treatment planner
        patient_data = {
            'age_months': child_data.age_months,
            'weight': child_data.weight_kg,
            'sex': gender,
            'edema': child_data.has_edema,
            'breastfeeding': 'Yes' if child_data.age_months <= 24 else 'No',
            'appetite': child_data.appetite,
            'diarrhea_days': child_data.diarrhea_days,
            'fever_days': child_data.fever_days,
            'vomiting': child_data.vomiting
        }
        
        # ML result from assessment
        ml_result = {
            'prediction': assessment_result['primary_diagnosis'],
            'probabilities': {assessment_result['primary_diagnosis']: assessment_result['confidence']}
        }
        
        # Risk assessment
        risk_assessment = {
            'overall': {
                'risk_score': assessment_result['risk_score'],
                'risk_factors': assessment_result['risk_factors']
            }
        }
        
        # WHO assessment
        who_assessment = assessment_result['who_assessment']
        
        treatment_plan = treatment_planner.generate_comprehensive_treatment_plan(
            patient_data=patient_data,
            ml_result=ml_result,
            risk_assessment=risk_assessment,
            who_assessment=who_assessment
        )
        
        # Combine results
        complete_result = {
            "assessment": assessment_result,
            "treatment_plan": treatment_plan,
            "timestamp": datetime.utcnow().isoformat(),
            "api_version": "1.0.0"
        }
        
        logger.info("Assessment completed successfully")
        return complete_result
        
    except Exception as e:
        logger.error(f"Assessment failed: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Assessment failed: {str(e)}"
        )

@app.post("/assess/malnutrition-only")
async def malnutrition_assessment_only(
    child_data: ChildData,
    current_user: str = Depends(verify_token)
):
    """
    Malnutrition assessment only (without treatment planning)
    """
    try:
        logger.info(f"Processing malnutrition assessment for user: {current_user}")
        
        gender = 'male' if child_data.gender.lower() in ['male', 'm'] else 'female'
        
        result = malnutrition_model.assess_malnutrition(
            age_months=child_data.age_months,
            weight_kg=child_data.weight_kg,
            height_cm=child_data.height_cm,
            gender=gender,
            muac_cm=child_data.muac_cm,
            has_edema=child_data.has_edema
        )
        
        result["timestamp"] = datetime.utcnow().isoformat()
        result["api_version"] = "1.0.0"
        
        logger.info("Malnutrition assessment completed successfully")
        return result
        
    except Exception as e:
        logger.error(f"Malnutrition assessment failed: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Assessment failed: {str(e)}"
        )

@app.get("/reference/who-standards/{gender}/{indicator}")
async def get_who_standards(
    gender: str,
    indicator: str,
    current_user: str = Depends(verify_token)
):
    """
    Get WHO standard reference data
    """
    try:
        if gender.lower() not in ['male', 'female', 'm', 'f']:
            raise HTTPException(status_code=400, detail="Invalid gender")
        
        if indicator.lower() not in ['wfa', 'lhfa']:
            raise HTTPException(status_code=400, detail="Invalid indicator. Use 'wfa' or 'lhfa'")
        
        gender_normalized = 'male' if gender.lower() in ['male', 'm'] else 'female'
        
        # Get WHO standards from malnutrition model (WHO calculator)
        try:
            # Access WHO reference data from the calculator
            who_calculator = malnutrition_model.who_calculator
            
            if indicator.lower() == 'wfa':
                if 'weight_for_age' in who_calculator.who_reference:
                    gender_key = 'boys' if gender_normalized == 'male' else 'girls'
                    standards = who_calculator.who_reference['weight_for_age'].get(gender_key, {})
                else:
                    standards = {}
            else:  # lhfa
                if 'height_for_age' in who_calculator.who_reference:
                    gender_key = 'boys' if gender_normalized == 'male' else 'girls'
                    standards = who_calculator.who_reference['height_for_age'].get(gender_key, {})
                else:
                    standards = {}
            
        except Exception as e:
            logger.warning(f"Could not access WHO data: {e}")
            standards = {}
        
        return {
            "gender": gender_normalized,
            "indicator": indicator.upper(),
            "data": standards,
            "timestamp": datetime.utcnow().isoformat()
        }
        
    except Exception as e:
        logger.error(f"WHO standards retrieval failed: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Failed to retrieve WHO standards: {str(e)}"
        )

@app.get("/reference/treatment-protocols")
async def get_treatment_protocols(current_user: str = Depends(verify_token)):
    """
    Get available treatment protocols
    """
    try:
        protocols = treatment_planner.get_available_protocols()
        return {
            "protocols": protocols,
            "timestamp": datetime.utcnow().isoformat()
        }
    except Exception as e:
        logger.error(f"Protocol retrieval failed: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Failed to retrieve protocols: {str(e)}"
        )

# Admin endpoints for managing WHO standards and treatment protocols
@app.get("/admin/who-standards/summary")
async def get_who_standards_summary(current_user: str = Depends(verify_token)):
    """
    Get summary of WHO standards data for admin dashboard
    """
    try:
        who_calculator = malnutrition_model.who_calculator
        summary = {
            "available_standards": list(who_calculator.who_reference.keys()),
            "data_counts": {},
            "last_updated": datetime.utcnow().isoformat()
        }
        
        # Count data points for each standard
        for standard_type, genders in who_calculator.who_reference.items():
            summary["data_counts"][standard_type] = {}
            for gender, data in genders.items():
                summary["data_counts"][standard_type][gender] = len(data) if isinstance(data, dict) else 0
        
        return summary
        
    except Exception as e:
        logger.error(f"WHO standards summary failed: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Failed to get WHO standards summary: {str(e)}"
        )

@app.get("/admin/treatment-protocols/detailed")
async def get_detailed_treatment_protocols(current_user: str = Depends(verify_token)):
    """
    Get detailed treatment protocols for admin management
    """
    try:
        protocols_detail = {
            "sam_protocol": {
                "name": "Severe Acute Malnutrition Protocol",
                "phases": ["stabilization", "transition", "rehabilitation"],
                "medications": {
                    "rutf_sachets": "1 sachet per kg daily",
                    "vitamin_a": "200,000 IU every 6 months (>12m) or 100,000 IU (<12m)",
                    "iron_folate": "2-6 mg/kg/day for 12 weeks",
                    "zinc": "10-20mg daily for 10-14 days (if diarrhea)"
                },
                "monitoring": "Daily during stabilization, weekly during rehabilitation",
                "success_criteria": "WHZ ≥-2 SD for 2 consecutive weeks"
            },
            "mam_protocol": {
                "name": "Moderate Acute Malnutrition Protocol",
                "phases": ["supplementary_feeding", "monitoring"],
                "medications": {
                    "supplementary_food": "75 kcal/kg/day",
                    "vitamin_a": "Same as SAM",
                    "iron_folate": "Same as SAM"
                },
                "monitoring": "Weekly visits, then bi-weekly",
                "success_criteria": "WHZ ≥-2 SD sustained"
            },
            "normal_protocol": {
                "name": "Preventive Nutrition Protocol",
                "phases": ["maintenance", "growth_monitoring"],
                "recommendations": [
                    "Continue age-appropriate feeding",
                    "Regular growth monitoring",
                    "Nutritional counseling for caregivers"
                ],
                "monitoring": "Monthly growth checks"
            }
        }
        
        return {
            "protocols": protocols_detail,
            "editable_parameters": [
                "medication_doses",
                "monitoring_frequency", 
                "success_criteria",
                "feeding_schedules"
            ],
            "timestamp": datetime.utcnow().isoformat()
        }
        
    except Exception as e:
        logger.error(f"Detailed protocols retrieval failed: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Failed to get detailed protocols: {str(e)}"
        )

@app.get("/admin/system-status")
async def get_system_status(current_user: str = Depends(verify_token)):
    """
    Get comprehensive system status for admin monitoring
    """
    try:
        # Test all major components
        test_assessment = malnutrition_model.assess_malnutrition(
            age_months=12, weight_kg=8.0, height_cm=75.0, gender='male'
        )
        
        # Test treatment planning
        test_child_data = {
            'age_months': 12,
            'weight': 8.0,
            'sex': 'male',
            'edema': False,
            'breastfeeding': 'Yes',
            'appetite': 'good',
            'diarrhea_days': 0,
            'fever_days': 0,
            'vomiting': False
        }
        
        test_ml_result = {
            'prediction': test_assessment['primary_diagnosis'],
            'probabilities': {test_assessment['primary_diagnosis']: test_assessment['confidence']}
        }
        
        test_risk_assessment = {
            'overall': {
                'risk_score': test_assessment['risk_score'],
                'risk_factors': test_assessment['risk_factors']
            }
        }
        
        test_treatment = treatment_planner.generate_comprehensive_treatment_plan(
            patient_data=test_child_data,
            ml_result=test_ml_result,
            risk_assessment=test_risk_assessment,
            who_assessment=test_assessment['who_assessment']
        )
        
        return {
            "status": "healthy",
            "components": {
                "malnutrition_model": "operational",
                "who_calculator": "operational", 
                "treatment_planner": "operational",
                "data_manager": "operational"
            },
            "test_results": {
                "assessment_test": "passed",
                "treatment_planning_test": "passed"
            },
            "api_version": "1.0.0",
            "timestamp": datetime.utcnow().isoformat(),
            "uptime": "Available"
        }
        
    except Exception as e:
        logger.error(f"System status check failed: {e}")
        return {
            "status": "degraded",
            "error": str(e),
            "timestamp": datetime.utcnow().isoformat()
        }

@app.get("/health")
async def health_check():
    """
    Health check endpoint for monitoring
    """
    try:
        # Test model loading
        test_result = malnutrition_model.assess_malnutrition(
            age_months=12, weight_kg=8.0, height_cm=75.0, gender='male'
        )
        
        return {
            "status": "healthy",
            "timestamp": datetime.utcnow().isoformat(),
            "models_loaded": True,
            "api_version": "1.0.0"
        }
    except Exception as e:
        logger.error(f"Health check failed: {e}")
        return {
            "status": "unhealthy",
            "error": str(e),
            "timestamp": datetime.utcnow().isoformat()
        }

# Security headers middleware
@app.middleware("http")
async def add_security_headers(request, call_next):
    response = await call_next(request)
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["X-XSS-Protection"] = "1; mode=block"
    response.headers["Strict-Transport-Security"] = "max-age=31536000; includeSubDomains"
    return response

if __name__ == "__main__":
    # Run the server
    uvicorn.run(
        "api_server:app",
        host="127.0.0.1",  # Bind to localhost only for security
        port=8001,
        reload=False,  # Set to False in production
        access_log=True,
        log_level="info"
    )
