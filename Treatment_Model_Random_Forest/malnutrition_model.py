"""
Child Malnutrition Assessment Model using Random Forest
Based on WHO guidelines for children aged 0-5 years
Now with flexible treatment protocol system
"""

import pandas as pd
import numpy as np
import os
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import matplotlib.pyplot as plt
import seaborn as sns
from scipy import stats
import joblib
import warnings
from treatment_protocol_manager import TreatmentProtocolManager
warnings.filterwarnings('ignore')

class WHO_ZScoreCalculator:
    """
    WHO Z-Score calculator for children 0-5 years
    """
    
    def __init__(self):
        # Load WHO reference data from Excel files
        self.who_reference = {}
        self.who_folder = "who_standard"
        self._load_who_reference_data()
        
        # Fallback data in case Excel files are not available (keeping original for safety)
        self.fallback_who_reference = {
            'weight_for_height': {
                'boys': {
                    45: {'mean': 2.5, 'sd': 0.3},
                    50: {'mean': 3.3, 'sd': 0.4},
                    55: {'mean': 4.3, 'sd': 0.4},
                    60: {'mean': 5.4, 'sd': 0.5},
                    65: {'mean': 6.7, 'sd': 0.5},
                    70: {'mean': 8.2, 'sd': 0.6},
                    75: {'mean': 9.9, 'sd': 0.6},
                    80: {'mean': 11.8, 'sd': 0.7},
                    85: {'mean': 13.9, 'sd': 0.8},
                    90: {'mean': 16.2, 'sd': 0.9},
                    95: {'mean': 18.7, 'sd': 1.0},
                    100: {'mean': 21.4, 'sd': 1.1},
                    105: {'mean': 24.3, 'sd': 1.2},
                    110: {'mean': 27.4, 'sd': 1.3}
                },
                'girls': {
                    45: {'mean': 2.4, 'sd': 0.3},
                    50: {'mean': 3.2, 'sd': 0.4},
                    55: {'mean': 4.2, 'sd': 0.4},
                    60: {'mean': 5.3, 'sd': 0.5},
                    65: {'mean': 6.5, 'sd': 0.5},
                    70: {'mean': 7.9, 'sd': 0.6},
                    75: {'mean': 9.5, 'sd': 0.6},
                    80: {'mean': 11.3, 'sd': 0.7},
                    85: {'mean': 13.3, 'sd': 0.8},
                    90: {'mean': 15.5, 'sd': 0.9},
                    95: {'mean': 17.9, 'sd': 1.0},
                    100: {'mean': 20.5, 'sd': 1.1},
                    105: {'mean': 23.3, 'sd': 1.2},
                    110: {'mean': 26.3, 'sd': 1.3}
                }
            }
        }
    
    def _load_who_reference_data(self):
        """
        Load WHO reference data from Excel files
        """
        import os
        
        try:
            # Initialize the reference data structure
            self.who_reference = {
                'weight_for_age': {'boys': {}, 'girls': {}},
                'height_for_age': {'boys': {}, 'girls': {}},
                'weight_for_height': {'boys': {}, 'girls': {}}  # Keep for backward compatibility
            }
            
            # Define the Excel files to load
            excel_files = {
                'weight_for_age': {
                    'boys': 'wfa_boys_0-to-5-years_zscores.xlsx',
                    'girls': 'wfa_girls_0-to-5-years_zscores.xlsx'
                },
                'height_for_age': {
                    'boys': 'lhfa_boys_0-to-5-years_zscores.xlsx',
                    'girls': 'lhfa_girls_0-to-5-years_zscores.xlsx'
                }
            }
            
            # Load each Excel file
            for measurement_type, genders in excel_files.items():
                for gender, filename in genders.items():
                    file_path = os.path.join(self.who_folder, filename)
                    
                    if os.path.exists(file_path):
                        try:
                            # Read the Excel file
                            df = pd.read_excel(file_path)
                            
                            # Process each row to create lookup table
                            for _, row in df.iterrows():
                                month = int(row['Month'])
                                
                                # Store the data with month as key
                                self.who_reference[measurement_type][gender][month] = {
                                    'L': float(row['L']),
                                    'M': float(row['M']),  # Median
                                    'S': float(row['S']),
                                    'mean': float(row['M']),  # For backward compatibility
                                    'sd': float(row['S']),    # For backward compatibility
                                    'SD3neg': float(row['SD3neg']),
                                    'SD2neg': float(row['SD2neg']),
                                    'SD1neg': float(row['SD1neg']),
                                    'SD0': float(row['SD0']),
                                    'SD1': float(row['SD1']),
                                    'SD2': float(row['SD2']),
                                    'SD3': float(row['SD3'])
                                }
                            
                            print(f"Successfully loaded {filename}")
                            
                        except Exception as e:
                            print(f"Error loading {filename}: {e}")
                    else:
                        print(f"WHO reference file not found: {file_path}")
            
            # If no Excel files were loaded, use fallback data
            if not any(self.who_reference[measurement_type][gender] 
                      for measurement_type in self.who_reference 
                      for gender in ['boys', 'girls']):
                print("No WHO Excel files found, using fallback data")
                self.who_reference = self.fallback_who_reference
            
        except Exception as e:
            print(f"Error loading WHO reference data: {e}")
            print("Using fallback reference data")
            self.who_reference = self.fallback_who_reference
    
    def calculate_weight_for_age_zscore(self, weight, age_months, sex):
        """
        Calculate Weight-for-Age Z-score using WHO standards
        """
        try:
            sex_key = 'boys' if sex.lower() in ['male', 'm', 'boy'] else 'girls'
            
            # Check if we have weight_for_age data
            if 'weight_for_age' in self.who_reference and sex_key in self.who_reference['weight_for_age']:
                age_data = self.who_reference['weight_for_age'][sex_key]
                
                # Find closest age in months
                if age_months in age_data:
                    reference = age_data[age_months]
                else:
                    # Find closest age
                    closest_age = min(age_data.keys(), key=lambda x: abs(x - age_months))
                    reference = age_data[closest_age]
                
                # Calculate Z-score using LMS method
                L = reference['L']
                M = reference['M']
                S = reference['S']
                
                if L != 0:
                    z_score = (((weight / M) ** L) - 1) / (L * S)
                else:
                    z_score = np.log(weight / M) / S
                
                return round(z_score, 2)
            else:
                return 0
                
        except Exception as e:
            print(f"Error calculating Weight-for-Age Z-score: {e}")
            return 0
    
    def calculate_height_for_age_zscore(self, height, age_months, sex):
        """
        Calculate Height-for-Age Z-score using WHO standards
        """
        try:
            sex_key = 'boys' if sex.lower() in ['male', 'm', 'boy'] else 'girls'
            
            # Check if we have height_for_age data
            if 'height_for_age' in self.who_reference and sex_key in self.who_reference['height_for_age']:
                age_data = self.who_reference['height_for_age'][sex_key]
                
                # Find closest age in months
                if age_months in age_data:
                    reference = age_data[age_months]
                else:
                    # Find closest age
                    closest_age = min(age_data.keys(), key=lambda x: abs(x - age_months))
                    reference = age_data[closest_age]
                
                # Calculate Z-score using LMS method
                L = reference['L']
                M = reference['M']
                S = reference['S']
                
                if L != 0:
                    z_score = (((height / M) ** L) - 1) / (L * S)
                else:
                    z_score = np.log(height / M) / S
                
                return round(z_score, 2)
            else:
                return 0
                
        except Exception as e:
            print(f"Error calculating Height-for-Age Z-score: {e}")
            return 0
    
    def calculate_bmi(self, weight, height):
        """
        Calculate BMI (Body Mass Index)
        BMI = weight (kg) / height (m)²
        """
        try:
            # Convert height from cm to meters
            height_m = height / 100
            
            # Calculate BMI
            bmi = weight / (height_m ** 2)
            
            return round(bmi, 2)
        
        except Exception as e:
            print(f"Error calculating BMI: {e}")
            return 0
    
    def classify_bmi_status(self, bmi, age_months):
        """
        Classify BMI status for children based on WHO standards
        """
        try:
            if age_months < 24:  # Under 2 years
                if bmi < 13:
                    return "Severely Underweight"
                elif bmi < 15:
                    return "Underweight" 
                elif bmi <= 18:
                    return "Normal"
                else:
                    return "Overweight"
            else:  # 2-5 years
                if bmi < 13.5:
                    return "Severely Underweight"
                elif bmi < 15.5:
                    return "Underweight"
                elif bmi <= 17.5:
                    return "Normal"
                else:
                    return "Overweight"
        except Exception as e:
            print(f"Error classifying BMI status: {e}")
            return "Unknown"
    
    def classify_nutritional_status(self, wfa_zscore, hfa_zscore, bmi, age_months, has_edema=False):
        """
        Classify nutritional status based on multiple indicators
        Following WHO guidelines with comprehensive assessment
        """
        if has_edema:
            return "Severe Acute Malnutrition (SAM)"
        
        # Classify based on BMI status
        bmi_status = self.classify_bmi_status(bmi, age_months)
        
        # Count severe indicators
        severe_indicators = 0
        moderate_indicators = 0
        
        if wfa_zscore < -3:
            severe_indicators += 1
        elif wfa_zscore < -2:
            moderate_indicators += 1
            
        if hfa_zscore < -3:
            severe_indicators += 1  
        elif hfa_zscore < -2:
            moderate_indicators += 1
            
        if bmi_status in ["Severely Underweight"]:
            severe_indicators += 1
        elif bmi_status in ["Underweight"]:
            moderate_indicators += 1
            
        # Classification logic
        if severe_indicators >= 2:
            return "Severe Acute Malnutrition (SAM)"
        elif severe_indicators >= 1 or moderate_indicators >= 2:
            return "Moderate Acute Malnutrition (MAM)"
        elif moderate_indicators >= 1:
            return "At Risk"
        else:
            return "Normal"
    
    def calculate_confidence_score(self, weight, height, age_months, sex, wfa_zscore, hfa_zscore, bmi):
        """
        Calculate confidence score for the nutritional assessment
        Returns a score from 0-100 indicating reliability of the assessment
        """
        confidence_factors = []
        
        # 1. Data quality factors (40% of total confidence)
        # Age appropriateness (0-60 months)
        if 0 <= age_months <= 60:
            age_confidence = 100
        elif age_months > 60:
            age_confidence = max(0, 100 - (age_months - 60) * 2)
        else:
            age_confidence = 0
        confidence_factors.append(("Age Range", age_confidence, 0.15))
        
        # Height plausibility (45-120 cm for 0-5 years)
        if 45 <= height <= 120:
            height_confidence = 100
        else:
            height_confidence = max(0, 100 - abs(height - 80) * 2)
        confidence_factors.append(("Height Range", height_confidence, 0.15))
        
        # Weight plausibility (2-30 kg for 0-5 years)
        if 2 <= weight <= 30:
            weight_confidence = 100
        else:
            weight_confidence = max(0, 100 - abs(weight - 15) * 5)
        confidence_factors.append(("Weight Range", weight_confidence, 0.10))
        
        # 2. Z-score consistency (30% of total confidence)
        # Check if Z-scores are within expected ranges (-5 to +5)
        wfa_confidence = max(0, 100 - abs(wfa_zscore) * 10) if abs(wfa_zscore) <= 5 else 0
        hfa_confidence = max(0, 100 - abs(hfa_zscore) * 10) if abs(hfa_zscore) <= 5 else 0
        confidence_factors.append(("WFA Z-score", wfa_confidence, 0.15))
        confidence_factors.append(("HFA Z-score", hfa_confidence, 0.15))
        
        # 3. BMI reasonableness (20% of total confidence)
        # Expected BMI range for children: 12-25
        if 12 <= bmi <= 25:
            bmi_confidence = 100
        else:
            bmi_confidence = max(0, 100 - abs(bmi - 17) * 5)
        confidence_factors.append(("BMI Range", bmi_confidence, 0.20))
        
        # 4. Indicator consistency (10% of total confidence)
        # Check if different indicators point to similar conclusions
        indicators = []
        if wfa_zscore < -2:
            indicators.append("underweight")
        if hfa_zscore < -2:
            indicators.append("stunted")
        if bmi < 15:
            indicators.append("thin")
            
        if len(set(indicators)) <= 1:  # Consistent indicators
            consistency_confidence = 100
        elif len(set(indicators)) == 2:
            consistency_confidence = 70
        else:
            consistency_confidence = 40
        confidence_factors.append(("Indicator Consistency", consistency_confidence, 0.10))
        
        # Calculate weighted confidence score
        total_confidence = sum(score * weight for _, score, weight in confidence_factors)
        
        return {
            'overall_confidence': round(total_confidence, 1),
            'confidence_level': self._get_confidence_level(total_confidence),
            'factors': confidence_factors
        }
    
    def _get_confidence_level(self, score):
        """Get confidence level description"""
        if score >= 85:
            return "Very High"
        elif score >= 70:
            return "High"
        elif score >= 55:
            return "Moderate"
        elif score >= 40:
            return "Low"
        else:
            return "Very Low"
    
    def comprehensive_assessment(self, weight, height, age_months, sex, has_edema=False):
        """
        Perform comprehensive nutritional assessment with confidence scoring
        """
        try:
            # Calculate all indicators
            wfa_zscore = self.calculate_weight_for_age_zscore(weight, age_months, sex)
            hfa_zscore = self.calculate_height_for_age_zscore(height, age_months, sex)
            bmi = self.calculate_bmi(weight, height)
            bmi_status = self.classify_bmi_status(bmi, age_months)
            
            # Classify nutritional status
            nutritional_status = self.classify_nutritional_status(
                wfa_zscore, hfa_zscore, bmi, age_months, has_edema
            )
            
            # Calculate confidence score
            confidence_data = self.calculate_confidence_score(
                weight, height, age_months, sex, wfa_zscore, hfa_zscore, bmi
            )
            
            return {
                'measurements': {
                    'weight': weight,
                    'height': height,
                    'age_months': age_months,
                    'sex': sex,
                    'bmi': bmi
                },
                'z_scores': {
                    'weight_for_age': wfa_zscore,
                    'height_for_age': hfa_zscore
                },
                'classifications': {
                    'bmi_status': bmi_status,
                    'nutritional_status': nutritional_status
                },
                'confidence': confidence_data,
                'has_edema': has_edema
            }
            
        except Exception as e:
            print(f"Error in comprehensive assessment: {e}")
            return None

class MalnutritionRandomForestModel:
    """
    Random Forest model for predicting malnutrition status in children
    Now includes flexible treatment protocol system
    """
    
    def __init__(self, protocol_name='who_standard'):
        self.model = RandomForestClassifier(
            n_estimators=100,
            random_state=42,
            max_depth=10,
            min_samples_split=5,
            min_samples_leaf=2
        )
        self.label_encoders = {}
        self.scaler = StandardScaler()
        self.who_calculator = WHO_ZScoreCalculator()
        self.is_trained = False
        
        # Initialize flexible treatment protocol system
        self.protocol_manager = TreatmentProtocolManager()
        self.protocol_manager.set_active_protocol(protocol_name)
        
        print(f"Initialized with protocol: {protocol_name}")
        print(f"Available protocols: {self.protocol_manager.get_available_protocols()}")
        self.feature_columns = []
        
    def preprocess_data(self, df):
        """
        Preprocess the input data
        """
        df_processed = df.copy()
        
        # Calculate BMI
        df_processed['bmi'] = df_processed.apply(
            lambda row: self.who_calculator.calculate_bmi(
                row['weight'], row['height']
            ), axis=1
        )
        
        # Calculate BMI status
        df_processed['bmi_status'] = df_processed.apply(
            lambda row: self.who_calculator.classify_bmi_status(
                row['bmi'], row['age_months']
            ), axis=1
        )
        
        # Calculate BMI
        df_processed['bmi'] = df_processed['weight'] / ((df_processed['height']/100) ** 2)
        
        # Create age groups
        df_processed['age_group'] = pd.cut(
            df_processed['age_months'], 
            bins=[0, 6, 12, 24, 36, 48, 60], 
            labels=['0-6m', '6-12m', '12-24m', '24-36m', '36-48m', '48-60m']
        )
        
        # Encode categorical variables
        categorical_columns = ['sex', 'municipality', '4ps_beneficiary', 'breastfeeding', 
                             'tuberculosis', 'malaria', 'congenital_anomalies', 'other_medical_problems',
                             'age_group']
        
        for col in categorical_columns:
            if col in df_processed.columns:
                if col not in self.label_encoders:
                    self.label_encoders[col] = LabelEncoder()
                    df_processed[col] = self.label_encoders[col].fit_transform(df_processed[col].astype(str))
                else:
                    # Handle new categories during prediction
                    unique_values = set(df_processed[col].astype(str))
                    known_values = set(self.label_encoders[col].classes_)
                    new_values = unique_values - known_values
                    
                    if new_values:
                        # Add new values to encoder
                        all_values = list(known_values) + list(new_values)
                        self.label_encoders[col].classes_ = np.array(all_values)
                    
                    df_processed[col] = self.label_encoders[col].transform(df_processed[col].astype(str))
        
        return df_processed
    
    def create_target_variable(self, df):
        """
        Create target variable based on WHZ score and clinical assessment
        Following the flowchart logic
        """
        def classify_status(row):
            whz = row['whz_score']
            edema = row.get('edema', False)
            
            # Following the flowchart logic
            if edema or whz < -3:
                return "Severe Acute Malnutrition (SAM)"
            elif whz >= -3 and whz < -2:
                return "Moderate Acute Malnutrition (MAM)"
            else:
                return "Normal"
        
        return df.apply(classify_status, axis=1)
    
    def train_model(self, df):
        """
        Train the Random Forest model
        """
        # Preprocess data
        df_processed = self.preprocess_data(df)
        
        # Create target variable
        y = self.create_target_variable(df_processed)
        
        # Select features for training
        feature_columns = ['age_months', 'weight', 'height', 'bmi', 'whz_score',
                          'total_household', 'adults', 'children', 'twins',
                          'sex', '4ps_beneficiary', 'breastfeeding',
                          'tuberculosis', 'malaria', 'congenital_anomalies',
                          'other_medical_problems', 'age_group']
        
        # Filter available columns
        available_columns = [col for col in feature_columns if col in df_processed.columns]
        X = df_processed[available_columns]
        
        self.feature_columns = available_columns
        
        # Split data
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42, stratify=y
        )
        
        # Scale features
        X_train_scaled = self.scaler.fit_transform(X_train)
        X_test_scaled = self.scaler.transform(X_test)
        
        # Train model
        self.model.fit(X_train_scaled, y_train)
        
        # Evaluate model
        y_pred = self.model.predict(X_test_scaled)
        
        print("Model Performance:")
        print(f"Accuracy: {accuracy_score(y_test, y_pred):.3f}")
        print("\nClassification Report:")
        print(classification_report(y_test, y_pred))
        
        # Cross-validation
        cv_scores = cross_val_score(self.model, X_train_scaled, y_train, cv=5)
        print(f"\nCross-validation scores: {cv_scores}")
        print(f"Average CV score: {cv_scores.mean():.3f} (+/- {cv_scores.std() * 2:.3f})")
        
        return X_test, y_test, y_pred
    
    def predict_single(self, patient_data):
        """
        Predict malnutrition status for a single patient
        """
        # Convert to DataFrame
        df = pd.DataFrame([patient_data])
        
        # Preprocess
        df_processed = self.preprocess_data(df)
        
        # Select features
        X = df_processed[self.feature_columns]
        
        # Scale
        X_scaled = self.scaler.transform(X)
        
        # Predict
        prediction = self.model.predict(X_scaled)[0]
        probability = self.model.predict_proba(X_scaled)[0]
        
        # Get class probabilities
        classes = self.model.classes_
        prob_dict = dict(zip(classes, probability))
        
        return {
            'prediction': prediction,
            'bmi': df_processed['bmi'].iloc[0],
            'bmi_status': df_processed['bmi_status'].iloc[0],
            'probabilities': prob_dict,
            'recommendation': self.get_treatment_recommendation(prediction, df_processed.iloc[0])
        }
    
    def predict_batch(self, df):
        """
        Predict malnutrition status for multiple patients
        """
        results = []
        
        for index, row in df.iterrows():
            try:
                # Convert row to dict
                patient_data = row.to_dict()
                
                # Get prediction for single patient
                result = self.predict_single(patient_data)
                
                # Add patient info to result
                batch_result = {
                    'name': patient_data.get('name', f'Patient_{index+1}'),
                    'age_months': patient_data.get('age_months', 0),
                    'weight': patient_data.get('weight', 0),
                    'height': patient_data.get('height', 0),
                    'whz_score': result['whz_score'],
                    'prediction': result['prediction'],
                    'treatment': result['recommendation']['treatment'],
                    'bmi': patient_data['weight'] / ((patient_data['height']/100) ** 2) if patient_data.get('weight') and patient_data.get('height') else 0
                }
                
                results.append(batch_result)
                
            except Exception as e:
                # Handle errors gracefully
                error_result = {
                    'name': patient_data.get('name', f'Patient_{index+1}'),
                    'age_months': 'Error',
                    'weight': 'Error',
                    'height': 'Error',
                    'whz_score': 'Error',
                    'prediction': f'Error: {str(e)}',
                    'treatment': 'Unable to determine',
                    'bmi': 'Error'
                }
                results.append(error_result)
        
        return pd.DataFrame(results)
    
    def enhanced_assessment(self, weight, height, age_months, sex, has_edema=False):
        """
        Perform enhanced nutritional assessment with confidence scoring
        Combines WHO Z-scores, BMI analysis, and confidence metrics
        
        Args:
            weight: Weight in kg
            height: Height in cm  
            age_months: Age in months
            sex: 'male' or 'female'
            has_edema: Boolean indicating presence of edema
            
        Returns:
            Dictionary with comprehensive assessment results and confidence score
        """
        try:
            # Get comprehensive assessment from WHO calculator
            result = self.who_calculator.comprehensive_assessment(
                weight, height, age_months, sex, has_edema
            )
            
            if result is None:
                return {
                    'error': 'Assessment failed',
                    'confidence': {'overall_confidence': 0, 'confidence_level': 'Very Low'}
                }
            
            # Add model prediction if data is suitable for ML prediction
            try:
                # Create prediction data
                prediction_data = {
                    'age_months': age_months,
                    'weight': weight,
                    'height': height,
                    'sex': sex
                }
                
                # Get ML model prediction
                ml_prediction = self.predict_single(prediction_data)
                result['ml_prediction'] = ml_prediction
                
            except Exception as e:
                result['ml_prediction'] = {
                    'error': f'ML prediction failed: {str(e)}',
                    'prediction': 'Unknown'
                }
            
            # Add interpretation
            confidence_level = result['confidence']['confidence_level']
            nutritional_status = result['classifications']['nutritional_status']
            
            result['interpretation'] = {
                'reliability': confidence_level,
                'recommendation': self._get_assessment_recommendation(nutritional_status, confidence_level),
                'follow_up': self._get_follow_up_recommendation(nutritional_status, confidence_level)
            }
            
            return result
            
        except Exception as e:
            return {
                'error': f'Enhanced assessment failed: {str(e)}',
                'confidence': {'overall_confidence': 0, 'confidence_level': 'Very Low'}
            }
    
    def _get_assessment_recommendation(self, status, confidence_level):
        """Get assessment-based recommendations"""
        if confidence_level in ['Very Low', 'Low']:
            return "Re-assess with more accurate measurements before making clinical decisions"
        elif status == "Severe Acute Malnutrition (SAM)":
            return "Immediate medical intervention required"
        elif status == "Moderate Acute Malnutrition (MAM)":
            return "Nutritional support and monitoring needed"
        elif status == "At Risk":
            return "Monitor closely and provide nutritional counseling"
        else:
            return "Continue regular monitoring and healthy feeding practices"
    
    def _get_follow_up_recommendation(self, status, confidence_level):
        """Get follow-up recommendations"""
        if confidence_level in ['Very Low', 'Low']:
            return "Immediate re-assessment with verified measurements"
        elif status in ["Severe Acute Malnutrition (SAM)", "Moderate Acute Malnutrition (MAM)"]:
            return "Weekly monitoring until improvement observed"
        elif status == "At Risk":
            return "Monthly monitoring for 3 months"
        else:
            return "Routine growth monitoring as per schedule"

    def get_treatment_recommendation(self, status, patient_data, protocol_name=None):
        """
        Get treatment recommendation using flexible protocol system
        
        Args:
            status: Malnutrition status
            patient_data: Patient data dictionary
            protocol_name: Optional protocol to use (uses active if None)
        
        Returns:
            Treatment recommendation dictionary
        """
        return self.protocol_manager.get_treatment_recommendation(status, patient_data, protocol_name)
    
    def set_treatment_protocol(self, protocol_name):
        """
        Change the active treatment protocol
        
        Args:
            protocol_name: Name of the protocol to activate
        
        Returns:
            bool: True if successful
        """
        return self.protocol_manager.set_active_protocol(protocol_name)
    
    def get_available_protocols(self):
        """Get list of available treatment protocols"""
        return self.protocol_manager.get_available_protocols()
    
    def get_protocol_info(self, protocol_name=None):
        """Get information about a protocol"""
        return self.protocol_manager.get_protocol_info(protocol_name)
    
    def plot_feature_importance(self):
        """
        Plot feature importance
        """
        if hasattr(self.model, 'feature_importances_'):
            importance_df = pd.DataFrame({
                'feature': self.feature_columns,
                'importance': self.model.feature_importances_
            }).sort_values('importance', ascending=False)
            
            plt.figure(figsize=(10, 6))
            sns.barplot(data=importance_df, x='importance', y='feature')
            plt.title('Feature Importance in Random Forest Model')
            plt.xlabel('Importance')
            plt.tight_layout()
            plt.show()
            
            return importance_df
    
    def save_model(self, filepath):
        """
        Save the trained model
        """
        model_data = {
            'model': self.model,
            'label_encoders': self.label_encoders,
            'scaler': self.scaler,
            'feature_columns': self.feature_columns,
            'who_calculator': self.who_calculator
        }
        joblib.dump(model_data, filepath)
        print(f"Model saved to {filepath}")
    
    def load_model(self, filepath):
        """
        Load a trained model
        """
        model_data = joblib.load(filepath)
        self.model = model_data['model']
        self.label_encoders = model_data['label_encoders']
        self.scaler = model_data['scaler']
        self.feature_columns = model_data['feature_columns']
        self.who_calculator = model_data['who_calculator']
        print(f"Model loaded from {filepath}")

def generate_sample_data(n_samples=1000):
    """
    Generate sample data for demonstration
    """
    np.random.seed(42)
    
    municipalities = ['Manila', 'Quezon City', 'Caloocan', 'Davao', 'Cebu', 'Zamboanga']
    
    data = []
    for i in range(n_samples):
        age_months = np.random.randint(0, 61)
        sex = np.random.choice(['Male', 'Female'])
        
        # Generate realistic height and weight based on age
        if age_months <= 6:
            height = np.random.normal(55 + age_months * 2, 3)
            base_weight = 3 + age_months * 0.6
        elif age_months <= 24:
            height = np.random.normal(65 + (age_months - 6) * 1.2, 4)
            base_weight = 7 + (age_months - 6) * 0.3
        else:
            height = np.random.normal(80 + (age_months - 24) * 0.8, 5)
            base_weight = 12 + (age_months - 24) * 0.2
        
        # Add some variation for malnutrition cases
        malnutrition_factor = np.random.choice([0.7, 0.8, 0.9, 1.0, 1.1], p=[0.1, 0.15, 0.25, 0.4, 0.1])
        weight = max(1, base_weight * malnutrition_factor + np.random.normal(0, 0.5))
        
        record = {
            'name': f'Child_{i+1}',
            'municipality': np.random.choice(municipalities),
            'number': f'ID_{i+1:04d}',
            'age_months': age_months,
            'sex': sex,
            'date_of_admission': pd.Timestamp.now() - pd.Timedelta(days=np.random.randint(0, 365)),
            'total_household': np.random.randint(3, 12),
            'adults': np.random.randint(2, 6),
            'children': np.random.randint(1, 6),
            'twins': np.random.choice([0, 1], p=[0.97, 0.03]),
            '4ps_beneficiary': np.random.choice(['Yes', 'No'], p=[0.6, 0.4]),
            'weight': round(weight, 1),
            'height': round(height, 1),
            'breastfeeding': np.random.choice(['Yes', 'No'], p=[0.7, 0.3]) if age_months <= 24 else 'No',
            'tuberculosis': np.random.choice(['Yes', 'No'], p=[0.05, 0.95]),
            'malaria': np.random.choice(['Yes', 'No'], p=[0.08, 0.92]),
            'congenital_anomalies': np.random.choice(['Yes', 'No'], p=[0.03, 0.97]),
            'other_medical_problems': np.random.choice(['Yes', 'No'], p=[0.1, 0.9]),
            'edema': np.random.choice([True, False], p=[0.02, 0.98])
        }
        data.append(record)
    
    return pd.DataFrame(data)

if __name__ == "__main__":
    # Generate sample data
    print("Generating sample data...")
    df = generate_sample_data(1000)
    
    # Initialize and train model
    print("Training Random Forest model...")
    model = MalnutritionRandomForestModel()
    X_test, y_test, y_pred = model.train_model(df)
    
    # Show feature importance
    print("\nFeature Importance:")
    importance_df = model.plot_feature_importance()
    print(importance_df)
    
    # Example prediction
    print("\nExample prediction:")
    sample_patient = {
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
        'weight': 7.5,
        'height': 75.0,
        'breastfeeding': 'No',
        'tuberculosis': 'No',
        'malaria': 'No',
        'congenital_anomalies': 'No',
        'other_medical_problems': 'No',
        'edema': False
    }
    
    result = model.predict_single(sample_patient)
    print(f"Prediction: {result['prediction']}")
    print(f"WHZ Score: {result['whz_score']}")
    print(f"Probabilities: {result['probabilities']}")
    print(f"Treatment Recommendation: {result['recommendation']}")
    
    # Save model
    model.save_model('malnutrition_model.pkl')
    print("\nModel training completed successfully!")
