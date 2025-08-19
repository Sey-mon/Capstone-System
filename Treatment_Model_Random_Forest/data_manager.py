"""
Data Manager Module for Child Malnutrition Assessment System
Handles data validation, cleaning, and sample dataset generation
"""

import pandas as pd
import numpy as np
from typing import Dict, List, Any, Optional
import logging
from datetime import datetime, date

logger = logging.getLogger(__name__)

class DataManager:
    """Manages data operations for the malnutrition assessment system"""
    
    def __init__(self):
        """Initialize the data manager"""
        self.required_fields = [
            'name', 'age_months', 'sex', 'weight', 'height',
            'municipality', 'total_household', 'adults', 'children',
            'twins', '4ps_beneficiary', 'breastfeeding', 'edema',
            'tuberculosis', 'malaria', 'congenital_anomalies', 'other_medical_problems'
        ]
        
        self.numeric_fields = [
            'age_months', 'weight', 'height', 'total_household', 'adults', 'children', 'twins'
        ]
        
        self.categorical_fields = {
            'sex': ['male', 'female'],
            '4ps_beneficiary': ['Yes', 'No'],
            'breastfeeding': ['Yes', 'No'],
            'tuberculosis': ['Yes', 'No'],
            'malaria': ['Yes', 'No'],
            'congenital_anomalies': ['Yes', 'No'],
            'other_medical_problems': ['Yes', 'No']
        }
        
        self.boolean_fields = ['edema']
        
    def _calculate_whz_from_bmi(self, bmi: float, age_months: int) -> float:
        """
        Calculate a simplified WHZ score based on BMI and age
        This is a practical approximation for compatibility with your model
        """
        # Age-specific BMI references (simplified WHO approach)
        if age_months < 24:  # Under 2 years
            normal_bmi_mean = 16.0
            normal_bmi_sd = 1.5
        else:  # 2-5 years  
            normal_bmi_mean = 15.5
            normal_bmi_sd = 1.2
            
        # Calculate Z-score: (observed - mean) / SD
        whz_score = (bmi - normal_bmi_mean) / normal_bmi_sd
        return round(whz_score, 2)
        
    def validate_data(self, df: pd.DataFrame) -> Dict[str, Any]:
        """
        Validate data structure and content
        
        Args:
            df: DataFrame to validate
            
        Returns:
            Dictionary with validation results
        """
        try:
            errors = []
            warnings = []
            missing_fields = []
            
            # Check required fields
            for field in self.required_fields:
                if field not in df.columns:
                    missing_fields.append(field)
                    errors.append(f"Missing required field: {field}")
            
            # Validate data types and ranges
            for row_number, (_, row) in enumerate(df.iterrows(), 1):
                row_errors = []
                
                # Validate numeric fields
                for field in self.numeric_fields:
                    if field in df.columns:
                        try:
                            value = pd.to_numeric(row[field], errors='coerce')
                            if pd.isna(value):
                                row_errors.append(f"Row {row_number}: Invalid numeric value for {field}")
                            elif field == 'age_months' and (value < 0 or value > 60):
                                row_errors.append(f"Row {row_number}: Age must be between 0-60 months")
                            elif field == 'weight' and (value < 0 or value > 50):
                                warnings.append(f"Row {row_number}: Unusual weight value: {value}kg")
                            elif field == 'height' and (value < 30 or value > 150):
                                warnings.append(f"Row {row_number}: Unusual height value: {value}cm")
                        except Exception as e:
                            row_errors.append(f"Row {row_number}: Error validating {field}: {str(e)}")
                
                # Validate categorical fields
                for field, valid_values in self.categorical_fields.items():
                    if field in df.columns and row[field] not in valid_values:
                        row_errors.append(f"Row {row_number}: Invalid value for {field}. Must be one of: {valid_values}")
                
                # Validate boolean fields
                for field in self.boolean_fields:
                    if field in df.columns:
                        if not isinstance(row[field], (bool, int)) and str(row[field]).lower() not in ['true', 'false', '1', '0']:
                            row_errors.append(f"Row {row_number}: Invalid boolean value for {field}")
                
                errors.extend(row_errors)
            
            is_valid = len(errors) == 0
            
            return {
                'valid': is_valid,
                'errors': errors,
                'warnings': warnings,
                'missing_fields': missing_fields,
                'total_rows': len(df),
                'valid_rows': len(df) - len([e for e in errors if 'Row' in e])
            }
            
        except Exception as e:
            logger.error(f"Error validating data: {e}")
            return {
                'valid': False,
                'errors': [f"Validation error: {str(e)}"],
                'warnings': [],
                'missing_fields': [],
                'total_rows': len(df) if df is not None else 0,
                'valid_rows': 0
            }
    
    def validate_and_clean_data(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Validate and clean data for assessment
        
        Args:
            df: DataFrame to clean
            
        Returns:
            Cleaned DataFrame
        """
        try:
            # Make a copy to avoid modifying original
            cleaned_df = df.copy()
            
            # Fill missing values with defaults
            defaults = {
                'name': 'Unknown',
                'age_months': 12,
                'sex': 'male',
                'weight': 10.0,
                'height': 75.0,
                'municipality': 'Unknown',
                'total_household': 4,
                'adults': 2,
                'children': 2,
                'twins': 0,
                '4ps_beneficiary': 'No',
                'breastfeeding': 'No',
                'edema': False,
                'tuberculosis': 'No',
                'malaria': 'No',
                'congenital_anomalies': 'No',
                'other_medical_problems': 'No'
            }
            
            for field, default_value in defaults.items():
                if field in cleaned_df.columns:
                    cleaned_df[field] = cleaned_df[field].fillna(default_value)
                else:
                    cleaned_df[field] = default_value
            
            # Convert data types
            for field in self.numeric_fields:
                if field in cleaned_df.columns:
                    cleaned_df[field] = pd.to_numeric(cleaned_df[field], errors='coerce')
                    cleaned_df[field] = cleaned_df[field].fillna(defaults.get(field, 0))
            
            # Ensure boolean fields are properly formatted
            for field in self.boolean_fields:
                if field in cleaned_df.columns:
                    cleaned_df[field] = cleaned_df[field].astype(bool)
            
            # Validate ranges and apply corrections
            cleaned_df['age_months'] = cleaned_df['age_months'].clip(0, 60)
            cleaned_df['weight'] = cleaned_df['weight'].clip(1, 50)
            cleaned_df['height'] = cleaned_df['height'].clip(30, 150)
            cleaned_df['total_household'] = cleaned_df['total_household'].clip(1, 20)
            cleaned_df['adults'] = cleaned_df['adults'].clip(0, 10)
            cleaned_df['children'] = cleaned_df['children'].clip(0, 15)
            cleaned_df['twins'] = cleaned_df['twins'].clip(0, 1)
            
            logger.info(f"Cleaned data for {len(cleaned_df)} records")
            return cleaned_df
            
        except Exception as e:
            logger.error(f"Error cleaning data: {e}")
            raise ValueError(f"Data cleaning failed: {str(e)}")
    
    def create_sample_dataset(self, num_samples: int = 5) -> pd.DataFrame:
        """
        Create sample dataset for template purposes
        
        Args:
            num_samples: Number of sample records to generate
            
        Returns:
            DataFrame with sample data including WHO Z-scores
        """
        try:
            np.random.seed(42)  # For reproducible samples
            
            municipalities = ['Manila', 'Quezon City', 'Caloocan', 'Davao', 'Cebu City']
            names = ['Patient A', 'Patient B', 'Patient C', 'Patient D', 'Patient E']
            
            sample_data = []
            
            for i in range(num_samples):
                # Generate basic measurements
                age_months = np.random.randint(6, 60)
                weight = round(np.random.uniform(5.0, 20.0), 1)
                height = round(np.random.uniform(50.0, 110.0), 1)
                sex = np.random.choice(['male', 'female'])
                
                # Calculate BMI
                bmi = weight / ((height/100) ** 2)
                
                sample = {
                    'name': names[i] if i < len(names) else f'Patient {i+1}',
                    'age_months': age_months,
                    'sex': sex,
                    'weight': weight,
                    'height': height,
                    'municipality': np.random.choice(municipalities),
                    'total_household': np.random.randint(2, 8),
                    'adults': np.random.randint(1, 4),
                    'children': np.random.randint(1, 5),
                    'twins': np.random.choice([0, 1], p=[0.9, 0.1]),
                    '4ps_beneficiary': np.random.choice(['Yes', 'No'], p=[0.3, 0.7]),
                    'breastfeeding': np.random.choice(['Yes', 'No'], p=[0.6, 0.4]),
                    'edema': np.random.choice([True, False], p=[0.1, 0.9]),
                    'tuberculosis': np.random.choice(['Yes', 'No'], p=[0.05, 0.95]),
                    'malaria': np.random.choice(['Yes', 'No'], p=[0.05, 0.95]),
                    'congenital_anomalies': np.random.choice(['Yes', 'No'], p=[0.05, 0.95]),
                    'other_medical_problems': np.random.choice(['Yes', 'No'], p=[0.1, 0.9]),
                    'date_of_admission': datetime.now().strftime('%Y-%m-%d'),
                    # Calculate WHZ score from BMI (what your model actually expects)
                    'whz_score': self._calculate_whz_from_bmi(bmi, age_months)
                }
                sample_data.append(sample)
            
            df = pd.DataFrame(sample_data)
            logger.info(f"Created sample dataset with {num_samples} records including WHZ scores")
            return df
            
        except Exception as e:
            logger.error(f"Error creating sample dataset: {e}")
            raise ValueError(f"Sample dataset creation failed: {str(e)}")
    
    def get_data_statistics(self, df: pd.DataFrame) -> Dict[str, Any]:
        """
        Get basic statistics for the dataset
        
        Args:
            df: DataFrame to analyze
            
        Returns:
            Dictionary with statistics
        """
        try:
            stats = {
                'total_records': len(df),
                'columns': list(df.columns),
                'missing_values': df.isnull().sum().to_dict(),
                'data_types': df.dtypes.astype(str).to_dict()
            }
            
            # Numeric field statistics
            numeric_stats = {}
            for field in self.numeric_fields:
                if field in df.columns:
                    numeric_stats[field] = {
                        'mean': float(df[field].mean()) if not df[field].empty else 0,
                        'std': float(df[field].std()) if not df[field].empty else 0,
                        'min': float(df[field].min()) if not df[field].empty else 0,
                        'max': float(df[field].max()) if not df[field].empty else 0
                    }
            
            stats['numeric_statistics'] = numeric_stats
            
            # Categorical field distributions
            categorical_stats = {}
            for field in self.categorical_fields.keys():
                if field in df.columns:
                    categorical_stats[field] = df[field].value_counts().to_dict()
            
            stats['categorical_distributions'] = categorical_stats
            
            return stats
            
        except Exception as e:
            logger.error(f"Error calculating statistics: {e}")
            return {'error': str(e)}
