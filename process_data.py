import pandas as pd
from sklearn.preprocessing import LabelEncoder

def preprocess_data(file_path):
    # Load the data
    df = pd.read_csv(file_path)
    
    # Encode categorical features
    label_encoder = LabelEncoder()
    categorical_columns = ['proto', 'service', 'state', 'attack_cat']
    for col in categorical_columns:
        if col in df.columns:
            df[col] = label_encoder.fit_transform(df[col].astype(str))

    # Handle missing values
    df = df.fillna(0)

    return df

# Example usage
if __name__ == '__main__':
    input_file = 'UNSW-NB15.csv'
    output = preprocess_data(input_file)
    print(output.head())
