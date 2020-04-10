#!/usr/bin/env python3
#encoding=utf-8

import json

with open('Dictionary.json', 'r') as read_file:
    dict_data = json.load(read_file)
   
char = '姚'
if char in dict_data:
    print(char, ":", dict_data[char])

char = '俊'
if char in dict_data:
    print(char, ":", dict_data[char])