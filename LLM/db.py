import os
from dotenv import load_dotenv

# Use the mysql.connector pooling module to provide a thread-safe connection pool
try:
    import mysql.connector
    from mysql.connector import Error
    from mysql.connector import pooling
except Exception:
    # Keep errors visible when importing in environments without mysql-connector
    raise

# Load environment variables
load_dotenv()

# Create a module-level connection pool on first import
_pool = None

def _init_pool():
    global _pool
    if _pool is not None:
        return _pool

    db_config = {
        'host': os.getenv('DB_HOST', 'localhost'),
        'user': os.getenv('DB_USER', 'capstone_user'),
        'password': os.getenv('DB_PASSWORD', ''),
        'database': os.getenv('DB_NAME', 'capstone_shares'),
        'autocommit': True,
    }

    pool_size = int(os.getenv('DB_POOL_SIZE', '10'))

    try:
        _pool = pooling.MySQLConnectionPool(
            pool_name="capstone_pool",
            pool_size=pool_size,
            pool_reset_session=True,
            **db_config
        )
        return _pool
    except Error as e:
        print(f"Error creating MySQL connection pool: {e}")
        raise

def get_connection():
    """Return a connection from the module-level pool."""
    global _pool
    if _pool is None:
        _init_pool()
    try:
        conn = _pool.get_connection()
        # Ensure connection is alive; attempt reconnect if needed
        try:
            conn.ping(reconnect=True, attempts=3, delay=1)
        except Exception:
            # If ping fails, try to close and get another connection
            try:
                conn.close()
            except Exception:
                pass
            conn = _pool.get_connection()
        return conn
    except Error as e:
        print(f"Error getting pooled connection: {e}")
        raise

def close_connection(connection):
    """Close or release a pooled connection."""
    try:
        if connection:
            connection.close()
    except Exception:
        pass

def execute_query(query, params=None, fetch=False):
    connection = None
    cursor = None
    try:
        connection = get_connection()
        cursor = connection.cursor(dictionary=True)
        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)

        if fetch:
            return cursor.fetchall()
        else:
            connection.commit()
            return cursor.lastrowid
    except Error as e:
        print(f"Error executing query: {e}")
        raise
    finally:
        try:
            if cursor:
                cursor.close()
        except Exception:
            pass
        try:
            if connection:
                connection.close()
        except Exception:
            pass

def execute_query_one(query, params=None):
    connection = None
    cursor = None
    try:
        connection = get_connection()
        cursor = connection.cursor(dictionary=True)
        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)
        return cursor.fetchone()
    except Error as e:
        print(f"Error executing query: {e}")
        raise
    finally:
        try:
            if cursor:
                cursor.close()
        except Exception:
            pass
        try:
            if connection:
                connection.close()
        except Exception:
            pass
