import os
import mysql.connector
from mysql.connector import Error
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

def get_connection():
    """
    Establish a connection to the MySQL database using environment variables
    """
    try:
        connection = mysql.connector.connect(
            host=os.getenv('DB_HOST', 'localhost'),
            user=os.getenv('DB_USER', 'root'),
            password=os.getenv('DB_PASSWORD', ''),
            database=os.getenv('DB_NAME', 'capstone_demo'),
            autocommit=True
        )
        
        if connection.is_connected():
            return connection
        else:
            raise Error("Failed to connect to database")
            
    except Error as e:
        print(f"Error connecting to MySQL database: {e}")
        raise e

def close_connection(connection):
    """
    Close the database connection
    """
    if connection and connection.is_connected():
        connection.close()

def execute_query(query, params=None, fetch=False):
    """
    Execute a query with optional parameters
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor(dictionary=True)
        
        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)
        
        if fetch:
            result = cursor.fetchall()
            return result
        else:
            connection.commit()
            return cursor.lastrowid
            
    except Error as e:
        print(f"Error executing query: {e}")
        raise e
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()

def execute_query_one(query, params=None):
    """
    Execute a query and return only the first result
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor(dictionary=True)
        
        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)
        
        result = cursor.fetchone()
        return result
            
    except Error as e:
        print(f"Error executing query: {e}")
        raise e
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
