# ========================================================================
# SISTEMA CERBERO - Servidor Localhost Nativo em PowerShell
# Emula o sistema na porta 8080 (http://localhost:8080/)
# ========================================================================

$Port = 8080
$RootPath = $PSScriptRoot
if (-not $RootPath) {
    $RootPath = "C:\Users\Aluno\.gemini\antigravity\scratch\cerbero"
}

$Listener = New-Object System.Net.HttpListener
$Listener.Prefixes.Add("http://localhost:$Port/")

try {
    $Listener.Start()
    Write-Host ""
    Write-Host "==========================================================" -ForegroundColor Green
    Write-Host " [CERBERO] Servidor Web Localhost Ativo!" -ForegroundColor Green
    Write-Host " Acesse no seu navegador: http://localhost:$Port/" -ForegroundColor Yellow
    Write-Host " Pressione Ctrl+C para encerrar o servidor." -ForegroundColor DarkGray
    Write-Host "==========================================================" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "Falha ao iniciar o servidor na porta $Port : $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

$MimeTypes = @{
    ".html" = "text/html; charset=utf-8"
    ".htm"  = "text/html; charset=utf-8"
    ".css"  = "text/css; charset=utf-8"
    ".js"   = "application/javascript; charset=utf-8"
    ".json" = "application/json; charset=utf-8"
    ".png"  = "image/png"
    ".jpg"  = "image/jpeg"
    ".svg"  = "image/svg+xml"
    ".ico"  = "image/x-icon"
}

while ($Listener.IsListening) {
    try {
        $Context = $Listener.GetContext()
        $Request = $Context.Request
        $Response = $Context.Response

        $RawUrl = $Request.Url.AbsolutePath
        $CleanPath = $RawUrl.TrimStart('/')

        Write-Host "[$((Get-Date).ToString('HH:mm:ss'))] $($Request.HttpMethod) $RawUrl" -ForegroundColor Cyan

        # Roteamento inteligente
        $FilePath = $null

        if ([string]::IsNullOrWhiteSpace($CleanPath) -or $CleanPath -eq "index.php" -or $CleanPath -eq "index.html" -or $CleanPath -eq "dashboard") {
            $FilePath = Join-Path $RootPath "emulador_web.html"
        } elseif ($CleanPath -eq "preview.html") {
            $FilePath = Join-Path $RootPath "preview.html"
        } else {
            $Candidate = Join-Path $RootPath $CleanPath
            if (Test-Path $Candidate -PathType Leaf) {
                $FilePath = $Candidate
            } else {
                # Fallback amigável para o emulador
                $FilePath = Join-Path $RootPath "emulador_web.html"
            }
        }

        if (Test-Path $FilePath -PathType Leaf) {
            $Extension = [System.IO.Path]::GetExtension($FilePath).ToLower()
            $ContentType = $MimeTypes[$Extension]
            if (-not $ContentType) { $ContentType = "text/html; charset=utf-8" }

            $Bytes = [System.IO.File]::ReadAllBytes($FilePath)
            $Response.ContentType = $ContentType
            $Response.ContentLength64 = $Bytes.Length
            $Response.StatusCode = 200
            $Response.AddHeader("Access-Control-Allow-Origin", "*")
            $Response.AddHeader("Cache-Control", "no-cache, no-store, must-revalidate")
            $Response.OutputStream.Write($Bytes, 0, $Bytes.Length)
        } else {
            $Response.StatusCode = 404
            $NotFoundMsg = [System.Text.Encoding]::UTF8.GetBytes("<h1>404 - Arquivo não encontrado no Cerbero</h1>")
            $Response.ContentType = "text/html; charset=utf-8"
            $Response.ContentLength64 = $NotFoundMsg.Length
            $Response.OutputStream.Write($NotFoundMsg, 0, $NotFoundMsg.Length)
        }

        $Response.OutputStream.Close()
    } catch {
        # Silencia exceções normais de desconexão de socket
    }
}
