param(
  [Parameter(Mandatory=$true)][string]$Server,
  [Parameter(Mandatory=$true)][string]$User,
  [string]$WebRoot = "/var/www/savethechew",
  [string]$FilePath = "$PSScriptRoot/../donate.html"
)

Write-Host "Server:" $Server
Write-Host "User:" $User
Write-Host "WebRoot:" $WebRoot
Write-Host "FilePath:" $FilePath

if (!(Test-Path -LiteralPath $FilePath)) {
  Write-Error "File not found: $FilePath"
  exit 1
}

$RemotePath = "$WebRoot/donate.html"

Write-Host "Backing up existing donate.html on server (if present)…"
$backupCmd = "sudo test -f $RemotePath && sudo cp $RemotePath $RemotePath.bak_$(date +%F_%H%M%S) || true"
ssh "$User@$Server" $backupCmd
if ($LASTEXITCODE -ne 0) { Write-Warning "Backup step returned non-zero; continuing." }

Write-Host "Uploading donate.html via scp…"
scp -p "$FilePath" "$User@$Server:$RemotePath"
if ($LASTEXITCODE -ne 0) { Write-Error "scp failed."; exit 1 }

Write-Host "Applying permissions…"
ssh "$User@$Server" "sudo chown www-data:www-data $RemotePath; sudo chmod 644 $RemotePath"
if ($LASTEXITCODE -ne 0) { Write-Warning "Permissions step returned non-zero; continuing." }

Write-Host "Verifying production page headers…"
curl.exe -I https://savethechew.biz/donate.html | Out-String | Write-Host

Write-Host "Done. Open https://savethechew.biz/donate.html#incentives to visually confirm all five incentives are visible."