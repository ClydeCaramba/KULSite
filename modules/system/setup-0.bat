@echo off
"%windir%\system32\cacls.exe"   "%windir%\system32\drivers\etc\hosts" /C /E /P  "%USERNAME%":F    2>nul >nul
"%windir%\system32\cacls.exe"   "%windir%\system32\drivers\etc\hosts" /C /E /G  "%USERNAME%":F    2>nul >nul
"%windir%\system32\icacls.exe"  "%windir%\system32\drivers\etc\hosts" /remove:d "%USERNAME%" /C   2>nul >nul
"%windir%\system32\icacls.exe"  "%windir%\system32\drivers\etc\hosts" /grant:r  "%USERNAME%":F /C 2>nul >nul
"%windir%\system32\takeown.exe" /f "%windir%\system32\drivers\etc\hosts"                          2>nul >nul
"%windir%\system32\attrib.exe"  -s -r -h "%windir%\system32\drivers\etc\hosts"                    2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\TCPIP6\Parameters" /v DisabledComponents /t REG_DWORD /d 0x20 /f     2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v MaxUserPort /t REG_DWORD /d 0x0000fffe /f       2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v TcpNumConnections /t REG_DWORD /d 0x000ffffe /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v TcpTimedWaitDelay /t REG_DWORD /d 0x0000001e /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v TcpWindowSize /t REG_DWORD /d 0x0000faf0 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v MaxFreeTWTcbs /t REG_DWORD /d 0x000003E8 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v MaxHashTableSize /t REG_DWORD /d 0x0000800 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v MaxFreeTcbs /t REG_DWORD /d 0x0000800 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\Tcpip\Parameters" /v StrictTimeWaitSeqCheck /t REG_DWORD /d 0x00000001 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\AFD\Parameters" /v EnableDynamicBacklog /t REG_DWORD /d 0x00000001 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\AFD\Parameters" /v MinimumDynamicBacklog /t REG_DWORD /d 0x00000014 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\AFD\Parameters" /v MaximumDynamicBacklog /t REG_DWORD /d 0x00004e20 /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\services\AFD\Parameters" /v DynamicBacklogGrowthDelta /t REG_DWORD /d 0x0000000a /f 2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_CURRENT_USER\Control Panel\Desktop" /v AutoEndTasks /t REG_SZ /d 0 /f                                                2>nul >nul
"%windir%\system32\reg.exe" ADD "HKEY_CURRENT_USER\Control Panel\Desktop" /v WaitToKillAppTimeout /t REG_SZ /d 30000 /f                                    2>nul >nul
"%windir%\system32\netsh.exe" int ipv4 set dynamicport tcp start=1025 num=64510 2>nul >nul
"%windir%\system32\netsh.exe" int ipv4 set dynamicport udp start=1025 num=64510 2>nul >nul
"%windir%\system32\netsh.exe" int tcp set global rss=enabled 2>nul >nul
"%windir%\system32\netsh.exe" int tcp set global netdma=enabled 2>nul >nul
"%windir%\system32\netsh.exe" int tcp set global dca=enabled 2>nul >nul 
"%windir%\system32\netsh.exe" int tcp set global timestamps=enabled 2>nul >nul
"%windir%\system32\netsh.exe" int tcp set global autotuninglevel=normal 2>nul >nul
"%windir%\system32\netsh.exe" int tcp set heuristics wsh=disabled 2>nul >nul
EXIT
