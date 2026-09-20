<#
.SYNOPSIS
    Finds the JDK, JMeter and OWASP ZAP on this machine and sets up the
    current PowerShell window to use them.

.DESCRIPTION
    Dot-source this at the top of every PowerShell window you use for
    testing:

        . .\testing-env.ps1

    (note the leading dot and space -- without it the variables it sets
    disappear when the script ends).

    It searches the usual install locations, checks each tool actually
    runs, and leaves these behind for the rest of the session:

        $env:JAVA_HOME   the JDK
        $env:PATH        with the JDK's bin prepended, so `java` works
        $JMeter          full path to jmeter.bat
        $ZapJar          full path to zap-<version>.jar
        $XamppRoot       the XAMPP installation
        $AppRoot         this repository's backend/ folder

    Nothing is installed or modified; if a tool is missing the script says
    which one and where it looked.

.PARAMETER JdkHome
    Override the JDK search, e.g. -JdkHome 'D:\jdk-17'.

.PARAMETER JMeterHome
    Override the JMeter search, e.g. -JMeterHome 'D:\apache-jmeter-5.6.3'.

.PARAMETER ZapHome
    Override the ZAP search, e.g. -ZapHome 'D:\ZAP_2.17.0'.

.PARAMETER XamppHome
    Override the XAMPP search, e.g. -XamppHome 'D:\xampp'.

.EXAMPLE
    . .\testing-env.ps1
    . .\testing-env.ps1 -JdkHome 'D:\jdk-17' -XamppHome 'D:\xampp'
#>
[CmdletBinding()]
param(
    [string] $JdkHome,
    [string] $JMeterHome,
    [string] $ZapHome,
    [string] $XamppHome
)

$ErrorActionPreference = 'Stop'
$problems = @()

function Find-First {
    param([string[]] $Patterns)
    foreach ($p in $Patterns) {
        $hit = Get-Item $p -ErrorAction SilentlyContinue |
               Sort-Object Name -Descending |
               Select-Object -First 1
        if ($hit) { return $hit.FullName }
    }
    return $null
}

# --- The application ---------------------------------------------------
# This script lives in backend/tools/loadtest.
$AppRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
if (-not (Test-Path (Join-Path $AppRoot 'artisan'))) {
    $problems += "The folder above this script ($AppRoot) has no artisan file, so it is not the Laravel application root."
}

# --- JDK ---------------------------------------------------------------
# JMeter and ZAP both need Java 17 or newer.
$jdk = $JdkHome
if (-not $jdk) {
    $jdk = Find-First @(
        'C:\fablab-tools\jdk*'
        'C:\Program Files\Eclipse Adoptium\jdk-17*'
        'C:\Program Files\Eclipse Adoptium\jdk-*'
        'C:\Program Files\Java\jdk-*'
        'C:\Program Files\Microsoft\jdk-*'
        'C:\Program Files\Amazon Corretto\jdk*'
        'C:\tools\jdk*'
    )
}
if ($jdk -and -not (Test-Path (Join-Path $jdk 'bin\java.exe'))) {
    # Some installers nest the JDK one level down.
    $jdk = Find-First @((Join-Path $jdk '*\bin\java.exe'))
    if ($jdk) { $jdk = Split-Path (Split-Path $jdk) }
}
if ($jdk) {
    $env:JAVA_HOME = $jdk
    if ($env:PATH -notlike "*$jdk\bin*") { $env:PATH = "$jdk\bin;$env:PATH" }
} else {
    $problems += 'No JDK found. Install Temurin 17 (https://adoptium.net/temurin/releases/?version=17), or pass -JdkHome.'
}

# --- JMeter ------------------------------------------------------------
$jm = $JMeterHome
if (-not $jm) {
    $jm = Find-First @(
        'C:\fablab-tools\apache-jmeter-*'
        'C:\tools\apache-jmeter-*'
        'C:\apache-jmeter-*'
        "$env:USERPROFILE\apache-jmeter-*"
        "$env:USERPROFILE\Downloads\apache-jmeter-*"
    )
}
$JMeter = if ($jm) { Join-Path $jm 'bin\jmeter.bat' } else { $null }
if (-not ($JMeter -and (Test-Path $JMeter))) {
    $problems += 'No JMeter found. Unzip apache-jmeter-5.6.3 into C:\fablab-tools, or pass -JMeterHome.'
    $JMeter = $null
}

# --- OWASP ZAP ---------------------------------------------------------
$zapDir = $ZapHome
if (-not $zapDir) {
    $zapDir = Find-First @(
        'C:\fablab-tools\ZAP*'
        'C:\Program Files\ZAP\Zed Attack Proxy'
        'C:\Program Files\ZAP\*'
        'C:\Program Files (x86)\ZAP\Zed Attack Proxy'
        "$env:USERPROFILE\ZAP*"
    )
}
$ZapJar = if ($zapDir) { Find-First @((Join-Path $zapDir 'zap-*.jar'), (Join-Path $zapDir 'zap.jar')) } else { $null }
if (-not $ZapJar) {
    $problems += 'No ZAP found. Install from https://www.zaproxy.org/download/, or pass -ZapHome.'
}

# --- XAMPP -------------------------------------------------------------
$XamppRoot = $XamppHome
if (-not $XamppRoot) {
    $XamppRoot = Find-First @('C:\xampp', 'D:\xampp', 'C:\Program Files\xampp')
}
if (-not ($XamppRoot -and (Test-Path (Join-Path $XamppRoot 'apache\bin\httpd.exe')))) {
    $problems += 'No XAMPP found. The app has to be served by Apache for these tests; pass -XamppHome.'
    $XamppRoot = $null
}

# --- Report ------------------------------------------------------------
Write-Host ''
Write-Host 'FabLab testing environment' -ForegroundColor Cyan
Write-Host '--------------------------'

$javaVersion = if ($env:JAVA_HOME) {
    try { (& java -version 2>&1 | Select-Object -First 1) } catch { 'found, but would not run' }
} else { $null }

$rows = [ordered]@{
    'Application' = $AppRoot
    'JDK'         = $(if ($env:JAVA_HOME) { "$env:JAVA_HOME  [$javaVersion]" } else { 'NOT FOUND' })
    'JMeter'      = $(if ($JMeter)    { $JMeter }    else { 'NOT FOUND' })
    'ZAP'         = $(if ($ZapJar)    { $ZapJar }    else { 'NOT FOUND' })
    'XAMPP'       = $(if ($XamppRoot) { $XamppRoot } else { 'NOT FOUND' })
}
foreach ($k in $rows.Keys) {
    $colour = if ($rows[$k] -eq 'NOT FOUND') { 'Red' } else { 'Gray' }
    Write-Host ("  {0,-12} {1}" -f $k, $rows[$k]) -ForegroundColor $colour
}

if ($problems) {
    Write-Host ''
    Write-Host 'Not ready yet:' -ForegroundColor Yellow
    $problems | ForEach-Object { Write-Host "  - $_" -ForegroundColor Yellow }
    Write-Host ''
} else {
    Write-Host ''
    Write-Host 'All four found. Next: build the Apache config with' -ForegroundColor Green
    Write-Host '    .\make-apache-conf.ps1' -ForegroundColor Green
    Write-Host ''
}
