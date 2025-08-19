"""
Quick model training script to generate malnutrition_model.pkl
This creates a basic trained model for the API to use
"""

import pandas as pd
import numpy as np
from malnutrition_model import MalnutritionRandomForestModel
import warnings
warnings.filterwarnings('ignore')

def create_simple_training_data():
    """Create simple training data for the model"""
    np.random.seed(42)
    
    data = []
    for i in range(500):  # Smaller dataset for quick training
        age_months = np.random.randint(6, 60)  # 6-60 months
        sex = np.random.choice(['Male', 'Female'])
        
        # Generate realistic height and weight based on age
        if age_months <= 12:
            height = 65 + age_months * 1.5 + np.random.normal(0, 3)
            base_weight = 6 + age_months * 0.4
        elif age_months <= 24:
            height = 75 + (age_months - 12) * 1.0 + np.random.normal(0, 4)
            base_weight = 10 + (age_months - 12) * 0.25
        else:
            height = 87 + (age_months - 24) * 0.7 + np.random.normal(0, 5)
            base_weight = 13 + (age_months - 24) * 0.15
        
        # Add malnutrition variation
        malnutrition_factor = np.random.choice([0.75, 0.85, 0.95, 1.0, 1.05], 
                                             p=[0.15, 0.20, 0.30, 0.25, 0.10])
        weight = max(2, base_weight * malnutrition_factor + np.random.normal(0, 0.8))
        height = max(45, height)
        
        # Calculate WHZ score (simplified)
        height_m = height / 100
        bmi = weight / (height_m ** 2)
        
        # Simple WHZ approximation based on BMI for age
        if age_months <= 24:
            expected_bmi = 16.5
        else:
            expected_bmi = 15.5
            
        whz_score = (bmi - expected_bmi) / 1.5  # Simplified z-score
        
        record = {
            'name': f'Child_{i+1}',
            'municipality': np.random.choice(['Manila', 'Quezon', 'Cebu']),
            'number': f'ID_{i+1:04d}',
            'age_months': age_months,
            'sex': sex,
            'date_of_admission': pd.Timestamp.now(),
            'total_household': np.random.randint(3, 10),
            'adults': np.random.randint(2, 4),
            'children': np.random.randint(1, 5),
            'twins': 0,
            '4ps_beneficiary': np.random.choice(['Yes', 'No']),
            'weight': round(weight, 1),
            'height': round(height, 1),
            'whz_score': round(whz_score, 2),
            'breastfeeding': np.random.choice(['Yes', 'No']) if age_months <= 24 else 'No',
            'tuberculosis': 'No',
            'malaria': 'No',
            'congenital_anomalies': 'No',
            'other_medical_problems': 'No',
            'edema': False
        }
        data.append(record)
    
    return pd.DataFrame(data)

def train_and_save_model():
    """Train and save the malnutrition model"""
    print("Creating training data...")
    df = create_simple_training_data()
    
    print("Training Random Forest model...")
    model = MalnutritionRandomForestModel()
    
    try:
        # Train the model
        X_test, y_test, y_pred = model.train_model(df)
        model.is_trained = True
        
        # Save the model
        model.save_model('malnutrition_model.pkl')
        print("✅ Model trained and saved successfully!")
        
        # Test the model
        print("\nTesting model...")
        sample_data = {
            'name': 'Test Child',
            'municipality': 'Manila',
            'number': 'TEST001',
            'age_months': 18,
            'sex': 'Male',
            'date_of_admission': pd.Timestamp.now(),
            'total_household': 5,
            'adults': 2,
            'children': 3,
            'twins': 0,
            '4ps_beneficiary': 'Yes',
            'weight': 8.5,
            'height': 78.0,
            'whz_score': -1.5,
            'breastfeeding': 'No',
            'tuberculosis': 'No',
            'malaria': 'No',
            'congenital_anomalies': 'No',
            'other_medical_problems': 'No',
            'edema': False
        }
        
        result = model.predict_single(sample_data)
        print(f"Test prediction: {result['prediction']}")
        print("✅ Model is working correctly!")
        
        return True
        
    except Exception as e:
        print(f"❌ Error training model: {e}")
        return False

if __name__ == "__main__":
    print("🚀 Quick Model Training for API")
    print("=" * 40)
    
    success = train_and_save_model()
    
    if success:
        print("\n🎯 Model ready for API use!")
        print("Now you can start the API server with: python api_server.py")
    else:
        print("\n❌ Model training failed. Check errors above.")
