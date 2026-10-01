$hosts = Join-Path $env:SystemRoot 'System32\drivers\etc\hosts'
$names = @('clinic.epharma.test', 'test.epharma.test')
$content = Get-Content -Path $hosts -Raw
foreach ($name in $names) {
    if ($content -notmatch [regex]::Escape($name)) {
        Add-Content -Path $hosts -Value "127.0.0.1 $name"
        $content += " 127.0.0.1 $name"
    }
}
ipconfig /flushdns | Out-Null
