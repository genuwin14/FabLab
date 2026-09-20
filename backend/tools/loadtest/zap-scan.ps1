<#
.SYNOPSIS
    Drives a running OWASP ZAP instance (GUI or daemon) through its REST API
    to scan FabLab twice: once anonymously, once logged in as a customer.

.DESCRIPTION
    Start ZAP first, e.g.

        java -jar zap-2.17.0.jar -port 8090 -config api.key=fablabzap

    then run

        .\zap-scan.ps1 -Target http://127.0.0.1:8080 -ReportDir C:\FabLab\backend\docs\testing\zap

    The script builds a ZAP context around the target, registers the seeded
    customer account with form-based authentication (ZAP fills the Laravel
    _token itself), spiders and active-scans both anonymously and as that
    user, then writes HTML + JSON reports into ReportDir.
#>
[CmdletBinding()]
param(
    [string] $Zap        = 'http://127.0.0.1:8090',
    [string] $ApiKey     = 'fablabzap',
    [string] $Target     = 'http://127.0.0.1:8080',
    [string] $Email      = 'customer@gmail.com',
    [string] $Password   = 'password',
    [string] $ReportDir  = 'C:\FabLab\backend\docs\testing\zap',
    [int]    $MaxScanMinutes = 25
)

$ErrorActionPreference = 'Stop'

function Zap([string] $Path, [hashtable] $Query = @{}) {
    $qs = ($Query.GetEnumerator() | ForEach-Object {
        "$([uri]::EscapeDataString($_.Key))=$([uri]::EscapeDataString([string]$_.Value))"
    }) -join '&'
    $url = "$Zap$Path`?apikey=$ApiKey" + ($(if ($qs) { "&$qs" } else { '' }))
    Invoke-RestMethod -Uri $url -TimeoutSec 120
}

function Wait-Scan([string] $StatusPath, [string] $ScanId, [string] $Label) {
    do {
        Start-Sleep -Seconds 10
        $pct = [int](Zap $StatusPath @{ scanId = $ScanId }).status
        Write-Host ("  {0,-28} {1,3}%" -f $Label, $pct)
    } while ($pct -lt 100)
}

$escaped = [regex]::Escape($Target)

Write-Host "ZAP $((Zap '/JSON/core/view/version/').version) at $Zap"
New-Item -ItemType Directory -Force $ReportDir | Out-Null

# --- Context ---------------------------------------------------------
$contextName = 'FabLab'
$contextId = (Zap '/JSON/context/action/newContext/' @{ contextName = $contextName }).contextId
Zap '/JSON/context/action/includeInContext/' @{ contextName = $contextName; regex = "$escaped.*" } | Out-Null

# Never let the scanner log the user out or wander off to Google.
foreach ($re in @("$escaped/logout.*", "$escaped/login/google.*")) {
    Zap '/JSON/context/action/excludeFromContext/' @{ contextName = $contextName; regex = $re } | Out-Null
    Zap '/JSON/spider/action/excludeFromScan/'     @{ regex = $re } | Out-Null
    Zap '/JSON/ascan/action/excludeFromScan/'      @{ regex = $re } | Out-Null
}

# --- Authentication (form-based, Laravel CSRF token filled by ZAP) -----
Zap '/JSON/acsrf/action/addOptionToken/' @{ String = '_token' } | Out-Null
$authParams = "loginUrl=$Target/login&loginPageUrl=$Target/login&loginRequestData=" +
    [uri]::EscapeDataString('email={%username%}&password={%password%}&_token=ZAP')
Zap '/JSON/authentication/action/setAuthenticationMethod/' @{
    contextId = $contextId; authMethodName = 'formBasedAuthentication'; authMethodConfigParams = $authParams
} | Out-Null
Zap '/JSON/authentication/action/setLoggedInIndicator/'  @{ contextId = $contextId; loggedInIndicatorRegex  = '\Q/logout\E' } | Out-Null
Zap '/JSON/authentication/action/setLoggedOutIndicator/' @{ contextId = $contextId; loggedOutIndicatorRegex = '\Qname="password"\E' } | Out-Null
Zap '/JSON/sessionManagement/action/setSessionManagementMethod/' @{ contextId = $contextId; methodName = 'cookieBasedSessionManagement' } | Out-Null

$userId = (Zap '/JSON/users/action/newUser/' @{ contextId = $contextId; name = 'customer' }).userId
Zap '/JSON/users/action/setAuthenticationCredentials/' @{
    contextId = $contextId; userId = $userId
    authCredentialsConfigParams = "username=$([uri]::EscapeDataString($Email))&password=$([uri]::EscapeDataString($Password))"
} | Out-Null
Zap '/JSON/users/action/setUserEnabled/' @{ contextId = $contextId; userId = $userId; enabled = 'true' } | Out-Null

# --- Scanner limits ----------------------------------------------------
Zap '/JSON/spider/action/setOptionMaxDuration/'         @{ Integer = 5 } | Out-Null
Zap '/JSON/ascan/action/setOptionMaxScanDurationInMins/' @{ Integer = $MaxScanMinutes } | Out-Null
Zap '/JSON/ascan/action/setOptionThreadPerHost/'         @{ Integer = 6 } | Out-Null

# --- Phase 1: anonymous ------------------------------------------------
Write-Host "`nPhase 1: anonymous spider + active scan of $Target"
Zap '/JSON/core/action/accessUrl/' @{ url = $Target; followRedirects = 'true' } | Out-Null
$sid = (Zap '/JSON/spider/action/scan/' @{ url = $Target; contextName = $contextName; recurse = 'true' }).scan
Wait-Scan '/JSON/spider/view/status/' $sid 'spider (anonymous)'
$aid = (Zap '/JSON/ascan/action/scan/' @{ url = $Target; recurse = 'true'; contextId = $contextId }).scan
Wait-Scan '/JSON/ascan/view/status/' $aid 'active scan (anonymous)'

# --- Phase 2: logged in as the seeded customer -------------------------
Write-Host "`nPhase 2: spider + active scan as $Email"
$sid = (Zap '/JSON/spider/action/scanAsUser/' @{ contextId = $contextId; userId = $userId; url = "$Target/customer/shop"; recurse = 'true' }).scanAsUser
Wait-Scan '/JSON/spider/view/status/' $sid 'spider (customer)'
$aid = (Zap '/JSON/ascan/action/scanAsUser/' @{ url = "$Target/customer/shop"; contextId = $contextId; userId = $userId; recurse = 'true' }).scanAsUser
Wait-Scan '/JSON/ascan/view/status/' $aid 'active scan (customer)'

# --- Reports -----------------------------------------------------------
Write-Host "`nWriting reports to $ReportDir"
foreach ($t in @(@{ template = 'traditional-html'; file = 'zap-report' }, @{ template = 'traditional-json'; file = 'zap-report' }, @{ template = 'traditional-md'; file = 'zap-report' })) {
    $r = Zap '/JSON/reports/action/generate/' @{
        title = 'FabLab OWASP ZAP scan'; template = $t.template; reportDir = $ReportDir; reportFileName = $t.file
        description = "Automated spider + active scan of $Target, anonymous and as $Email"
    }
    Write-Host "  $($r.generate)"
}

$summary = Zap '/JSON/alert/view/alertsSummary/' @{ baseurl = $Target }
Write-Host "`nAlerts by risk: $($summary.alertsSummary | ConvertTo-Json -Compress)"
Write-Host "URLs known to ZAP: $((Zap '/JSON/core/view/numberOfMessages/' @{ baseurl = $Target }).numberOfMessages) messages"
Write-Host 'Done.'
