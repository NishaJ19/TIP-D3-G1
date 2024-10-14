import pandas as pd
import numpy as np
import joblib
import json
from sklearn.ensemble import IsolationForest
from cps_prediction_model import load_and_prepare_data, train_and_predict, save_predictions

# Load and process CPS data
def load_data(file_path):
    return pd.read_csv(file_path)

# Predict system status using anomaly detection
def detect_anomalies(df, model):
    # Create a list of columns to drop
    columns_to_drop = ['DATETIME']
    if 'ATT_FLAG' in df.columns:
        columns_to_drop.append('ATT_FLAG')
    
    # Drop the columns and create features
    features = df.drop(columns=columns_to_drop, errors='ignore')
    
    df['Anomaly'] = model.predict(features)
    return df

# Helper function to convert numpy types to Python types
def convert_to_serializable(obj):
    if isinstance(obj, np.integer):
        return int(obj)
    elif isinstance(obj, np.floating):
        return float(obj)
    elif isinstance(obj, np.ndarray):
        return obj.tolist()
    else:
        return obj

# Save results to JSON
def save_results(df):
    real_time_status = {}
    anomalies = []

    # Collect real-time status
    for column in df.columns:
        if column not in ['DATETIME', 'ATT_FLAG', 'Anomaly']:
            real_time_status[column] = {
                'current': convert_to_serializable(df[column].iloc[-1]),
                'status': 'Anomaly' if df['Anomaly'].iloc[-1] == -1 else 'Normal'
            }

    # Collect detected anomalies
    for index, row in df[df['Anomaly'] == -1].iterrows():
        anomalies.append(f"Anomaly detected at {row['DATETIME']}")

    # Save to JSON
    results = {
        "real_time_status": real_time_status,
        "anomalies": anomalies
    }
    with open('cps_results.json', 'w') as f:
        json.dump(results, f, default=convert_to_serializable)

# Main execution
if __name__ == "__main__":
    model = joblib.load('cps_model.joblib')
    data = load_data('CPS_dataset.csv')
    analyzed_data = detect_anomalies(data, model)
    save_results(analyzed_data)
    
    # Generate predictions
    prepared_data = load_and_prepare_data('CPS_dataset.csv')
    forecast, predicted_column = train_and_predict(prepared_data)  # It will choose the first numerical column
    save_predictions(forecast, predicted_column)
    
    print(f"CPS Analysis and Predictions for {predicted_column} completed and saved.")