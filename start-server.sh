#!/bin/bash
export PATH="/c/Users/Julian/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe:$PATH"
cd "$(dirname "$0")"
"/c/Users/Julian/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe" artisan serve --host=127.0.0.1 --port=8000
