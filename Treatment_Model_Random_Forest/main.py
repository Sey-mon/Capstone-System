"""
FastAPI Backend for Child Malnutrition Assessment System
Provides REST API endpoints for all model functionalities
"""

from fastapi import FastAPI, HTTPException, Depends, UploadFile, File, Form, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from pydantic import BaseModel, Field
from typing import List, Optional, Dict, Any
import pandas as pd
import numpy as np
import json
import os
from datetime import datetime, date
import logging
from contextlib import asynccontextmanager
import jwt
from jwt.exceptions import ExpiredSignatureError, InvalidTokenError
import time
from slowapi import Limiter
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded
from dotenv import load_dotenv

# Import model components
from malnutrition_model import MalnutritionRandomForestModel, generate_sample_data
from data_manager import DataManager
from treatment_protocol_manager import TreatmentProtocolManager
from model_enhancements import (
    calculate_anthropometric_risk, calculate_clinical_risk, calculate_socioeconomic_risk,
    calculate_environmental_risk, get_risk_based_recommendations, calculate_prediction_intervals,
    calculate_entropy, get_uncertainty_reason, generate_personalized_recommendations
)

# Load environment variables from .env file
load_dotenv()

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Security configuration
SECRET_KEY = os.getenv('SECRET_KEY', 'your-super-secret-key-here')
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

# Rate limiting
limiter = Limiter(key_func=get_remote_address)

# Security logging
security_logger = logging.getLogger('security')
security_logger.setLevel(logging.INFO)

def log_security_event(event_type: str, details: dict):
    security_logger.info(f"{datetime.now()} - {event_type}: {details}")

# Global model instance
model = None
data_manager = None

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Initialize and cleanup model resources"""
    global model, data_manager
    
    # Initialize model
    try:
        model = MalnutritionRandomForestModel()
        if os.path.exists('malnutrition_model.pkl'):
            model.load_model('malnutrition_model.pkl')
            logger.info("Loaded existing model")
        else:
            # Train new model with sample data
            sample_data = generate_sample_data(1000)
            model.train_model(sample_data)
            model.save_model('malnutrition_model.pkl')
            logger.info("Trained and saved new model")
        
        data_manager = DataManager()
        logger.info("Data manager initialized")
        
    except Exception as e:
        logger.error(f"Error initializing model: {e}")
        raise
    
    yield
    
    # Cleanup
    logger.info("Shutting down model")

# Create FastAPI app
app = FastAPI(
    title="Child Malnutrition Assessment API",
    description="REST API for child malnutrition assessment using Random Forest model",
    version="1.0.0",
    lifespan=lifespan
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # In production, specify exact origins
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Add rate limiting
app.state.limiter = limiter

# Custom rate limit handler
def rate_limit_handler(request: Request, exc: Exception):
    return JSONResponse(
        status_code=429,
        content={"detail": f"Rate limit exceeded: {str(exc)}"}
    )

app.add_exception_handler(RateLimitExceeded, rate_limit_handler)

# Security middleware
@app.middleware("http")
async def security_middleware(request: Request, call_next):
    # Log access attempt
    log_security_event("API_ACCESS", {
        "ip": request.client.host if request.client else "unknown",
        "endpoint": request.url.path,
        "method": request.method,
        "user_agent": request.headers.get("user-agent", "Unknown")
    })
    
    # Check request size
    if request.headers.get("content-length"):
        if int(request.headers["content-length"]) > 1024 * 1024:  # 1MB limit
            return JSONResponse(
                status_code=413,
                content={"error": "Request too large"}
            )
    
    # Add request timestamp
    request.state.start_time = time.time()
    
    response = await call_next(request)
    
    # Log slow requests
    duration = time.time() - request.state.start_time
    if duration > 5:
        log_security_event("SLOW_REQUEST", {
            "duration": duration,
            "path": request.url.path
        })
    
    return response

# Pydantic models for request/response
class PatientData(BaseModel):
    """Patient data for single assessment"""
    name: str = Field(..., description="Patient name")
    age_months: int = Field(..., ge=0, le=60, description="Age in months")
    sex: str = Field(..., description="Sex (male/female)")
    weight: float = Field(..., gt=0, description="Weight in kg")
    height: float = Field(..., gt=0, description="Height in cm")
    municipality: str = Field(..., description="Municipality")
    total_household: int = Field(..., ge=1, description="Total household members")
    adults: int = Field(..., ge=0, description="Number of adults")
    children: int = Field(..., ge=1, description="Number of children")
    twins: int = Field(0, ge=0, le=1, description="Is twin (0/1)")
    four_ps_beneficiary: str = Field(..., description="4PS beneficiary (Yes/No)")
    breastfeeding: str = Field(..., description="Breastfeeding (Yes/No)")
    edema: bool = Field(False, description="Has edema")
    tuberculosis: str = Field(..., description="Has tuberculosis (Yes/No)")
    malaria: str = Field(..., description="Has malaria (Yes/No)")
    congenital_anomalies: str = Field(..., description="Has congenital anomalies (Yes/No)")
    other_medical_problems: str = Field(..., description="Other medical problems (Yes/No)")
    date_of_admission: Optional[str] = Field(None, description="Date of admission")

class AssessmentResponse(BaseModel):
    """Response model for assessment results"""
    patient_name: str
    age_months: int
    weight: float
    height: float
    whz_score: float
    bmi: float
    prediction: str
    confidence_score: float
    probabilities: Dict[str, float]
    treatment_recommendation: Dict[str, Any]
    risk_level: str
    model_version: str = "1.0.0"
    assessment_date: str

class BatchAssessmentRequest(BaseModel):
    """Request model for batch assessment"""
    patients: List[PatientData]

class BatchAssessmentResponse(BaseModel):
    """Response model for batch assessment"""
    total_patients: int
    successful_assessments: int
    failed_assessments: int
    results: List[AssessmentResponse]
    summary: Dict[str, int]

class ModelInfo(BaseModel):
    """Model information response"""
    model_type: str
    version: str
    features: List[str]
    protocols: List[str]
    last_trained: str
    accuracy: Optional[float]

class TrainingRequest(BaseModel):
    """Request model for model training"""
    data_url: Optional[str] = None
    protocol_name: str = "who_standard"
    test_size: float = 0.2

class TrainingResponse(BaseModel):
    """Response model for training results"""
    success: bool
    accuracy: float
    cross_validation_score: float
    feature_importance: Dict[str, float]
    training_samples: int
    test_samples: int
    model_saved: bool

# Security dependencies
security = HTTPBearer()

async def verify_token(credentials: HTTPAuthorizationCredentials = Depends(security)):
    """Verify JWT token"""
    try:
        payload = jwt.decode(credentials.credentials, SECRET_KEY, algorithms=[ALGORITHM])
        return payload
    except ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token has expired")
    except InvalidTokenError:
        raise HTTPException(status_code=401, detail="Invalid authentication credentials")
    except Exception:
        raise HTTPException(status_code=401, detail="Invalid authentication credentials")

# Optional: Simple API key authentication for development
API_KEYS = {
    "laravel_app_key": os.getenv('LARAVEL_API_KEY', 'your-laravel-api-key'),
    "mobile_app_key": os.getenv('MOBILE_API_KEY', 'mobile-app-specific-key')
}

async def verify_api_key(request: Request):
    """Verify API key for simpler authentication"""
    api_key = request.headers.get("X-API-Key")
    
    # Debug logging
    logger.info(f"Received API key: {api_key}")
    logger.info(f"Expected API keys: {list(API_KEYS.values())}")
    
    if not api_key:
        raise HTTPException(status_code=401, detail="Missing API key")
    
    if api_key not in API_KEYS.values():
        log_security_event("INVALID_API_KEY", {
            "received_key": api_key[:10] + "..." if len(api_key) > 10 else api_key,
            "ip": request.client.host if request.client else "unknown"
        })
        raise HTTPException(status_code=401, detail="Invalid API key")
    
    return {"api_key": api_key}

# Dependency to get model instance
def get_model():
    if model is None:
        raise HTTPException(status_code=503, detail="Model not initialized")
    return model

def get_data_manager():
    if data_manager is None:
        raise HTTPException(status_code=503, detail="Data manager not initialized")
    return data_manager

# Health check endpoint
@app.get("/health", tags=["System"])
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "model_loaded": model is not None,
        "timestamp": datetime.now().isoformat()
    }

# Single patient assessment
@app.post("/assess/single", response_model=AssessmentResponse, tags=["Assessment"])
@limiter.limit("10/minute")  # Rate limiting
async def assess_single_patient(
    request: Request,
    patient: PatientData,
    model_instance: MalnutritionRandomForestModel = Depends(get_model),
    auth: dict = Depends(verify_api_key)  # API key authentication
):
    """
    Assess nutritional status for a single patient
    
    - **patient**: Patient data including measurements and demographics
    - Returns: Complete assessment with prediction, treatment recommendation, and risk level
    """
    try:
        # Convert to dictionary format expected by model
        patient_dict = patient.dict()
        
        # Rename fields to match model expectations
        patient_dict['4ps_beneficiary'] = patient_dict.pop('four_ps_beneficiary')
        
        # Get prediction
        result = model_instance.predict_single(patient_dict)
        
        # Calculate BMI
        bmi = patient.weight / ((patient.height/100) ** 2)
        
        # Determine risk level
        risk_level = "low"
        if result['prediction'] == "Severe Acute Malnutrition (SAM)":
            risk_level = "critical"
        elif result['prediction'] == "Moderate Acute Malnutrition (MAM)":
            risk_level = "high"
        elif result['prediction'] == "Normal":
            risk_level = "low"
        
        # Get confidence score (highest probability)
        confidence_score = max(result['probabilities'].values())
        
        return AssessmentResponse(
            patient_name=patient.name,
            age_months=patient.age_months,
            weight=patient.weight,
            height=patient.height,
            whz_score=result['whz_score'],
            bmi=round(bmi, 2),
            prediction=result['prediction'],
            confidence_score=round(confidence_score, 4),
            probabilities=result['probabilities'],
            treatment_recommendation=result['recommendation'],
            risk_level=risk_level,
            assessment_date=datetime.now().isoformat()
        )
        
    except Exception as e:
        logger.error(f"Error in single assessment: {e}")
        raise HTTPException(status_code=500, detail=f"Assessment failed: {str(e)}")

# Batch assessment
@app.post("/assess/batch", response_model=BatchAssessmentResponse, tags=["Assessment"])
async def assess_batch_patients(
    request: BatchAssessmentRequest,
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Assess nutritional status for multiple patients
    
    - **request**: List of patient data
    - Returns: Batch assessment results with summary statistics
    """
    try:
        results = []
        successful = 0
        failed = 0
        
        for patient in request.patients:
            try:
                # Convert to dictionary format
                patient_dict = patient.dict()
                patient_dict['4ps_beneficiary'] = patient_dict.pop('four_ps_beneficiary')
                
                # Get prediction
                result = model_instance.predict_single(patient_dict)
                
                # Calculate BMI
                bmi = patient.weight / ((patient.height/100) ** 2)
                
                # Determine risk level
                risk_level = "low"
                if result['prediction'] == "Severe Acute Malnutrition (SAM)":
                    risk_level = "critical"
                elif result['prediction'] == "Moderate Acute Malnutrition (MAM)":
                    risk_level = "high"
                
                # Get confidence score
                confidence_score = max(result['probabilities'].values())
                
                assessment = AssessmentResponse(
                    patient_name=patient.name,
                    age_months=patient.age_months,
                    weight=patient.weight,
                    height=patient.height,
                    whz_score=result['whz_score'],
                    bmi=round(bmi, 2),
                    prediction=result['prediction'],
                    confidence_score=round(confidence_score, 4),
                    probabilities=result['probabilities'],
                    treatment_recommendation=result['recommendation'],
                    risk_level=risk_level,
                    assessment_date=datetime.now().isoformat()
                )
                
                results.append(assessment)
                successful += 1
                
            except Exception as e:
                logger.error(f"Error assessing patient {patient.name}: {e}")
                failed += 1
                continue
        
        # Calculate summary
        summary = {
            "normal": len([r for r in results if "Normal" in r.prediction]),
            "mam": len([r for r in results if "Moderate" in r.prediction]),
            "sam": len([r for r in results if "Severe" in r.prediction]),
            "critical_risk": len([r for r in results if r.risk_level == "critical"]),
            "high_risk": len([r for r in results if r.risk_level == "high"])
        }
        
        return BatchAssessmentResponse(
            total_patients=len(request.patients),
            successful_assessments=successful,
            failed_assessments=failed,
            results=results,
            summary=summary
        )
        
    except Exception as e:
        logger.error(f"Error in batch assessment: {e}")
        raise HTTPException(status_code=500, detail=f"Batch assessment failed: {str(e)}")

# File upload for batch assessment
@app.post("/assess/upload", tags=["Assessment"])
async def assess_uploaded_file(
    file: UploadFile = File(...),
    model_instance: MalnutritionRandomForestModel = Depends(get_model),
    data_manager_instance: DataManager = Depends(get_data_manager)
):
    """
    Assess patients from uploaded file (CSV, Excel, JSON)
    
    - **file**: Uploaded file with patient data
    - Returns: Batch assessment results
    """
    try:
        # Validate file type
        if not file.filename or not file.filename.lower().endswith(('.csv', '.xlsx', '.json')):
            raise HTTPException(status_code=400, detail="Unsupported file format")
        
        # Read file
        if file.filename.lower().endswith('.csv'):
            df = pd.read_csv(file.file)
        elif file.filename.lower().endswith('.xlsx'):
            df = pd.read_excel(file.file)
        elif file.filename.lower().endswith('.json'):
            df = pd.read_json(file.file)
        
        # Validate and clean data
        df = data_manager_instance.validate_and_clean_data(df)
        
        # Convert to batch request format
        patients = []
        for _, row in df.iterrows():
            try:
                patient = PatientData(
                    name=row.get('name', 'Unknown'),
                    age_months=row.get('age_months', 0),
                    sex=row.get('sex', 'male'),
                    weight=row.get('weight', 0),
                    height=row.get('height', 0),
                    municipality=row.get('municipality', 'Unknown'),
                    total_household=row.get('total_household', 1),
                    adults=row.get('adults', 0),
                    children=row.get('children', 1),
                    twins=row.get('twins', 0),
                    four_ps_beneficiary=row.get('4ps_beneficiary', 'No'),
                    breastfeeding=row.get('breastfeeding', 'No'),
                    edema=row.get('edema', False),
                    tuberculosis=row.get('tuberculosis', 'No'),
                    malaria=row.get('malaria', 'No'),
                    congenital_anomalies=row.get('congenital_anomalies', 'No'),
                    other_medical_problems=row.get('other_medical_problems', 'No'),
                    date_of_admission=row.get('date_of_admission')
                )
                patients.append(patient)
            except Exception as e:
                logger.warning(f"Error processing row: {e}")
                continue
        
        # Create batch request
        batch_request = BatchAssessmentRequest(patients=patients)
        
        # Perform batch assessment
        return await assess_batch_patients(batch_request, model_instance)
        
    except Exception as e:
        logger.error(f"Error processing uploaded file: {e}")
        raise HTTPException(status_code=500, detail=f"File processing failed: {str(e)}")

# Model information
@app.get("/model/info", response_model=ModelInfo, tags=["Model"])
async def get_model_info(
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Get model information and capabilities
    
    Returns: Model details including features, protocols, and performance metrics
    """
    try:
        protocols = model_instance.get_available_protocols()
        
        return ModelInfo(
            model_type="Random Forest Classifier",
            version="1.0.0",
            features=model_instance.feature_columns,
            protocols=protocols,
            last_trained=datetime.now().isoformat(),
            accuracy=None  # Could be calculated if needed
        )
        
    except Exception as e:
        logger.error(f"Error getting model info: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to get model info: {str(e)}")

# Treatment protocols
@app.get("/protocols", tags=["Treatment"])
async def get_protocols(
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Get available treatment protocols
    
    Returns: List of available protocols with details
    """
    try:
        protocols = model_instance.get_available_protocols()
        protocol_details = {}
        
        for protocol in protocols:
            protocol_details[protocol] = model_instance.get_protocol_info(protocol)
        
        return {
            "available_protocols": protocols,
            "protocol_details": protocol_details,
            "active_protocol": model_instance.protocol_manager.active_protocol
        }
        
    except Exception as e:
        logger.error(f"Error getting protocols: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to get protocols: {str(e)}")

@app.post("/protocols/set", tags=["Treatment"])
async def set_protocol(
    protocol_name: str = Form(...),
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Set active treatment protocol
    
    - **protocol_name**: Name of the protocol to activate
    - Returns: Success status
    """
    try:
        success = model_instance.set_treatment_protocol(protocol_name)
        
        if success:
            return {"success": True, "active_protocol": protocol_name}
        else:
            raise HTTPException(status_code=400, detail=f"Protocol '{protocol_name}' not found")
            
    except Exception as e:
        logger.error(f"Error setting protocol: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to set protocol: {str(e)}")

# Model training
@app.post("/model/train", response_model=TrainingResponse, tags=["Model"])
async def train_model(
    request: TrainingRequest,
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Retrain the model with new data
    
    - **request**: Training parameters
    - Returns: Training results and performance metrics
    """
    try:
        # Generate sample data for training (in production, load from request.data_url)
        training_data = generate_sample_data(1000)
        
        # Set protocol if specified
        if request.protocol_name:
            model_instance.set_treatment_protocol(request.protocol_name)
        
        # Train model
        model_instance.train_model(training_data)
        
        # Save model
        model_instance.save_model('malnutrition_model.pkl')
        
        # Calculate performance metrics
        X = training_data[model_instance.feature_columns]
        y = training_data['nutritional_status']
        
        # Split data
        from sklearn.model_selection import train_test_split
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=request.test_size, random_state=42
        )
        
        # Get predictions
        y_pred = model_instance.model.predict(X_test)
        
        # Calculate accuracy
        from sklearn.metrics import accuracy_score
        accuracy = accuracy_score(y_test, y_pred)
        
        # Cross-validation score
        from sklearn.model_selection import cross_val_score
        cv_scores = cross_val_score(model_instance.model, X, y, cv=5)
        cv_score = cv_scores.mean()
        
        # Feature importance
        feature_importance = dict(zip(
            model_instance.feature_columns,
            model_instance.model.feature_importances_
        ))
        
        return TrainingResponse(
            success=True,
            accuracy=float(round(accuracy, 4)),
            cross_validation_score=round(cv_score, 4),
            feature_importance=feature_importance,
            training_samples=len(training_data),
            test_samples=len(X_test),
            model_saved=True
        )
        
    except Exception as e:
        logger.error(f"Error training model: {e}")
        raise HTTPException(status_code=500, detail=f"Training failed: {str(e)}")

# Data management endpoints
@app.get("/data/template", tags=["Data"])
async def get_data_template(
    data_manager_instance: DataManager = Depends(get_data_manager)
):
    """
    Get data template for patient assessment
    
    Returns: Sample data template
    """
    try:
        # Create sample template
        template_data = data_manager_instance.create_sample_dataset(5)
        return {
            "template": template_data.to_dict('records'),
            "required_fields": [
                "name", "age_months", "sex", "weight", "height",
                "municipality", "total_household", "adults", "children",
                "twins", "4ps_beneficiary", "breastfeeding", "edema",
                "tuberculosis", "malaria", "congenital_anomalies", "other_medical_problems"
            ],
            "field_descriptions": {
                "name": "Patient name",
                "age_months": "Age in months (0-60)",
                "sex": "Sex (male/female)",
                "weight": "Weight in kilograms",
                "height": "Height in centimeters",
                "municipality": "City/Municipality",
                "total_household": "Total household members",
                "adults": "Number of adults in household",
                "children": "Number of children in household",
                "twins": "Is twin (0/1)",
                "4ps_beneficiary": "4PS beneficiary (Yes/No)",
                "breastfeeding": "Breastfeeding (Yes/No)",
                "edema": "Has edema (True/False)",
                "tuberculosis": "Has tuberculosis (Yes/No)",
                "malaria": "Has malaria (Yes/No)",
                "congenital_anomalies": "Has congenital anomalies (Yes/No)",
                "other_medical_problems": "Other medical problems (Yes/No)"
            }
        }
        
    except Exception as e:
        logger.error(f"Error getting template: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to get template: {str(e)}")

@app.post("/data/validate", tags=["Data"])
async def validate_data(
    file: UploadFile = File(...),
    data_manager_instance: DataManager = Depends(get_data_manager)
):
    """
    Validate uploaded data file
    
    - **file**: Data file to validate
    - Returns: Validation results
    """
    try:
        # Read file
        if not file.filename:
            raise HTTPException(status_code=400, detail="No filename provided")
        
        if file.filename.lower().endswith('.csv'):
            df = pd.read_csv(file.file)
        elif file.filename.lower().endswith('.xlsx'):
            df = pd.read_excel(file.file)
        elif file.filename.lower().endswith('.json'):
            df = pd.read_json(file.file)
        else:
            raise HTTPException(status_code=400, detail="Unsupported file format")
        
        # Validate data
        validation_result = data_manager_instance.validate_data(df)
        
        return {
            "valid": validation_result['valid'],
            "errors": validation_result.get('errors', []),
            "warnings": validation_result.get('warnings', []),
            "total_rows": len(df),
            "valid_rows": len(df) - len(validation_result.get('errors', [])),
            "missing_fields": validation_result.get('missing_fields', [])
        }
        
    except Exception as e:
        logger.error(f"Error validating data: {e}")
        raise HTTPException(status_code=500, detail=f"Validation failed: {str(e)}")

# Analytics endpoints
@app.get("/analytics/summary", tags=["Analytics"])
async def get_analytics_summary():
    """
    Get system analytics summary
    
    Returns: System usage statistics and performance metrics
    """
    try:
        return {
            "total_assessments": 0,  # Would be tracked in database
            "model_accuracy": 0.95,  # Example
            "average_response_time": "0.5s",
            "system_uptime": "99.9%",
            "last_model_update": datetime.now().isoformat(),
            "supported_protocols": 3,
            "data_formats": ["CSV", "Excel", "JSON"]
        }
        
    except Exception as e:
        logger.error(f"Error getting analytics: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to get analytics: {str(e)}")

# Risk Stratification endpoint
@app.post("/risk/stratify", tags=["Risk Assessment"])
async def risk_stratification(
    patient: PatientData,
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Perform multi-level risk stratification
    
    Returns: Risk level and detailed risk factors
    """
    try:
        # Convert to dictionary format
        patient_dict = patient.dict()
        patient_dict['4ps_beneficiary'] = patient_dict.pop('four_ps_beneficiary')
        
        # Get base prediction
        result = model_instance.predict_single(patient_dict)
        
        # Calculate risk factors
        risk_factors = {
            'anthropometric_risk': calculate_anthropometric_risk(patient_dict, result),
            'clinical_risk': calculate_clinical_risk(patient_dict),
            'socioeconomic_risk': calculate_socioeconomic_risk(patient_dict),
            'environmental_risk': calculate_environmental_risk(patient_dict)
        }
        
        # Calculate total risk score
        total_risk = sum(risk_factors.values())
        
        # Determine risk level
        if total_risk >= 8:
            risk_level = 'critical'
        elif total_risk >= 5:
            risk_level = 'high'
        elif total_risk >= 3:
            risk_level = 'medium'
        else:
            risk_level = 'low'
        
        return {
            "risk_level": risk_level,
            "total_risk_score": total_risk,
            "risk_factors": risk_factors,
            "recommendations": get_risk_based_recommendations(risk_level, risk_factors)
        }
        
    except Exception as e:
        logger.error(f"Error in risk stratification: {e}")
        raise HTTPException(status_code=500, detail=f"Risk stratification failed: {str(e)}")

# Uncertainty Quantification endpoint
@app.post("/predict/uncertainty", tags=["Prediction"])
async def predict_with_uncertainty(
    patient: PatientData,
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Predict malnutrition status with uncertainty quantification
    
    Returns: Prediction with confidence intervals and uncertainty measures
    """
    try:
        # Convert to dictionary format
        patient_dict = patient.dict()
        patient_dict['4ps_beneficiary'] = patient_dict.pop('four_ps_beneficiary')
        
        # Get base prediction
        result = model_instance.predict_single(patient_dict)
        
        # Calculate uncertainty measures
        probabilities = result['probabilities']
        max_prob = max(probabilities.values())
        
        # Determine confidence level
        if max_prob >= 0.9:
            confidence_level = "high"
        elif max_prob >= 0.7:
            confidence_level = "medium"
        else:
            confidence_level = "low"
        
        # Calculate prediction intervals
        prediction_intervals = calculate_prediction_intervals(probabilities)
        
        # Uncertainty quantification
        uncertainty_measures = {
            "confidence_level": confidence_level,
            "max_probability": max_prob,
            "entropy": calculate_entropy(probabilities),
            "prediction_intervals": prediction_intervals,
            "uncertainty_reason": get_uncertainty_reason(probabilities, patient_dict)
        }
        
        return {
            "prediction": result['prediction'],
            "whz_score": result['whz_score'],
            "probabilities": probabilities,
            "uncertainty_measures": uncertainty_measures,
            "recommendation": result['recommendation']
        }
        
    except Exception as e:
        logger.error(f"Error in uncertainty quantification: {e}")
        raise HTTPException(status_code=500, detail=f"Uncertainty quantification failed: {str(e)}")

# Personalized Recommendations endpoint
@app.post("/recommendations/personalized", tags=["Recommendations"])
async def get_personalized_recommendations(
    patient: PatientData,
    model_instance: MalnutritionRandomForestModel = Depends(get_model)
):
    """
    Get personalized recommendations based on individual factors
    
    Returns: Age-specific, family-considerate, and culturally-adapted recommendations
    """
    try:
        # Convert to dictionary format
        patient_dict = patient.dict()
        patient_dict['4ps_beneficiary'] = patient_dict.pop('four_ps_beneficiary')
        
        # Get base prediction
        result = model_instance.predict_single(patient_dict)
        
        # Generate personalized recommendations
        personalized_recs = generate_personalized_recommendations(patient_dict, result)
        
        return {
            "patient_info": {
                "name": patient.name,
                "age_months": patient.age_months,
                "sex": patient.sex,
                "municipality": patient.municipality
            },
            "assessment_result": {
                "prediction": result['prediction'],
                "whz_score": result['whz_score']
            },
            "personalized_recommendations": personalized_recs
        }
        
    except Exception as e:
        logger.error(f"Error generating personalized recommendations: {e}")
        raise HTTPException(status_code=500, detail=f"Personalized recommendations failed: {str(e)}")

# Root endpoint
@app.get("/", tags=["System"])
async def root():
    """Root endpoint with API information"""
    return {
        "message": "Child Malnutrition Assessment API",
        "version": "1.0.0",
        "status": "running",
        "endpoints": {
            "health": "/health",
            "single_assessment": "/assess/single",
            "batch_assessment": "/assess/batch",
            "file_upload": "/assess/upload",
            "model_info": "/model/info",
            "protocols": "/protocols",
            "training": "/model/train",
            "data_template": "/data/template",
            "data_validation": "/data/validate",
            "analytics": "/analytics/summary"
        },
        "documentation": "/docs"
    }

if __name__ == "__main__":
    import uvicorn
    # Use port 8080 to avoid conflict with Laravel (port 8000)
    port = int(os.getenv('PORT', 8081))
    host = os.getenv('HOST', '127.0.0.1')
    uvicorn.run(app, host=host, port=port) 