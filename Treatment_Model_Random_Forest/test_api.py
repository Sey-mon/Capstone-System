"""
Test script for FastAPI Malnutrition Assessment Server
"""

import requests
import json
import sys

def test_api():
    """Test the FastAPI endpoints"""
    base_url = "http://127.0.0.1:8001"
    api_key = "malnutrition-api-key-2025"
    
    print("🧪 Testing Malnutrition Assessment API")
    print("=" * 50)
    
    # Test 1: Health check (no auth required)
    print("\n1. Testing health check...")
    try:
        response = requests.get(f"{base_url}/health")
        if response.status_code == 200:
            print("✅ Health check passed")
            print(f"   Status: {response.json().get('status')}")
        else:
            print(f"❌ Health check failed: {response.status_code}")
    except Exception as e:
        print(f"❌ Health check error: {e}")
    
    # Test 2: Authentication
    print("\n2. Testing authentication...")
    try:
        auth_response = requests.post(f"{base_url}/auth/token", 
                                    json={"api_key": api_key})
        if auth_response.status_code == 200:
            token = auth_response.json()["access_token"]
            print("✅ Authentication successful")
            print(f"   Token obtained: {token[:20]}...")
        else:
            print(f"❌ Authentication failed: {auth_response.status_code}")
            print(f"   Error: {auth_response.text}")
            return
    except Exception as e:
        print(f"❌ Authentication error: {e}")
        return
    
    # Headers for authenticated requests
    headers = {"Authorization": f"Bearer {token}"}
    
    # Test 3: Malnutrition assessment only
    print("\n3. Testing malnutrition assessment...")
    child_data = {
        "age_months": 18,
        "weight_kg": 9.5,
        "height_cm": 78.0,
        "gender": "female",
        "muac_cm": 12.5,
        "has_edema": False
    }
    
    try:
        response = requests.post(f"{base_url}/assess/malnutrition-only", 
                               json=child_data, headers=headers)
        if response.status_code == 200:
            result = response.json()
            print("✅ Malnutrition assessment successful")
            print(f"   Diagnosis: {result.get('primary_diagnosis', 'N/A')}")
            print(f"   Risk Level: {result.get('risk_level', 'N/A')}")
        else:
            print(f"❌ Assessment failed: {response.status_code}")
            print(f"   Error: {response.text}")
    except Exception as e:
        print(f"❌ Assessment error: {e}")
    
    # Test 4: Complete assessment with treatment plan
    print("\n4. Testing complete assessment...")
    complete_request = {
        "child_data": child_data,
        "socioeconomic_data": {
            "is_4ps_beneficiary": True,
            "household_size": 5,
            "has_electricity": True,
            "has_clean_water": False,
            "mother_education": "secondary"
        }
    }
    
    try:
        response = requests.post(f"{base_url}/assess/complete", 
                               json=complete_request, headers=headers)
        if response.status_code == 200:
            result = response.json()
            print("✅ Complete assessment successful")
            print(f"   Assessment: {result.get('assessment', {}).get('primary_diagnosis', 'N/A')}")
            treatment_plan = result.get('treatment_plan', {})
            print(f"   Treatment phases: {len(treatment_plan.get('treatment_phases', {}))}")
            print(f"   Immediate actions: {len(treatment_plan.get('immediate_actions', []))}")
        else:
            print(f"❌ Complete assessment failed: {response.status_code}")
            print(f"   Error: {response.text}")
    except Exception as e:
        print(f"❌ Complete assessment error: {e}")
    
    # Test 5: WHO standards
    print("\n5. Testing WHO standards...")
    try:
        response = requests.get(f"{base_url}/reference/who-standards/female/wfa", 
                              headers=headers)
        if response.status_code == 200:
            result = response.json()
            print("✅ WHO standards retrieval successful")
            print(f"   Gender: {result.get('gender')}")
            print(f"   Indicator: {result.get('indicator')}")
            print(f"   Data points: {len(result.get('data', []))}")
        else:
            print(f"❌ WHO standards failed: {response.status_code}")
    except Exception as e:
        print(f"❌ WHO standards error: {e}")
    
    # Test 6: Treatment protocols
    print("\n6. Testing treatment protocols...")
    try:
        response = requests.get(f"{base_url}/reference/treatment-protocols", 
                              headers=headers)
        if response.status_code == 200:
            result = response.json()
            print("✅ Treatment protocols retrieval successful")
            protocols = result.get('protocols', {})
            print(f"   Available protocols: {len(protocols.get('protocols', []))}")
        else:
            print(f"❌ Treatment protocols failed: {response.status_code}")
    except Exception as e:
        print(f"❌ Treatment protocols error: {e}")
    
    print("\n" + "=" * 50)
    print("🎯 API Testing Complete!")
    print("\nTo start the API server, run:")
    print("   python api_server.py")
    print("\nAPI Documentation available at:")
    print("   http://127.0.0.1:8001/docs")

if __name__ == "__main__":
    print("📋 This is a test script for the API.")
    print("⚠️  Start the API server first with: python api_server.py")
    print("Then run this test with: python test_api.py")
    
    # Check if we should run the test
    if len(sys.argv) > 1 and sys.argv[1] == "run":
        test_api()
    else:
        print("\nTo run the test, use: python test_api.py run")
