# Database Field Mapping for Malnutrition Model

## Overview
This document shows how the malnutrition model has been updated to work with your database schema.

## Database Fields Supported

### Core Patient Information
- `patient_id` (BIGINT PRIMARY KEY AUTO_INCREMENT) - Unique patient identifier
- `parent_id` (BIGINT NULL) - Reference to parent/guardian
- `nutritionist_id` (BIGINT NULL) - Assigned nutritionist
- `first_name` (VARCHAR(255) NOT NULL) - Patient's first name
- `middle_name` (VARCHAR(255) NULL) - Patient's middle name
- `last_name` (VARCHAR(255) NOT NULL) - Patient's last name
- `barangay_id` (BIGINT NOT NULL) - Location identifier
- `contact_number` (VARCHAR(255) NULL) - Contact information

### Medical Measurements
- `age_months` (INT NOT NULL) - Age in months (0-60)
- `sex` (ENUM('Male', 'Female') NOT NULL) - Patient's sex
- `date_of_admission` (DATE NOT NULL) - When patient was assessed
- `weight_kg` (DECIMAL(5, 2) NOT NULL) - Weight in kilograms
- `height_cm` (DECIMAL(5, 2) NOT NULL) - Height in centimeters

### Household Information
- `total_household_adults` (INT DEFAULT 0) - Number of adults in household
- `total_household_children` (INT DEFAULT 0) - Number of children in household
- `total_household_twins` (INT DEFAULT 0) - Number of twins in household
- `is_4ps_beneficiary` (BOOLEAN DEFAULT FALSE) - 4Ps program beneficiary status

### Calculated WHO Standards (Auto-populated by model)
- `weight_for_age` (VARCHAR(50) NULL) - WHO weight-for-age classification
- `height_for_age` (VARCHAR(50) NULL) - WHO height-for-age classification
- `bmi_for_age` (VARCHAR(50) NULL) - BMI-for-age classification

### Additional Medical Information
- `breastfeeding` (ENUM('Yes', 'No') NULL) - Breastfeeding status
- `other_medical_problems` (TEXT NULL) - Any other medical conditions
- `edema` (ENUM('Yes', 'No') NULL) - Presence of edema

## Changes Made to Model

### 1. Field Name Updates
- `weight` → `weight_kg`
- `height` → `height_cm`
- `4ps_beneficiary` → `is_4ps_beneficiary`
- `name` → `first_name`, `middle_name`, `last_name`
- `municipality` → `barangay_id`
- `adults` → `total_household_adults`
- `children` → `total_household_children`
- `twins` → `total_household_twins`

### 2. Data Processing Updates
- WHO Z-scores are automatically calculated and stored in the database fields
- BMI calculations use the new field names
- Medical conditions are consolidated into `other_medical_problems` text field
- Categorical encoding updated for new field names

### 3. New Features Added
- Automatic calculation of `weight_for_age`, `height_for_age`, and `bmi_for_age`
- Support for NULL values in optional fields
- Enhanced error handling for missing data
- Bulk processing capabilities for multiple patients

## Example Usage

### Single Patient Assessment
```python
from malnutrition_model import MalnutritionRandomForestModel

model = MalnutritionRandomForestModel()

patient_data = {
    'patient_id': 12345,
    'first_name': 'Juan',
    'last_name': 'Santos',
    'age_months': 18,
    'sex': 'Male',
    'weight_kg': 8.5,
    'height_cm': 75.0,
    'total_household_adults': 2,
    'total_household_children': 3,
    'is_4ps_beneficiary': True,
    'breastfeeding': 'No',
    'edema': 'No'
}

result = model.enhanced_assessment(
    weight=patient_data['weight_kg'],
    height=patient_data['height_cm'],
    age_months=patient_data['age_months'],
    sex=patient_data['sex'],
    has_edema=patient_data['edema'] == 'Yes'
)
```

### Database Integration
```sql
-- Example query to get patient data for the model
SELECT 
    patient_id, parent_id, nutritionist_id,
    first_name, middle_name, last_name,
    barangay_id, contact_number,
    age_months, sex, date_of_admission,
    total_household_adults, total_household_children, total_household_twins,
    is_4ps_beneficiary,
    weight_kg, height_cm,
    weight_for_age, height_for_age, bmi_for_age,
    breastfeeding, other_medical_problems, edema
FROM patients 
WHERE patient_id = ?;

-- Update calculated fields after model assessment
UPDATE patients 
SET weight_for_age = ?,
    height_for_age = ?,
    bmi_for_age = ?
WHERE patient_id = ?;
```

## Key Benefits

1. **Full Database Compatibility**: All your database fields are now supported
2. **Automatic WHO Calculations**: Z-scores and classifications calculated automatically
3. **Confidence Scoring**: Reliability assessment for each prediction
4. **Bulk Processing**: Handle multiple patients efficiently
5. **API Ready**: Easy integration with web applications
6. **Backward Compatible**: Existing treatment protocols still work

## Files Updated

- `malnutrition_model.py` - Main model updated with new field names
- `api_example.py` - API usage examples updated
- `database_integration_example.py` - Complete integration example
- `database_field_mapping.md` - This documentation

The model is now fully compatible with your database schema and ready for production use!
