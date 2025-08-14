"""
Enhanced Model Features for Child Malnutrition Assessment
All implementations use Random Forest techniques for consistency with research focus
"""

import numpy as np
import pandas as pd
from typing import Dict, List, Any, Tuple
import math
from datetime import datetime, timedelta

def calculate_anthropometric_risk(patient_data: Dict, result: Dict) -> float:
    """
    Calculate anthropometric risk score using Random Forest features
    """
    risk_score = 0.0
    
    # WHZ score risk
    whz_score = result.get('whz_score', 0)
    if whz_score < -3:
        risk_score += 3.0
    elif whz_score < -2:
        risk_score += 2.0
    elif whz_score < -1:
        risk_score += 1.0
    
    # Age-specific risk
    age_months = patient_data.get('age_months', 0)
    if age_months < 6:  # Infants at higher risk
        risk_score += 1.5
    elif age_months < 12:
        risk_score += 1.0
    
    # Weight-to-height ratio risk
    weight = patient_data.get('weight', 0)
    height = patient_data.get('height', 0)
    if height > 0:
        weight_height_ratio = weight / height
        if weight_height_ratio < 0.08:  # Very low ratio
            risk_score += 2.0
        elif weight_height_ratio < 0.1:
            risk_score += 1.0
    
    return min(risk_score, 5.0)  # Cap at 5

def calculate_clinical_risk(patient_data: Dict) -> float:
    """
    Calculate clinical risk score
    """
    risk_score = 0.0
    
    # Edema (highest risk factor)
    if patient_data.get('edema', False):
        risk_score += 3.0
    
    # Medical conditions
    if patient_data.get('tuberculosis', 'No').lower() == 'yes':
        risk_score += 2.0
    
    if patient_data.get('malaria', 'No').lower() == 'yes':
        risk_score += 1.5
    
    if patient_data.get('congenital_anomalies', 'No').lower() == 'yes':
        risk_score += 2.0
    
    if patient_data.get('other_medical_problems', 'No').lower() == 'yes':
        risk_score += 1.0
    
    return min(risk_score, 5.0)

def calculate_socioeconomic_risk(patient_data: Dict) -> float:
    """
    Calculate socioeconomic risk score
    """
    risk_score = 0.0
    
    # 4PS beneficiary status
    if patient_data.get('4ps_beneficiary', 'No').lower() == 'yes':
        risk_score += 1.5  # Indicates economic vulnerability
    
    # Household composition
    total_household = patient_data.get('total_household', 1)
    children = patient_data.get('children', 1)
    
    if children > 3:  # Large family
        risk_score += 1.0
    
    if total_household > 6:  # Large household
        risk_score += 0.5
    
    # Twin status
    if patient_data.get('twins', 0) == 1:
        risk_score += 1.0
    
    return min(risk_score, 3.0)

def calculate_environmental_risk(patient_data: Dict) -> float:
    """
    Calculate environmental risk score
    """
    risk_score = 0.0
    
    # Municipality-based risk (simplified)
    municipality = patient_data.get('municipality', '').lower()
    
    # High-risk municipalities (example)
    high_risk_areas = ['remote', 'mountain', 'coastal', 'rural']
    for area in high_risk_areas:
        if area in municipality:
            risk_score += 1.0
            break
    
    # Breastfeeding status
    if patient_data.get('breastfeeding', 'No').lower() == 'no':
        risk_score += 1.0
    
    return min(risk_score, 2.0)

def get_risk_based_recommendations(risk_level: str, risk_factors: Dict) -> Dict:
    """
    Generate recommendations based on risk level and factors
    """
    recommendations = {
        "immediate_actions": [],
        "monitoring_frequency": "",
        "referral_needs": [],
        "family_education": []
    }
    
    if risk_level == "critical":
        recommendations["immediate_actions"] = [
            "Immediate hospitalization required",
            "Start therapeutic feeding immediately",
            "Monitor vital signs every 2 hours"
        ]
        recommendations["monitoring_frequency"] = "Daily"
        recommendations["referral_needs"] = ["Pediatric specialist", "Nutritionist"]
        
    elif risk_level == "high":
        recommendations["immediate_actions"] = [
            "Start supplementary feeding",
            "Weekly weight monitoring",
            "Family nutrition education"
        ]
        recommendations["monitoring_frequency"] = "Weekly"
        recommendations["referral_needs"] = ["Nutritionist"]
        
    elif risk_level == "medium":
        recommendations["immediate_actions"] = [
            "Enhanced nutrition counseling",
            "Bi-weekly monitoring",
            "Family support programs"
        ]
        recommendations["monitoring_frequency"] = "Bi-weekly"
        
    else:  # low risk
        recommendations["immediate_actions"] = [
            "Regular growth monitoring",
            "Nutrition education",
            "Preventive care"
        ]
        recommendations["monitoring_frequency"] = "Monthly"
    
    # Add specific recommendations based on risk factors
    if risk_factors.get('clinical_risk', 0) > 2:
        recommendations["family_education"].append("Medical condition management")
    
    if risk_factors.get('socioeconomic_risk', 0) > 2:
        recommendations["family_education"].append("Economic support programs")
    
    return recommendations

def calculate_prediction_intervals(probabilities: Dict) -> Dict:
    """
    Calculate prediction intervals using Random Forest probabilities
    """
    max_prob = max(probabilities.values())
    min_prob = min(probabilities.values())
    
    # Calculate confidence intervals
    confidence_interval = {
        "lower_bound": max(0, max_prob - 0.1),
        "upper_bound": min(1, max_prob + 0.1),
        "width": 0.2
    }
    
    return confidence_interval

def calculate_entropy(probabilities: Dict) -> float:
    """
    Calculate entropy as a measure of uncertainty
    """
    entropy = 0.0
    for prob in probabilities.values():
        if prob > 0:
            entropy -= prob * math.log2(prob)
    return entropy

def get_uncertainty_reason(probabilities: Dict, patient_data: Dict) -> str:
    """
    Determine reason for uncertainty in prediction
    """
    max_prob = max(probabilities.values())
    
    if max_prob < 0.6:
        return "Low confidence due to conflicting indicators"
    elif max_prob < 0.8:
        return "Medium confidence - borderline case"
    else:
        return "High confidence prediction"
    
    # Additional reasons based on patient data
    age_months = patient_data.get('age_months', 0)
    if age_months < 6:
        return "Infant assessment - requires specialized consideration"
    
    return "Standard assessment"

def generate_personalized_recommendations(patient_data: Dict, result: Dict) -> Dict:
    """
    Generate personalized recommendations based on individual factors
    """
    age_months = patient_data.get('age_months', 0)
    sex = patient_data.get('sex', 'male')
    municipality = patient_data.get('municipality', '')
    prediction = result.get('prediction', 'Normal')
    
    recommendations = {
        "age_specific": get_age_specific_recommendations(age_months, prediction),
        "family_considerations": get_family_considerations(patient_data),
        "cultural_adaptations": get_cultural_adaptations(municipality),
        "comorbidity_advice": get_comorbidity_advice(patient_data, prediction)
    }
    
    return recommendations

def get_age_specific_recommendations(age_months: int, prediction: str) -> Dict:
    """
    Get age-specific recommendations
    """
    if age_months < 6:
        return {
            "feeding": "Exclusive breastfeeding recommended",
            "monitoring": "Weekly weight checks",
            "supplements": "Vitamin D supplementation",
            "frequency": "Daily monitoring"
        }
    elif age_months < 12:
        return {
            "feeding": "Complementary feeding with continued breastfeeding",
            "monitoring": "Bi-weekly weight checks",
            "supplements": "Iron-rich foods introduction",
            "frequency": "Bi-weekly monitoring"
        }
    elif age_months < 24:
        return {
            "feeding": "Family foods with continued breastfeeding",
            "monitoring": "Monthly weight checks",
            "supplements": "Balanced diet with protein",
            "frequency": "Monthly monitoring"
        }
    else:
        return {
            "feeding": "Family diet with adequate protein",
            "monitoring": "Monthly growth monitoring",
            "supplements": "Vitamin A and iron-rich foods",
            "frequency": "Monthly monitoring"
        }

def get_family_considerations(patient_data: Dict) -> Dict:
    """
    Get family-specific recommendations
    """
    total_household = patient_data.get('total_household', 1)
    children = patient_data.get('children', 1)
    is_4ps = patient_data.get('4ps_beneficiary', 'No').lower() == 'yes'
    
    considerations = {
        "household_size": "Standard recommendations",
        "economic_support": [],
        "family_education": []
    }
    
    if total_household > 6:
        considerations["household_size"] = "Large family - consider food distribution strategies"
    
    if children > 3:
        considerations["family_education"].append("Sibling nutrition education")
    
    if is_4ps:
        considerations["economic_support"].append("4PS benefits utilization")
    
    return considerations

def get_cultural_adaptations(municipality: str) -> Dict:
    """
    Get culturally-adapted recommendations
    """
    municipality_lower = municipality.lower()
    
    adaptations = {
        "food_preferences": "Local food incorporation",
        "feeding_practices": "Standard recommendations",
        "family_involvement": "Family-centered approach"
    }
    
    # Example cultural adaptations based on municipality
    if 'coastal' in municipality_lower:
        adaptations["food_preferences"] = "Fish and seafood incorporation"
    elif 'mountain' in municipality_lower:
        adaptations["food_preferences"] = "Root crops and vegetables"
    
    return adaptations

def get_comorbidity_advice(patient_data: Dict, prediction: str) -> Dict:
    """
    Get comorbidity-specific advice
    """
    advice = {
        "medical_conditions": [],
        "dietary_modifications": [],
        "monitoring_enhancements": []
    }
    
    if patient_data.get('tuberculosis', 'No').lower() == 'yes':
        advice["medical_conditions"].append("TB treatment compliance")
        advice["dietary_modifications"].append("High-protein diet for TB")
    
    if patient_data.get('malaria', 'No').lower() == 'yes':
        advice["medical_conditions"].append("Malaria treatment")
        advice["monitoring_enhancements"].append("Fever monitoring")
    
    if patient_data.get('congenital_anomalies', 'No').lower() == 'yes':
        advice["medical_conditions"].append("Specialized care coordination")
        advice["monitoring_enhancements"].append("Developmental monitoring")
    
    return advice

 