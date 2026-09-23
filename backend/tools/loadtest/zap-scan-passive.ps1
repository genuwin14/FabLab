<#
.SYNOPSIS
    A gentle OWASP ZAP pass for a LIVE site: spider + passive scan only, with
    no active (attack) scan, plus a short access-control probe. Safe to run
    against the production Hostinger site — it only browses pages a normal
    visitor would, and never fuzzes inputs or submits attacks.

.DESCRIPTION
    Start ZAP first, e.g.

        java -jar zap-2.17.0.jar -port 8090 -config api.key=fablabzap

    then run

        .\zap-scan-passive.ps1 -Target https://palegoldenrod-kudu-488454.hostingersite.com

    What it does:
      1. Builds a context around the target and excludes /logout and Google.
      2. Traditional spider (GET only, forms NOT submitted), bounded by time
         and depth, as an anonymous visitor. Passive rules analyse every
         response ZAP sees — headers, cookies, information leaks — without
         sending a single attack.
      3. Access-control probe: requests admin, staff and customer pages while
         signed out and records the HTTP status, to show they redirect to the
         login page (302) rather than serving protected content.
      4. Writes HTML + JSON + Markdown reports.
#>
[CmdletBinding()]
param(
    [string] $Zap        = 'http://127.0.0.1:8090',
    [string] $ApiKey     = 'fablabzap',
    [string] $Target     = 'https://palegoldenrod-kudu-488454.hostingersite.com',
    [string] $ReportDir  = (Join-Path $PSScriptRoot '..\..\docs\testing-20260923\zap-live'),
    [int]    $SpiderMinutes = 4
)

$ErrorActionPreference = 'Stop'

function Zap([string] $Path, [hashtable] $Query = @{}) {
    $qs = ($Query.GetEnumerator() | ForEach-Object {
        "$([uri]::EscapeDataString($_.Key))=$([uri]::EscapeDataString([string]$_.Value))"
    }) -join '&'
    $url = "$Zap$Path`?apikey=$ApiKey" + ($(if ($qs) { "&$qs" } else { '' }))
    Invoke-RestMethod -Uri $url -TimeoutSec 120
}

$escaped = [regex]::Escape($Target)

Write-Host "ZAP $((Zap '/JSON/core/view/version/').version) at $Zap"
New-Item -ItemType Directory -Force $ReportDir | Out-Null
$ReportDir = (Resolve-Path $ReportDir).Path

# --- Context ---------------------------------------------------------
$contextName = 'FabLab-Live'
# Start clean so re-runs don't stack contexts.
try { Zap '/JSON/context/action/removeContext/' @{ contextName = $contextName } | Out-Null } catch {}
$contextId = (Zap '/JSON/context/action/newContext/' @{ contextName = $contextName }).contextId
Zap '/JSON/context/action/includeInContext/' @{ contextName = $contextName; regex = "$escaped.*" } | Out-Null
foreach ($re in @("$escaped/logout.*", "$escaped/login/google.*")) {
    Zap '/JSON/context/action/excludeFromContext/' @{ contextName = $contextName; regex = $re } | Out-Null
    Zap '/JSON/spider/action/excludeFromScan/'     @{ regex = $re } | Out-Null
}

# --- Spider limits: bounded and form-safe ------------------------------
Zap '/JSON/spider/action/setOptionMaxDuration/'   @{ Integer = $SpiderMinutes } | Out-Null
Zap '/JSON/spider/action/setOptionMaxDepth/'      @{ Integer = 6 } | Out-Null
Zap '/JSON/spider/action/setOptionPostForm/'      @{ Boolean = 'false' } | Out-Null
Zap '/JSON/spider/action/setOptionProcessForm/'   @{ Boolean = 'false' } | Out-Null
Zap '/JSON/pscan/action/enableAllScanners/' | Out-Null

# --- Spider (anonymous, passive analysis happens automatically) --------
Write-Host "`nSpidering $Target (GET only, no attacks) for up to $SpiderMinutes min"
Zap '/JSON/core/action/accessUrl/' @{ url = $Target; followRedirects = 'true' } | Out-Null
$sid = (Zap '/JSON/spider/action/scan/' @{ url = $Target; contextName = $contextName; recurse = 'true' }).scan
do {
    Start-Sleep -Seconds 5
    $pct = [int](Zap '/JSON/spider/view/status/' @{ scanId = $sid }).status
    Write-Host ("  spider {0,3}%" -f $pct)
} while ($pct -lt 100)

Write-Host "Waiting for passive scan queue to drain"
do {
    Start-Sleep -Seconds 3
    $left = [int](Zap '/JSON/pscan/view/recordsToScan/').recordsToScan
    Write-Host ("  passive queue: {0}" -f $left)
} while ($left -gt 0)

# --- Access-control probe (anonymous) ----------------------------------
Write-Host "`nAccess-control probe (signed out):"
$protected = @('/admin/dashboard', '/admin/orders', '/admin/users',
               '/staff/dashboard', '/staff/orders',
               '/customer/orders', '/customer/shop', '/customer/cart')
$acRows = foreach ($p in $protected) {
    $r = Zap '/JSON/core/action/accessUrl/' @{ url = "$Target$p"; followRedirects = 'false' }
    # accessUrl returns the response; pull the status line and any Location.
    $msg = (Zap '/JSON/core/view/messages/' @{ baseurl = "$Target$p" }).messages | Select-Object -Last 1
    $status = if ($msg) { ($msg.responseHeader -split "`r?`n")[0] } else { 'n/a' }
    $loc = if ($msg) { (($msg.responseHeader -split "`r?`n") | Where-Object { $_ -match '^Location:' }) -replace 'Location:\s*', '' } else { '' }
    Write-Host ("  {0,-22} {1}  {2}" -f $p, $status, $loc)
    [pscustomobject]@{ path = $p; status = $status; location = $loc }
}
$acRows | ConvertTo-Json | Set-Content -Path (Join-Path $ReportDir 'access-control.json') -Encoding UTF8

# --- Reports -----------------------------------------------------------
Write-Host "`nWriting reports to $ReportDir"
foreach ($t in @(@{ template = 'traditional-html'; file = 'zap-live-report' }, @{ template = 'traditional-json'; file = 'zap-live-report' }, @{ template = 'traditional-md'; file = 'zap-live-report' })) {
    $r = Zap '/JSON/reports/action/generate/' @{
        title = 'FabLab OWASP ZAP - live site (passive)'; template = $t.template; reportDir = $ReportDir; reportFileName = $t.file
        description = "Spider + passive scan of the live site $Target, anonymous, no active attack. Plus an access-control probe of admin/staff/customer pages while signed out."
    }
    Write-Host "  $($r.generate)"
}

$summary = Zap '/JSON/alert/view/alertsSummary/' @{ baseurl = $Target }
Write-Host "`nAlerts by risk: $($summary.alertsSummary | ConvertTo-Json -Compress)"
Write-Host "URLs known to ZAP: $((Zap '/JSON/core/view/numberOfMessages/' @{ baseurl = $Target }).numberOfMessages) messages"
Write-Host 'Done (live passive).'
