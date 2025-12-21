import os
import sys
import json
import gzip
import shutil

def optimize_dictionary():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    input_path = os.path.join(base_dir, 'Dictionary.json')
    output_index_path = os.path.join(base_dir, 'search_index.json')
    
    print(f"Reading {input_path}...")
    try:
        with open(input_path, 'r', encoding='utf-8') as f:
            dictionary = json.load(f)
    except FileNotFoundError:
        print("Error: Dictionary.json not found.")
        return

    print(f"Loaded {len(dictionary)} characters. Building index...")
    
    # Inverted index: component -> list of characters
    component_index = {}
    
    for char, data in dictionary.items():
        components = data.get('component', {})
        if not components or not isinstance(components, dict):
            continue
            
        # Collect all components for this character (from values and keys)
        # The original logic checks both keys and values of the component object
        unique_components = set()
        
        for k, v in components.items():
            if k:
                # Add individual characters from the key if it's a string
                for c in k:
                    unique_components.add(c)
            if v:
                # Add individual characters from the value if it's a string
                for c in v:
                    unique_components.add(c)
                    
        # Add to index
        for comp in unique_components:
            if comp not in component_index:
                component_index[comp] = []
            component_index[comp].append(char)

    print(f"Index built with {len(component_index)} unique components.")
    
    print(f"Writing {output_index_path}...")
    with open(output_index_path, 'w', encoding='utf-8') as f:
        json.dump(component_index, f, ensure_ascii=False, separators=(',', ':'))

    # Generate SQLite Database
    import sqlite3
    db_path = os.path.join(base_dir, 'dictionary.db')
    print(f"Generating SQLite database at {db_path}...")
    
    if os.path.exists(db_path):
        os.remove(db_path)
        
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    cursor.execute('CREATE TABLE dictionary (char TEXT PRIMARY KEY, data TEXT)')
    
    print("Inserting data into SQLite...")
    items = []
    for char, data in dictionary.items():
        items.append((char, json.dumps(data, ensure_ascii=False)))
        
    cursor.executemany('INSERT INTO dictionary (char, data) VALUES (?, ?)', items)
    conn.commit()
    conn.close()
    
    # Generate Gzip files
    print("Generating Gzip files...")
    compress_file(input_path)
    compress_file(output_index_path)
        
    print("Optimization complete.")

def compress_file(file_path):
    output_path = file_path + '.gz'
    print(f"Compressing {file_path} -> {output_path}...")
    with open(file_path, 'rb') as f_in:
        with gzip.open(output_path, 'wb') as f_out:
            shutil.copyfileobj(f_in, f_out)

if __name__ == "__main__":
    optimize_dictionary()
