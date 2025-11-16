$ExcelFile = "C:\xampp\htdocs\bibliSM\rptListadoEstudiantes.xlsx"
$OutputCSV = "C:\xampp\htdocs\bibliSM\rptListadoEstudiantes_converted.csv"

try {
    $Excel = New-Object -ComObject Excel.Application
    $Excel.Visible = $false
    $Workbook = $Excel.Workbooks.Open($ExcelFile)
    $Worksheet = $Workbook.Sheets.Item(1)
    
    # Export to CSV
    $Worksheet.SaveAs($OutputCSV, 6)  # 6 = xlCSV format
    
    $Workbook.Close($false)
    $Excel.Quit()
    
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($Excel) | Out-Null
    
    Write-Host "CSV creado exitosamente: $OutputCSV"
    
    # Read and display first 20 lines
    Write-Host "`n=== PRIMERAS 20 LINEAS DEL CSV ===`n"
    $lines = @(Get-Content $OutputCSV | Select-Object -First 20)
    $count = 0
    foreach ($line in $lines) {
        $count++
        Write-Host "LINEA $count : $line"
    }
    
} catch {
    Write-Host "Error: $_"
}
