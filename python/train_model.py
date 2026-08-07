import sys
from ml_pipeline import TPUIRFPipeline

if __name__ == '__main__':
    data_path = sys.argv[1] if len(sys.argv) > 1 else '../storage/app/exports/full_dataset.csv'
    pipeline = TPUIRFPipeline()
    try:
        pipeline.load_model()
        print("Model loaded for retraining...")
    except:
        print("No existing model found. Training new model...")
    result = pipeline.train(data_path)
    print(f"Training complete. Accuracy: {result['accuracy']:.4f}")
