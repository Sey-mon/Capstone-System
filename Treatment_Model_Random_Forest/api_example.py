#!/usr/bin/env python3
"""
API Usage Example for Malnutrition Model
Simple example showing how to use the trained model for API purposes
"""

from malnutrition_model import MalnutritionRandomForestModel
import json
from datetime import datetime

def load_model_for_api():
    """Load the trained model for API use"""
    model = MalnutritionRandomForestModel()
    # You can load a pre-trained model if available
    # model.load_model('malnutrition_model.pkl')
    return model

def api_assess_patient(patient_data):
    """
    API function to assess malnutrition status
    
    Args:
        patient_data (dict): Patient information with keys:
            - weight (float): Weight in kg
            - height (float): Height in cm
            - age_months (int): Age in months
            - sex (str): 'male' or 'female'
            - has_edema (bool, optional): Presence of edema
    
    Returns:
        dict: Assessment results ready for API response
    """
    try:
        model = load_model_for_api()
        
        # Get comprehensive assessment
        result = model.enhanced_assessment(
            weight=patient_data['weight'],
            height=patient_data['height'],
            age_months=patient_data['age_months'],
            sex=patient_data['sex'],
            has_edema=patient_data.get('has_edema', False)
        )
        
        if result and 'error' not in result:
            return {
                'success': True,
                'patient_id': patient_data.get('id', 'unknown'),
                'assessment': {
                    'nutritional_status': result['classifications']['nutritional_status'],
                    'bmi': result['measurements']['bmi'],
                    'bmi_status': result['classifications']['bmi_status'],
                    'z_scores': {
                        'weight_for_age': result['z_scores']['weight_for_age'],
                        'height_for_age': result['z_scores']['height_for_age']
                    },
                    'confidence': {
                        'score': result['confidence']['overall_confidence'],
                        'level': result['confidence']['confidence_level']
                    },
                    'recommendations': {
                        'clinical': result['interpretation']['recommendation'],
                        'follow_up': result['interpretation']['follow_up']
                    }
                },
                'timestamp': datetime.now().isoformat()
            }
        else:
            return {
                'success': False,
                'error': result.get('error', 'Assessment failed'),
                'timestamp': datetime.now().isoformat()
            }
            
    except Exception as e:
        return {
            'success': False,
            'error': f'API error: {str(e)}',
            'timestamp': datetime.now().isoformat()
        }

def main():
    """Example usage of the API function"""
    print("🔌 Malnutrition Model API Usage Example")
    print("=" * 50)
    
    # Example patient data
    test_patients = [
        {
            'id': 'PAT001',
            'weight': 9.0,
            'height': 74.0,
            'age_months': 12,
            'sex': 'female'
        },
        {
            'id': 'PAT002',
            'weight': 7.5,
            'height': 75.0,
            'age_months': 18,
            'sex': 'male'
        },
        {
            'id': 'PAT003',
            'weight': 12.5,
            'height': 88.0,
            'age_months': 24,
            'sex': 'male'
        }
    ]
    
    # Test the API function
    for patient in test_patients:
        print(f"\n📊 Assessing Patient {patient['id']}:")
        print(f"   {patient['age_months']} months, {patient['sex']}")
        print(f"   Weight: {patient['weight']} kg, Height: {patient['height']} cm")
        
        result = api_assess_patient(patient)
        
        if result['success']:
            assessment = result['assessment']
            print(f"\n✅ Assessment Results:")
            print(f"   Status: {assessment['nutritional_status']}")
            print(f"   BMI: {assessment['bmi']} ({assessment['bmi_status']})")
            print(f"   Confidence: {assessment['confidence']['score']}% ({assessment['confidence']['level']})")
            print(f"   Recommendation: {assessment['recommendations']['clinical']}")
        else:
            print(f"\n❌ Error: {result['error']}")
    
    print(f"\n{'=' * 50}")
    print("🎉 API Example completed!")
    print("💡 This shows how to integrate the model into your API")

if __name__ == "__main__":
    main()
