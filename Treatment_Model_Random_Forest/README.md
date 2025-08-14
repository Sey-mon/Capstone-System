# Child Malnutrition Assessment System - FastAPI Backend

A comprehensive Random Forest-based prescriptive analytics system for assessing malnutrition in children aged 0-5 years, following WHO guidelines and implementing the clinical decision flowchart for treatment recommendations. Now with FastAPI backend and essential security features.

## 🎯 Overview

This system provides:
- **REST API endpoints** for all model functionalities
- **Automated WHZ score calculation** based on WHO reference standards
- **Random Forest prediction model** for malnutrition classification
- **Treatment recommendations** following the clinical flowchart provided
- **Risk stratification** with multi-level assessment
- **Uncertainty quantification** for prediction confidence
- **Personalized recommendations** based on individual factors
- **Batch processing** capabilities for multiple patients
- **File upload support** (CSV, Excel, JSON)
- **Security features** (API authentication, rate limiting, logging)

## 📊 Classification Categories

The system classifies children into the following nutritional status categories:

1. **Normal** (WHZ ≥ -2)
2. **Moderate Acute Malnutrition (MAM)** (-3 ≤ WHZ < -2)
3. **Severe Acute Malnutrition (SAM)** (WHZ < -3 or presence of edema)

## 🚀 Quick Start

### Installation

1. **Clone or download the project**
2. **Install Python 3.8 or higher**
3. **Install required packages:**
   ```bash
   pip install -r requirements.txt
   ```

### Configuration

1. **Set up environment variables** (optional):
   ```env
   SECRET_KEY=your-super-secret-key-here
   LARAVEL_API_KEY=your-laravel-api-key
   MOBILE_API_KEY=mobile-app-specific-key
   ```

### Running the API

1. **Start the FastAPI server:**
   ```bash
   python main.py
   ```
   or
   ```bash
   uvicorn main:app --host 0.0.0.0 --port 8000 --reload
   ```

2. **Access the API:**
   - API Documentation: `http://localhost:8000/docs`
   - Alternative docs: `http://localhost:8000/redoc`
   - Health check: `http://localhost:8000/health`

### Laravel Integration

See `LARAVEL_INTEGRATION_GUIDE.md` for complete integration instructions.

## 🏗️ System Architecture

```
Treatment_Model_Random_Forest/
├── main.py                      # FastAPI application with security
├── malnutrition_model.py        # Core model and WHO calculator
├── data_manager.py              # Data import/export utilities
├── treatment_protocol_manager.py # Treatment protocol management
├── model_enhancements.py        # Enhanced features (risk, uncertainty, etc.)
├── requirements.txt             # Required Python packages
├── malnutrition_model.pkl       # Trained model file
├── treatment_protocols/         # Treatment protocol JSON files
│   ├── who_standard.json
│   ├── community_based.json
│   └── hospital_intensive.json
├── LARAVEL_INTEGRATION_GUIDE.md # Laravel integration guide
├── security_guidelines.md       # Security implementation guide
└── README.md                   # This documentation
```

## 📋 API Endpoints

### System Endpoints
- `GET /` - API information and available endpoints
- `GET /health` - Health check

### Assessment Endpoints
- `POST /assess/single` - Single patient assessment (with authentication)
- `POST /assess/batch` - Batch assessment for multiple patients
- `POST /assess/upload` - File upload for batch assessment

### Enhanced Features
- `POST /risk/stratify` - Risk stratification with multi-level assessment
- `POST /predict/uncertainty` - Prediction with uncertainty quantification
- `POST /recommendations/personalized` - Personalized recommendations

### Model Management
- `GET /model/info` - Model information and capabilities
- `POST /model/train` - Retrain model with new data

### Treatment Protocols
- `GET /protocols` - Get available treatment protocols
- `POST /protocols/set` - Set active treatment protocol

### Data Management
- `GET /data/template` - Get data template for patient assessment
- `POST /data/validate` - Validate uploaded data file

### Analytics
- `GET /analytics/summary` - System analytics summary

## 🔒 Security Features

The system includes comprehensive security measures:

### Authentication
- **API Key Authentication** - Secure access with API keys
- **Rate Limiting** - 10 requests per minute per endpoint
- **Request Validation** - Input sanitization and validation

### Monitoring
- **Security Logging** - All API access logged with timestamps
- **Request Size Limits** - 1MB maximum request size
- **Slow Request Detection** - Automatic logging of requests >5 seconds

### Production Ready
- **HTTPS Support** - SSL certificate configuration ready
- **CORS Configuration** - Cross-origin request handling
- **Error Handling** - Comprehensive error responses

## 🔬 Model Details

### Features Used
- **Anthropometric**: age_months, weight, height, BMI, WHZ_score
- **Demographic**: sex, age_group
- **Household**: total_household, adults, children, twins
- **Socio-economic**: 4ps_beneficiary, municipality
- **Medical**: breastfeeding, tuberculosis, malaria, congenital_anomalies, other_medical_problems

### Model Performance
- **Algorithm**: Random Forest Classifier
- **Cross-validation**: 5-fold CV
- **Features**: Automatic importance ranking
- **Validation**: Built-in train/test split

## 🏥 Treatment Recommendations

The system follows the clinical decision flowchart and provides specific treatment recommendations:

### Severe Acute Malnutrition (SAM)
- **With edema**: Inpatient therapeutic care with stabilization protocols
- **Without edema**: Outpatient therapeutic care with RUTF (Ready-to-Use Therapeutic Food)

### Moderate Acute Malnutrition (MAM)
- Targeted supplementary feeding program
- 75 kcal/kg/day supplementation
- Regular monitoring and follow-up

### Normal
- Routine health check and nutrition care
- Preventive interventions
- Regular growth monitoring

## 📁 Data Import/Export

### Supported Formats
- **CSV**: Comma-separated values
- **Excel**: .xlsx format
- **JSON**: JavaScript Object Notation

### Data Validation
- Automatic data type conversion
- Range validation for measurements
- Standardization of categorical values
- Missing data handling

## 📊 Example API Usage

### Single Patient Assessment
```bash
curl -X POST "http://localhost:8000/assess/single" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Child",
    "age_months": 18,
    "sex": "male",
    "weight": 8.5,
    "height": 76.0,
    "municipality": "Manila",
    "total_household": 4,
    "adults": 2,
    "children": 2,
    "twins": 0,
    "four_ps_beneficiary": "Yes",
    "breastfeeding": "No",
    "edema": false,
    "tuberculosis": "No",
    "malaria": "No",
    "congenital_anomalies": "No",
    "other_medical_problems": "No"
  }'
```

### Batch Assessment
```bash
curl -X POST "http://localhost:8000/assess/batch" \
  -H "Content-Type: application/json" \
  -d '{
    "patients": [
      {
        "name": "Child 1",
        "age_months": 24,
        "sex": "female",
        "weight": 10.2,
        "height": 85.0,
        "municipality": "Quezon City",
        "total_household": 5,
        "adults": 2,
        "children": 3,
        "twins": 0,
        "four_ps_beneficiary": "No",
        "breastfeeding": "Yes",
        "edema": false,
        "tuberculosis": "No",
        "malaria": "No",
        "congenital_anomalies": "No",
        "other_medical_problems": "No"
      }
    ]
  }'
```

### File Upload
```bash
curl -X POST "http://localhost:8000/assess/upload" \
  -F "file=@patients_data.csv"
```

## 🎛️ Customization

### Adding Custom WHO Charts
You can import your own WHO reference charts through the data manager:

```python
from data_manager import DataManager
dm = DataManager()
dm.load_who_charts('custom_who_charts.xlsx')
```

### Model Retraining
Retrain the model with new data via API:

```bash
curl -X POST "http://localhost:8000/model/train" \
  -H "Content-Type: application/json" \
  -d '{
    "protocol_name": "who_standard",
    "test_size": 0.2
  }'
```

## 🚨 Important Notes

### Data Quality
- Ensure accurate measurements (weight, height)
- Verify age in months (0-60 range)
- Complete all required fields for best results

### Clinical Use
- This system is designed as a **decision support tool**
- **Always consult qualified healthcare professionals**
- Use in conjunction with clinical assessment
- Regular model validation recommended

### WHO Guidelines Compliance
- Follows WHO Child Growth Standards
- Implements standard Z-score calculations
- Adheres to malnutrition classification criteria

## 🔧 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL server status
   - Verify database credentials in `.env`
   - Ensure database schema is created

2. **Model Loading Error**
   - Check if `malnutrition_model.pkl` exists
   - Restart the API server
   - Check model file permissions

3. **API Response Errors**
   - Validate request data format
   - Check required fields
   - Review API documentation at `/docs`

### Getting Help
1. Check the API logs for error messages
2. Validate your data against the template
3. Ensure all required packages are installed
4. Review the API documentation at `/docs`

## 📈 Future Enhancements

### Planned Features
- [ ] Real-time assessment tracking
- [ ] Advanced analytics dashboard
- [ ] Mobile app integration
- [ ] Automated alert systems
- [ ] Integration with health information systems
- [ ] Multi-language support
- [ ] Advanced reporting capabilities
- [ ] Machine learning model versioning

### Contributing
This system is designed for educational and research purposes. For production use in healthcare settings, additional validation and clinical testing would be required.

## 📄 License

This project is intended for educational and research purposes. Please ensure compliance with local healthcare regulations when adapting for clinical use.

## 🙏 Acknowledgments

- WHO Child Growth Standards and Guidelines
- Scikit-learn machine learning library
- FastAPI for REST API framework
- MySQL for database management
- Plotly for interactive visualizations

---

**Note**: This system implements the clinical decision flowchart provided and follows WHO guidelines for child malnutrition assessment. It should be used as a decision support tool in conjunction with professional clinical judgment. 