"""
Data Import/Export utilities for the Child Malnutrition Assessment System
Handles various data formats and WHO reference tables
"""

import pandas as pd
import numpy as np
import json
import os
from datetime import datetime

class DataManager:
    """
    Handles data import, export, and WHO reference data management
    """
    
    def __init__(self):
        self.supported_formats = ['.csv', '.xlsx', '.json']
        self.who_reference_data = self.load_who_reference_data()
    
    def load_who_reference_data(self):
        """
        Load WHO reference data for z-score calculations
        This is a simplified version - in production, you would load full WHO tables
        """
        return {
            'weight_for_height_boys': {
                # Height (cm): {'L': L_value, 'M': median, 'S': coefficient_of_variation}
                45.0: {'L': 0.3533, 'M': 2.441, 'S': 0.09189},
                45.5: {'L': 0.3555, 'M': 2.498, 'S': 0.09157},
                46.0: {'L': 0.3577, 'M': 2.555, 'S': 0.09126},
                # ... (you would include all heights from 45-110 cm)
                110.0: {'L': -0.5070, 'M': 20.280, 'S': 0.10574}
            },
            'weight_for_height_girls': {
                45.0: {'L': 0.3809, 'M': 2.369, 'S': 0.08770},
                45.5: {'L': 0.3831, 'M': 2.424, 'S': 0.08741},
                46.0: {'L': 0.3853, 'M': 2.480, 'S': 0.08713},
                # ... (you would include all heights from 45-110 cm)
                110.0: {'L': -0.4880, 'M': 19.370, 'S': 0.10100}
            },
            'height_for_age_boys': {
                # Age (months): {'L': L_value, 'M': median, 'S': coefficient_of_variation}
                0: {'L': 1, 'M': 49.8842, 'S': 0.03686},
                1: {'L': 1, 'M': 54.7244, 'S': 0.03293},
                # ... (you would include all ages from 0-60 months)
                60: {'L': 1, 'M': 109.4496, 'S': 0.03795}
            },
            'height_for_age_girls': {
                0: {'L': 1, 'M': 49.1477, 'S': 0.03790},
                1: {'L': 1, 'M': 53.6872, 'S': 0.03370},
                # ... (you would include all ages from 0-60 months)
                60: {'L': 1, 'M': 108.1349, 'S': 0.03900}
            },
            'weight_for_age_boys': {
                0: {'L': 0.3487, 'M': 3.3464, 'S': 0.14602},
                1: {'L': 0.2581, 'M': 4.4709, 'S': 0.13395},
                # ... (you would include all ages from 0-60 months)
                60: {'L': 0.1738, 'M': 18.3006, 'S': 0.12462}
            },
            'weight_for_age_girls': {
                0: {'L': 0.3809, 'M': 3.2322, 'S': 0.14171},
                1: {'L': 0.1233, 'M': 4.1873, 'S': 0.13724},
                # ... (you would include all ages from 0-60 months)
                60: {'L': 0.0064, 'M': 17.0960, 'S': 0.13218}
            }
        }
    
    def calculate_z_score_lms(self, measurement, reference_values):
        """
        Calculate z-score using LMS method (Box-Cox transformation)
        L = power in the Box-Cox transformation
        M = median
        S = coefficient of variation
        """
        L = reference_values['L']
        M = reference_values['M']
        S = reference_values['S']
        
        if L != 0:
            z_score = (((measurement / M) ** L) - 1) / (L * S)
        else:
            z_score = np.log(measurement / M) / S
        
        return round(z_score, 2)
    
    def import_data(self, file_path, file_format=None):
        """
        Import data from various file formats
        """
        if file_format is None:
            file_format = os.path.splitext(file_path)[1].lower()
        
        if file_format not in self.supported_formats:
            raise ValueError(f"Unsupported file format: {file_format}")
        
        try:
            if file_format == '.csv':
                df = pd.read_csv(file_path)
            elif file_format == '.xlsx':
                df = pd.read_excel(file_path)
            elif file_format == '.json':
                df = pd.read_json(file_path)
            
            # Validate and clean data
            df_cleaned = self.validate_and_clean_data(df)
            return df_cleaned
            
        except Exception as e:
            raise Exception(f"Error importing data: {str(e)}")
    
    def export_data(self, df, file_path, file_format=None):
        """
        Export data to various file formats
        """
        if file_format is None:
            file_format = os.path.splitext(file_path)[1].lower()
        
        try:
            if file_format == '.csv':
                df.to_csv(file_path, index=False)
            elif file_format == '.xlsx':
                df.to_excel(file_path, index=False)
            elif file_format == '.json':
                df.to_json(file_path, orient='records', indent=2)
            
            print(f"Data exported successfully to {file_path}")
            
        except Exception as e:
            raise Exception(f"Error exporting data: {str(e)}")
    
    def validate_and_clean_data(self, df):
        """
        Validate and clean imported data
        """
        df_clean = df.copy()
        
        # Required columns mapping (handle different naming conventions)
        column_mapping = {
            'name': ['name', 'child_name', 'patient_name'],
            'municipality': ['municipality', 'city', 'location'],
            'number': ['number', 'id', 'patient_id', 'child_id'],
            'age_months': ['age_months', 'age', 'age_in_months'],
            'sex': ['sex', 'gender'],
            'weight': ['weight', 'weight_kg'],
            'height': ['height', 'height_cm'],
            '4ps_beneficiary': ['4ps_beneficiary', 'fourps_beneficiary', '4ps'],
            'breastfeeding': ['breastfeeding', 'breastfed'],
            'edema': ['edema', 'edema_present']
        }
        
        # Standardize column names
        for standard_name, variations in column_mapping.items():
            for col in df_clean.columns:
                if col.lower() in [v.lower() for v in variations]:
                    df_clean = df_clean.rename(columns={col: standard_name})
                    break
        
        # Data type conversions and validations
        if 'age_months' in df_clean.columns:
            df_clean['age_months'] = pd.to_numeric(df_clean['age_months'], errors='coerce')
            df_clean = df_clean[df_clean['age_months'].between(0, 60)]
        
        if 'weight' in df_clean.columns:
            df_clean['weight'] = pd.to_numeric(df_clean['weight'], errors='coerce')
            df_clean = df_clean[df_clean['weight'].between(1, 50)]
        
        if 'height' in df_clean.columns:
            df_clean['height'] = pd.to_numeric(df_clean['height'], errors='coerce')
            df_clean = df_clean[df_clean['height'].between(30, 120)]
        
        # Standardize categorical values
        if 'sex' in df_clean.columns:
            df_clean['sex'] = df_clean['sex'].str.lower().str.strip()
            df_clean['sex'] = df_clean['sex'].map({
                'male': 'Male', 'm': 'Male', 'boy': 'Male',
                'female': 'Female', 'f': 'Female', 'girl': 'Female'
            }).fillna(df_clean['sex'])
        
        if '4ps_beneficiary' in df_clean.columns:
            df_clean['4ps_beneficiary'] = df_clean['4ps_beneficiary'].str.lower().str.strip()
            df_clean['4ps_beneficiary'] = df_clean['4ps_beneficiary'].map({
                'yes': 'Yes', 'y': 'Yes', '1': 'Yes', 'true': 'Yes',
                'no': 'No', 'n': 'No', '0': 'No', 'false': 'No'
            }).fillna('No')
        
        # Remove rows with missing critical data
        critical_columns = ['age_months', 'weight', 'height', 'sex']
        available_critical = [col for col in critical_columns if col in df_clean.columns]
        df_clean = df_clean.dropna(subset=available_critical)
        
        return df_clean
    
    def validate_data(self, df):
        """
        Validate data and return validation results
        """
        issues = []
        
        # Check required columns
        required_cols = ['name', 'age_months', 'sex', 'weight', 'height']
        missing_cols = [col for col in required_cols if col not in df.columns]
        if missing_cols:
            issues.append(f"Missing required columns: {', '.join(missing_cols)}")
        
        # Check data ranges
        if 'age_months' in df.columns:
            df_numeric_age = pd.to_numeric(df['age_months'], errors='coerce')
            invalid_ages = df[~df_numeric_age.between(0, 60) | df_numeric_age.isna()]
            if not invalid_ages.empty:
                issues.append(f"Invalid age values found in {len(invalid_ages)} rows (should be 0-60 months)")
        
        if 'weight' in df.columns:
            df_numeric_weight = pd.to_numeric(df['weight'], errors='coerce')
            invalid_weights = df[~df_numeric_weight.between(1, 50) | df_numeric_weight.isna()]
            if not invalid_weights.empty:
                issues.append(f"Invalid weight values found in {len(invalid_weights)} rows (should be 1-50 kg)")
        
        if 'height' in df.columns:
            df_numeric_height = pd.to_numeric(df['height'], errors='coerce')
            invalid_heights = df[~df_numeric_height.between(30, 120) | df_numeric_height.isna()]
            if not invalid_heights.empty:
                issues.append(f"Invalid height values found in {len(invalid_heights)} rows (should be 30-120 cm)")
        
        # Check for missing critical data
        if 'name' in df.columns:
            missing_names = df[df['name'].isna() | (df['name'] == '')]
            if not missing_names.empty:
                issues.append(f"Missing names in {len(missing_names)} rows")
        
        return {
            'valid': len(issues) == 0,
            'issues': issues,
            'total_rows': len(df),
            'valid_rows': len(df) - len(issues) if issues else len(df)
        }

    def create_sample_dataset(self, n_samples=100, save_path=None):
        """
        Create a sample dataset for testing
        """
        np.random.seed(42)
        
        data = []
        municipalities = ['Manila', 'Quezon City', 'Caloocan', 'Davao', 'Cebu', 'Zamboanga', 'Iloilo', 'Cagayan de Oro']
        
        for i in range(n_samples):
            age_months = np.random.randint(0, 61)
            sex = np.random.choice(['Male', 'Female'])
            
            # Generate realistic anthropometric data
            if age_months <= 6:
                height = np.random.normal(55 + age_months * 2.5, 3)
                base_weight = 3.2 + age_months * 0.65
            elif age_months <= 12:
                height = np.random.normal(65 + (age_months - 6) * 1.8, 4)
                base_weight = 7.5 + (age_months - 6) * 0.4
            elif age_months <= 24:
                height = np.random.normal(75 + (age_months - 12) * 1.2, 5)
                base_weight = 10 + (age_months - 12) * 0.3
            else:
                height = np.random.normal(85 + (age_months - 24) * 0.8, 6)
                base_weight = 13.5 + (age_months - 24) * 0.25
            
            # Add malnutrition variations
            malnutrition_prob = np.random.random()
            if malnutrition_prob < 0.15:  # 15% malnourished
                weight_factor = np.random.uniform(0.6, 0.85)  # Underweight
            else:
                weight_factor = np.random.uniform(0.85, 1.15)  # Normal range
            
            weight = max(1.5, base_weight * weight_factor + np.random.normal(0, 0.3))
            height = max(35, height)
            
            record = {
                'name': f'Child_{i+1:03d}',
                'municipality': np.random.choice(municipalities),
                'number': f'ID{i+1:04d}',
                'age_months': age_months,
                'sex': sex,
                'date_of_admission': datetime.now().strftime('%Y-%m-%d'),
                'total_household': np.random.randint(3, 12),
                'adults': np.random.randint(2, 6),
                'children': np.random.randint(1, 6),
                'twins': np.random.choice([0, 1], p=[0.97, 0.03]),
                '4ps_beneficiary': np.random.choice(['Yes', 'No'], p=[0.65, 0.35]),
                'weight': round(weight, 1),
                'height': round(height, 1),
                'breastfeeding': np.random.choice(['Yes', 'No'], p=[0.7, 0.3]) if age_months <= 24 else 'No',
                'tuberculosis': np.random.choice(['Yes', 'No'], p=[0.03, 0.97]),
                'malaria': np.random.choice(['Yes', 'No'], p=[0.05, 0.95]),
                'congenital_anomalies': np.random.choice(['Yes', 'No'], p=[0.02, 0.98]),
                'other_medical_problems': np.random.choice(['Yes', 'No'], p=[0.08, 0.92]),
                'edema': np.random.choice([True, False], p=[0.01, 0.99])
            }
            data.append(record)
        
        df = pd.DataFrame(data)
        
        if save_path:
            self.export_data(df, save_path)
        
        return df
    
    def create_data_template(self, save_path):
        """
        Create a data entry template
        """
        template_data = {
            'name': ['Child Name'],
            'municipality': ['City/Municipality'],
            'number': ['Patient ID'],
            'age_months': [18],
            'sex': ['Male/Female'],
            'date_of_admission': [datetime.now().strftime('%Y-%m-%d')],
            'total_household': [5],
            'adults': [2],
            'children': [3],
            'twins': [0],
            '4ps_beneficiary': ['Yes/No'],
            'weight': [10.5],
            'height': [80.0],
            'breastfeeding': ['Yes/No'],
            'tuberculosis': ['Yes/No'],
            'malaria': ['Yes/No'],
            'congenital_anomalies': ['Yes/No'],
            'other_medical_problems': ['Yes/No'],
            'edema': ['True/False']
        }
        
        df_template = pd.DataFrame(template_data)
        self.export_data(df_template, save_path)
        print(f"Data template created at {save_path}")
        
        return df_template
    
    def load_who_charts(self, chart_file_path):
        """
        Load custom WHO reference charts
        Expected format: Excel file with sheets for different references
        """
        try:
            if chart_file_path.endswith('.xlsx'):
                # Load all sheets
                excel_file = pd.ExcelFile(chart_file_path)
                who_data = {}
                
                for sheet_name in excel_file.sheet_names:
                    df = pd.read_excel(chart_file_path, sheet_name=sheet_name)
                    who_data[sheet_name.lower()] = df
                
                self.custom_who_data = who_data
                print(f"Custom WHO charts loaded from {chart_file_path}")
                
                return who_data
            else:
                raise ValueError("WHO charts file must be in Excel format (.xlsx)")
                
        except Exception as e:
            print(f"Error loading WHO charts: {str(e)}")
            return None

# Example usage and testing
if __name__ == "__main__":
    # Initialize data manager
    dm = DataManager()
    
    # Create sample dataset
    sample_df = dm.create_sample_dataset(200, 'sample_data.csv')
    print(f"Created sample dataset with {len(sample_df)} records")
    
    # Create data entry template
    template_df = dm.create_data_template('data_template.xlsx')
    print("Data entry template created")
    
    # Test import/export
    imported_df = dm.import_data('sample_data.csv')
    print(f"Imported {len(imported_df)} records")
    
    # Export to different formats
    dm.export_data(imported_df, 'exported_data.xlsx')
    dm.export_data(imported_df, 'exported_data.json')
    
    print("Data import/export testing completed successfully!")
