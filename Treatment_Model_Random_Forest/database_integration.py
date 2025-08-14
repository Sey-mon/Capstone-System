"""
Database Integration for Child Malnutrition Assessment System
Integrates with MySQL database schema for patient and assessment data
"""

import mysql.connector
from mysql.connector import Error
import pandas as pd
from datetime import datetime, date
import json
import logging
from typing import Dict, List, Optional, Any
from dataclasses import dataclass
import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

logger = logging.getLogger(__name__)

@dataclass
class DatabaseConfig:
    """Database configuration"""
    host: str = os.getenv('DB_HOST', 'localhost')
    port: int = int(os.getenv('DB_PORT', 3306))
    database: str = os.getenv('DB_NAME', 'malnutrition_assessment')
    user: str = os.getenv('DB_USER', 'root')
    password: str = os.getenv('DB_PASSWORD', '')

class DatabaseManager:
    """Manages database operations for the malnutrition assessment system"""
    
    def __init__(self, config: DatabaseConfig = None):
        self.config = config or DatabaseConfig()
        self.connection = None
    
    def connect(self):
        """Establish database connection"""
        try:
            self.connection = mysql.connector.connect(
                host=self.config.host,
                port=self.config.port,
                database=self.config.database,
                user=self.config.user,
                password=self.config.password,
                autocommit=True
            )
            logger.info("Database connection established")
            return True
        except Error as e:
            logger.error(f"Error connecting to database: {e}")
            return False
    
    def disconnect(self):
        """Close database connection"""
        if self.connection and self.connection.is_connected():
            self.connection.close()
            logger.info("Database connection closed")
    
    def execute_query(self, query: str, params: tuple = None):
        """Execute a database query"""
        try:
            if not self.connection or not self.connection.is_connected():
                self.connect()
            
            cursor = self.connection.cursor(dictionary=True)
            cursor.execute(query, params or ())
            
            if query.strip().upper().startswith('SELECT'):
                result = cursor.fetchall()
            else:
                result = cursor.rowcount
            
            cursor.close()
            return result
        except Error as e:
            logger.error(f"Database query error: {e}")
            raise
    
    def save_patient(self, patient_data: Dict[str, Any]) -> Optional[int]:
        """
        Save patient data to database
        
        Args:
            patient_data: Patient information dictionary
            
        Returns:
            patient_id: ID of the created patient record
        """
        try:
            query = """
            INSERT INTO patients (
                facility_id, patient_number, first_name, middle_name, last_name,
                nickname, sex, date_of_birth, place_of_birth, barangay_id,
                address, coordinates, date_of_admission, admission_status,
                admission_weight, admission_height, mother_name, mother_age,
                mother_education, father_name, father_age, father_education,
                guardian_name, guardian_relationship, guardian_contact,
                total_household_members, household_adults, household_children,
                is_twin, is_4ps_beneficiary, birth_weight, birth_length,
                gestational_age_weeks, delivery_type, birth_complications,
                current_weight, current_height, whz_score, waz_score, haz_score,
                is_breastfeeding, breastfeeding_duration_months, immunization_status,
                allergies, has_tuberculosis, has_malaria, has_congenital_anomalies,
                congenital_anomalies_details, other_medical_problems, has_edema,
                medical_history, contact_number, alternate_contact, emergency_contact,
                status, photo, created_by, parent_id
            ) VALUES (
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
            )
            """
            
            # Parse name into components
            full_name = patient_data.get('name', '').split()
            first_name = full_name[0] if full_name else ''
            last_name = full_name[-1] if len(full_name) > 1 else ''
            middle_name = ' '.join(full_name[1:-1]) if len(full_name) > 2 else ''
            
            # Calculate date of birth from age_months
            age_months = patient_data.get('age_months', 0)
            current_date = date.today()
            # Approximate calculation (not exact due to varying month lengths)
            birth_date = current_date.replace(year=current_date.year - (age_months // 12))
            
            params = (
                patient_data.get('facility_id'),  # facility_id
                patient_data.get('patient_number', f"PAT{datetime.now().strftime('%Y%m%d%H%M%S')}"),  # patient_number
                first_name,  # first_name
                middle_name,  # middle_name
                last_name,  # last_name
                patient_data.get('nickname'),  # nickname
                patient_data.get('sex', 'male'),  # sex
                birth_date,  # date_of_birth
                patient_data.get('place_of_birth'),  # place_of_birth
                patient_data.get('barangay_id'),  # barangay_id
                patient_data.get('address'),  # address
                json.dumps(patient_data.get('coordinates', {})),  # coordinates
                datetime.strptime(patient_data.get('date_of_admission', current_date.isoformat()), date),  # date_of_admission
                'admitted',  # admission_status
                patient_data.get('weight'),  # admission_weight
                patient_data.get('height'),  # admission_height
                patient_data.get('mother_name'),  # mother_name
                patient_data.get('mother_age'),  # mother_age
                patient_data.get('mother_education'),  # mother_education
                patient_data.get('father_name'),  # father_name
                patient_data.get('father_age'),  # father_age
                patient_data.get('father_education'),  # father_education
                patient_data.get('guardian_name'),  # guardian_name
                patient_data.get('guardian_relationship'),  # guardian_relationship
                patient_data.get('guardian_contact'),  # guardian_contact
                patient_data.get('total_household', 1),  # total_household_members
                patient_data.get('adults', 0),  # household_adults
                patient_data.get('children', 1),  # household_children
                bool(patient_data.get('twins', 0)),  # is_twin
                patient_data.get('4ps_beneficiary', 'No').lower() == 'yes',  # is_4ps_beneficiary
                patient_data.get('birth_weight'),  # birth_weight
                patient_data.get('birth_length'),  # birth_length
                patient_data.get('gestational_age_weeks'),  # gestational_age_weeks
                patient_data.get('delivery_type'),  # delivery_type
                patient_data.get('birth_complications'),  # birth_complications
                patient_data.get('weight'),  # current_weight
                patient_data.get('height'),  # current_height
                patient_data.get('whz_score'),  # whz_score
                patient_data.get('waz_score'),  # waz_score
                patient_data.get('haz_score'),  # haz_score
                patient_data.get('breastfeeding', 'No').lower() == 'yes',  # is_breastfeeding
                patient_data.get('breastfeeding_duration_months'),  # breastfeeding_duration_months
                patient_data.get('immunization_status'),  # immunization_status
                patient_data.get('allergies'),  # allergies
                patient_data.get('tuberculosis', 'No').lower() == 'yes',  # has_tuberculosis
                patient_data.get('malaria', 'No').lower() == 'yes',  # has_malaria
                patient_data.get('congenital_anomalies', 'No').lower() == 'yes',  # has_congenital_anomalies
                patient_data.get('congenital_anomalies_details'),  # congenital_anomalies_details
                patient_data.get('other_medical_problems'),  # other_medical_problems
                patient_data.get('edema', False),  # has_edema
                patient_data.get('medical_history'),  # medical_history
                patient_data.get('contact_number'),  # contact_number
                patient_data.get('alternate_contact'),  # alternate_contact
                patient_data.get('emergency_contact'),  # emergency_contact
                'active',  # status
                patient_data.get('photo'),  # photo
                patient_data.get('created_by'),  # created_by
                patient_data.get('parent_id')  # parent_id
            )
            
            self.execute_query(query, params)
            
            # Get the inserted patient ID
            cursor = self.connection.cursor()
            cursor.execute("SELECT LAST_INSERT_ID()")
            patient_id = cursor.fetchone()[0]
            cursor.close()
            
            logger.info(f"Patient saved with ID: {patient_id}")
            return patient_id
            
        except Exception as e:
            logger.error(f"Error saving patient: {e}")
            raise
    
    def save_assessment(self, patient_id: int, assessment_data: Dict[str, Any], 
                       visit_id: Optional[int] = None, assessed_by: int = 1) -> Optional[int]:
        """
        Save nutrition assessment to database
        
        Args:
            patient_id: ID of the patient
            assessment_data: Assessment results
            visit_id: Optional visit ID
            assessed_by: User ID who conducted the assessment
            
        Returns:
            assessment_id: ID of the created assessment record
        """
        try:
            query = """
            INSERT INTO nutrition_assessments (
                patient_id, visit_id, assessed_by, assessment_date, assessment_time,
                assessment_location, weight, height, bmi, whz_score, waz_score,
                haz_score, bmiz_score, edema, edema_grade, skin_changes, hair_changes,
                eye_changes, mouth_changes, nutrition_status, malnutrition_type,
                risk_level, confidence_score, api_response, model_version,
                assessment_method, symptoms, dietary_intake, feeding_practices,
                appetite, meal_frequency, food_diversity_score, has_fever,
                has_cough, has_vomiting, has_diarrhea, has_skin_lesions,
                clinical_signs, recommendations, immediate_actions, referral_needed,
                referral_type, hospitalization_needed, next_assessment_date,
                follow_up_required, follow_up_interval_weeks, weather_conditions,
                measurement_conditions, equipment_used, notes, photos
            ) VALUES (
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
            )
            """
            
            # Map prediction to nutrition status
            prediction = assessment_data.get('prediction', 'Normal')
            nutrition_status = 'normal'
            malnutrition_type = 'none'
            
            if 'Severe' in prediction:
                nutrition_status = 'severe_malnutrition'
                malnutrition_type = 'acute'
            elif 'Moderate' in prediction:
                nutrition_status = 'moderate_malnutrition'
                malnutrition_type = 'acute'
            
            # Determine risk level
            risk_level = assessment_data.get('risk_level', 'low')
            
            # Calculate BMI
            weight = assessment_data.get('weight', 0)
            height = assessment_data.get('height', 0)
            bmi = weight / ((height/100) ** 2) if height > 0 else 0
            
            params = (
                patient_id,  # patient_id
                visit_id,  # visit_id
                assessed_by,  # assessed_by
                date.today(),  # assessment_date
                datetime.now().time(),  # assessment_time
                'clinic',  # assessment_location
                weight,  # weight
                height,  # height
                round(bmi, 2),  # bmi
                assessment_data.get('whz_score'),  # whz_score
                assessment_data.get('waz_score'),  # waz_score
                assessment_data.get('haz_score'),  # haz_score
                assessment_data.get('bmiz_score'),  # bmiz_score
                assessment_data.get('edema', False),  # edema
                'none',  # edema_grade
                assessment_data.get('skin_changes'),  # skin_changes
                assessment_data.get('hair_changes'),  # hair_changes
                assessment_data.get('eye_changes'),  # eye_changes
                assessment_data.get('mouth_changes'),  # mouth_changes
                nutrition_status,  # nutrition_status
                malnutrition_type,  # malnutrition_type
                risk_level,  # risk_level
                assessment_data.get('confidence_score'),  # confidence_score
                json.dumps(assessment_data),  # api_response
                assessment_data.get('model_version', '1.0.0'),  # model_version
                'api',  # assessment_method
                assessment_data.get('symptoms'),  # symptoms
                assessment_data.get('dietary_intake'),  # dietary_intake
                assessment_data.get('feeding_practices'),  # feeding_practices
                assessment_data.get('appetite', 'good'),  # appetite
                assessment_data.get('meal_frequency'),  # meal_frequency
                assessment_data.get('food_diversity_score'),  # food_diversity_score
                assessment_data.get('has_fever', False),  # has_fever
                assessment_data.get('has_cough', False),  # has_cough
                assessment_data.get('has_vomiting', False),  # has_vomiting
                assessment_data.get('has_diarrhea', False),  # has_diarrhea
                assessment_data.get('has_skin_lesions', False),  # has_skin_lesions
                assessment_data.get('clinical_signs'),  # clinical_signs
                assessment_data.get('recommendations'),  # recommendations
                assessment_data.get('immediate_actions'),  # immediate_actions
                assessment_data.get('referral_needed', False),  # referral_needed
                assessment_data.get('referral_type'),  # referral_type
                assessment_data.get('hospitalization_needed', False),  # hospitalization_needed
                assessment_data.get('next_assessment_date'),  # next_assessment_date
                assessment_data.get('follow_up_required', False),  # follow_up_required
                assessment_data.get('follow_up_interval_weeks'),  # follow_up_interval_weeks
                assessment_data.get('weather_conditions'),  # weather_conditions
                assessment_data.get('measurement_conditions'),  # measurement_conditions
                assessment_data.get('equipment_used'),  # equipment_used
                assessment_data.get('notes'),  # notes
                json.dumps(assessment_data.get('photos', []))  # photos
            )
            
            self.execute_query(query, params)
            
            # Get the inserted assessment ID
            cursor = self.connection.cursor()
            cursor.execute("SELECT LAST_INSERT_ID()")
            assessment_id = cursor.fetchone()[0]
            cursor.close()
            
            logger.info(f"Assessment saved with ID: {assessment_id}")
            return assessment_id
            
        except Exception as e:
            logger.error(f"Error saving assessment: {e}")
            raise
    
    def get_patient_assessments(self, patient_id: int) -> List[Dict[str, Any]]:
        """
        Get all assessments for a patient
        
        Args:
            patient_id: Patient ID
            
        Returns:
            List of assessment records
        """
        try:
            query = """
            SELECT na.*, p.first_name, p.last_name, p.patient_number
            FROM nutrition_assessments na
            JOIN patients p ON na.patient_id = p.patient_id
            WHERE na.patient_id = %s
            ORDER BY na.assessment_date DESC, na.assessment_time DESC
            """
            
            results = self.execute_query(query, (patient_id,))
            return results
            
        except Exception as e:
            logger.error(f"Error getting patient assessments: {e}")
            raise
    
    def get_patients_by_status(self, status: str = None, limit: int = 100) -> List[Dict[str, Any]]:
        """
        Get patients filtered by nutrition status
        
        Args:
            status: Nutrition status filter
            limit: Maximum number of records
            
        Returns:
            List of patient records
        """
        try:
            if status:
                query = """
                SELECT p.*, na.nutrition_status, na.assessment_date as last_assessment
                FROM patients p
                LEFT JOIN (
                    SELECT patient_id, nutrition_status, assessment_date,
                           ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY assessment_date DESC) as rn
                    FROM nutrition_assessments
                ) na ON p.patient_id = na.patient_id AND na.rn = 1
                WHERE na.nutrition_status = %s
                ORDER BY na.assessment_date DESC
                LIMIT %s
                """
                results = self.execute_query(query, (status, limit))
            else:
                query = """
                SELECT p.*, na.nutrition_status, na.assessment_date as last_assessment
                FROM patients p
                LEFT JOIN (
                    SELECT patient_id, nutrition_status, assessment_date,
                           ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY assessment_date DESC) as rn
                    FROM nutrition_assessments
                ) na ON p.patient_id = na.patient_id AND na.rn = 1
                ORDER BY na.assessment_date DESC
                LIMIT %s
                """
                results = self.execute_query(query, (limit,))
            
            return results
            
        except Exception as e:
            logger.error(f"Error getting patients by status: {e}")
            raise
    
    def get_analytics_summary(self) -> Dict[str, Any]:
        """
        Get analytics summary for the system
        
        Returns:
            Analytics summary dictionary
        """
        try:
            # Total patients
            total_patients = self.execute_query("SELECT COUNT(*) as count FROM patients")[0]['count']
            
            # Total assessments
            total_assessments = self.execute_query("SELECT COUNT(*) as count FROM nutrition_assessments")[0]['count']
            
            # Status distribution
            status_query = """
            SELECT nutrition_status, COUNT(*) as count
            FROM nutrition_assessments
            GROUP BY nutrition_status
            """
            status_distribution = self.execute_query(status_query)
            
            # Recent assessments (last 30 days)
            recent_query = """
            SELECT COUNT(*) as count
            FROM nutrition_assessments
            WHERE assessment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            """
            recent_assessments = self.execute_query(recent_query)[0]['count']
            
            return {
                "total_patients": total_patients,
                "total_assessments": total_assessments,
                "recent_assessments_30_days": recent_assessments,
                "status_distribution": {item['nutrition_status']: item['count'] for item in status_distribution},
                "average_assessments_per_patient": round(total_assessments / total_patients, 2) if total_patients > 0 else 0
            }
            
        except Exception as e:
            logger.error(f"Error getting analytics summary: {e}")
            raise
    
    def __enter__(self):
        """Context manager entry"""
        self.connect()
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        """Context manager exit"""
        self.disconnect()

# Example usage
if __name__ == "__main__":
    # Test database connection
    db = DatabaseManager()
    if db.connect():
        print("Database connection successful")
        
        # Test analytics
        try:
            summary = db.get_analytics_summary()
            print("Analytics summary:", summary)
        except Exception as e:
            print(f"Error getting analytics: {e}")
        
        db.disconnect()
    else:
        print("Database connection failed") 