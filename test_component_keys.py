import argparse
import os
from ChineseDictionary import ChineseDictionary
if __name__ == "__main__":
    all_key = set()
    dictionary = ChineseDictionary()
    for char in dictionary.data.keys():
        components = dictionary.component(char)
        if isinstance(components, dict):
            for part in components.keys():
                all_key.add(part)
                break
    # get all component keys
    print(f"components key: {all_key}")

