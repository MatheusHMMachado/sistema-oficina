# Gera PDFs a partir dos documentos Markdown em docs/
# Requisito: Node.js instalado
# Uso (qualquer pasta):
#   .\docs\gerar-pdfs.ps1
#   ou clique duplo em docs\gerar-pdfs.bat

$ErrorActionPreference = "Stop"

$docsDir = $PSScriptRoot
$root = Split-Path -Parent $docsDir
Set-Location $root

$pdfDir = Join-Path $docsDir "pdf"
New-Item -ItemType Directory -Force -Path $pdfDir | Out-Null

$css = Join-Path $docsDir "pdf-style.css"

if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
    Write-Host "Erro: Node.js nao encontrado. Instale em https://nodejs.org" -ForegroundColor Red
    exit 1
}

$docs = @(
    "01-modelo-entidade-relacionamento.md",
    "02-regras-de-negocio.md",
    "03-requisitos-funcionais-e-nao-funcionais.md"
)

Write-Host ""
Write-Host "=== Gerando PDFs do Sistema de Oficina ===" -ForegroundColor Cyan
Write-Host ""

$ok = 0
$fail = 0

foreach ($doc in $docs) {
    $mdPath = Join-Path $docsDir $doc
    $pdfName = [System.IO.Path]::ChangeExtension($doc, ".pdf")
    $dest = Join-Path $pdfDir $pdfName

    if (-not (Test-Path $mdPath)) {
        Write-Warning "Arquivo nao encontrado: $mdPath"
        $fail++
        continue
    }

    Write-Host "Convertendo: $doc" -ForegroundColor Gray

    & npx --yes md-to-pdf $mdPath --stylesheet $css
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  FALHOU: $doc" -ForegroundColor Red
        $fail++
        continue
    }

    $generated = Join-Path $docsDir $pdfName
    if (Test-Path $generated) {
        Move-Item -Force $generated $dest
    }

    if (Test-Path $dest) {
        $size = [math]::Round((Get-Item $dest).Length / 1KB, 0)
        Write-Host "  OK -> docs\pdf\$pdfName ($size KB)" -ForegroundColor Green
        $ok++
    } else {
        Write-Host "  FALHOU: PDF nao gerado para $doc" -ForegroundColor Red
        $fail++
    }
}

Write-Host ""
if ($fail -eq 0) {
    Write-Host "Concluido! $ok PDF(s) em:" -ForegroundColor Green
    Write-Host "  $pdfDir" -ForegroundColor White
} else {
    Write-Host "Finalizado com erros: $ok OK, $fail falha(s)." -ForegroundColor Yellow
    Write-Host "Pasta de saida: $pdfDir" -ForegroundColor White
}
Write-Host ""
