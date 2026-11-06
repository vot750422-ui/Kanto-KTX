param([switch]$ExportPdf, [switch]$Preview, [int[]]$PreviewPages = @(), [string]$DraftName = 'BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx')
$ErrorActionPreference = 'Stop'
$reportDirectory = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../chapter2'))
if ($DraftName -notmatch '^[A-Za-z0-9_]+\.docx$') { throw 'Invalid draft filename' }
$inputPath = Join-Path $reportDirectory $DraftName
$pdfPath = Join-Path $reportDirectory ([System.IO.Path]::ChangeExtension($DraftName, '.pdf'))
$word = $null
$document = $null
$originalPrintBackground = $null
$previousWordIds = @(Get-Process WINWORD -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Id)
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $word.AutomationSecurity = 3
    $originalPrintBackground = $word.Options.PrintBackground
    $word.Options.PrintBackground = $false
    $createdWordIds = @(Get-Process WINWORD -ErrorAction SilentlyContinue | Where-Object { $_.Id -notin $previousWordIds } | Select-Object -ExpandProperty Id)
    $createdWordIds | Set-Content -LiteralPath (Join-Path $PSScriptRoot 'working/word-process.txt')
    Write-Output 'Opening draft DOCX...'
    $document = $word.Documents.Open($inputPath, $false, $true, $false)
    Write-Output 'Opened; paginating...'
    $document.Repaginate()
    Write-Output 'Paginated; collecting statistics...'
    $pageCount = $document.ComputeStatistics(2)
    $wordCount = $document.ComputeStatistics(0)
    Write-Output "Word opened successfully: $pageCount pages; $wordCount words; $($document.InlineShapes.Count) figures."
    # Record page start/end text to inspect pagination without modifying the draft.
    $pageAudit = @()
    for ($pageNumber = 1; $pageNumber -le $pageCount; $pageNumber++) {
        $start = $document.GoTo(1, 1, $pageNumber).Start
        $end = if ($pageNumber -lt $pageCount) { $document.GoTo(1, 1, ($pageNumber + 1)).Start } else { $document.Content.End }
        $pageText = $document.Range($start, $end).Text -replace '[\r\a\v\f]+', ' '
        $pageAudit += "Page ${pageNumber}: " + $pageText.Substring(0, [Math]::Min(110, $pageText.Length)) + ' ... ' + $pageText.Substring([Math]::Max(0, $pageText.Length - 110))
    }
    $pageAudit | Set-Content -LiteralPath (Join-Path $PSScriptRoot 'working/pagination.txt') -Encoding UTF8
    if ($Preview) {
        Add-Type -AssemblyName System.Drawing
        $document.ActiveWindow.View.Type = 3
        $selectedPages = if ($PreviewPages.Count -gt 0) { $PreviewPages } else { @(1, 19, $pageCount) }
        foreach ($previewPage in $selectedPages) {
            if ($previewPage -lt 1 -or $previewPage -gt $pageCount) { throw "Invalid preview page: $previewPage" }
            $bits = $document.ActiveWindow.Panes.Item(1).Pages.Item($previewPage).EnhMetaFileBits
            $stream = [System.IO.MemoryStream]::new([byte[]]$bits)
            $metafile = [System.Drawing.Image]::FromStream($stream)
            $bitmap = [System.Drawing.Bitmap]::new($metafile.Width, $metafile.Height)
            $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
            $graphics.Clear([System.Drawing.Color]::White)
            $graphics.DrawImage($metafile, 0, 0, $bitmap.Width, $bitmap.Height)
            $bitmap.Save((Join-Path $PSScriptRoot "working/page-$previewPage.png"), [System.Drawing.Imaging.ImageFormat]::Png)
            $graphics.Dispose(); $bitmap.Dispose(); $metafile.Dispose(); $stream.Dispose()
        }
        Write-Output 'Saved page previews.'
    }
    if ($ExportPdf) {
        Write-Output "Exporting $pageCount pages to PDF..."
        $document.ExportAsFixedFormat($pdfPath, 17, $false, 0, 0, 1, $pageCount, 0, $true, $false, 0, $false, $true, $false)
        Write-Output "PDF exported: $pdfPath"
    }
} finally {
    if ($null -ne $document) { $document.Close(0); [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($document) }
    if ($null -ne $word) {
        if ($null -ne $originalPrintBackground) { $word.Options.PrintBackground = $originalPrintBackground }
        $word.Quit()
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($word)
    }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
