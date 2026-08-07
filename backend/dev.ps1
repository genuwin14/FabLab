<#
.SYNOPSIS
    FabLab - Windows dev launcher.

.DESCRIPTION
    Runs the three dev processes side by side in THIS window, with each
    line tagged and coloured by which process produced it:

        php artisan serve         - web server
        php artisan schedule:work - scheduler, daily overdue-PO check
        php artisan queue:listen  - queue worker

    This is the Node-free equivalent of "composer dev". That script does
    the same three plus pail and Vite, but shells out to npx concurrently
    and so needs Node installed. This one needs only PHP.

    Press Ctrl+C to stop all three at once. If any one of them dies on its
    own, the other two are shut down too, so you never get a half-running
    stack.

.PARAMETER Port
    Port for the web server. Defaults to 8000.

.EXAMPLE
    .\dev.ps1
    .\dev.ps1 -Port 8080

.NOTES
    Double-clicking a .ps1 opens it in Notepad rather than running it, and
    the default execution policy blocks scripts anyway. Use dev.bat, which
    is a one-line shim around this file.
#>

[CmdletBinding()]
param(
    [int]$Port = 8000
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $root

Write-Host ''
Write-Host '  =====================================================' -ForegroundColor DarkGray
Write-Host '    FabLab - starting dev processes'
Write-Host '  =====================================================' -ForegroundColor DarkGray
Write-Host ''

# --- Preflight --------------------------------------------------------

function Fail($message, $fix) {
    Write-Host "  [FAIL] $message" -ForegroundColor Red
    Write-Host "         $fix"     -ForegroundColor Yellow
    Write-Host ''
}

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Fail 'PHP was not found on your PATH.' 'Install PHP 8.2+ or add it to PATH - see README section 1.'
    exit 1
}
if (-not (Test-Path (Join-Path $root 'vendor\autoload.php'))) {
    Fail 'vendor\ is missing - dependencies are not installed.' 'Run:  composer install'
    exit 1
}
if (-not (Test-Path (Join-Path $root '.env'))) {
    Fail '.env is missing.' 'Run:  copy .env.example .env    then:  php artisan key:generate'
    exit 1
}

# --- Process table ----------------------------------------------------

$specs = @(
    [pscustomobject]@{ Name = 'serve';    Color = 'Cyan';    Arguments = "artisan serve --port=$Port" }
    [pscustomobject]@{ Name = 'schedule'; Color = 'Green';   Arguments = 'artisan schedule:work' }
    [pscustomobject]@{ Name = 'queue';    Color = 'Magenta'; Arguments = 'artisan queue:listen --tries=1 --timeout=0' }
)

# Output arrives on background threads, so it is parked in a thread-safe
# queue and drained by the main loop. Writing to the console straight from
# an event handler is not safe here.
$lines   = New-Object System.Collections.Concurrent.ConcurrentQueue[psobject]
$running = @()
$subs    = @()

function Drain {
    $item = $null
    while ($lines.TryDequeue([ref]$item)) {
        Write-Host ('  [{0,-8}] ' -f $item.Name) -ForegroundColor $item.Color -NoNewline
        Write-Host $item.Text
    }
}

foreach ($spec in $specs) {
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName               = 'php'
    $psi.Arguments              = $spec.Arguments
    $psi.WorkingDirectory       = $root
    $psi.UseShellExecute        = $false
    $psi.CreateNoWindow         = $true
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError  = $true

    $proc = New-Object System.Diagnostics.Process
    $proc.StartInfo = $psi

    $tag = [pscustomobject]@{ Name = $spec.Name; Color = $spec.Color; Sink = $lines }

    $handler = {
        if ($null -ne $EventArgs.Data) {
            $Event.MessageData.Sink.Enqueue([pscustomobject]@{
                Name  = $Event.MessageData.Name
                Color = $Event.MessageData.Color
                Text  = $EventArgs.Data
            })
        }
    }

    $subs += Register-ObjectEvent -InputObject $proc -EventName OutputDataReceived -MessageData $tag -Action $handler
    $subs += Register-ObjectEvent -InputObject $proc -EventName ErrorDataReceived  -MessageData $tag -Action $handler

    [void]$proc.Start()
    $proc.BeginOutputReadLine()
    $proc.BeginErrorReadLine()

    $running += [pscustomobject]@{ Name = $spec.Name; Color = $spec.Color; Proc = $proc }
    Write-Host ('  started {0,-8} PID {1}' -f $spec.Name, $proc.Id) -ForegroundColor DarkGray
}

Write-Host ''
Write-Host "  Open http://localhost:$Port" -ForegroundColor White
Write-Host '  Log in with a seeded account, e.g. admin@gmail.com / password' -ForegroundColor DarkGray
Write-Host '  Ctrl+C stops all three.' -ForegroundColor DarkGray
Write-Host ''

# --- Run --------------------------------------------------------------

# Ctrl+C is read as a keypress rather than left to tear the script down,
# so the finally block is guaranteed to run and clean up the children.
$interactive = -not [Console]::IsInputRedirected
if ($interactive) { [Console]::TreatControlCAsInput = $true }

try {
    while ($true) {
        Drain

        $dead = $running | Where-Object { $_.Proc.HasExited }
        if ($dead) {
            Start-Sleep -Milliseconds 200
            Drain
            foreach ($d in $dead) {
                Write-Host ''
                Write-Host ("  [{0}] exited with code {1} - stopping the others." -f $d.Name, $d.Proc.ExitCode) -ForegroundColor Red
            }
            break
        }

        if ($interactive -and [Console]::KeyAvailable) {
            $key = [Console]::ReadKey($true)
            if ($key.Key -eq 'C' -and ($key.Modifiers -band [ConsoleModifiers]::Control)) {
                Write-Host ''
                Write-Host '  Ctrl+C - stopping all three...' -ForegroundColor Yellow
                break
            }
        }

        Start-Sleep -Milliseconds 120
    }
}
finally {
    Drain

    foreach ($r in $running) {
        if (-not $r.Proc.HasExited) {
            # /T because "artisan serve" runs the built-in server as a child
            # process; killing only the parent would leave the port bound.
            & taskkill.exe /PID $r.Proc.Id /T /F *>$null
        }
    }

    foreach ($s in $subs) {
        Unregister-Event -SubscriptionId $s.Id -ErrorAction SilentlyContinue
        Remove-Job -Job $s -Force -ErrorAction SilentlyContinue
    }

    if ($interactive) { [Console]::TreatControlCAsInput = $false }

    Write-Host '  All stopped.' -ForegroundColor DarkGray
    Write-Host ''
}
