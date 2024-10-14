import pandas as pd
from sklearn.ensemble import IsolationForest
import joblib

def load_data(file_path):
    df = pd.read_csv(file_path)
    # Create a list of columns to drop
    columns_to_drop = ['DATETIME']
    if 'ATT_FLAG' in df.columns:
        columns_to_drop.append('ATT_FLAG')
    
    # Drop the columns and create features
    features = df.drop(columns=columns_to_drop, errors='ignore')
    return features

def train_isolation_forest(data):
    model = IsolationForest(contamination=0.1, random_state=42)
    model.fit(data)
    return model

if __name__ == "__main__":
    # Load the data
    data = load_data('CPS_dataset.csv')
    
    # Train the model
    model = train_isolation_forest(data)
    
    # Save the model
    joblib.dump(model, 'cps_model.joblib')
    
    print("Model trained and saved as cps_model.joblib")