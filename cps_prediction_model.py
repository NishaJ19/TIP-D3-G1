import pandas as pd
import numpy as np
from prophet import Prophet
import json
from cps_model import load_data, detect_anomalies, get_latest_data

def train_and_predict(df, columns_to_predict, periods=24):
    predictions = {}
    for column in columns_to_predict:
        if column not in df.columns:
            print(f"Warning: Column '{column}' not found in the dataset. Skipping.")
            continue
        print(f"Training model for column: {column}")
        prophet_df = df[['DATETIME', column]].rename(columns={'DATETIME': 'ds', column: 'y'})
        model = Prophet()
        model.fit(prophet_df)
        future = model.make_future_dataframe(periods=periods, freq='H')
        forecast = model.predict(future)
        predictions[column] = forecast[['ds', 'yhat']].tail(periods).to_dict('records')
    return predictions

def save_results(real_time_status, anomalies, predictions):
    results = {
        "real_time_status": real_time_status,
        "anomalies": anomalies,
        "predictions": predictions
    }
    with open('cps_results.json', 'w') as f:
        json.dump(results, f, default=str)
    print("Results saved to cps_results.json")
    print("Predictions:", predictions)  # Debug print

if __name__ == "__main__":
    df = load_data('CPS_dataset.csv')
    print("Columns in the dataset:", df.columns.tolist())
    
    latest_data = get_latest_data(df)
    
    columns_to_analyze = [col for col in df.columns if col not in ['DATETIME', 'ATT_FLAG']]
    print("Columns to analyze:", columns_to_analyze)
    
    anomalies = detect_anomalies(latest_data, columns_to_analyze)
    
    # Adjust tank_columns based on actual column names
    tank_columns = [col for col in df.columns if col.startswith('L_T')]
    print("Tank columns found:", tank_columns)
    
    if not tank_columns:
        print("Warning: No tank columns (L_T*) found. Using all numerical columns for predictions.")
        tank_columns = df.select_dtypes(include=[np.number]).columns.tolist()
        print("Columns used for predictions:", tank_columns)
    
    predictions = train_and_predict(df, tank_columns)
    
    real_time_status = {
        col: {
            'current': float(latest_data[col].iloc[-1]), 
            'status': 'Anomaly' if anomalies[col].iloc[-1] else 'Normal'
        } 
        for col in columns_to_analyze
    }
    
    anomaly_messages = [
        f"Anomaly detected at {latest_data['DATETIME'].iloc[i]} in {', '.join(anomalies.columns[anomalies.iloc[i]])}"
        for i in range(len(latest_data)) if anomalies.iloc[i].any()
    ]
    
    save_results(real_time_status, anomaly_messages, predictions)
    print("CPS Analysis and Predictions completed and saved.")