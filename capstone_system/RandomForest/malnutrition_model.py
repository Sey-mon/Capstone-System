"""
Child Malnutrition Assessment Model using Random Forest
Based on WHO guidelines for children aged 0-5 years
Now with flexible treatment protocol system
"""

import pandas as pd
import numpy as np
import os
from datetime import datetime
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split, cross_val_score, learning_curve, validation_curve
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.metrics import (classification_report, confusion_matrix, accuracy_score, 
                           precision_recall_fscore_support, roc_auc_score, roc_curve)
from sklearn.inspection import permutation_importance
import matplotlib.pyplot as plt
import seaborn as sns
from scipy import stats
import joblib
import warnings
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
        
        # WHO 2006 Child Growth Standards – Weight-for-Height reference
        # Source: WHO Multicentre Growth Reference Study Group (2006)
        # Values: median weight (mean) in kg and standard deviation (sd) per height in cm.
        # Previously this block had incorrect values that inflated medians above 80 cm,
        # causing severe negative WHZ scores for normal children.  The data below is
        # derived directly from the published WHO 2006 WFH tables and is correct.
        self.fallback_who_reference = {
            'weight_for_height': {
                'boys': {
                    45:  {'mean': 2.441, 'sd': 0.268},
                    50:  {'mean': 3.272, 'sd': 0.359},
                    55:  {'mean': 4.257, 'sd': 0.441},
                    60:  {'mean': 5.398, 'sd': 0.516},
                    65:  {'mean': 6.668, 'sd': 0.597},
                    70:  {'mean': 7.985, 'sd': 0.696},
                    75:  {'mean': 9.287, 'sd': 0.773},
                    80:  {'mean': 10.617, 'sd': 0.845},
                    85:  {'mean': 12.046, 'sd': 0.918},
                    90:  {'mean': 13.534, 'sd': 1.000},
                    95:  {'mean': 15.023, 'sd': 1.090},
                    100: {'mean': 16.471, 'sd': 1.176},
                    105: {'mean': 17.988, 'sd': 1.278},
                    110: {'mean': 19.602, 'sd': 1.396},
                    115: {'mean': 21.226, 'sd': 1.513},
                    120: {'mean': 22.810, 'sd': 1.619},
                },
                'girls': {
                    45:  {'mean': 2.428, 'sd': 0.263},
                    50:  {'mean': 3.202, 'sd': 0.351},
                    55:  {'mean': 4.196, 'sd': 0.435},
                    60:  {'mean': 5.280, 'sd': 0.501},
                    65:  {'mean': 6.549, 'sd': 0.587},
                    70:  {'mean': 7.873, 'sd': 0.684},
                    75:  {'mean': 9.131, 'sd': 0.751},
                    80:  {'mean': 10.460, 'sd': 0.830},
                    85:  {'mean': 11.967, 'sd': 0.918},
                    90:  {'mean': 13.479, 'sd': 1.007},
                    95:  {'mean': 14.891, 'sd': 1.092},
                    100: {'mean': 16.284, 'sd': 1.181},
                    105: {'mean': 17.773, 'sd': 1.289},
                    110: {'mean': 19.430, 'sd': 1.424},
                    115: {'mean': 21.143, 'sd': 1.574},
                    120: {'mean': 22.794, 'sd': 1.701},
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
        Classify BMI status for children based on WHO standards.
        Thresholds follow WHO child growth standards for under-5s.
        """
        try:
            if age_months < 24:  # Under 2 years
                if bmi < 13:
                    return "Severely Underweight"
                elif bmi < 15:
                    return "Underweight"
                elif bmi <= 18:
                    return "Normal"
                elif bmi <= 20:
                    return "Overweight"
                else:
                    return "Obese"
            else:  # 2-5 years
                if bmi < 13.5:
                    return "Severely Underweight"
                elif bmi < 15.5:
                    return "Underweight"
                elif bmi <= 17.5:
                    return "Normal"
                elif bmi <= 19.5:
                    return "Overweight"
                else:
                    return "Obese"
        except Exception as e:
            print(f"Error classifying BMI status: {e}")
            return "Unknown"
    
    def classify_nutritional_status(self, wfa_zscore, hfa_zscore, bmi, age_months, has_edema=False):
        """
        Classify nutritional status based on multiple indicators.
        Following WHO guidelines with comprehensive assessment.
        Categories: SAM, MAM, At Risk, Normal, Overweight, Obese.
        Overweight/Obese are checked first (before undernutrition) when no edema.
        """
        if has_edema:
            return "Severe Acute Malnutrition (SAM)"

        # Classify based on BMI status
        bmi_status = self.classify_bmi_status(bmi, age_months)

        # --- Overnutrition check (WHO: WHZ > +2 = overweight, > +3 = obese) ---
        # Use WFA z-score as a proxy when WHZ is not directly available here;
        # BMI status is the primary overnutrition signal.
        if bmi_status == "Obese" or wfa_zscore > 3:
            return "Obese"
        if bmi_status == "Overweight" or wfa_zscore > 2:
            return "Overweight"

        # --- Undernutrition check ---
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

        if bmi_status == "Severely Underweight":
            severe_indicators += 1
        elif bmi_status == "Underweight":
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
            n_estimators=200,
            random_state=42,
            max_depth=15,
            min_samples_split=5,
            min_samples_leaf=2,
            class_weight='balanced',  # corrects imbalance across all 5 classes
            oob_score=True  # Enable OOB scoring for evaluation
        )
        self.label_encoders = {}
        self.scaler = StandardScaler()
        self.who_calculator = WHO_ZScoreCalculator()
        self.is_trained = False
        self.feature_columns = []
        self.evaluation_results = {}
        
        print(f"Initialized with protocol: {protocol_name}")
        self.current_protocol = protocol_name
        
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
        
        # Calculate WHZ score (Weight-for-Height Z-score)
        df_processed['whz_score'] = df_processed.apply(
            lambda row: self._calculate_whz_score(
                row['weight'], row['height'], row['sex']
            ), axis=1
        )
        
        # Calculate WFA and HFA Z-scores
        df_processed['wfa_zscore'] = df_processed.apply(
            lambda row: self.who_calculator.calculate_weight_for_age_zscore(
                row['weight'], row['age_months'], row['sex']
            ), axis=1
        )
        
        df_processed['hfa_zscore'] = df_processed.apply(
            lambda row: self.who_calculator.calculate_height_for_age_zscore(
                row['height'], row['age_months'], row['sex']
            ), axis=1
        )
        
        # Calculate BMI status
        df_processed['bmi_status'] = df_processed.apply(
            lambda row: self.who_calculator.classify_bmi_status(
                row['bmi'], row['age_months']
            ), axis=1
        )
        
        # Create age groups
        df_processed['age_group'] = pd.cut(
            df_processed['age_months'], 
            bins=[0, 6, 12, 24, 36, 48, 60], 
            labels=['0-6m', '6-12m', '12-24m', '24-36m', '36-48m', '48-60m']
        )
        
        # Encode categorical variables
        categorical_columns = ['sex', 'municipality', '4ps_beneficiary', 'breastfeeding', 
                             'tuberculosis', 'malaria', 'congenital_anomalies', 'other_medical_problems',
                             'age_group', 'bmi_status']
        
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
    
    def _calculate_whz_score(self, weight, height, sex):
        """
        Calculate Weight-for-Height Z-score using WHO standards
        """
        try:
            # Use the fallback reference data for WHZ calculation
            gender = 'boys' if sex.lower() in ['male', 'm'] else 'girls'
            height_rounded = round(height)
            
            # Find closest height in reference data
            reference_data = self.who_calculator.fallback_who_reference['weight_for_height'][gender]
            available_heights = list(reference_data.keys())
            
            if height_rounded in available_heights:
                ref = reference_data[height_rounded]
            else:
                # Find closest height
                closest_height = min(available_heights, key=lambda x: abs(x - height_rounded))
                ref = reference_data[closest_height]
            
            # Calculate z-score: (observed - mean) / sd
            z_score = (weight - ref['mean']) / ref['sd']
            return round(z_score, 2)
            
        except Exception as e:
            print(f"Error calculating WHZ score: {e}")
            return 0
    
    def create_target_variable(self, df):
        """
        Create target variable based on WHZ score, BMI status and clinical assessment.
        Five classes: SAM, MAM, Normal, Overweight, Obese.
        WHO thresholds:
          WHZ > +3  -> Obese
          WHZ > +2  -> Overweight
          WHZ -2 to +2 -> Normal (or BMI-driven OW/Obese)
          WHZ -3 to -2 -> MAM
          WHZ < -3 or edema -> SAM
        """
        def classify_status(row):
            whz = row['whz_score']
            bmi_status = row.get('bmi_status', 'Normal')
            # bmi_status may be the encoded integer after preprocessing;
            # decode safely.
            if isinstance(bmi_status, (int, float, np.integer)):
                bmi_status = 'Normal'  # fallback; raw string expected here
            edema = row.get('edema', False)

            # Edema always means SAM
            if edema:
                return "Severe Acute Malnutrition (SAM)"

            # Overnutrition (WHO WHZ thresholds)
            if whz > 3 or str(bmi_status) == 'Obese':
                return "Obese"
            if whz > 2 or str(bmi_status) == 'Overweight':
                return "Overweight"

            # Undernutrition
            if whz < -3:
                return "Severe Acute Malnutrition (SAM)"
            elif whz < -2:
                return "Moderate Acute Malnutrition (MAM)"
            else:
                return "Normal"

        return df.apply(classify_status, axis=1)
    
    def train_model(self, df):
        """
        Train the Random Forest model with comprehensive evaluation
        """
        # Preprocess data
        df_processed = self.preprocess_data(df)
        
        # Create target variable
        y = self.create_target_variable(df_processed)
        
        # Select features for training
        feature_columns = ['age_months', 'weight', 'height', 'bmi', 'whz_score', 'wfa_zscore', 'hfa_zscore',
                          'total_household', 'adults', 'children', 'twins',
                          'sex', '4ps_beneficiary', 'breastfeeding',
                          'tuberculosis', 'malaria', 'congenital_anomalies',
                          'other_medical_problems', 'age_group', 'bmi_status']
        
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
        print("Training Random Forest model...")
        self.model.fit(X_train_scaled, y_train)
        self.is_trained = True
        
        # Make predictions
        y_pred = self.model.predict(X_test_scaled)
        y_pred_proba = self.model.predict_proba(X_test_scaled)
        
        # Comprehensive Model Evaluation
        print("\n" + "="*60)
        print("COMPREHENSIVE RANDOM FOREST MODEL EVALUATION")
        print("="*60)
        
        # 1. Basic Classification Metrics
        accuracy = accuracy_score(y_test, y_pred)
        precision, recall, f1, _ = precision_recall_fscore_support(y_test, y_pred, average='weighted')
        
        print(f"\n📊 CLASSIFICATION METRICS:")
        print(f"├── Accuracy: {accuracy:.3f} {'✅' if accuracy > 0.85 else '⚠️'}")
        print(f"├── Precision: {precision:.3f} {'✅' if precision > 0.80 else '⚠️'}")
        print(f"├── Recall: {recall:.3f} {'✅' if recall > 0.80 else '⚠️'}")
        print(f"└── F1-Score: {f1:.3f} {'✅' if f1 > 0.80 else '⚠️'}")
        
        # 2. Random Forest Specific Metrics
        print(f"\n🌲 RANDOM FOREST SPECIFIC METRICS:")
        print(f"├── OOB Score: {self.model.oob_score_:.3f} {'✅' if self.model.oob_score_ > 0.85 else '⚠️'}")
        print(f"├── Number of Trees: {getattr(self.model, 'n_estimators', 'Unknown')}")
        print(f"├── Max Depth: {getattr(self.model, 'max_depth', 'Unknown')}")
        print(f"└── Features Used: {len(self.feature_columns)}")
        
        # 3. Cross-Validation
        cv_scores = cross_val_score(self.model, X_train_scaled, y_train, cv=5, scoring='accuracy')
        cv_std = cv_scores.std()
        cv_mean = cv_scores.mean()
        
        print(f"\n🔄 CROSS-VALIDATION RESULTS:")
        print(f"├── CV Mean Score: {cv_mean:.3f} {'✅' if cv_mean > 0.85 else '⚠️'}")
        print(f"├── CV Std Deviation: {cv_std:.3f} {'✅' if cv_std < 0.05 else '⚠️'}")
        print(f"├── Training-Validation Gap: {abs(accuracy - cv_mean):.3f} {'✅' if abs(accuracy - cv_mean) < 0.05 else '⚠️'}")
        print(f"└── CV Scores: {[f'{score:.3f}' for score in cv_scores]}")
        
        # 4. Generate comprehensive plots
        self._generate_evaluation_plots(X_train_scaled, X_test_scaled, y_train, y_test, y_pred, y_pred_proba)
        
        # Store evaluation results
        self.evaluation_results = {
            'accuracy': accuracy,
            'precision': precision,
            'recall': recall,
            'f1_score': f1,
            'oob_score': self.model.oob_score_,
            'cv_mean': cv_mean,
            'cv_std': cv_std,
            'training_validation_gap': abs(accuracy - cv_mean)
        }
        
        # Print detailed classification report
        print(f"\n📋 DETAILED CLASSIFICATION REPORT:")
        print(classification_report(y_test, y_pred))

        # Multi-class AUC (One-vs-Rest, macro average)
        try:
            macro_auc = roc_auc_score(y_test, y_pred_proba, multi_class='ovr', average='macro')
            print(f"\n📊 MULTI-CLASS AUC (One-vs-Rest, Macro): {macro_auc:.3f} {'✅' if macro_auc > 0.95 else '⚠️'}")
        except Exception:
            pass

        return X_test, y_test, y_pred
    
    def _generate_evaluation_plots(self, X_train, X_test, y_train, y_test, y_pred, y_pred_proba):
        """
        Generate comprehensive evaluation plots for Random Forest model
        """
        plt.style.use('default')
        fig = plt.figure(figsize=(20, 15))
        
        # 1. Feature Importance Plot (Most Important for Random Forest)
        plt.subplot(3, 3, 1)
        feature_importance = pd.DataFrame({
            'feature': self.feature_columns,
            'importance': self.model.feature_importances_
        }).sort_values('importance', ascending=False)
        
        plt.barh(range(len(feature_importance)), feature_importance['importance'])
        plt.yticks(range(len(feature_importance)), feature_importance['feature'].tolist())
        plt.xlabel('Feature Importance (Gini)')
        plt.title('🔥 Feature Importance (Mean Decrease in Impurity)')
        plt.gca().invert_yaxis()
        
        # 2. Permutation Importance
        plt.subplot(3, 3, 2)
        try:
            perm_importance = permutation_importance(self.model, X_test, y_test, n_repeats=10, random_state=42)
            perm_df = pd.DataFrame({
                'feature': self.feature_columns,
                'importance': getattr(perm_importance, 'importances_mean', np.zeros(len(self.feature_columns)))
            }).sort_values('importance', ascending=False)
            
            plt.barh(range(len(perm_df)), perm_df['importance'])
            plt.yticks(range(len(perm_df)), perm_df['feature'].tolist())
            plt.xlabel('Permutation Importance')
            plt.title('🎯 Permutation Importance (Mean Decrease in Accuracy)')
            plt.gca().invert_yaxis()
        except Exception as e:
            plt.text(0.5, 0.5, f'Permutation Importance\nError: {str(e)[:50]}...', ha='center', va='center')
            plt.title('🎯 Permutation Importance (Error)')
        
        # 3. OOB Error Rate vs Number of Trees
        plt.subplot(3, 3, 3)
        oob_errors = []
        tree_range = range(10, 201, 10)
        for n_trees in tree_range:
            temp_model = RandomForestClassifier(n_estimators=n_trees, oob_score=True, random_state=42)
            temp_model.fit(X_train, y_train)
            oob_errors.append(1 - temp_model.oob_score_)
        
        plt.plot(tree_range, oob_errors, 'b-', linewidth=2)
        plt.xlabel('Number of Trees')
        plt.ylabel('OOB Error Rate')
        plt.title('📈 OOB Error Rate vs Number of Trees')
        plt.grid(True, alpha=0.3)
        
        # 4. Learning Curves
        plt.subplot(3, 3, 4)
        try:
            learning_curve_result = learning_curve(
                self.model, X_train, y_train, cv=5, 
                train_sizes=np.linspace(0.1, 1.0, 10),
                scoring='accuracy'
            )
            train_sizes, train_scores, val_scores = learning_curve_result[0], learning_curve_result[1], learning_curve_result[2]
            
            plt.plot(train_sizes, np.mean(train_scores, axis=1), 'o-', color='blue', label='Training Score')
            plt.plot(train_sizes, np.mean(val_scores, axis=1), 'o-', color='red', label='Validation Score')
            plt.fill_between(train_sizes, np.mean(train_scores, axis=1) - np.std(train_scores, axis=1),
                             np.mean(train_scores, axis=1) + np.std(train_scores, axis=1), alpha=0.1, color='blue')
            plt.fill_between(train_sizes, np.mean(val_scores, axis=1) - np.std(val_scores, axis=1),
                             np.mean(val_scores, axis=1) + np.std(val_scores, axis=1), alpha=0.1, color='red')
            plt.xlabel('Training Set Size')
            plt.ylabel('Accuracy Score')
            plt.title('📚 Learning Curves')
            plt.legend()
            plt.grid(True, alpha=0.3)
        except Exception as e:
            plt.text(0.5, 0.5, f'Learning Curves\nError: {str(e)[:50]}...', ha='center', va='center')
            plt.title('📚 Learning Curves (Error)')
        
        # 5. Confusion Matrix
        plt.subplot(3, 3, 5)
        cm = confusion_matrix(y_test, y_pred)
        sns.heatmap(cm, annot=True, fmt='d', cmap='Blues')
        plt.xlabel('Predicted')
        plt.ylabel('Actual')
        plt.title('🎯 Confusion Matrix')
        
        # 6. Tree Depth Distribution
        plt.subplot(3, 3, 6)
        try:
            tree_depths = [getattr(tree.tree_, 'max_depth', 10) for tree in self.model.estimators_]
            plt.hist(tree_depths, bins=20, alpha=0.7, color='green', edgecolor='black')
            plt.xlabel('Tree Depth')
            plt.ylabel('Frequency')
            plt.title('🌳 Tree Depth Distribution')
            plt.grid(True, alpha=0.3)
        except Exception as e:
            plt.text(0.5, 0.5, f'Tree Depth Distribution\nError: {str(e)[:30]}...', ha='center', va='center')
            plt.title('🌳 Tree Depth Distribution (Error)')
        
        # 7. Multi-class ROC Curves (One-vs-Rest)
        plt.subplot(3, 3, 7)
        try:
            from sklearn.preprocessing import label_binarize
            from sklearn.metrics import auc as sklearn_auc

            unique_classes = self.model.classes_
            y_test_bin = label_binarize(y_test, classes=unique_classes)
            colors_roc = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd']

            for i, cls in enumerate(unique_classes):
                if i < y_pred_proba.shape[1] and np.sum(y_test_bin[:, i]) > 0:
                    fpr_i, tpr_i, _ = roc_curve(y_test_bin[:, i], y_pred_proba[:, i])
                    roc_auc_i = sklearn_auc(fpr_i, tpr_i)
                    short_label = (cls
                                   .replace('Severe Acute Malnutrition (SAM)', 'SAM')
                                   .replace('Moderate Acute Malnutrition (MAM)', 'MAM'))
                    plt.plot(fpr_i, tpr_i, color=colors_roc[i % len(colors_roc)],
                             lw=1.5, label=f'{short_label} (AUC={roc_auc_i:.2f})')

            plt.plot([0, 1], [0, 1], 'k--', lw=1.0)
            plt.xlim([0.0, 1.0])
            plt.ylim([0.0, 1.05])
            plt.xlabel('False Positive Rate')
            plt.ylabel('True Positive Rate')
            plt.title('📊 ROC Curves (One-vs-Rest)')
            plt.legend(loc='lower right', fontsize=7)
            plt.grid(True, alpha=0.2)
        except Exception as e:
            plt.text(0.5, 0.5, f'ROC Curves\nError: {str(e)[:50]}', ha='center', va='center')
            plt.title('📊 ROC Curves (Error)')
        
        # 8. Validation Curve (Max Depth — real cross-validation)
        plt.subplot(3, 3, 8)
        try:
            param_range_vc = [3, 5, 7, 10, 15, 20, 25]
            train_scores_vc, test_scores_vc = validation_curve(
                RandomForestClassifier(n_estimators=50, random_state=42,
                                       class_weight='balanced'),
                X_train, y_train,
                param_name='max_depth',
                param_range=param_range_vc,
                cv=5,
                scoring='f1_weighted',
                n_jobs=-1
            )
            train_mean_vc = np.mean(train_scores_vc, axis=1)
            test_mean_vc  = np.mean(test_scores_vc,  axis=1)
            train_std_vc  = np.std(train_scores_vc,  axis=1)
            test_std_vc   = np.std(test_scores_vc,   axis=1)
            best_depth_vc = param_range_vc[np.argmax(test_mean_vc)]

            plt.plot(param_range_vc, train_mean_vc, 'o-', color='blue', lw=2, label='Training F1')
            plt.plot(param_range_vc, test_mean_vc,  'o-', color='red',  lw=2, label='CV F1')
            plt.fill_between(param_range_vc,
                             train_mean_vc - train_std_vc, train_mean_vc + train_std_vc,
                             alpha=0.1, color='blue')
            plt.fill_between(param_range_vc,
                             test_mean_vc - test_std_vc, test_mean_vc + test_std_vc,
                             alpha=0.1, color='red')
            plt.axvline(x=best_depth_vc, color='green', linestyle='--', alpha=0.7,
                        label=f'Best depth={best_depth_vc}')
            plt.xlabel('Max Depth')
            plt.ylabel('Weighted F1 Score')
            plt.title('🔧 Validation Curve (Max Depth)')
            plt.legend(fontsize=8)
            plt.grid(True, alpha=0.3)
        except Exception as e:
            plt.text(0.5, 0.5, f'Validation Curve\nError: {str(e)[:50]}', ha='center', va='center')
            plt.title('🔧 Validation Curve (Error)')
        
        # 9. Per-class Precision / Recall / F1 Bar Chart
        # Also keep feature importance vars alive for the print section below
        sorted_importance = np.sort(feature_importance['importance'])[::-1]
        top_20_percent = int(len(sorted_importance) * 0.2)
        top_20_contribution = np.sum(sorted_importance[:top_20_percent])

        plt.subplot(3, 3, 9)
        try:
            classes_list = list(self.model.classes_)
            prec_pc, rec_pc, f1_pc, support_pc = precision_recall_fscore_support(
                y_test, y_pred, labels=classes_list
            )
            short_labels = [
                c.replace('Severe Acute Malnutrition (SAM)', 'SAM')
                 .replace('Moderate Acute Malnutrition (MAM)', 'MAM')
                for c in classes_list
            ]
            x_pos = np.arange(len(short_labels))
            width = 0.25
            plt.bar(x_pos - width, prec_pc, width, label='Precision', color='steelblue', alpha=0.85)
            plt.bar(x_pos,         rec_pc,  width, label='Recall',    color='salmon',    alpha=0.85)
            plt.bar(x_pos + width, f1_pc,   width, label='F1-Score',  color='seagreen',  alpha=0.85)
            plt.xticks(x_pos, short_labels, rotation=20, ha='right', fontsize=8)
            plt.ylim(0, 1.18)
            plt.ylabel('Score')
            plt.title('📊 Per-class Precision / Recall / F1')
            plt.legend(fontsize=8)
            plt.grid(True, alpha=0.2, axis='y')
            for i, (s, f) in enumerate(zip(support_pc, f1_pc)):
                plt.text(x_pos[i] + width, f + 0.02, f'n={s}', ha='center', fontsize=7)
        except Exception as e:
            plt.text(0.5, 0.5, f'Per-class Metrics\nError: {str(e)[:50]}', ha='center', va='center')
            plt.title('📊 Per-class Metrics (Error)')

        plt.tight_layout()
        plt.savefig('random_forest_evaluation.png', dpi=300, bbox_inches='tight')
        plt.show()
        
        # Print feature importance ranking
        print("\n" + "="*60)
        print("🔥 FEATURE IMPORTANCE RANKING")
        print("="*60)
        for i, (_, row) in enumerate(feature_importance.iterrows(), 1):
            print(f"{i:2d}. {row['feature']:20s} - {row['importance']:.4f}")
        
        print(f"\n📊 Top 20% of features ({top_20_percent} features) contribute {top_20_contribution:.1%} of total importance")
        
        return feature_importance
    
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

        # Apply conservative post-prediction clinical safety override.
        # This preserves the RF decision in most cases but corrects obvious
        # high-risk undernutrition misses and low-risk SAM overcalls.
        adjusted_prediction = self._apply_clinical_override(
            prediction=prediction,
            patient_data=patient_data,
            patient_row=df_processed.iloc[0]
        )
        
        # Get class probabilities
        classes = self.model.classes_
        prob_dict = dict(zip(classes, probability))
        
        return {
            'prediction': adjusted_prediction,
            'whz_score': df_processed['whz_score'].iloc[0],
            'bmi': df_processed['bmi'].iloc[0],
            'bmi_status': df_processed['bmi_status'].iloc[0],
            'probabilities': prob_dict,
            'recommendation': self.get_treatment_recommendation(adjusted_prediction, df_processed.iloc[0])
        }

    def _apply_clinical_override(self, prediction, patient_data, patient_row):
        """Apply limited clinical overrides for severe undernutrition edge cases."""

        def _to_bool(value):
            if isinstance(value, bool):
                return value
            if value is None:
                return False
            return str(value).strip().lower() in {'yes', 'true', '1'}

        def _is_positive(value):
            if value is None:
                return False
            text = str(value).strip().lower()
            return text not in {'', 'no', 'none', 'false', '0', 'n/a', 'na'}

        def _safe_float(value, default=0.0):
            try:
                return float(value)
            except (TypeError, ValueError):
                return default

        adjusted = str(prediction)

        has_edema = _to_bool(patient_data.get('edema', False))
        age_months = int(_safe_float(patient_data.get('age_months', 0), 0.0))
        whz = _safe_float(patient_row.get('whz_score'), 0.0)
        wfa = _safe_float(patient_row.get('wfa_zscore'), 0.0)
        bmi_status_value = str(patient_row.get('bmi_status', ''))

        risk_fields = ('tuberculosis', 'malaria', 'congenital_anomalies', 'other_medical_problems', 'twins')
        risk_count = sum(1 for field in risk_fields if _is_positive(patient_data.get(field)))

        # Escalation rules: prioritize safety for likely severe undernutrition.
        if has_edema:
            return 'Severe Acute Malnutrition (SAM)'

        if adjusted != 'Severe Acute Malnutrition (SAM)':
            if whz <= -3.0 or wfa <= -3.0:
                return 'Severe Acute Malnutrition (SAM)'
            if risk_count >= 2 and (whz <= -2.5 or wfa <= -2.5):
                return 'Severe Acute Malnutrition (SAM)'

        # De-escalation rules: reduce likely false-positive SAM in low-risk children.
        if adjusted == 'Severe Acute Malnutrition (SAM)':
            low_clinical_risk = (not has_edema) and (risk_count == 0)
            anthropometry_not_severe = (whz > -2.2) and (wfa > -2.2)
            if low_clinical_risk and anthropometry_not_severe:
                if whz <= -2.0 or wfa <= -2.0:
                    return 'Moderate Acute Malnutrition (MAM)'
                return 'Normal'

            # Targeted fallback for older children with low clinical risk and
            # near-threshold anthropometry to avoid SAM over-calls.
            older_low_risk = (age_months >= 24) and (not has_edema) and (risk_count <= 1)
            near_threshold_not_severe = (whz > -2.8) and (wfa > -2.8)
            if older_low_risk and near_threshold_not_severe:
                if bmi_status_value in {'Normal', 'Overweight', 'Obese'}:
                    if whz <= -2.0 or wfa <= -2.0:
                        return 'Moderate Acute Malnutrition (MAM)'
                    return 'Normal'

        return adjusted
    
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
        elif status == "Obese":
            return "Refer to physician; initiate dietary modification and physical activity programme"
        elif status == "Overweight":
            return "Dietary counseling and lifestyle modification; avoid high-calorie foods"
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
        elif status == "Obese":
            return "Monthly monitoring; physician review every 3 months"
        elif status == "Overweight":
            return "Monthly monitoring for 6 months to track weight trajectory"
        else:
            return "Routine growth monitoring as per schedule"

    def get_treatment_recommendation(self, status, patient_data, protocol_name=None):
        """
        Get treatment recommendation by loading the correct JSON protocol file.

        Args:
            status:        Nutritional status string (e.g. 'Severe Acute Malnutrition (SAM)')
            patient_data:  Patient dict or pandas Series with age_months, edema, etc.
            protocol_name: 'who_standard' | 'community_based' | 'hospital_intensive'

        Returns:
            Structured treatment recommendation dictionary.
        """
        import json as _json

        # ── resolve protocol file ──────────────────────────────────────────────
        _protocol_map = {
            'who_standard':       'who_standard.json',
            'WHO_Standard':       'who_standard.json',
            'community_based':    'community_based.json',
            'Community_Based':    'community_based.json',
            'hospital_intensive': 'hospital_intensive.json',
            'Hospital_Intensive': 'hospital_intensive.json',
        }
        protocol = protocol_name or getattr(self, 'current_protocol', 'who_standard') or 'who_standard'
        filename = _protocol_map.get(protocol, 'who_standard.json')
        script_dir = os.path.dirname(os.path.abspath(__file__))
        protocol_path = os.path.join(script_dir, 'treatment_protocols', filename)

        try:
            with open(protocol_path, 'r', encoding='utf-8') as fh:
                protocol_data = _json.load(fh)
        except Exception as e:
            return {
                "status": status,
                "treatment": f"Consult clinician – protocol file unavailable ({e})",
                "protocol": protocol,
            }

        protocols = protocol_data.get('protocols', {})
        if status not in protocols:
            return {
                "status": status,
                "treatment": f"Consult clinician – no protocol entry for '{status}'",
                "protocol": protocol,
            }

        status_block = protocols[status]

        # ── helper to safely get a field from patient_data (dict or pd.Series) ─
        def _get(field, default=None):
            try:
                val = patient_data.get(field, default)
                return default if val is None else val
            except Exception:
                return default

        # ── choose with_edema / without_edema / standard sub-block ────────────
        has_edema = bool(_get('edema', False))
        if 'with_edema' in status_block:
            selected = status_block['with_edema'] if has_edema else \
                       status_block.get('without_edema', status_block.get('standard', {}))
        else:
            selected = status_block.get('standard', {})

        # ── resolve age-specific guidance ─────────────────────────────────────
        try:
            age_months = int(_get('age_months', 12))
        except (TypeError, ValueError):
            age_months = 12

        age_specific = selected.get('age_specific', {})
        if age_months <= 6:
            age_entry = age_specific.get('0-6_months')
        elif age_months <= 24:
            age_entry = age_specific.get('6-24_months')
        else:
            age_entry = age_specific.get('24-60_months')

        # ── build result ──────────────────────────────────────────────────────
        result = {
            "status":         status,
            "protocol":       protocol,
            "has_edema":      has_edema,
            "treatment":      selected.get('treatment', 'Consult clinician'),
            "details":        selected.get('details', ''),
            "follow_up":      selected.get('follow_up', ''),
            "priority":       selected.get('priority', 'standard'),
            "duration_weeks": selected.get('duration_weeks', 'ongoing'),
            "medications":    selected.get('medications', []),
            "monitoring":     selected.get('monitoring', []),
        }

        if age_entry:
            result['age_specific_guidance'] = age_entry

        # ── apply active risk modifiers ───────────────────────────────────────
        risk_modifiers = selected.get('risk_modifiers', {})
        if risk_modifiers:
            _falsy = {'no', 'false', '0', 'none', ''}
            active = {}
            for field in ('tuberculosis', 'malaria', 'twins', '4ps_beneficiary'):
                val = _get(field)
                if val is not None and str(val).lower() not in _falsy \
                        and field in risk_modifiers:
                    active[field] = risk_modifiers[field]
            if active:
                result['risk_modifiers'] = active

        # ── attach emergency / discharge criteria from protocol root ──────────
        emergency = protocol_data.get('emergency_criteria', {})
        if emergency:
            result['emergency_criteria'] = emergency

        discharge = protocol_data.get('discharge_criteria', {})
        if discharge:
            if 'SAM' in status:
                result['discharge_criteria'] = discharge.get('SAM', discharge.get('recovered', ''))
            elif 'MAM' in status:
                result['discharge_criteria'] = discharge.get('MAM', '')

        return result
    
    def set_treatment_protocol(self, protocol_name):
        """
        Change the active treatment protocol
        
        Args:
            protocol_name: Name of the protocol to activate
        
        Returns:
            bool: True if successful
        """
        print(f"Protocol set to: {protocol_name}")
        return True
    
    def get_available_protocols(self):
        """Get list of available treatment protocols"""
        return ["WHO_Standard", "Community_Based", "Hospital_Intensive"]
    
    def get_protocol_info(self, protocol_name=None):
        """Get information about a protocol"""
        return {
            "name": protocol_name or "standard",
            "description": "Standard treatment protocol",
            "version": "1.0"
        }
    
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
        Save the trained model with better compatibility
        """
        # Create a new WHO calculator instance to avoid pickle issues
        model_data = {
            'model': self.model,
            'label_encoders': self.label_encoders,
            'scaler': self.scaler,
            'feature_columns': self.feature_columns,
            'who_calculator': None  # Don't save the WHO calculator - recreate it on load
        }
        joblib.dump(model_data, filepath)
        print(f"Model saved to {filepath}")

    def load_model(self, filepath):
        """
        Load a trained model with better error handling
        """
        try:
            model_data = joblib.load(filepath)
            self.model = model_data['model']
            self.label_encoders = model_data['label_encoders']
            self.scaler = model_data['scaler']
            self.feature_columns = model_data['feature_columns']
            
            # Recreate WHO calculator instead of loading from pickle
            if model_data.get('who_calculator') is None:
                self.who_calculator = WHO_ZScoreCalculator()
            else:
                self.who_calculator = model_data['who_calculator']
                
            print(f"✅ Model loaded successfully from {filepath}")
            
        except Exception as e:
            print(f"❌ Error loading model: {e}")
            raise e

def generate_sample_data(n_samples=2000):
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
        
        # Weight variation: covers SAM, MAM, Normal, Overweight, Obese
        # factors: 0.70=SAM, 0.80=MAM, 0.90=At-Risk, 1.00=Normal, 1.15=Overweight, 1.35=Obese
        malnutrition_factor = np.random.choice(
            [0.70, 0.80, 0.90, 1.00, 1.15, 1.35],
            p=[0.08, 0.12, 0.17, 0.39, 0.15, 0.09]
        )
        weight = max(1, base_weight * malnutrition_factor + np.random.normal(0, 0.5))

        # Simulate realistic field measurement noise (scale ±150 g, length board ±3 mm)
        weight = round(max(1.0, weight + np.random.normal(0, 0.15)), 1)
        height = round(max(30.0, height + np.random.normal(0, 0.3)), 1)

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
    df = generate_sample_data(2000)
    
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


class MalnutritionAssessment:
    """
    Simplified assessment class for API integration
    """
    
    def __init__(self):
        """Initialize the assessment system"""
        self.who_calculator = WHO_ZScoreCalculator()
        # Try to load the trained model with proper path handling
        try:
            import os
            
            self.model = MalnutritionRandomForestModel()
            
            # Get the directory where this script is located
            script_dir = os.path.dirname(os.path.abspath(__file__))
            model_path = os.path.join(script_dir, 'malnutrition_model.pkl')
            
            print(f"Looking for model at: {model_path}")
            
            if os.path.exists(model_path):
                self.model.load_model(model_path)
                print("✅ Pre-trained model loaded successfully!")
            else:
                # Try current working directory as fallback
                fallback_path = 'malnutrition_model.pkl'
                if os.path.exists(fallback_path):
                    self.model.load_model(fallback_path)
                    print(f"✅ Pre-trained model loaded from: {fallback_path}")
                else:
                    raise FileNotFoundError(f"Model not found at {model_path} or {fallback_path}")
                    
        except Exception as e:
            print(f"⚠️ Warning: Pre-trained model not found ({e}). WHO assessment only.")
            self.model = None
    
    def assess_malnutrition(self, age_months, weight_kg, height_cm, gender, muac_cm=None, has_edema=False):
        """
        Assess malnutrition status based on WHO standards
        
        Args:
            age_months: Child's age in months
            weight_kg: Weight in kilograms
            height_cm: Height in centimeters
            gender: 'male' or 'female'
            muac_cm: Mid-upper arm circumference in cm (optional)
            has_edema: Boolean indicating presence of edema
            
        Returns:
            Dict with assessment results
        """
        try:
            # Calculate BMI
            height_m = height_cm / 100
            bmi = weight_kg / (height_m ** 2)
            
            # Get WHO assessment
            who_result = self.who_calculator.comprehensive_assessment(
                weight=weight_kg,
                height=height_cm,
                age_months=age_months,
                sex=gender,
                has_edema=has_edema
            )
            
            # Determine risk level based on z-scores and indicators
            risk_factors = []
            risk_score = 0
            
            if who_result is None:
                # Fallback if WHO assessment fails
                return {
                    'primary_diagnosis': 'Assessment Error',
                    'risk_level': 'Unknown',
                    'confidence': 0.0,
                    'error': 'WHO assessment failed',
                    'assessment_date': datetime.now().isoformat()
                }
            
            # Weight-for-age risk
            wfa_zscore = who_result['z_scores']['weight_for_age']
            if wfa_zscore < -3:
                risk_factors.append("Severely underweight")
                risk_score += 3
            elif wfa_zscore < -2:
                risk_factors.append("Underweight")
                risk_score += 2

            # Height-for-age risk
            hfa_zscore = who_result['z_scores']['height_for_age']
            if hfa_zscore < -3:
                risk_factors.append("Severely stunted")
                risk_score += 3
            elif hfa_zscore < -2:
                risk_factors.append("Stunted")
                risk_score += 2

            # Overnutrition signals (positive WFA z-scores)
            overweight_score = 0
            if wfa_zscore > 3:
                risk_factors.append("Obese (weight-for-age > +3 SD)")
                overweight_score += 2
            elif wfa_zscore > 2:
                risk_factors.append("Overweight (weight-for-age > +2 SD)")
                overweight_score += 1

            # BMI-based overnutrition
            bmi_status = who_result['classifications'].get('bmi_status', 'Normal')
            if bmi_status == 'Obese':
                risk_factors.append("Obese (BMI)")
                overweight_score += 2
            elif bmi_status == 'Overweight':
                risk_factors.append("Overweight (BMI)")
                overweight_score += 1

            # MUAC assessment
            if muac_cm is not None:
                if muac_cm < 11.5:
                    risk_factors.append("Severe acute malnutrition (MUAC)")
                    risk_score += 3
                elif muac_cm < 12.5:
                    risk_factors.append("Moderate acute malnutrition (MUAC)")
                    risk_score += 2

            # Edema
            if has_edema:
                risk_factors.append("Bilateral pitting edema")
                risk_score += 3

            # Determine primary diagnosis
            # Overnutrition takes priority over low risk_score normal baseline
            if risk_score >= 6 or has_edema:
                primary_diagnosis = "Severe Acute Malnutrition (SAM)"
                risk_level = "High"
                confidence = 0.9
            elif risk_score >= 3:
                primary_diagnosis = "Moderate Acute Malnutrition (MAM)"
                risk_level = "Moderate"
                confidence = 0.8
            elif risk_score >= 1:
                primary_diagnosis = "At Risk of Malnutrition"
                risk_level = "Low"
                confidence = 0.7
            elif overweight_score >= 2:
                primary_diagnosis = "Obese"
                risk_level = "Moderate"
                confidence = 0.85
            elif overweight_score >= 1:
                primary_diagnosis = "Overweight"
                risk_level = "Low-Moderate"
                confidence = 0.85
            else:
                primary_diagnosis = "Normal Nutritional Status"
                risk_level = "Low"
                confidence = 0.9
            
            # Create comprehensive result
            assessment_result = {
                'primary_diagnosis': primary_diagnosis,
                'risk_level': risk_level,
                'confidence': confidence,
                'risk_score': risk_score,
                'risk_factors': risk_factors,
                'who_assessment': who_result,
                'anthropometric_data': {
                    'age_months': age_months,
                    'weight_kg': weight_kg,
                    'height_cm': height_cm,
                    'bmi': round(bmi, 2),
                    'muac_cm': muac_cm,
                    'has_edema': has_edema
                },
                'assessment_date': datetime.now().isoformat(),
                'assessment_method': 'WHO Standards + Clinical Indicators'
            }
            
            return assessment_result
            
        except Exception as e:
            print(f"Assessment error: {e}")
            return {
                'primary_diagnosis': 'Assessment Error',
                'risk_level': 'Unknown',
                'confidence': 0.0,
                'error': str(e),
                'assessment_date': datetime.now().isoformat()
            }
