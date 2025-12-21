"""
Simple HTTP Server for Chinese Component Search
Run this script to start the web server, then open http://localhost:8000 in your browser.
"""

import http.server
import socketserver
import os
import webbrowser
from functools import partial

PORT = 8000
DIRECTORY = os.path.dirname(os.path.abspath(__file__))

class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=DIRECTORY, **kwargs)
    
    def end_headers(self):
        # Enable CORS for local development
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Cache-Control', 'no-cache')
        super().end_headers()

def main():
    os.chdir(DIRECTORY)
    
    # Check if Dictionary.json exists
    dict_path = os.path.join(DIRECTORY, "Dictionary.json")
    parent_dict_path = os.path.join(os.path.dirname(DIRECTORY), "Dictionary.json")
    
    if not os.path.exists(dict_path):
        if os.path.exists(parent_dict_path):
            # Create a symbolic link or copy the file
            print(f"正在連結字典檔案...")
            try:
                os.symlink(parent_dict_path, dict_path)
                print(f"已建立符號連結: {dict_path}")
            except (OSError, NotImplementedError):
                # If symlink fails (e.g., on Windows without admin), copy the file
                import shutil
                shutil.copy2(parent_dict_path, dict_path)
                print(f"已複製字典檔案到: {dict_path}")
        else:
            print(f"警告: 找不到 Dictionary.json")
            print(f"請將 Dictionary.json 放到 {DIRECTORY} 目錄中")
            return
    
    with socketserver.TCPServer(("", PORT), Handler) as httpd:
        url = f"http://localhost:{PORT}"
        print(f"\n🔥 中文部件查詢系統已啟動!")
        print(f"📍 伺服器地址: {url}")
        print(f"📁 服務目錄: {DIRECTORY}")
        print(f"\n按 Ctrl+C 停止伺服器...\n")
        
        # Open browser automatically
        webbrowser.open(url)
        
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print("\n\n👋 伺服器已停止")

if __name__ == "__main__":
    main()
