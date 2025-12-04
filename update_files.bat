@echo off
setlocal enabledelayedexpansion

echo Memperbarui semua file materi untuk menggunakan handler aman...

REM Daftar file yang perlu diperbarui
set "files=dashboard\pages\matematika_kelas11.php dashboard\pages\matematika_kelas12.php dashboard\pages\fisika_kelas11.php dashboard\pages\fisika_kelas12.php dashboard\pages\kimia_kelas10.php dashboard\pages\kimia_kelas11.php dashboard\pages\kimia_kelas12.php dashboard\pages\biologi_kelas10.php dashboard\pages\biologi_kelas11.php dashboard\pages\biologi_kelas12.php"

for %%f in (%files%) do (
    if exist "%%f" (
        echo Memperbarui: %%f
        powershell -Command "(gc '%%f') -replace 'href=\"`$php echo `$(material|\\[\\'file_path\\'\\])', 'href=\"view_material.php?file=`$php echo urlencode(`$(material|\\[\\'file_path\\'\\])&id=`$php echo `$(material|\\[\\'id\\'\\])' | Out-File -encoding UTF8 '%%f.tmp'"
        if exist "%%f.tmp" (
            move /Y "%%f.tmp" "%%f"
        )
    ) else (
        echo File tidak ditemukan: %%f
    )
)

echo.
echo Proses update selesai!
pause