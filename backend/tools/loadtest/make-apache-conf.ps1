<#
.SYNOPSIS
    Builds a standalone Apache configuration that serves FabLab on its own
    port, for load and security testing, without touching the XAMPP service.

.DESCRIPTION
    `php artisan serve` is single-threaded on Windows, so a load test against
    it measures the request queue rather than the application. These tests
    need a threaded Apache.

    Rather than edit the machine's XAMPP configuration -- which would need
    administrator rights to restart, and would disturb whatever else the
    owner of the machine uses XAMPP for -- this script copies XAMPP's own
    httpd.conf and makes four changes to the copy:

      1. Listen 80                          -> Listen <Port>
      2. Include conf/extra/httpd-vhosts.conf  commented out
      3. Include conf/extra/httpd-ssl.conf     commented out  (frees 443)
      4. Appends its own PidFile, logs, and a VirtualHost whose DocumentRoot
         is the application's public/ folder.

    Everything else -- most importantly the PHP module, which XAMPP loads
    through conf/extra/httpd-xampp.conf -- is inherited unchanged, so the
    copy works on any machine where XAMPP itself works.

    The result runs in the foreground, needs no administrator rights, and
    leaves the XAMPP service on port 80 alone.

.EXAMPLE
    .\make-apache-conf.ps1
    Uses C:\xampp, this repository's public/ folder, and port 8080.

.EXAMPLE
    .\make-apache-conf.ps1 -XamppRoot D:\xampp -Port 8090
    For a machine where XAMPP lives elsewhere or 8080 is already taken.
#>
[CmdletBinding()]
param(
    # Where XAMPP is installed on THIS machine.
    [string] $XamppRoot = 'C:\xampp',

    # The application's public/ folder. Defaults to this repository's.
    [string] $AppPublic,

    # Where to write the generated configuration and its log files.
    [string] $OutDir = (Join-Path $env:USERPROFILE 'fablab-test'),

    # The port the test instance listens on. Must be free.
    [int] $Port = 8080
)

$ErrorActionPreference = 'Stop'

# --- Work out the paths ------------------------------------------------
if (-not $AppPublic) {
    # This script lives in backend/tools/loadtest, so public/ is two up.
    $AppPublic = Join-Path $PSScriptRoot '..\..\public'
}

$AppPublic = (Resolve-Path $AppPublic -ErrorAction SilentlyContinue).Path
if (-not $AppPublic) {
    throw "Could not find the application's public/ folder. Pass -AppPublic with the full path to it."
}
if (-not (Test-Path (Join-Path $AppPublic 'index.php'))) {
    throw "$AppPublic does not contain index.php, so it is not the Laravel public/ folder."
}

$stockConf = Join-Path $XamppRoot 'apache\conf\httpd.conf'
if (-not (Test-Path $stockConf)) {
    throw "No Apache config at $stockConf. Pass -XamppRoot with the folder XAMPP is installed in."
}

$httpd = Join-Path $XamppRoot 'apache\bin\httpd.exe'
if (-not (Test-Path $httpd)) {
    throw "No httpd.exe at $httpd. Is this a complete XAMPP installation?"
}

# --- Is the port free? -------------------------------------------------
$inUse = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
if ($inUse) {
    $owner = (Get-Process -Id $inUse[0].OwningProcess -ErrorAction SilentlyContinue).ProcessName
    throw "Port $Port is already in use by $owner (PID $($inUse[0].OwningProcess)). Stop it, or re-run with -Port on a free port."
}

New-Item -ItemType Directory -Force $OutDir | Out-Null
$OutDir  = (Resolve-Path $OutDir).Path
$outFile = Join-Path $OutDir 'httpd-fablab.conf'

# Apache wants forward slashes in directives, on every platform.
$docRoot = $AppPublic.Replace('\', '/')
$logBase = $OutDir.Replace('\', '/')

# --- Transform a copy of XAMPP's own config ----------------------------
$conf = Get-Content $stockConf -Raw

$conf = $conf -replace '(?m)^\s*Listen\s+80\s*$', "Listen $Port"
$conf = $conf -replace '(?m)^(\s*)(Include\s+conf/extra/httpd-vhosts\.conf)', '$1#$2'
$conf = $conf -replace '(?m)^(\s*)(Include\s+conf/extra/httpd-ssl\.conf)',    '$1#$2'

if ($conf -notmatch "(?m)^Listen $Port\s*$") {
    throw "Could not find 'Listen 80' in $stockConf to rewrite. Edit the generated file by hand."
}

$conf += @"

# ---------------------------------------------------------------------
# Appended by make-apache-conf.ps1 for FabLab performance/security testing.
# Generated $(Get-Date -Format 'yyyy-MM-dd HH:mm') from $stockConf
# ---------------------------------------------------------------------
PidFile   "$logBase/httpd-fablab.pid"
ErrorLog  "$logBase/httpd-fablab-error.log"
CustomLog "$logBase/httpd-fablab-access.log" common
<VirtualHost *:$Port>
    ServerName   fablab.local
    DocumentRoot "$docRoot"
    <Directory "$docRoot">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

Set-Content -Path $outFile -Value $conf -Encoding ASCII

# --- Check Apache is happy with it -------------------------------------
Write-Host "Wrote $outFile" -ForegroundColor Green
Write-Host "  serving $docRoot on port $Port`n"

$check = & $httpd -f $outFile -t 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host 'Apache rejected the generated configuration:' -ForegroundColor Red
    $check | ForEach-Object { Write-Host "  $_" }
    throw 'Configuration test failed.'
}
Write-Host "Apache config test: $check" -ForegroundColor Green

Write-Host @"

Start the test server with (leave this window open, Ctrl+C to stop):

    & '$httpd' -f '$outFile' -D FOREGROUND

Then check it answers:

    (Invoke-WebRequest http://127.0.0.1:$Port/ -UseBasicParsing).StatusCode

"@
